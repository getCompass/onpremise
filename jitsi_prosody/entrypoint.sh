#!/bin/bash
# copy SSL certificates if they exist
if [ -d "/tmp/ssl" ]; then
  cp -r /tmp/ssl/* /usr/local/share/ca-certificates/
  update-ca-certificates
fi

# execute the original entrypoint with any passed arguments
exec /init "$@"