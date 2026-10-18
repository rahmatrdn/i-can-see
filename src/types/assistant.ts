export type InteractionPhase =
  | "IDLE"
  | "ASKING_INTENT"
  | "LISTENING_VOICE"
  | "AIMING_CAMERA"
  | "CAPTURING_CAMERA"
  | "PROCESSING_AI"
  | "SPEAKING_RESPONSE";

export type DevicePermissionState = "checking" | "granted" | "prompt" | "denied";

export interface DetectedObject {
  name: string;
  position: string;
}

export interface InteractionLog {
  id: string;
  timestamp: string;
  intentUsed: "camera" | "qa";
  userTranscript: string;
  summaryTitle: string;
  spokenResponse: string;
  detectedObjects: DetectedObject[];
  safetyNote?: string;
  snapshotDataUrl?: string;
}

export interface RipplePoint {
  id: number;
  x: number;
  y: number;
}

export interface OrbThemeConfig {
  waveBorder: string;
  waveBg: string;
  auraStyle: string;
  coreStyle: string;
  coreShadow: string;
  textColor: string;
  badgeBg: string;
  barColor: string;
}
