import React from "react";

interface CameraBackgroundProps {
  videoRef: React.RefObject<HTMLVideoElement | null>;
  cameraActive: boolean;
  lastCapturedImage: string | null;
}

export const CameraBackground: React.FC<CameraBackgroundProps> = ({
  videoRef,
  cameraActive,
  lastCapturedImage,
}) => {
  return (
    <div className="absolute inset-0 z-0 bg-[#F8FAFC]">
      <video
        ref={videoRef}
        autoPlay
        playsInline
        muted
        className={`w-full h-full object-cover transition-opacity duration-300 ${
          cameraActive ? "opacity-95" : "opacity-0 pointer-events-none"
        }`}
      />
      {!cameraActive && lastCapturedImage && (
        <img
          src={lastCapturedImage}
          alt="Last captured camera photo"
          referrerPolicy="no-referrer"
          className="w-full h-full object-cover opacity-20 blur-[2px]"
        />
      )}
      <div
        className={`absolute inset-0 transition-opacity duration-300 ${
          cameraActive
            ? "bg-gradient-to-b from-white/75 via-white/30 to-white/85"
            : "bg-gradient-to-b from-white/95 via-[#F8FAFC]/90 to-white/95"
        }`}
      />
    </div>
  );
};
