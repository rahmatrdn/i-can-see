import React from "react";
import { SwitchCamera, Mic, MicOff } from "lucide-react";
import { DevicePermissionState } from "../../types/assistant";

interface TopHeaderBarProps {
  facingMode: "environment" | "user";
  onToggleFacingMode: () => void;
  micPermission?: DevicePermissionState;
  cameraPermission?: DevicePermissionState;
  onOpenPermissionsModal?: () => void;
}

export const TopHeaderBar: React.FC<TopHeaderBarProps> = ({
  facingMode,
  onToggleFacingMode,
  micPermission,
  cameraPermission,
  onOpenPermissionsModal,
}) => {
  const isMicGranted = micPermission === "granted";

  return (
    <header className="relative z-20 flex items-center justify-between px-4 h-16 bg-white/90 backdrop-blur-md border-b border-slate-200/80 shadow-[0_2px_12px_rgba(35,48,83,0.04)]">
      <div className="flex items-center">
        <img
          src="/logo-icansee.png"
          alt="iCanSee Logo"
          className="h-9 w-auto object-contain select-none"
        />
      </div>

      <div className="flex items-center gap-2">
        {onOpenPermissionsModal && (
          <button
            type="button"
            onClick={(e) => {
              e.stopPropagation();
              onOpenPermissionsModal();
            }}
            aria-label="Microphone and camera permissions"
            className={`min-h-[42px] px-2.5 rounded-xl border flex items-center gap-1.5 text-xs font-bold transition-all whitespace-nowrap shrink-0 cursor-pointer ${
              isMicGranted
                ? "bg-[#F1F5F9] text-emerald-700 border-emerald-200 hover:bg-emerald-50"
                : "bg-amber-50 text-amber-800 border-amber-300 hover:bg-amber-100"
            }`}
          >
            {isMicGranted ? (
              <Mic className="w-4 h-4 text-emerald-600" />
            ) : (
              <MicOff className="w-4 h-4 text-amber-600" />
            )}
            <span className="hidden sm:inline">
              {isMicGranted ? "Mic OK" : "Permissions"}
            </span>
          </button>
        )}

        <button
          type="button"
          onClick={(e) => {
            e.stopPropagation();
            onToggleFacingMode();
          }}
          aria-label="Switch between front and rear camera"
          className="min-h-[42px] px-3 rounded-xl bg-[#F1F5F9] text-[#233053] border border-slate-200 flex items-center gap-1.5 text-xs font-bold hover:bg-slate-200/70 active:scale-95 transition-all whitespace-nowrap shrink-0 cursor-pointer"
        >
          <SwitchCamera className="w-4 h-4 text-[#517DA8]" />
          <span>{facingMode === "environment" ? "Rear" : "Front"}</span>
        </button>
      </div>
    </header>
  );
};
