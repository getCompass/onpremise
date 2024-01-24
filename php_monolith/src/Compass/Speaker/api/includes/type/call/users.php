<?php

namespace Compass\Speaker;

/**
 * Класс работает с полем users
 * Через него происходят все действия по взаимодействию с этим полем
 *
 * Структура поля users
 *
 * @formatter:off
 * Array
 * (
 *      [1] => Array
 *      (
 *            [status]			=> 10 – статус звонка для участника
 *            [role]			=> 1 - роль участника в звонке
 *            [finish_reason]		=> 0 – причина завершения звонка
 *            [invited_by_user_id]	=> 160605 – идентификатор пользоватея, который пригласил участника в звонок
 *            [session_uniq]		=> "415ab40ae9b7cc4e66d6769cb2c08106e8293b48" – session_uniq участника
 *            [user_agent]		=> "Compass (0.6.9) Electron darwin"
 *            [ip_address]		=> "95.53.122.55"
 *            [created_at]		=> 1530097651
 *            [updated_at]		=> 0
 *            [is_lost_connection]	=> 0 – потеряно ли в текущий момент соединение у участника
 *            [is_speaking]		=> 0 – установил ли пользователь соединение
 *            [is_need_relay]		=> 0 – нужно ли пользователю принудительно использовать TURN сервер
 *            [joined_at]		=> 1530097670
 *            [accepted_at]		=> 1530097670
 *            [established_at]	=> 1530097670
 *            [started_at]		=> 0
 *            [finished_at]		=> 0
 *            [conversation_map]	=> "{"a":1,"b":"2019_5","c":3,"d":1,"_":1,"?":"conversation","z":"d88c2b37"}"
 *            [node_ping_result]	=> [
 *                  [
 *                        "latency" => 10,
 *                        "node_id" => 1,
 *                  ],
 *            ]
 *      )
 *
 * )
 * @formatter:on
 */
class Type_Call_Users {

	// текущая версия
	protected const _USER_VERSION = 7;

	// схема users
	protected const _USER_SCHEMA = [
		1 => [
			"created_at"         => 0,
			"updated_at"         => 0,
			"is_lost_connection" => 0,
			"is_speaking"        => 0,
		],
		2 => [
			"session_uniq"       => "",
			"created_at"         => 0,
			"updated_at"         => 0,
			"is_lost_connection" => 0,
			"is_speaking"        => 0,
			"conversation_map"   => "",
			"node_ping_result"   => [],
		],
		3 => [
			"session_uniq"       => "",
			"user_agent"         => "",
			"created_at"         => 0,
			"updated_at"         => 0,
			"is_lost_connection" => 0,
			"is_speaking"        => 0,
			"conversation_map"   => "",
			"node_ping_result"   => [],
		],
		4 => [
			"status"             => CALL_STATUS_DIALING,
			"role"               => self::ROLE_DEFAULT,
			"finish_reason"      => 0,
			"invited_by_user_id" => 0,
			"session_uniq"       => "",
			"user_agent"         => "",
			"created_at"         => 0,
			"updated_at"         => 0,
			"is_lost_connection" => 0,
			"is_speaking"        => 0,
			"joined_at"          => 0,
			"accepted_at"        => 0,
			"started_at"         => 0,
			"finished_at"        => 0,
			"conversation_map"   => "",
			"node_ping_result"   => [],
		],
		5 => [
			"status"             => CALL_STATUS_DIALING,
			"role"               => self::ROLE_DEFAULT,
			"finish_reason"      => 0,
			"invited_by_user_id" => 0,
			"session_uniq"       => "",
			"user_agent"         => "",
			"created_at"         => 0,
			"updated_at"         => 0,
			"is_lost_connection" => 0,
			"is_speaking"        => 0,
			"joined_at"          => 0,
			"accepted_at"        => 0,
			"established_at"     => 0,
			"started_at"         => 0,
			"finished_at"        => 0,
			"conversation_map"   => "",
			"node_ping_result"   => [],
		],
		6 => [
			"status"             => CALL_STATUS_DIALING,
			"role"               => self::ROLE_DEFAULT,
			"finish_reason"      => 0,
			"invited_by_user_id" => 0,
			"session_uniq"       => "",
			"user_agent"         => "",
			"created_at"         => 0,
			"updated_at"         => 0,
			"is_lost_connection" => 0,
			"is_speaking"        => 0,
			"joined_at"          => 0,
			"accepted_at"        => 0,
			"established_at"     => 0,
			"started_at"         => 0,
			"finished_at"        => 0,
			"conversation_map"   => "",
			"node_ping_result"   => [],
			"device_id"          => "",
		],
		7 => [
			"status"             => CALL_STATUS_DIALING,
			"role"               => self::ROLE_DEFAULT,
			"finish_reason"      => 0,
			"invited_by_user_id" => 0,
			"session_uniq"       => "",
			"user_agent"         => "",
			"ip_address"         => "",
			"created_at"         => 0,
			"updated_at"         => 0,
			"is_lost_connection" => 0,
			"is_speaking"        => 0,
			"is_need_relay"      => 0,
			"joined_at"          => 0,
			"accepted_at"        => 0,
			"established_at"     => 0,
			"started_at"         => 0,
			"finished_at"        => 0,
			"conversation_map"   => "",
			"node_ping_result"   => [],
			"device_id"          => "",
		],
	];

	// роли пользователей в звонке
	public const ROLE_DEFAULT = 1;
	public const ROLE_LEAVED  = 8;

	// -------------------------------------------------------
	// PUBLIC STATIC
	// -------------------------------------------------------

	// проверяем, является ли пользователь участником звонка
	public static function isMember(int $user_id, array $users):bool {

		if (!isset($users[$user_id])) {
			return false;
		}

		if (self::getRole($users[$user_id]) == self::ROLE_LEAVED) {
			return false;
		}

		return true;
	}

	// проверяем, что сессия соответствует той, с которой запустился звонок
	public static function isCallStartSessionUniq(int $user_id, string $session_uniq, array $users):bool {

		$start_session_uniq = self::getSessionUniq($users[$user_id]);
		return $start_session_uniq == $session_uniq;
	}

	// проверяем, что айди девайса соответствует тому, с которого запустился звонок
	public static function isCallStartDeviceId(int $user_id, string $device_id, array $users):bool {

		$start_device_id = self::getDeviceId($users[$user_id]);
		return $start_device_id == $device_id;
	}

	// вернуть количество участников звонка
	public static function getCount(array $users):int {

		return count($users);
	}

	// подготовить talking_user_list на основе участников звонка
	public static function makeTalkingUserList(array $users):array {

		$talking_user_list = [];
		foreach ($users as $v) {
			$talking_user_list[] = Gateway_Bus_Sender::makeTalkingUserItem($v, false);
		}

		return $talking_user_list;
	}

	// создать новую структуру для users
	public static function initUserSchema(string $conversation_map, int $invited_by_user_id):array {

		// получаем текущую схему users
		$user_schema = self::_USER_SCHEMA[self::_USER_VERSION];

		// устанавливаем персональные параметры
		$user_schema["created_at"]         = time();
		$user_schema["conversation_map"]   = $conversation_map;
		$user_schema["invited_by_user_id"] = $invited_by_user_id;

		// устанавливаем текущую версию
		$user_schema["version"] = self::_USER_VERSION;

		return $user_schema;
	}

	// изменяем флаг is_lost_connection
	public static function setLostConnection(array $users, int $user_id, bool $is_lost_connection):array {

		$user_schema                       = self::_getUserSchema($users[$user_id]);
		$user_schema["is_lost_connection"] = $is_lost_connection ? 1 : 0;

		return $user_schema;
	}

	// получить флаг is_lost_connection
	public static function isLostConnection(int $user_id, array $users):bool {

		$user_schema = self::_getUserSchema($users[$user_id]);
		return $user_schema["is_lost_connection"] == 1;
	}

	// изменяем флаг is_speaking
	public static function setSpeaking(array $user_schema, bool $is_speaking):array {

		$user_schema                = self::_getUserSchema($user_schema);
		$user_schema["is_speaking"] = $is_speaking ? 1 : 0;

		return $user_schema;
	}

	// получить флаг is_speaking
	public static function isSpeaking(int $user_id, array $users):bool {

		$user_schema = self::_getUserSchema($users[$user_id]);
		return $user_schema["is_speaking"] == 1;
	}

	// получить оппонента в single-звонке
	public static function getOpponentFromSingleCall(int $user_id, array $users):int {

		if (count($users) > 2) {
			throw new \paramException("This is not single call");
		}

		foreach ($users as $k => $_) {

			if ($k != $user_id) {
				return $k;
			}
		}

		throw new \returnException("Not found another user_id");
	}

	// достаем conversation_map
	public static function getConversationMap(int $user_id, array $users):string {

		$user_schema = self::_getUserSchema($users[$user_id]);
		return $user_schema["conversation_map"] ?? "";
	}

	// записываем результаты пинга ноды
	public static function setPingResult(array $users, int $user_id, int $node_id, int $latency):array {

		$user_schema                       = self::_getUserSchema($users[$user_id]);
		$user_schema["node_ping_result"][] = [
			"node_id" => $node_id,
			"latency" => $latency,
		];

		return $user_schema;
	}

	// получаем результаты пинга ноды
	public static function getPingResult(int $user_id, array $users):array {

		$user_schema = self::_getUserSchema($users[$user_id]);
		return $user_schema["node_ping_result"];
	}

	// записываем session_uniq с помощью которого инициировали/приняли звонок
	public static function setSessionUniq(array $user_schema, string $session_uniq):array {

		$user_schema                 = self::_getUserSchema($user_schema);
		$user_schema["session_uniq"] = $session_uniq;

		return $user_schema;
	}

	// записываем device_id, с которого инициировали/приняли звонок
	public static function setDeviceId(array $user_schema, string $device_id):array {

		$user_schema              = self::_getUserSchema($user_schema);
		$user_schema["device_id"] = $device_id;

		return $user_schema;
	}

	// получаем session_uniq с помощью которого инициировали/приняли звонок
	public static function getSessionUniq(array $user_schema):string {

		$user_schema = self::_getUserSchema($user_schema);
		return $user_schema["session_uniq"];
	}

	// получаем device_id, с которого инициировали/приняли звонок
	public static function getDeviceId(array $user_schema):string {

		$user_schema = self::_getUserSchema($user_schema);
		return $user_schema["device_id"];
	}

	// записываем user_agent с помощью которого инициировали/приняли звонок
	public static function setUserAgent(array $user_schema, string $user_agent):array {

		$user_schema               = self::_getUserSchema($user_schema);
		$user_schema["user_agent"] = $user_agent;

		return $user_schema;
	}

	// получаем user_agent с помощью которого инициировали/приняли звонок
	public static function getUserAgent(array $user_schema):string {

		$user_schema = self::_getUserSchema($user_schema);
		return $user_schema["user_agent"];
	}

	// получаем статус пользователя в звонке
	public static function getStatus(array $user_schema):int {

		$user_schema = self::_getUserSchema($user_schema);
		return $user_schema["status"];
	}

	// устанавливаем статус пользователя в звонке
	public static function setStatus(array $user_schema, int $status):array {

		$user_schema           = self::_getUserSchema($user_schema);
		$user_schema["status"] = $status;

		return $user_schema;
	}

	// получаем причину hangup пользователя в звонке
	public static function getFinishReason(array $user_schema):int {

		$user_schema = self::_getUserSchema($user_schema);
		return $user_schema["finish_reason"];
	}

	// устанавливаем причину hangup пользователя в звонке
	public static function setFinishReason(array $user_schema, int $finish_reason):array {

		$user_schema                  = self::_getUserSchema($user_schema);
		$user_schema["finish_reason"] = $finish_reason;

		return $user_schema;
	}

	// получаем временную метку, когда приняли звонок
	public static function getAcceptedAt(array $user_schema):int {

		$user_schema = self::_getUserSchema($user_schema);
		return $user_schema["accepted_at"];
	}

	// устанавливаем временную метку, когда принял звонок пользователь
	public static function setAcceptedAt(array $user_schema, int $accepted_at):array {

		$user_schema                = self::_getUserSchema($user_schema);
		$user_schema["accepted_at"] = $accepted_at;

		return $user_schema;
	}

	// проверяем, может ли пользователь приглашать в звонок
	public static function isCanInvite(int $user_id, array $users):bool {

		if (!self::isMember($user_id, $users)) {
			return false;
		}

		return true;
	}

	// проверяем, может ли пользователь кикать участников звонка
	public static function isCanKick(int $user_id, array $users):bool {

		if (!self::isMember($user_id, $users)) {
			return false;
		}

		return true;
	}

	// получаем роль участника
	public static function getRole(array $user_schema):int {

		$user_schema = self::_getUserSchema($user_schema);
		return $user_schema["role"];
	}

	// устанавливаем роль участнику
	public static function setRole(array $user_schema, int $role):array {

		$user_schema         = self::_getUserSchema($user_schema);
		$user_schema["role"] = $role;

		return $user_schema;
	}

	// получаем пригласителя
	public static function getInvitedByUserId(array $user_schema):int {

		$user_schema = self::_getUserSchema($user_schema);
		return $user_schema["invited_by_user_id"];
	}

	// устанавливаем пригласителя
	public static function setInvitedByUserId(array $user_schema, int $invited_by_user_id):array {

		$user_schema                       = self::_getUserSchema($user_schema);
		$user_schema["invited_by_user_id"] = $invited_by_user_id;

		return $user_schema;
	}

	// функия возвращает участников звонка (в том числе и тех, кто бросил трубку)
	public static function getMemberList(array $users):array {

		// оставляем только тех пользователей, кто является участником звонка (например даже те кто положил трубку)
		$output = [];
		foreach ($users as $k => $v) {

			if (Type_Call_Users::getRole($v) == Type_Call_Users::ROLE_LEAVED) {
				continue;
			}

			$output[$k] = $v;
		}

		return $output;
	}

	// устанавливаем временную метку, когда пользователь начал звонок
	public static function setStartedAt(array $user_schema, int $started_at):array {

		$user_schema               = self::_getUserSchema($user_schema);
		$user_schema["started_at"] = $started_at;

		return $user_schema;
	}

	// получаем временную метку, когда пользователь начал звонок
	public static function getStartedAt(array $user_schema):int {

		$user_schema = self::_getUserSchema($user_schema);
		return $user_schema["started_at"];
	}

	// устанавливаем временную метку, когда пользователь закончил разговор
	public static function setFinishedAt(array $user_schema, int $finished_at):array {

		$user_schema                = self::_getUserSchema($user_schema);
		$user_schema["finished_at"] = $finished_at;

		return $user_schema;
	}

	// получаем временную метку, когда пользователь закончил разговор
	public static function getFinishedAt(array $user_schema):int {

		$user_schema = self::_getUserSchema($user_schema);
		return $user_schema["finished_at"];
	}

	// устанавливаем временную метку, когда пользователь вступил в разговор
	public static function setJoinedAt(array $user_schema, int $joined_at):array {

		$user_schema              = self::_getUserSchema($user_schema);
		$user_schema["joined_at"] = $joined_at;

		return $user_schema;
	}

	// устанавливаем временную метку в ms, когда пользователь установил соединение
	public static function setEstablishedAt(array $user_schema, int $established_at_ms):array {

		$user_schema                   = self::_getUserSchema($user_schema);
		$user_schema["established_at"] = $established_at_ms;

		return $user_schema;
	}

	// получаем временную метку в ms, когда пользователь установил соединение
	public static function getEstablishedAt(array $user_schema):int {

		$user_schema = self::_getUserSchema($user_schema);
		return $user_schema["established_at"];
	}

	// устанавливаем флаг – нужен ли пользователю relay (TURN сервер) для подключения
	public static function setNeedRelay(array $user_schema, bool $is_need_relay):array {

		$user_schema                  = self::_getUserSchema($user_schema);
		$user_schema["is_need_relay"] = intval($is_need_relay);

		return $user_schema;
	}

	// получаем флаг – нужен ли пользователю relay (TURN сервер) для подключения
	public static function isNeedRelay(array $user_schema):bool {

		$user_schema = self::_getUserSchema($user_schema);
		return $user_schema["is_need_relay"] == true;
	}

	// устанавливаем ip address
	public static function setIpAddress(array $user_schema, string $ip_address):array {

		$user_schema               = self::_getUserSchema($user_schema);
		$user_schema["ip_address"] = $ip_address;

		return $user_schema;
	}

	// получаем ip address
	public static function getIpAddress(array $user_schema):string {

		$user_schema = self::_getUserSchema($user_schema);
		return $user_schema["ip_address"];
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// получить актуальную структуру для users
	protected static function _getUserSchema(array $user_schema):array {

		// если версия не совпадает - дополняем её до текущей
		if ($user_schema["version"] != self::_USER_VERSION) {

			$user_schema            = array_merge(self::_USER_SCHEMA[self::_USER_VERSION], $user_schema);
			$user_schema["version"] = self::_USER_VERSION;
		}

		return $user_schema;
	}
}
