package grpc

import (
	"fmt"
	"net"
	"time"

	"github.com/getCompassUtils/go_base_frame/api/system/log"
	pb "github.com/getCompassUtils/pivot_protobuf_schemes/go/auth"
	"google.golang.org/grpc"
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

// Listen слушаем
func (s *ApiTokenServer) Listen(host string, port int) error {

	lis, err := net.Listen(connectionType, fmt.Sprintf("%s:%d", host, port))
	if err != nil {
		return err
	}

	grpcServer := grpc.NewServer(grpc.ConnectionTimeout(connectionTimeout), grpc.MaxConcurrentStreams(routinesMax), grpc.MaxSendMsgSize(maxMsgSize), grpc.MaxRecvMsgSize(maxMsgSize))
	pb.RegisterAuthServer(grpcServer, s)

	// в конце концов перестаем слушать
	defer func() {

		_ = lis.Close()
		grpcServer.Stop()
	}()

	// логируем успешное начало прослушивания
	log.Successf("start listening grpc on %s:%d", host, port)

	return grpcServer.Serve(lis)
}
