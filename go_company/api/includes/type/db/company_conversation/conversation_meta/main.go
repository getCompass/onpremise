package dbConversationMeta

import (
	"context"
	"encoding/json"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"github.com/getCompassUtils/go_base_frame/api/system/mysql"
	"go_company/api/includes/type/db/company_conversation"
	"strings"
)

const tableKey = "conversation_meta"

// GetByConversationForUpdate получить несколько записей из чата на обновление
func GetAllUsers(ctx context.Context, companyId int64, dbConn *company_conversation.DbConn, conversationMapIdList map[int][]int64) (map[string]UsersRow, error) {

	if len(conversationMapIdList) == 0 {
		return map[string]UsersRow{}, nil
	}

	whereClause := ""

	finalQueryArgs := make([]interface{}, 0)
	metaIdCount := 0
	for year, metaIdList := range conversationMapIdList {

		plugForIn := make([]string, 0, len(metaIdList))
		queryArgs := make([]interface{}, 0, len(metaIdList)+1)

		for _, v := range metaIdList {
			plugForIn = append(plugForIn, "?")
			queryArgs = append(queryArgs, v)
		}

		queryArgs = append(queryArgs, year)
		whereClause += fmt.Sprintf("(`meta_id` IN (%s) AND `year` = ?) OR ", strings.Join(plugForIn, ","))
		metaIdCount += len(metaIdList)
		finalQueryArgs = append(finalQueryArgs, queryArgs...)
	}

	whereClause = whereClause[:len(whereClause)-4]
	finalQueryArgs = append(finalQueryArgs, metaIdCount)

	// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
	query := fmt.Sprintf("SELECT `meta_id`, `year`, `users` FROM `%s` WHERE %s LIMIT ?", getTableName(), whereClause)

	queryCtx, cancel := context.WithTimeout(ctx, mysql.QueryTimeout)
	defer cancel()

	results, err := dbConn.Conn.QueryContext(queryCtx, query, finalQueryArgs...)
	defer func() { _ = results.Close(); cancel() }()

	if err != nil {

		log.Errorf(err.Error())
		return nil, err
	}

	conversationMetaUsersMap := make(map[string]UsersRow, metaIdCount)

	for results.Next() {

		var row UsersRow

		err = results.Scan(
			&row.MetaId,
			&row.Year,
			&row.UsersRaw,
		)
		if err != nil {
			return nil, err
		}

		err = json.Unmarshal(row.UsersRaw, &row.Users)
		if err != nil {

			log.Errorf("Ошибка парсинга пользователей из меты чата с id %d, компания %d", row.MetaId, companyId)
			continue
		}

		conversationMetaUsersMap[GetRowKey(row.MetaId, row.Year)] = row
	}

	return conversationMetaUsersMap, nil
}

// GetRow получить ключ записи
func GetRowKey(metaId int64, year int) string {

	return fmt.Sprintf("%d_%d", metaId, year)
}

func getTableName() string {
	return tableKey
}
