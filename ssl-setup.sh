#!/bin/bash

# ===========================================
# SSL Setup Script using Let's Encrypt
# ===========================================
# This script obtains SSL certificates for domains
# without overwriting the nginx configuration.
#
# Usage:
#   ./ssl-setup.sh crm.qazaq.education
#   ./ssl-setup.sh crm.education
#   ./ssl-setup.sh evo.qazaq.education
#
# The nginx config should already have server blocks
# configured for the domain with correct SSL paths.
# ===========================================

set -e

DOMAIN=$1
EMAIL=${2:-"admin@qazaq.education"}

if [ -z "$DOMAIN" ]; then
    echo "Usage: ./ssl-setup.sh domain.com [email@example.com]"
    echo ""
    echo "Examples:"
    echo "  ./ssl-setup.sh crm.qazaq.education"
    echo "  ./ssl-setup.sh crm.education admin@crm.education"
    exit 1
fi

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${YELLOW}Setting up SSL certificate for ${DOMAIN}...${NC}"

# Check if docker compose is running
if ! docker compose ps | grep -q "nginx"; then
    echo -e "${RED}Error: nginx container is not running. Start it first with 'docker compose up -d'${NC}"
    exit 1
fi

# Create certbot webroot directory
mkdir -p docker/nginx/ssl/live/$DOMAIN

# Create certbot webroot volume directory if needed
docker compose exec nginx mkdir -p /var/www/certbot/.well-known/acme-challenge 2>/dev/null || true

echo -e "${YELLOW}Obtaining certificate via webroot method...${NC}"

# Get certificate using webroot method (nginx keeps running)
docker run --rm \
    -v "$(pwd)/docker/nginx/ssl:/etc/letsencrypt" \
    -v "certbot_webroot:/var/www/certbot" \
    certbot/certbot certonly \
    --webroot \
    -w /var/www/certbot \
    -d $DOMAIN \
    --email $EMAIL \
    --agree-tos \
    --non-interactive \
    --key-type ecdsa

if [ $? -ne 0 ]; then
    echo -e "${RED}Failed to obtain certificate. Trying standalone method...${NC}"

    # Stop nginx temporarily
    docker compose stop nginx

    # Get certificate using standalone method
    docker run --rm \
        -p 80:80 \
        -v "$(pwd)/docker/nginx/ssl:/etc/letsencrypt" \
        certbot/certbot certonly \
        --standalone \
        -d $DOMAIN \
        --email $EMAIL \
        --agree-tos \
        --non-interactive \
        --key-type ecdsa

    # Start nginx again
    docker compose up -d nginx
fi

# Verify certificate was created
if [ -f "docker/nginx/ssl/live/$DOMAIN/fullchain.pem" ]; then
    echo -e "${GREEN}Certificate obtained successfully!${NC}"
else
    echo -e "${RED}Certificate files not found at expected location.${NC}"
    exit 1
fi

# Reload nginx to pick up new certificate
echo -e "${YELLOW}Reloading nginx...${NC}"
docker compose exec nginx nginx -s reload

# Create renewal config
mkdir -p docker/nginx/ssl/renewal
cat > docker/nginx/ssl/renewal/$DOMAIN.conf << EOF
# Options used in the renewal process
[renewalparams]
account = # will be filled by certbot
authenticator = webroot
server = https://acme-v02.api.letsencrypt.org/directory
webroot_path = /var/www/certbot
key_type = ecdsa

[[webroot_map]]
$DOMAIN = /var/www/certbot
EOF

echo -e "${GREEN}SSL setup completed for ${DOMAIN}!${NC}"
echo ""
echo "Certificate location: docker/nginx/ssl/live/$DOMAIN/"
echo ""
echo -e "${YELLOW}Don't forget to set up auto-renewal cron job:${NC}"
echo ""
echo "# Add to crontab (crontab -e):"
echo "0 0 1 * * cd /opt/qazeducrm && docker run --rm -v \$(pwd)/docker/nginx/ssl:/etc/letsencrypt -v certbot_webroot:/var/www/certbot certbot/certbot renew --quiet && docker compose exec nginx nginx -s reload"
echo ""
echo "Your site should now be available at: https://$DOMAIN"
