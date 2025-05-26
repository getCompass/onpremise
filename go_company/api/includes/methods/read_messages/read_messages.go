package readMessages

import (
	Isolation "go_company/api/includes/type/isolation"
)

// Add добавление задачи на прочтение сообщения
func Add(isolation *Isolation.Isolation, EntityType string, EntityMap string, EntityMetaId int64, EntityKey string, UserId int64, MessageMap string, MessageKey string, EntityMessageIndex int64, MessageCreatedAt int64, ReadAt int64, TableShard int, DbShard int, HideReadParticipantList []int64) {

	isolation.ReadMessageStore.Add(
		EntityType,
		EntityMap,
		EntityMetaId,
		EntityKey,
		UserId,
		MessageMap,
		MessageKey,
		EntityMessageIndex,
		MessageCreatedAt,
		ReadAt,
		TableShard,
		DbShard,
		HideReadParticipantList)
}
