package grpcController

import (
	"context"
	"encoding/json"
	pb "github.com/getCompassUtils/company_protobuf_schemes/go/sender"
	"go_sender/api/includes/methods/sender"
	"go_sender/api/includes/methods/system"
	Isolation "go_sender/api/includes/type/isolation"
	"go_sender/api/includes/type/push"
	"go_sender/api/includes/type/sender"
	"go_sender/api/includes/type/structures"
	"google.golang.org/grpc/status"
)

// пакет с gRPC контролером — содержит в себе все методы микросервиса
// вынесен отдельно, так как grpc реализует свой роутер запросов и интерфейс работы с request/response

type Server struct {
	pb.SenderServer
	CompanyEnvList *Isolation.CompanyEnvList
}

// -------------------------------------------------------
// sender methods group
// -------------------------------------------------------

// метод для установки пользовательского токена на go_sender для авторизации подключения
// принимает параметры: user_id int64, token string, expire	int64
// обращается к ноде go_sender, чтобы тот сохранил токен к себе
func (s *Server) SenderSetToken(_ context.Context, in *pb.SenderSetTokenRequestStruct) (*pb.SenderSetTokenResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.SenderSetTokenResponseStruct{}, status.Error(503, "isolation not found")
	}

	err := talking.SetToken(isolation, in.GetToken(), in.GetPlatform(), in.GetDeviceId(), in.GetUserId(), in.GetExpire())
	if err != nil {
		return nil, status.Error(status.Code(err), status.Convert(err).Message())
	}

	return &pb.SenderSetTokenResponseStruct{
		Node: 1,
	}, nil
}

// метод для очистки кэша пользователя
func (s *Server) ClearUserNotificationCache(_ context.Context, in *pb.ClearUserNotificationCacheRequestStruct) (*pb.ClearUserNotificationCacheResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.ClearUserNotificationCacheResponseStruct{}, status.Error(503, "isolation not found")
	}

	err := talking.ClearUserNotificationCache(isolation, in.GetUserId())
	if err != nil {
		return nil, status.Error(status.Code(err), status.Convert(err).Message())
	}

	return &pb.ClearUserNotificationCacheResponseStruct{}, nil
}

// метод для отправки WS событий на активные подключения пользователей
// в случае, если у пользователя нет активных подключений
// отправляет push уведомление, если это необходимо
// @long
func (s *Server) SenderSendEvent(_ context.Context, in *pb.SenderSendEventRequestStruct) (*pb.SenderSendEventResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.SenderSendEventResponseStruct{}, status.Error(503, "isolation not found")
	}

	// коневртируем строки в интерфейсы для grpc
	var WsUsers interface{}
	var PushData push.PushDataStruct
	err := json.Unmarshal([]byte(in.GetPushData()), &PushData)
	if err != nil {
		return nil, status.Error(105, "bad json in request")
	}

	if in.GetWsUsers() != "" {

		err = json.Unmarshal([]byte(in.GetWsUsers()), &WsUsers)
		if err != nil {
			return nil, status.Error(105, "bad json in request")
		}
	}

	// коневртируем массив структур для grpc
	var userList []structures.SendEventUserStruct
	for _, s := range in.GetUserList() {

		var userStruct structures.SendEventUserStruct
		userStruct.UserId = s.GetUserId()
		userStruct.NeedPush = int(s.GetNeedPush())
		userStruct.NeedForcePush = int(s.GetNeedForcePush())
		userList = append(userList, userStruct)
	}

	// конвертируем полученные версии события в более удобный формат
	eventListByVersion := sender.ConvertProtobufEventVersionList(in.GetEventVersionList())

	talking.SendEvent(isolation, userList, in.GetEvent(), eventListByVersion, PushData, WsUsers, in.GetUuid(), in.GetRoutineKey(), in.GetChannel())

	return &pb.SenderSendEventResponseStruct{}, nil
}

func (s *Server) SenderSendEventToAll(_ context.Context, in *pb.SenderSendEventToAllRequestStruct) (*pb.SenderSendEventToAllResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.SenderSendEventToAllResponseStruct{}, status.Error(503, "isolation not found")
	}

	// коневртируем строки в интерфейсы для grpc
	var WsUsers interface{}
	var PushData push.PushDataStruct
	err := json.Unmarshal([]byte(in.GetPushData()), &PushData)
	if err != nil {
		return nil, status.Error(105, "bad json in request")
	}

	if in.GetWsUsers() != "" {

		err = json.Unmarshal([]byte(in.GetWsUsers()), &WsUsers)
		if err != nil {
			return nil, status.Error(105, "bad json in request")
		}
	}

	// конвертируем полученные версии события в более удобный формат
	eventListByVersion := sender.ConvertProtobufEventVersionList(in.GetEventVersionList())

	talking.SendEventToAll(isolation, in.GetEvent(), eventListByVersion, PushData, WsUsers, in.GetUuid(), in.GetRoutineKey(), int(in.GetIsNeedPush()), in.GetChannel())

	return &pb.SenderSendEventToAllResponseStruct{}, nil
}

// метод для получения списка ws соединений по идентификатору пользователя (userId)
func (s *Server) SenderGetOnlineConnectionsByUserId(_ context.Context, in *pb.SenderGetOnlineConnectionsByUserIdRequestStruct) (*pb.SenderGetOnlineConnectionsByUserIdResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.SenderGetOnlineConnectionsByUserIdResponseStruct{}, status.Error(503, "isolation not found")
	}

	localUserConnectionList, err := talking.GetOnlineConnectionsByUserId(isolation, in.GetUserId())
	if err != nil {
		return nil, status.Error(status.Code(err), status.Convert(err).Message())
	}

	// коневртируем массив структур для grpc
	var connectionList []*pb.ConnectionInfoStruct
	for _, s := range localUserConnectionList {
		connectionStruct := new(pb.ConnectionInfoStruct)
		connectionStruct.ConnectionId = s.ConnId
		connectionStruct.UserId = s.UserId
		connectionStruct.IpAddress = s.IpAddress
		connectionStruct.ConnectedAt = s.ConnectedAt
		connectionStruct.UserAgent = s.UserAgent
		connectionStruct.Platform = s.Platform
		connectionStruct.IsFocused = int64(s.IsFocused)

		connectionList = append(connectionList, connectionStruct)
	}

	return &pb.SenderGetOnlineConnectionsByUserIdResponseStruct{
		OnlineConnectionList: connectionList,
	}, nil
}

// метод для закрытия подключений по идентификатору пользователя (userId)
func (s *Server) SenderCloseConnectionsByUserId(_ context.Context, in *pb.SenderCloseConnectionsByUserIdRequestStruct) (*pb.SenderCloseConnectionsByUserIdResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.SenderCloseConnectionsByUserIdResponseStruct{}, status.Error(503, "isolation not found")
	}

	err := talking.CloseConnectionsByUserId(isolation, in.GetUserId())
	if err != nil {
		return nil, status.Error(status.Code(err), status.Convert(err).Message())
	}

	return &pb.SenderCloseConnectionsByUserIdResponseStruct{}, nil
}

// метод для закрытия подключений по идентификатору пользователя (userId)
func (s *Server) SenderCloseConnectionsByUserIdWithWait(_ context.Context, in *pb.SenderCloseConnectionsByUserIdWithWaitRequestStruct) (*pb.SenderCloseConnectionsByUserIdWithWaitResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.SenderCloseConnectionsByUserIdWithWaitResponseStruct{}, status.Error(503, "isolation not found")
	}

	err := talking.CloseConnectionsByUserIdWithWait(isolation, in.GetUserId(), in.GetRoutineKey())
	if err != nil {
		return nil, status.Error(status.Code(err), status.Convert(err).Message())
	}

	return &pb.SenderCloseConnectionsByUserIdWithWaitResponseStruct{}, nil
}

// метод для закрытия подключений по идентификатору устройства (deviceId)
func (s *Server) SenderCloseConnectionsByDeviceIdWithWait(_ context.Context, in *pb.SenderCloseConnectionsByDeviceIdWithWaitRequestStruct) (*pb.SenderCloseConnectionsByDeviceIdWithWaitResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.SenderCloseConnectionsByDeviceIdWithWaitResponseStruct{}, status.Error(503, "isolation not found")
	}

	err := talking.CloseConnectionsByDeviceIdWithWait(isolation, in.GetUserId(), in.GetDeviceId(), in.GetRoutineKey())
	if err != nil {
		return nil, status.Error(status.Code(err), status.Convert(err).Message())
	}

	return &pb.SenderCloseConnectionsByDeviceIdWithWaitResponseStruct{}, nil
}

// метод для добавления задачи на отправку пушей пользователям в RabbitMq очередь микросервиса go_pusher
func (s *Server) SenderAddTaskPushNotification(_ context.Context, in *pb.SenderAddTaskPushNotificationRequestStruct) (*pb.SenderAddTaskPushNotificationResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.SenderAddTaskPushNotificationResponseStruct{}, status.Error(503, "isolation not found")
	}

	// коневртируем строки в интерфейсы для grpc
	var PushData push.PushDataStruct
	err := json.Unmarshal([]byte(in.GetPushData()), &PushData)
	if err != nil {
		return nil, status.Error(105, "bad json in request")
	}

	talking.AddTaskPushNotification(isolation, in.GetUserList(), PushData, in.GetUuid())

	return &pb.SenderAddTaskPushNotificationResponseStruct{}, nil
}

// метод для запроса онлайна ряда пользователей на ноды go_sender_*
func (s *Server) SenderGetOnlineUsers(_ context.Context, in *pb.SenderGetOnlineUsersRequestStruct) (*pb.SenderGetOnlineUsersResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.SenderGetOnlineUsersResponseStruct{}, status.Error(503, "isolation not found")
	}

	onlineUserList, offlineUserList := talking.GetOnlineUsers(isolation, in.GetUserList())

	return &pb.SenderGetOnlineUsersResponseStruct{
		OnlineUserList:  onlineUserList,
		OfflineUserList: offlineUserList,
	}, nil
}

// метод для добавления пользователя к треду
func (s *Server) SenderAddUsersToThread(_ context.Context, in *pb.SenderAddUsersToThreadRequestStruct) (*pb.SenderAddUsersToThreadResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.SenderAddUsersToThreadResponseStruct{}, status.Error(503, "isolation not found")
	}

	talking.AddUsersToThread(isolation, in.GetThreadKey(), in.GetUserList(), in.GetRoutineKey(), in.GetChannel())

	return &pb.SenderAddUsersToThreadResponseStruct{}, nil
}

// метод для отправки typing события на ноды go_sender_*
func (s *Server) SenderSendTypingEvent(_ context.Context, in *pb.SenderSendTypingEventRequestStruct) (*pb.SenderSendTypingEventResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.SenderSendTypingEventResponseStruct{}, status.Error(503, "isolation not found")
	}

	// конвертируем полученные версии события в более удобный формат
	eventListByVersion := sender.ConvertProtobufEventVersionList(in.GetEventVersionList())

	talking.SendTypingEvent(isolation, in.GetUserList(), in.GetEvent(), eventListByVersion, in.GetRoutineKey(), in.GetChannel())

	return &pb.SenderSendTypingEventResponseStruct{}, nil
}

// запрос для отправки voip пуша со звонком
func (s *Server) SenderSendVoIP(_ context.Context, in *pb.SenderSendVoIPRequestStruct) (*pb.SenderSendVoIPResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.SenderSendVoIPResponseStruct{}, status.Error(503, "isolation not found")
	}

	// коневртируем строки в интерфейсы для grpc
	var PushData interface{}
	err := json.Unmarshal([]byte(in.GetPushData()), &PushData)
	if err != nil {
		return nil, status.Error(105, "bad json in request")
	}

	talking.SendVoIP(isolation, in.GetUserId(), PushData, in.GetUuid(), in.GetTimeToLive(), in.GetRoutineKey())

	return &pb.SenderSendVoIPResponseStruct{}, nil
}

// запрос для отправки ws-события и voip-пуша о входящем звонке
func (s *Server) SenderSendIncomingCall(_ context.Context, in *pb.SenderSendIncomingCallRequestStruct) (*pb.SenderSendIncomingCallResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.SenderSendIncomingCallResponseStruct{}, status.Error(503, "isolation not found")
	}

	// коневртируем строки в интерфейсы для grpc
	var PushData, WsUsers interface{}

	err := json.Unmarshal([]byte(in.GetPushData()), &PushData)
	if err != nil {
		return nil, status.Error(105, "bad json in request")
	}

	err = json.Unmarshal([]byte(in.GetWsUsers()), &WsUsers)
	if err != nil {
		return nil, status.Error(105, "bad json in request")
	}

	// конвертируем полученные версии события в более удобный формат
	eventListByVersion := sender.ConvertProtobufEventVersionList(in.GetEventVersionList())

	talking.SendIncomingCall(isolation, in.GetUserId(), eventListByVersion, PushData, WsUsers, in.GetUuid(), in.GetTimeToLive(), in.GetRoutineKey(), in.GetChannel())

	return &pb.SenderSendIncomingCallResponseStruct{}, nil
}

// -------------------------------------------------------
// system methods group
// -------------------------------------------------------

// собираем информацию о состоянии системы
func (s *Server) SystemStatus(_ context.Context, _ *pb.SystemStatusRequestStruct) (*pb.SystemStatusResponseStruct, error) {

	statusInfoItem, err := system.Status(s.CompanyEnvList)
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
