package conf

var fullConfigPath string = ""
var configSubPath = "/api/conf"

// получить папку конфигов
func getFullConfigDir() string {

	fullConfigPath = "/app/" + configSubPath + "/"
	return fullConfigPath
}
