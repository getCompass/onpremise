package grpcController

// пакет с gRPC контролером — содержит в себе все методы микросервиса
// вынесен отдельно, так как grpc реализует свой роутер запросов и интерфейс работы с request/response

import (
	pb "github.com/getCompassUtils/pivot_protobuf_schemes/go/pivot_cache"
)

type Server struct {
	pb.PivotCacheServer
}
