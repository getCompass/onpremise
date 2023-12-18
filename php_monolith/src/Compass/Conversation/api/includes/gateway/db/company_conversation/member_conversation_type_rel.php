<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс-интерфейс для таблицы company_conversation.member_conversation_type_rel
 */
class Gateway_Db_CompanyConversation_MemberConversationTypeRel extends Gateway_Db_CompanyConversation_Main {

	protected const _TABLE_KEY = "member_conversation_type_rel";

	/**
	 * создание записи
	 *
	 * @throws \queryException
	 */
	public static function insert(array $insert):void {

		static::_connect(self::_getDbKey())->insert(self::_getTable(), $insert);
	}

	/**
	 * получение записи
	 *
	 * @throws \parseException
	 */
	public static function getByRowId(int $row_id):Struct_Db_CompanyConversation_MemberConversationTypeRel {

		$query = "SELECT * FROM `?p` WHERE row_id = ?i LIMIT ?i";
		$row   = static::_connect(self::_getDbKey())->getOne($query, self::_getTable(), $row_id, 1);

		return self::_rowToObject($row);
	}

	/**
	 * получение записи по user_id and type
	 *
	 * @param int $user_id
	 * @param int $type
	 *
	 * @return Struct_Db_CompanyConversation_MemberConversationTypeRel
	 * @throws ParseFatalException
	 * @throws \cs_RowIsEmpty
	 */
	public static function getByUserIdAndType(int $user_id, int $type):Struct_Db_CompanyConversation_MemberConversationTypeRel {

		$query = "SELECT * FROM `?p` WHERE user_id = ?i AND `type` = ?i LIMIT ?i";
		$row   = static::_connect(self::_getDbKey())->getOne($query, self::_getTable(), $user_id, $type, 1);

		if (!isset($row["conversation_map"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_rowToObject($row);
	}

	/**
	 * обновление записи
	 *
	 */
	public static function set(int $row_id, array $set):void {

		$query = "UPDATE `?p` SET ?u WHERE row_id = ?i LIMIT ?i";
		static::_connect(self::_getDbKey())->update($query, self::_getTable(), $set, $row_id, 1);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * преобразовываем строку таблица в объект
	 *
	 * @param array $row
	 *
	 * @return Struct_Db_CompanyConversation_MemberConversationTypeRel
	 * @throws ParseFatalException
	 */
	protected static function _rowToObject(array $row):Struct_Db_CompanyConversation_MemberConversationTypeRel {

		foreach ($row as $field => $_) {

			if (!property_exists(Struct_Db_CompanyConversation_MemberConversationTypeRel::class, $field)) {
				throw new ParseFatalException("send unknown field '$field'");
			}
		}

		return new Struct_Db_CompanyConversation_MemberConversationTypeRel(
			$row["row_id"],
			$row["user_id"],
			$row["type"],
			$row["conversation_map"],
			$row["created_at"],
		);
	}

	/**
	 * получаем название таблицы
	 *
	 */
	protected static function _getTable():string {

		return self::_TABLE_KEY;
	}
}