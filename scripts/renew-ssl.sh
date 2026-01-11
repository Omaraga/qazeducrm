#!/bin/bash
# SSL Certificate Renewal Script for QazEduCRM
# Run via cron: 0 3 * * * /var/www/qazeducrm/scripts/renew-ssl.sh >> /var/log/ssl-renewal.log 2>&1

set -e

DOMAIN="crm.qazaq.education"
PROJECT_DIR="/var/www/qazeducrm"
SSL_DIR="${PROJECT_DIR}/docker/nginx/ssl"

echo "=== SSL Renewal Started: $(date) ==="

# Run certbot renewal
docker run --rm --name certbot-renew \
  -v "${SSL_DIR}:/etc/letsencrypt" \
  -v qazeducrm_certbot_webroot:/var/www/certbot \
  certbot/certbot renew --webroot -w /var/www/certbot --quiet

# Check if renewal was successful
if [ $? -eq 0 ]; then
    echo "Certificate renewed successfully"

    # Reload nginx to apply new certificate
    docker exec qazeducrm-nginx-1 nginx -s reload
    echo "Nginx reloaded"
else
    echo "Certificate renewal failed or not due"
fi

echo "=== SSL Renewal Finished: $(date) ==="
