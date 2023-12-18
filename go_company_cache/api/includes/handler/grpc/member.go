package grpcController

// member methods group
import (
	"context"
	"fmt"
	pb "github.com/getCompassUtils/company_protobuf_schemes/go/company_cache"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_company_cache/api/includes/controller/member"
	"google.golang.org/grpc/status"
	"time"
)

// MemberGetList получаем информацию
func (s *Server) MemberGetList(_ context.Context, in *pb.MemberGetListRequestStruct) (*pb.MemberGetListResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.MemberGetListResponseStruct{}, status.Error(503, "isolation not found")
	}

	start := time.Now()

	memberList, notFoundList := member.GetList(isolation, in.GetUserIdList())

	if time.Since(start) > time.Millisecond*100 {

		log.Errorf(fmt.Sprintf("\r\n// -------------------------------------------------------\r\n"+
			"// [includes/grpc/controller/controller.go][getInfo] Время выполнения метода: %s\r\n"+
			"// -------------------------------------------------------", time.Since(start)))
	}

	var MemberGetListResponseStruct pb.MemberGetListResponseStruct
	MemberGetListResponseStruct.NotFoundList = notFoundList
	for _, v := range memberList {

		MemberGetListResponseStruct.MemberList = append(MemberGetListResponseStruct.MemberList, &pb.MemberInfoStruct{
			UserId:            v.UserId,
			Role:              v.Role,
			NpcType:           v.NpcType,
			Permissions:       v.Permissions,
			CreatedAt:         v.CreatedAt,
			UpdatedAt:         v.UpdatedAt,
			CompanyJoinedAt:   v.CompanyJoinedAt,
			LeftAt:            v.LeftAt,
			FullnameUpdatedAt: v.FullNameUpdatedAt,
			Fullname:          v.FullName,
			MbtiType:          v.MbtiType,
			ShortDescription:  v.ShortDescription,
			AvatarFileKey:     v.AvatarFileKey,
			Comment:           v.Comment,
			Extra:             v.Extra,
		})
	}
	return &MemberGetListResponseStruct, nil
}

// MemberGetShortList получаем информацию
func (s *Server) MemberGetShortList(_ context.Context, in *pb.MemberGetShortListRequestStruct) (*pb.MemberGetShortListResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.MemberGetShortListResponseStruct{}, status.Error(503, "isolation not found")
	}

	start := time.Now()

	memberList, notFoundList := member.GetList(isolation, in.GetUserIdList())

	if time.Since(start) > time.Millisecond*100 {

		log.Errorf(fmt.Sprintf("\r\n// -------------------------------------------------------\r\n"+
			"// [includes/grpc/controller/controller.go][getInfo] Время выполнения метода: %s\r\n"+
			"// -------------------------------------------------------", time.Since(start)))
	}

	var MemberGetListResponseStruct pb.MemberGetShortListResponseStruct
	MemberGetListResponseStruct.NotFoundList = notFoundList
	for _, v := range memberList {

		MemberGetListResponseStruct.MemberList = append(MemberGetListResponseStruct.MemberList, &pb.MemberShortInfoStruct{
			UserId:      v.UserId,
			Role:        v.Role,
			Permissions: v.Permissions,
			NpcType:     v.NpcType,
		})
	}
	return &MemberGetListResponseStruct, nil
}

// MemberGetOne получаем информацию об одно пользователе
func (s *Server) MemberGetOne(_ context.Context, in *pb.MemberGetOneRequestStruct) (*pb.MemberGetOneResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.MemberGetOneResponseStruct{}, status.Error(503, "isolation not found")
	}

	start := time.Now()

	memberItem, exist := member.GetOne(isolation, in.GetUserId())

	if time.Since(start) > time.Millisecond*100 {

		log.Errorf(fmt.Sprintf("\r\n// -------------------------------------------------------\r\n"+
			"// [includes/grpc/controller/controller.go][MemberGetOne] Время выполнения метода: %s\r\n"+
			"// -------------------------------------------------------", time.Since(start)))
	}

	var MemberGetOneResponseStruct pb.MemberGetOneResponseStruct
	MemberGetOneResponseStruct.Exist = exist

	if exist {

		MemberGetOneResponseStruct.Member = &pb.MemberInfoStruct{
			UserId:            memberItem.UserId,
			Role:              memberItem.Role,
			Permissions:       memberItem.Permissions,
			CreatedAt:         memberItem.CreatedAt,
			UpdatedAt:         memberItem.UpdatedAt,
			NpcType:           memberItem.NpcType,
			CompanyJoinedAt:   memberItem.CompanyJoinedAt,
			LeftAt:            memberItem.LeftAt,
			FullnameUpdatedAt: memberItem.FullNameUpdatedAt,
			Fullname:          memberItem.FullName,
			MbtiType:          memberItem.MbtiType,
			ShortDescription:  memberItem.ShortDescription,
			AvatarFileKey:     memberItem.AvatarFileKey,
			Comment:           memberItem.Comment,
			Extra:             memberItem.Extra,
		}
	}

	return &MemberGetOneResponseStruct, nil
}

// MemberDeleteFromCacheByUserId удалить все сессии пользователя по его userID
func (s *Server) MemberDeleteFromCacheByUserId(_ context.Context, in *pb.MemberDeleteFromCacheByUserIdRequestStruct) (*pb.MemberDeleteFromCacheByUserIdResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.MemberDeleteFromCacheByUserIdResponseStruct{}, status.Error(503, "isolation not found")
	}

	member.DeleteFromCacheByUserId(isolation, in.GetUserId())

	return &pb.MemberDeleteFromCacheByUserIdResponseStruct{}, nil
}

// MemberDeleteFromCacheByUserIdList удалить все сессии пользователя по его userID
func (s *Server) MemberDeleteFromCacheByUserIdList(_ context.Context, in *pb.MemberDeleteFromCacheByUserIdListRequestStruct) (*pb.MemberDeleteFromCacheByUserIdListResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.MemberDeleteFromCacheByUserIdListResponseStruct{}, status.Error(503, "isolation not found")
	}

	member.DeleteFromCacheByUserIdList(isolation, in.GetUserIdList())

	return &pb.MemberDeleteFromCacheByUserIdListResponseStruct{}, nil
}

// очистить кэш пользователей
func (s *Server) MemberClearCache(_ context.Context, in *pb.MemberClearCacheRequestStruct) (*pb.MemberClearCacheResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.MemberClearCacheResponseStruct{}, status.Error(503, "isolation not found")
	}

	member.ClearCache(isolation)

	return &pb.MemberClearCacheResponseStruct{}, nil
}
