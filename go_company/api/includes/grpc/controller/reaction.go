package grpcController

import (
	"context"
	pb "github.com/getCompassUtils/company_protobuf_schemes/go/company"
	"github.com/getCompassUtils/go_base_frame"
	"go_company/api/includes/methods/reactions"
	"go_company/api/includes/type/structures"
	"google.golang.org/grpc/status"
)

// -------------------------------------------------------
// reactions methods group
// -------------------------------------------------------

// метод ReactionsAddInConversation
func (s *Server) ReactionsAddInConversation(_ context.Context, in *pb.ReactionsAddInConversationRequestStruct) (*pb.ReactionsAddInConversationResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.ReactionsAddInConversationResponseStruct{}, status.Error(503, "isolation not found")
	}

	var WsUserList interface{}

	err := go_base_frame.Json.Unmarshal([]byte(in.GetConversationReaction().GetWsUserList()), &WsUserList)
	if err != nil {
		return &pb.ReactionsAddInConversationResponseStruct{}, status.Error(105, "bad json in request")
	}

	convertedEventVersionList := convertGrpcEventVersionItemToInternalStruct(in.GetConversationReaction().GetEventVersionList())
	err = reactions.AddInConversation(
		isolation,
		in.GetConversationReaction().GetConversationMap(),
		in.GetConversationReaction().GetMessageMap(),
		in.GetConversationReaction().GetBlockId(),
		in.GetConversationReaction().GetReactionName(),
		in.GetConversationReaction().GetUserId(),
		in.GetConversationReaction().GetUpdatedAtMs(),
		WsUserList,
		convertedEventVersionList,
	)
	if err != nil {
		return &pb.ReactionsAddInConversationResponseStruct{}, status.Error(status.Code(err), status.Convert(err).Message())
	}

	return &pb.ReactionsAddInConversationResponseStruct{}, nil
}

// метод ReactionsRemoveInConversation
func (s *Server) ReactionsRemoveInConversation(_ context.Context, in *pb.ReactionsRemoveInConversationRequestStruct) (*pb.ReactionsRemoveInConversationResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.ReactionsRemoveInConversationResponseStruct{}, status.Error(503, "isolation not found")
	}

	var WsUserList interface{}

	err := go_base_frame.Json.Unmarshal([]byte(in.GetConversationReaction().GetWsUserList()), &WsUserList)
	if err != nil {
		return &pb.ReactionsRemoveInConversationResponseStruct{}, status.Error(105, "bad json in request")
	}

	convertedEventVersionList := convertGrpcEventVersionItemToInternalStruct(in.GetConversationReaction().GetEventVersionList())
	err = reactions.RemoveInConversation(isolation,
		in.GetConversationReaction().GetConversationMap(),
		in.GetConversationReaction().GetMessageMap(),
		in.GetConversationReaction().GetBlockId(),
		in.GetConversationReaction().GetReactionName(),
		in.GetConversationReaction().GetUserId(),
		in.GetConversationReaction().GetUpdatedAtMs(),
		WsUserList,
		convertedEventVersionList,
	)
	if err != nil {
		return &pb.ReactionsRemoveInConversationResponseStruct{}, status.Error(status.Code(err), status.Convert(err).Message())
	}

	return &pb.ReactionsRemoveInConversationResponseStruct{}, nil
}

// метод ReactionsAddInThread
func (s *Server) ReactionsAddInThread(_ context.Context, in *pb.ReactionsAddInThreadRequestStruct) (*pb.ReactionsAddInThreadResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.ReactionsAddInThreadResponseStruct{}, status.Error(503, "isolation not found")
	}

	var WsUserList interface{}

	err := go_base_frame.Json.Unmarshal([]byte(in.GetThreadReaction().GetWsUserList()), &WsUserList)
	if err != nil {
		return &pb.ReactionsAddInThreadResponseStruct{}, status.Error(105, "bad json in request")
	}

	convertedEventVersionList := convertGrpcEventVersionItemToInternalStruct(in.GetThreadReaction().GetEventVersionList())
	err = reactions.AddInThread(
		isolation,
		in.GetThreadReaction().GetThreadMap(),
		in.GetThreadReaction().GetMessageMap(),
		in.GetThreadReaction().GetBlockId(),
		in.GetThreadReaction().GetReactionName(),
		in.GetThreadReaction().GetUserId(),
		in.GetThreadReaction().GetUpdatedAtMs(),
		WsUserList,
		convertedEventVersionList,
	)
	if err != nil {
		return &pb.ReactionsAddInThreadResponseStruct{}, status.Error(status.Code(err), status.Convert(err).Message())
	}

	return &pb.ReactionsAddInThreadResponseStruct{}, nil
}

// метод ReactionsRemoveInThread
func (s *Server) ReactionsRemoveInThread(_ context.Context, in *pb.ReactionsRemoveInThreadRequestStruct) (*pb.ReactionsRemoveInThreadResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.ReactionsRemoveInThreadResponseStruct{}, status.Error(503, "isolation not found")
	}

	var WsUserList interface{}

	err := go_base_frame.Json.Unmarshal([]byte(in.GetThreadReaction().GetWsUserList()), &WsUserList)
	if err != nil {
		return &pb.ReactionsRemoveInThreadResponseStruct{}, status.Error(105, "bad json in request")
	}

	convertedEventVersionList := convertGrpcEventVersionItemToInternalStruct(in.GetThreadReaction().GetEventVersionList())
	err = reactions.RemoveInThread(isolation,
		in.GetThreadReaction().GetThreadMap(),
		in.GetThreadReaction().GetMessageMap(),
		in.GetThreadReaction().GetBlockId(),
		in.GetThreadReaction().GetReactionName(),
		in.GetThreadReaction().GetUserId(),
		in.GetThreadReaction().GetUpdatedAtMs(),
		WsUserList,
		convertedEventVersionList,
	)
	if err != nil {
		return &pb.ReactionsRemoveInThreadResponseStruct{}, status.Error(status.Code(err), status.Convert(err).Message())
	}

	return &pb.ReactionsRemoveInThreadResponseStruct{}, nil
}

// функция для конвертации grpc структуры []*EventVersionItem в []structures.WsEventVersionItemStruct
func convertGrpcEventVersionItemToInternalStruct(eventVersionList []*pb.EventVersionItem) []structures.WsEventVersionItemStruct {

	var output []structures.WsEventVersionItemStruct
	for _, eventVersionItem := range eventVersionList {

		output = append(output, structures.WsEventVersionItemStruct{
			Version: int(eventVersionItem.Version),
			Data:    eventVersionItem.Data,
		})
	}

	return output
}
