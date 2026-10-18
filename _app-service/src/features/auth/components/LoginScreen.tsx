import React, { useState } from "react";
import { User, Lock, Eye, EyeOff, LogIn, AlertCircle, Sparkles, ShieldCheck } from "lucide-react";
import { useAuth } from "../context/AuthContext";

export const LoginScreen: React.FC = () => {
  const { login } = useAuth();
  const [username, setUsername] = useState("");
  const [password, setPassword] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!username.trim() || !password) {
      setErrorMessage("Please enter your username or email and password.");
      return;
    }

    setErrorMessage(null);
    setIsSubmitting(true);

    try {
      const res = await login({
        username: username.trim(),
        password,
      });

      if (!res.success) {
        setErrorMessage(res.message || "Failed to sign in. Please verify your credentials.");
      }
    } catch (err: any) {
      setErrorMessage(err?.message || "An unexpected error occurred while connecting to the server.");
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleFillDemo = (demoUser: string, demoPass: string) => {
    setUsername(demoUser);
    setPassword(demoPass);
    setErrorMessage(null);
  };

  return (
    <div className="min-h-screen bg-[#F1F5F9] text-[#233053] flex flex-col justify-between selection:bg-[#72ADD9]/30 selection:text-[#233053]">
      {/* Main Responsive Container */}
      <main className="flex-1 w-full max-w-7xl mx-auto flex flex-col lg:flex-row items-center justify-center lg:px-8 lg:py-6">
        <section
          aria-label="iCanSee Sign In Screen"
          className="relative w-full h-[100dvh] lg:h-[840px] lg:max-h-[90vh] lg:w-[412px] lg:shrink-0 bg-[#FFFFFF] lg:rounded-[48px] lg:border-[10px] lg:border-[#E2E8F0] lg:shadow-[0_25px_70px_rgba(35,48,83,0.14)] overflow-y-auto flex flex-col justify-between p-6 sm:p-8"
        >
          {/* Hardware Cutout for Desktop Simulation */}
          <div
            aria-hidden="true"
            className="hidden lg:flex items-center justify-center -mt-4 pb-4"
          >
            <div className="w-24 h-1.5 rounded-full bg-[#CBD5E1]" />
          </div>

          {/* Header Brand Section */}
          <div className="flex flex-col items-center text-center mt-8 pt-6 sm:pt-4">
            <div className="relative mb-3">
              <div className="absolute -inset-2 bg-gradient-to-r from-[#72ADD9]/30 to-[#517DA8]/30 rounded-full blur-md opacity-70 animate-pulse" />
              <img
                src="/logo-icansee.png"
                alt="iCanSee Logo"
                className="relative h-14 w-auto object-contain select-none drop-shadow-sm"
              />
            </div>

            <p className="text-sm font-medium text-slate-500 mt-1 max-w-[280px]">
              Smart Voice & Vision Assistant for the Visually Impaired
            </p>
          </div>

          {/* Login Form */}
          <form
            onSubmit={handleSubmit}
            className="my-auto flex flex-col gap-4 py-4"
            noValidate
          >
            {/* Error Notification */}
            {errorMessage && (
              <div
                role="alert"
                aria-live="assertive"
                className="flex items-start gap-2.5 p-3.5 bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm rounded-2xl animate-in fade-in slide-in-from-top-2 duration-200"
              >
                <AlertCircle className="w-5 h-5 text-rose-600 shrink-0 mt-0.5" />
                <span className="font-semibold leading-snug">{errorMessage}</span>
              </div>
            )}

            {/* Username / Email Field */}
            <div className="flex flex-col gap-1.5 text-left">
              <label
                htmlFor="username"
                className="text-xs font-bold uppercase tracking-wider text-slate-600 ml-1"
              >
                Username
              </label>
              <div className="relative flex items-center">
                <div className="absolute left-3.5 text-slate-400 pointer-events-none">
                  <User className="w-5 h-5" />
                </div>
                <input
                  id="username"
                  name="username"
                  type="text"
                  autoComplete="username"
                  required
                  value={username}
                  onChange={(e) => setUsername(e.target.value)}
                  placeholder="budi12 or username"
                  className="w-full h-12 pl-11 pr-4 rounded-2xl bg-[#F8FAFC] border border-slate-200 text-[#233053] font-medium placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#517DA8] focus:border-transparent transition-all shadow-inner text-sm"
                />
              </div>
            </div>

            {/* Password Field */}
            <div className="flex flex-col gap-1.5 text-left">
              <label
                htmlFor="password"
                className="text-xs font-bold uppercase tracking-wider text-slate-600 ml-1"
              >
                Password
              </label>
              <div className="relative flex items-center">
                <div className="absolute left-3.5 text-slate-400 pointer-events-none">
                  <Lock className="w-5 h-5" />
                </div>
                <input
                  id="password"
                  name="password"
                  type={showPassword ? "text" : "password"}
                  autoComplete="current-password"
                  required
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  placeholder="Enter your password..."
                  className="w-full h-12 pl-11 pr-11 rounded-2xl bg-[#F8FAFC] border border-slate-200 text-[#233053] font-medium placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#517DA8] focus:border-transparent transition-all shadow-inner text-sm"
                />
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  aria-label={showPassword ? "Hide password" : "Show password"}
                  className="absolute right-3.5 text-slate-400 hover:text-slate-600 focus:outline-none p-1 transition-colors cursor-pointer"
                >
                  {showPassword ? (
                    <EyeOff className="w-5 h-5" />
                  ) : (
                    <Eye className="w-5 h-5" />
                  )}
                </button>
              </div>
            </div>

            {/* Submit Button */}
            <button
              type="submit"
              disabled={isSubmitting}
              className="mt-2 w-full h-13 rounded-2xl bg-gradient-to-r from-[#233053] to-[#344673] hover:from-[#1b2642] hover:to-[#29385c] text-white font-bold flex items-center justify-center gap-2 text-base shadow-[0_8px_20px_rgba(35,48,83,0.25)] active:scale-[0.98] transition-all cursor-pointer disabled:opacity-70 disabled:cursor-not-allowed"
            >
              {isSubmitting ? (
                <>
                  <svg
                    className="animate-spin h-5 w-5 text-white"
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
                  <span>Signing in...</span>
                </>
              ) : (
                <>
                  <LogIn className="w-5 h-5" />
                  <span>Sign In to App</span>
                </>
              )}
            </button>

            {/* Quick Demo Credentials Autofill Pill */}
            <div className="mt-2 p-3 bg-slate-50 border border-slate-200/80 rounded-2xl text-left">
              <div className="flex items-center gap-1.5 text-xs font-bold text-slate-600 mb-1.5">
                <Sparkles className="w-3.5 h-3.5 text-[#517DA8]" />
                <span>Quick Demo Account:</span>
              </div>
              <button
                type="button"
                onClick={() => handleFillDemo("budi12", "asdasd")}
                className="w-full text-left text-xs bg-white hover:bg-slate-100 border border-slate-200 rounded-xl px-2.5 py-1.5 font-medium text-slate-700 flex items-center justify-between transition-colors cursor-pointer"
              >
                <span>
                  User: <strong className="text-[#233053]">budi12</strong> (Pass: asdasd)
                </span>
                <span className="text-[10px] font-bold text-[#517DA8] underline">
                  Use Demo
                </span>
              </button>
            </div>
          </form>

        </section>
      </main>
    </div>
  );
};
