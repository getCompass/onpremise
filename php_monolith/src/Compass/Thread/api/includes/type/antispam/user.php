<?php

namespace Compass\Thread;

use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для блокировки пользователя по user_id
 */
class Type_Antispam_User extends Type_Antispam_Main {

	##########################################################
	# region константы для блокировок
	##########################################################

	public const THREADS_ADD = [
		"key"    => "THREADS_ADD",
		"limit"  => 50,
		"expire" => 60 * 60,

	];

	public const THREADS_ADDMESSAGE = [
		"key"    => "THREADS_ADDMESSAGE",
		"limit"  => 50,
		"expire" => 60 * 1,

	];

	public const THREADS_TRYEDITMESSAGE = [
		"key"    => "THREADS_TRYEDITMESSAGE",
		"limit"  => 5,
		"expire" => 5,

	];

	public const THREADS_TRYDELETEMESSAGE = [
		"key"    => "THREADS_TRYDELETEMESSAGE",
		"limit"  => 10,
		"expire" => 60 * 1,

	];

	public const THREADS_TRYHIDEMESSAGE = [
		"key"    => "THREADS_TRYHIDEMESSAGE",
		"limit"  => 20,
		"expire" => 60 * 1,

	];

	public const THREADS_SETREACTION = [
		"key"    => "THREADS_SETREACTION",
		"limit"  => 200,
		"expire" => 60,
	];

	public const THREADS_DOREPORTMESSAGE = [
		"key"    => "THREADS_DOREPORTMESSAGE",
		"limit"  => 5,
		"expire" => 60 * 1,

	];

	public const THREADS_FOLLOW = [
		"key"    => "THREADS_FOLLOW",
		"limit"  => 10,
		"expire" => 60,
	];

	public const THREADS_UNFOLLOW = [
		"key"    => "THREADS_UNFOLLOW",
		"limit"  => 10,
		"expire" => 60,
	];

	public const THREADS_ADDREPOSTTOCONVERSATION = [
		"key"    => "THREADS_ADDREPOSTTOCONVERSATION",
		"limit"  => 8,
		"expire" => 60,
	];

	public const THREADS_MUTE = [
		"key"    => "THREADS_MUTE",
		"limit"  => 10,
		"expire" => 60 * 20,
	];

	public const THREADS_UNMUTE = [
		"key"    => "THREADS_UNMUTE",
		"limit"  => 10,
		"expire" => 60 * 20,
	];

	public const THREADS_SET_AS_UNREAD = [
		"key"    => "THREADS_SET_AS_UNREAD",
		"limit"  => 60,
		"expire" => 60 * 5,
	];

	public const THREADS_DOCOMMITWORKEDHOURS = [
		"key"    => "THREADS_DOCOMMITWORKEDHOURS",
		"limit"  => 8,
		"expire" => 60,
	];

	public const THREADS_TRYEXACTING = [
		"key"    => "THREADS_TRYEXACTING",
		"limit"  => 8,
		"expire" => 60,
	];

	const ALL_THREADS_MESSAGES_READ = [
		"key"    => "ALL_THREADS_MESSAGES_READ",
		"limit"  => 5,
		"expire" => 10 * 60,
	];

	const THREADS_ADD_TO_FAVORITE = [
		"key"    => "THREADS_ADD_TO_FAVORITE",
		"limit"  => 20,
		"expire" => 60,
	];

	const THREADS_REMOVE_FROM_FAVORITE = [
		"key"    => "THREADS_REMOVE_FROM_FAVORITE",
		"limit"  => 20,
		"expire" => 60,
	];

	const THREADS_ADD_REPOST = [
		"key"    => "THREADS_ADD_REPOST",
		"limit"  => 10,
		"expire" => 60,
	];

	public const REMIND_CREATE = [
		"key"    => "REMIND_CREATE",
		"limit"  => 10,
		"expire" => 60,
	];

	public const REMIND_REMOVE = [
		"key"    => "REMIND_REMOVE",
		"limit"  => 10,
		"expire" => 60,
	];

	# endregion
	##########################################################

	protected const _DB_KEY    = "company_system";
	protected const _TABLE_KEY = "antispam_user";

	// ------------------------------------------------------------
	// PUBLIC
	// ------------------------------------------------------------

	/**
	 * Проверяем на срабатывание блокировок по конкретному ключу
	 * Пишем статистику по срабатыванию блокировки если необходимо
	 *
	 * @param int         $user_id
	 * @param array       $block_key
	 * @param string|null $namespace
	 * @param string|null $row
	 *
	 * @return void
	 * @throws BlockException
	 * @throws \parseException
	 */
	public static function throwIfBlocked(int $user_id, array $block_key, string $namespace = null, string $row = null):void {

		if (self::needCheckIsBlocked()) {
			return;
		}

		// проверяем, что пользователь авторизован
		self::_throwIfPassedInvalidUserId($user_id);

		// получаем текущее состояние блокировки
		$row = self::_getRow($user_id, $block_key["key"], $block_key["expire"]);

		// если превысили лимит - выбрасываем исключение
		if ($row["count"] >= $block_key["limit"]) {

			// если необходимо - отправляем статистику (только один раз!!)
			if ($row["is_stat_sent"] == 0 && !is_null($namespace)) {
				self::_set($row["user_id"], $row["key"], 1, $row["count"], $row["expires_at"]);
			}

			throw new BlockException("User with user_id [{$user_id}] blocked with key: '{$block_key["key"]}'", $row["expires_at"]);
		}

		// обновляем запись
		self::_set($row["user_id"], $row["key"], $row["is_stat_sent"], $row["count"] + 1, $row["expires_at"]);
	}

	/**
	 * Установить значение блокировки метода
	 *
	 * @param int   $user_id
	 * @param array $block_key
	 * @param int   $value
	 *
	 * @return void
	 */
	public static function setBlockValue(int $user_id, array $block_key, int $value):void {

		$block_key = "self::{$block_key["key"]}";
		$block_key = constant($block_key);

		// получаем текущее состояние блокировки
		$row = self::_getRow($user_id, $block_key["key"], $block_key["expire"]);

		// обновляем запись
		self::_set($row["user_id"], $row["key"], $row["is_stat_sent"], $value, $row["expires_at"]);
	}

	// получаем состояние блокировки
	protected static function _getRow(int $user_id, string $key, int $expire):array {

		// получаем запись с блокировкой из базы
		$row = self::_get($user_id, $key);

		// если время превысило expires_at, то сбрасываем блокировку
		if (time() > $row["expires_at"]) {

			$row["count"]        = 0;
			$row["is_stat_sent"] = 0;
			$row["expires_at"]   = time() + $expire;
		}

		return $row;
	}

	// узнать сработала ли блокировка без инкремента
	public static function isBlock(int $user_id, array $block_key):bool {

		// проверяем, что пользователь авторизован
		self::_throwIfPassedInvalidUserId($user_id);

		// получаем текущее состояние блокировки
		$row = self::_get($user_id, $block_key["key"]);

		// если время превысило expires_at, то блокировка неактуальна и не сработает
		if (time() > $row["expires_at"]) {
			return false;
		}

		// если превысили лимит - блокировка сработала, возвращаем true
		if ($row["count"] >= $block_key["limit"]) {
			return true;
		}

		// во всех иных случаях - false
		return false;
	}

	/**
	 * Чистим таблицу с блокировками, если передан user_id чистим только по пользователю
	 */
	public static function clearAll(int $user_id = 0):void {

		// проверяем выполняется ли код на тестовом сервере/стейдже
		assertNotPublicServer();

		// формируем стандартный запрос для удаления всех блокировок
		$query = "DELETE FROM `?p` WHERE ?i = ?i LIMIT ?i";
		$args  = [1, 1, 1000000];

		// если передан пользователь - формируем запрос на удаление
		if ($user_id > 0) {

			$query = "DELETE FROM `?p` WHERE user_id = ?i LIMIT ?i";
			$args  = [$user_id, 1000000];
		}

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		ShardingGateway::database(self::_DB_KEY)->delete($query, self::_TABLE_KEY, ...$args);
	}

	// ------------------------------------------------------------
	// PROTECTED
	// ------------------------------------------------------------

	// выбрасываем ошибку, если пришел некорректный user_id
	protected static function _throwIfPassedInvalidUserId(int $user_id):void {

		// выбрасываем ошибку, если передан левачный user_id
		if ($user_id < 1) {
			throw new ParseFatalException("Not valid user_id passed to " . __CLASS__);
		}
	}

	// метод для обновления блокировки по пользователю
	protected static function _set(int $user_id, string $key, int $is_stat_sent, int $count, int $expires_at):void {

		$set = [
			"user_id"      => $user_id,
			"key"          => $key,
			"is_stat_sent" => $is_stat_sent,
			"count"        => $count,
			"expires_at"   => $expires_at,
		];

		ShardingGateway::database(self::_DB_KEY)->insertOrUpdate(self::_TABLE_KEY, $set);
	}

	// метод для получения статуса блокировки по пользователю
	protected static function _get(int $user_id, string $key):array {

		$row = ShardingGateway::database(self::_DB_KEY)
			->getOne("SELECT * FROM `?p` WHERE `user_id`=?i AND `key`=?s LIMIT ?i", self::_TABLE_KEY, $user_id, $key, 1);

		// если записи нет - формируем
		if (!isset($row["user_id"])) {

			$row = [
				"user_id"      => $user_id,
				"key"          => $key,
				"is_stat_sent" => 0,
				"count"        => 0,
				"expires_at"   => 0,
			];
		}

		return $row;
	}
}
