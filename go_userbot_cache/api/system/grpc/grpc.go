package grpc

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	pb "github.com/getCompassUtils/pivot_protobuf_schemes/go/userbot_cache"
	grpcController "go_userbot_cache/api/includes/handler/grpc"
	"google.golang.org/grpc"
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

	grpcServer := grpc.NewServer(grpc.ConnectionTimeout(connectionTimeout), grpc.MaxConcurrentStreams(routinesMax), grpc.MaxSendMsgSize(maxMsgSize), grpc.MaxRecvMsgSize(maxMsgSize))
	pb.RegisterUserbotCacheServer(grpcServer, &grpcController.Server{})

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
