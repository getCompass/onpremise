#!/bin/bash

# дожидаемся сервисов
bash "/wait-services.sh" || die "service waiting failed"

# execute the original entrypoint with any passed arguments
exec /init "$@"