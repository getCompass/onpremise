package grpcController

// user methods group

import (
	"context"
	pb "github.com/getCompassUtils/pivot_protobuf_schemes/go/pivot_cache"
	"go_pivot_cache/api/includes/controller/user"
	"go_pivot_cache/api/includes/structures"
)

// получаем информацию по пользователю
func (s *Server) UserGetInfo(_ context.Context, in *pb.UsersGetInfoRequestStruct) (*pb.UsersGetInfoResponseStruct, error) {

	userInfo, err := user.GetInfo(in.GetUserId())
	if err != nil {
		return &pb.UsersGetInfoResponseStruct{}, err
	}

	return &pb.UsersGetInfoResponseStruct{
		UserId:               userInfo.UserId,
		NpcType:              userInfo.NpcType,
		InvitedByPartnerId:   userInfo.InvitedByPartnerId,
		LastActiveDayStartAt: userInfo.LastActiveDayStartAt,
		InvitedByUserId:      userInfo.InvitedByUserId,
		CountryCode:          userInfo.CountryCode,
		FullNameUpdatedAt:    userInfo.FullNameUpdatedAt,
		FullName:             userInfo.FullName,
		AvatarFileMap:        userInfo.AvatarFileMap,
		CreatedAt:            userInfo.CreatedAt,
		UpdatedAt:            userInfo.UpdatedAt,
		Extra:                userInfo.Extra,
	}, err
}

// получаем информацию по списку пользователей
func (s *Server) UserGetInfoList(_ context.Context, in *pb.UsersGetInfoListRequestStruct) (*pb.UsersGetInfoListResponseStruct, error) {

	userInfoList, err := user.GetListInfo(in.GetUserIdList())

	if err != nil {
		return &pb.UsersGetInfoListResponseStruct{}, err
	}

	userList := make([]*pb.UsersGetInfoResponseStruct, len(userInfoList.UserList))

	for key, userInfo := range userInfoList.UserList {
		userList[key] = prepareUsersGetInfoResponseStruct(userInfo)
	}
	return &pb.UsersGetInfoListResponseStruct{
		UserList: userList,
	}, err
}

// собираем массив пользователей к объект UsersGetInfoResponseStruct
func prepareUsersGetInfoResponseStruct(userInfo structures.UserInfoStruct) *pb.UsersGetInfoResponseStruct {

	return &pb.UsersGetInfoResponseStruct{
		UserId:               userInfo.UserId,
		NpcType:              userInfo.NpcType,
		InvitedByPartnerId:   userInfo.InvitedByPartnerId,
		LastActiveDayStartAt: userInfo.LastActiveDayStartAt,
		InvitedByUserId:      userInfo.InvitedByUserId,
		CountryCode:          userInfo.CountryCode,
		FullNameUpdatedAt:    userInfo.FullNameUpdatedAt,
		FullName:             userInfo.FullName,
		AvatarFileMap:        userInfo.AvatarFileMap,
		CreatedAt:            userInfo.CreatedAt,
		UpdatedAt:            userInfo.UpdatedAt,
		Extra:                userInfo.Extra,
	}
}

// выполняем сброс кэша
func (s *Server) UserResetCache(_ context.Context, _ *pb.UserResetCacheRequestStruct) (*pb.UserResetCacheResponseStruct, error) {

	user.ResetCache()

	return &pb.UserResetCacheResponseStruct{}, nil
}
