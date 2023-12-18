package grpcController

import (
	"context"
	pb "github.com/getCompassUtils/pivot_protobuf_schemes/go/sender_balancer"
	"go_sender_balancer/api/includes/methods/system"
	"go_sender_balancer/api/includes/methods/talking"
	"google.golang.org/grpc/status"
)

// пакет с gRPC контролером — содержит в себе все методы микросервиса
// вынесен отдельно, так как grpc реализует свой роутер запросов и интерфейс работы с request/response

type Server struct {
	pb.GoSenderBalancerServer
}

// -------------------------------------------------------
// talking methods group
// -------------------------------------------------------

// метод для установки пользовательского токена на go_sender для авторизации подключения
// принимает параметры: user_id int64, token string, expire	int64
// обращается к ноде go_sender, чтобы тот сохранил токен к себе
func (s *Server) SenderBalancerSetToken(_ context.Context, in *pb.SenderBalancerSetTokenRequestStruct) (*pb.SenderBalancerSetTokenResponseStruct, error) {

	senderNodeId, err := talking.SetToken(in.GetToken(), in.GetPlatform(), in.GetDeviceId(), in.GetUserId(), in.GetExpire())
	if err != nil {
		return nil, status.Error(status.Code(err), status.Convert(err).Message())
	}

	return &pb.SenderBalancerSetTokenResponseStruct{
		Node: senderNodeId,
	}, nil
}

// метод для получения списка ws соединений по идентификатору пользователя (userId)
func (s *Server) SenderBalancerGetOnlineConnectionsByUserId(_ context.Context, in *pb.SenderBalancerGetOnlineConnectionsByUserIdRequestStruct) (*pb.SenderBalancerGetOnlineConnectionsByUserIdResponseStruct, error) {

	localUserConnectionList, err := talking.GetOnlineConnectionsByUserId(in.GetUserId())
	if err != nil {
		return nil, status.Error(status.Code(err), status.Convert(err).Message())
	}

	// коневртируем массив структур для grpc
	var connectionList []*pb.ConnectionInfoStruct
	for _, s := range localUserConnectionList {
		connectionStruct := new(pb.ConnectionInfoStruct)
		connectionStruct.SenderNodeId = s.SenderNodeId
		connectionStruct.ConnectionId = s.ConnId
		connectionStruct.UserId = s.UserId
		connectionStruct.IpAddress = s.IpAddress
		connectionStruct.ConnectedAt = s.ConnectedAt
		connectionStruct.UserAgent = s.UserAgent
		connectionStruct.Platform = s.Platform
		connectionStruct.IsFocused = int64(s.IsFocused)

		connectionList = append(connectionList, connectionStruct)
	}

	return &pb.SenderBalancerGetOnlineConnectionsByUserIdResponseStruct{
		OnlineConnectionList: connectionList,
	}, nil
}

// метод для закрытия подключений по идентификатору пользователя (userId)
func (s *Server) SenderBalancerCloseConnectionsByUserId(_ context.Context, in *pb.SenderBalancerCloseConnectionsByUserIdRequestStruct) (*pb.SenderBalancerCloseConnectionsByUserIdResponseStruct, error) {

	err := talking.CloseConnectionsByUserId(in.GetUserId())
	if err != nil {
		return nil, status.Error(status.Code(err), status.Convert(err).Message())
	}

	return &pb.SenderBalancerCloseConnectionsByUserIdResponseStruct{}, nil
}

// метод для запроса онлайна ряда пользователей на ноды go_sender_*
func (s *Server) SenderBalancerGetOnlineUsers(_ context.Context, in *pb.SenderBalancerGetOnlineUsersRequestStruct) (*pb.SenderBalancerGetOnlineUsersResponseStruct, error) {

	onlineUserList, offlineUserList := talking.GetOnlineUsers(in.GetUserList())

	return &pb.SenderBalancerGetOnlineUsersResponseStruct{
		OnlineUserList:  onlineUserList,
		OfflineUserList: offlineUserList,
	}, nil
}

// -------------------------------------------------------
// system methods group
// -------------------------------------------------------

// собираем информацию о состоянии системы
func (s *Server) SystemStatus(_ context.Context, _ *pb.SystemStatusRequestStruct) (*pb.SystemStatusResponseStruct, error) {

	statusInfoItem, err := system.Status()
	if err != nil {
		return &pb.SystemStatusResponseStruct{}, err
	}

	return &pb.SystemStatusResponseStruct{
		Name:       statusInfoItem.Name,
		Goroutines: statusInfoItem.Goroutines,
		Memory:     statusInfoItem.Memory,
		MemoryKb:   statusInfoItem.MemoryKB,
		MemoryMb:   statusInfoItem.MemoryMB,
		Uptime:     statusInfoItem.UpTime,
	}, err
}

// получаем информацию о goroutines микросервиса
func (s *Server) SystemTraceGoroutine(_ context.Context, _ *pb.SystemTraceGoroutineRequestStruct) (*pb.SystemTraceGoroutineResponseStruct, error) {

	err := system.TraceGoroutine()
	if err != nil {
		return &pb.SystemTraceGoroutineResponseStruct{}, err
	}

	return &pb.SystemTraceGoroutineResponseStruct{}, err
}

// получаем информацию о выделенной памяти
func (s *Server) SystemTraceMemory(_ context.Context, _ *pb.SystemTraceMemoryRequestStruct) (*pb.SystemTraceMemoryResponseStruct, error) {

	err := system.TraceMemory()
	if err != nil {
		return &pb.SystemTraceMemoryResponseStruct{}, err
	}

	return &pb.SystemTraceMemoryResponseStruct{}, err
}

// собираем информацию о нагрузке процессора за указанное время
func (s *Server) SystemCpuProfile(_ context.Context, in *pb.SystemCpuProfileRequestStruct) (*pb.SystemCpuProfileResponseStruct, error) {

	err := system.CpuProfile(int(in.GetTime()))
	if err != nil {
		return &pb.SystemCpuProfileResponseStruct{}, err
	}

	return &pb.SystemCpuProfileResponseStruct{}, err
}

// обновляем конфигурацию
func (s *Server) SystemReloadConfig(_ context.Context, _ *pb.SystemReloadConfigRequestStruct) (*pb.SystemReloadConfigResponseStruct, error) {

	configItem, err := system.ReloadConfig()
	if err != nil {
		return &pb.SystemReloadConfigResponseStruct{}, err
	}

	return &pb.SystemReloadConfigResponseStruct{
		LoggingLevel: int32(configItem.LoggingLevel),
		ServerType:   configItem.ServerType,
		TcpPort:      configItem.TcpPort,
	}, err
}

// обновляем sharding конфигурацию
func (s *Server) SystemReloadSharding(_ context.Context, _ *pb.SystemReloadShardingRequestStruct) (*pb.SystemReloadShardingResponseStruct, error) {

	err := system.ReloadSharding()
	if err != nil {
		return &pb.SystemReloadShardingResponseStruct{}, err
	}

	return &pb.SystemReloadShardingResponseStruct{}, err
}

// проверяем sharding конфигурацию
func (s *Server) SystemCheckSharding(_ context.Context, _ *pb.SystemCheckShardingRequestStruct) (*pb.SystemCheckShardingResponseStruct, error) {

	system.CheckSharding()
	return &pb.SystemCheckShardingResponseStruct{}, nil
}
