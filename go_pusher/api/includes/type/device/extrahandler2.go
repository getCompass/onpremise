package device

// структура обработчика версии 2
type extraHandlerVersion2 struct {
	TokenList                []TokenItem `json:"token_list"`
	UserCompanyTokenPushList []string    `json:"user_company_push_token_list"`
	CompanyIdList            []int       `json:"company_id_list"`
}

// определяет, был ли ивент замьючен с таймером
func (d extraHandlerVersion2) getTokenList() []TokenItem {

	return d.TokenList
}

// определяет, был ли ивент замьючен с таймером
func (d extraHandlerVersion2) setTokenList(tokenList []TokenItem) extraHandlerVersion2 {

	d.TokenList = tokenList

	return d
}

// получить список токенов компании
func (d extraHandlerVersion2) getUserCompanyTokenPushList() []string {

	return d.UserCompanyTokenPushList
}

// получить список ид компаний
func (d extraHandlerVersion2) getCompanyIdList() []int {

	return d.CompanyIdList
}
