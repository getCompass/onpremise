package CompanySystem

import (
	"context"
	"database/sql"
	"encoding/json"
	"fmt"
	"go_rating/api/includes/type/database"
)

// ConversationDynamicRecord структура для чтения из таблицы
type ConversationDynamicRecord struct {
	ConversationMap         string          `json:"conversation_map,omitempty"`
	IsLocked                int64           `json:"is_locked,omitempty"`
	LastBlockId             int64           `json:"last_block_id,omitempty"`
	StartBlockId            int64           `json:"start_block_id,omitempty"`
	TotalMessageCount       int64           `json:"total_message_count,omitempty"`
	TotalActionCount        int64           `json:"total_action_count,omitempty"`
	FileCount               int64           `json:"file_count,omitempty"`
	ImageCount              int64           `json:"image_count,omitempty"`
	VideoCount              int64           `json:"video_count,omitempty"`
	CreatedAt               int64           `json:"created_at,omitempty"`
	UpdatedAt               int64           `json:"updated_at,omitempty"`
	MessagesUpdatedAt       int64           `json:"messages_updated_at,omitempty"`
	ReactionsUpdatedAt      int64           `json:"reactions_updated_at,omitempty"`
	ThreadsUpdatedAt        int64           `json:"threads_updated_at,omitempty"`
	MessagesUpdatedVersion  int64           `json:"messages_updated_version,omitempty"`
	ReactionsUpdatedVersion int64           `json:"reactions_updated_version,omitempty"`
	ThreadsUpdatedVersion   int64           `json:"threads_updated_version,omitempty"`
	UserMuteInfo            json.RawMessage `json:"user_mute_info,omitempty"`
	UserClearInfo           json.RawMessage `json:"user_clear_info,omitempty"`
	UserFileClearInfo       json.RawMessage `json:"user_file_clear_info,omitempty"`
	ConversationClearInfo   json.RawMessage `json:"conversation_clear_info,omitempty"`
}

// конвертирует структуру в map из интерфейсов для апдейта
func (r *ConversationDynamicRecord) toUpdateStringMap() map[string]interface{} {

	return map[string]interface{}{
		"total_action_count": r.TotalActionCount,
	}
}

// структура хендлера таблицы
type conversationDynamicTable struct {
	tableName string
	fieldList string
}

// ConversationDynamicTable основной хендлер таблицы
var ConversationDynamicTable = conversationDynamicTable{
	tableName: "conversation_dynamic",
	fieldList: "`conversation_map`, `is_locked`, `last_block_id`, `start_block_id`, `total_message_count`, `total_action_count`, `file_count`, `image_count`, `video_count`, `created_at`, `updated_at`, `messages_updated_at`, `reactions_updated_at`, `threads_updated_at`, `messages_updated_version`, `reactions_updated_version`, `threads_updated_version`, `user_mute_info`, `user_clear_info`, `user_file_clear_info`, `conversation_clear_info`",
}

// GetOne получает запись из базы
func (t *conversationDynamicTable) GetOne(ctx context.Context, connection *Database.Connection, conversationMap string) (*ConversationDynamicRecord, error) {

	result := &ConversationDynamicRecord{}

	// запрос проверен на EXPLAIN (INDEX=PRIMARY)
	query := fmt.Sprintf("SELECT %s FROM `%s` WHERE `conversation_map` = ? LIMIT ?", t.fieldList, t.tableName)

	err := connection.QueryRow(ctx, query, conversationMap, 1).Scan(
		&result.ConversationMap,
		&result.IsLocked,
		&result.LastBlockId,
		&result.StartBlockId,
		&result.TotalMessageCount,
		&result.TotalActionCount,
		&result.FileCount,
		&result.ImageCount,
		&result.VideoCount,
		&result.CreatedAt,
		&result.UpdatedAt,
		&result.MessagesUpdatedAt,
		&result.ReactionsUpdatedAt,
		&result.ThreadsUpdatedAt,
		&result.MessagesUpdatedVersion,
		&result.ReactionsUpdatedVersion,
		&result.ThreadsUpdatedVersion,
		&result.UserMuteInfo,
		&result.UserClearInfo,
		&result.UserFileClearInfo,
		&result.ConversationClearInfo,
	)

	if err != nil {

		if err == sql.ErrNoRows {
			return nil, nil
		}
		return nil, err
	}

	return result, nil
}

// UpdateTotalActionCount обновляет общее количество действий в диалоге
func (t *conversationDynamicTable) UpdateTotalActionCount(ctx context.Context, connection *Database.Connection, conversation *ConversationDynamicRecord) error {

	// запрос проверен на EXPLAIN (INDEX=PRIMARY)
	query := fmt.Sprintf("UPDATE `%s` SET ?? WHERE `conversation_map` = ? LIMIT ?", t.tableName)
	return connection.Update(ctx, query, conversation.toUpdateStringMap(), conversation.ConversationMap, 1)
}
