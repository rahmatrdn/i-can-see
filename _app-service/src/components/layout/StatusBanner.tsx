import React from "react";
import { Camera, CameraOff, MicOff } from "lucide-react";
import { InteractionPhase } from "../../types/assistant";

interface StatusBannerProps {
  cameraActive: boolean;
  phase: InteractionPhase;
  micError: string | null;
  onRequestMic?: () => void;
  onOpenPermissionsModal?: () => void;
}

export const StatusBanner: React.FC<StatusBannerProps> = ({
  cameraActive,
  phase,
  micError,
  onRequestMic,
  onOpenPermissionsModal,
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
        <div
          role="button"
          tabIndex={0}
          onClick={(e) => {
            e.stopPropagation();
            if (onRequestMic) {
              onRequestMic();
            } else if (onOpenPermissionsModal) {
              onOpenPermissionsModal();
            }
          }}
          onKeyDown={(e) => {
            if (e.key === "Enter" || e.key === " ") {
              e.preventDefault();
              e.stopPropagation();
              if (onRequestMic) onRequestMic();
              else if (onOpenPermissionsModal) onOpenPermissionsModal();
            }
          }}
          className="bg-rose-50 border border-rose-300 text-rose-800 px-3.5 py-2.5 rounded-xl text-xs font-medium flex items-center justify-between gap-2 cursor-pointer hover:bg-rose-100/90 active:scale-[0.99] transition-all shadow-xs"
        >
          <div className="flex items-center gap-2 flex-1">
            <MicOff className="w-4 h-4 text-rose-600 shrink-0" />
            <span className="leading-snug">{micError}</span>
          </div>
          <span className="shrink-0 text-[11px] font-bold text-rose-700 bg-white px-2 py-1 rounded-lg border border-rose-200">
            Allow Mic ❯
          </span>
        </div>
      )}
    </div>
  );
};
