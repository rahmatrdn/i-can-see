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
          "⚠️ iOS Safari requires an HTTPS or localhost connection. On plain HTTP (http://...), Safari automatically disables microphone and camera access. Please open via HTTPS or a secure tunnel (e.g. ngrok) to enable permissions."
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
          "⚠️ iOS Safari blocks microphone access on plain HTTP connections. Please open the app over HTTPS or a secure tunnel."
        );
      } else {
        setPermissionRequestError(
          "Your browser or device does not support microphone access on this connection."
        );
      }
      return false;
    }

    setIsRequestingAccess(true);
    setPermissionRequestError(null);
    soundFX.unlockAudio();
    soundFX.vibrate(25);

    try {
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
          "Microphone access was denied or blocked in Safari. On iPhone: Go to iPhone Settings > Safari > Microphone > set to 'Allow' (or tap the 'aA' icon in the Safari address bar > Website Settings > Microphone: Allow)."
        );
      } else if (err?.name === "NotFoundError" || err?.name === "DevicesNotFoundError") {
        setPermissionRequestError(
          "No microphone hardware was detected on your device."
        );
      } else {
        setPermissionRequestError(
          `Failed to request microphone access: ${err?.message || err?.name || "Unknown error"}`
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
          "⚠️ iOS Safari blocks camera access on plain HTTP connections. Please open the app over HTTPS or a secure tunnel."
        );
      } else {
        setPermissionRequestError(
          "Your browser or device does not support camera access on this connection."
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
          "Camera access was denied in Safari. On iPhone: Go to iPhone Settings > Safari > Camera > set to 'Allow'."
        );
      } else {
        setPermissionRequestError(
          `Failed to request camera access: ${err?.message || err?.name || "Unknown error"}`
        );
      }
      return false;
    }
  }, [facingMode, isInsecureContext, micPermission]);

  /**
   * Requests both microphone and camera permissions together.
   */
  const handleRequestBrowserPermissions = useCallback(async () => {
    if (!navigator.mediaDevices?.getUserMedia) {
      if (isInsecureContext) {
        setPermissionRequestError(
          "⚠️ iOS Safari blocks microphone and camera access on plain HTTP connections. Use HTTPS or a secure tunnel (e.g. ngrok) so iPhone can display the permission popup."
        );
      } else {
        setPermissionRequestError(
          "Your browser does not support media access on this connection."
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

    // 1. Try requesting combined stream (standard web)
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
      // 2. If combined fails, request sequentially
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
      if (!micGranted) missing.push("Microphone");
      if (!camGranted) missing.push("Camera");
      setPermissionRequestError(
        `${missing.join(
          " & "
        )} access has not been granted yet. Please click "Allow" on the browser popup or enable permissions in iPhone Settings > Safari > Microphone & Camera.`
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
