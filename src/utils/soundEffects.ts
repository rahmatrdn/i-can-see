// Local Stored Audio Files (/audio/*.mp3), Realistic Camera Shutter, & Haptic Utility

export const POINT_CAMERA_INSTRUCTION_TEXT =
  "Point your camera at the object you want to capture. Then, tap the screen once when you're ready.";

/**
 * Generates a realistic two-part mechanical SLR/Smartphone Camera Shutter WAV in memory.
 */
function createCameraShutterWavUrl(): string {
  if (typeof window === "undefined") return "";
  const sampleRate = 44100;
  const durationSeconds = 0.26;
  const numSamples = Math.floor(sampleRate * durationSeconds);
  const buffer = new ArrayBuffer(44 + numSamples * 2);
  const view = new DataView(buffer);

  const writeStr = (offset: number, str: string) => {
    for (let i = 0; i < str.length; i++) {
      view.setUint8(offset + i, str.charCodeAt(i));
    }
  };

  writeStr(0, "RIFF");
  view.setUint32(4, 36 + numSamples * 2, true);
  writeStr(8, "WAVE");
  writeStr(12, "fmt ");
  view.setUint32(16, 16, true);
  view.setUint16(20, 1, true);
  view.setUint16(22, 1, true);
  view.setUint32(24, sampleRate, true);
  view.setUint32(28, sampleRate * 2, true);
  view.setUint16(32, 2, true);
  view.setUint16(34, 16, true);
  writeStr(36, "data");
  view.setUint32(40, numSamples * 2, true);

  let prevNoise = 0;

  for (let i = 0; i < numSamples; i++) {
    const t = i / sampleRate;
    const white = Math.random() * 2 - 1;
    const crispNoise = white - 0.65 * prevNoise;
    prevNoise = white;

    let signal = 0;

    // 1st click: first curtain snap
    if (t >= 0.005 && t < 0.06) {
      const dt1 = t - 0.005;
      const env1 = Math.exp(-dt1 * 130);
      const click1 =
        0.65 * crispNoise +
        0.35 * Math.sin(2 * Math.PI * 1950 * dt1) +
        0.25 * Math.sin(2 * Math.PI * 820 * dt1);
      signal += click1 * env1 * 0.95;
    }

    // Mechanical slide friction
    if (t >= 0.03 && t < 0.068) {
      const dtSlide = t - 0.03;
      const slideEnv = Math.sin((dtSlide / 0.038) * Math.PI) * 0.22;
      signal += crispNoise * slideEnv;
    }

    // 2nd click: shutter close + crisp metallic ring
    if (t >= 0.068) {
      const dt2 = t - 0.068;
      const snapEnv2 = Math.exp(-dt2 * 95);
      const ringEnv2 = Math.exp(-dt2 * 38);
      const click2 =
        0.7 * crispNoise * snapEnv2 +
        0.4 * Math.sin(2 * Math.PI * 2650 * dt2) * snapEnv2 +
        0.28 * Math.sin(2 * Math.PI * 1380 * dt2) * ringEnv2 +
        0.15 * Math.sin(2 * Math.PI * 3900 * dt2) * ringEnv2;
      signal += click2 * 1.0;
    }

    const clamped = Math.max(-1, Math.min(1, signal));
    view.setInt16(44 + i * 2, clamped * 32767, true);
  }

  const blob = new Blob([buffer], { type: "audio/wav" });
  return URL.createObjectURL(blob);
}

class SoundAndHaptics {
  private ctx: AudioContext | null = null;
  private storedShutterWavUrl: string | null = null;
  private shutterAudioElement: HTMLAudioElement | null = null;

  // Preloaded static audio file players (/public/audio/*.mp3)
  private pointCameraAudio: HTMLAudioElement | null = null;
  private helloAudio: HTMLAudioElement | null = null;
  private processingAnswerAudio: HTMLAudioElement | null = null;
  private processingImageAudio: HTMLAudioElement | null = null;
  private activeVoiceAudio: HTMLAudioElement | null = null;

  constructor() {
    if (typeof window !== "undefined") {
      this.storedShutterWavUrl = createCameraShutterWavUrl();

      // Preload static MP3 audio files from /public/audio/
      this.pointCameraAudio = new Audio("/audio/point-camera.mp3?v=3");
      this.pointCameraAudio.preload = "auto";

      this.helloAudio = new Audio("/audio/hello.mp3?v=3");
      this.helloAudio.preload = "auto";

      this.processingAnswerAudio = new Audio("/audio/processing-answer.mp3?v=3");
      this.processingAnswerAudio.preload = "auto";

      this.processingImageAudio = new Audio("/audio/processing-image.mp3?v=3");
      this.processingImageAudio.preload = "auto";
    }
  }

  private getContext(): AudioContext | null {
    if (typeof window === "undefined") return null;
    if (!this.ctx) {
      const AudioCtx =
        window.AudioContext ||
        (window as unknown as { webkitAudioContext: typeof AudioContext }).webkitAudioContext;
      if (AudioCtx) {
        this.ctx = new AudioCtx();
      }
    }
    if (this.ctx && this.ctx.state === "suspended") {
      this.ctx.resume().catch(() => {});
    }
    return this.ctx;
  }

  public vibrate(pattern: number | number[]) {
    try {
      if (typeof navigator !== "undefined" && navigator.vibrate) {
        navigator.vibrate(pattern);
      }
    } catch {
      // Ignore on unsupported devices
    }
  }

  /**
   * Plays a preloaded static MP3 audio file from /public/audio/*.mp3 directly.
   */
  private playStaticAudioFile(
    audioEl: HTMLAudioElement | null,
    srcPath: string,
    isMuted: boolean,
    onFinished?: () => void
  ) {
    if (isMuted) {
      if (onFinished) setTimeout(onFinished, 200);
      return;
    }

    this.stopStoredGreeting();

    let completed = false;
    const finishOnce = () => {
      if (completed) return;
      completed = true;
      if (onFinished) onFinished();
    };

    try {
      const player = audioEl || new Audio(srcPath);
      this.activeVoiceAudio = player;
      player.currentTime = 0;
      player.volume = 1.0;

      player.onended = () => {
        finishOnce();
      };
      player.onerror = () => {
        finishOnce();
      };

      const playPromise = player.play();
      if (playPromise !== undefined) {
        playPromise.catch(() => {
          finishOnce();
        });
      }
    } catch {
      finishOnce();
    }
  }

  /**
   * Plays the exact stored audio file (/audio/point-camera.mp3) when user double-taps (2x click) anywhere:
   * "Point your camera at the object you want to capture. Then, tap the screen once when you're ready."
   */
  public playStoredPointCameraPrompt(isMuted: boolean, onFinished?: () => void) {
    this.vibrate([35, 35, 65]);
    this.playStaticAudioFile(
      this.pointCameraAudio,
      "/audio/point-camera.mp3?v=3",
      isMuted,
      onFinished
    );
  }

  /**
   * Plays stored/local "/audio/hello.mp3" ("Hi there.") audio file immediately.
   */
  public playStoredHelloGreeting(isMuted: boolean, onFinished: () => void) {
    this.vibrate([40, 30, 60]);
    this.playStaticAudioFile(
      this.helloAudio,
      "/audio/hello.mp3?v=3",
      isMuted,
      onFinished
    );
  }

  /**
   * Plays stored/local "/audio/processing-answer.mp3" ("Processing answer.") audio file immediately.
   */
  public playStoredProcessingAnswer(isMuted: boolean, onFinished?: () => void) {
    this.playStaticAudioFile(
      this.processingAnswerAudio,
      "/audio/processing-answer.mp3?v=3",
      isMuted,
      onFinished
    );
  }

  /**
   * Plays stored/local "/audio/processing-image.mp3" ("Processing image") audio file immediately.
   */
  public playStoredProcessingImage(isMuted: boolean, onFinished?: () => void) {
    this.playStaticAudioFile(
      this.processingImageAudio,
      "/audio/processing-image.mp3",
      isMuted,
      onFinished
    );
  }

  public stopStoredGreeting() {
    if (this.activeVoiceAudio) {
      try {
        this.activeVoiceAudio.onended = null;
        this.activeVoiceAudio.onerror = null;
        this.activeVoiceAudio.pause();
        this.activeVoiceAudio.currentTime = 0;
      } catch {
        // ignore
      }
      this.activeVoiceAudio = null;
    }
    if (typeof window !== "undefined" && "speechSynthesis" in window) {
      window.speechSynthesis.cancel();
    }
  }

  // Descending double-tone when user clicks to STOP AI speaking & halt process
  public playStopChime() {
    this.vibrate([60, 40, 80]);
    const ctx = this.getContext();
    if (!ctx) return;

    const now = ctx.currentTime;
    this.playTone(ctx, 587.33, now, 0.08, "sine", 0.16); // D5
    this.playTone(ctx, 392.0, now + 0.08, 0.15, "sine", 0.18); // G4
  }

  // Soft high chime when microphone starts listening
  public playListeningStart() {
    this.vibrate(50);
    const ctx = this.getContext();
    if (!ctx) return;

    const now = ctx.currentTime;
    this.playTone(ctx, 587.33, now, 0.08, "sine", 0.14); // D5
    this.playTone(ctx, 880.0, now + 0.08, 0.15, "sine", 0.16); // A5
  }

  /**
   * Authentic two-part mechanical camera shutter sound when taking a photo.
   */
  public playCameraShutter() {
    this.vibrate([35, 45, 55]);

    if (this.storedShutterWavUrl) {
      try {
        if (!this.shutterAudioElement) {
          this.shutterAudioElement = new Audio(this.storedShutterWavUrl);
        }
        this.shutterAudioElement.currentTime = 0;
        this.shutterAudioElement.volume = 1.0;
        this.shutterAudioElement.play().catch(() => {});
        return;
      } catch {
        // Fallback to Web Audio synthesis below
      }
    }

    const ctx = this.getContext();
    if (!ctx) return;
    const now = ctx.currentTime;
    this.playTone(ctx, 1800, now, 0.03, "triangle", 0.25);
    this.playTone(ctx, 2400, now + 0.065, 0.05, "sawtooth", 0.25);
  }

  // Success chord when AI starts speaking the answer
  public playSuccessChime() {
    this.vibrate([35, 40, 70]);
    const ctx = this.getContext();
    if (!ctx) return;

    const now = ctx.currentTime;
    this.playTone(ctx, 523.25, now, 0.1, "sine", 0.12); // C5
    this.playTone(ctx, 659.25, now + 0.07, 0.1, "sine", 0.12); // E5
    this.playTone(ctx, 783.99, now + 0.14, 0.2, "sine", 0.15); // G5
  }

  private playTone(
    ctx: AudioContext,
    freq: number,
    startTime: number,
    duration: number,
    type: OscillatorType,
    maxGain: number
  ) {
    try {
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.type = type;
      osc.frequency.setValueAtTime(freq, startTime);

      gain.gain.setValueAtTime(0.001, startTime);
      gain.gain.exponentialRampToValueAtTime(maxGain, startTime + 0.015);
      gain.gain.exponentialRampToValueAtTime(0.001, startTime + duration);

      osc.connect(gain);
      gain.connect(ctx.destination);

      osc.start(startTime);
      osc.stop(startTime + duration + 0.02);
    } catch {
      // Ignore audio errors
    }
  }
}

export const soundFX = new SoundAndHaptics();
