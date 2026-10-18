import { LoginCredentials, AuthResponse, User } from "../types/auth";

export async function loginApi(credentials: LoginCredentials): Promise<AuthResponse> {
  try {
    const response = await fetch("/api/auth/login", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(credentials),
    });

    const data = await response.json();
    return data;
  } catch (error: any) {
    return {
      success: false,
      message: error?.message || "Failed to connect to the authentication server. Please ensure the server is online.",
    };
  }
}

export async function getMeApi(token: string): Promise<{ success: boolean; user?: User; message?: string }> {
  try {
    const response = await fetch("/api/auth/me", {
      method: "GET",
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });

    const data = await response.json();
    return data;
  } catch (error: any) {
    return {
      success: false,
      message: error?.message || "Failed to verify session token.",
    };
  }
}

export async function logoutApi(): Promise<void> {
  try {
    await fetch("/api/auth/logout", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
    });
  } catch {
    // Non-blocking logout call
  }
}
