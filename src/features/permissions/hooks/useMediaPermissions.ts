import { useState, useEffect, useCallback } from "react";
import { DevicePermissionState } from "../../../types/assistant";
import { soundFX } from "../../../utils/soundEffects";

interface UseMediaPermissionsOptions {
  facingMode: "environment" | "user";
  onMicGranted?: () => void;
}

export function useMediaPermissions({
  facingMode,
  onMicGranted,
}: UseMediaPermissionsOptions) {
  const [micPermission, setMicPermission] =
    useState<DevicePermissionState>("checking");
  const [cameraPermission, setCameraPermission] =
    useState<DevicePermissionState>("checking");
  const [showPermissionModal, setShowPermissionModal] = useState<boolean>(false);
  const [isRequestingAccess, setIsRequestingAccess] = useState<boolean>(false);
  const [permissionRequestError, setPermissionRequestError] = useState<
    string | null
  >(null);

  const isInsecureContext =
    typeof window !== "undefined" &&
    !window.isSecureContext &&
    window.location.hostname !== "localhost" &&
    window.location.hostname !== "127.0.0.1";

  useEffect(() => {
    let isMounted = true;
    let micPermStatus: PermissionStatus | null = null;
    let camPermStatus: PermissionStatus | null = null;

    const evaluatePermissions = async () => {
      let micState: "granted" | "prompt" | "denied" = "prompt";
      let camState: "granted" | "prompt" | "denied" = "prompt";
      let permissionsApiSupported = false;

      // 1. Try modern Permissions API query (may throw on iOS Safari)
      if (typeof navigator !== "undefined" && navigator.permissions?.query) {
        try {
          const micRes = await navigator.permissions.query({
            name: "microphone" as PermissionName,
          });
          micPermStatus = micRes;
          micState = micRes.state;
          permissionsApiSupported = true;

          micRes.onchange = () => {
            if (!isMounted) return;
            setMicPermission(micRes.state);
            if (micRes.state === "granted" && onMicGranted) {
              onMicGranted();
            }
          };
        } catch {
          // iOS Safari throws TypeError for 'microphone' PermissionName
        }

        try {
          const camRes = await navigator.permissions.query({
            name: "camera" as PermissionName,
          });
          camPermStatus = camRes;
          camState = camRes.state;
          permissionsApiSupported = true;

          camRes.onchange = () => {
            if (!isMounted) return;
            setCameraPermission(camRes.state);
          };
        } catch {
          // iOS Safari throws TypeError for 'camera' PermissionName
        }
      }

      // 2. Fallback check using enumerateDevices label inspection
      if (
        (!permissionsApiSupported || (micState === "prompt" && camState === "prompt")) &&
        navigator.mediaDevices?.enumerateDevices
      ) {
        try {
          const devices = await navigator.mediaDevices.enumerateDevices();
          const hasMicLabel = devices.some(
            (d) =>
              d.kind === "audioinput" && d.label && d.label.trim().length > 0
          );
          const hasCamLabel = devices.some(
            (d) =>
              d.kind === "videoinput" && d.label && d.label.trim().length > 0
          );
          if (hasMicLabel) micState = "granted";
          if (hasCamLabel) camState = "granted";
        } catch {
          // keep default 'prompt'
        }
      }

      if (!isMounted) return;
      setMicPermission(micState);
      setCameraPermission(camState);

      if (micState !== "granted" || camState !== "granted") {
        setShowPermissionModal(true);
      } else {
        setShowPermissionModal(false);
      }

      // Inform users immediately if opened on iPhone via plain HTTP
      if (isInsecureContext && !navigator.mediaDevices?.getUserMedia) {
        setPermissionRequestError(
          "⚠️ iOS Safari / iPhone mewajibkan koneksi HTTPS atau localhost. Pada HTTP biasa (http://...), Safari otomatis menonaktifkan mikrofon dan kamera. Buka via HTTPS atau tunnel (seperti ngrok) agar izin muncul."
        );
      }
    };

    evaluatePermissions();

    return () => {
      isMounted = false;
      if (micPermStatus) micPermStatus.onchange = null;
      if (camPermStatus) camPermStatus.onchange = null;
    };
  }, [isInsecureContext, onMicGranted]);

  /**
   * Directly requests microphone permission on iOS Safari using reliable { audio: true }.
   * Must be called in direct response to user interaction.
   */
  const handleRequestMicPermission = useCallback(async () => {
    if (!navigator.mediaDevices?.getUserMedia) {
      if (isInsecureContext) {
        setPermissionRequestError(
          "⚠️ iOS Safari memblokir mikrofon pada HTTP biasa. Buka aplikasi menggunakan HTTPS atau tunnel ngrok agar iPhone menampilkan popup izin mikrofon."
        );
      } else {
        setPermissionRequestError(
          "Perangkat atau browser ini tidak mendukung getUserMedia microphone."
        );
      }
      return false;
    }

    setIsRequestingAccess(true);
    setPermissionRequestError(null);
    soundFX.unlockAudio();
    soundFX.vibrate(25);

    try {
      // Use simple boolean { audio: true } which is 100% compatible with iOS Safari WebKit
      const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
      stream.getTracks().forEach((track) => track.stop());

      setMicPermission("granted");
      if (onMicGranted) onMicGranted();
      setIsRequestingAccess(false);

      if (cameraPermission === "granted") {
        setShowPermissionModal(false);
      }
      return true;
    } catch (err: any) {
      console.warn("handleRequestMicPermission error:", err);
      setIsRequestingAccess(false);
      setMicPermission("denied");

      if (err?.name === "NotAllowedError" || err?.name === "PermissionDeniedError") {
        setPermissionRequestError(
          "Akses mikrofon ditolak atau diblokir di Safari. Di iPhone: Buka Pengaturan iPhone > Safari > Mikrofon > ubah ke 'Izinkan' (atau tap ikon 'aA' di kiri address bar Safari > Pengaturan Situs Web > Mikrofon: Izinkan)."
        );
      } else if (err?.name === "NotFoundError" || err?.name === "DevicesNotFoundError") {
        setPermissionRequestError(
          "Tidak ada perangkat mikrofon yang terdeteksi di perangkat Anda."
        );
      } else {
        setPermissionRequestError(
          `Gagal meminta izin mikrofon: ${err?.message || err?.name || "Unknown error"}`
        );
      }
      return false;
    }
  }, [cameraPermission, isInsecureContext, onMicGranted]);

  /**
   * Directly requests camera permission.
   */
  const handleRequestCameraPermission = useCallback(async () => {
    if (!navigator.mediaDevices?.getUserMedia) {
      if (isInsecureContext) {
        setPermissionRequestError(
          "⚠️ iOS Safari memblokir kamera pada HTTP biasa. Buka aplikasi menggunakan HTTPS atau tunnel ngrok."
        );
      } else {
        setPermissionRequestError(
          "Perangkat atau browser ini tidak mendukung getUserMedia camera."
        );
      }
      return false;
    }

    setIsRequestingAccess(true);
    setPermissionRequestError(null);
    soundFX.unlockAudio();
    soundFX.vibrate(25);

    try {
      const stream = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: { ideal: facingMode } },
      });
      stream.getTracks().forEach((track) => track.stop());

      setCameraPermission("granted");
      setIsRequestingAccess(false);

      if (micPermission === "granted") {
        setShowPermissionModal(false);
      }
      return true;
    } catch (err: any) {
      console.warn("handleRequestCameraPermission error:", err);
      setIsRequestingAccess(false);
      setCameraPermission("denied");

      if (err?.name === "NotAllowedError" || err?.name === "PermissionDeniedError") {
        setPermissionRequestError(
          "Akses kamera ditolak di Safari. Di iPhone: Buka Pengaturan iPhone > Safari > Kamera > ubah ke 'Izinkan'."
        );
      } else {
        setPermissionRequestError(
          `Gagal meminta izin kamera: ${err?.message || err?.name || "Unknown error"}`
        );
      }
      return false;
    }
  }, [facingMode, isInsecureContext, micPermission]);

  /**
   * Requests both microphone and camera permissions.
   */
  const handleRequestBrowserPermissions = useCallback(async () => {
    if (!navigator.mediaDevices?.getUserMedia) {
      if (isInsecureContext) {
        setPermissionRequestError(
          "⚠️ iOS Safari memblokir mikrofon & kamera pada HTTP biasa. Gunakan HTTPS atau tunnel ngrok agar iPhone dapat memunculkan popup izin."
        );
      } else {
        setPermissionRequestError(
          "Browser Anda tidak mendukung akses media pada koneksi ini."
        );
      }
      return;
    }

    setIsRequestingAccess(true);
    setPermissionRequestError(null);
    soundFX.unlockAudio();
    soundFX.vibrate(25);

    let micGranted = micPermission === "granted";
    let camGranted = cameraPermission === "granted";

    // 1. Try requesting combined stream
    try {
      const combinedStream = await navigator.mediaDevices.getUserMedia({
        audio: true,
        video: {
          facingMode: { ideal: facingMode },
        },
      });

      combinedStream.getTracks().forEach((track) => track.stop());
      micGranted = true;
      camGranted = true;
      if (onMicGranted) onMicGranted();
    } catch {
      // 2. If combined fails (very common on iOS Safari), request individually
      if (!micGranted) {
        try {
          const audioStream = await navigator.mediaDevices.getUserMedia({
            audio: true,
          });
          audioStream.getTracks().forEach((track) => track.stop());
          micGranted = true;
          if (onMicGranted) onMicGranted();
        } catch {
          micGranted = false;
        }
      }

      if (!camGranted) {
        try {
          const videoStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: { ideal: facingMode } },
          });
          videoStream.getTracks().forEach((track) => track.stop());
          camGranted = true;
        } catch {
          camGranted = false;
        }
      }
    }

    setMicPermission(micGranted ? "granted" : "denied");
    setCameraPermission(camGranted ? "granted" : "denied");
    setIsRequestingAccess(false);

    if (micGranted && camGranted) {
      setShowPermissionModal(false);
      setPermissionRequestError(null);
    } else {
      const missing: string[] = [];
      if (!micGranted) missing.push("Mikrofon");
      if (!camGranted) missing.push("Kamera");
      setPermissionRequestError(
        `Akses ${missing.join(
          " & "
        )} belum diizinkan. Silakan klik "Izinkan" pada popup browser atau buka Pengaturan iPhone > Safari > Izinkan akses mikrofon/kamera.`
      );
    }
  }, [cameraPermission, facingMode, isInsecureContext, micPermission, onMicGranted]);

  const openPermissionModal = useCallback(() => {
    setShowPermissionModal(true);
  }, []);

  const closePermissionModal = useCallback(() => {
    setShowPermissionModal(false);
  }, []);

  return {
    micPermission,
    cameraPermission,
    showPermissionModal,
    isRequestingAccess,
    permissionRequestError,
    isInsecureContext,
    handleRequestMicPermission,
    handleRequestCameraPermission,
    handleRequestBrowserPermissions,
    openPermissionModal,
    closePermissionModal,
  };
}
