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

  useEffect(() => {
    let isMounted = true;
    let micPermStatus: PermissionStatus | null = null;
    let camPermStatus: PermissionStatus | null = null;

    const evaluatePermissions = async () => {
      let micState: "granted" | "prompt" | "denied" = "prompt";
      let camState: "granted" | "prompt" | "denied" = "prompt";
      let permissionsApiSupported = false;

      if (typeof navigator !== "undefined" && navigator.permissions?.query) {
        try {
          const [micRes, camRes] = await Promise.all([
            navigator.permissions.query({
              name: "microphone" as PermissionName,
            }),
            navigator.permissions.query({ name: "camera" as PermissionName }),
          ]);
          micPermStatus = micRes;
          camPermStatus = camRes;
          micState = micRes.state;
          camState = camRes.state;
          permissionsApiSupported = true;

          const handlePermChange = () => {
            if (!isMounted) return;
            const nextMic = micPermStatus ? micPermStatus.state : micState;
            const nextCam = camPermStatus ? camPermStatus.state : camState;
            setMicPermission(nextMic);
            setCameraPermission(nextCam);
            if (nextMic === "granted" && nextCam === "granted") {
              setShowPermissionModal(false);
              setPermissionRequestError(null);
            } else {
              setShowPermissionModal(true);
            }
          };

          micRes.onchange = handlePermChange;
          camRes.onchange = handlePermChange;
        } catch {
          permissionsApiSupported = false;
        }
      }

      if (!permissionsApiSupported && navigator.mediaDevices?.enumerateDevices) {
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
          micState = hasMicLabel ? "granted" : "prompt";
          camState = hasCamLabel ? "granted" : "prompt";
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
    };

    evaluatePermissions();

    return () => {
      isMounted = false;
      if (micPermStatus) micPermStatus.onchange = null;
      if (camPermStatus) camPermStatus.onchange = null;
    };
  }, []);

  const handleRequestBrowserPermissions = useCallback(async () => {
    if (!navigator.mediaDevices?.getUserMedia) {
      setPermissionRequestError(
        "Your browser does not support camera and microphone access on this connection."
      );
      return;
    }

    setIsRequestingAccess(true);
    setPermissionRequestError(null);
    soundFX.vibrate(25);

    let micGranted = micPermission === "granted";
    let camGranted = cameraPermission === "granted";

    try {
      const combinedStream = await navigator.mediaDevices.getUserMedia({
        audio: {
          echoCancellation: true,
          noiseSuppression: true,
          autoGainControl: true,
        },
        video: {
          facingMode: { ideal: facingMode },
        },
      });

      combinedStream.getTracks().forEach((track) => track.stop());
      micGranted = true;
      camGranted = true;
      if (onMicGranted) onMicGranted();
    } catch {
      try {
        const audioStream = await navigator.mediaDevices.getUserMedia({
          audio: {
            echoCancellation: true,
            noiseSuppression: true,
            autoGainControl: true,
          },
        });
        audioStream.getTracks().forEach((track) => track.stop());
        micGranted = true;
        if (onMicGranted) onMicGranted();
      } catch {
        micGranted = false;
      }

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
        )} access has not been allowed yet. Please click "Allow" on your browser popup or enable permissions in your browser's address bar settings, then try again.`
      );
    }
  }, [cameraPermission, facingMode, micPermission, onMicGranted]);

  return {
    micPermission,
    cameraPermission,
    showPermissionModal,
    isRequestingAccess,
    permissionRequestError,
    handleRequestBrowserPermissions,
  };
}
