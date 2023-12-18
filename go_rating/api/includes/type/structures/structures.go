package structures

// Пакет содержит описание структур используемых в микросервисе

type RequestSaveScreenTimeDataStruct struct {
	ScreenTimeList []SpaceUserScreenTimeListStruct `json:"screen_time_list"`
}

// SpaceUserScreenTimeListStruct структура объекта
type SpaceUserScreenTimeListStruct struct {
	SpaceId            int64                  `json:"space_id"`
	CacheAt            int64                  `json:"cache_at"`
	UserScreenTimeList []UserScreenTimeStruct `json:"user_screen_time_list"`
}

// UserScreenTimeStruct структура объекта
type UserScreenTimeStruct struct {
	UserID        int64  `json:"user_id"`
	ScreenTime    int64  `json:"screen_time"`
	LocalOnlineAt string `json:"local_online_at"`
}

type RequestSaveUserActionListStruct struct {
	UserList []UserListStruct `json:"user_list"`
}

// UserListStruct структура объекта
type UserListStruct struct {
	UserId     int64            `json:"user_id"`
	SpaceId    int64            `json:"space_id"`
	ActionAt   int64            `json:"action_at"`
	ActionList ActionListStruct `json:"action_list"`
}

// ActionListStruct структура объекта
type ActionListStruct struct {
	GroupsCreated              int64 `json:"groups_created"`
	ConversationsRead          int64 `json:"conversations_read"`
	ConversationMessagesSent   int64 `json:"conversation_messages_sent"`
	ConversationReactionsAdded int64 `json:"conversation_reactions_added"`
	ConversationRemindsCreated int64 `json:"conversation_reminds_created"`
	Calls                      int64 `json:"calls"`
	ThreadsCreated             int64 `json:"threads_created"`
	ThreadsRead                int64 `json:"threads_read"`
	ThreadMessagesSent         int64 `json:"thread_messages_sent"`
	ThreadReactionsAdded       int64 `json:"thread_reactions_added"`
	ThreadRemindsCreated       int64 `json:"thread_reminds_created"`
}

type RequestSaveUserAnswerTimeDataStruct struct {
	ConversationList []ConversationUserAnswerTimeListStruct `json:"conversation_list"`
}

// ConversationUserAnswerTimeListStruct структура объекта
type ConversationUserAnswerTimeListStruct struct {
	Min15StartAt       int64                  `json:"min15_start_at"`
	SpaceId            int64                  `json:"space_id"`
	ConversationKey    string                 `json:"conversation_key"`
	UserAnswerTimeList []UserAnswerTimeStruct `json:"user_answer_time_list"`
}

// UserAnswerTimeStruct структура объекта
type UserAnswerTimeStruct struct {
	UserID     int64 `json:"user_id"`
	AnswerTime int64 `json:"answer_time"`
	AnsweredAt int64 `json:"answered_at"`
}
