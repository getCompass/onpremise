package grpc

// пакет с gRPC контролером — содержит в себе все методы микросервиса
// вынесен отдельно, так как grpc реализует свой роутер запросов и интерфейс работы с request/response

import (
	"go_auth/internal/apitoken"
	"go_auth/internal/config"

	"github.com/getCompassUtils/go_base_frame/api/system/base64"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	pb "github.com/getCompassUtils/pivot_protobuf_schemes/go/auth"
)

type ApiTokenServer struct {
	pb.AuthServer
	cacheManager          *apitoken.ApiTokenCacheManager
	apiKeyTemplatesConfig *config.ApiKeyTemplatesConfig
	secretKey             []byte
}

// InitApiTokenServer инициализировать grpc сервер
func InitApiTokenServer(cacheManager *apitoken.ApiTokenCacheManager, apiKeyTemplatesConfig *config.ApiKeyTemplatesConfig, secretKeyB64 string) *ApiTokenServer {

	secretKey, err := base64.Base64Decode(secretKeyB64)

	if err != nil {

		log.Errorf("GRPC Server not initialized! Error %v", err)
		return nil
	}
	return &ApiTokenServer{
		cacheManager:          cacheManager,
		apiKeyTemplatesConfig: apiKeyTemplatesConfig,
		secretKey:             secretKey,
	}
}
