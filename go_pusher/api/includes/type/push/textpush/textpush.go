package textpush

// данные для локализации пуша
type LocalizationData struct {
	Key  string   `json:"key"`
	Args []string `json:"args"`
}

// параметры для пуша, приходящие в запросе
type PushStruct struct {
	CompanyId         int64            `json:"company_id,omitempty"`
	SenderUserId      int64            `json:"sender_user_id,omitempty"`
	Title             string           `json:"title,omitempty"`
	TitleLocalization LocalizationData `json:"title_localization,omitempty"`
	Body              string           `json:"body,omitempty"`
	BodyLocalization  LocalizationData `json:"body_localization,omitempty"`
	ConversationKey   string           `json:"conversation_key,omitempty"`
	ThreadKey         string           `json:"thread_key,omitempty"`
	ParentKey         string           `json:"parent_key,omitempty"`
	CollapseId        string           `json:"collapse_id,omitempty"`
	ParentId          string           `json:"parent_id,omitempty"`
	ParentType        string           `json:"parent_type,omitempty"`
	ConversationType  string           `json:"conversation_type,omitempty"`
	EntityType        string           `json:"entity_type,omitempty"`
	EntityData        interface{}      `json:"entity_data,omitempty"`
	Category          string           `json:"category,omitempty"`
}

// создать тестовый пуш
func CreateTest(pushType string) PushStruct {

	return PushStruct{
		Title:    "Compass",
		Body:     "Привет 👋 Это тестовое уведомление",
		Category: pushType,
		TitleLocalization: LocalizationData{
			Key: "TEST_MESSAGE_TITLE",
		},
		BodyLocalization: LocalizationData{
			Key: "TEST_MESSAGE_BODY",
		},
	}
}
