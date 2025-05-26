package grpcController

import (
	"context"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	readMessages "go_company/api/includes/methods/read_messages"

	pb "github.com/getCompassUtils/company_protobuf_schemes/go/company"
	"google.golang.org/grpc/status"
)

// -------------------------------------------------------
// readMessage methods group
// -------------------------------------------------------

// ReadMessageAdd добавить прочитанное сообщение
func (s *Server) ReadMessageAdd(_ context.Context, in *pb.ReadMessageAddRequestStruct) (*pb.ReadMessageAddResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.ReadMessageAddResponseStruct{}, status.Error(503, "isolation not found")
	}

	log.Infof("Добавили задачу для %s %s пользователю %d", in.GetEntityType(), in.GetEntityMap(), in.GetUserId())
	readMessages.Add(
		isolation,
		in.GetEntityType(),
		in.GetEntityMap(),
		in.GetEntityMetaId(),
		in.GetEntityKey(),
		in.GetUserId(),
		in.GetMessageMap(),
		in.GetMessageKey(),
		in.GetEntityMessageIndex(),
		in.GetMessageCreatedAt(),
		in.GetReadAt(),
		int(in.GetTableShard()),
		int(in.GetDbShard()),
		in.GetHideReadParticipantList(),
	)

	return &pb.ReadMessageAddResponseStruct{}, nil
}
