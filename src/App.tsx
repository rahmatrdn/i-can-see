import React from "react";
import { TopHeaderBar } from "./components/layout/TopHeaderBar";
import { StatusBanner } from "./components/layout/StatusBanner";
import { CameraBackground } from "./features/camera/components/CameraBackground";
import { CenterAssistantOrb } from "./features/assistant/components/CenterAssistantOrb";
import { useAssistantController } from "./features/assistant/hooks/useAssistantController";
import { useMediaPermissions } from "./features/permissions/hooks/useMediaPermissions";
import { PermissionModal } from "./features/permissions/components/PermissionModal";

export default function App() {
  const {
    phase,
    statusMessage,
    subStatusText,
    liveTranscript,
    micLevel,
    micError,
    cameraActive,
    facingMode,
    lastCapturedImage,
    ripples,
    videoRef,
    canvasRef,
    triggerScreenGestureAction,
    handleUniversalCanvasTap,
    handleCenterCircleClick,
    handleToggleFacingMode,
    clearMicError,
    triggerMicrophonePermissionRequest,
  } = useAssistantController();

  const {
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
  } = useMediaPermissions({
    facingMode,
    onMicGranted: clearMicError,
  });

  return (
    <div className="min-h-screen bg-[#F1F5F9] text-[#233053] flex flex-col justify-between selection:bg-[#72ADD9]/30 selection:text-[#233053]">
      {/* Hidden offscreen canvas for capturing camera frames */}
      <canvas ref={canvasRef} className="hidden" />

      {/* Main Responsive Layout: Fullscreen on Mobile Devices, Centered Phone Frame on Desktop */}
      <main className="flex-1 w-full max-w-7xl mx-auto flex flex-col lg:flex-row items-center justify-center lg:px-8 lg:py-6">
        <section
          aria-label="iCanSee Main Screen"
          className="relative w-full h-[100dvh] lg:h-[840px] lg:max-h-[90vh] lg:w-[412px] lg:shrink-0 bg-[#FFFFFF] lg:rounded-[48px] lg:border-[10px] lg:border-[#E2E8F0] lg:shadow-[0_25px_70px_rgba(35,48,83,0.14)] overflow-hidden flex flex-col select-none"
        >
          {/* Desktop Hardware Speaker Cutout */}
          <div
            aria-hidden="true"
            className="hidden lg:flex items-center justify-center pt-2 pb-1 bg-[#FFFFFF] z-30"
          >
            <div className="w-24 h-1.5 rounded-full bg-[#CBD5E1]" />
          </div>

          {/* Background Layer: Live Camera Stream / Snapshot */}
          <CameraBackground
            videoRef={videoRef}
            cameraActive={cameraActive}
            lastCapturedImage={lastCapturedImage}
          />

          {/* Compact Mobile Top App Bar */}
          <TopHeaderBar
            facingMode={facingMode}
            onToggleFacingMode={handleToggleFacingMode}
            micPermission={micPermission}
            cameraPermission={cameraPermission}
            onOpenPermissionsModal={openPermissionModal}
          />

          {/* Universal Touch Canvas: 1x Tap for Voice, 2x Double-Tap for Camera Shortcut */}
          <div
            role="button"
            tabIndex={0}
            aria-live="assertive"
            aria-label={`${statusMessage}. ${subStatusText}.`}
            onClick={handleUniversalCanvasTap}
            onKeyDown={(e) => {
              if (e.key === "Enter" || e.key === " ") {
                e.preventDefault();
                triggerScreenGestureAction();
              }
            }}
            className="relative z-10 flex-1 flex flex-col justify-between p-5 pb-8 cursor-pointer focus:outline-none focus-visible:ring-4 focus-visible:ring-[#517DA8]"
          >
            {/* Touch Ripple Animations */}
            {ripples.map((r) => (
              <span
                key={r.id}
                style={{ left: r.x - 48, top: r.y - 48 }}
                className="pointer-events-none absolute w-24 h-24 rounded-full border-4 border-[#517DA8] bg-[#72ADD9]/25 animate-ripple"
              />
            ))}

            {/* Top Status Banner */}
            <StatusBanner
              cameraActive={cameraActive}
              phase={phase}
              micError={micError}
              onRequestMic={triggerMicrophonePermissionRequest}
              onOpenPermissionsModal={openPermissionModal}
            />

            {/* Pure Circular Center Orb */}
            <CenterAssistantOrb
              phase={phase}
              statusMessage={statusMessage}
              subStatusText={subStatusText}
              liveTranscript={liveTranscript}
              micLevel={micLevel}
              onCenterClick={handleCenterCircleClick}
            />

            {/* Bottom Instruction Caption */}
            <div className="text-center pt-2">
              <p className="text-xs font-mono font-semibold text-[#354C6E] tracking-wide">
                {phase === "IDLE"
                  ? "TAP 1x FOR VOICE · DOUBLE-TAP (2x) TO TAKE PICTURE"
                  : phase === "AIMING_CAMERA"
                  ? "TAP 1x ANYWHERE ON SCREEN WHEN READY TO CAPTURE"
                  : "TAP 1x TO STOP AI · DOUBLE-TAP (2x) TO TAKE PICTURE"}
              </p>
            </div>
          </div>

          {/* Startup Microphone & Camera Permission Popup Modal */}
          <PermissionModal
            isOpen={showPermissionModal}
            micPermission={micPermission}
            cameraPermission={cameraPermission}
            isRequestingAccess={isRequestingAccess}
            permissionRequestError={permissionRequestError}
            isInsecureContext={isInsecureContext}
            onRequestPermissions={handleRequestBrowserPermissions}
            onRequestMicOnly={handleRequestMicPermission}
            onRequestCameraOnly={handleRequestCameraPermission}
            onClose={closePermissionModal}
          />
        </section>
      </main>
    </div>
  );
}
