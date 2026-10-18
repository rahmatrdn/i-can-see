import React from "react";
import { Camera, Hand, Mic, Sparkles } from "lucide-react";
import { InteractionPhase } from "../../../types/assistant";
import { getOrbTheme } from "../utils/orbTheme";

interface CenterAssistantOrbProps {
  phase: InteractionPhase;
  statusMessage: string;
  subStatusText: string;
  liveTranscript: string;
  micLevel: number;
  onCenterClick: (e: React.MouseEvent<HTMLButtonElement>) => void;
}

export const CenterAssistantOrb: React.FC<CenterAssistantOrbProps> = ({
  phase,
  statusMessage,
  subStatusText,
  liveTranscript,
  micLevel,
  onCenterClick,
}) => {
  const isAiSpeaking =
    phase === "ASKING_INTENT" ||
    phase === "SPEAKING_RESPONSE" ||
    phase === "AIMING_CAMERA";
  const isProcessActive = phase !== "IDLE";
  const orbTheme = getOrbTheme(phase, isAiSpeaking);

  return (
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

        {/* Layer 2: Pure Radial Circular Glowing Aura */}
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

        {/* Layer 3: Circular Soft Halo Rim */}
        <span
          aria-hidden="true"
          style={
            phase === "LISTENING_VOICE"
              ? {
                  transform: `scale(${1 + Math.min(0.42, micLevel / 140)})`,
                }
              : undefined
          }
          className="pointer-events-none absolute w-36 h-36 rounded-full bg-[#72ADD9]/10 border border-[#517DA8]/25 transition-transform duration-100"
        />

        {/* Layer 4: Interactive Circular Core Sphere with Pure Radial Gradient */}
        <button
          type="button"
          onClick={onCenterClick}
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
          className={`group relative z-10 w-32 h-32 rounded-full overflow-hidden flex flex-col items-center justify-center gap-1.5 transition-all duration-300 focus:outline-none focus-visible:ring-4 focus-visible:ring-[#517DA8] active:scale-95 border-2 border-white/80 ${
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
              <span
                className={`relative z-10 flex items-center gap-1.5 text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-0.5 rounded-full ${orbTheme.badgeBg}`}
              >
                <span className="w-2 h-2 rounded-full bg-current" />
                Tap 1x
              </span>
            </>
          )}

          {(phase === "ASKING_INTENT" || phase === "SPEAKING_RESPONSE") && (
            <>
              <div
                aria-hidden="true"
                className="relative z-10 flex items-center justify-center gap-1 h-9"
              >
                <span
                  className={`w-1.5 h-7 rounded-full ${orbTheme.barColor} animate-voice-bar-1`}
                />
                <span
                  className={`w-1.5 h-8 rounded-full ${orbTheme.barColor} animate-voice-bar-2`}
                />
                <span
                  className={`w-1.5 h-9 rounded-full ${orbTheme.barColor} animate-voice-bar-3`}
                />
                <span
                  className={`w-1.5 h-8 rounded-full ${orbTheme.barColor} animate-voice-bar-4`}
                />
                <span
                  className={`w-1.5 h-7 rounded-full ${orbTheme.barColor} animate-voice-bar-5`}
                />
              </div>
              <span
                className={`relative z-10 flex items-center gap-1.5 text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-0.5 rounded-full ${orbTheme.badgeBg}`}
              >
                <span className="w-2 h-2 rounded-full bg-current" />
                Stop AI
              </span>
            </>
          )}

          {phase === "LISTENING_VOICE" && (
            <>
              <Mic className="relative z-10 w-10 h-10 stroke-[2.3]" />
              <span
                className={`relative z-10 flex items-center gap-1.5 text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-0.5 rounded-full ${orbTheme.badgeBg}`}
              >
                <span className="w-2 h-2 rounded-full bg-current" />
                Stop
              </span>
            </>
          )}

          {phase === "CAPTURING_CAMERA" && (
            <>
              <Camera className="relative z-10 w-10 h-10 stroke-[2.3]" />
              <span
                className={`relative z-10 flex items-center gap-1.5 text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-0.5 rounded-full ${orbTheme.badgeBg}`}
              >
                <span className="w-2 h-2 rounded-full bg-current" />
                Stop
              </span>
            </>
          )}

          {phase === "PROCESSING_AI" && (
            <>
              <Sparkles className="relative z-10 w-10 h-10 stroke-[2.3] animate-spin" />
              <span
                className={`relative z-10 flex items-center gap-1.5 text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-0.5 rounded-full ${orbTheme.badgeBg}`}
              >
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
                className="w-1.5 rounded-full bg-[#2563EB] transition-all duration-75"
              />
            );
          })}
        </div>
      )}

      <h1
        className="mt-4 text-2xl sm:text-[26px] font-extrabold tracking-tight text-[#233053] leading-snug"
        style={{ textWrap: "balance" }}
      >
        {statusMessage}
      </h1>

      <p className="mt-2.5 text-base font-medium text-slate-600 leading-relaxed max-w-sm line-clamp-4">
        {subStatusText}
      </p>
    </div>
  );
};
