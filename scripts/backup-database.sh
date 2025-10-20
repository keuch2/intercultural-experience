#!/bin/bash

# Automated Database Backup Script for Intercultural Experience Platform
set -e

# Configuration
APP_NAME="intercultural-experience"
BACKUP_DIR="/var/backups/intercultural-experience"
RETENTION_DAYS=30
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
DATE_SUFFIX=$(date +%Y%m%d)

# Database configuration from environment
DB_HOST="${DB_HOST:-localhost}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:-intercultural_experience}"
DB_USERNAME="${DB_USERNAME:-root}"
DB_PASSWORD="${DB_PASSWORD:-}"

# S3 Configuration (optional)
S3_BUCKET="${S3_BUCKET:-}"
AWS_REGION="${AWS_REGION:-us-east-1}"

echo "🗄️  Starting automated database backup..."

# Create backup directory
mkdir -p "$BACKUP_DIR"

# Generate backup filename
BACKUP_FILE="$BACKUP_DIR/${APP_NAME}_${TIMESTAMP}.sql"
COMPRESSED_FILE="$BACKUP_FILE.gz"

# Create MySQL dump with extended options
echo "📦 Creating database dump..."
mysqldump \
    --host="$DB_HOST" \
    --port="$DB_PORT" \
    --user="$DB_USERNAME" \
    --password="$DB_PASSWORD" \
    --single-transaction \
    --routines \
    --triggers \
    --events \
    --hex-blob \
    --quick \
    --lock-tables=false \
    --add-drop-table \
    --disable-keys \
    --extended-insert \
    "$DB_DATABASE" > "$BACKUP_FILE"

# Verify backup file was created and has content
if [ ! -s "$BACKUP_FILE" ]; then
    echo "❌ Backup file is empty or was not created"
    exit 1
fi

# Compress the backup
echo "🗜️  Compressing backup file..."
gzip "$BACKUP_FILE"

# Calculate file size
BACKUP_SIZE=$(du -h "$COMPRESSED_FILE" | cut -f1)
echo "✅ Backup created: $COMPRESSED_FILE (Size: $BACKUP_SIZE)"

# Upload to S3 if configured
if [ -n "$S3_BUCKET" ]; then
    echo "☁️  Uploading backup to S3..."
    aws s3 cp "$COMPRESSED_FILE" "s3://$S3_BUCKET/database-backups/" \
        --region "$AWS_REGION" \
        --storage-class STANDARD_IA
    
    if [ $? -eq 0 ]; then
        echo "✅ Backup uploaded to S3 successfully"
        
        # Clean S3 old backups (keep last 90 days)
        CUTOFF_DATE=$(date -d "90 days ago" +%Y%m%d)
        aws s3 ls "s3://$S3_BUCKET/database-backups/" | while read -r line; do
            FILE_DATE=$(echo "$line" | awk '{print $4}' | grep -oE '[0-9]{8}' | head -1)
            FILE_NAME=$(echo "$line" | awk '{print $4}')
            
            if [ -n "$FILE_DATE" ] && [ "$FILE_DATE" -lt "$CUTOFF_DATE" ]; then
                aws s3 rm "s3://$S3_BUCKET/database-backups/$FILE_NAME"
                echo "🗑️  Removed old S3 backup: $FILE_NAME"
            fi
        done
    else
        echo "⚠️  Failed to upload backup to S3"
    fi
fi

# Test backup integrity
echo "🧪 Testing backup integrity..."
gunzip -t "$COMPRESSED_FILE"
if [ $? -eq 0 ]; then
    echo "✅ Backup integrity verified"
else
    echo "❌ Backup integrity check failed"
    exit 1
fi

# Create backup metadata
METADATA_FILE="$BACKUP_DIR/${APP_NAME}_${TIMESTAMP}.meta"
cat > "$METADATA_FILE" << EOF
{
    "timestamp": "$TIMESTAMP",
    "database": "$DB_DATABASE",
    "host": "$DB_HOST",
    "backup_file": "$(basename "$COMPRESSED_FILE")",
    "file_size": "$BACKUP_SIZE",
    "backup_type": "full",
    "created_at": "$(date -u +%Y-%m-%dT%H:%M:%SZ)",
    "retention_until": "$(date -u -d "+$RETENTION_DAYS days" +%Y-%m-%dT%H:%M:%SZ)"
}
EOF

# Clean old local backups
echo "🧹 Cleaning old local backups (retention: $RETENTION_DAYS days)..."
find "$BACKUP_DIR" -name "${APP_NAME}_*.sql.gz" -mtime +$RETENTION_DAYS -delete
find "$BACKUP_DIR" -name "${APP_NAME}_*.meta" -mtime +$RETENTION_DAYS -delete

# Log backup completion
echo "$(date): Backup completed successfully - $COMPRESSED_FILE" >> "$BACKUP_DIR/backup.log"

# Send notification
if [ -n "$SLACK_WEBHOOK_URL" ]; then
    curl -X POST "$SLACK_WEBHOOK_URL" \
        -H 'Content-type: application/json' \
        --data "{\"text\":\"✅ Database backup completed for $APP_NAME\\nFile: $(basename "$COMPRESSED_FILE")\\nSize: $BACKUP_SIZE\"}" \
        2>/dev/null || true
fi

echo "🎉 Database backup completed successfully!"
echo "📁 Backup location: $COMPRESSED_FILE"
echo "📊 Backup size: $BACKUP_SIZE"
