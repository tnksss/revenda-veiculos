#!/usr/bin/env bash
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BACKUP_DIR="${BACKUP_DIR:-/var/backups/tobias-veiculos}"
RETENTION_DAYS="${RETENTION_DAYS:-14}"
TIMESTAMP="$(date +%Y-%m-%d_%H-%M-%S)"

mkdir -p "$BACKUP_DIR"

# Carrega variáveis do .env
if [ -f "$APP_DIR/.env" ]; then
    set -a
    # shellcheck disable=SC1091
    source "$APP_DIR/.env"
    set +a
fi

echo "==> [1/3] Gerando dump do PostgreSQL ($DB_DATABASE)..."
PGPASSWORD="${DB_PASSWORD}" pg_dump \
    -h "${DB_HOST:-127.0.0.1}" \
    -p "${DB_PORT:-5432}" \
    -U "${DB_USERNAME}" \
    -F c \
    "${DB_DATABASE}" \
    -f "$BACKUP_DIR/db_${TIMESTAMP}.dump"

echo "==> [2/3] Compactando mídia dos veículos (storage/app/public)..."
tar -czf "$BACKUP_DIR/media_${TIMESTAMP}.tar.gz" -C "$APP_DIR/storage/app" public

echo "==> [3/3] Removendo backups com mais de ${RETENTION_DAYS} dias..."
find "$BACKUP_DIR" -type f \( -name "db_*.dump" -o -name "media_*.tar.gz" \) -mtime +"${RETENTION_DAYS}" -delete

echo "✅ Backup concluído em: $BACKUP_DIR ($TIMESTAMP)"
