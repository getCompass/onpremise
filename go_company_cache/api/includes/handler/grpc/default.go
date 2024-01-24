package grpcController

import (
	pb "github.com/getCompassUtils/company_protobuf_schemes/go/company_cache"
	Isolation "go_company_cache/api/includes/type/isolation"
)

// пакет с gRPC контролером — содержит в себе все методы микросервиса
// вынесен отдельно, так как grpc реализует свой роутер запросов и интерфейс работы с request/response
type Server struct {
	pb.CompanyCacheServer
	CompanyEnvList *Isolation.CompanyEnvList
}
