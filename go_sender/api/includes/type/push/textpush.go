package push

// структура текстового пуша
type TextPushStruct struct {
	Category          string      `json:"category,omitempty"`
	CompanyId         int64       `json:"company_id,omitempty"`
	SenderUserId      int64       `json:"sender_user_id,omitempty"`
	Title             string      `json:"title,omitempty"`
	TitleLocalization interface{} `json:"title_localization,omitempty"`
	Body              string      `json:"body,omitempty"`
	BodyLocalization  interface{} `json:"body_localization,omitempty"`
	ConversationKey   string      `json:"conversation_key,omitempty"`
	ThreadKey         string      `json:"thread_key,omitempty"`
	ParentKey         string      `json:"parent_key,omitempty"`
	CollapseId        string      `json:"collapse_id,omitempty"`
	ParentId          string      `json:"parent_id,omitempty"`
	ParentType        string      `json:"parent_type,omitempty"`
	ConversationType  string      `json:"conversation_type,omitempty"`
	EntityType        string      `json:"entity_type,omitempty"`
	EntityId          string      `json:"entity_id,omitempty"`
	EntityData        interface{} `json:"entity_data,omitempty"`
	Type              string      `json:"type,omitempty"`
}
