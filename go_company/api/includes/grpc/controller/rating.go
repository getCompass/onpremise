package grpcController

import (
	"context"
	pb "github.com/getCompassUtils/company_protobuf_schemes/go/company"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_company/api/includes/methods/methods_rating"
	"google.golang.org/grpc/status"
)

// -------------------------------------------------------
// rating methods group
// -------------------------------------------------------

// метод RatingInc
func (s *Server) RatingInc(_ context.Context, in *pb.RatingIncRequestStruct) (*pb.RatingIncResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.RatingIncResponseStruct{}, status.Error(503, "isolation not found")
	}

	err := methods_rating.Inc(isolation.RatingStore, in.GetEvent(), int64(in.GetUserId()), int(in.GetInc()))
	if err != nil {
		return &pb.RatingIncResponseStruct{}, status.Error(status.Code(err), status.Convert(err).Message())
	}

	return &pb.RatingIncResponseStruct{}, nil
}

// метод RatingGet
func (s *Server) RatingGet(_ context.Context, in *pb.RatingGetRequestStruct) (*pb.RatingGetResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {

		log.Errorf("isolation not found")
		return &pb.RatingGetResponseStruct{}, status.Error(503, "isolation not found")
	}

	_, response, err := methods_rating.Get(isolation.Context, isolation.MainStorage, isolation.CompanyDataConn, in.GetEvent(), int(in.GetFromDateAt()),
		int(in.GetToDateAt()), int(in.GetTopListOffset()), int(in.GetTopListCount()))
	if err != nil {

		log.Errorf(err.Error())
		return response, status.Error(status.Code(err), status.Convert(err).Message())
	}

	return response, nil
}

// метод RatingGetByUserId
func (s *Server) RatingGetByUserId(_ context.Context, in *pb.RatingGetByUserIdRequestStruct) (*pb.RatingGetByUserIdListResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.RatingGetByUserIdListResponseStruct{}, status.Error(503, "isolation not found")
	}

	_, userRating, err := methods_rating.GetByUserId(isolation.Context, isolation.CompanyDataConn, isolation.UserRatingByDays, int64(in.GetUserId()), int(in.GetYear()),
		[]int64(in.GetFromDateAtList()), []int64(in.GetToDateAtList()), []int64(in.GetIsFromCacheList()))
	if err != nil {
		return nil, status.Error(status.Code(err), status.Convert(err).Message())
	}

	return userRating, nil
}

// метод RatingGetEventCountByInterval
func (s *Server) RatingGetEventCountByInterval(_ context.Context, in *pb.RatingGetEventCountByIntervalRequestStruct) (*pb.RatingGetEventCountByIntervalResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.RatingGetEventCountByIntervalResponseStruct{}, status.Error(503, "isolation not found")
	}

	_, eventCountList, err := methods_rating.GetEventCountByInterval(isolation.Context, isolation.CompanyDataConn, in.GetEvent(), int(in.GetYear()),
		int(in.GetFromDateAt()), int(in.GetToDateAt()))
	if err != nil {
		return nil, status.Error(status.Code(err), status.Convert(err).Message())
	}

	return &pb.RatingGetEventCountByIntervalResponseStruct{
		EventCountList: eventCountList,
	}, nil
}

// метод RatingGetGeneralEventCountByInterval
func (s *Server) RatingGetGeneralEventCountByInterval(_ context.Context,
	in *pb.RatingGetGeneralEventCountByIntervalRequestStruct) (*pb.RatingGetGeneralEventCountByIntervalResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.RatingGetGeneralEventCountByIntervalResponseStruct{}, status.Error(503, "isolation not found")
	}

	_, eventCountList, err := methods_rating.GetGeneralEventCountByInterval(isolation.Context, isolation.CompanyDataConn, int(in.GetYear()),
		int(in.GetFromDateAt()), int(in.GetToDateAt()))
	if err != nil {
		return nil, status.Error(status.Code(err), status.Convert(err).Message())
	}

	return &pb.RatingGetGeneralEventCountByIntervalResponseStruct{
		EventCountList: eventCountList,
	}, nil
}

// метод ForceSaveCache
func (s *Server) RatingForceSaveCache(_ context.Context, in *pb.RatingForceSaveCacheRequestStruct) (*pb.RatingForceSaveCacheResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.RatingForceSaveCacheResponseStruct{}, status.Error(503, "isolation not found")
	}

	// не ждём ответа, просто запускаем рутину
	methods_rating.ForceSaveCache(isolation.Context, isolation.MainStorage, isolation.RatingStore, isolation.CompanyDataConn, isolation.GetGlobalIsolation())
	return &pb.RatingForceSaveCacheResponseStruct{}, nil
}

// получаем всю статистику по пользователям за день
func (s *Server) RatingGetListByDay(_ context.Context, in *pb.RatingGetListByDayRequestStruct) (*pb.RatingGetListByDayResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.RatingGetListByDayResponseStruct{}, status.Error(503, "isolation not found")
	}

	_, list, err := methods_rating.GetListByDay(isolation.Context, isolation.CompanyDataConn, int(in.GetYear()), int(in.GetDay()))
	if err != nil {
		return nil, status.Error(status.Code(err), status.Convert(err).Message())
	}

	return &pb.RatingGetListByDayResponseStruct{UserDayStatsList: list}, err
}

// узнаем, заблокирован ли пользователь
func (s *Server) RatingGetUserStatus(_ context.Context, in *pb.RatingGetUserStatusRequestStruct) (*pb.RatingGetUserStatusResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.RatingGetUserStatusResponseStruct{}, status.Error(503, "isolation not found")
	}

	userStatus, err := methods_rating.GetUserStatus(isolation.Context, isolation.CompanyDataConn, in.GetUserId())
	if err != nil {
		return nil, status.Error(status.Code(err), status.Convert(err).Message())
	}

	return &pb.RatingGetUserStatusResponseStruct{Status: int32(userStatus)}, err
}

// помечаем пользователя забаненным (разбаненным в рейтинге)
func (s *Server) RatingSetUserBlockInSystemStatus(_ context.Context,
	in *pb.RatingSetUserBlockInSystemStatusRequestStruct) (*pb.RatingSetUserBlockInSystemStatusResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.RatingSetUserBlockInSystemStatusResponseStruct{}, status.Error(503, "isolation not found")
	}

	err := methods_rating.SetUserBlockInSystemStatus(isolation.Context, isolation.CompanyDataConn, int64(in.GetUserId()), int(in.GetStatus()))
	if err != nil {
		return nil, status.Error(status.Code(err), status.Convert(err).Message())
	}
	return &pb.RatingSetUserBlockInSystemStatusResponseStruct{}, nil
}
