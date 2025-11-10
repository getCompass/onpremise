<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для блокировки пользователя по user_id
 */
class Type_Antispam_User extends Type_Antispam_Main {

	##########################################################
	# region константы для блокировок
	##########################################################

	// -------------------------------------------------------
	// conversations
	// -------------------------------------------------------

	public const CONVERSATIONS_ADDSINGLE = [
		"key"    => "CONVERSATIONS_ADDSINGLE",
		"limit"  => 10,
		"expire" => 60,
	];

	public const CONVERSATIONS_DOREMOVESINGLE = [
		"key"    => "CONVERSATIONS_DOREMOVESINGLE",
		"limit"  => 15,
		"expire" => 60,
	];

	public const CONVERSATIONS_SETFAVORITE = [
		"key"    => "CONVERSATIONS_SETFAVORITE",
		"limit"  => 25,
		"expire" => 60 * 10,
	];

	public const CONVERSATIONS_REMOVEFAVORITE = [
		"key"    => "CONVERSATIONS_REMOVEFAVORITE",
		"limit"  => 25,
		"expire" => 60 * 10,
	];

	public const CONVERSATIONS_MUTE = [
		"key"    => "CONVERSATIONS_MUTE",
		"limit"  => 100,
		"expire" => 60 * 5,
	];

	public const CONVERSATIONS_UNMUTE = [
		"key"    => "CONVERSATIONS_UNMUTE",
		"limit"  => 100,
		"expire" => 60 * 5,
	];

	public const CONVERSATIONS_SET_AS_UNREAD = [
		"key"    => "CONVERSATIONS_SET_AS_UNREAD",
		"limit"  => 60,
		"expire" => 60 * 5,
	];

	public const CONVERSATIONS_DOCLEARMESSAGES = [
		"key"    => "CONVERSATIONS_DOCLEARMESSAGES",
		"limit"  => 10,
		"expire" => 60 * 5,
	];

	public const CONVERSATIONS_ADDMESSAGE = [
		"key"    => "CONVERSATIONS_ADDMESSAGE",
		"limit"  => 50,
		"expire" => 60,
	];

	public const CONVERSATIONS_EDITMESSAGE = [
		"key"    => "CONVERSATIONS_EDITMESSAGE",
		"limit"  => 5,
		"expire" => 5,
	];

	public const CONVERSATIONS_DELETEMESSAGE = [
		"key"    => "CONVERSATIONS_DELETEMESSAGE",
		"limit"  => 100,
		"expire" => 60 * 5,
	];

	public const CONVERSATIONS_HIDEMESSAGE = [
		"key"    => "CONVERSATIONS_HIDEMESSAGE",
		"limit"  => 50,
		"expire" => 60 * 5,
	];

	public const CONVERSATIONS_SETREACTION = [
		"key"    => "CONVERSATIONS_SETREACTION",
		"limit"  => 200,
		"expire" => 60,
	];

	public const CONVERSATIONS_ADDREPOST = [
		"key"    => "CONVERSATIONS_ADDREPOST",
		"limit"  => 8,
		"expire" => 60,
	];

	public const CONVERSATIONS_DOREPORTMESSAGE = [
		"key"    => "CONVERSATIONS_DOREPORTMESSAGE",
		"limit"  => 5,
		"expire" => DAY1,
	];

	public const CONVERSATIONS_SETMESSAGEASLAST = [
		"key"    => "CONVERSATIONS_SETMESSAGEASLAST",
		"limit"  => 50,
		"expire" => 60 * 5,
	];

	public const CONVERSATIONS_DOLIFTUP = [
		"key"    => "CONVERSATIONS_DOLIFTUP",
		"limit"  => 60,
		"expire" => 60,
	];

	public const CONVERSATIONS_DOCOMMITWORKEDHOURS = [
		"key"    => "CONVERSATIONS_DOCOMMITWORKEDHOURS",
		"limit"  => 8,
		"expire" => 60,
	];

	public const CONVERSATIONS_TRYEXACTING = [
		"key"    => "CONVERSATIONS_TRYEXACTING",
		"limit"  => 8,
		"expire" => 60,
	];

	public const CONVERSATIONS_SEARCH_MESSAGE_BY_PHRASE = [
		"key"    => "CONVERSATIONS_SEARCH_MESSAGE_BY_PHRASE",
		"limit"  => 120,
		"expire" => 60,
	];

	public const CONVERSATIONS_GET_MESSAGE_LIST_BY_PHRASE = [
		"key"    => "CONVERSATIONS_GET_MESSAGE_LIST_BY_PHRASE",
		"limit"  => 120,
		"expire" => 60,
	];

	const ALL_CONVERSATIONS_MESSAGES_READ = [
		"key"    => "ALL_CONVERSATIONS_MESSAGES_READ",
		"limit"  => 5,
		"expire" => 10 * 60,
	];

	public const CONVERSATIONS_SHAREMEMBER = [
		"key"    => "CONVERSATIONS_SHAREMEMBER",
		"limit"  => 10,
		"expire" => 60,
	];

	// -------------------------------------------------------
	// groups
	// -------------------------------------------------------

	public const GROUPS_ADD = [
		"key"    => "GROUPS_ADD",
		"limit"  => 10,
		"expire" => 60 * 2,
	];

	public const GROUPS_TRYCOPYGROUP = [
		"key"    => "GROUPS_TRYCOPYGROUP",
		"limit"  => 5,
		"expire" => 60 * 20,
	];

	public const GROUPS_DOLEAVE = [
		"key"    => "GROUPS_DOLEAVE",
		"limit"  => 50,
		"expire" => 60 * 10,
	];

	public const GROUPS_TRYKICK = [
		"key"    => "GROUPS_TRYKICK",
		"limit"  => 100,
		"expire" => 60 * 3,
	];

	public const GROUPS_SETNAME = [
		"key"    => "GROUPS_SETNAME",
		"limit"  => 20,
		"expire" => 60 * 60,
	];

	public const GROUPS_SETINFO = [
		"key"    => "GROUPS_SETINFO",
		"limit"  => 50,
		"expire" => 60 * 60,
	];

	public const GROUPS_SETAVATAR = [
		"key"    => "GROUPS_SETAVATAR",
		"limit"  => 20,
		"expire" => 60 * 60,
	];

	public const GROUPS_CLEARAVATAR = [
		"key"    => "GROUPS_CLEARAVATAR",
		"limit"  => 20,
		"expire" => 60 * 60,
	];

	public const GROUPS_ADDADMIN = [
		"key"    => "GROUPS_ADDADMIN",
		"limit"  => 30,
		"expire" => 60 * 20,
	];

	public const GROUPS_REMOVEADMIN = [
		"key"    => "GROUPS_REMOVEADMIN",
		"limit"  => 30,
		"expire" => 60 * 20,
	];

	public const GROUPS_TRYSELFASSIGNADMIN = [
		"key"    => "GROUPS_TRYSELFASSIGNADMIN",
		"limit"  => 30,
		"expire" => 60 * 20,
	];

	public const GROUPS_CHANGEROLE = [
		"key"    => "GROUPS_CHANGEROLE",
		"limit"  => 30,
		"expire" => 60 * 20,
	];

	public const GROUPS_DOREVOKEINVITE = [
		"key"    => "GROUPS_DOREVOKEINVITE",
		"limit"  => 20,
		"expire" => 60,
	];

	public const GROUPS_SETOPTIONS_IS_SHOW_HISTORY = [
		"key"    => "GROUPS_SETOPTIONS_IS_SHOW_HISTORY",
		"limit"  => 16,
		"expire" => 60,
	];

	public const GROUPS_SETOPTIONS_IS_CAN_COMMIT = [
		"key"    => "GROUPS_SETOPTIONS_IS_CAN_COMMIT",
		"limit"  => 16,
		"expire" => 60,
	];

	public const GROUPS_SETOPTIONS_IS_SHOW_SYSTEM_MESSAGE = [
		"key"    => "GROUPS_SETOPTIONS_IS_SHOW_SYSTEM_MESSAGE",
		"limit"  => 16,
		"expire" => 60,
	];

	public const GROUPS_SETOPTIONS_IS_NEED_SHOW_SYSTEM_MESSAGE_ON_INVITE_AND_JOIN_ON  = [
		"key"    => "GROUPS_SETOPTIONS_IS_NEED_SHOW_SYSTEM_MESSAGE_ON_INVITE_AND_JOIN_ON",
		"limit"  => 10,
		"expire" => 60,
	];
	public const GROUPS_SETOPTIONS_IS_NEED_SHOW_SYSTEM_MESSAGE_ON_INVITE_AND_JOIN_OFF = [
		"key"    => "GROUPS_SETOPTIONS_IS_NEED_SHOW_SYSTEM_MESSAGE_ON_INVITE_AND_JOIN_OFF",
		"limit"  => 10,
		"expire" => 60,
	];

	public const GROUPS_SETOPTIONS_IS_NEED_SHOW_SYSTEM_MESSAGE_ON_LEAVE_AND_KICKED_ON = [
		"key"    => "GROUPS_SETOPTIONS_IS_NEED_SHOW_SYSTEM_MESSAGE_ON_LEAVE_AND_KICKED_ON",
		"limit"  => 10,
		"expire" => 60,
	];

	public const GROUPS_SETOPTIONS_IS_NEED_SHOW_SYSTEM_MESSAGE_ON_LEAVE_AND_KICKED_OFF = [
		"key"    => "GROUPS_SETOPTIONS_IS_NEED_SHOW_SYSTEM_MESSAGE_ON_LEAVE_AND_KICKED_OFF",
		"limit"  => 10,
		"expire" => 60,
	];

	public const GROUPS_SETOPTIONS_IS_NEED_SHOW_SYSTEM_DELETED_MESSAGE_ON = [
		"key"    => "GROUPS_SETOPTIONS_IS_NEED_SHOW_SYSTEM_DELETED_MESSAGE_ON",
		"limit"  => 10,
		"expire" => 60,
	];

	public const GROUPS_SETOPTIONS_IS_NEED_SHOW_SYSTEM_DELETED_MESSAGE_OFF = [
		"key"    => "GROUPS_SETOPTIONS_IS_NEED_SHOW_SYSTEM_DELETED_MESSAGE_OFF",
		"limit"  => 10,
		"expire" => 60,
	];

	public const GROUPS_CLEARMESSAGESFORALL = [
		"key"    => "GROUPS_CLEARMESSAGESFORALL",
		"limit"  => 10,
		"expire" => 60 * 5,
	];

	public const GROUPS_ADDPARTICIPANT = [
		"key"    => "GROUPS_ADDPARTICIPANT",
		"limit"  => 10,
		"expire" => 60,
	];

	public const GROUPS_SETOPTIONS_IS_REACTIONS_ENABLED = [
		"key"    => "GROUPS_SETOPTIONS_IS_REACTIONS_ENABLED",
		"limit"  => 16,
		"expire" => 60,
	];

	public const GROUPS_SETOPTIONS_IS_COMMENTS_ENABLED = [
		"key"    => "GROUPS_SETOPTIONS_IS_COMMENTS_ENABLED",
		"limit"  => 16,
		"expire" => 60,
	];

	public const GROUPS_SETOPTIONS_IS_CHANNEL = [
		"key"    => "GROUPS_SETOPTIONS_IS_CHANNEL",
		"limit"  => 16,
		"expire" => 60,
	];

	// -------------------------------------------------------
	// invites
	// -------------------------------------------------------

	public const INVITES_TRYACCEPT = [
		"key"    => "INVITES_TRYACCEPT",
		"limit"  => 50,
		"expire" => HOUR3,
	];

	public const INVITES_DODECLINE = [
		"key"    => "INVITES_DODECLINE",
		"limit"  => 20,
		"expire" => 60,
	];

	public const INVITES_TRYSEND = [
		"key"    => "INVITES_TRYSEND",
		"limit"  => 30,
		"expire" => 60,
	];

	public const INVITES_GETALLOWEDUSERSFORINVITE = [
		"key"    => "INVITES_GETALLOWEDUSERSFORINVITE",
		"limit"  => 50,
		"expire" => 60 * 5,
	];

	public const INVITES_TRYSENDBATCHING = [
		"key"    => "INVITES_TRYSENDBATCHING",
		"limit"  => 50,
		"expire" => 60 * 5,
	];

	public const INVITES_TRYSENDBATCHINGFORGROUPS = [
		"key"    => "INVITES_TRYSENDBATCHINGFORGROUPS",
		"limit"  => 10,
		"expire" => 60 * 5,
	];

	// -------------------------------------------------------
	// remind
	// -------------------------------------------------------

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
	 * @param string|null $row_name
	 * @param string|null $key_extra
	 *
	 * @return void
	 * @throws BlockException
	 * @throws ParseFatalException
	 */
	public static function throwIfBlocked(int $user_id, array $block_key, ?string $namespace = null, ?string $row_name = null, ?string $key_extra = null):void {

		if (self::needCheckIsBlocked()) {
			return;
		}

		// проверяем, что пользователь авторизован
		self::_throwIfPassedInvalidUserId($user_id);

		// получаем текущее состояние блокировки
		$key = $block_key["key"] . $key_extra;
		$row = self::_getRow($user_id, $key, $block_key["expire"]);

		// если превысили лимит - выбрасываем исключение
		if ($row["count"] >= $block_key["limit"]) {

			// если необходимо - отправляем статистику (только один раз!!)
			if ($row["is_stat_sent"] == 0 && !is_null($namespace)) {

				Gateway_Bus_Statholder::inc($namespace, $row_name);
				self::_set($row["user_id"], $row["key"], 1, $row["count"], $row["expires_at"]);
			}

			throw new BlockException("User with user_id [{$user_id}] blocked with key: '{$block_key["key"]}'", $row["expires_at"]);
		}

		self::_set($row["user_id"], $row["key"], $row["is_stat_sent"], $row["count"] + 1, $row["expires_at"]);
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

	// получаем информацию о блокировке пользователя
	public static function get(int $user_id, string $key):array {

		// проверяем, что пользователь авторизован
		self::_throwIfPassedInvalidUserId($user_id);

		return self::_get($user_id, $key);
	}

	/**
	 * Чистим таблицу с блокировками, если передан пользователь, то только блокировки переданного пользователя
	 */
	public static function clearAll(int $user_id = 0):void {

		// проверяем выполняется ли код на тестовом сервере/стейдже
		assertNotPublicServer();

		// Формируем стандартный запрос для удаления всех блокировок
		$query = "DELETE FROM `?p` WHERE ?i = ?i LIMIT ?i";
		$args  = [1, 1, 1000000];

		// Если передан пользователь - формируем запрос на удаление
		if ($user_id > 0) {

			$query = "DELETE FROM `?p` WHERE user_id = ?i LIMIT ?i";
			$args  = [$user_id, 1000000];
		}

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		static::_connect()->delete($query, self::_TABLE_KEY, ...$args);
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

	// создаем новую или обновляем существующую запись в базе
	protected static function _set(int $user_id, string $key, int $is_stat_sent, int $count, int $expires_at):void {

		$set = [
			"user_id"      => $user_id,
			"key"          => $key,
			"is_stat_sent" => $is_stat_sent,
			"count"        => $count,
			"expires_at"   => $expires_at,
		];

		ShardingGateway::database(static::_DB_KEY)->insertOrUpdate(self::_TABLE_KEY, $set);
	}

	// получаем информацию по ключу и идентификатору пользователя
	protected static function _get(int $user_id, string $key):array {

		$row = ShardingGateway::database(static::_DB_KEY)
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
