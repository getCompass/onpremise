package grpcController

// system methods group
import (
	"context"
	pb "github.com/getCompassUtils/company_protobuf_schemes/go/company_cache"
	"go_company_cache/api/includes/controller/system"
)

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
