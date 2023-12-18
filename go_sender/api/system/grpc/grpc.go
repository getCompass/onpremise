package grpc

import (
	"fmt"
	pb "github.com/getCompassUtils/company_protobuf_schemes/go/sender"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_sender/api/includes/grpc/controller"
	Isolation "go_sender/api/includes/type/isolation"
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
	connectionTimeout = time.Second * 20

	// ограничение размера реквеста на 10MB (4MB default)
	maxMsgSize = 83886080
)

// слушаем
func Listen(host string, port int64, companyEnvList *Isolation.CompanyEnvList) {

	lis, err := net.Listen(connectionType, fmt.Sprintf("%s:%d", host, port))
	if err != nil {
		log.Errorf("unable to start listening, error: %v", err)
		return
	}

	grpcServer := grpc.NewServer(grpc.ConnectionTimeout(connectionTimeout), grpc.MaxConcurrentStreams(routinesMax), grpc.MaxSendMsgSize(maxMsgSize), grpc.MaxRecvMsgSize(maxMsgSize))
	server := &grpcController.Server{}
	server.CompanyEnvList = companyEnvList
	pb.RegisterSenderServer(grpcServer, server)

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
