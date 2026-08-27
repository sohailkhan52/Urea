#!/bin/bash

# Warehouse Chat System - Development Setup Script
# This script sets up the chat system for development

set -e

echo "🚀 Setting up Warehouse Chat System..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Step 1: Check prerequisites
echo -e "\n${YELLOW}Step 1: Checking prerequisites...${NC}"

if ! command -v php &> /dev/null; then
    echo -e "${RED}❌ PHP not found${NC}"
    exit 1
fi

if ! command -v composer &> /dev/null; then
    echo -e "${RED}❌ Composer not found${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Prerequisites OK${NC}"

# Step 2: Update .env
echo -e "\n${YELLOW}Step 2: Configuring .env...${NC}"

if grep -q "BROADCAST_DRIVER=log" .env; then
    echo "Updating BROADCAST_DRIVER to websocket..."
    sed -i 's/BROADCAST_DRIVER=log/BROADCAST_DRIVER=websocket/g' .env
    echo -e "${GREEN}✅ Updated BROADCAST_DRIVER${NC}"
else
    echo -e "${YELLOW}⚠ BROADCAST_DRIVER already configured${NC}"
fi

# Step 3: Install WebSockets package
echo -e "\n${YELLOW}Step 3: Installing Laravel WebSockets package...${NC}"

if composer require beyondcode/laravel-websockets; then
    echo -e "${GREEN}✅ WebSockets package installed${NC}"
else
    echo -e "${RED}❌ Failed to install WebSockets${NC}"
    exit 1
fi

# Step 4: Publish WebSockets config
echo -e "\n${YELLOW}Step 4: Publishing WebSockets configuration...${NC}"

php artisan vendor:publish --provider="BeyondCode\LaravelWebSockets\WebSocketsServiceProvider"

# Step 5: Run migrations
echo -e "\n${YELLOW}Step 5: Running migrations...${NC}"

php artisan migrate

echo -e "${GREEN}✅ Migrations completed${NC}"

# Step 6: Initialize conversations
echo -e "\n${YELLOW}Step 6: Initializing warehouse conversations...${NC}"

php artisan chat:init-conversations

echo -e "${GREEN}✅ Conversations initialized${NC}"

# Step 7: Clear caches
echo -e "\n${YELLOW}Step 7: Clearing caches...${NC}"

php artisan cache:clear
php artisan config:cache
php artisan route:cache

echo -e "${GREEN}✅ Caches cleared${NC}"

# Summary
echo -e "\n${GREEN}✅ Setup Complete!${NC}\n"

echo -e "${YELLOW}Next steps:${NC}"
echo "1. Open two terminal windows"
echo "2. In terminal 1, run: php artisan serve"
echo "3. In terminal 2, run: php artisan websockets:serve"
echo "4. Visit http://localhost:8000/admin/chat"
echo "5. Login as Super Admin and Regular Admin in different browsers"
echo "6. Start chatting!"

echo -e "\n${YELLOW}Documentation:${NC}"
echo "- README: CHAT_SYSTEM_README.md"
echo "- Deployment: CHAT_DEPLOYMENT_COMPLETE.md"
echo "- Implementation: WAREHOUSE_CHAT_IMPLEMENTATION.md"
echo "- WebSocket Setup: WEBSOCKET_SETUP_GUIDE.md"

echo -e "\n${GREEN}Happy chatting! 💬${NC}\n"
