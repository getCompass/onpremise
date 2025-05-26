Для запуска контейнера генератора кода из протофайлов используйте команду:
docker-compose run proto_generator protoc --go_out=plugins=grpc:go/ --proto_path=. --php_out=php/ --grpc_out=grpc/ --plugin=protoc-gen-grpc=/tmp/grpc/cmake/build/grpc_php_plugin proto/go_pivot_cache.proto
