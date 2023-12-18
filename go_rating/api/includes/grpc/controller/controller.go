package grpcController

import (
	"context"
	"fmt"
	pb "github.com/getCompassUtils/company_protobuf_schemes/go/rating"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	CompanyEnvironment "go_rating/api/includes/type/company_config"
	"go_rating/api/includes/type/rating/scrapping/user_action"
	"go_rating/api/includes/type/rating/scrapping/user_answer_time"
	"go_rating/api/includes/type/rating/scrapping/user_screen_time"
	"google.golang.org/grpc/status"
	"time"
)

// пакет с gRPC контролером — содержит в себе все методы микросервиса
// вынесен отдельно, так как grpc реализует свой роутер запросов и интерфейс работы с request/response

type Server struct {
	pb.RatingServer
}

// RatingAddScreenTime добавляем пользователю экранное время
func (s *Server) RatingAddScreenTime(_ context.Context, in *pb.RatingAddScreenTimeRequestStruct) (*pb.RatingAddScreenTimeResponseStruct, error) {

	isolation := CompanyEnvironment.GetEnv(in.GetSpaceId())
	if isolation == nil {
		return &pb.RatingAddScreenTimeResponseStruct{}, status.Error(503, "isolation not found")
	}

	start := time.Now()

	if err := user_screen_time.Push(isolation, in.GetUserId(), in.GetScreenTime(), in.GetLocalOnlineAt()); err != nil {
		log.Errorf("can't add screen time in cache — %s", err.Error())
	}

	if time.Since(start) > time.Millisecond*100 {

		log.Errorf(fmt.Sprintf("\r\n// -------------------------------------------------------\r\n"+
			"// [includes/handler/grpc/rating.go][AddScreenTime] Время выполнения метода: %s\r\n"+
			"// -------------------------------------------------------", time.Since(start)))
	}

	return &pb.RatingAddScreenTimeResponseStruct{}, nil
}

// RatingIncActionCount инкрементим количество действий
func (s *Server) RatingIncActionCount(_ context.Context, in *pb.RatingIncActionCountRequestStruct) (*pb.RatingIncActionCountResponseStruct, error) {

	isolation := CompanyEnvironment.GetEnv(in.GetSpaceId())
	if isolation == nil {
		return &pb.RatingIncActionCountResponseStruct{}, status.Error(503, "isolation not found")
	}

	start := time.Now()

	if err := user_action.Push(isolation, in.GetUserId(), in.GetAction(), in.GetConversationMap(), in.GetIsHuman()); err != nil {
		log.Errorf("can't add screen time in cache — %s", err.Error())
	}

	if time.Since(start) > time.Millisecond*100 {

		log.Errorf(fmt.Sprintf("\r\n// -------------------------------------------------------\r\n"+
			"// [includes/handler/grpc/rating.go][AddScreenTime] Время выполнения метода: %s\r\n"+
			"// -------------------------------------------------------", time.Since(start)))
	}

	return &pb.RatingIncActionCountResponseStruct{}, nil
}

// RatingUpdateConversationAnswerState обновляем информацию об ответе на сообщение в диалоге
func (s *Server) RatingUpdateConversationAnswerState(_ context.Context, in *pb.RatingUpdateConversationAnswerStateRequestStruct) (*pb.RatingUpdateConversationAnswerStateResponseStruct, error) {

	isolation := CompanyEnvironment.GetEnv(in.GetSpaceId())
	if isolation == nil {
		return &pb.RatingUpdateConversationAnswerStateResponseStruct{}, status.Error(503, "isolation not found")
	}

	start := time.Now()

	spaceId, conversationKey, answerTime, createdAt, microConversationStartAt, microConversationEndAt, err := user_answer_time.PushConversationAnswerState(
		isolation,
		in.GetConversationKey(),
		in.GetSenderUserId(),
		in.GetReceiverUserIdList(),
		in.GetSentAt(),
		in.GetLocalSentAt(),
	)
	if err != nil {
		log.Errorf("can't add answer time in cache — %s", err.Error())
	}

	if time.Since(start) > time.Millisecond*100 {

		log.Errorf(fmt.Sprintf("\r\n// -------------------------------------------------------\r\n"+
			"// [includes/handler/grpc/rating.go][UpdateConversationAnswerState] Время выполнения метода: %s\r\n"+
			"// -------------------------------------------------------", time.Since(start)))
	}

	return &pb.RatingUpdateConversationAnswerStateResponseStruct{
		SpaceId:                  spaceId,
		ConversationKey:          conversationKey,
		AnswerTime:               answerTime,
		CreatedAt:                createdAt,
		MicroConversationStartAt: microConversationStartAt,
		MicroConversationEndAt:   microConversationEndAt,
	}, nil
}

// RatingUpdateConversationAnswerStateForReceivers обновляем информацию о полученных сообщениях в диалоге
func (s *Server) RatingUpdateConversationAnswerStateForReceivers(_ context.Context, in *pb.RatingUpdateConversationAnswerStateForReceiversRequestStruct) (*pb.RatingUpdateConversationAnswerStateForReceiversResponseStruct, error) {

	isolation := CompanyEnvironment.GetEnv(in.GetSpaceId())
	if isolation == nil {
		return &pb.RatingUpdateConversationAnswerStateForReceiversResponseStruct{}, status.Error(503, "isolation not found")
	}

	start := time.Now()

	if err := user_answer_time.PushForReceivers(
		isolation,
		in.GetConversationKey(),
		in.GetSenderUserId(),
		in.GetReceiverUserIdList(),
		in.GetSentAt(),
		in.GetLocalSentAt(),
	); err != nil {
		log.Errorf("can't add receiver answer time in cache — %s", err.Error())
	}

	if time.Since(start) > time.Millisecond*100 {

		log.Errorf(fmt.Sprintf("\r\n// -------------------------------------------------------\r\n"+
			"// [includes/handler/grpc/rating.go][UpdateConversationAnswerStateForReceivers] Время выполнения метода: %s\r\n"+
			"// -------------------------------------------------------", time.Since(start)))
	}

	return &pb.RatingUpdateConversationAnswerStateForReceiversResponseStruct{}, nil
}

// RatingCloseMicroConversation закрываем микро-диалог
func (s *Server) RatingCloseMicroConversation(_ context.Context, in *pb.RatingCloseMicroConversationRequestStruct) (*pb.RatingCloseMicroConversationResponseStruct, error) {

	isolation := CompanyEnvironment.GetEnv(in.GetSpaceId())
	if isolation == nil {
		return &pb.RatingCloseMicroConversationResponseStruct{}, status.Error(503, "isolation not found")
	}

	start := time.Now()

	if err := user_answer_time.CloseMicroConversation(isolation, in.GetConversationKey(), in.GetSenderUserId(), in.GetReceiverUserIdList()); err != nil {
		log.Errorf("can't close micro conversation — %s", err.Error())
	}

	if time.Since(start) > time.Millisecond*100 {

		log.Errorf(fmt.Sprintf("\r\n// -------------------------------------------------------\r\n"+
			"// [includes/handler/grpc/rating.go][RatingCloseMicroConversation] Время выполнения метода: %s\r\n"+
			"// -------------------------------------------------------", time.Since(start)))
	}

	return &pb.RatingCloseMicroConversationResponseStruct{}, nil
}
