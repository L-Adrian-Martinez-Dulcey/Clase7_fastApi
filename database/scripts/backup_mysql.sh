#!/bin/bash

DB_HOST="${DB_HOST:-127.0.0.1}"
DB_NAME="${DB_NAME:-evoria}"
DB_USER="${DB_USER:-evoria_user}"
DB_PASS="${DB_PASS:-evoria_password}"
BACKUP_DIR="${BACKUP_DIR:-./storage/backups}"
RETENTION_DAYS="${RETENTION_DAYS:-30}"

mkdir -p "$BACKUP_DIR"
DATE_STAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="$BACKUP_DIR/${DB_NAME}_${DATE_STAMP}.sql"

mysqldump -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_FILE"

find "$BACKUP_DIR" -type f -name "*.sql" -mtime +$RETENTION_DAYS -delete

echo "Backup realizado: $BACKUP_FILE"
