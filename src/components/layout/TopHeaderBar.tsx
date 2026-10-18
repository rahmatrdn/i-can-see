import React from "react";
import { SwitchCamera } from "lucide-react";

interface TopHeaderBarProps {
  facingMode: "environment" | "user";
  onToggleFacingMode: () => void;
}

export const TopHeaderBar: React.FC<TopHeaderBarProps> = ({
  facingMode,
  onToggleFacingMode,
}) => {
  return (
    <header className="relative z-20 flex items-center justify-between px-4 h-16 bg-white/90 backdrop-blur-md border-b border-slate-200/80 shadow-[0_2px_12px_rgba(35,48,83,0.04)]">
      <div className="flex items-center">
        <img
          src="/logo-icansee.svg"
          alt="iCanSee Logo"
          className="h-9 w-auto object-contain select-none"
        />
      </div>

      <div className="flex items-center gap-2">
        <button
          type="button"
          onClick={(e) => {
            e.stopPropagation();
            onToggleFacingMode();
          }}
          aria-label="Switch between front and rear camera"
          className="min-h-[42px] px-3 rounded-xl bg-[#F1F5F9] text-[#233053] border border-slate-200 flex items-center gap-1.5 text-xs font-bold hover:bg-slate-200/70 active:scale-95 transition-all whitespace-nowrap shrink-0"
        >
          <SwitchCamera className="w-4 h-4 text-[#517DA8]" />
          <span>{facingMode === "environment" ? "Rear" : "Front"}</span>
        </button>
      </div>
    </header>
  );
};
