<?php

namespace Compass\Conversation;

/**
 * класс-интерфейс для таблицы company_conversation.conversation_meta
 */
class Gateway_Db_CompanyConversation_ConversationMeta extends Gateway_Db_CompanyConversation_Main {

	protected const _TABLE_KEY = "conversation_meta";

	/**
	 * метод для получения записи
	 *
	 * @param string $conversation_map
	 *
	 * @return Struct_Db_CompanyConversation_ConversationMeta
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_UnpackHasFailed
	 * @throws \cs_RowIsEmpty
	 */
	public static function getOne(string $conversation_map):Struct_Db_CompanyConversation_ConversationMeta {

		$meta_id  = \CompassApp\Pack\Conversation::getMetaId($conversation_map);
		$shard_id = \CompassApp\Pack\Conversation::getShardId($conversation_map);

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$meta_row = static::_connect(self::_getDbKey())->getOne("SELECT * FROM `?p` WHERE meta_id = ?i AND year = ?i LIMIT ?i",
			self::_getTable(), $meta_id, $shard_id, 1);

		if (count($meta_row) === 0) {
			throw new \cs_RowIsEmpty("conversation not found");
		}

		return self::_rowToObject($meta_row, $conversation_map);
	}

	/**
	 * получаем все записи
	 *
	 * @param array $conversation_map_list
	 * @param bool  $assoc_list
	 *
	 * @return array
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_UnpackHasFailed
	 */
	public static function getAll(array $conversation_map_list, bool $assoc_list = false):array {

		$meta_year_list           = [];
		$conversation_meta_list   = [];
		$conversation_meta_output = [];
		$meta_map_list            = [];
		foreach ($conversation_map_list as $v) {

			$meta_year_list[\CompassApp\Pack\Conversation::getShardId($v)][] = \CompassApp\Pack\Conversation::getMetaId($v);

			$meta_map_list[\CompassApp\Pack\Conversation::getMetaId($v) . "_" . \CompassApp\Pack\Conversation::getShardId($v)] = $v;
		}

		foreach ($meta_year_list as $year => $meta_list) {

			// запрос проверен на EXPLAIN (INDEX=PRIMARY)
			$query                  = "SELECT * FROM `?p` WHERE `meta_id` IN (?a) AND `year` = ?i LIMIT ?i";
			$conversation_meta_list = array_merge($conversation_meta_list, static::_connect(self::_getDbKey())->getAll(
				$query,
				self::_getTable(),
				$meta_list,
				$year,
				count($meta_list)
			));
		}

		return self::_doFormatOutputConversationList(
			$conversation_meta_output,
			$conversation_meta_list,
			$meta_map_list,
			$assoc_list
		);
	}

	// метод для обновления записи
	public static function set(string $conversation_map, array $set):void {

		$meta_id  = \CompassApp\Pack\Conversation::getMetaId($conversation_map);
		$shard_id = \CompassApp\Pack\Conversation::getShardId($conversation_map);

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		static::_connect(self::_getDbKey())->update("UPDATE `?p` SET ?u WHERE meta_id = ?i AND year = ?i LIMIT ?i",
			self::_getTable(), $set, $meta_id, $shard_id, 1);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// получаем таблицу
	protected static function _getTable():string {

		return self::_TABLE_KEY;
	}

	// форматируем ответ из базы
	protected static function _doFormatOutputConversationList(array $conversation_meta_output, array $conversation_meta_list, array $meta_map_list, bool $assoc_list):array {

		//
		foreach ($conversation_meta_list as $conversation_meta) {

			//
			$conversation_map = $meta_map_list[$conversation_meta["meta_id"] . "_" . $conversation_meta["year"]];

			// если нужен ассоциативный массив на выходе
			if ($assoc_list) {

				$conversation_meta_output[$conversation_map] = self::_rowToObject(
					$conversation_meta,
					$conversation_map
				);
				continue;
			}

			$conversation_meta_output[] = self::_rowToObject(
				$conversation_meta,
				$conversation_map
			);
		}

		return $conversation_meta_output;
	}

	// функция для форматирования row из базы
	protected static function _rowToObject(array $meta_row, string $conversation_map):Struct_Db_CompanyConversation_ConversationMeta {

		return new Struct_Db_CompanyConversation_ConversationMeta(
			$conversation_map,
			$meta_row["allow_status"],
			$meta_row["type"],
			$meta_row["created_at"],
			$meta_row["updated_at"],
			$meta_row["creator_user_id"],
			$meta_row["avatar_file_map"],
			$meta_row["conversation_name"],
			fromJson($meta_row["users"]),
			fromJson($meta_row["extra"])
		);
	}
}