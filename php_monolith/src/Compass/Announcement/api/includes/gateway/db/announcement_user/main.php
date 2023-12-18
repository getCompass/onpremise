<?php

namespace Compass\Announcement;

/**
 * класс-интерфейс для базы данных announcement
 */
class Gateway_Db_AnnouncementUser_Main extends Gateway_Db_Db {

	protected const _DB_KEY = "announcement_user";

	/**
	 * Возвращает суффикс для шарда бд по пользователю.
	 *
	 * @param int $user_id
	 *
	 * @return string
	 */
	#[\JetBrains\PhpStorm\Pure]
	protected static function _getShardSuffix(int $user_id):string {

		return ceil($user_id / 10000000) . "0m";
	}

	/**
	 * Метод возвращает все возможные шарды таблицы.
	 *
	 * @return array
	 */
	protected static function _getAllDatabaseShards():array {

		return [static::_getShardSuffix(1)];
	}
}