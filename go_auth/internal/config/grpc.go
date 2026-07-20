package config

import (
	"fmt"
	"strconv"
)

// структура конфига grpc
type GrpcConfigStruct struct {
	GrpcPort int `json:"grpc_port"`
}

// LoadGrpcConfig загрузить конфиг для сервера grpc
func LoadGrpcConfig() (*GrpcConfigStruct, error) {

	grpcPort, err := strconv.Atoi(getEnv("GO_AUTH_GRPC_PORT", ""))

	if err != nil {
		return nil, fmt.Errorf("grpc port is not int in grpc config. Passed value: %v", grpcPort)
	}

	grpcConfig := &GrpcConfigStruct{
		GrpcPort: grpcPort,
	}

	fmt.Println("Loaded grpc config")
	return grpcConfig, nil
}
