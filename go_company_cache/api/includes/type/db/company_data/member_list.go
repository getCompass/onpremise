package company_data

import (
	"context"
	"database/sql"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"github.com/getCompassUtils/go_base_frame/api/system/mysql"
	"strings"
)

const memberListTableKey = "member_list"

type MemberRow struct {
	UserId            int64  `json:"user_id"`
	Role              int32  `json:"role"`
	NpcType           int32  `json:"npc_type"`
	Permissions       int32  `json:"permissions"`
	CreatedAt         int32  `json:"created_at"`
	UpdatedAt         int32  `json:"updated_at"`
	CompanyJoinedAt   int32  `json:"company_joined_at"`
	LeftAt            int32  `json:"left_at"`
	FullNameUpdatedAt int32  `json:"fullname_updated_at"`
	FullName          string `json:"fullname"`
	MbtiType          string `json:"mbti_type"`
	ShortDescription  string `json:"short_description"`
	AvatarFileKey     string `json:"avatar_file_key"`
	Comment           string `json:"comment"`
	Extra             string `json:"extra"`
}

// GetMemberRow получаем пользователя по id
func (dbConn *DbConn) GetMemberRow(ctx context.Context, userId int64) (*MemberRow, error) {

	var row MemberRow

	// проверяем, что у нас имеется подключение к необходимой базе данных
	query := fmt.Sprintf("SELECT user_id, role, npc_type, permissions, created_at, updated_at, company_joined_at, left_at, full_name_updated_at, full_name, mbti_type, short_description, avatar_file_key, comment, extra FROM `%s` WHERE `user_id` = ? LIMIT ?", memberListTableKey)

	queryCtx, cancel := context.WithTimeout(ctx, mysql.QueryTimeout)
	defer cancel()

	err := dbConn.Conn.QueryRowContext(queryCtx, query, userId, 1).Scan(
		&row.UserId,
		&row.Role,
		&row.NpcType,
		&row.Permissions,
		&row.CreatedAt,
		&row.UpdatedAt,
		&row.CompanyJoinedAt,
		&row.LeftAt,
		&row.FullNameUpdatedAt,
		&row.FullName,
		&row.MbtiType,
		&row.ShortDescription,
		&row.AvatarFileKey,
		&row.Comment,
		&row.Extra,
	)

	if err != nil {

		if err == sql.ErrNoRows {

			log.Infof("Row with user_id %d not found", userId)
			return nil, nil
		}
		return nil, err
	}

	return &row, nil
}

// GetMemberList получаем пользователя по id
func (dbConn *DbConn) GetMemberList(ctx context.Context, userIdList []int64) ([]*MemberRow, error) {

	var plugForIn []string
	var queryArgs []interface{}
	for _, v := range userIdList {

		plugForIn = append(plugForIn, "?")
		queryArgs = append(queryArgs, v)
	}

	queryArgs = append(queryArgs, len(plugForIn))
	query := fmt.Sprintf("SELECT user_id, role, npc_type, permissions, created_at, updated_at, company_joined_at, left_at, full_name_updated_at, full_name, mbti_type, short_description, avatar_file_key, comment, extra FROM `%s` WHERE `user_id` IN (%s) LIMIT ?", memberListTableKey, strings.Join(plugForIn, ","))

	queryCtx, cancel := context.WithTimeout(ctx, mysql.QueryTimeout)

	results, err := dbConn.Conn.QueryContext(queryCtx, query, queryArgs...)
	defer func() { _ = results.Close(); cancel() }()

	if err != nil {

		log.Errorf(err.Error())
		return nil, err
	}

	memberList := make([]*MemberRow, 0, len(plugForIn))

	for results.Next() {

		var row MemberRow

		err = results.Scan(&row.UserId,
			&row.Role,
			&row.NpcType,
			&row.Permissions,
			&row.CreatedAt,
			&row.UpdatedAt,
			&row.CompanyJoinedAt,
			&row.LeftAt,
			&row.FullNameUpdatedAt,
			&row.FullName,
			&row.MbtiType,
			&row.ShortDescription,
			&row.AvatarFileKey,
			&row.Comment,
			&row.Extra,
		)
		if err != nil {
			return nil, err
		}

		memberList = append(memberList, &row)
	}
	return memberList, nil
}
