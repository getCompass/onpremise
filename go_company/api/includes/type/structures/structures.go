package structures

import "time"

// Пакет содержит описание структур используемых в микросервисе

const (
	ConversationEntityType = "conversation"
	ThreadEntityType       = "thread"
)

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

// структура хранилища прочитанных сообщений
type ReadMessageCacheItem struct {
	EntityMap               string
	EntityType              string
	EntityMetaId            int64
	EntityKey               string
	EntityMessageIndex      int64
	MessageMap              string
	MessageKey              string
	UserId                  int64
	ReadAt                  int64
	MessageCreatedAt        int64
	TableShard              int
	DbShard                 int
	HideReadParticipantList []int64
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

type EntityReadMessageStruct struct {
	EntityMap               string
	EntityType              string
	EntityKey               string
	EntityMetaId            int64
	DbShard                 int
	TableShard              int
	IsForceShowParticipant  bool
	ReadMessageParticipants map[int64]*ReadMessageStruct // ключ {$entity_message_index}
}

type ReadMessageStruct struct {
	EntityMessageIndex      int64
	MessageMap              string
	MessageKey              string
	MessageCreatedAt        int64
	ReadParticipants        map[int64]*ReadParticipant
	HideReadParticipantList []int64
}

type LastReadMessageStruct struct {
	EntityMap          string
	EntityType         string
	EntityMessageIndex int64
	MessageMap         string
	MessageCreatedAt   int64
	ReadParticipants   map[int64]*ReadParticipant
	ExpiredAt          time.Time
}

type ReadParticipant struct {
	UserId int64 `json:"user_id"`
	ReadAt int64 `json:"read_at"`
}
