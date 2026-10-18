ARG SERVICE_DIR=.

# Stage 1: Build stage
FROM oven/bun:1.4.3-slim AS builder

ARG SERVICE_DIR=.

WORKDIR /app

# Copy dependency definition (wildcard ensures build will not fail if bun.lock or package-lock.json is absent)
COPY ${SERVICE_DIR}/package.json ${SERVICE_DIR}/bun.lock* ${SERVICE_DIR}/package-lock.json* ./

# Install all dependencies (including devDependencies required for vite build)
RUN bun install

# Copy source code and assets
COPY ${SERVICE_DIR}/ .

# Build the frontend production bundle
RUN bun run build

# Stage 2: Production runtime stage
FROM oven/bun:1.4.3-slim AS runner

ARG SERVICE_DIR=.

WORKDIR /app

ENV NODE_ENV=production
ENV PORT=3000

# Install production dependencies only
COPY ${SERVICE_DIR}/package.json ${SERVICE_DIR}/bun.lock* ${SERVICE_DIR}/package-lock.json* ./
RUN bun install --production

# Copy compiled frontend from builder
COPY --from=builder /app/dist ./dist

# Copy backend server, server modules, and static public assets
COPY ${SERVICE_DIR}/server.ts ./
COPY ${SERVICE_DIR}/server ./server
COPY ${SERVICE_DIR}/public ./public
COPY ${SERVICE_DIR}/tsconfig.json* ./

# Expose default port
EXPOSE 3000

# Start production server using bun
CMD ["bun", "run", "start"]

