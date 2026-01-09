# QazEduCRM - Deploy Guide (Ubuntu Server)

## Requirements

- Ubuntu 20.04+ (22.04 recommended)
- Minimum 2GB RAM, 20GB disk
- Root access (SSH)
- Domain name (optional, for SSL)

---

## Step 1: Connect to Server

```bash
ssh root@your-server-ip
```

---

## Step 2: Update System

```bash
apt update && apt upgrade -y
```

---

## Step 3: Install Docker

```bash
# Install dependencies
apt install -y ca-certificates curl gnupg lsb-release

# Add Docker GPG key
install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | gpg --dearmor -o /etc/apt/keyrings/docker.gpg
chmod a+r /etc/apt/keyrings/docker.gpg

# Add Docker repository
echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
  $(lsb_release -cs) stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null

# Install Docker
apt update
apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

# Start Docker
systemctl enable docker
systemctl start docker

# Verify installation
docker --version
docker compose version
```

**Expected output:**
```
Docker version 24.x.x
Docker Compose version v2.x.x
```

---

## Step 4: Install Git

```bash
apt install -y git
```

---

## Step 5: Clone Project

```bash
cd /opt
git clone https://github.com/your-repo/qazeducrm.git
cd qazeducrm
```

**Or upload manually via SCP:**
```bash
# From your Windows machine (Git Bash or PowerShell)
scp -r /path/to/qazeducrm root@your-server-ip:/opt/
```

---

## Step 6: Configure Environment

```bash
cp .env.production .env
nano .env
```

**Change these values:**

```env
DOMAIN=crm.yourdomain.com
DB_PASS=your_secure_mysql_password
MYSQL_ROOT_PASSWORD=your_secure_root_password
POSTGRES_PASSWORD=your_secure_postgres_password
EVOLUTION_API_KEY=your_random_api_key_here
EVOLUTION_SERVER_URL=https://crm.yourdomain.com/whatsapp
```

**Generate secure passwords:**

```bash
# Generate random password
openssl rand -base64 32
```

### 4. Run deploy script

```bash
chmod +x deploy.sh ssl-setup.sh
sudo ./deploy.sh
```

### 5. Setup SSL (optional but recommended)

```bash
sudo ./ssl-setup.sh crm.yourdomain.com
```

---

## Manual Deploy

### Step 1: Install Docker

```bash
# Install Docker
curl -fsSL https://get.docker.com | sh

# Add current user to docker group
sudo usermod -aG docker $USER

# Start Docker
sudo systemctl enable docker
sudo systemctl start docker
```

### Step 2: Clone and configure

```bash
cd /opt
git clone https://github.com/your-repo/qazeducrm.git
cd qazeducrm

# Create .env
cp .env.production .env
nano .env  # edit values
```

### Step 3: Set permissions

```bash
mkdir -p runtime web/assets
chmod -R 777 runtime web/assets
```

### Step 4: Start containers

```bash
# Pull images
docker compose pull

# Build PHP image
docker compose build

# Start all services
docker compose up -d

# Check status
docker compose ps
```

### Step 5: Run migrations

```bash
# Wait for MySQL to be ready (about 30 seconds)
sleep 30

# Run migrations
docker compose exec php php yii migrate --interactive=0
```

### Step 6: Import existing database (if needed)

```bash
# Copy dump to server
scp qazeducrm.sql root@your-server:/opt/qazeducrm/

# Import
docker compose exec -T mysql mysql -u root -p$MYSQL_ROOT_PASSWORD qazeducrm < qazeducrm.sql
```

---

## Services

| Service | Container | Internal Port | Access |
|---------|-----------|---------------|--------|
| Nginx | qazeducrm-nginx | 80, 443 | `http://yourdomain.com` |
| PHP-FPM | qazeducrm-php | 9000 | internal |
| MySQL | qazeducrm-mysql | 3306 | internal |
| Evolution API | qazeducrm-evolution | 8080 | `http://yourdomain.com/whatsapp/` |
| PostgreSQL | qazeducrm-postgres | 5432 | internal |
| Redis | qazeducrm-redis | 6379 | internal |

---

## Common Commands

### Container management

```bash
# View status
docker compose ps

# View logs
docker compose logs -f              # all
docker compose logs -f php          # php only
docker compose logs -f evolution-api  # whatsapp

# Restart services
docker compose restart
docker compose restart php

# Stop all
docker compose stop

# Start all
docker compose start

# Full restart (recreate)
docker compose down && docker compose up -d
```

### Yii2 commands

```bash
# Run migrations
docker compose exec php php yii migrate

# Clear cache
docker compose exec php php yii cache/flush-all

# Run console command
docker compose exec php php yii <command>
```

### Database

```bash
# Connect to MySQL
docker compose exec mysql mysql -u root -p

# Backup database
docker compose exec mysql mysqldump -u root -p qazeducrm > backup_$(date +%Y%m%d).sql

# Restore database
docker compose exec -T mysql mysql -u root -p qazeducrm < backup.sql
```

### Update application

```bash
cd /opt/qazeducrm

# Pull latest code
git pull origin main

# Rebuild if Dockerfile changed
docker compose build php

# Restart
docker compose up -d

# Run new migrations
docker compose exec php php yii migrate --interactive=0

# Clear cache
docker compose exec php php yii cache/flush-all
```

---

## WhatsApp Integration

### Access Evolution API

- **API**: `https://yourdomain.com/whatsapp/`
- **Swagger Docs**: `https://yourdomain.com/whatsapp/docs`

### Create WhatsApp instance

```bash
curl -X POST 'https://yourdomain.com/whatsapp/instance/create' \
  -H 'apikey: YOUR_API_KEY' \
  -H 'Content-Type: application/json' \
  -d '{
    "instanceName": "main",
    "qrcode": true,
    "integration": "WHATSAPP-BAILEYS"
  }'
```

### Get QR code

```bash
curl -X GET 'https://yourdomain.com/whatsapp/instance/qrcode/main' \
  -H 'apikey: YOUR_API_KEY'
```

---

## Troubleshooting

### Container won't start

```bash
# Check logs
docker compose logs <service-name>

# Check if ports are in use
netstat -tulpn | grep :80
netstat -tulpn | grep :443
```

### PHP errors

```bash
# Check PHP logs
docker compose logs php

# Check Yii2 runtime logs
docker compose exec php cat runtime/logs/app.log
```

### Database connection issues

```bash
# Check if MySQL is healthy
docker compose exec mysql mysqladmin ping -u root -p

# Test connection from PHP
docker compose exec php php -r "new PDO('mysql:host=mysql;dbname=qazeducrm', 'qazeducrm', 'password');"
```

### Permission issues

```bash
# Fix permissions
docker compose exec php chown -R www-data:www-data runtime web/assets
docker compose exec php chmod -R 777 runtime web/assets
```

### Reset everything

```bash
# Stop and remove all containers and volumes
docker compose down -v

# Start fresh
docker compose up -d
```

---

## Backups

### Automated daily backup

```bash
# Create backup script
cat > /opt/backup-qazeducrm.sh << 'EOF'
#!/bin/bash
BACKUP_DIR=/opt/backups
DATE=$(date +%Y%m%d_%H%M%S)
mkdir -p $BACKUP_DIR

# Database backup
docker compose -f /opt/qazeducrm/docker-compose.yml exec -T mysql \
  mysqldump -u root -p$MYSQL_ROOT_PASSWORD qazeducrm | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Keep only last 7 days
find $BACKUP_DIR -name "*.sql.gz" -mtime +7 -delete

echo "Backup completed: $BACKUP_DIR/db_$DATE.sql.gz"
EOF

chmod +x /opt/backup-qazeducrm.sh

# Add to cron (daily at 3 AM)
echo "0 3 * * * /opt/backup-qazeducrm.sh" | crontab -
```

---

## Security Recommendations

1. **Change all default passwords** in `.env`
2. **Enable firewall**:
   ```bash
   ufw allow 22
   ufw allow 80
   ufw allow 443
   ufw enable
   ```
3. **Use SSH keys** instead of passwords
4. **Keep system updated**: `apt update && apt upgrade -y`
5. **Setup fail2ban** for brute-force protection
