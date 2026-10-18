import React from "react";
import { Camera, CameraOff } from "lucide-react";
import { InteractionPhase } from "../../types/assistant";

interface StatusBannerProps {
  cameraActive: boolean;
  phase: InteractionPhase;
  micError: string | null;
}

export const StatusBanner: React.FC<StatusBannerProps> = ({
  cameraActive,
  phase,
  micError,
}) => {
  return (
    <div className="space-y-2">
      <div className="flex items-center justify-between text-xs font-mono text-slate-600 bg-white/90 backdrop-blur-md px-3.5 py-2.5 rounded-xl border border-slate-200/90 shadow-xs">
        <span className="flex items-center gap-1.5">
          {cameraActive ? (
            <>
              <Camera className="w-3.5 h-3.5 text-emerald-600" />
              <span className="text-emerald-700 font-semibold">CAMERA READY</span>
            </>
          ) : (
            <>
              <CameraOff className="w-3.5 h-3.5 text-slate-400" />
              <span>CAMERA OFF (STANDBY)</span>
            </>
          )}
        </span>
        <span>·</span>
        <span className="text-[#233053] font-semibold">
          {phase === "IDLE" && "1x SPEAK · 2x CAMERA"}
          {phase === "ASKING_INTENT" && "AI SPEAKING"}
          {phase === "LISTENING_VOICE" && "LISTENING"}
          {phase === "AIMING_CAMERA" && "TAP 1x TO CAPTURE"}
          {phase === "CAPTURING_CAMERA" && "CAPTURING PHOTO"}
          {phase === "PROCESSING_AI" && "PROCESSING AI"}
          {phase === "SPEAKING_RESPONSE" && "AI SPEAKING"}
        </span>
      </div>

      {micError && (
        <div className="bg-rose-50 border border-rose-300 text-rose-700 px-3.5 py-2.5 rounded-xl text-xs font-medium">
          {micError}
        </div>
      )}
    </div>
  );
};
