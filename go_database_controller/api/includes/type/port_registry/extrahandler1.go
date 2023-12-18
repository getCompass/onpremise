package port_registry

// структура обработчика версии 1
type extraHandlerVersion1 struct {
	EncryptedMysqlUser string `json:"encrypted_mysql_user"`
	EncryptedMysqlPass string `json:"encrypted_mysql_pass"`
}
