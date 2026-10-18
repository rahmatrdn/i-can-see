import React from "react";
import { AuthProvider, useAuth } from "./features/auth/context/AuthContext";
import { LoginScreen } from "./features/auth/components/LoginScreen";
import { TopHeaderBar } from "./components/layout/TopHeaderBar";
import { CameraBackground } from "./features/camera/components/CameraBackground";
import { CenterAssistantOrb } from "./features/assistant/components/CenterAssistantOrb";
import { useAssistantController } from "./features/assistant/hooks/useAssistantController";
import { useMediaPermissions } from "./features/permissions/hooks/useMediaPermissions";
import { PermissionModal } from "./features/permissions/components/PermissionModal";

function AssistantApp() {
  const { user, logout } = useAuth();

  const {
    phase,
    statusMessage,
    subStatusText,
    liveTranscript,
    micLevel,
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
  } = useAssistantController();

  const {
    micPermission,
    cameraPermission,
    showPermissionModal,
    isRequestingAccess,
    permissionRequestError,
    isInsecureContext,
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

          {/* Compact Mobile Top App Bar with User Profile and Logout */}
          <TopHeaderBar
            facingMode={facingMode}
            onToggleFacingMode={handleToggleFacingMode}
            micPermission={micPermission}
            cameraPermission={cameraPermission}
            onOpenPermissionsModal={openPermissionModal}
            user={user}
            onLogout={logout}
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

            {/* Pure Circular Center Orb */}
            <CenterAssistantOrb
              phase={phase}
              statusMessage={statusMessage}
              subStatusText={subStatusText}
              liveTranscript={liveTranscript}
              micLevel={micLevel}
              onCenterClick={handleCenterCircleClick}
            />
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
            onClose={closePermissionModal}
          />
        </section>
      </main>
    </div>
  );
}

function MainAppRouter() {
  const { isAuthenticated, isLoading } = useAuth();

  if (isLoading) {
    return (
      <div className="min-h-screen bg-[#F1F5F9] text-[#233053] flex items-center justify-center">
        <div className="flex flex-col items-center gap-4">
          <div className="relative">
            <div className="absolute -inset-3 bg-[#72ADD9]/30 rounded-full blur-lg animate-pulse" />
            <img
              src="/logo-icansee.png"
              alt="iCanSee Loading"
              className="relative h-16 w-auto object-contain animate-bounce drop-shadow"
            />
          </div>
          <div className="flex items-center gap-2 text-sm font-bold text-slate-600">
            <svg
              className="animate-spin h-4 w-4 text-[#517DA8]"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
            >
              <circle
                className="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                strokeWidth="4"
              />
              <path
                className="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8v8H4z"
              />
            </svg>
            <span>Verifying iCanSee session...</span>
          </div>
        </div>
      </div>
    );
  }

  if (!isAuthenticated) {
    return <LoginScreen />;
  }

  return <AssistantApp />;
}

export default function App() {
  return (
    <AuthProvider>
      <MainAppRouter />
    </AuthProvider>
  );
}
