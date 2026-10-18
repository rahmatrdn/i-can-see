import express from "express";
import path from "path";
import { fileURLToPath } from "url";
import { createServer as createViteServer } from "vite";
import { GoogleGenAI, Type } from "@google/genai";
import dotenv from "dotenv";

dotenv.config();

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const ai = new GoogleGenAI({
  apiKey: process.env.GEMINI_API_KEY,
  httpOptions: {
    headers: {
      "User-Agent": "aistudio-build",
    },
  },
});

async function generateSpeechBase64(text: string): Promise<string | null> {
  // 1. Primary: Fetch natural English MP3 speech stream (reliable, zero quota exhaustion)
  try {
    const cleanText = text.replace(/\s+/g, " ").trim().slice(0, 195);
    const url =
      "https://translate.google.com/translate_tts?ie=UTF-8&client=tw-ob&tl=en&q=" +
      encodeURIComponent(cleanText);
    const res = await fetch(url, {
      headers: {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)",
      },
    });
    if (res.ok) {
      const buf = Buffer.from(await res.arrayBuffer());
      if (buf.length > 200) {
        return `data:audio/mpeg;base64,${buf.toString("base64")}`;
      }
    }
  } catch (fallbackErr) {
    console.warn("Primary MP3 TTS warning:", fallbackErr);
  }

  // 2. Secondary fallback: Gemini TTS
  try {
    const ttsResponse = await ai.models.generateContent({
      model: "gemini-3.8-flash-lite-tts",
      contents: [
        {
          role: "user",
          parts: [
            {
              text: text,
            },
          ],
        },
      ],
      config: {
        responseModalities: ["AUDIO"],
        speechConfig: {
          voiceConfig: {
            prebuiltVoiceConfig: { voiceName: "Kore" },
          },
        },
      },
    });
    const base64Audio =
      ttsResponse.candidates?.[0]?.content?.parts?.[0]?.inlineData?.data;
    return base64Audio ? `data:audio/wav;base64,${base64Audio}` : null;
  } catch (err) {
    console.error("TTS generation error:", err);
    return null;
  }
}

async function transcribeAudioWithFallback(
  cleanAudioBase64: string,
  normalizedMime: string
): Promise<string> {
  const prompt =
    "Listen to this audio recording and transcribe the exact words spoken by the user (in English or Indonesian). Return ONLY the exact words spoken without any extra commentary or quotation marks. If the audio is completely silent with no human voice at all, return an empty string.";

  try {
    const res1 = await ai.models.generateContent({
      model: "gemini-3.5-transcribe",
      contents: {
        parts: [
          {
            inlineData: {
              mimeType: normalizedMime,
              data: cleanAudioBase64,
            },
          },
          { text: prompt },
        ],
      },
    });
    const text1 = (res1.text || "").trim();
    if (text1) return text1;
  } catch (err1) {
    console.warn("gemini-3.5-transcribe fallback to gemini-3.8-flash:", err1);
  }

  try {
    const res2 = await ai.models.generateContent({
      model: "gemini-3.8-flash",
      contents: {
        parts: [
          {
            inlineData: {
              mimeType: normalizedMime,
              data: cleanAudioBase64,
            },
          },
          { text: prompt },
        ],
      },
    });
    return (res2.text || "").trim();
  } catch (err2) {
    console.error("Audio transcription fallback error:", err2);
    return "";
  }
}

async function startServer() {
  const app = express();
  const PORT = 3000;

  app.use(express.json({ limit: "25mb" }));

  // 1. Endpoint for standalone TTS
  app.post("/api/tts", async (req, res) => {
    try {
      const { text } = req.body;
      if (!text) {
        return res.status(400).json({ error: "Text is required" });
      }
      const audioBase64 = await generateSpeechBase64(text);
      res.json({ audioBase64 });
    } catch (error: any) {
      console.error("Error in /api/tts:", error);
      res.status(500).json({ error: error.message || "Failed to generate TTS" });
    }
  });

  // 2. Main Multimodal Voice & Vision Assistant Endpoint
  app.post("/api/assist", async (req, res) => {
    try {
      const {
        mode, // 'qa' | 'camera'
        userText,
        audioBase64,
        audioMimeType,
        imageBase64,
        imageMimeType,
        history = [],
      } = req.body;

      let transcribedUserText = (userText || "").trim();
      const cleanAudioBase64 = audioBase64
        ? audioBase64.replace(/^data:audio\/[^;]+;base64,/, "")
        : "";
      const normalizedAudioMime = (audioMimeType || "audio/webm")
        .split(";")[0]
        .trim();

      // If audio was recorded without client-side text, transcribe it first
      if (!transcribedUserText && cleanAudioBase64) {
        transcribedUserText = await transcribeAudioWithFallback(
          cleanAudioBase64,
          normalizedAudioMime
        );
      }

      const hasImage = Boolean(imageBase64);

      // Validation: if not capturing camera image and no spoken text was detected at all, return noSpeechDetected immediately
      if (!hasImage && mode !== "camera" && !transcribedUserText) {
        return res.json({
          noSpeechDetected: true,
        });
      }

      // Check if user's spoken words ask to open camera / take a picture / see what's in front
      const cameraIntentRegex =
        /\b(take a picture|take a photo|capture|open camera|use camera|turn on camera|what is in front|what's in front|look in front|describe what|read this|read the text|what am i holding|scan around|see around|check camera|take picture|take photo|ambil gambar|ambil foto)\b/i;

      const userWantsCameraNow =
        mode === "camera" || cameraIntentRegex.test(transcribedUserText);

      // If user asked to take a picture via voice, but client hasn't sent imageBase64 yet,
      // tell the client to activate camera, take the snapshot, and re-submit with the image!
      if (userWantsCameraNow && !imageBase64) {
        return res.json({
          requiresCameraCapture: true,
          userTranscript:
            transcribedUserText ||
            "Please take a picture and describe what is in front of me.",
        });
      }

      const systemInstruction = `You are "iCanSee", a warm, empathetic, and crystal-clear voice and visual AI assistant designed for blind and low-vision users.
Core rules:
1. By default, you are a conversational voice companion. Speak in natural, warm, concise English that sounds great when read aloud (never use markdown symbols, asterisks, or bullet points).
2. When NO camera image is attached (voice Q&A mode):
   - Answer the user's question or conversation directly, clearly, and helpfully in English.
3. When a camera image IS attached (because the user asked to take a picture / describe surroundings):
   - Describe the main objects in front of the camera spatially (center, left, right, approximate distance, colors, any visible text, and any obstacles or safety hazards).
   - Populate the main detected items in 'detectedObjects'.
4. Conclude your answer or visual description cleanly and naturally so the user knows the explanation is complete.`;

      const parts: any[] = [];

      // Include recent conversation context
      if (Array.isArray(history) && history.length > 0) {
        const recentHistory = history
          .slice(-6)
          .map(
            (h: { role: string; text: string }) =>
              `${h.role === "user" ? "User" : "iCanSee"}: ${h.text}`
          )
          .join("\n");
        parts.push({
          text: `Previous conversation context:\n${recentHistory}\n---`,
        });
      }

      if (hasImage && imageBase64) {
        const cleanBase64 = imageBase64.replace(/^data:image\/\w+;base64,/, "");
        parts.push({
          inlineData: {
            mimeType: imageMimeType || "image/jpeg",
            data: cleanBase64,
          },
        });
      }

      const promptText = hasImage
        ? `The user asked to capture an image with the request: "${
            transcribedUserText ||
            "Take a picture and describe what is in front of me"
          }". Describe clearly, spatially, and in natural spoken English what is visible in this camera image to assist a blind person.`
        : `User's spoken message via microphone: "${transcribedUserText}". Answer the user's question or statement directly, warmly, and naturally in English as the iCanSee voice assistant.`;

      parts.push({ text: promptText });

      let parsed: any = {};
      try {
        const response = await ai.models.generateContent({
          model: "gemini-3.8-flash",
          contents: { parts },
          config: {
            systemInstruction,
            responseMimeType: "application/json",
            responseSchema: {
              type: Type.OBJECT,
              properties: {
                userHeardText: {
                  type: Type.STRING,
                  description: "The words spoken by the user that you heard.",
                },
                intentUsed: {
                  type: Type.STRING,
                  description:
                    "Set to 'camera' if describing a camera image, or 'qa' if answering a voice question without camera.",
                },
                spokenResponse: {
                  type: Type.STRING,
                  description:
                    "Natural spoken response in English to be read aloud to the blind user. Do not use markdown.",
                },
                summaryTitle: {
                  type: Type.STRING,
                  description:
                    "Short 2-4 word title in English summarizing the response or camera result.",
                },
                detectedObjects: {
                  type: Type.ARRAY,
                  items: {
                    type: Type.OBJECT,
                    properties: {
                      name: {
                        type: Type.STRING,
                        description: "Object name in English",
                      },
                      position: {
                        type: Type.STRING,
                        description:
                          "Spatial position in English (e.g., Center front, Slightly left)",
                      },
                    },
                    required: ["name", "position"],
                  },
                  description:
                    "List of objects detected in the camera (empty array [] if voice Q&A without camera).",
                },
                safetyNote: {
                  type: Type.STRING,
                  description:
                    "Brief safety note in English if there is an obstacle in the camera, or empty string if clear / no camera.",
                },
              },
              required: [
                "intentUsed",
                "spokenResponse",
                "summaryTitle",
                "detectedObjects",
              ],
            },
          },
        });

        const rawText = response.text || "{}";
        parsed = JSON.parse(rawText);
      } catch (genErr) {
        console.warn("Structured JSON generation fallback to plain text:", genErr);
        const plainRes = await ai.models.generateContent({
          model: "gemini-3.8-flash",
          contents: { parts },
          config: { systemInstruction },
        });
        parsed = {
          intentUsed: hasImage ? "camera" : "qa",
          spokenResponse:
            plainRes.text ||
            "I hear you. Please let me know how I can help you today.",
          summaryTitle: hasImage ? "Camera Description" : "AI Voice Answer",
          detectedObjects: [],
        };
      }

      const finalUserTranscript =
        transcribedUserText ||
        parsed.userHeardText ||
        (hasImage ? "Take a picture" : "Voice message");

      // Double check: if audio was processed directly by gemini-3.8-flash and it turns out userHeardText was asking for camera!
      if (!hasImage && cameraIntentRegex.test(finalUserTranscript)) {
        return res.json({
          requiresCameraCapture: true,
          userTranscript: finalUserTranscript,
        });
      }

      const spokenText =
        parsed.spokenResponse ||
        "Sorry, I could not hear that clearly. Please tap the screen and speak again.";

      const ttsAudioBase64 = await generateSpeechBase64(spokenText);

      res.json({
        requiresCameraCapture: false,
        userTranscript: finalUserTranscript,
        intentUsed: hasImage ? "camera" : "qa",
        spokenResponse: spokenText,
        summaryTitle:
          parsed.summaryTitle ||
          (hasImage ? "Camera Description" : "AI Voice Answer"),
        detectedObjects: hasImage ? parsed.detectedObjects || [] : [],
        safetyNote: hasImage ? parsed.safetyNote || "" : "",
        ttsAudioBase64,
      });
    } catch (error: any) {
      console.error("Error in /api/assist:", error);
      res.status(500).json({
        error: error.message || "An error occurred while processing the AI request.",
      });
    }
  });

  app.use(express.static(path.join(__dirname, "public")));

  if (process.env.NODE_ENV !== "production") {
    const vite = await createViteServer({
      server: { middlewareMode: true },
      appType: "spa",
    });
    app.use(vite.middlewares);
  } else {
    const distPath = path.join(__dirname, "dist");
    app.use(express.static(distPath));
    app.get("*all", (_req, res) => {
      res.sendFile(path.join(distPath, "index.html"));
    });
  }

  app.listen(PORT, "0.0.0.0", () => {
    console.log(`iCanSee Server running on http://localhost:${PORT}`);
  });
}

startServer();
