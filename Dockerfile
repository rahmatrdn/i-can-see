# Stage 1: Build stage
FROM oven/bun:1.4.3-slim AS builder

WORKDIR /app

# Copy dependency definition
COPY package.json ./

# Install all dependencies (including devDependencies required for vite build)
RUN bun install

# Copy source code and assets
COPY . .

# Build the frontend production bundle
RUN bun run build

# Stage 2: Production runtime stage
FROM oven/bun:1.4.3-slim AS runner

WORKDIR /app

ENV NODE_ENV=production
ENV PORT=3000

# Install production dependencies only
COPY package.json ./
RUN bun install --production

# Copy compiled frontend from builder
COPY --from=builder /app/dist ./dist

# Copy backend server and static public assets
COPY server.ts ./
COPY public ./public

# Expose default port
EXPOSE 3000

# Start production server using bun
CMD ["bun", "run", "start"]
