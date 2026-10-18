import React, { createContext, useContext, useState, useEffect, ReactNode } from "react";
import { User, LoginCredentials, AuthContextType } from "../types/auth";
import { loginApi, getMeApi, logoutApi } from "../api/authClient";

const TOKEN_KEY = "icansee_auth_token";
const USER_KEY = "icansee_auth_user";

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
  const [user, setUser] = useState<User | null>(() => {
    try {
      const saved = localStorage.getItem(USER_KEY);
      return saved ? JSON.parse(saved) : null;
    } catch {
      return null;
    }
  });

  const [token, setToken] = useState<string | null>(() => {
    return localStorage.getItem(TOKEN_KEY);
  });

  const [isLoading, setIsLoading] = useState<boolean>(true);

  // Validate existing session on initial load
  useEffect(() => {
    let isMounted = true;

    async function checkSession() {
      const savedToken = localStorage.getItem(TOKEN_KEY);
      if (!savedToken) {
        if (isMounted) setIsLoading(false);
        return;
      }

      try {
        const res = await getMeApi(savedToken);
        if (isMounted) {
          if (res.success && res.user) {
            setUser(res.user);
            localStorage.setItem(USER_KEY, JSON.stringify(res.user));
          } else {
            // Token expired or invalid
            setUser(null);
            setToken(null);
            localStorage.removeItem(TOKEN_KEY);
            localStorage.removeItem(USER_KEY);
          }
        }
      } catch (err) {
        console.warn("Session check error:", err);
      } finally {
        if (isMounted) setIsLoading(false);
      }
    }

    checkSession();

    return () => {
      isMounted = false;
    };
  }, []);

  const login = async (credentials: LoginCredentials) => {
    const res = await loginApi(credentials);

    if (res.success && res.token && res.user) {
      setToken(res.token);
      setUser(res.user);
      localStorage.setItem(TOKEN_KEY, res.token);
      localStorage.setItem(USER_KEY, JSON.stringify(res.user));
      return { success: true, message: res.message };
    }

    return {
      success: false,
      message: res.message || "Failed to sign in.",
    };
  };

  const logout = () => {
    logoutApi();
    setToken(null);
    setUser(null);
    localStorage.removeItem(TOKEN_KEY);
    localStorage.removeItem(USER_KEY);
  };

  return (
    <AuthContext.Provider
      value={{
        user,
        token,
        isAuthenticated: Boolean(token && user),
        isLoading,
        login,
        logout,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
};

export function useAuth(): AuthContextType {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error("useAuth must be used within an AuthProvider");
  }
  return context;
}
