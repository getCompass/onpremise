#!/bin/sh

envsubst </app/web/src/private/custom.local.ts >/app/web/src/private/custom.ts
rm /app/web/src/private/custom.local.ts

cd /app/web && pnpm prepare && pnpm build

chown -R www-data:www-data /app/web/dist

cd /app && sh install.sh

exec nginx -g 'daemon off;'