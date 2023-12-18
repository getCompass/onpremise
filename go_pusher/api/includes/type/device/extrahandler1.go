package device

// структура обработчика версии 1
type extraHandlerVersion1 struct {
	TokenList                []TokenItem `json:"token_list"`
	UserCompanyTokenPushList []string    `json:"user_company_push_token_list"`
}

// определяет, был ли ивент замьючен с таймером
func (d extraHandlerVersion1) getTokenList() []TokenItem {

	return d.TokenList
}

// определяет, был ли ивент замьючен с таймером
func (d extraHandlerVersion1) setTokenList(tokenList []TokenItem) extraHandlerVersion1 {

	d.TokenList = tokenList

	return d
}

// получить список токенов компании
func (d extraHandlerVersion1) getUserCompanyTokenPushList() []string {

	return d.UserCompanyTokenPushList
}
