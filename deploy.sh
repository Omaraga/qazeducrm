#!/bin/bash

# ===========================================
# QazEduCRM Deploy Script for Ubuntu
# ===========================================

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}=========================================${NC}"
echo -e "${GREEN}   QazEduCRM Deployment Script${NC}"
echo -e "${GREEN}=========================================${NC}"

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}Please run as root (sudo ./deploy.sh)${NC}"
    exit 1
fi

# Check if .env exists
if [ ! -f .env ]; then
    echo -e "${YELLOW}Creating .env from .env.production...${NC}"
    cp .env.production .env
    echo -e "${RED}IMPORTANT: Edit .env file with your actual values!${NC}"
    echo -e "${RED}Run: nano .env${NC}"
    exit 1
fi

# Install Docker if not installed
if ! command -v docker &> /dev/null; then
    echo -e "${YELLOW}Installing Docker...${NC}"
    curl -fsSL https://get.docker.com | sh
    systemctl enable docker
    systemctl start docker
    echo -e "${GREEN}Docker installed successfully${NC}"
fi

# Install Docker Compose plugin if not installed
if ! docker compose version &> /dev/null; then
    echo -e "${YELLOW}Installing Docker Compose plugin...${NC}"
    apt-get update
    apt-get install -y docker-compose-plugin
    echo -e "${GREEN}Docker Compose installed successfully${NC}"
fi

# Create required directories
echo -e "${YELLOW}Creating directories...${NC}"
mkdir -p runtime web/assets docker/mysql/init

# Set permissions
echo -e "${YELLOW}Setting permissions...${NC}"
chmod -R 777 runtime web/assets

# Pull images
echo -e "${YELLOW}Pulling Docker images...${NC}"
docker compose pull

# Build PHP image
echo -e "${YELLOW}Building PHP image...${NC}"
docker compose build php

# Start containers
echo -e "${YELLOW}Starting containers...${NC}"
docker compose up -d

# Wait for MySQL to be ready
echo -e "${YELLOW}Waiting for MySQL to be ready...${NC}"
sleep 15

# Run migrations
echo -e "${YELLOW}Running database migrations...${NC}"
docker compose exec -T php php yii migrate --interactive=0

# Install composer dependencies (if needed)
echo -e "${YELLOW}Installing composer dependencies...${NC}"
docker compose exec -T php composer install --no-dev --optimize-autoloader

# Clear cache
echo -e "${YELLOW}Clearing cache...${NC}"
docker compose exec -T php php yii cache/flush-all || true

echo -e "${GREEN}=========================================${NC}"
echo -e "${GREEN}   Deployment completed!${NC}"
echo -e "${GREEN}=========================================${NC}"
echo ""
echo -e "CRM:      http://localhost"
echo -e "WhatsApp: http://localhost/whatsapp/docs"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo -e "1. Configure your domain DNS to point to this server"
echo -e "2. Set up SSL with: ./ssl-setup.sh yourdomain.com"
echo -e "3. Import your database dump if needed"
echo ""
docker compose ps
