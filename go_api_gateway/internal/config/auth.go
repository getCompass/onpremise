package config

import (
	"fmt"
	"strconv"
)

// конфиг с параметрами общения с go_auth
type AuthConfigStruct struct {
	AuthGrpcHost string `json:"auth_grpc_host"`
	AuthGrpcPort int64  `json:"auth_grpc_port"`
}

// LoadAuthConfig инициализация конфига go_auth
func LoadAuthConfig() (*AuthConfigStruct, error) {

	authGrpcPort, err := strconv.Atoi(getEnv("GO_AUTH_GRPC_PORT", ""))

	if err != nil {
		return nil, fmt.Errorf("auth grpc port is not int in config. Passed value: %v", authGrpcPort)
	}

	authConfig := &AuthConfigStruct{
		AuthGrpcHost: getEnv("GO_AUTH_GRPC_HOST", ""),
		AuthGrpcPort: int64(authGrpcPort),
	}

	fmt.Println("Loaded main config")
	return authConfig, nil
}
