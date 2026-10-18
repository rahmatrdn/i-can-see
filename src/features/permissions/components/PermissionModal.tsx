import React, { useState } from "react";
import { Mic, Camera, CheckCircle2, AlertTriangle, HelpCircle, X, ExternalLink } from "lucide-react";
import { DevicePermissionState } from "../../../types/assistant";

interface PermissionModalProps {
  isOpen: boolean;
  micPermission: DevicePermissionState;
  cameraPermission: DevicePermissionState;
  isRequestingAccess: boolean;
  permissionRequestError: string | null;
  isInsecureContext?: boolean;
  onRequestPermissions: () => void;
  onRequestMicOnly?: () => void;
  onRequestCameraOnly?: () => void;
  onClose?: () => void;
}

export const PermissionModal: React.FC<PermissionModalProps> = ({
  isOpen,
  micPermission,
  cameraPermission,
  isRequestingAccess,
  permissionRequestError,
  isInsecureContext = false,
  onRequestPermissions,
  onRequestMicOnly,
  onRequestCameraOnly,
  onClose,
}) => {
  const [showIphoneHelp, setShowIphoneHelp] = useState<boolean>(false);

  if (!isOpen) return null;

  const isMicBlocked = micPermission === "denied";
  const isCamBlocked = cameraPermission === "denied";
  const isMicGranted = micPermission === "granted";
  const isCamGranted = cameraPermission === "granted";

  return (
    <div
      role="dialog"
      aria-modal="true"
      aria-labelledby="permission-modal-title"
      aria-describedby="permission-modal-desc"
      onClick={(e) => e.stopPropagation()}
      className="absolute inset-0 z-50 flex items-center justify-center p-4 bg-[#233053]/55 backdrop-blur-md overflow-y-auto"
    >
      <div className="relative w-full max-w-sm rounded-3xl bg-white border border-slate-200 shadow-[0_24px_60px_rgba(35,48,83,0.25)] p-5 text-center space-y-4 my-auto">
        {/* Optional Close Button */}
        {onClose && (
          <button
            type="button"
            onClick={onClose}
            aria-label="Tutup dialog izin"
            className="absolute top-4 right-4 p-1.5 rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer"
          >
            <X className="w-5 h-5" />
          </button>
        )}

        <div className="mx-auto flex items-center justify-center gap-3 pt-1">
          <div
            className={`w-12 h-12 rounded-2xl flex items-center justify-center border transition-all ${
              isMicGranted
                ? "bg-emerald-50 border-emerald-300 text-emerald-600"
                : isMicBlocked
                ? "bg-rose-50 border-rose-300 text-rose-600"
                : "bg-[#F0F7FF] border-[#72ADD9]/40 text-[#517DA8]"
            }`}
          >
            <Mic className="w-6 h-6" />
          </div>
          <div
            className={`w-12 h-12 rounded-2xl flex items-center justify-center border transition-all ${
              isCamGranted
                ? "bg-emerald-50 border-emerald-300 text-emerald-600"
                : isCamBlocked
                ? "bg-rose-50 border-rose-300 text-rose-600"
                : "bg-[#F0F7FF] border-[#72ADD9]/40 text-[#233053]"
            }`}
          >
            <Camera className="w-6 h-6" />
          </div>
        </div>

        <div className="space-y-1">
          <h2
            id="permission-modal-title"
            className="text-lg font-extrabold tracking-tight text-[#233053]"
          >
            Izin Mikrofon &amp; Kamera
          </h2>
          <p
            id="permission-modal-desc"
            className="text-xs text-slate-600 leading-relaxed"
          >
            <strong>iCanSee</strong> memerlukan akses <strong>Mikrofon</strong>{" "}
            untuk mendengarkan pertanyaan suara dan <strong>Kamera</strong> untuk
            menganalisis objek di sekitar Anda.
          </p>
        </div>

        {/* Warning if running on plain HTTP on Mobile Safari */}
        {isInsecureContext && (
          <div className="rounded-xl bg-amber-50 border border-amber-300 p-3 text-left space-y-1">
            <div className="flex items-center gap-1.5 text-amber-800 font-bold text-xs">
              <AlertTriangle className="w-4 h-4 shrink-0 text-amber-600" />
              <span>Penting untuk Pengguna iPhone:</span>
            </div>
            <p className="text-[11px] text-amber-900 leading-relaxed">
              Safari di iOS <strong>memblokir mikrofon pada HTTP biasa</strong>.
              Untuk memunculkan popup izin di iPhone, buka melalui <strong>HTTPS</strong> atau gunakan tunnel seperti <code>ngrok</code> / <code>cloudflared</code>.
            </p>
          </div>
        )}

        {/* Permission Status List */}
        <div className="rounded-2xl bg-[#F8FAFC] border border-slate-200 p-3 space-y-2 text-left">
          <div className="flex items-center justify-between text-xs">
            <span className="flex items-center gap-2 font-bold text-[#233053]">
              <Mic className="w-4 h-4 text-[#517DA8]" />
              Akses Mikrofon
            </span>
            <span
              className={`font-mono text-[11px] font-semibold px-2 py-0.5 rounded-full ${
                isMicGranted
                  ? "bg-emerald-100 text-emerald-700"
                  : isMicBlocked
                  ? "bg-rose-100 text-rose-700"
                  : "bg-amber-100 text-amber-800"
              }`}
            >
              {isMicGranted
                ? "Diizinkan ✓"
                : isMicBlocked
                ? "Diblokir ✕"
                : "Belum Diizinkan"}
            </span>
          </div>

          <div className="border-t border-slate-200/80" />

          <div className="flex items-center justify-between text-xs">
            <span className="flex items-center gap-2 font-bold text-[#233053]">
              <Camera className="w-4 h-4 text-[#517DA8]" />
              Akses Kamera
            </span>
            <span
              className={`font-mono text-[11px] font-semibold px-2 py-0.5 rounded-full ${
                isCamGranted
                  ? "bg-emerald-100 text-emerald-700"
                  : isCamBlocked
                  ? "bg-rose-100 text-rose-700"
                  : "bg-amber-100 text-amber-800"
              }`}
            >
              {isCamGranted
                ? "Diizinkan ✓"
                : isCamBlocked
                ? "Diblokir ✕"
                : "Belum Diizinkan"}
            </span>
          </div>
        </div>

        {/* Error message / feedback */}
        {permissionRequestError && (
          <div className="rounded-xl bg-rose-50 border border-rose-200 p-3 text-left space-y-1">
            <div className="flex items-start gap-1.5 text-rose-700 text-xs font-semibold">
              <AlertTriangle className="w-4 h-4 shrink-0 mt-0.5" />
              <span className="leading-snug">{permissionRequestError}</span>
            </div>
          </div>
        )}

        {/* iPhone Help Accordion */}
        <div className="text-left">
          <button
            type="button"
            onClick={() => setShowIphoneHelp((prev) => !prev)}
            className="flex items-center gap-1.5 text-xs font-bold text-[#517DA8] hover:text-[#233053] transition-colors cursor-pointer py-1"
          >
            <HelpCircle className="w-4 h-4" />
            <span>
              {showIphoneHelp
                ? "Sembunyikan panduan iPhone"
                : "Panduan jika izin tidak muncul di iPhone"}
            </span>
          </button>

          {showIphoneHelp && (
            <div className="mt-2 p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 space-y-1.5">
              <p className="font-bold text-[#233053]">Cara mengizinkan di Safari iOS:</p>
              <ol className="list-decimal list-inside space-y-1 text-[11px] leading-relaxed text-slate-600">
                <li>
                  Ketuk ikon <strong>"aA"</strong> di sebelah kiri bilah URL Safari.
                </li>
                <li>
                  Pilih <strong>"Pengaturan Situs Web"</strong> (Website Settings).
                </li>
                <li>
                  Ubah <strong>Mikrofon</strong> dan <strong>Kamera</strong> menjadi <strong>"Izinkan"</strong> (Allow).
                </li>
                <li>
                  Muat ulang (refresh) halaman.
                </li>
              </ol>
              <p className="text-[10px] text-slate-500 pt-1">
                Atau melalui <strong>Pengaturan iPhone &gt; Safari &gt; Mikrofon &gt; Izinkan</strong>.
              </p>
            </div>
          )}
        </div>

        {/* Action Buttons */}
        <div className="space-y-2 pt-1">
          {/* Main button: Request Both */}
          <button
            type="button"
            disabled={isRequestingAccess}
            onClick={onRequestPermissions}
            className="w-full min-h-[46px] py-2.5 px-4 rounded-2xl bg-[#233053] hover:bg-[#354C6E] active:scale-[0.98] disabled:opacity-60 text-white font-extrabold text-sm shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer"
          >
            <CheckCircle2 className="w-4 h-4 text-[#72ADD9]" />
            <span>
              {isRequestingAccess
                ? "Meminta Izin Browser..."
                : "Izinkan Mikrofon & Kamera"}
            </span>
          </button>

          {/* Individual Requests: Allows iPhone user to directly isolate and trigger microphone */}
          <div className="grid grid-cols-2 gap-2">
            {onRequestMicOnly && (
              <button
                type="button"
                disabled={isRequestingAccess || isMicGranted}
                onClick={onRequestMicOnly}
                className="py-2 px-3 rounded-xl border border-[#517DA8]/30 bg-[#F0F7FF] hover:bg-[#E0EFFE] text-[#233053] text-xs font-bold transition-all disabled:opacity-50 flex items-center justify-center gap-1.5 cursor-pointer"
              >
                <Mic className="w-3.5 h-3.5 text-[#517DA8]" />
                <span>{isMicGranted ? "Mikrofon OK" : "Izin Mic Saja"}</span>
              </button>
            )}

            {onRequestCameraOnly && (
              <button
                type="button"
                disabled={isRequestingAccess || isCamGranted}
                onClick={onRequestCameraOnly}
                className="py-2 px-3 rounded-xl border border-slate-300 bg-slate-50 hover:bg-slate-100 text-[#233053] text-xs font-bold transition-all disabled:opacity-50 flex items-center justify-center gap-1.5 cursor-pointer"
              >
                <Camera className="w-3.5 h-3.5 text-slate-600" />
                <span>{isCamGranted ? "Kamera OK" : "Izin Kamera Saja"}</span>
              </button>
            )}
          </div>

          {/* Dismiss button if user wants to proceed or test */}
          {onClose && (
            <button
              type="button"
              onClick={onClose}
              className="text-xs text-slate-500 hover:text-slate-700 underline font-medium pt-1 cursor-pointer block mx-auto"
            >
              Lanjutkan Tanpa Izin Sekarang
            </button>
          )}
        </div>
      </div>
    </div>
  );
};
