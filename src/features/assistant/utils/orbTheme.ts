import { InteractionPhase, OrbThemeConfig } from "../../../types/assistant";

export function getOrbTheme(
  phase: InteractionPhase,
  isAiSpeaking: boolean
): OrbThemeConfig {
  if (phase === "IDLE") {
    return {
      waveBorder: "border-[#517DA8]/45",
      waveBg: "bg-[#72ADD9]/15",
      auraStyle:
        "radial-gradient(circle, rgba(114,173,217,0.45) 0%, rgba(81,125,168,0.18) 55%, rgba(114,173,217,0) 72%)",
      coreStyle:
        "radial-gradient(circle at 35% 28%, #517DA8 0%, #354C6E 52%, #233053 100%)",
      coreShadow: "shadow-[0_18px_55px_rgba(35,48,83,0.38)]",
      textColor: "text-white",
      badgeBg: "bg-white/20 text-white",
      barColor: "bg-white",
    };
  }

  if (phase === "AIMING_CAMERA") {
    return {
      waveBorder: "border-[#0284C7]/65",
      waveBg: "bg-[#38BDF8]/20",
      auraStyle:
        "radial-gradient(circle, rgba(56,189,248,0.52) 0%, rgba(2,132,199,0.22) 55%, rgba(56,189,248,0) 72%)",
      coreStyle:
        "radial-gradient(circle at 35% 28%, #38BDF8 0%, #0284C7 52%, #0369A1 100%)",
      coreShadow: "shadow-[0_18px_65px_rgba(2,132,199,0.48)]",
      textColor: "text-white",
      badgeBg: "bg-white/20 text-white",
      barColor: "bg-white",
    };
  }

  if (isAiSpeaking) {
    return {
      waveBorder: "border-[#10B981]/55",
      waveBg: "bg-[#10B981]/15",
      auraStyle:
        "radial-gradient(circle, rgba(16,185,129,0.42) 0%, rgba(5,150,105,0.18) 55%, rgba(16,185,129,0) 72%)",
      coreStyle:
        "radial-gradient(circle at 35% 28%, #34D399 0%, #10B981 50%, #047857 100%)",
      coreShadow: "shadow-[0_18px_60px_rgba(16,185,129,0.45)]",
      textColor: "text-white",
      badgeBg: "bg-white/25 text-white",
      barColor: "bg-white",
    };
  }

  if (phase === "LISTENING_VOICE") {
    return {
      waveBorder: "border-[#2563EB]/55",
      waveBg: "bg-[#3B82F6]/15",
      auraStyle:
        "radial-gradient(circle, rgba(59,130,246,0.46) 0%, rgba(37,99,235,0.2) 55%, rgba(59,130,246,0) 72%)",
      coreStyle:
        "radial-gradient(circle at 35% 28%, #60A5FA 0%, #2563EB 52%, #1D4ED8 100%)",
      coreShadow: "shadow-[0_18px_65px_rgba(37,99,235,0.48)]",
      textColor: "text-white",
      badgeBg: "bg-white/20 text-white",
      barColor: "bg-white",
    };
  }

  return {
    waveBorder: "border-[#354C6E]/45",
    waveBg: "bg-[#517DA8]/15",
    auraStyle:
      "radial-gradient(circle, rgba(81,125,168,0.4) 0%, rgba(53,76,110,0.18) 55%, rgba(81,125,168,0) 72%)",
    coreStyle:
      "radial-gradient(circle at 35% 28%, #517DA8 0%, #354C6E 55%, #233053 100%)",
    coreShadow: "shadow-[0_18px_55px_rgba(35,48,83,0.4)]",
    textColor: "text-white",
    badgeBg: "bg-white/20 text-white",
    barColor: "bg-white",
  };
}
