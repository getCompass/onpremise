package grpcController

// user methods group

import (
	"context"
	pb "github.com/getCompassUtils/pivot_protobuf_schemes/go/activity"
	"go_activity/api/includes/controller/user"
)

// UserGetActivity получаем информацию по активности пользователя
func (s *Server) UserGetActivity(ctx context.Context, in *pb.UserGetActivityRequestStruct) (*pb.UserGetActivityResponseStruct, error) {

	userActivity, err := user.GetActivity(ctx, in.GetUserId())
	if err != nil {
		return &pb.UserGetActivityResponseStruct{}, err
	}

	return &pb.UserGetActivityResponseStruct{
		UserId:       userActivity.UserId,
		Status:       userActivity.Status,
		CreatedAt:    userActivity.CreatedAt,
		UpdatedAt:    userActivity.UpdatedAt,
		LastWsPingAt: userActivity.LastPingWsAt,
	}, err
}

// UserGetActivityList получает информацию по активности пользователей
func (s *Server) UserGetActivityList(ctx context.Context, in *pb.UserGetActivityListRequestStruct) (*pb.UserGetActivityListResponseStruct, error) {

	// получаем список активности пользователей
	userActivityList, err := user.GetActivityList(ctx, in.GetUserIdList())
	if err != nil {
		return &pb.UserGetActivityListResponseStruct{}, err
	}

	// преобразуем список в ответ
	var userActivities []*pb.UserGetActivityResponseStruct
	for _, activity := range userActivityList.ActivityList {
		userActivities = append(userActivities, &pb.UserGetActivityResponseStruct{
			UserId:       activity.UserId,
			Status:       activity.Status,
			CreatedAt:    activity.CreatedAt,
			UpdatedAt:    activity.UpdatedAt,
			LastWsPingAt: activity.LastPingWsAt,
		})
	}

	return &pb.UserGetActivityListResponseStruct{
		ActivityList: userActivities,
	}, nil
}

// UserResetCache выполняем сброс кэша
func (s *Server) UserResetCache(_ context.Context, _ *pb.UserResetCacheRequestStruct) (*pb.UserResetCacheResponseStruct, error) {

	user.ResetCache()

	return &pb.UserResetCacheResponseStruct{}, nil
}
