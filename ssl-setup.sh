#!/bin/bash

# ===========================================
# SSL Setup Script using Let's Encrypt
# ===========================================

set -e

DOMAIN=$1

if [ -z "$DOMAIN" ]; then
    echo "Usage: ./ssl-setup.sh yourdomain.com"
    exit 1
fi

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${YELLOW}Setting up SSL for ${DOMAIN}...${NC}"

# Install certbot
if ! command -v certbot &> /dev/null; then
    apt-get update
    apt-get install -y certbot
fi

# Stop nginx temporarily
docker compose stop nginx

# Get certificate
certbot certonly --standalone -d $DOMAIN --non-interactive --agree-tos --email admin@$DOMAIN

# Copy certificates to docker volume
mkdir -p docker/nginx/ssl
cp /etc/letsencrypt/live/$DOMAIN/fullchain.pem docker/nginx/ssl/
cp /etc/letsencrypt/live/$DOMAIN/privkey.pem docker/nginx/ssl/

# Create SSL nginx config
cat > docker/nginx/default.conf << 'NGINXEOF'
# HTTP -> HTTPS redirect
server {
    listen 80;
    listen [::]:80;
    server_name DOMAIN_PLACEHOLDER;

    location /.well-known/acme-challenge/ {
        root /var/www/certbot;
    }

    location / {
        return 301 https://$host$request_uri;
    }
}

# HTTPS server
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name DOMAIN_PLACEHOLDER;

    ssl_certificate /etc/nginx/ssl/fullchain.pem;
    ssl_certificate_key /etc/nginx/ssl/privkey.pem;

    # SSL configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_prefer_server_ciphers on;
    ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;

    root /var/www/html/web;
    index index.php;

    charset utf-8;
    client_max_body_size 50M;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # Gzip
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/javascript application/javascript application/json application/xml;

    # Static files
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # Yii2
    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass php:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    # WhatsApp API
    location /whatsapp/ {
        proxy_pass http://evolution-api:8080/;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    location ~ /\. { deny all; }
    location ~* (composer\.json|composer\.lock|\.env|\.git) { deny all; }

    access_log /var/log/nginx/qazeducrm_access.log;
    error_log /var/log/nginx/qazeducrm_error.log;
}
NGINXEOF

# Replace domain placeholder
sed -i "s/DOMAIN_PLACEHOLDER/$DOMAIN/g" docker/nginx/default.conf

# Update .env with domain
sed -i "s/EVOLUTION_SERVER_URL=.*/EVOLUTION_SERVER_URL=https:\/\/$DOMAIN\/whatsapp/" .env

# Start nginx
docker compose up -d nginx

# Setup auto-renewal cron
echo "0 0 1 * * certbot renew --quiet && cp /etc/letsencrypt/live/$DOMAIN/*.pem /opt/qazeducrm/docker/nginx/ssl/ && docker compose -f /opt/qazeducrm/docker-compose.yml restart nginx" | crontab -

echo -e "${GREEN}SSL setup completed!${NC}"
echo -e "Your site is now available at: https://$DOMAIN"
