package grpc

import (
	"context"
	"crypto/tls"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	pb "github.com/getCompassUtils/pivot_protobuf_schemes/go/database_controller"
	"go_database_controller/api/conf"
	controller "go_database_controller/api/includes/grpc"
	"go_database_controller/api/includes/type/auth"
	"google.golang.org/grpc"
	"google.golang.org/grpc/codes"
	"google.golang.org/grpc/credentials"
	"google.golang.org/grpc/metadata"
	"google.golang.org/grpc/status"
	"net"
	"time"
)

// -------------------------------------------------------
// пакет для обработки входящих запросов по протоколу gRPC
// -------------------------------------------------------

const (

	// максимальное количество соединений
	routinesMax = 1000

	// тип соединения
	connectionType = "tcp"

	// таймаут установления tcp соединений
	connectionTimeout = time.Second * 2

	// ограничение размера реквеста на 10MB (4MB default)
	maxMsgSize = 83886080
)

// слушаем
func Listen(host string, port int64) {

	lis, err := net.Listen(connectionType, fmt.Sprintf("%s:%d", host, port))
	if err != nil {
		log.Errorf("unable to start listening, error: %v", err)
		return
	}

	cert, err := tls.X509KeyPair(
		[]byte(conf.GetConfig().MysqlHostCertificate),
		[]byte(conf.GetConfig().MysqlHostPrivateKey),
	)

	if err != nil {

		log.Errorf("unable to load certificates, error: %v", err)
		return
	}

	creds := credentials.NewServerTLSFromCert(&cert)

	grpcServer := grpc.NewServer(
		grpc.ConnectionTimeout(connectionTimeout),
		grpc.MaxConcurrentStreams(routinesMax),
		grpc.MaxSendMsgSize(maxMsgSize),
		grpc.MaxRecvMsgSize(maxMsgSize),
		grpc.UnaryInterceptor(unaryInterceptor),
		grpc.Creds(creds),
	)

	pb.RegisterDatabaseControllerServer(grpcServer, &controller.Server{})

	// в конце концов перестаем слушать
	defer func() {

		_ = lis.Close()
		grpcServer.Stop()
	}()

	err = grpcServer.Serve(lis)
	if err != nil {
		log.Errorf("unable to start listening, error: %v", err)
		return
	}
}

// перехватчик запроса (крутое название мидлвара в grpc)
func unaryInterceptor(ctx context.Context, req interface{}, _ *grpc.UnaryServerInfo, handler grpc.UnaryHandler) (interface{}, error) {

	// получаем метадату
	md, ok := metadata.FromIncomingContext(ctx)

	// если нет метадаты - прислали левак вместо запроса
	if !ok {
		return nil, status.Error(codes.Internal, "unable to get metadata")
	}

	// нет хедера авторизации - значит и токен не увидим
	if len(md["authorization"]) < 1 {
		return nil, status.Error(codes.Unauthenticated, "no authorization header")
	}

	// аутентифицирукем клиента по токену
	err := auth.AuthenticateClient(conf.GetConfig().DominoSecretKey, md["authorization"][0])

	if err != nil {
		return nil, status.Error(codes.Unauthenticated, "invalid token")
	}

	// выполняем запрос
	m, err := handler(ctx, req)

	return m, err
}
