<?php

namespace Compass\Thread;

/**
 * -------------------------------------------------------
 * класс для работы с полем users в thread_meta
 * -------------------------------------------------------
 *
 * структура users
 * array
 * (
 *     [1] => array
 *         (
 *             [version] => 1
 *             [access_mask] => 8
 *         )
 *
 *     [2] => array
 *         (
 *             [version] => 1
 *             [access_mask] => 4
 *         )
 *
 * )
 */
class Type_Thread_Meta_Users {

	// private - все
	// public - только админы
	// open - админы/модераторы

	protected const _USERS_VERSION = 1;
	protected const _USERS_SCHEMA  = [
		"access_mask"          => 0,
		"count_hidden_message" => 0,
	];

	/**
	 * возвращает количество скрытых сообщений
	 *
	 */
	public static function getCountHiddenMessage(array $user_schema):int {

		$user_schema = self::_getUserSchema($user_schema);
		return $user_schema["count_hidden_message"];
	}

	// возвращает маску с правами участника
	public static function getAccessMask(array $user_schema):int {

		$user_schema = self::_getUserSchema($user_schema);
		return $user_schema["access_mask"];
	}

	// установить маску с правами участнику
	public static function setAccessMask(array $user_schema, int $mask, bool $value):array {

		$user_schema = self::_getUserSchema($user_schema);

		if ($value) {
			$user_schema["access_mask"] = $user_schema["access_mask"] | $mask;
		} else {
			$user_schema["access_mask"] = $user_schema["access_mask"] & ~$mask;
		}

		return $user_schema;
	}

	// увеличивает количество скрытых сообщений участнику
	public static function incCountHiddenMessage(array $user_schema, int $inc_count = 1):array {

		$user_schema = self::_getUserSchema($user_schema);
		$user_schema["count_hidden_message"] += $inc_count;

		return $user_schema;
	}

	// создать новую структуру для users
	public static function initUserSchema(int $access_mask, int $count_hidden_message = 0):array {

		// получаем текущую схему users
		$user_schema = self::_USERS_SCHEMA;

		// устанавливаем персональные параметры
		$user_schema["access_mask"]          = $access_mask;
		$user_schema["count_hidden_message"] = $count_hidden_message;

		// устанавливаем текущую версию
		$user_schema["version"] = self::_USERS_VERSION;

		return $user_schema;
	}

	// проверяем, что пользователь является участником треда
	public static function isMember(int $user_id, array $users):bool {

		return isset($users[$user_id]);
	}

	// проверяем, что пользователь может писать в тред
	public static function isCanWrite(int $user_id, array $users):bool {

		// если пользователь не участник
		if (!self::isMember($user_id, $users)) {
			return false;
		}

		$access_mask = self::getAccessMask($users[$user_id]);
		return $access_mask & THREAD_MEMBER_ACCESS_WRITE;
	}

	// проверяем, что пользователь может читать сообщения треде
	public static function isCanRead(int $user_id, array $users):bool {

		// если пользователь не участник
		if (!self::isMember($user_id, $users)) {
			return false;
		}

		$access_mask = self::getAccessMask($users[$user_id]);
		return $access_mask & THREAD_MEMBER_ACCESS_READ;
	}

	// проверяем, что пользователь может ставить реакции
	public static function isCanReact(int $user_id, array $users):bool {

		// если пользователь не участник
		if (!self::isMember($user_id, $users)) {
			return false;
		}

		$access_mask = self::getAccessMask($users[$user_id]);
		return $access_mask & THREAD_MEMBER_ACCESS_REACT;
	}

	// проверяем, что пользователь может управлять сообщениями
	public static function isCanManage(int $user_id, array $users):bool {

		// если пользователь не участник
		if (!self::isMember($user_id, $users)) {
			return false;
		}

		$access_mask = self::getAccessMask($users[$user_id]);

		return $access_mask & THREAD_MEMBER_ACCESS_MANAGE;
	}

	// добавляет юзеров из relationship в общий user_list
	public static function doMergeUsers(array $user_list, array $relationship_user_list):array {

		// создаем массив с пользователями что есть мете диалога
		$exist_user_list = $relationship_user_list;

		// убираем исключенных
		foreach ($relationship_user_list as $k1 => $_) {

			// если такой пользователь ранее был в треде
			if (isset($user_list[$k1])) {
				$exist_user_list[$k1]["count_hidden_message"] = Type_Thread_Meta_Users::getCountHiddenMessage($user_list[$k1]);
			}
		}

		return $exist_user_list;
	}

	// получаем массив для отправки go_sender ($talking_user_list)
	public static function getTalkingUserList(array $users, bool $is_need_push = false):array {

		$talking_user_list = [];
		foreach ($users as $k => $_) {
			$talking_user_list[] = Gateway_Bus_Sender::makeTalkingUserItem($k, $is_need_push);
		}

		return $talking_user_list;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// получить актуальную структуру для users
	protected static function _getUserSchema(array $user_schema):array {

		// если версия не совпадает - дополняем её до текущей
		if ($user_schema["version"] != self::_USERS_VERSION) {

			$user_schema            = array_merge(self::_USERS_SCHEMA, $user_schema);
			$user_schema["version"] = self::_USERS_VERSION;
		}

		return $user_schema;
	}
}
