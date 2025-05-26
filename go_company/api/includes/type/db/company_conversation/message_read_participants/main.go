package dbConversationMessageReadParticipants

// -------------------------------------------------------
// пакет, содержащий интерфейсы для работы с таблицей
// содержащей количество реакций
// -------------------------------------------------------

import (
	"context"
	"database/sql"
	"github.com/getCompassUtils/go_base_frame/api/system/mysql"
	"strconv"
)

const tableKey = "message_read_participants"

// InsertArray вставить список записей
func InsertArray(ctx context.Context, tableShard int, transactionItem *sql.Tx, messageReadParticipantList []Row) error {

	insertList := make([][]interface{}, 0, len(messageReadParticipantList))

	for _, messageReadParticipant := range messageReadParticipantList {

		insertList = append(insertList, []interface{}{
			messageReadParticipant.ConversationMap,
			messageReadParticipant.ConversationMessageIndex,
			messageReadParticipant.UserId,
			messageReadParticipant.ReadAt,
			messageReadParticipant.MessageCreatedAt,
			messageReadParticipant.CreatedAt,
			messageReadParticipant.UpdatedAt,
			messageReadParticipant.MessageMap,
		})
	}

	query, values := mysql.InsertArray(getTableName(tableShard), []string{"conversation_map", "conversation_message_index", "user_id", "read_at", "message_created_at", "created_at", "updated_at", "message_map"}, insertList)

	queryCtx, cancel := context.WithTimeout(ctx, mysql.QueryTimeout)
	defer cancel()

	_, err := transactionItem.ExecContext(queryCtx, query, values...)

	return err
}

// получаем названи таблицы
func getTableName(tableShard int) string {

	return tableKey + "_" + strconv.Itoa(tableShard)
}
