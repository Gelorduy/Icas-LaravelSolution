#!/bin/bash

# ICAS Docker Stop Script
# This script stops all ICAS services

set -e

echo "🛑 Stopping ICAS Docker Environment..."
echo ""

# Navigate to docker directory
cd "$(dirname "$0")"

# Stop all services including MySQL
echo "📦 Stopping all containers..."
sudo docker compose -f docker-compose.dev.yml --profile mysql down

echo ""
echo "✅ All services stopped!"
echo ""
