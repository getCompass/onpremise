package grpcCompanyCacheClient

import (
	"encoding/json"
	"fmt"
	pb "github.com/getCompassUtils/company_protobuf_schemes/go/company_cache"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_company/api/conf"
	Isolation "go_company/api/includes/type/isolation"
	"google.golang.org/grpc"
	"google.golang.org/grpc/credentials/insecure"
)

type ValueStruct struct {
	Value int `json:"value"`
}

// ConfigGetOne получить конфиг
func ConfigGetList(isolation *Isolation.Isolation, keyList []string) (map[string]int, []string, error) {

	conn, err := prepareConn()
	defer conn.Close()

	client := pb.NewCompanyCacheClient(conn)
	if err != nil {
		log.Errorf("cant connect to go_company_cache, error %v", err.Error())
		return nil, nil, err
	}

	request := pb.ConfigGetListRequestStruct{
		KeyList:   keyList,
		CompanyId: isolation.GetCompanyId(),
	}

	response, err := client.ConfigGetList(isolation.Context, &request)

	if err != nil || response == nil {
		log.Error("cant send get config")
	}

	valueMap := make(map[string]int, len(response.KeyList))

	for _, v := range response.KeyList {

		var configValue ValueStruct
		err = json.Unmarshal([]byte(v.GetValue()), &configValue)

		if err != nil {

			log.Errorf("cant unmarshal config %v %v: error %v", v.GetKey(), v.GetValue(), err.Error())
			continue
		}
		valueMap[v.Key] = configValue.Value
	}
	return valueMap, response.NotFoundList, nil
}

// GetShortMemberList получить информацю об участниках
func GetShortMemberList(isolation *Isolation.Isolation, userIdList []int64) (map[int64]*MemberShortInfo, error) {

	conn, err := prepareConn()
	defer conn.Close()

	client := pb.NewCompanyCacheClient(conn)
	if err != nil {
		log.Errorf("cant connect to go_company_cache, error %v", err.Error())
		return nil, err
	}

	request := pb.MemberGetShortListRequestStruct{
		UserIdList: userIdList,
		CompanyId:  isolation.GetCompanyId(),
	}

	response, err := client.MemberGetShortList(isolation.Context, &request)

	if err != nil || response == nil {
		log.Error("cant send get short member list")
	}

	memberShortInfoList := make(map[int64]*MemberShortInfo, len(response.GetMemberList()))

	for _, member := range response.GetMemberList() {

		memberShortInfoList[member.UserId] = &MemberShortInfo{
			UserId:      member.UserId,
			Role:        int(member.Role),
			Permissions: int(member.Permissions),
			NpcType:     int(member.NpcType),
		}
	}
	return memberShortInfoList, nil
}

func prepareConn() (*grpc.ClientConn, error) {

	var opts []grpc.DialOption

	opts = append(opts, grpc.WithTransportCredentials(insecure.NewCredentials()))

	shardingConfig, err := conf.GetShardingConfig()

	if err != nil {

		log.Error("cant find sharding config")
		return nil, err
	}
	companyCacheConfig, exist := shardingConfig.Go["company_cache"]

	if !exist {

		log.Errorf("can not find company_cache")
		return nil, err
	}

	conn, err := grpc.NewClient(fmt.Sprintf("%s:%s", companyCacheConfig.Host, companyCacheConfig.GrpcPort), opts...)

	if err != nil {

		log.Errorf("can connect to company_cache")
		return nil, err
	}
	return conn, nil
}
