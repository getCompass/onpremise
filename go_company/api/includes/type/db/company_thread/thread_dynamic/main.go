package dbThreadDynamic

// -------------------------------------------------------
// пакет, содержащий интерфейсы для работы с таблицей
// содержащей количество реакций
// -------------------------------------------------------

import (
	"context"
	"database/sql"
	"encoding/json"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/mysql"
)

type LastReadMessage struct {
	MessageMap         string          `json:"message_map"`
	ThreadMessageIndex int64           `json:"thread_message_index"`
	ReadParticipants   map[int64]int64 `json:"read_participants"`
}

const tableKeyDynamic = "thread_dynamic"

// GetLastReadMessage получить последнее сообщение чата
func GetLastReadMessageForUpdate(ctx context.Context, tx *sql.Tx, threadMap string) (*LastReadMessage, error) {

	var lastReadMessageRaw []byte

	// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
	query := fmt.Sprintf("SELECT `last_read_message` FROM `%s` WHERE `thread_map` = ? LIMIT ? FOR UPDATE", getTableName())

	queryCtx, cancel := context.WithTimeout(ctx, mysql.QueryTimeout)
	defer cancel()

	err := tx.QueryRowContext(queryCtx, query, threadMap, 1).Scan(&lastReadMessageRaw)

	if err != nil {
		return nil, err
	}

	lastReadMessage := &LastReadMessage{}

	err = json.Unmarshal(lastReadMessageRaw, &lastReadMessage)

	if err != nil {
		return nil, err
	}

	return lastReadMessage, nil

}

// UpdateLastMessage обновить последнее сообщение в dynamic
func UpdateLastMessage(ctx context.Context, tx *sql.Tx, threadMap string, lastReadMessage *LastReadMessage) error {

	lastReadMessageRaw, err := json.Marshal(lastReadMessage)

	if err != nil {
		return err
	}

	// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
	// nosemgrep
	query := fmt.Sprintf("UPDATE `%s` SET `last_read_message` = ?, `updated_at` = ? WHERE `thread_map` = ? LIMIT ?", getTableName())

	queryCtx, cancel := context.WithTimeout(ctx, mysql.QueryTimeout)
	defer cancel()

	_, err = tx.ExecContext(queryCtx, query, lastReadMessageRaw, functions.GetCurrentTimeStamp(), threadMap, 1)
	return err
}

// получаем названи таблицы
func getTableName() string {

	return tableKeyDynamic
}
