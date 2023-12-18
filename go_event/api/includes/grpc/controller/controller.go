package grpcController

import (
	"context"
	"encoding/json"
	"errors"
	pb "github.com/getCompassUtils/company_protobuf_schemes/go/event"
	AsyncTask "go_event/api/includes/type/async_task"
	"go_event/api/includes/type/async_trap"
	CompanyEnvironment "go_event/api/includes/type/company_config"
	"google.golang.org/grpc/status"
)

// пакет с gRPC контролером — содержит в себе все методы микросервиса
// вынесен отдельно, так как grpc реализует свой роутер запросов и интерфейс работы с request/response

type Server struct {
	pb.EventServer
}

// TaskPush метод для пуша задач
func (s *Server) TaskPush(_ context.Context, in *pb.TaskPushRequestStruct) (*pb.TaskPushResponseStruct, error) {

	isolation := CompanyEnvironment.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.TaskPushResponseStruct{}, status.Error(503, "isolation not found")
	}

	if in.UniqueKey == "" || in.Type == "" {
		return nil, errors.New("passed bad data — key or event is empty")
	}

	// добавляем задачу в хранилище
	err := AsyncTask.CreateAndPushToDiscrete(isolation, in.Type, AsyncTask.TaskTypeSingle, in.GetNeedWork(), json.RawMessage(in.Data), in.Module, in.Group)
	if err != nil {
		return nil, errors.New("bad task in request")
	}

	return &pb.TaskPushResponseStruct{}, nil
}

// EventSetEventTrap метод для установки ловушки для событий
func (s *Server) EventSetEventTrap(_ context.Context, in *pb.EventSetEventTrapRequestStruct) (*pb.EventSetEventTrapResponseStruct, error) {

	if in.UniqueKey == "" || in.EventType == "" {
		return nil, errors.New("passed bad data — key or event is empty")
	}

	isolation := CompanyEnvironment.GetEnv(in.GetCompanyId())

	if isolation == nil {
		return &pb.EventSetEventTrapResponseStruct{}, status.Error(503, "isolation not found")
	}

	// создаем ловушку
	var eTrap = AsyncTrap.MakeAsyncTrap(in.UniqueKey, in.Subscriber, in.CreatedAfter, in.EventType, in.Translations)

	// устанавливаем ловушку
	return &pb.EventSetEventTrapResponseStruct{}, AsyncTrap.SetTrap(isolation, eTrap, 10)
}

// EventWaitEventTrap метод для прослушивания ловушки для событий
func (s *Server) EventWaitEventTrap(_ context.Context, in *pb.EventWaitEventTrapRequestStruct) (*pb.EventWaitEventTrapResponseStruct, error) {

	if in.UniqueKey == "" {
		return nil, errors.New("passed bad data — key is empty")
	}

	isolation := CompanyEnvironment.GetEnv(in.GetCompanyId())

	if isolation == nil {
		return &pb.EventWaitEventTrapResponseStruct{}, status.Error(503, "isolation not found")
	}

	// устанавливаем ловушку
	if AsyncTrap.WaitTillTrapped(isolation, in.UniqueKey, 10) {

		return &pb.EventWaitEventTrapResponseStruct{
			IsFound: 1,
		}, nil
	}

	return &pb.EventWaitEventTrapResponseStruct{
		IsFound: 0,
	}, nil
}
