package grpc

import (
	"context"
	pb "github.com/getCompassUtils/pivot_protobuf_schemes/go/pusher"
	"go_pusher/api/includes/controller/pusher"
	"go_pusher/api/includes/controller/system"
	"go_pusher/api/includes/type/device"
)

// пакет с gRPC контролером — содержит в себе все методы микросервиса
// вынесен отдельно, так как grpc реализует свой роутер запросов и интерфейс работы с request/response

type Server struct {
	pb.PusherServer
}

// -------------------------------------------------------
// pusher methods group
// -------------------------------------------------------

// отправляем тестовый пуш на устройство
func (s *Server) PusherSendTestPush(_ context.Context, in *pb.PusherSendTestPushRequestStruct) (*pb.PusherSendTestPushResponseStruct, error) {

	pusher.SendTestPush(convertTokenItem(in.GetTokenItem()), in.GetType())

	return &pb.PusherSendTestPushResponseStruct{}, nil
}

// конвертация структуры из grpc в обычную
func convertTokenItem(in *pb.TokenItem) device.TokenItem {

	return device.TokenItem{
		Version:           int(in.GetVersion()),
		Token:             in.GetToken(),
		TokenType:         int(in.GetTokenType()),
		SessionUniq:       in.GetSessionUniq(),
		DeviceId:          in.GetDeviceId(),
		SoundType:         int(in.GetSoundType()),
		IsNewFirebasePush: int(in.GetIsNewFirebasePush()),
		AppName:           in.GetAppName(),
	}
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
		Uptime:     int32(statusInfoItem.UpTime),
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
