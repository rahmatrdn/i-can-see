// Local Stored Audio Files (/audio/*.mp3), Realistic Camera Shutter, & Haptic Utility

export const POINT_CAMERA_INSTRUCTION_TEXT =
  "Point your camera at the object you want to capture. Then, tap the screen once when you're ready.";

class SoundAndHaptics {
  private ctx: AudioContext | null = null;
  private shutterAudioElement: HTMLAudioElement | null = null;

  // Preloaded static audio file players (/public/audio/*.mp3)
  private pointCameraAudio: HTMLAudioElement | null = null;
  private helloAudio: HTMLAudioElement | null = null;
  private processingAnswerAudio: HTMLAudioElement | null = null;
  private processingImageAudio: HTMLAudioElement | null = null;
  private activeVoiceAudio: HTMLAudioElement | null = null;

  constructor() {
    if (typeof window !== "undefined") {
      // Preload camera shutter MP3 from /public/audio/shutter.mp3
      this.shutterAudioElement = new Audio("/audio/shutter.mp3");
      this.shutterAudioElement.preload = "auto";

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

  /**
   * Unlocks Web Audio API AudioContext during a direct user gesture (essential for iOS Safari).
   */
  public unlockAudio() {
    return this.getContext();
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
   * Seamlessly triggers callback right as the speech vocalization finishes (~720ms)
   * to eliminate mobile MP3 tail silence lag and start listening with zero dead delay.
   */
  public playStoredHelloGreeting(isMuted: boolean, onFinished: () => void) {
    this.vibrate([40, 30, 60]);
    if (isMuted) {
      setTimeout(onFinished, 150);
      return;
    }

    let finished = false;
    const triggerFinishedOnce = () => {
      if (finished) return;
      finished = true;
      this.stopStoredGreeting();
      // Natural, comfortable 200ms pause after greeting finishes before listening chime
      setTimeout(onFinished, 200);
    };

    // Safety fallback timer only if onended does not fire
    const safetyTimer = setTimeout(triggerFinishedOnce, 1400);

    this.playStaticAudioFile(
      this.helloAudio,
      "/audio/hello.mp3?v=3",
      isMuted,
      () => {
        clearTimeout(safetyTimer);
        triggerFinishedOnce();
      }
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

  // Soft quick chime when microphone starts listening
  public playListeningStart() {
    this.vibrate(40);
    const ctx = this.getContext();
    if (!ctx) return;

    const now = ctx.currentTime;
    this.playTone(ctx, 659.25, now, 0.05, "sine", 0.08); // E5
    this.playTone(ctx, 880.0, now + 0.05, 0.08, "sine", 0.09); // A5
  }

  /**
   * Authentic two-part mechanical camera shutter sound when taking a photo.
   */
  public playCameraShutter() {
    this.vibrate([35, 45, 55]);

    if (this.shutterAudioElement) {
      try {
        this.shutterAudioElement.currentTime = 0;
        this.shutterAudioElement.volume = 1.0;
        const playPromise = this.shutterAudioElement.play();
        if (playPromise !== undefined) {
          playPromise.catch(() => {});
        }
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
