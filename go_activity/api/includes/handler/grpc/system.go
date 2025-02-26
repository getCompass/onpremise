package grpcController

// system methods group
import (
	"context"
	pb "github.com/getCompassUtils/pivot_protobuf_schemes/go/activity"
	"go_activity/api/includes/controller/system"
)

// SystemStatus собираем информацию о состоянии системы
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

// SystemTraceGoroutine получаем информацию о goroutines микросервиса
func (s *Server) SystemTraceGoroutine(_ context.Context, _ *pb.SystemTraceGoroutineRequestStruct) (*pb.SystemTraceGoroutineResponseStruct, error) {

	err := system.TraceGoroutine()
	if err != nil {
		return &pb.SystemTraceGoroutineResponseStruct{}, err
	}

	return &pb.SystemTraceGoroutineResponseStruct{}, err
}

// SystemTraceMemory получаем информацию о выделенной памяти
func (s *Server) SystemTraceMemory(_ context.Context, _ *pb.SystemTraceMemoryRequestStruct) (*pb.SystemTraceMemoryResponseStruct, error) {

	err := system.TraceMemory()
	if err != nil {
		return &pb.SystemTraceMemoryResponseStruct{}, err
	}

	return &pb.SystemTraceMemoryResponseStruct{}, err
}

// SystemCpuProfile собираем информацию о нагрузке процессора за указанное время
func (s *Server) SystemCpuProfile(_ context.Context, in *pb.SystemCpuProfileRequestStruct) (*pb.SystemCpuProfileResponseStruct, error) {

	err := system.CpuProfile(int(in.GetTime()))
	if err != nil {
		return &pb.SystemCpuProfileResponseStruct{}, err
	}

	return &pb.SystemCpuProfileResponseStruct{}, err
}

// SystemReloadConfig обновляем конфигурацию
func (s *Server) SystemReloadConfig(_ context.Context, _ *pb.SystemReloadConfigRequestStruct) (*pb.SystemReloadConfigResponseStruct, error) {

	configItem, err := system.ReloadConfig()
	if err != nil {
		return &pb.SystemReloadConfigResponseStruct{}, err
	}

	return &pb.SystemReloadConfigResponseStruct{
		LoggingLevel:   int32(configItem.LoggingLevel),
		ServerType:     configItem.ServerType,
		TcpPort:        configItem.TcpPort,
		GrpcPort:       configItem.GrpcPort,
		RabbitQueue:    configItem.RabbitQueue,
		RabbitExchange: configItem.RabbitExchange,
	}, err
}

// SystemReloadSharding обновляем sharding конфигурацию
func (s *Server) SystemReloadSharding(_ context.Context, _ *pb.SystemReloadShardingRequestStruct) (*pb.SystemReloadShardingResponseStruct, error) {

	err := system.ReloadSharding()
	if err != nil {
		return &pb.SystemReloadShardingResponseStruct{}, err
	}

	return &pb.SystemReloadShardingResponseStruct{}, err
}

// SystemCheckSharding проверяем конфигурацию sharding
func (s *Server) SystemCheckSharding(ctx context.Context, _ *pb.SystemCheckShardingRequestStruct) (*pb.SystemCheckShardingResponseStruct, error) {

	system.CheckSharding(ctx)

	return &pb.SystemCheckShardingResponseStruct{}, nil
}
