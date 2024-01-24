package grpcController

// session methods group

import (
	"context"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	pb "github.com/getCompassUtils/pivot_protobuf_schemes/go/pivot_cache"
	"go_pivot_cache/api/includes/controller/session"
	"time"
)

// получаем информацию по сессии
func (s *Server) SessionGetInfo(ctx context.Context, in *pb.SessionGetInfoRequestStruct) (*pb.SessionGetInfoResponseStruct, error) {

	start := time.Now()

	userId, refreshedAt, err := session.GetInfo(ctx, in.GetSessionUniq(), in.GetShardId(), in.GetTableId())
	if err != nil {
		return &pb.SessionGetInfoResponseStruct{}, err
	}

	if time.Since(start) > time.Millisecond*100 {

		log.Errorf(fmt.Sprintf("\r\n// -------------------------------------------------------\r\n"+
			"// [includes/grpc/controller/controller.go][getInfo] Время выполнения метода: %s\r\n"+
			"// -------------------------------------------------------", time.Since(start)))
	}

	return &pb.SessionGetInfoResponseStruct{
		UserId:      userId,
		RefreshedAt: refreshedAt,
	}, err
}

// удалить все сессии пользователя по его userID
func (s *Server) SessionDeleteByUserId(_ context.Context, in *pb.SessionDeleteByUserIdRequestStruct) (*pb.SessionDeleteByUserIdResponseStruct, error) {

	session.DeleteByUserId(in.GetUserId())

	return &pb.SessionDeleteByUserIdResponseStruct{}, nil
}

// удалить конкретную сессию из кэша
func (s *Server) SessionDeleteBySessionUniq(_ context.Context, in *pb.SessionDeleteBySessionUniqRequestStruct) (*pb.SessionDeleteBySessionUniqResponseStruct, error) {

	session.DeleteBySessionUniq(in.GetSessionUniq())

	return &pb.SessionDeleteBySessionUniqResponseStruct{}, nil
}

// удаляет информацио о пользователе, не трогает сессию
func (s *Server) SessionDeleteUserInfo(_ context.Context, in *pb.SessionDeleteUserInfoRequestStruct) (*pb.SessionDeleteUserInfoResponseStruct, error) {

	session.DeleteUserInfo(in.GetUserId())

	return &pb.SessionDeleteUserInfoResponseStruct{}, nil
}

// выполняем сброс кэша
func (s *Server) SessionResetCache(_ context.Context, _ *pb.SessionResetCacheRequestStruct) (*pb.SessionResetCacheResponseStruct, error) {

	session.ResetCache()

	return &pb.SessionResetCacheResponseStruct{}, nil
}
