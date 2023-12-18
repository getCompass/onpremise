package structures

// Пакет содержит описание структур используемых в микросервисе

type RequestUpdateBadgeDataStruct struct {
	MessagesUnreadCount int      `json:"messages_unread_count"`
	InboxUnreadCount    int      `json:"inbox_unread_count"`
	TaskList            []string `json:"task_list"`
}

// структура хранилища реакций
type ReactionCacheItem struct {
	EntityMap    string
	EntityType   string
	BlockID      int64
	ReactionList map[string]ReactionStruct // ключ string - "{$block_message_index}_{$reaction_index}"
}

// структура версионного события
type WsEventVersionItemStruct struct {
	Version int    `json:"version"`
	Data    []byte `json:"data"`
}

// структура ws ивента
type WsEventStruct struct {
	IsAdd              bool
	WsEventVersionList []WsEventVersionItemStruct
	WsUserList         interface{} // список пользователей, которым нужно отправить событие
}

// структура реакции
type ReactionStruct struct {
	AddUserList    map[int64]int64 // поставили реакицю
	RemoveUserList map[int64]int64 // сняли реакцию
	EventList      map[int64]WsEventStruct
}
