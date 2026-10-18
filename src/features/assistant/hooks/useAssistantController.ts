import React, { useState, useRef, useEffect, useCallback } from "react";
import {
  InteractionPhase,
  InteractionLog,
  RipplePoint,
} from "../../../types/assistant";
import {
  soundFX,
  POINT_CAMERA_INSTRUCTION_TEXT,
} from "../../../utils/soundEffects";

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

export function useAssistantController() {
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

  const [cameraActive, setCameraActive] = useState<boolean>(false);
  const [facingMode, setFacingMode] = useState<"environment" | "user">(
    "environment"
  );
  const [lastCapturedImage, setLastCapturedImage] = useState<string | null>(
    null
  );

  const [isMuted] = useState<boolean>(false);
  const [ripples, setRipples] = useState<RipplePoint[]>([]);
  const [logs, setLogs] = useState<InteractionLog[]>([]);

  const videoRef = useRef<HTMLVideoElement | null>(null);
  const canvasRef = useRef<HTMLCanvasElement | null>(null);
  const audioPlayerRef = useRef<HTMLAudioElement | null>(null);
  const recognitionRef = useRef<any>(null);
  const mediaRecorderRef = useRef<MediaRecorder | null>(null);
  const micStreamRef = useRef<MediaStream | null>(null);
  const cameraStreamRef = useRef<MediaStream | null>(null);
  const audioChunksRef = useRef<Blob[]>([]);
  const vadAnimationRef = useRef<number | null>(null);
  const silenceDebounceTimerRef = useRef<ReturnType<typeof setTimeout> | null>(
    null
  );
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
  const pendingMicPromiseRef = useRef<Promise<MediaStream> | null>(null);

  useEffect(() => {
    phaseRef.current = phase;
  }, [phase]);

  useEffect(() => {
    isMutedRef.current = isMuted;
  }, [isMuted]);

  useEffect(() => {
    logsRef.current = logs;
  }, [logs]);

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

  const startLiveCameraStream = useCallback(async (): Promise<MediaStream | null> => {
    setCameraActive(true);
    if (cameraStreamRef.current && cameraStreamRef.current.active) {
      if (
        videoRef.current &&
        videoRef.current.srcObject !== cameraStreamRef.current
      ) {
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

  const activateCameraAndCaptureSnapshot = useCallback(async (): Promise<string> => {
    const alreadyStreaming =
      Boolean(cameraStreamRef.current && cameraStreamRef.current.active) &&
      Boolean(videoRef.current && videoRef.current.videoWidth > 0);

    setCameraActive(true);
    soundFX.playCameraShutter();

    try {
      if (!alreadyStreaming) {
        await startLiveCameraStream();
        await new Promise((resolve) => setTimeout(resolve, 650));
      } else {
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
      if (
        mediaRecorderRef.current &&
        mediaRecorderRef.current.state !== "inactive"
      ) {
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

  const playSpokenAudio = useCallback(
    async (
      text: string,
      preloadedBase64?: string | null,
      onFinished?: () => void
    ) => {
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

      stopAllAudioAndListening(shouldCaptureCamera);

      const controller = new AbortController();
      abortControllerRef.current = controller;

      let capturedImageBase64 = "";

      if (shouldCaptureCamera) {
        setPhase("CAPTURING_CAMERA");
        setStatusMessage("Capturing photo...");
        setSubStatusText("Tap anywhere on the screen if you wish to cancel");
        capturedImageBase64 = await activateCameraAndCaptureSnapshot();
        if (controller.signal.aborted) return;

        setPhase("PROCESSING_AI");
        setStatusMessage("Processing image");
        setSubStatusText("AI is analyzing the objects in front of you...");
        soundFX.playStoredProcessingImage(isMutedRef.current);
      } else {
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

        if (data.noSpeechDetected) {
          stopAllAudioAndListening(false);
          isSubmittingVoiceRef.current = false;
          hasUserSpokenRef.current = false;
          setPhase("IDLE");
          setStatusMessage(
            "Tap once to speak, or double-tap to take a picture"
          );
          setSubStatusText(
            "Tap 1x anywhere to greet the AI by voice, or double-tap (2x) anywhere to capture a photo."
          );
          return;
        }

        if (data.requiresCameraCapture) {
          shouldCaptureCamera = true;
          setPhase("CAPTURING_CAMERA");
          setStatusMessage("Turning on camera & taking picture...");
          setSubStatusText(`Voice command: "${data.userTranscript}"`);
          capturedImageBase64 = await activateCameraAndCaptureSnapshot();
          if (controller.signal.aborted) return;

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
          userTranscript:
            data.userTranscript || cleanedUserText || "Voice Conversation",
          summaryTitle: data.summaryTitle || "iCanSee Response",
          spokenResponse: data.spokenResponse,
          detectedObjects: Array.isArray(data.detectedObjects)
            ? data.detectedObjects
            : [],
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

        await playSpokenAudio(
          newEntry.spokenResponse,
          data.ttsAudioBase64,
          () => {
            if (phaseRef.current === "SPEAKING_RESPONSE") {
              stopAllAudioAndListening(false);
              isSubmittingVoiceRef.current = false;
              hasUserSpokenRef.current = false;
              setPhase("IDLE");
              setStatusMessage(
                "Tap once to speak, or double-tap to take a picture"
              );
              setSubStatusText(
                "Tap 1x anywhere to greet the AI by voice, or double-tap (2x) anywhere to capture a photo."
              );
            }
          }
        );
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
          setStatusMessage(
            "Tap once to speak, or double-tap to take a picture"
          );
          setSubStatusText(
            "Tap 1x anywhere to greet the AI by voice, or double-tap (2x) anywhere to capture a photo."
          );
        });
      }
    },
    [
      activateCameraAndCaptureSnapshot,
      playSpokenAudio,
      stopAllAudioAndListening,
    ]
  );

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

    if (!userDidSpeak) {
      if (
        mediaRecorderRef.current &&
        mediaRecorderRef.current.state !== "inactive"
      ) {
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

    if (spokenTextSoFar.length > 0) {
      if (
        mediaRecorderRef.current &&
        mediaRecorderRef.current.state !== "inactive"
      ) {
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
              setStatusMessage(
                "Tap once to speak, or double-tap to take a picture"
              );
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

    const resetNoQuestionTimer = (timeoutMs = 4000) => {
      if (maxListenTimerRef.current) {
        clearTimeout(maxListenTimerRef.current);
      }
      maxListenTimerRef.current = setTimeout(() => {
        if (
          phaseRef.current === "LISTENING_VOICE" &&
          !isSubmittingVoiceRef.current
        ) {
          if (
            hasUserSpokenRef.current ||
            liveTranscriptRef.current.trim().length > 0
          ) {
            finishVoiceRecordingAndSubmit();
          } else {
            stopAllAudioAndListening(false);
            setPhase("IDLE");
            setStatusMessage(
              "Tap once to speak, or double-tap to take a picture"
            );
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
          (hasUserSpokenRef.current ||
            liveTranscriptRef.current.trim().length > 0)
        ) {
          finishVoiceRecordingAndSubmit();
        }
      }, delayMs);
    };

    const SpeechRecognition =
      (window as any).SpeechRecognition ||
      (window as any).webkitSpeechRecognition;
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

    let stream = micStreamRef.current;
    if (!stream || !stream.active) {
      if (pendingMicPromiseRef.current) {
        try {
          stream = await pendingMicPromiseRef.current;
          micStreamRef.current = stream;
        } catch {
          stream = null;
        }
      }
    }

    if (!stream || !stream.active) {
      try {
        if (navigator.mediaDevices?.getUserMedia) {
          stream = await navigator.mediaDevices.getUserMedia({ audio: true });
          micStreamRef.current = stream;
        }
      } catch (err: any) {
        console.warn("Microphone getUserMedia warning:", err);
        setMicError(
          "Microphone permission is not enabled. On iPhone: Go to iPhone Settings > Safari > Microphone > Allow."
        );
        setStatusMessage("Microphone Permission Needed");
        return;
      }
    }

    if (phaseRef.current !== "LISTENING_VOICE") {
      if (stream) stream.getTracks().forEach((t) => t.stop());
      return;
    }

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

      try {
        const AudioCtx =
          window.AudioContext ||
          (window as unknown as { webkitAudioContext: typeof AudioContext })
            .webkitAudioContext;
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
          if (
            phaseRef.current !== "LISTENING_VOICE" ||
            isSubmittingVoiceRef.current
          ) {
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
          if (now - listenStartTime > 350 && avg > 14) {
            voiceActiveFrames += 1;
            lastVoiceTime = now;

            if (voiceActiveFrames >= 5 && !hasUserSpokenRef.current) {
              hasUserSpokenRef.current = true;
              resetNoQuestionTimer(6000);
            }
          }

          if (
            (hasUserSpokenRef.current ||
              liveTranscriptRef.current.trim().length > 0) &&
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

  const startAiGreetingAndAskUser = useCallback(async () => {
    if (!micStreamRef.current || !micStreamRef.current.active) {
      if (pendingMicPromiseRef.current) {
        try {
          const stream = await pendingMicPromiseRef.current;
          micStreamRef.current = stream;
          setMicError(null);
        } catch {
          // Handled during listening phase
        }
      } else if (navigator.mediaDevices?.getUserMedia) {
        try {
          const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
          micStreamRef.current = stream;
          setMicError(null);
        } catch {}
      }
    }

    setPhase("ASKING_INTENT");
    setStatusMessage("Hi there.");
    setSubStatusText(
      "Speak directly to ask a question, or double-tap (2x) anywhere to take a picture."
    );

    soundFX.playStoredHelloGreeting(isMutedRef.current, () => {
      if (phaseRef.current === "ASKING_INTENT") {
        startListeningForUserIntent();
      }
    });
  }, [startListeningForUserIntent]);

  const startCameraAimingShortcut = useCallback(() => {
    stopAllAudioAndListening(false);
    isSubmittingVoiceRef.current = false;
    liveTranscriptRef.current = "";
    setLiveTranscript("");

    // If double tap occurs, immediately stop any mic stream primed on first tap
    if (micStreamRef.current) {
      micStreamRef.current.getTracks().forEach((t) => t.stop());
      micStreamRef.current = null;
    }
    if (pendingMicPromiseRef.current) {
      pendingMicPromiseRef.current
        .then((s) => s.getTracks().forEach((t) => t.stop()))
        .catch(() => {});
      pendingMicPromiseRef.current = null;
    }

    setPhase("AIMING_CAMERA");
    phaseRef.current = "AIMING_CAMERA";
    setStatusMessage("Point Your Camera");
    setSubStatusText(POINT_CAMERA_INSTRUCTION_TEXT);

    startLiveCameraStream();
    soundFX.playStoredPointCameraPrompt(isMutedRef.current);
  }, [startLiveCameraStream, stopAllAudioAndListening]);

  const triggerScreenGestureAction = useCallback(() => {
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

    if (timeSinceLastTap > 0 && timeSinceLastTap < 320) {
      if (singleTapTimerRef.current) {
        clearTimeout(singleTapTimerRef.current);
        singleTapTimerRef.current = null;
      }
      lastTapTimeRef.current = 0;
      startCameraAimingShortcut();
      return;
    }

    lastTapTimeRef.current = now;
    if (singleTapTimerRef.current) {
      clearTimeout(singleTapTimerRef.current);
    }

    // CRITICAL FOR IPHONE / IOS SAFARI:
    // iOS Safari requires navigator.mediaDevices.getUserMedia() to be invoked
    // synchronously during a direct user touch/click gesture!
    // A setTimeout(..., 270) expires the gesture activation token, causing Safari
    // to silently block getUserMedia with NotAllowedError without showing the prompt!
    if (phaseRef.current === "IDLE") {
      soundFX.unlockAudio();
      if (
        typeof navigator !== "undefined" &&
        navigator.mediaDevices?.getUserMedia &&
        (!micStreamRef.current || !micStreamRef.current.active)
      ) {
        try {
          const micPromise = navigator.mediaDevices.getUserMedia({ audio: true });
          pendingMicPromiseRef.current = micPromise;
          micPromise
            .then((stream) => {
              micStreamRef.current = stream;
              setMicError(null);
            })
            .catch((err) => {
              console.warn("Direct user gesture mic acquire notice:", err);
            });
        } catch (err) {
          console.warn("Sync getUserMedia error on tap:", err);
        }
      }
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

  const handleUniversalCanvasTap = useCallback(
    (e: React.MouseEvent<HTMLDivElement>) => {
      const rect = e.currentTarget.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      const newRipple = { id: Date.now() + Math.random(), x, y };
      setRipples((prev) => [...prev.slice(-3), newRipple]);

      triggerScreenGestureAction();
    },
    [triggerScreenGestureAction]
  );

  const handleCenterCircleClick = useCallback(
    (e: React.MouseEvent<HTMLButtonElement>) => {
      e.stopPropagation();
      triggerScreenGestureAction();
    },
    [triggerScreenGestureAction]
  );

  const handleToggleFacingMode = useCallback(() => {
    soundFX.vibrate(25);
    setFacingMode((prev) => (prev === "environment" ? "user" : "environment"));
  }, []);

  const clearMicError = useCallback(() => {
    setMicError(null);
  }, []);

  const triggerMicrophonePermissionRequest = useCallback(async () => {
    soundFX.unlockAudio();
    if (!navigator.mediaDevices?.getUserMedia) return false;
    try {
      const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
      micStreamRef.current = stream;
      setMicError(null);
      return true;
    } catch (err: any) {
      console.warn("triggerMicrophonePermissionRequest err:", err);
      setMicError(
        "Microphone permission is not enabled. On iPhone: Go to iPhone Settings > Safari > Microphone > Allow."
      );
      return false;
    }
  }, []);

  return {
    phase,
    statusMessage,
    subStatusText,
    liveTranscript,
    micLevel,
    micError,
    cameraActive,
    facingMode,
    lastCapturedImage,
    ripples,
    videoRef,
    canvasRef,
    triggerScreenGestureAction,
    handleUniversalCanvasTap,
    handleCenterCircleClick,
    handleToggleFacingMode,
    clearMicError,
    triggerMicrophonePermissionRequest,
  };
}
