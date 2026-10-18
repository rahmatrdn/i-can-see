import React, { useState } from "react";
import { SwitchCamera, Mic, MicOff, LogOut, AlertTriangle } from "lucide-react";
import { DevicePermissionState } from "../../types/assistant";
import { User } from "../../features/auth/types/auth";

interface TopHeaderBarProps {
  facingMode: "environment" | "user";
  onToggleFacingMode: () => void;
  micPermission?: DevicePermissionState;
  cameraPermission?: DevicePermissionState;
  onOpenPermissionsModal?: () => void;
  user?: User | null;
  onLogout?: () => void;
}

export const TopHeaderBar: React.FC<TopHeaderBarProps> = ({
  facingMode,
  onToggleFacingMode,
  micPermission,
  cameraPermission,
  onOpenPermissionsModal,
  user,
  onLogout,
}) => {
  const isMicGranted = micPermission === "granted";
  const [showConfirmLogout, setShowConfirmLogout] = useState(false);

  return (
    <header className="relative z-20 flex items-center justify-between px-3.5 h-16 bg-white/90 backdrop-blur-md border-b border-slate-200/80 shadow-[0_2px_12px_rgba(35,48,83,0.04)]">
      {/* Brand Logo & User Info */}
      <div className="flex items-center gap-2 min-w-0">
        <img
          src="/logo-icansee.png"
          alt="iCanSee Logo"
          className="h-8 w-auto object-contain select-none shrink-0"
        />
        {user && (
          <div className="hidden sm:flex flex-col text-left overflow-hidden">
            <span className="text-xs font-bold text-[#233053] truncate max-w-[120px]">
              {user.name}
            </span>
            <span className="text-[10px] text-slate-400 font-medium truncate max-w-[120px]">
              {user.email}
            </span>
          </div>
        )}
      </div>

      {/* Action Controls: Icon-only buttons for Mic, Camera, and Logout */}
      <div className="flex items-center gap-1.5 sm:gap-2">
        {onOpenPermissionsModal && (
          <button
            type="button"
            onClick={(e) => {
              e.stopPropagation();
              onOpenPermissionsModal();
            }}
            title={isMicGranted ? "Microphone active" : "Microphone permissions required"}
            aria-label="Microphone and camera permissions"
            className={`w-10 h-10 rounded-xl border flex items-center justify-center transition-all shrink-0 cursor-pointer active:scale-95 shadow-xs ${
              isMicGranted
                ? "bg-[#F1F5F9] text-emerald-700 border-emerald-200 hover:bg-emerald-50"
                : "bg-amber-50 text-amber-800 border-amber-300 hover:bg-amber-100"
            }`}
          >
            {isMicGranted ? (
              <Mic className="w-4.5 h-4.5 text-emerald-600" />
            ) : (
              <MicOff className="w-4.5 h-4.5 text-amber-600" />
            )}
          </button>
        )}

        <button
          type="button"
          onClick={(e) => {
            e.stopPropagation();
            onToggleFacingMode();
          }}
          title={facingMode === "environment" ? "Switch to front camera" : "Switch to rear camera"}
          aria-label={facingMode === "environment" ? "Switch to front camera" : "Switch to rear camera"}
          className="w-10 h-10 rounded-xl bg-[#F1F5F9] text-[#233053] border border-slate-200 flex items-center justify-center hover:bg-slate-200/70 active:scale-95 transition-all shrink-0 cursor-pointer shadow-xs"
        >
          <SwitchCamera className="w-4.5 h-4.5 text-[#517DA8]" />
        </button>

        {/* Logout Red Icon Button */}
        {onLogout && (
          <div className="relative">
            <button
              type="button"
              onClick={(e) => {
                e.stopPropagation();
                setShowConfirmLogout(!showConfirmLogout);
              }}
              title={user ? `Sign out (${user.name})` : "Sign out"}
              aria-label="Sign out / Logout"
              className="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 border border-rose-200 hover:bg-rose-100 hover:text-rose-700 hover:border-rose-300 flex items-center justify-center transition-all active:scale-95 shrink-0 cursor-pointer shadow-xs"
            >
              <LogOut className="w-4.5 h-4.5 text-rose-600" />
            </button>

            {/* Click-outside backdrop */}
            {showConfirmLogout && (
              <div
                className="fixed inset-0 z-40 bg-slate-900/20 backdrop-blur-[1px]"
                onClick={(e) => {
                  e.stopPropagation();
                  setShowConfirmLogout(false);
                }}
              />
            )}

            {/* Logout Warning Popup Card: Positioned clearly below the button */}
            {showConfirmLogout && (
              <div
                role="dialog"
                aria-modal="true"
                aria-labelledby="logout-warning-title"
                onClick={(e) => e.stopPropagation()}
                className="absolute right-0 top-13 z-50 w-72 p-4 bg-white rounded-2xl border border-slate-200 shadow-2xl text-left animate-in fade-in zoom-in-95 duration-150"
              >
                <div className="flex items-center gap-2 mb-2">
                  <div className="w-8 h-8 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                    <AlertTriangle className="w-4.5 h-4.5" />
                  </div>
                  <div>
                    <h3 id="logout-warning-title" className="text-xs font-bold text-[#233053]">
                      Sign Out Warning
                    </h3>
                    <p className="text-[10px] text-slate-400 font-medium">
                      Confirm session termination
                    </p>
                  </div>
                </div>

                <p className="text-xs text-slate-600 mb-3.5 leading-relaxed">
                  Are you sure you want to sign out of <strong className="text-slate-800">{user?.name || "this account"}</strong>?
                </p>

                <div className="flex items-center gap-2">
                  <button
                    type="button"
                    onClick={() => setShowConfirmLogout(false)}
                    className="flex-1 h-9 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all cursor-pointer"
                  >
                    Cancel
                  </button>
                  <button
                    type="button"
                    onClick={() => {
                      setShowConfirmLogout(false);
                      onLogout();
                    }}
                    className="flex-1 h-9 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition-all cursor-pointer shadow-sm active:scale-95"
                  >
                    Yes, Sign Out
                  </button>
                </div>
              </div>
            )}
          </div>
        )}
      </div>
    </header>
  );
};
