package grpcController

// session methods group
import (
	"context"
	"fmt"
	pb "github.com/getCompassUtils/company_protobuf_schemes/go/company_cache"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_company_cache/api/includes/controller/session"
	"google.golang.org/grpc/status"
	"time"
)

// получаем информацию по сессии
func (s *Server) SessionGetInfo(_ context.Context, in *pb.SessionGetInfoRequestStruct) (*pb.SessionGetInfoResponseStruct, error) {

	start := time.Now()
	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.SessionGetInfoResponseStruct{}, status.Error(503, "isolation not found")
	}
	sessionInfo, err := session.GetInfo(isolation, in.GetSessionUniq())
	if err != nil {
		return &pb.SessionGetInfoResponseStruct{}, err
	}

	if time.Since(start) > time.Millisecond*100 {

		log.Errorf(fmt.Sprintf("\r\n// -------------------------------------------------------\r\n"+
			"// [includes/grpc/controller/controller.go][getInfo] Время выполнения метода: %s\r\n"+
			"// -------------------------------------------------------", time.Since(start)))
	}

	member := &pb.MemberInfoStruct{
		UserId:            sessionInfo.Member.UserId,
		Role:              sessionInfo.Member.Role,
		Permissions:       sessionInfo.Member.Permissions,
		CreatedAt:         sessionInfo.Member.CreatedAt,
		UpdatedAt:         sessionInfo.Member.UpdatedAt,
		NpcType:           sessionInfo.Member.NpcType,
		CompanyJoinedAt:   sessionInfo.Member.CompanyJoinedAt,
		LeftAt:            sessionInfo.Member.LeftAt,
		FullnameUpdatedAt: sessionInfo.Member.FullNameUpdatedAt,
		Fullname:          sessionInfo.Member.FullName,
		MbtiType:          sessionInfo.Member.MbtiType,
		ShortDescription:  sessionInfo.Member.ShortDescription,
		AvatarFileKey:     sessionInfo.Member.AvatarFileKey,
		Comment:           sessionInfo.Member.Comment,
		Extra:             sessionInfo.Member.Extra,
	}

	return &pb.SessionGetInfoResponseStruct{
		UserId:    sessionInfo.UserID,
		UaHash:    sessionInfo.UserAgent,
		IpAddress: sessionInfo.IpAddress,
		Extra:     sessionInfo.Extra,
		Member:    member,
	}, err
}

// удалить все сессии пользователя по его userID
func (s *Server) SessionDeleteByUserId(_ context.Context, in *pb.SessionDeleteByUserIdRequestStruct) (*pb.SessionDeleteByUserIdResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.SessionDeleteByUserIdResponseStruct{}, status.Error(503, "isolation not found")
	}
	session.DeleteByUserId(isolation, in.GetUserId())

	return &pb.SessionDeleteByUserIdResponseStruct{}, nil
}

// получить сессии пользователя по его userID
func (s *Server) SessionGetListByUserId(_ context.Context, in *pb.SessionGetListByUserIdRequestStruct) (*pb.SessionGetListByUserIdResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.SessionGetListByUserIdResponseStruct{}, status.Error(503, "isolation not found")
	}
	sessionUniqList := session.GetListByUserId(isolation, in.GetUserId())

	return &pb.SessionGetListByUserIdResponseStruct{
		SessionUniqList: sessionUniqList,
	}, nil
}

// удалить конкретную сессию из кэша
func (s *Server) SessionDeleteBySessionUniq(_ context.Context, in *pb.SessionDeleteBySessionUniqRequestStruct) (*pb.SessionDeleteBySessionUniqResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.SessionDeleteBySessionUniqResponseStruct{}, status.Error(503, "isolation not found")
	}
	session.DeleteBySessionUniq(isolation, in.GetSessionUniq())

	return &pb.SessionDeleteBySessionUniqResponseStruct{}, nil
}

// очистить кэш
func (s *Server) SessionClearCache(_ context.Context, in *pb.SessionClearCacheRequestStruct) (*pb.SessionClearCacheResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.SessionClearCacheResponseStruct{}, status.Error(503, "isolation not found")
	}
	session.ClearCache(isolation)

	return &pb.SessionClearCacheResponseStruct{}, nil
}
