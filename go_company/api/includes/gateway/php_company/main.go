package gatewayPhpCompany

import (
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_company/api/includes/type/socket"
	socketAuthKey "go_company/api/includes/type/socket/auth"
)

// GetPivotSocketKey получаем ключ для доступа к pivot
func GetPivotSocketKey(companyId int64, socketKeyMe string) string {

	jsonData := "{}"

	// получаем подпись модуля
	signature := socketAuthKey.GetCompanySignature(socketKeyMe, []byte(jsonData))

	// делаем запрос в php модуль
	response, err := socket.DoCall("php_company", "system.getPivotSocketKey", signature, jsonData, 0, companyId)
	if err != nil {

		log.Errorf("error socket request: %s", err.Error())
		return ""
	}

	if response.Status != "ok" {
		return ""
	}

	responseApi := response.Response.(map[string]interface{})
	socketKey, ok := responseApi["socket_key"]
	if !ok {

		log.Errorf("error socket response unknown")
		return ""
	}
	return socketKey.(string)
}
