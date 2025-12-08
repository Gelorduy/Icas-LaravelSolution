#!/bin/bash

# ICAS Docker Startup Script
# This script starts all ICAS services including MySQL

set -e

echo "🚀 Starting ICAS Docker Environment..."
echo ""

# Navigate to docker directory
cd "$(dirname "$0")"

# Start all services with MySQL profile
echo "📦 Starting all containers..."
docker compose -f docker-compose.dev.yml --profile mysql up -d

echo ""
echo "⏳ Waiting for services to be ready..."
sleep 3

# Check service status
echo ""
echo "📊 Service Status:"
docker compose -f docker-compose.dev.yml ps

echo ""
echo "✅ ICAS is ready!"
echo ""
echo "🌐 Access the application at: https://icas.local"
echo "👤 Admin login: admin@icas.local / inmate.2025"
echo "🗄️  MySQL: localhost:3306 (root/root)"
echo ""
echo "📝 Useful commands:"
echo "   Stop all:    ./stop.sh"
echo "   View logs:   docker compose -f docker-compose.dev.yml logs -f"
echo "   Rebuild:     docker compose -f docker-compose.dev.yml up -d --build"
echo ""
