#!/usr/bin/env bash
# Simple upload helper using lftp mirror (fill credentials before use)

FTP_HOST="ftp.example.com"
FTP_USER="username"
FTP_PASS="password"
REMOTE_DIR="/htdocs"

if [ -z "$FTP_HOST" ] || [ -z "$FTP_USER" ] || [ -z "$FTP_PASS" ]; then
  echo "Preencha FTP_HOST, FTP_USER e FTP_PASS no script antes de usar."
  exit 1
fi

echo "Conectando em $FTP_HOST e enviando para $REMOTE_DIR..."

lftp -u "$FTP_USER","$FTP_PASS" "$FTP_HOST" <<EOF
mirror -R --delete --verbose . $REMOTE_DIR
quit
EOF

echo "Upload concluído."
