package grpc

import (
	"context"
	"fmt"

	pb "github.com/getCompassUtils/pivot_protobuf_schemes/go/auth"
	"google.golang.org/grpc"
	"google.golang.org/grpc/credentials/insecure"
)

// клиент go_auth
type AuthClient struct {
	pb.AuthClient
}

// известные ошибки go_auth
const (
	ErrTokenNotFound = 901
	ErrTokenExpired  = 902
	ErrTokenInvalid  = 913
)

// InitAuthClient инициализировать клиент go_auth
func InitAuthClient(host string, port int64) (*AuthClient, error) {

	conn, err := grpc.NewClient(fmt.Sprintf("%s:%d", host, port), grpc.WithTransportCredentials(insecure.NewCredentials()))

	if err != nil {
		return nil, err
	}

	authClient := &AuthClient{
		pb.NewAuthClient(conn),
	}
	return authClient, nil

}

// аутентифицировать клиента по API ключу
func (c *AuthClient) ApikeyAuthenticate(userId int64, apiToken string) (*pb.ApiTokenAuthenticateResponseStruct, error) {

	request := &pb.ApiTokenAuthenticateRequestStruct{
		UserId:   userId,
		ApiToken: apiToken,
	}
	response, err := c.AuthClient.ApikeyAuthenticate(context.TODO(), request)

	return response, err
}

// получить информацию об API ключе
func (c *AuthClient) ApikeyGet(apiKey string) (*pb.ApiKeyStruct, error) {

	request := &pb.ApikeyGetRequestStruct{
		ApiKey: apiKey,
	}
	response, err := c.AuthClient.ApikeyGet(context.TODO(), request)

	return response, err
}
