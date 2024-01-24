package structures

// структура объекта
type UserbotStruct struct {
	userbotId  string
	dateCached int64
	userbotRow map[string]string

	// проброс ошибки из БД
	err error
}

// структура объекта
type UserbotInfoStruct struct {
	UserbotId        string
	Token            string
	Status           int64
	CompanyId        int64
	DominoEntrypoint string
	CompanyUrl       string
	SecretKey        string
	IsReactCommand   int64
	UserbotUserId    int64
	Extra            string
}
