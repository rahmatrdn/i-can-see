import React, { useState, useRef, useEffect, useCallback } from "react";
import {
  Camera,
  CameraOff,
  Mic,
  Volume2,
  VolumeX,
  SwitchCamera,
  Sparkles,
  MessageSquare,
  Eye,
  CheckCircle2,
  Headphones,
  Hand,
  Radio,
} from "lucide-react";
import { soundFX, POINT_CAMERA_INSTRUCTION_TEXT } from "./utils/soundEffects";

export type InteractionPhase =
  | "IDLE"
  | "ASKING_INTENT"
  | "LISTENING_VOICE"
  | "AIMING_CAMERA"
  | "CAPTURING_CAMERA"
  | "PROCESSING_AI"
  | "SPEAKING_RESPONSE";

export interface DetectedObject {
  name: string;
  position: string;
}

export interface InteractionLog {
  id: string;
  timestamp: string;
  intentUsed: "camera" | "qa";
  userTranscript: string;
  summaryTitle: string;
  spokenResponse: string;
  detectedObjects: DetectedObject[];
  safetyNote?: string;
  snapshotDataUrl?: string;
}

interface RipplePoint {
  id: number;
  x: number;
  y: number;
}

function getSupportedAudioMimeType(): string {
  if (typeof MediaRecorder === "undefined") return "";
  const types = [
    "audio/webm;codecs=opus",
    "audio/webm",
    "audio/mp4",
    "audio/ogg;codecs=opus",
  ];
  for (const t of types) {
    if (MediaRecorder.isTypeSupported(t)) {
      return t;
    }
  }
  return "";
}

export default function App() {
  const [phase, setPhase] = useState<InteractionPhase>("IDLE");
  const [statusMessage, setStatusMessage] = useState<string>(
    "Tap once to speak, or double-tap to take a picture"
  );
  const [subStatusText, setSubStatusText] = useState<string>(
    "Tap 1x anywhere to greet the AI by voice, or double-tap (2x) anywhere to capture a photo."
  );
  const [liveTranscript, setLiveTranscript] = useState<string>("");
  const [micLevel, setMicLevel] = useState<number>(0);
  const [micError, setMicError] = useState<string | null>(null);

  // Camera is OFF by default! Turns on when double-tapped (AIMING_CAMERA) or when capturing a photo
  const [cameraActive, setCameraActive] = useState<boolean>(false);
  const [facingMode, setFacingMode] = useState<"environment" | "user">("environment");
  const [lastCapturedImage, setLastCapturedImage] = useState<string | null>(null);

  const [isMuted, setIsMuted] = useState<boolean>(false);
  const [ripples, setRipples] = useState<RipplePoint[]>([]);
  const [logs, setLogs] = useState<InteractionLog[]>([]);
  const [textFallbackInput, setTextFallbackInput] = useState<string>("");

  const videoRef = useRef<HTMLVideoElement | null>(null);
  const canvasRef = useRef<HTMLCanvasElement | null>(null);
  const audioPlayerRef = useRef<HTMLAudioElement | null>(null);
  const recognitionRef = useRef<any>(null);
  const mediaRecorderRef = useRef<MediaRecorder | null>(null);
  const micStreamRef = useRef<MediaStream | null>(null);
  const cameraStreamRef = useRef<MediaStream | null>(null);
  const audioChunksRef = useRef<Blob[]>([]);
  const vadAnimationRef = useRef<number | null>(null);
  const silenceDebounceTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null);
  const maxListenTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null);
  const singleTapTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null);
  const lastTapTimeRef = useRef<number>(0);
  const audioContextRef = useRef<AudioContext | null>(null);
  const abortControllerRef = useRef<AbortController | null>(null);
  const isSubmittingVoiceRef = useRef<boolean>(false);
  const hasUserSpokenRef = useRef<boolean>(false);
  const liveTranscriptRef = useRef<string>("");
  const logsRef = useRef<InteractionLog[]>([]);

  const phaseRef = useRef<InteractionPhase>("IDLE");
  const isMutedRef = useRef<boolean>(false);

  useEffect(() => {
    phaseRef.current = phase;
  }, [phase]);

  useEffect(() => {
    isMutedRef.current = isMuted;
  }, [isMuted]);

  useEffect(() => {
    logsRef.current = logs;
  }, [logs]);

  // Stop camera hardware stream completely so camera stays OFF when not needed
  const stopCameraStream = useCallback(() => {
    if (cameraStreamRef.current) {
      cameraStreamRef.current.getTracks().forEach((track) => track.stop());
      cameraStreamRef.current = null;
    }
    if (videoRef.current) {
      videoRef.current.srcObject = null;
    }
    setCameraActive(false);
  }, []);

  useEffect(() => {
    return () => {
      if (singleTapTimerRef.current) {
        clearTimeout(singleTapTimerRef.current);
      }
      stopCameraStream();
    };
  }, [stopCameraStream]);

  // Turn on live camera stream (used during AIMING_CAMERA when user double-taps, or right before capture)
  const startLiveCameraStream = useCallback(async (): Promise<MediaStream | null> => {
    setCameraActive(true);
    if (cameraStreamRef.current && cameraStreamRef.current.active) {
      if (videoRef.current && videoRef.current.srcObject !== cameraStreamRef.current) {
        videoRef.current.srcObject = cameraStreamRef.current;
        await videoRef.current.play().catch(() => {});
      }
      return cameraStreamRef.current;
    }

    try {
      const stream = await navigator.mediaDevices.getUserMedia({
        video: {
          facingMode: { ideal: facingMode },
          width: { ideal: 1280 },
          height: { ideal: 720 },
        },
        audio: false,
      });

      cameraStreamRef.current = stream;

      if (videoRef.current) {
        videoRef.current.srcObject = stream;
        await videoRef.current.play().catch(() => {});
      }
      return stream;
    } catch (err) {
      console.warn("Live camera stream fallback:", err);
      return null;
    }
  }, [facingMode]);

  // Capture base64 JPEG from active camera (or activate camera if not already open), play shutter sound, and turn camera back off
  const activateCameraAndCaptureSnapshot = useCallback(async (): Promise<string> => {
    const alreadyStreaming =
      Boolean(cameraStreamRef.current && cameraStreamRef.current.active) &&
      Boolean(videoRef.current && videoRef.current.videoWidth > 0);

    setCameraActive(true);
    soundFX.playCameraShutter();

    try {
      if (!alreadyStreaming) {
        await startLiveCameraStream();
        // Give camera sensor ~650ms to adjust auto-exposure and focus if it wasn't already aiming
        await new Promise((resolve) => setTimeout(resolve, 650));
      } else {
        // Brief 120ms settle when user taps to capture from live aiming preview
        await new Promise((resolve) => setTimeout(resolve, 120));
      }

      const canvas = canvasRef.current;
      if (canvas && videoRef.current && videoRef.current.videoWidth > 0) {
        canvas.width = videoRef.current.videoWidth;
        canvas.height = videoRef.current.videoHeight;
        const ctx = canvas.getContext("2d");
        if (ctx) {
          ctx.drawImage(videoRef.current, 0, 0, canvas.width, canvas.height);
          const dataUrl = canvas.toDataURL("image/jpeg", 0.84);
          setLastCapturedImage(dataUrl);
          stopCameraStream();
          return dataUrl;
        }
      }
    } catch (err) {
      console.warn("On-demand camera fallback:", err);
    }

    // Fallback simulated scene if device has no physical camera or permission denied in sandbox
    stopCameraStream();
    const canvas = canvasRef.current;
    if (!canvas) return "";
    const ctx = canvas.getContext("2d");
    if (!ctx) return "";

    canvas.width = 640;
    canvas.height = 480;
    ctx.fillStyle = "#131B2E";
    ctx.fillRect(0, 0, 640, 480);

    ctx.fillStyle = "#334155";
    ctx.fillRect(40, 280, 560, 180);
    ctx.fillStyle = "#38BDF8";
    ctx.fillRect(140, 180, 80, 130);
    ctx.fillStyle = "#FACC15";
    ctx.fillRect(280, 220, 160, 95);
    ctx.fillStyle = "#FFFFFF";
    ctx.font = "bold 20px sans-serif";
    ctx.fillText("Mineral Water Bottle (Left)", 80, 165);
    ctx.fillText("Notebook & Reading Glasses (Center)", 215, 205);
    ctx.fillText("Warm Coffee Mug (Right)", 400, 260);
    const fallbackUrl = canvas.toDataURL("image/jpeg", 0.82);
    setLastCapturedImage(fallbackUrl);
    return fallbackUrl;
  }, [startLiveCameraStream, stopCameraStream]);

  // Cleanly stop any ongoing audio, speech recognition, fetch requests, or microphone stream
  const stopAllAudioAndListening = useCallback(
    (keepCameraOpen = false) => {
      if (abortControllerRef.current) {
        try {
          abortControllerRef.current.abort();
        } catch {
          // ignore
        }
        abortControllerRef.current = null;
      }

      if (silenceDebounceTimerRef.current) {
        clearTimeout(silenceDebounceTimerRef.current);
        silenceDebounceTimerRef.current = null;
      }
      if (maxListenTimerRef.current) {
        clearTimeout(maxListenTimerRef.current);
        maxListenTimerRef.current = null;
      }
      if (vadAnimationRef.current) {
        cancelAnimationFrame(vadAnimationRef.current);
        vadAnimationRef.current = null;
      }
      setMicLevel(0);

      if (audioPlayerRef.current) {
        audioPlayerRef.current.onended = null;
        audioPlayerRef.current.onerror = null;
        audioPlayerRef.current.pause();
        audioPlayerRef.current.currentTime = 0;
      }
      if (typeof window !== "undefined" && "speechSynthesis" in window) {
        window.speechSynthesis.cancel();
      }
      soundFX.stopStoredGreeting();
      if (recognitionRef.current) {
        try {
          recognitionRef.current.onresult = null;
          recognitionRef.current.onend = null;
          recognitionRef.current.onerror = null;
          recognitionRef.current.stop();
        } catch {
          // ignore
        }
        recognitionRef.current = null;
      }
      if (mediaRecorderRef.current && mediaRecorderRef.current.state !== "inactive") {
        try {
          mediaRecorderRef.current.onstop = null;
          mediaRecorderRef.current.stop();
        } catch {
          // ignore
        }
      }
      mediaRecorderRef.current = null;

      if (micStreamRef.current) {
        micStreamRef.current.getTracks().forEach((t) => t.stop());
        micStreamRef.current = null;
      }

      if (!keepCameraOpen) {
        stopCameraStream();
      }
    },
    [stopCameraStream]
  );

  // Complete reset to IDLE state when user clicks once while active (except when in AIMING_CAMERA where 1x tap captures photo)
  const haltAllProcessesAndReturnToIdle = useCallback(() => {
    stopAllAudioAndListening(false);
    isSubmittingVoiceRef.current = false;
    hasUserSpokenRef.current = false;
    liveTranscriptRef.current = "";
    setLiveTranscript("");
    soundFX.playStopChime();
    setPhase("IDLE");
    setStatusMessage("Interaction Stopped");
    setSubStatusText(
      "Tap 1x anywhere to speak with AI, or double-tap (2x) anywhere to take a picture."
    );
  }, [stopAllAudioAndListening]);

  // Speak text using Gemini TTS base64 WAV or fallback to Web Speech API
  const playSpokenAudio = useCallback(
    async (text: string, preloadedBase64?: string | null, onFinished?: () => void) => {
      if (isMutedRef.current) {
        if (onFinished) onFinished();
        return;
      }

      if (typeof window !== "undefined" && "speechSynthesis" in window) {
        window.speechSynthesis.cancel();
      }

      let base64Wav = preloadedBase64;

      if (!base64Wav) {
        try {
          const res = await fetch("/api/tts", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ text }),
          });
          if (res.ok) {
            const data = await res.json();
            base64Wav = data.audioBase64;
          }
        } catch {
          // Fallback to browser speechSynthesis
        }
      }

      // Check if user already stopped the process while TTS was fetching
      if (
        phaseRef.current !== "ASKING_INTENT" &&
        phaseRef.current !== "SPEAKING_RESPONSE" &&
        phaseRef.current !== "PROCESSING_AI"
      ) {
        return;
      }

      if (base64Wav) {
        try {
          if (!audioPlayerRef.current) {
            audioPlayerRef.current = new Audio();
          }
          const player = audioPlayerRef.current;
          player.src = base64Wav.startsWith("data:")
            ? base64Wav
            : `data:audio/wav;base64,${base64Wav}`;
          player.onended = () => {
            if (onFinished) onFinished();
          };
          player.onerror = () => {
            if (onFinished) onFinished();
          };
          await player.play();
          return;
        } catch {
          // If autoplay or audio element fails, use browser SpeechSynthesis
        }
      }

      if (typeof window !== "undefined" && "speechSynthesis" in window) {
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = "en-US";
        utterance.rate = 1.02;
        utterance.onend = () => {
          if (onFinished) onFinished();
        };
        utterance.onerror = () => {
          if (onFinished) onFinished();
        };
        window.speechSynthesis.speak(utterance);
      } else if (onFinished) {
        onFinished();
      }
    },
    []
  );

  // Process user's voice or camera request and send to /api/assist
  const processUserIntent = useCallback(
    async (opts: {
      forceCamera?: boolean;
      userText?: string;
      audioBase64?: string;
      audioMimeType?: string;
    }) => {
      const cleanedUserText = (opts.userText || "").trim();
      const explicitCameraRegex =
        /\b(take a picture|take a photo|capture|open camera|use camera|turn on camera|what is in front|what's in front|look in front|describe what|read this|read the text|what am i holding|scan around|see around|check camera|take picture|take photo|ambil gambar|ambil foto)\b/i;

      let shouldCaptureCamera =
        Boolean(opts.forceCamera) || explicitCameraRegex.test(cleanedUserText);

      // Validation: If not capturing camera and nothing was spoken at all, return straight to IDLE without "Processing answer"
      if (!shouldCaptureCamera && !cleanedUserText && !opts.audioBase64) {
        stopAllAudioAndListening(false);
        isSubmittingVoiceRef.current = false;
        hasUserSpokenRef.current = false;
        setPhase("IDLE");
        setStatusMessage("Tap once to speak, or double-tap to take a picture");
        setSubStatusText(
          "Tap 1x anywhere to greet the AI by voice, or double-tap (2x) anywhere to capture a photo."
        );
        return;
      }

      // Keep camera stream open if we are about to capture from AIMING_CAMERA
      stopAllAudioAndListening(shouldCaptureCamera);

      const controller = new AbortController();
      abortControllerRef.current = controller;

      let capturedImageBase64 = "";

      // If capturing from camera (either via 2x shortcut + 1x tap, or via voice command)
      if (shouldCaptureCamera) {
        setPhase("CAPTURING_CAMERA");
        setStatusMessage("Capturing photo...");
        setSubStatusText("Tap anywhere on the screen if you wish to cancel");
        capturedImageBase64 = await activateCameraAndCaptureSnapshot();
        if (controller.signal.aborted) return;

        // Play local stored "Processing image" right after taking the picture!
        setPhase("PROCESSING_AI");
        setStatusMessage("Processing image");
        setSubStatusText("AI is analyzing the objects in front of you...");
        soundFX.playStoredProcessingImage(isMutedRef.current);
      } else {
        // User spoke into the mic -> Play local stored "Processing answer"!
        setPhase("PROCESSING_AI");
        setStatusMessage("Processing answer");
        setSubStatusText(
          cleanedUserText
            ? `You said: "${cleanedUserText}"`
            : "Processing your spoken question..."
        );
        soundFX.playStoredProcessingAnswer(isMutedRef.current);
      }

      try {
        const historyPayload = logsRef.current
          .slice(0, 5)
          .reverse()
          .flatMap((item) => [
            { role: "user", text: item.userTranscript },
            { role: "assistant", text: item.spokenResponse },
          ]);

        let response = await fetch("/api/assist", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          signal: controller.signal,
          body: JSON.stringify({
            mode: shouldCaptureCamera ? "camera" : "qa",
            userText: cleanedUserText,
            audioBase64: opts.audioBase64,
            audioMimeType: opts.audioMimeType,
            imageBase64: capturedImageBase64 || undefined,
            imageMimeType: capturedImageBase64 ? "image/jpeg" : undefined,
            history: historyPayload,
          }),
        });

        if (!response.ok) {
          throw new Error("Failed to reach the AI server");
        }

        let data = await response.json();
        if (controller.signal.aborted) return;

        // If server confirmed no speech was detected in audio, return straight to IDLE
        if (data.noSpeechDetected) {
          stopAllAudioAndListening(false);
          isSubmittingVoiceRef.current = false;
          hasUserSpokenRef.current = false;
          setPhase("IDLE");
          setStatusMessage("Tap once to speak, or double-tap to take a picture");
          setSubStatusText(
            "Tap 1x anywhere to greet the AI by voice, or double-tap (2x) anywhere to capture a photo."
          );
          return;
        }

        // If the user spoke via raw audio (transcribed on server) and the server realized they asked to take a picture!
        if (data.requiresCameraCapture) {
          shouldCaptureCamera = true;
          setPhase("CAPTURING_CAMERA");
          setStatusMessage("Turning on camera & taking picture...");
          setSubStatusText(`Voice command: "${data.userTranscript}"`);
          capturedImageBase64 = await activateCameraAndCaptureSnapshot();
          if (controller.signal.aborted) return;

          // Play local stored "Processing image" right after capturing the image while AI analyzes it!
          setPhase("PROCESSING_AI");
          setStatusMessage("Processing image");
          setSubStatusText("AI is analyzing the objects in front of you...");
          soundFX.playStoredProcessingImage(isMutedRef.current);

          response = await fetch("/api/assist", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            signal: controller.signal,
            body: JSON.stringify({
              mode: "camera",
              userText: data.userTranscript,
              imageBase64: capturedImageBase64,
              imageMimeType: "image/jpeg",
              history: historyPayload,
            }),
          });

          if (!response.ok) {
            throw new Error("Failed to analyze the camera image");
          }
          data = await response.json();
          if (controller.signal.aborted) return;
        }

        const newEntry: InteractionLog = {
          id: Date.now().toString(),
          timestamp: new Date().toLocaleTimeString("en-US", {
            hour: "2-digit",
            minute: "2-digit",
            second: "2-digit",
          }),
          intentUsed: data.intentUsed === "camera" ? "camera" : "qa",
          userTranscript: data.userTranscript || cleanedUserText || "Voice Conversation",
          summaryTitle: data.summaryTitle || "iCanSee Response",
          spokenResponse: data.spokenResponse,
          detectedObjects: Array.isArray(data.detectedObjects) ? data.detectedObjects : [],
          safetyNote: data.safetyNote || "",
          snapshotDataUrl: capturedImageBase64 || undefined,
        };

        setLogs((prev) => [newEntry, ...prev.slice(0, 9)]);
        setLiveTranscript("");
        liveTranscriptRef.current = "";
        setPhase("SPEAKING_RESPONSE");
        setStatusMessage(newEntry.summaryTitle);
        setSubStatusText(newEntry.spokenResponse);
        soundFX.playSuccessChime();

        // After AI finishes speaking its answer or camera explanation, STOP the app and return to IDLE so user starts by tapping again from the beginning!
        await playSpokenAudio(newEntry.spokenResponse, data.ttsAudioBase64, () => {
          if (phaseRef.current === "SPEAKING_RESPONSE") {
            stopAllAudioAndListening(false);
            isSubmittingVoiceRef.current = false;
            hasUserSpokenRef.current = false;
            setPhase("IDLE");
            setStatusMessage("Tap once to speak, or double-tap to take a picture");
            setSubStatusText(
              "Tap 1x anywhere to greet the AI by voice, or double-tap (2x) anywhere to capture a photo."
            );
          }
        });
      } catch (err: any) {
        if (err?.name === "AbortError") {
          return;
        }
        console.error("AI Assist error:", err);
        const fallbackMsg =
          "Sorry, there was a connection issue reaching the AI. Please tap the screen to try again.";
        setPhase("SPEAKING_RESPONSE");
        setStatusMessage("Connection Issue");
        setSubStatusText(fallbackMsg);
        await playSpokenAudio(fallbackMsg, null, () => {
          setPhase("IDLE");
          setStatusMessage("Tap once to speak, or double-tap to take a picture");
          setSubStatusText(
            "Tap 1x anywhere to greet the AI by voice, or double-tap (2x) anywhere to capture a photo."
          );
        });
      }
    },
    [activateCameraAndCaptureSnapshot, playSpokenAudio, stopAllAudioAndListening]
  );

  // Finalize current voice recording and dispatch to Gemini AI ONLY if user actually spoke
  const finishVoiceRecordingAndSubmit = useCallback(() => {
    if (isSubmittingVoiceRef.current) return;
    isSubmittingVoiceRef.current = true;

    if (silenceDebounceTimerRef.current) {
      clearTimeout(silenceDebounceTimerRef.current);
      silenceDebounceTimerRef.current = null;
    }
    if (maxListenTimerRef.current) {
      clearTimeout(maxListenTimerRef.current);
      maxListenTimerRef.current = null;
    }
    if (vadAnimationRef.current) {
      cancelAnimationFrame(vadAnimationRef.current);
      vadAnimationRef.current = null;
    }
    setMicLevel(0);

    if (recognitionRef.current) {
      try {
        recognitionRef.current.onresult = null;
        recognitionRef.current.onend = null;
        recognitionRef.current.onerror = null;
        recognitionRef.current.stop();
      } catch {
        // ignore
      }
      recognitionRef.current = null;
    }

    const spokenTextSoFar = liveTranscriptRef.current.trim();
    const userDidSpeak = hasUserSpokenRef.current || spokenTextSoFar.length > 0;

    // Validation: If nothing was spoken at all, do NOT play "Processing answer" — return straight to IDLE!
    if (!userDidSpeak) {
      if (mediaRecorderRef.current && mediaRecorderRef.current.state !== "inactive") {
        try {
          mediaRecorderRef.current.onstop = null;
          mediaRecorderRef.current.stop();
        } catch {
          // ignore
        }
      }
      mediaRecorderRef.current = null;
      if (micStreamRef.current) {
        micStreamRef.current.getTracks().forEach((t) => t.stop());
        micStreamRef.current = null;
      }

      isSubmittingVoiceRef.current = false;
      hasUserSpokenRef.current = false;
      liveTranscriptRef.current = "";
      setLiveTranscript("");
      setPhase("IDLE");
      setStatusMessage("Tap once to speak, or double-tap to take a picture");
      setSubStatusText(
        "Tap 1x anywhere to greet the AI by voice, or double-tap (2x) anywhere to capture a photo."
      );
      return;
    }

    // 1. FAST PATH: If SpeechRecognition captured the user's spoken words, send immediately
    if (spokenTextSoFar.length > 0) {
      if (mediaRecorderRef.current && mediaRecorderRef.current.state !== "inactive") {
        try {
          mediaRecorderRef.current.onstop = null;
          mediaRecorderRef.current.stop();
        } catch {
          // ignore
        }
      }
      mediaRecorderRef.current = null;
      if (micStreamRef.current) {
        micStreamRef.current.getTracks().forEach((t) => t.stop());
        micStreamRef.current = null;
      }

      processUserIntent({ userText: spokenTextSoFar });
      return;
    }

    // 2. AUDIO BLOB PATH: User spoke into the mic (detected by Web Audio VAD), extract MediaRecorder audio and send to Gemini
    const recorder = mediaRecorderRef.current;
    if (recorder && recorder.state !== "inactive") {
      const mimeType = recorder.mimeType || "audio/webm";
      let handled = false;

      const submitBlobChunks = () => {
        if (handled) return;
        handled = true;

        if (micStreamRef.current) {
          micStreamRef.current.getTracks().forEach((t) => t.stop());
          micStreamRef.current = null;
        }

        const audioBlob = new Blob(audioChunksRef.current, { type: mimeType });
        if (audioBlob.size > 100) {
          const reader = new FileReader();
          reader.onloadend = () => {
            const resultStr = (reader.result as string) || "";
            const base64data = resultStr.includes(",")
              ? resultStr.split(",")[1]
              : resultStr;
            if (base64data) {
              processUserIntent({
                audioBase64: base64data,
                audioMimeType: mimeType,
              });
            } else {
              isSubmittingVoiceRef.current = false;
              hasUserSpokenRef.current = false;
              setPhase("IDLE");
              setStatusMessage("Tap once to speak, or double-tap to take a picture");
            }
          };
          reader.readAsDataURL(audioBlob);
        } else {
          isSubmittingVoiceRef.current = false;
          hasUserSpokenRef.current = false;
          setPhase("IDLE");
          setStatusMessage("Tap once to speak, or double-tap to take a picture");
        }
      };

      recorder.onstop = submitBlobChunks;
      try {
        recorder.stop();
        setTimeout(submitBlobChunks, 350);
      } catch {
        submitBlobChunks();
      }
      return;
    }

    if (micStreamRef.current) {
      micStreamRef.current.getTracks().forEach((t) => t.stop());
      micStreamRef.current = null;
    }

    isSubmittingVoiceRef.current = false;
    hasUserSpokenRef.current = false;
    setPhase("IDLE");
    setStatusMessage("Tap once to speak, or double-tap to take a picture");
  }, [processUserIntent]);

  // Start Hybrid Microphone Capture with 4-Second No-Question Timeout & Spoken-Word Validation
  const startListeningForUserIntent = useCallback(async () => {
    stopAllAudioAndListening(false);
    isSubmittingVoiceRef.current = false;
    hasUserSpokenRef.current = false;
    liveTranscriptRef.current = "";
    setLiveTranscript("");
    setMicError(null);
    setPhase("LISTENING_VOICE");
    setStatusMessage("Listening to your voice...");
    setSubStatusText(
      'Ask the AI anything, or say "Take a picture" if you want to use the camera'
    );
    soundFX.playListeningStart();

    // Wait 4 full seconds (4000ms) if no question is spoken before stopping and returning to IDLE
    const resetNoQuestionTimer = (timeoutMs = 4000) => {
      if (maxListenTimerRef.current) {
        clearTimeout(maxListenTimerRef.current);
      }
      maxListenTimerRef.current = setTimeout(() => {
        if (phaseRef.current === "LISTENING_VOICE" && !isSubmittingVoiceRef.current) {
          if (
            hasUserSpokenRef.current ||
            liveTranscriptRef.current.trim().length > 0
          ) {
            finishVoiceRecordingAndSubmit();
          } else {
            stopAllAudioAndListening(false);
            setPhase("IDLE");
            setStatusMessage("Tap once to speak, or double-tap to take a picture");
            setSubStatusText(
              "Tap 1x anywhere to greet the AI by voice, or double-tap (2x) anywhere to capture a photo."
            );
          }
        }
      }, timeoutMs);
    };

    resetNoQuestionTimer(4000);

    const scheduleAutoSubmitAfterPause = (delayMs = 1350) => {
      if (silenceDebounceTimerRef.current) {
        clearTimeout(silenceDebounceTimerRef.current);
      }
      silenceDebounceTimerRef.current = setTimeout(() => {
        if (
          phaseRef.current === "LISTENING_VOICE" &&
          !isSubmittingVoiceRef.current &&
          (hasUserSpokenRef.current || liveTranscriptRef.current.trim().length > 0)
        ) {
          finishVoiceRecordingAndSubmit();
        }
      }, delayMs);
    };

    // 1. Start Web Speech API in English (en-US) for real-time speech capture & automatic turn-taking
    const SpeechRecognition =
      (window as any).SpeechRecognition || (window as any).webkitSpeechRecognition;
    if (SpeechRecognition) {
      try {
        const recognition = new SpeechRecognition();
        recognitionRef.current = recognition;
        recognition.lang = "en-US";
        recognition.interimResults = true;
        recognition.continuous = true;

        recognition.onresult = (event: any) => {
          let combined = "";
          let hasFinalPiece = false;
          for (let i = 0; i < event.results.length; ++i) {
            combined += event.results[i][0].transcript;
            if (event.results[i].isFinal) {
              hasFinalPiece = true;
            }
          }
          const cleaned = combined.trim();
          if (cleaned) {
            hasUserSpokenRef.current = true;
            liveTranscriptRef.current = cleaned;
            setLiveTranscript(cleaned);
            resetNoQuestionTimer(6000);
            scheduleAutoSubmitAfterPause(hasFinalPiece ? 1000 : 1500);
          }
        };

        recognition.onerror = () => {
          if (
            liveTranscriptRef.current.trim().length > 0 &&
            !isSubmittingVoiceRef.current
          ) {
            finishVoiceRecordingAndSubmit();
          }
        };

        recognition.onend = () => {
          if (
            phaseRef.current === "LISTENING_VOICE" &&
            !isSubmittingVoiceRef.current
          ) {
            if (liveTranscriptRef.current.trim().length > 0) {
              finishVoiceRecordingAndSubmit();
            } else {
              // Keep SpeechRecognition alive until the 4-second no-question timer expires!
              try {
                recognition.start();
              } catch {
                // ignore if already started or stopped by 4s timer
              }
            }
          }
        };

        recognition.start();
      } catch (e) {
        console.warn("SpeechRecognition start warning:", e);
      }
    }

    // 2. Acquire microphone stream for MediaRecorder + real-time volume meter
    let stream = micStreamRef.current;
    try {
      if (!stream || !stream.active) {
        stream = await navigator.mediaDevices.getUserMedia({
          audio: {
            echoCancellation: true,
            noiseSuppression: true,
            autoGainControl: true,
          },
        });
        micStreamRef.current = stream;
      }
    } catch (err: any) {
      console.warn("Microphone getUserMedia warning:", err);
      if (!SpeechRecognition) {
        setMicError(
          "Microphone permission is not enabled. Please allow microphone access in your browser."
        );
        setStatusMessage("Microphone Permission Needed");
        return;
      }
    }

    if (phaseRef.current !== "LISTENING_VOICE") {
      if (stream) stream.getTracks().forEach((t) => t.stop());
      return;
    }

    // 3. Start MediaRecorder so spoken audio is captured reliably even if SpeechRecognition is blocked in iframe
    if (stream) {
      try {
        audioChunksRef.current = [];
        const supportedMime = getSupportedAudioMimeType();
        const recorder = supportedMime
          ? new MediaRecorder(stream, { mimeType: supportedMime })
          : new MediaRecorder(stream);

        mediaRecorderRef.current = recorder;
        recorder.ondataavailable = (e) => {
          if (e.data && e.data.size > 0) {
            audioChunksRef.current.push(e.data);
          }
        };
        recorder.start(250);
      } catch (recErr) {
        console.warn("MediaRecorder init error:", recErr);
      }

      // 4. Web Audio API real-time microphone volume meter & voice activity detection (VAD)
      try {
        const AudioCtx =
          window.AudioContext ||
          (window as unknown as { webkitAudioContext: typeof AudioContext }).webkitAudioContext;
        if (!audioContextRef.current) {
          audioContextRef.current = new AudioCtx();
        }
        const audioCtx = audioContextRef.current;
        if (audioCtx.state === "suspended") {
          await audioCtx.resume().catch(() => {});
        }

        const source = audioCtx.createMediaStreamSource(stream);
        const analyser = audioCtx.createAnalyser();
        analyser.fftSize = 512;
        analyser.smoothingTimeConstant = 0.4;
        source.connect(analyser);

        const dataArray = new Uint8Array(analyser.frequencyBinCount);
        const listenStartTime = Date.now();
        let voiceActiveFrames = 0;
        let lastVoiceTime = Date.now();

        const checkVolumeLoop = () => {
          if (phaseRef.current !== "LISTENING_VOICE" || isSubmittingVoiceRef.current) {
            setMicLevel(0);
            return;
          }

          analyser.getByteFrequencyData(dataArray);
          let sum = 0;
          for (let i = 0; i < dataArray.length; i++) {
            sum += dataArray[i];
          }
          const avg = sum / dataArray.length;
          const normalizedLevel = Math.min(100, Math.round((avg / 55) * 100));
          setMicLevel(normalizedLevel);

          const now = Date.now();
          // Ignore the first 350ms so the listening start chime is never mistaken for user speech
          if (now - listenStartTime > 350 && avg > 14) {
            voiceActiveFrames += 1;
            lastVoiceTime = now;

            // Once user produces sustained vocal sound (>= 5 frames ~100ms), mark that user has spoken!
            if (voiceActiveFrames >= 5 && !hasUserSpokenRef.current) {
              hasUserSpokenRef.current = true;
              resetNoQuestionTimer(6000);
            }
          }

          // Once user has spoken and then pauses for 1.35 seconds, automatically submit their recorded voice!
          if (
            (hasUserSpokenRef.current || liveTranscriptRef.current.trim().length > 0) &&
            now - lastVoiceTime > 1350
          ) {
            finishVoiceRecordingAndSubmit();
            return;
          }

          vadAnimationRef.current = requestAnimationFrame(checkVolumeLoop);
        };

        vadAnimationRef.current = requestAnimationFrame(checkVolumeLoop);
      } catch {
        // Ignore AudioContext analyser errors
      }
    }
  }, [finishVoiceRecordingAndSubmit, stopAllAudioAndListening]);

  // Trigger concise stored Greeting ("Hello!!") locally without calling AI when user single-taps from IDLE
  const startAiGreetingAndAskUser = useCallback(() => {
    // Pre-unlock microphone permission inside user gesture
    if (!micStreamRef.current || !micStreamRef.current.active) {
      navigator.mediaDevices
        .getUserMedia({
          audio: {
            echoCancellation: true,
            noiseSuppression: true,
            autoGainControl: true,
          },
        })
        .then((stream) => {
          micStreamRef.current = stream;
          setMicError(null);
        })
        .catch(() => {});
    }

    setPhase("ASKING_INTENT");
    setStatusMessage("Hi there.");
    setSubStatusText(
      'Speak directly to ask a question, or double-tap (2x) anywhere to take a picture.'
    );

    // Play stored local "/audio/hello.mp3" ("Hi there.") voice immediately (no AI API request)
    soundFX.playStoredHelloGreeting(isMutedRef.current, () => {
      if (phaseRef.current === "ASKING_INTENT") {
        startListeningForUserIntent();
      }
    });
  }, [startListeningForUserIntent]);

  // DOUBLE-TAP (2x CLICK) SHORTCUT ANYWHERE ON SCREEN:
  // 1. Turns on live camera so user can point at the object
  // 2. Plays the voice prompt: "Point your camera at the object you want to capture. Then, tap the screen once when you're ready."
  // 3. Waits for user to tap 1x anywhere on the screen, then captures and sends to AI!
  const startCameraAimingShortcut = useCallback(() => {
    stopAllAudioAndListening(false);
    isSubmittingVoiceRef.current = false;
    liveTranscriptRef.current = "";
    setLiveTranscript("");

    setPhase("AIMING_CAMERA");
    phaseRef.current = "AIMING_CAMERA";
    setStatusMessage("Point Your Camera");
    setSubStatusText(POINT_CAMERA_INSTRUCTION_TEXT);

    // Turn on the live camera feed while user aims at the object
    startLiveCameraStream();

    // Play the voice instruction: "Point your camera at the object you want to capture. Then, tap the screen once when you're ready."
    soundFX.playStoredPointCameraPrompt(isMutedRef.current);
  }, [startLiveCameraStream, stopAllAudioAndListening]);

  // Unified Single-Tap (1x) vs Double-Tap (2x) gesture dispatcher anywhere on the screen
  const triggerScreenGestureAction = useCallback(() => {
    // If already in AIMING_CAMERA mode (after double-tap), a single tap (1x) immediately captures & processes the picture!
    if (phaseRef.current === "AIMING_CAMERA") {
      if (singleTapTimerRef.current) {
        clearTimeout(singleTapTimerRef.current);
        singleTapTimerRef.current = null;
      }
      lastTapTimeRef.current = 0;
      processUserIntent({
        forceCamera: true,
        userText: "Take a picture and describe the object in front of me.",
      });
      return;
    }

    const now = Date.now();
    const timeSinceLastTap = now - lastTapTimeRef.current;

    // Detect Double-Tap (2x click within 320ms) anywhere on screen -> Take Picture Shortcut!
    if (timeSinceLastTap > 0 && timeSinceLastTap < 320) {
      if (singleTapTimerRef.current) {
        clearTimeout(singleTapTimerRef.current);
        singleTapTimerRef.current = null;
      }
      lastTapTimeRef.current = 0;
      startCameraAimingShortcut();
      return;
    }

    // First tap: wait briefly (270ms) to check if a second tap follows for a double-tap
    lastTapTimeRef.current = now;
    if (singleTapTimerRef.current) {
      clearTimeout(singleTapTimerRef.current);
    }

    singleTapTimerRef.current = setTimeout(() => {
      singleTapTimerRef.current = null;
      lastTapTimeRef.current = 0;

      if (phaseRef.current === "IDLE") {
        startAiGreetingAndAskUser();
      } else if (phaseRef.current === "AIMING_CAMERA") {
        processUserIntent({
          forceCamera: true,
          userText: "Take a picture and describe the object in front of me.",
        });
      } else {
        haltAllProcessesAndReturnToIdle();
      }
    }, 270);
  }, [
    haltAllProcessesAndReturnToIdle,
    processUserIntent,
    startAiGreetingAndAskUser,
    startCameraAimingShortcut,
  ]);

  // Universal Screen Tap Handler (1x Tap vs 2x Double-Tap anywhere on the screen)
  const handleUniversalCanvasTap = (e: React.MouseEvent<HTMLDivElement>) => {
    const rect = e.currentTarget.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    const newRipple = { id: Date.now() + Math.random(), x, y };
    setRipples((prev) => [...prev.slice(-3), newRipple]);

    triggerScreenGestureAction();
  };

  // Center Circle Click Handler: behaves identically to tapping anywhere on the screen
  const handleCenterCircleClick = (e: React.MouseEvent<HTMLButtonElement>) => {
    e.stopPropagation();
    triggerScreenGestureAction();
  };

  const handleQuickPrompt = (promptText: string, forceCamera: boolean) => {
    processUserIntent({
      forceCamera,
      userText: promptText,
    });
  };

  const handleTextFallbackSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!textFallbackInput.trim()) return;
    const q = textFallbackInput.trim();
    setTextFallbackInput("");
    processUserIntent({
      userText: q,
    });
  };

  const isAiSpeaking =
    phase === "ASKING_INTENT" ||
    phase === "SPEAKING_RESPONSE" ||
    phase === "AIMING_CAMERA";
  const isProcessActive = phase !== "IDLE";

  // Dynamic color theme for the redesigned centerpiece orb based on current state (pure radial & circular gradients, zero rectangular box artifacts)
  const orbTheme =
    phase === "IDLE"
      ? {
          waveBorder: "border-[#FACC15]/45",
          waveBg: "bg-[#FACC15]/10",
          auraStyle:
            "radial-gradient(circle, rgba(250,204,21,0.42) 0%, rgba(245,158,11,0.18) 55%, rgba(250,204,21,0) 72%)",
          coreStyle:
            "radial-gradient(circle at 35% 28%, #FEF08A 0%, #FACC15 48%, #CA8A04 100%)",
          coreShadow: "shadow-[0_0_65px_rgba(250,204,21,0.5)]",
          textColor: "text-[#090D16]",
        }
      : phase === "AIMING_CAMERA"
      ? {
          waveBorder: "border-[#FACC15]/75",
          waveBg: "bg-[#FACC15]/20",
          auraStyle:
            "radial-gradient(circle, rgba(250,204,21,0.58) 0%, rgba(245,158,11,0.25) 55%, rgba(250,204,21,0) 72%)",
          coreStyle:
            "radial-gradient(circle at 35% 28%, #FEF08A 0%, #FACC15 48%, #D97706 100%)",
          coreShadow: "shadow-[0_0_85px_rgba(250,204,21,0.8)]",
          textColor: "text-[#090D16]",
        }
      : isAiSpeaking
      ? {
          waveBorder: "border-[#34D399]/60",
          waveBg: "bg-[#34D399]/15",
          auraStyle:
            "radial-gradient(circle, rgba(52,211,153,0.5) 0%, rgba(16,185,129,0.22) 55%, rgba(52,211,153,0) 72%)",
          coreStyle:
            "radial-gradient(circle at 35% 28%, #A7F3D0 0%, #34D399 48%, #059669 100%)",
          coreShadow: "shadow-[0_0_80px_rgba(52,211,153,0.7)]",
          textColor: "text-[#090D16]",
        }
      : phase === "LISTENING_VOICE"
      ? {
          waveBorder: "border-[#38BDF8]/60",
          waveBg: "bg-[#38BDF8]/15",
          auraStyle:
            "radial-gradient(circle, rgba(56,189,248,0.5) 0%, rgba(2,132,199,0.22) 55%, rgba(56,189,248,0) 72%)",
          coreStyle:
            "radial-gradient(circle at 35% 28%, #BAE6FD 0%, #38BDF8 48%, #0284C7 100%)",
          coreShadow: "shadow-[0_0_80px_rgba(56,189,248,0.75)]",
          textColor: "text-[#090D16]",
        }
      : {
          waveBorder: "border-white/50",
          waveBg: "bg-white/10",
          auraStyle:
            "radial-gradient(circle, rgba(255,255,255,0.45) 0%, rgba(148,163,184,0.2) 55%, rgba(255,255,255,0) 72%)",
          coreStyle:
            "radial-gradient(circle at 35% 28%, #FFFFFF 0%, #E2E8F0 55%, #94A3B8 100%)",
          coreShadow: "shadow-[0_0_70px_rgba(255,255,255,0.6)]",
          textColor: "text-[#090D16]",
        };

  return (
    <div className="min-h-screen bg-[#090D16] text-[#F8FAFC] flex flex-col justify-between selection:bg-[#FACC15] selection:text-[#090D16]">
      {/* Hidden offscreen canvas for capturing camera frames */}
      <canvas ref={canvasRef} className="hidden" />

      {/* Main Responsive Layout: Fullscreen on Mobile Devices, Centered Mobile Frame + Companion Panel on Desktop */}
      <main className="flex-1 w-full max-w-7xl mx-auto flex flex-col lg:flex-row items-center lg:items-stretch justify-center lg:gap-10 lg:px-8 lg:py-6">
        {/* ================= CENTERED MOBILE FRAME (FULL VIEWPORT ON MOBILE, PHONE BEZEL ON DESKTOP) ================= */}
        <section
          aria-label="iCanSee Main Screen"
          className="relative w-full h-[100dvh] lg:h-[840px] lg:max-h-[90vh] lg:w-[412px] lg:shrink-0 bg-[#090D16] lg:rounded-[48px] lg:border-[10px] lg:border-[#1E293B] lg:shadow-[0_25px_80px_rgba(0,0,0,0.85)] overflow-hidden flex flex-col select-none"
        >
          {/* Desktop Hardware Speaker Cutout */}
          <div
            aria-hidden="true"
            className="hidden lg:flex items-center justify-center pt-2 pb-1 bg-[#090D16] z-30"
          >
            <div className="w-24 h-1.5 rounded-full bg-[#1E293B]" />
          </div>

          {/* BACKGROUND LAYER: CAMERA IS OFF BY DEFAULT, SHOWS LIVE STREAM DURING AIMING/CAPTURE OR LAST SNAPSHOT */}
          <div className="absolute inset-0 z-0 bg-[#090D16]">
            <video
              ref={videoRef}
              autoPlay
              playsInline
              muted
              className={`w-full h-full object-cover transition-opacity duration-300 ${
                cameraActive ? "opacity-90" : "opacity-0 pointer-events-none"
              }`}
            />
            {!cameraActive && lastCapturedImage && (
              <img
                src={lastCapturedImage}
                alt="Last captured camera photo"
                referrerPolicy="no-referrer"
                className="w-full h-full object-cover opacity-25 blur-[2px]"
              />
            )}
            <div
              className={`absolute inset-0 transition-opacity duration-300 ${
                cameraActive
                  ? "bg-gradient-to-b from-[#090D16]/80 via-[#090D16]/45 to-[#090D16]/85"
                  : "bg-gradient-to-b from-[#090D16]/95 via-[#090D16]/75 to-[#090D16]/95"
              }`}
            />
          </div>

          {/* COMPACT MOBILE TOP APP BAR (<= 56px) */}
          <header className="relative z-20 flex items-center justify-between px-4 h-14 bg-[#090D16]/85 backdrop-blur-md border-b border-white/10">
            <div className="flex items-center gap-2.5">
              <div className="w-9 h-9 rounded-full bg-[#FACC15] text-[#090D16] flex items-center justify-center font-extrabold text-lg shadow-md">
                <Eye className="w-5 h-5 stroke-[2.5]" />
              </div>
              <span className="text-lg font-extrabold tracking-tight text-white whitespace-nowrap">
                iCanSee
              </span>
            </div>

            <div className="flex items-center gap-2">
              <button
                type="button"
                onClick={(e) => {
                  e.stopPropagation();
                  soundFX.vibrate(25);
                  setFacingMode((prev) => (prev === "environment" ? "user" : "environment"));
                }}
                aria-label="Switch between front and rear camera"
                className="min-h-[44px] px-3 rounded-xl bg-[#131B2E]/90 text-slate-200 border border-white/15 flex items-center gap-1.5 text-xs font-bold hover:bg-[#1E293B] active:scale-95 transition-all whitespace-nowrap shrink-0"
              >
                <SwitchCamera className="w-4 h-4 text-[#FACC15]" />
                <span>{facingMode === "environment" ? "Rear" : "Front"}</span>
              </button>

              <button
                type="button"
                onClick={(e) => {
                  e.stopPropagation();
                  soundFX.vibrate(25);
                  setIsMuted((prev) => {
                    const next = !prev;
                    if (next) stopAllAudioAndListening(false);
                    return next;
                  });
                }}
                aria-label={isMuted ? "Unmute assistant voice" : "Mute assistant voice"}
                className={`min-h-[44px] min-w-[44px] rounded-xl border flex items-center justify-center transition-all ${
                  isMuted
                    ? "bg-rose-500/20 border-rose-400 text-rose-300"
                    : "bg-[#131B2E]/90 border-white/15 text-[#FACC15]"
                }`}
              >
                {isMuted ? <VolumeX className="w-5 h-5" /> : <Volume2 className="w-5 h-5" />}
              </button>
            </div>
          </header>

          {/* UNIVERSAL TOUCH CANVAS — 1x TAP TO SPEAK/STOP, 2x DOUBLE-TAP FOR CAMERA SHORTCUT */}
          <div
            role="button"
            tabIndex={0}
            aria-live="assertive"
            aria-label={`${statusMessage}. ${subStatusText}.`}
            onClick={handleUniversalCanvasTap}
            onKeyDown={(e) => {
              if (e.key === "Enter" || e.key === " ") {
                e.preventDefault();
                triggerScreenGestureAction();
              }
            }}
            className="relative z-10 flex-1 flex flex-col justify-between p-5 pb-8 cursor-pointer focus:outline-none focus-visible:ring-4 focus-visible:ring-[#FACC15]"
          >
            {/* Touch Ripple Animations */}
            {ripples.map((r) => (
              <span
                key={r.id}
                style={{ left: r.x - 48, top: r.y - 48 }}
                className="pointer-events-none absolute w-24 h-24 rounded-full border-4 border-[#FACC15] bg-[#FACC15]/25 animate-ripple"
              />
            ))}

            {/* TOP STATUS BAR */}
            <div className="space-y-2">
              <div className="flex items-center justify-between text-xs font-mono text-slate-300 bg-[#131B2E]/90 backdrop-blur-md px-3.5 py-2.5 rounded-xl border border-white/10">
                <span className="flex items-center gap-1.5">
                  {cameraActive ? (
                    <>
                      <Camera className="w-3.5 h-3.5 text-[#34D399]" />
                      <span className="text-[#34D399] font-semibold">CAMERA READY</span>
                    </>
                  ) : (
                    <>
                      <CameraOff className="w-3.5 h-3.5 text-slate-400" />
                      <span>CAMERA OFF (STANDBY)</span>
                    </>
                  )}
                </span>
                <span>·</span>
                <span className="text-[#FACC15] font-semibold">
                  {phase === "IDLE" && "1x SPEAK · 2x CAMERA"}
                  {phase === "ASKING_INTENT" && "AI SPEAKING"}
                  {phase === "LISTENING_VOICE" && "LISTENING"}
                  {phase === "AIMING_CAMERA" && "TAP 1x TO CAPTURE"}
                  {phase === "CAPTURING_CAMERA" && "CAPTURING PHOTO"}
                  {phase === "PROCESSING_AI" && "PROCESSING AI"}
                  {phase === "SPEAKING_RESPONSE" && "AI SPEAKING"}
                </span>
              </div>

              {/* Microphone Permission Error Banner if blocked */}
              {micError && (
                <div className="bg-rose-950/90 border border-rose-400/50 text-rose-200 px-3.5 py-2.5 rounded-xl text-xs font-medium">
                  {micError}
                </div>
              )}
            </div>

            {/* ================= PURE CIRCULAR CENTER ORB WITH RADIAL GRADIENTS & SMOOTH WAVE RINGS ================= */}
            <div className="my-auto flex flex-col items-center text-center px-2">
              <div className="relative w-52 h-52 rounded-full flex items-center justify-center">
                {/* Layer 1: Smooth Continuous Circular Expanding Wave Rings */}
                <span
                  aria-hidden="true"
                  className={`pointer-events-none absolute w-36 h-36 rounded-full border ${orbTheme.waveBorder} ${orbTheme.waveBg} ${
                    isAiSpeaking ? "animate-speaking-wave-1" : "animate-orb-wave-1"
                  }`}
                />
                <span
                  aria-hidden="true"
                  className={`pointer-events-none absolute w-36 h-36 rounded-full border ${orbTheme.waveBorder} ${orbTheme.waveBg} ${
                    isAiSpeaking ? "animate-speaking-wave-2" : "animate-orb-wave-2"
                  }`}
                />
                <span
                  aria-hidden="true"
                  className={`pointer-events-none absolute w-36 h-36 rounded-full border ${orbTheme.waveBorder} ${
                    isAiSpeaking ? "animate-speaking-wave-3" : "animate-orb-wave-3"
                  }`}
                />

                {/* Layer 2: Pure Radial Circular Glowing Aura (Zero rectangular box edge) */}
                <span
                  aria-hidden="true"
                  style={{ background: orbTheme.auraStyle }}
                  className="pointer-events-none absolute w-48 h-48 rounded-full animate-orb-morph"
                />
                <span
                  aria-hidden="true"
                  style={{ background: orbTheme.auraStyle }}
                  className="pointer-events-none absolute w-40 h-40 rounded-full animate-orb-morph-reverse"
                />

                {/* Layer 3: Circular Glassmorphic Halo Rim */}
                <span
                  aria-hidden="true"
                  style={
                    phase === "LISTENING_VOICE"
                      ? {
                          transform: `scale(${1 + Math.min(0.42, micLevel / 140)})`,
                        }
                      : undefined
                  }
                  className="pointer-events-none absolute w-36 h-36 rounded-full bg-white/5 border border-white/25 transition-transform duration-100"
                />

                {/* Layer 4: Interactive Circular Core Sphere with Pure Radial Gradient */}
                <button
                  type="button"
                  onClick={handleCenterCircleClick}
                  aria-label={
                    phase === "AIMING_CAMERA"
                      ? "Tap once now to capture the picture"
                      : isProcessActive
                      ? "Tap once to stop AI, or double-tap to take a picture"
                      : "Tap once to speak with AI, or double-tap to take a picture"
                  }
                  style={{
                    background: orbTheme.coreStyle,
                    ...(phase === "LISTENING_VOICE"
                      ? {
                          transform: `scale(${1 + Math.min(0.26, micLevel / 190)})`,
                        }
                      : {}),
                  }}
                  className={`group relative z-10 w-32 h-32 rounded-full overflow-hidden flex flex-col items-center justify-center gap-1.5 transition-all duration-300 focus:outline-none focus-visible:ring-4 focus-visible:ring-white active:scale-95 border-2 border-white/60 ${
                    orbTheme.coreShadow
                  } ${orbTheme.textColor} ${
                    phase === "IDLE" ? "animate-orb-breathe" : ""
                  }`}
                >
                  {phase === "IDLE" && (
                    <>
                      <Hand className="relative z-10 w-10 h-10 stroke-[2.3] transition-transform group-hover:scale-110" />
                      <span className="relative z-10 text-[11px] font-extrabold uppercase tracking-widest">
                        Tap
                      </span>
                    </>
                  )}

                  {phase === "AIMING_CAMERA" && (
                    <>
                      <Camera className="relative z-10 w-10 h-10 stroke-[2.3] animate-pulse" />
                      <span className="relative z-10 flex items-center gap-1.5 text-[10px] font-extrabold uppercase tracking-wider bg-[#090D16]/20 px-2.5 py-0.5 rounded-full">
                        <span className="w-2 h-2 rounded-full bg-current" />
                        Tap 1x
                      </span>
                    </>
                  )}

                  {(phase === "ASKING_INTENT" || phase === "SPEAKING_RESPONSE") && (
                    <>
                      {/* Smooth Acoustic Wave Bars Inside Center Circle while AI Speaks */}
                      <div
                        aria-hidden="true"
                        className="relative z-10 flex items-center justify-center gap-1 h-9"
                      >
                        <span className="w-1.5 h-7 rounded-full bg-[#090D16] animate-voice-bar-1" />
                        <span className="w-1.5 h-8 rounded-full bg-[#090D16] animate-voice-bar-2" />
                        <span className="w-1.5 h-9 rounded-full bg-[#090D16] animate-voice-bar-3" />
                        <span className="w-1.5 h-8 rounded-full bg-[#090D16] animate-voice-bar-4" />
                        <span className="w-1.5 h-7 rounded-full bg-[#090D16] animate-voice-bar-5" />
                      </div>
                      <span className="relative z-10 flex items-center gap-1.5 text-[10px] font-extrabold uppercase tracking-wider bg-[#090D16]/20 px-2.5 py-0.5 rounded-full">
                        <span className="w-2 h-2 rounded-full bg-current" />
                        Stop AI
                      </span>
                    </>
                  )}

                  {phase === "LISTENING_VOICE" && (
                    <>
                      <Mic className="relative z-10 w-10 h-10 stroke-[2.3]" />
                      <span className="relative z-10 flex items-center gap-1.5 text-[10px] font-extrabold uppercase tracking-wider bg-[#090D16]/20 px-2.5 py-0.5 rounded-full">
                        <span className="w-2 h-2 rounded-full bg-current" />
                        Stop
                      </span>
                    </>
                  )}

                  {phase === "CAPTURING_CAMERA" && (
                    <>
                      <Camera className="relative z-10 w-10 h-10 stroke-[2.3]" />
                      <span className="relative z-10 flex items-center gap-1.5 text-[10px] font-extrabold uppercase tracking-wider bg-[#090D16]/20 px-2.5 py-0.5 rounded-full">
                        <span className="w-2 h-2 rounded-full bg-current" />
                        Stop
                      </span>
                    </>
                  )}

                  {phase === "PROCESSING_AI" && (
                    <>
                      <Sparkles className="relative z-10 w-10 h-10 stroke-[2.3] animate-spin" />
                      <span className="relative z-10 flex items-center gap-1.5 text-[10px] font-extrabold uppercase tracking-wider bg-[#090D16]/20 px-2.5 py-0.5 rounded-full">
                        <span className="w-2 h-2 rounded-full bg-current" />
                        Stop
                      </span>
                    </>
                  )}
                </button>
              </div>

              {/* Live Audio Equalizer Bar when User Microphone is Listening */}
              {phase === "LISTENING_VOICE" && (
                <div className="mt-2 flex items-center justify-center gap-1.5 h-6">
                  {[0.5, 0.85, 1.2, 1.0, 0.65].map((mult, idx) => {
                    const barHeight = Math.max(
                      6,
                      Math.min(24, Math.round((micLevel * mult) / 3.5))
                    );
                    return (
                      <span
                        key={idx}
                        style={{ height: `${barHeight}px` }}
                        className="w-1.5 rounded-full bg-[#38BDF8] transition-all duration-75"
                      />
                    );
                  })}
                </div>
              )}

              <h1
                className="mt-4 text-2xl sm:text-[26px] font-extrabold tracking-tight text-white leading-snug"
                style={{ textWrap: "balance" }}
              >
                {statusMessage}
              </h1>

              {/* Highlight captured voice text clearly in a smooth pill/rounded container */}
              {liveTranscript ? (
                <div className="mt-3 px-5 py-3 rounded-full bg-[#131B2E] border-2 border-[#38BDF8] max-w-sm w-full">
                  <p className="text-[11px] font-mono text-[#38BDF8] uppercase tracking-wider">
                    Voice Captured (Sending to AI...)
                  </p>
                  <p className="mt-0.5 text-base font-bold text-white leading-snug truncate">
                    &ldquo;{liveTranscript}&rdquo;
                  </p>
                </div>
              ) : (
                <p className="mt-2.5 text-base font-medium text-slate-200 leading-relaxed max-w-sm line-clamp-4">
                  {subStatusText}
                </p>
              )}
            </div>

            {/* Quiet Bottom Instruction Caption */}
            <div className="text-center pt-2">
              <p className="text-xs font-mono text-[#FACC15] tracking-wide">
                {phase === "IDLE"
                  ? "TAP 1x FOR VOICE · DOUBLE-TAP (2x) TO TAKE PICTURE"
                  : phase === "AIMING_CAMERA"
                  ? "TAP 1x ANYWHERE ON SCREEN WHEN READY TO CAPTURE"
                  : "TAP 1x TO STOP AI · DOUBLE-TAP (2x) TO TAKE PICTURE"}
              </p>
            </div>
          </div>
        </section>

        {/* ================= DESKTOP COMPANION PANEL (HIDDEN ON MOBILE PHONES, VISIBLE ON LG DESKTOP) ================= */}
        <aside className="hidden lg:flex flex-1 flex-col justify-between bg-[#131B2E] rounded-[36px] border border-white/10 p-8 max-h-[840px] overflow-y-auto">
          <div className="space-y-6">
            {/* Brand & Accessibility Architecture Header */}
            <div className="border-b border-white/10 pb-5">
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-3">
                  <Headphones className="w-6 h-6 text-[#FACC15]" />
                  <h2 className="text-xl font-extrabold tracking-tight text-white">
                    iCanSee — Voice &amp; Double-Tap Camera Assistant
                  </h2>
                </div>
                <span className="text-xs font-mono text-slate-400">
                  1x Tap Voice · 2x Tap Camera
                </span>
              </div>
              <p className="mt-2 text-sm text-slate-300 leading-relaxed">
                Designed for blind and low-vision users. <strong>Single-tap (1x)</strong> anywhere to wake the assistant with a local <strong>&ldquo;Hi there.&rdquo;</strong> for voice Q&amp;A. <strong>Double-tap (2x)</strong> anywhere as a shortcut to take a picture: the assistant announces <em>&ldquo;Point your camera at the object you want to capture. Then, tap the screen once when you&apos;re ready.&rdquo;</em> Once you tap once, it snaps the photo and AI processes the image.
              </p>
            </div>

            {/* Interactive Flow Steps */}
            <div className="grid grid-cols-3 gap-4">
              <div className="p-4 rounded-2xl bg-[#090D16]/70 border border-white/10">
                <div className="text-xs font-mono text-[#38BDF8]">01. Single-Tap (1x)</div>
                <div className="mt-1 font-bold text-sm text-white">Voice Q&amp;A (&ldquo;Hi there.&rdquo;)</div>
                <p className="mt-1 text-xs text-slate-400 leading-relaxed">
                  Tap once anywhere on the screen to hear &ldquo;Hi there.&rdquo; and ask the AI any question by voice.
                </p>
              </div>
              <div className="p-4 rounded-2xl bg-[#090D16]/70 border border-[#FACC15]/30">
                <div className="text-xs font-mono text-[#FACC15]">02. Double-Tap (2x)</div>
                <div className="mt-1 font-bold text-sm text-white">Point Camera Prompt</div>
                <p className="mt-1 text-xs text-slate-400 leading-relaxed">
                  Double-tap anywhere to activate camera aiming with the voice guide: &ldquo;Point your camera... Then, tap once when ready.&rdquo;
                </p>
              </div>
              <div className="p-4 rounded-2xl bg-[#090D16]/70 border border-white/10">
                <div className="text-xs font-mono text-[#34D399]">03. Tap Once to Capture</div>
                <div className="mt-1 font-bold text-sm text-white">Snap &amp; Process Image</div>
                <p className="mt-1 text-xs text-slate-400 leading-relaxed">
                  Tap once when ready: the shutter clicks, announces &ldquo;Processing image&rdquo;, and AI describes what is in front of you.
                </p>
              </div>
            </div>

            {/* Quick Voice & Gesture Simulation Triggers for Testing */}
            <div className="space-y-3">
              <div className="flex items-center justify-between">
                <h3 className="text-sm font-bold text-slate-200">
                  Simulate User Gestures &amp; Voice Commands
                </h3>
                <span className="text-xs font-mono text-slate-400">
                  Test 2x Camera Shortcut &amp; Voice Q&amp;A
                </span>
              </div>

              <div className="grid grid-cols-2 gap-2.5">
                <button
                  type="button"
                  onClick={() => startCameraAimingShortcut()}
                  className="text-left p-3.5 rounded-xl bg-[#090D16] hover:bg-[#1E293B] border border-[#FACC15]/40 transition-colors flex items-start gap-3"
                >
                  <Camera className="w-5 h-5 text-[#FACC15] shrink-0 mt-0.5" />
                  <div>
                    <div className="text-xs font-bold text-[#FACC15]">
                      Simulate Double-Tap (2x Click) Shortcut
                    </div>
                    <div className="text-[11px] text-slate-400 mt-0.5">
                      Plays &ldquo;Point your camera... Then, tap once when ready&rdquo;
                    </div>
                  </div>
                </button>

                <button
                  type="button"
                  onClick={() =>
                    handleQuickPrompt(
                      "Please take a picture right now and describe what objects are in front of me.",
                      true
                    )
                  }
                  className="text-left p-3.5 rounded-xl bg-[#090D16] hover:bg-[#1E293B] border border-[#34D399]/30 transition-colors flex items-start gap-3"
                >
                  <CheckCircle2 className="w-5 h-5 text-[#34D399] shrink-0 mt-0.5" />
                  <div>
                    <div className="text-xs font-bold text-[#34D399]">
                      Snap &amp; Process Image Immediately
                    </div>
                    <div className="text-[11px] text-slate-400 mt-0.5">
                      Captures photo, says &ldquo;Processing image&rdquo; &amp; describes scene
                    </div>
                  </div>
                </button>

                <button
                  type="button"
                  onClick={() =>
                    handleQuickPrompt(
                      "Hello iCanSee, what time is it and how can you assist me today?",
                      false
                    )
                  }
                  className="text-left p-3.5 rounded-xl bg-[#090D16] hover:bg-[#1E293B] border border-white/10 transition-colors flex items-start gap-3"
                >
                  <MessageSquare className="w-5 h-5 text-[#38BDF8] shrink-0 mt-0.5" />
                  <div>
                    <div className="text-xs font-bold text-white">
                      &ldquo;Hello, how can you assist me?&rdquo;
                    </div>
                    <div className="text-[11px] text-slate-400 mt-0.5">
                      Voice Q&amp;A (Camera remains off)
                    </div>
                  </div>
                </button>

                <button
                  type="button"
                  onClick={() =>
                    handleQuickPrompt(
                      "What are some helpful tips for organizing daily medications for a blind person?",
                      false
                    )
                  }
                  className="text-left p-3.5 rounded-xl bg-[#090D16] hover:bg-[#1E293B] border border-white/10 transition-colors flex items-start gap-3"
                >
                  <Radio className="w-5 h-5 text-[#38BDF8] shrink-0 mt-0.5" />
                  <div>
                    <div className="text-xs font-bold text-white">
                      &ldquo;Tips for organizing medications?&rdquo;
                    </div>
                    <div className="text-[11px] text-slate-400 mt-0.5">
                      Voice Q&amp;A (Camera remains off)
                    </div>
                  </div>
                </button>
              </div>

              {/* Custom Question Input */}
              <form onSubmit={handleTextFallbackSubmit} className="flex gap-2 pt-1">
                <input
                  type="text"
                  value={textFallbackInput}
                  onChange={(e) => setTextFallbackInput(e.target.value)}
                  placeholder='Type a spoken command (e.g. "How are you?" or "Take a picture")...'
                  className="flex-1 bg-[#090D16] border border-white/15 rounded-xl px-4 py-2.5 text-sm text-white placeholder:text-slate-500 focus:outline-none focus:border-[#FACC15]"
                />
                <button
                  type="submit"
                  className="px-4 py-2.5 rounded-xl bg-[#FACC15] text-[#090D16] font-bold text-xs whitespace-nowrap hover:bg-amber-300 transition-colors"
                >
                  Send Command
                </button>
              </form>
            </div>

            {/* Recent Voice & Vision Log */}
            <div className="space-y-3 pt-2 border-t border-white/10">
              <div className="flex items-center justify-between">
                <h3 className="text-sm font-bold text-slate-200">
                  Conversation &amp; Camera History
                </h3>
                <span className="text-xs font-mono text-slate-400">
                  {logs.length} interactions
                </span>
              </div>

              {logs.length === 0 ? (
                <div className="p-6 rounded-2xl bg-[#090D16]/50 border border-white/5 text-center text-sm text-slate-400">
                  No interactions yet. Tap once to speak, or double-tap (2x) on the mobile screen to take a picture.
                </div>
              ) : (
                <div className="space-y-3 max-h-[220px] overflow-y-auto pr-1">
                  {logs.map((item) => (
                    <div
                      key={item.id}
                      className="p-4 rounded-2xl bg-[#090D16] border border-white/10 flex gap-4 items-start"
                    >
                      {item.snapshotDataUrl && item.intentUsed === "camera" && (
                        <img
                          src={item.snapshotDataUrl}
                          alt={item.summaryTitle}
                          referrerPolicy="no-referrer"
                          className="w-16 h-16 rounded-xl object-cover shrink-0 border border-white/15"
                        />
                      )}
                      <div className="flex-1 min-w-0">
                        <div className="flex items-center gap-2 text-xs font-mono text-slate-400">
                          <span
                            className={`font-semibold ${
                              item.intentUsed === "camera" ? "text-[#FACC15]" : "text-[#38BDF8]"
                            }`}
                          >
                            {item.intentUsed === "camera" ? "CAMERA PHOTO" : "VOICE Q&A"}
                          </span>
                          <span>·</span>
                          <span>{item.timestamp}</span>
                        </div>
                        <p className="text-xs text-slate-300 mt-1 truncate">
                          You: &ldquo;{item.userTranscript}&rdquo;
                        </p>
                        <p className="text-sm font-semibold text-white mt-1 leading-snug">
                          {item.spokenResponse}
                        </p>
                      </div>
                      <button
                        type="button"
                        onClick={() => {
                          setPhase("SPEAKING_RESPONSE");
                          setStatusMessage(item.summaryTitle);
                          setSubStatusText(item.spokenResponse);
                          playSpokenAudio(item.spokenResponse, null, () => {
                            if (phaseRef.current === "SPEAKING_RESPONSE") {
                              setPhase("IDLE");
                            }
                          });
                        }}
                        aria-label="Replay this spoken response"
                        className="min-h-[40px] min-w-[40px] rounded-xl bg-[#131B2E] hover:bg-[#1E293B] text-[#FACC15] flex items-center justify-center shrink-0"
                      >
                        <Volume2 className="w-4 h-4" />
                      </button>
                    </div>
                  ))}
                </div>
              )}
            </div>
          </div>
        </aside>
      </main>
    </div>
  );
}
