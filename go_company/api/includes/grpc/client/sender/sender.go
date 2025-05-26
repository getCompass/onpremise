package grpсSenderClient

import (
	"encoding/json"
	"fmt"
	pb "github.com/getCompassUtils/company_protobuf_schemes/go/sender"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_company/api/conf"
	Isolation "go_company/api/includes/type/isolation"
	"go_company/api/includes/type/sender"
	"google.golang.org/grpc"
	"google.golang.org/grpc/credentials/insecure"
)

func SendEventBatching(isolation *Isolation.Isolation, eventList []*sender.Event) error {

	conn, err := prepareConn()
	defer conn.Close()

	client := pb.NewSenderClient(conn)

	if err != nil {
		log.Errorf("cant connect to sender, error %v", err.Error())
		return err
	}

	// составляем список ивентов
	requestEventList := make([]*pb.SenderSendEventRequestStruct, 0, len(eventList))
	for _, event := range eventList {

		eventRequestStruct := &pb.SenderSendEventRequestStruct{
			Event:    event.Event,
			WsUsers:  event.WsUsers,
			PushData: event.PushData,
			Uuid:     event.UUID,
			Channel:  event.Channel,
		}

		// формируем список пользователей
		eventRequestStruct.UserList = make([]*pb.EventUserStruct, 0, len(event.UserIdList))

		for _, userId := range event.UserIdList {

			eventRequestStruct.UserList = append(eventRequestStruct.UserList, &pb.EventUserStruct{
				UserId:        userId,
				NeedPush:      0,
				NeedForcePush: 0,
			})
		}

		eventRequestStruct.EventVersionList = make([]*pb.EventVersionItem, 0, len(event.EventVersionList))

		// формируем список версий ивентов
		for _, eventVersioned := range event.EventVersionList {

			data, _ := json.Marshal(eventVersioned.Data)

			eventRequestStruct.EventVersionList = append(eventRequestStruct.EventVersionList, &pb.EventVersionItem{
				Version: int32(eventVersioned.Version),
				Data:    data,
			})
		}

		// добавляем ивент в общий список
		requestEventList = append(requestEventList, eventRequestStruct)
	}

	request := &pb.SenderSendEventBatchingRequestStruct{
		CompanyId: isolation.GetCompanyId(),
		EventList: requestEventList,
	}

	_, err = client.SenderSendEventBatching(isolation.Context, request)

	if err != nil {
		log.Errorf("cant send events: %v", err)
	}

	return nil
}

// подгтовить соединение
func prepareConn() (*grpc.ClientConn, error) {

	var opts []grpc.DialOption

	opts = append(opts, grpc.WithTransportCredentials(insecure.NewCredentials()))

	shardingConfig, err := conf.GetShardingConfig()

	if err != nil {

		log.Error("cant find sharding config")
		return nil, err
	}
	senderConfig, exist := shardingConfig.Go["sender"]

	if !exist {

		log.Errorf("can not find sender")
		return nil, err
	}

	conn, err := grpc.NewClient(fmt.Sprintf("%s:%s", senderConfig.Host, senderConfig.GrpcPort), opts...)

	if err != nil {

		log.Errorf("can connect to sender")
		return nil, err
	}
	return conn, nil
}
