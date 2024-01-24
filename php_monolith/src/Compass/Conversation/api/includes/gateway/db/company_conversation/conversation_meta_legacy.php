<?php

namespace Compass\Conversation;

/**
 * класс-интерфейс для таблицы company_conversation.conversation_meta
 */
class Gateway_Db_CompanyConversation_ConversationMetaLegacy extends Gateway_Db_CompanyConversation_Main {

	protected const _TABLE_KEY = "conversation_meta";

	// метод для создания записи
	// принимает в качестве параметров не привычный conversation_map как в других методах класса
	// потому что на момент insert"а conversation_map не сформирован (не хватает meta_id, который возвращает функция)
	public static function insert(array $insert):int {

		return static::_connect(self::_getDbKey())->insert(self::_getTable(), $insert);
	}

	// метод для обновления записи
	public static function set(string $conversation_map, array $set):void {

		$meta_id  = \CompassApp\Pack\Conversation::getMetaId($conversation_map);
		$shard_id = \CompassApp\Pack\Conversation::getShardId($conversation_map);

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		static::_connect(self::_getDbKey())->update("UPDATE `?p` SET ?u WHERE meta_id = ?i AND year = ?i LIMIT ?i",
			self::_getTable(), $set, $meta_id, $shard_id, 1);
	}

	// метод для обновления записей
	public static function setList(array $conversation_map_list, array $set):void {

		$meta_year_list = [];
		foreach ($conversation_map_list as $v) {

			$meta_year_list[\CompassApp\Pack\Conversation::getShardId($v)][] = \CompassApp\Pack\Conversation::getMetaId($v);
		}

		foreach ($meta_year_list as $year => $meta_list) {

			// запрос проверен на EXPLAIN (INDEX=PRIMARY)
			static::_connect(self::_getDbKey())->update("UPDATE `?p` SET ?u WHERE `meta_id` IN (?a) AND `year` = ?i  LIMIT ?i",
				self::_getTable(),
				$set,
				$meta_list,
				$year,
				count($meta_list));
		}
	}

	// метод для получения записи на обновление
	public static function getForUpdate(string $conversation_map):array {

		$meta_id  = \CompassApp\Pack\Conversation::getMetaId($conversation_map);
		$shard_id = \CompassApp\Pack\Conversation::getShardId($conversation_map);

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$meta_row = static::_connect(self::_getDbKey())->getOne("SELECT * FROM `?p` WHERE meta_id = ?i AND year = ?i LIMIT ?i FOR UPDATE",
			self::_getTable(), $meta_id, $shard_id, 1);

		return self::_doFormatRowFromDb($meta_row, $conversation_map);
	}

	/**
	 * метод для получения записи
	 *
	 * @param string $conversation_map
	 *
	 * @return array
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_UnpackHasFailed
	 * @throws \cs_RowIsEmpty
	 */
	public static function getOne(string $conversation_map):array {

		$meta_id  = \CompassApp\Pack\Conversation::getMetaId($conversation_map);
		$shard_id = \CompassApp\Pack\Conversation::getShardId($conversation_map);

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$meta_row = static::_connect(self::_getDbKey())->getOne("SELECT * FROM `?p` WHERE meta_id = ?i AND year = ?i LIMIT ?i",
			self::_getTable(), $meta_id, $shard_id, 1);

		if (count($meta_row) === 0) {
			throw new \cs_RowIsEmpty("conversation not found");
		}

		return self::_doFormatRowFromDb($meta_row, $conversation_map);
	}

	// получаем все записи
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
			$conversation_meta_list = array_merge($conversation_meta_list, ShardingGateway::database(self::_getDbKey())->getAll(
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

				$conversation_meta_output[$conversation_map] = self::_doFormatRowFromDb(
					$conversation_meta,
					$conversation_map
				);
				continue;
			}

			$conversation_meta_output[] = self::_doFormatRowFromDb(
				$conversation_meta,
				$conversation_map
			);
		}

		return $conversation_meta_output;
	}

	// функция для форматирования row из базы
	protected static function _doFormatRowFromDb(array $meta_row, string $conversation_map):array {

		$meta_row["conversation_map"] = $conversation_map;

		unset($meta_row["meta_id"]);

		$meta_row["users"] = fromJson($meta_row["users"]);
		$meta_row["extra"] = fromJson($meta_row["extra"]);

		return $meta_row;
	}
}