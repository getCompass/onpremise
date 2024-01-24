package grpcController

// member methods group
import (
	"context"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	pb "github.com/getCompassUtils/pivot_protobuf_schemes/go/userbot_cache"
	"go_userbot_cache/api/includes/controller/userbot"
	"time"
)

// UserbotGetOne получаем информацию о боте
func (s *Server) UserbotGetOne(_ context.Context, in *pb.UserbotGetOneRequestStruct) (*pb.UserbotGetOneResponseStruct, error) {

	start := time.Now()

	userbotItem, _ := userbot.GetOne(in.GetToken())

	if time.Since(start) > time.Millisecond*100 {

		log.Errorf(fmt.Sprintf("\r\n// -------------------------------------------------------\r\n"+
			"// [includes/grpc/controller/controller.go][MemberGetOne] Время выполнения метода: %s\r\n"+
			"// -------------------------------------------------------", time.Since(start)))
	}

	var UserbotCacheGetOneResponseStruct pb.UserbotGetOneResponseStruct

	UserbotCacheGetOneResponseStruct.Token = userbotItem.Token
	UserbotCacheGetOneResponseStruct.UserbotId = userbotItem.UserbotId
	UserbotCacheGetOneResponseStruct.Status = userbotItem.Status
	UserbotCacheGetOneResponseStruct.CompanyId = userbotItem.CompanyId
	UserbotCacheGetOneResponseStruct.DominoEntrypoint = userbotItem.DominoEntrypoint
	UserbotCacheGetOneResponseStruct.CompanyUrl = userbotItem.CompanyUrl
	UserbotCacheGetOneResponseStruct.SecretKey = userbotItem.SecretKey
	UserbotCacheGetOneResponseStruct.IsReactCommand = userbotItem.IsReactCommand
	UserbotCacheGetOneResponseStruct.UserbotUserId = userbotItem.UserbotUserId
	UserbotCacheGetOneResponseStruct.Extra = userbotItem.Extra

	return &UserbotCacheGetOneResponseStruct, nil
}

// UserbotClear чистим информацию о боте из кэша
func (s *Server) UserbotClear(_ context.Context, in *pb.UserbotClearRequestStruct) (*pb.UserbotClearResponseStruct, error) {

	userbot.ClearFromCacheByToken(in.GetToken())

	return &pb.UserbotClearResponseStruct{}, nil
}
