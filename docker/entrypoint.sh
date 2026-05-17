#!/bin/bash
set -e

STORAGE=/mnt/data

mkdir -p "$STORAGE/db"
mkdir -p "$STORAGE/uploads/products"

# Seed product images on first deploy
if [ -z "$(ls -A "$STORAGE/uploads/products" 2>/dev/null)" ] && [ -d /var/www/html/uploads/products ]; then
    cp -rn /var/www/html/uploads/products/. "$STORAGE/uploads/products/" 2>/dev/null || true
fi

# Protect uploads directory
if [ ! -f "$STORAGE/uploads/.htaccess" ]; then
    cat > "$STORAGE/uploads/.htaccess" << 'HTEOF'
Options -Indexes
<FilesMatch "\.(php|php5|phtml|cgi|pl|py|rb)$">
    Order deny,allow
    Deny from all
</FilesMatch>
HTEOF
fi

# Replace local dirs with symlinks to persistent volume
rm -rf /var/www/html/data
ln -sf "$STORAGE/db" /var/www/html/data

rm -rf /var/www/html/uploads
ln -sf "$STORAGE/uploads" /var/www/html/uploads

chown -R www-data:www-data "$STORAGE"

exec "$@"
