package grpcController

import (
	"context"
	"fmt"
	pb "github.com/getCompassUtils/company_protobuf_schemes/go/company_cache"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_company_cache/api/includes/controller/config"
	"google.golang.org/grpc/status"
	"time"
)

// ConfigGetList получаем информацию
func (s *Server) ConfigGetList(_ context.Context, in *pb.ConfigGetListRequestStruct) (*pb.ConfigGetListResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.ConfigGetListResponseStruct{}, status.Error(503, "isolation not found")
	}

	start := time.Now()

	keyList, notFoundList := config.GetList(isolation, in.GetKeyList())

	if time.Since(start) > time.Millisecond*100 {

		log.Errorf(fmt.Sprintf("\r\n// -------------------------------------------------------\r\n"+
			"// [includes/grpc/controller/controller.go][ConfigGetList] Время выполнения метода: %s\r\n"+
			"// -------------------------------------------------------", time.Since(start)))
	}

	var ConfigGetListResponseStruct pb.ConfigGetListResponseStruct
	ConfigGetListResponseStruct.NotFoundList = notFoundList
	for _, v := range keyList {

		ConfigGetListResponseStruct.KeyList = append(ConfigGetListResponseStruct.KeyList, &pb.KeyInfoStruct{
			Key:       v.Key,
			CreatedAt: v.CreatedAt,
			UpdatedAt: v.UpdatedAt,
			Value:     v.Value,
		})
	}
	return &ConfigGetListResponseStruct, nil
}

// ConfigGetOne получаем информацию об одно пользователе
func (s *Server) ConfigGetOne(_ context.Context, in *pb.ConfigGetOneRequestStruct) (*pb.ConfigGetOneResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.ConfigGetOneResponseStruct{}, status.Error(503, "isolation not found")
	}

	start := time.Now()

	keyItem, exist := config.GetOne(isolation, in.GetKey())

	if time.Since(start) > time.Millisecond*100 {

		log.Errorf(fmt.Sprintf("\r\n// -------------------------------------------------------\r\n"+
			"// [includes/grpc/controller/controller.go][ConfigGetOne] Время выполнения метода: %s\r\n"+
			"// -------------------------------------------------------", time.Since(start)))
	}

	var ConfigGetOneResponseStruct pb.ConfigGetOneResponseStruct
	ConfigGetOneResponseStruct.Exist = exist

	if exist {

		ConfigGetOneResponseStruct.Key = &pb.KeyInfoStruct{
			Key:       keyItem.Key,
			CreatedAt: keyItem.CreatedAt,
			UpdatedAt: keyItem.UpdatedAt,
			Value:     keyItem.Value,
		}
	}

	return &ConfigGetOneResponseStruct, nil
}

// ConfigDeleteFromCacheByKey удалить все сессии пользователя по его userID
func (s *Server) ConfigDeleteFromCacheByKey(_ context.Context, in *pb.ConfigDeleteFromCacheByKeyRequestStruct) (*pb.ConfigDeleteFromCacheByKeyResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.ConfigDeleteFromCacheByKeyResponseStruct{}, status.Error(503, "isolation not found")
	}

	config.DeleteFromCacheByKey(isolation, in.GetKey())

	return &pb.ConfigDeleteFromCacheByKeyResponseStruct{}, nil
}

// ConfigClearCache очистить кэш пользователей
func (s *Server) ConfigClearCache(_ context.Context, in *pb.ConfigClearCacheRequestStruct) (*pb.ConfigClearCacheResponseStruct, error) {

	isolation := s.CompanyEnvList.GetEnv(in.GetCompanyId())
	if isolation == nil {
		return &pb.ConfigClearCacheResponseStruct{}, status.Error(503, "isolation not found")
	}

	config.ClearCache(isolation)

	return &pb.ConfigClearCacheResponseStruct{}, nil
}
