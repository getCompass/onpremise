package sender

import (
	"encoding/json"
	pb "github.com/getCompassUtils/company_protobuf_schemes/go/sender"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_sender/api/includes/type/structures"
)

// конвертируем protobuf структуру со списком событий
func ConvertProtobufEventVersionList(EventVersionList []*pb.EventVersionItem) map[int]interface{} {

	output := make(map[int]interface{})
	for _, eventVersion := range EventVersionList {

		// распаковываем json
		var parsedData interface{}
		err := json.Unmarshal(eventVersion.Data, &parsedData)
		if err != nil {

			log.Errorf("event have incorrect data; unmarshal has failed: %v", err)
			continue
		}

		output[int(eventVersion.Version)] = parsedData
	}

	return output
}

// конвертируем структуру запроса со списком событий
func ConvertEventVersionList(EventVersionList []structures.SendEventVersionItemStruct) map[int]interface{} {

	output := make(map[int]interface{})
	for _, eventVersionItem := range EventVersionList {
		output[eventVersionItem.Version] = eventVersionItem.Data
	}

	return output
}
