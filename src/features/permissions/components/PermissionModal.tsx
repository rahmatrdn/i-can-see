import React from "react";
import { Mic, Camera, CheckCircle2 } from "lucide-react";
import { DevicePermissionState } from "../../../types/assistant";

interface PermissionModalProps {
  isOpen: boolean;
  micPermission: DevicePermissionState;
  cameraPermission: DevicePermissionState;
  isRequestingAccess: boolean;
  permissionRequestError: string | null;
  onRequestPermissions: () => void;
}

export const PermissionModal: React.FC<PermissionModalProps> = ({
  isOpen,
  micPermission,
  cameraPermission,
  isRequestingAccess,
  permissionRequestError,
  onRequestPermissions,
}) => {
  if (!isOpen) return null;

  return (
    <div
      role="dialog"
      aria-modal="true"
      aria-labelledby="permission-modal-title"
      aria-describedby="permission-modal-desc"
      onClick={(e) => e.stopPropagation()}
      className="absolute inset-0 z-50 flex items-center justify-center p-5 bg-[#233053]/45 backdrop-blur-sm"
    >
      <div className="w-full max-w-sm rounded-3xl bg-white border border-slate-200 shadow-[0_24px_60px_rgba(35,48,83,0.25)] p-6 text-center space-y-5">
        <div className="mx-auto flex items-center justify-center gap-3">
          <div className="w-12 h-12 rounded-2xl bg-[#F0F7FF] border border-[#72ADD9]/40 flex items-center justify-center text-[#233053]">
            <Mic className="w-6 h-6 text-[#517DA8]" />
          </div>
          <div className="w-12 h-12 rounded-2xl bg-[#F0F7FF] border border-[#72ADD9]/40 flex items-center justify-center text-[#233053]">
            <Camera className="w-6 h-6 text-[#233053]" />
          </div>
        </div>

        <div className="space-y-1.5">
          <h2
            id="permission-modal-title"
            className="text-xl font-extrabold tracking-tight text-[#233053]"
          >
            Allow Microphone &amp; Camera
          </h2>
          <p
            id="permission-modal-desc"
            className="text-sm text-slate-600 leading-relaxed"
          >
            <strong>iCanSee</strong> requires access to your{" "}
            <strong>Microphone</strong> for voice questions and{" "}
            <strong>Camera</strong> to capture and describe objects in front of
            you.
          </p>
        </div>

        {/* Permission Status List */}
        <div className="rounded-2xl bg-[#F8FAFC] border border-slate-200 p-3.5 space-y-2.5 text-left">
          <div className="flex items-center justify-between text-xs">
            <span className="flex items-center gap-2 font-bold text-[#233053]">
              <Mic className="w-4 h-4 text-[#517DA8]" />
              Microphone Access
            </span>
            <span
              className={`font-mono font-semibold ${
                micPermission === "granted"
                  ? "text-emerald-600"
                  : micPermission === "denied"
                  ? "text-rose-600"
                  : "text-amber-600"
              }`}
            >
              {micPermission === "granted"
                ? "Allowed"
                : micPermission === "denied"
                ? "Blocked"
                : "Not Allowed Yet"}
            </span>
          </div>

          <div className="border-t border-slate-200/80" />

          <div className="flex items-center justify-between text-xs">
            <span className="flex items-center gap-2 font-bold text-[#233053]">
              <Camera className="w-4 h-4 text-[#517DA8]" />
              Camera Access
            </span>
            <span
              className={`font-mono font-semibold ${
                cameraPermission === "granted"
                  ? "text-emerald-600"
                  : cameraPermission === "denied"
                  ? "text-rose-600"
                  : "text-amber-600"
              }`}
            >
              {cameraPermission === "granted"
                ? "Allowed"
                : cameraPermission === "denied"
                ? "Blocked"
                : "Not Allowed Yet"}
            </span>
          </div>
        </div>

        {permissionRequestError && (
          <div className="rounded-xl bg-rose-50 border border-rose-200 px-3.5 py-2.5 text-xs font-medium text-rose-700 text-left leading-relaxed">
            {permissionRequestError}
          </div>
        )}

        <div className="space-y-2 pt-1">
          <button
            type="button"
            disabled={isRequestingAccess}
            onClick={onRequestPermissions}
            className="w-full min-h-[48px] py-3 px-5 rounded-2xl bg-[#233053] hover:bg-[#354C6E] active:scale-[0.98] disabled:opacity-60 text-white font-extrabold text-sm shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer"
          >
            <CheckCircle2 className="w-4 h-4 text-[#72ADD9]" />
            <span>
              {isRequestingAccess
                ? "Requesting Browser Access..."
                : "Allow Microphone & Camera"}
            </span>
          </button>
        </div>
      </div>
    </div>
  );
};
