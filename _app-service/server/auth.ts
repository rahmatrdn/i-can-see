import { Request, Response } from "express";
import bcrypt from "bcryptjs";
import jwt from "jsonwebtoken";
import { pool } from "./db.ts";

const JWT_SECRET = process.env.JWT_SECRET || "icansee_jwt_default_secret_key_2026";
const TOKEN_EXPIRY = "7d";

export interface UserRow {
  id: number;
  name: string;
  email: string;
  password?: string;
  access_type: number | null;
  is_active: number;
  deleted_at: string | null;
}

export interface AuthenticatedUser {
  id: number;
  name: string;
  email: string;
  access_type: number | null;
}

export function generateToken(user: AuthenticatedUser): string {
  return jwt.sign(
    {
      id: user.id,
      name: user.name,
      email: user.email,
      access_type: user.access_type,
    },
    JWT_SECRET,
    { expiresIn: TOKEN_EXPIRY }
  );
}

export function verifyToken(token: string): AuthenticatedUser | null {
  try {
    const decoded = jwt.verify(token, JWT_SECRET) as AuthenticatedUser;
    return decoded;
  } catch (err) {
    return null;
  }
}

/**
 * POST /api/auth/login
 * Body: { username, password }
 * Checks MySQL `users` table where email = username OR name = username
 */
export async function handleLogin(req: Request, res: Response) {
  try {
    const { username, password } = req.body;

    if (!username || !password) {
      return res.status(400).json({
        success: false,
        message: "Username/email and password are required.",
      });
    }

    const trimmedUsername = String(username).trim();
    const plainPassword = String(password);

    // Query user by email or name (ignoring soft-deleted records)
    const [rows] = await pool.query<any[]>(
      `SELECT id, name, email, password, access_type, is_active, deleted_at 
       FROM users 
       WHERE (email = ? OR name = ?) 
       ORDER BY (email = ?) DESC 
       LIMIT 1`,
      [trimmedUsername, trimmedUsername, trimmedUsername]
    );

    if (!rows || rows.length === 0) {
      return res.status(401).json({
        success: false,
        message: "User not found or invalid credentials.",
      });
    }

    const user: UserRow = rows[0];

    // Check soft delete
    if (user.deleted_at) {
      return res.status(403).json({
        success: false,
        message: "This account has been deactivated or removed.",
      });
    }

    // Check active status
    if (Number(user.is_active) !== 1) {
      return res.status(403).json({
        success: false,
        message: "Your account is currently inactive. Please contact the administrator.",
      });
    }

    const storedHash = user.password || "";
    // Bcrypt comparison: handles standard bcrypt hashes including $2y$ from PHP Laravel
    let passwordMatches = await bcrypt.compare(plainPassword, storedHash).catch(() => false);

    // Fallback comparison for $2y$ compatibility if needed
    if (!passwordMatches && storedHash.startsWith("$2y$")) {
      const normalizedHash = storedHash.replace(/^\$2y\$/, "$2a$");
      passwordMatches = await bcrypt.compare(plainPassword, normalizedHash).catch(() => false);
    }

    if (!passwordMatches) {
      return res.status(401).json({
        success: false,
        message: "Incorrect username or password.",
      });
    }

    const authUser: AuthenticatedUser = {
      id: user.id,
      name: user.name,
      email: user.email,
      access_type: user.access_type,
    };

    const token = generateToken(authUser);

    return res.status(200).json({
      success: true,
      message: "Sign in successful. Welcome back!",
      token,
      user: authUser,
    });
  } catch (error: any) {
    console.error("Error during handleLogin:", error);
    return res.status(500).json({
      success: false,
      message: "An error occurred with the server or MySQL database connection.",
      error: process.env.NODE_ENV === "development" ? error?.message : undefined,
    });
  }
}

/**
 * GET /api/auth/me
 * Headers: { Authorization: "Bearer <token>" }
 */
export async function handleGetCurrentUser(req: Request, res: Response) {
  try {
    const authHeader = req.headers.authorization;
    if (!authHeader || !authHeader.startsWith("Bearer ")) {
      return res.status(401).json({
        success: false,
        message: "Authentication token missing.",
      });
    }

    const token = authHeader.substring(7).trim();
    const payload = verifyToken(token);

    if (!payload) {
      return res.status(401).json({
        success: false,
        message: "Session expired or invalid. Please sign in again.",
      });
    }

    // Validate that user still exists and is active in DB
    const [rows] = await pool.query<any[]>(
      `SELECT id, name, email, access_type, is_active, deleted_at 
       FROM users 
       WHERE id = ? AND deleted_at IS NULL 
       LIMIT 1`,
      [payload.id]
    );

    if (!rows || rows.length === 0 || Number(rows[0].is_active) !== 1) {
      return res.status(401).json({
        success: false,
        message: "User account is inactive or has been deleted.",
      });
    }

    const dbUser = rows[0];
    const user: AuthenticatedUser = {
      id: dbUser.id,
      name: dbUser.name,
      email: dbUser.email,
      access_type: dbUser.access_type,
    };

    return res.status(200).json({
      success: true,
      user,
    });
  } catch (error: any) {
    console.error("Error in handleGetCurrentUser:", error);
    return res.status(500).json({
      success: false,
      message: "Failed to verify user session.",
    });
  }
}

/**
 * POST /api/auth/logout
 */
export function handleLogout(_req: Request, res: Response) {
  return res.status(200).json({
    success: true,
    message: "Successfully signed out.",
  });
}
