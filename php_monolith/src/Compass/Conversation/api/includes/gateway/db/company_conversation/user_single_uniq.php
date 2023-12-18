<?php

namespace Compass\Conversation;

/**
 * класс-интерфейс для базы данных cluster_user_conversation_uniq
 * весь sharding ВСЕГДА происходит по МЕНЬШЕМУ user_id из двух
 */
class Gateway_Db_CompanyConversation_UserSingleUniq extends Gateway_Db_CompanyConversation_Main {

	protected const _TABLE_KEY = "user_single_uniq";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	// метод для создания записи
	public static function insert(int $user1_id, int $user2_id, string $conversation_map):void {

		// сортируем user_id по возрастанию
		$temp = [$user1_id, $user2_id];
		sort($temp);
		$user1_id = $temp[0];
		$user2_id = $temp[1];

		// формируем массив $insert
		$insert = [
			"user1_id"         => $user1_id,
			"user2_id"         => $user2_id,
			"conversation_map" => $conversation_map,
		];

		static::_connect(self::_getDbKey())->insert(self::_getTable(), $insert);
	}

	// метод для получения записи пользователя
	public static function getOne(int $user1_id, int $user2_id):array {

		// сортируем user_id по возрастанию
		$temp = [$user1_id, $user2_id];
		sort($temp);
		$user1_id = $temp[0];
		$user2_id = $temp[1];

		// формируем и осуществляем запрос
		$query = "SELECT * FROM `?p` WHERE `user1_id` = ?i AND `user2_id` = ?i LIMIT ?i";
		return static::_connect(self::_getDbKey())->getOne($query, self::_getTable(), $user1_id, $user2_id, 1);
	}

	/**
	 * Получаем массив с записями батчинг запросом
	 *
	 */
	public static function getList(array $user_pair_list):array {

		// формируем и осуществляем запрос
		$query = "SELECT * FROM `?p` WHERE (`user1_id`, `user2_id`) IN (?p) LIMIT ?i";
		$in    = self::_makeIn($user_pair_list);
		return static::_connect(self::_getDbKey())->getAll($query, self::_getTable(), $in, count($user_pair_list));
	}

	/**
	 * Формируем IN для выборки
	 *
	 */
	protected static function _makeIn(array $user_pair_list):string {

		$in = [];
		foreach ($user_pair_list as $user_pair) {

			// работаем по составному первочному ключу через IN
			$in[] = "({$user_pair[0]}, {$user_pair[1]})";
		}
		$in = implode(",", $in);

		return $in;
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	// получает таблицу
	protected static function _getTable():string {

		return self::_TABLE_KEY;
	}
}