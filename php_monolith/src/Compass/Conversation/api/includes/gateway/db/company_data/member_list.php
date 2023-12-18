<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use CompassApp\Domain\Member\Entity\Member;
use CompassApp\Domain\User\Main;
use CompassApp\Domain\Member\Struct\Main as MemberStruct;

/**
 * класс-интерфейс для таблицы company_data . member_list
 */
class Gateway_Db_CompanyData_MemberList extends Gateway_Db_CompanyData_Main {

	protected const _TABLE_KEY = "member_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод для получения списка участников по маске группы
	 *
	 * @param int $permissions
	 *
	 * @return MemberStruct[]
	 * @throws ParseFatalException
	 */
	public static function getByPermissionMask(int $permissions):array {

		// запрос проверен на EXPLAIN (INDEX=npc_type.role.company_joined_at)
		$query = "SELECT * FROM `?p` FORCE INDEX (`?p`) WHERE `npc_type` = ?i AND `role` = ?i AND `permissions` & ?i LIMIT ?i";
		$rows  = ShardingGateway::database(self::_DB_KEY)->getAll(
			$query, self::_TABLE_KEY, "npc_type.role.company_joined_at", Main::NPC_TYPE_HUMAN, Member::ROLE_ADMINISTRATOR, $permissions, 10000);

		$list = [];
		foreach ($rows as $row) {
			$list[$row["user_id"]] = self::_formatRow($row);
		}

		return $list;
	}
	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * преобразовываем массив в структуру
	 *
	 */
	protected static function _formatRow(array $row):MemberStruct {

		return new MemberStruct(
			$row["user_id"],
			$row["role"],
			$row["npc_type"],
			$row["permissions"],
			$row["created_at"],
			$row["updated_at"],
			$row["company_joined_at"],
			$row["left_at"],
			$row["full_name_updated_at"],
			$row["full_name"],
			$row["mbti_type"],
			$row["short_description"],
			$row["avatar_file_key"],
			$row["comment"],
			fromJson($row["extra"]),
		);
	}
}