<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс работает с полем users в мете диалога
 * через него происходят все действия по взаимодействию с этим полем
 * структура поля users
 * Array
 * (
 *      [1] => Array
 *      (
 *            [role]       => 3
 *            [user_dpc]   => 1
 *            [created_at] => 1530097651
 *            [updated_at] => 0
 *      )
 * )
 */
class Type_Conversation_Meta_Users {

	public const ROLE_NOT_ATTACHED = 0; // пользователь не прикреплен
	public const ROLE_DEFAULT      = 1; // обычный участник диалога
	public const ROLE_ADMIN        = 2; // участник с правами администратора
	public const ROLE_OWNER        = 3; // создатель диалога/верховный

	// массив из доступных ролей
	public const AVAILABLE_ROLES = [
		self::ROLE_NOT_ATTACHED,
		self::ROLE_DEFAULT,
		self::ROLE_OWNER,
	];

	// массив из ролей, которые могут управлять группой
	public const MANAGED_ROLES = [
		self::ROLE_OWNER,
	];

	// -------------------------------------------------------
	// PUBLIC STATIC
	// -------------------------------------------------------

	// проверяем, является ли пользователь участником диалога
	public static function assertIsMember(int $user_id, array $users):void {

		if (!self::isMember($user_id, $users)) {
			throw new cs_UserIsNotMember();
		}
	}

	// проверяем, является ли пользователь участником диалога
	public static function isMember(int $user_id, array $users):bool {

		if (isset($users[$user_id]) && self::getUserRole($users[$user_id]) != self::ROLE_NOT_ATTACHED) {
			return true;
		}

		return false;
	}

	// проверяем, есть ли пользователь в users
	public static function isExistInUsers(int $user_id, array $users):bool {

		if (isset($users[$user_id])) {
			return true;
		}

		return false;
	}

	// проверяем, что пользователь является администратором диалога
	public static function isOwnerMember(int $user_id, array $users):bool {

		if (!isset($users[$user_id])) {
			return false;
		}

		// если пользователь админ или владелец
		if (self::getUserRole($users[$user_id]) == self::ROLE_ADMIN ||
			self::getUserRole($users[$user_id]) == self::ROLE_OWNER) {
			return true;
		}

		return false;
	}

	// проверяем, что пользователь является обычным участником диалога
	public static function isDefaultMember(int $user_id, array $users):bool {

		if (!isset($users[$user_id])) {
			return false;
		}

		if (self::getUserRole($users[$user_id]) == self::ROLE_DEFAULT) {
			return true;
		}

		return false;
	}

	// проверяем, что пользователь является непривязанным участником диалога
	public static function isNotAttachedMember(int $user_id, array $users):bool {

		if (!isset($users[$user_id])) {
			return false;
		}

		if (self::getUserRole($users[$user_id]) == self::ROLE_NOT_ATTACHED) {
			return true;
		}

		return false;
	}

	/**
	 * Проверяем, что являемся администратором группы
	 *
	 * @param int   $user_id
	 * @param array $users
	 *
	 * @return bool
	 */
	public static function isGroupAdmin(int $user_id, array $users):bool {

		// если пользователь не является участником
		if (!isset($users[$user_id])) {
			return false;
		}

		// наш пользователь админ группы или админ всех групп
		return self::isOwnerMember($user_id, $users);
	}

	// пользователь является единственным верховным
	public static function isUserLastOwner(int $user_id, array $users):bool {

		// если роль пользователя не верховный
		if (self::getRole($user_id, $users) != self::ROLE_OWNER) {
			return false;
		}

		// собираем только верховных
		$owner_list = [];
		foreach ($users as $k => $_) {

			if (self::getRole($k, $users) != self::ROLE_OWNER) {
				continue;
			}
			$owner_list[] = true;
		}

		// если набралось всего один штука верховный, значит пользователь - последний в своем роде
		if (count($owner_list) == 1) {
			return true;
		}

		return false;
	}

	// проверяем есть ли в группе кто-то с управляющей ролью
	public static function isConversationHasAdminOrOwner(array $users):bool {

		// проверяем всех пользователей в группе
		foreach ($users as $k => $_) {

			// если нашли админа или владельца - вернули true
			if (self::isOwnerMember($k, $users)) {
				return true;
			}
		}

		return false;
	}

	// получаем создателя группы
	public static function getOwner(array $users):int {

		foreach ($users as $k => $v) {

			$role = self::getUserRole($v);

			// если роль создателя группы, то возвращаем идентификатор этого пользователя
			if ($role == self::ROLE_OWNER) {
				return $k;
			}
		}

		throw new ParseFatalException("Conversation have not member with owner role");
	}

	// получаем владельцев группы
	public static function getOwners(array $users):array {

		// массив с идентификаторами овнеров
		$owner_list = [];

		foreach ($users as $k => $v) {

			// получаем роль
			$role = self::getUserRole($v);

			// если роль овнера, то добавляем его в массив
			if ($role == self::ROLE_OWNER) {
				$owner_list[] = $k;
			}
		}

		return $owner_list;
	}

	// возвращает собеседника в сингл диалоге
	public static function getOpponentId(int $user_id, array $users):int|null {

		unset($users[$user_id]);
		return key($users);
	}

	// меняет роль пользователю
	public static function changeMemberRole(int $user_id, int $new_role, array $users):array {

		// проверяем доступна ли эта роль
		if (!in_array($new_role, self::AVAILABLE_ROLES)) {
			throw new ParseFatalException("Role with ID '$new_role' is not available");
		}

		if (self::getRole($user_id, $users) < self::ROLE_ADMIN && self::isConversationHasAdminOrOwner($users)) {
			throw new ParseFatalException("User with ID '$user_id' try get AdminRights in conversation");
		}

		// выставляем новую роль
		$users[$user_id] = self::setUserRole($users[$user_id], $new_role);

		return $users;
	}

	// добавляет в массив users нового участника
	public static function addMember(array $users, int $user_id, int $role):array {

		// проверяем что такая роль доступна
		if (!in_array($role, self::AVAILABLE_ROLES)) {
			throw new ParseFatalException("Role with ID '$role' is not available");
		}

		$users[$user_id] = self::initUserSchema($role);

		return $users;
	}

	// убирает участника из массива users
	public static function removeMember(array $users, int $user_id):array {

		unset($users[$user_id]);

		return $users;
	}

	// возвращаем список участников для php_thread в формате:
	//  {
	//    "user_id": 1,
	//    "role": 1
	//   },
	public static function formatUsersForThread(array $users):array {

		$output = [];

		foreach ($users as $k => $v) {

			$output[] = [
				"user_id" => $k,
				"role"    => self::getUserRole($v),
			];
		}

		return $output;
	}

	// возвращаем роль участника
	public static function getRole(int $user_id, array $users):int {

		if (!self::isMember($user_id, $users)) {
			return self::ROLE_NOT_ATTACHED;
		}

		$user_schema = $users[$user_id];
		return self::getUserRole($user_schema);
	}

	// массив юзеров для толкинга
	public static function getTalkingUserList(array $users, bool $is_need_push = false):array {

		$talking_user_list = [];
		foreach ($users as $k => $_) {
			$talking_user_list[] = Gateway_Bus_Sender::makeTalkingUserItem($k, $is_need_push);
		}

		return $talking_user_list;
	}

	// получает список user_id из массива users отсортированный по дате обновления
	public static function getUserIdListSortedByUpdateTime(array $users):array {

		// сортируем массив
		uasort($users, function(array $a, array $b) {

			return $a["updated_at"] <=> $b["updated_at"];
		});

		// возвращаем только идентификаторы юзеров
		return array_keys($users);
	}

	// получает список user_id из массива users отсортированный по дате вступления
	public static function getUserIdListSortedByJoinTime(array $users):array {

		// сортируем массив
		uasort($users, function(array $a, array $b) {

			return $a["created_at"] <=> $b["created_at"];
		});

		// возвращаем только идентификаторы юзеров
		return array_keys($users);
	}

	# region DATA VARIABLES

	// текущая версия
	protected const _USERS_SCHEMA_VERSION = 1;

	// схема users
	protected const _USERS_SCHEMA = [
		"role"       => 0,
		"created_at" => 0,
		"updated_at" => 0,
	];

	// поменять роль пользователя
	public static function setUserRole(array $user_schema, int $role):array {

		// актуализируем user_schema
		$user_schema = self::_getUserSchema($user_schema);

		// устанавливаем роль
		$user_schema["role"] = $role;

		// обновляем updated_at
		$user_schema["updated_at"] = time();

		return $user_schema;
	}

	// возвращает роль пользователя
	public static function getUserRole(array $user_schema):int {

		// актуализируем user_schema
		$user_schema = self::_getUserSchema($user_schema);

		return $user_schema["role"];
	}

	// создать новую структуру для users
	public static function initUserSchema(int $role):array {

		// получаем текущую схему users
		$user_schema = self::_USERS_SCHEMA;

		// устанавливаем персональные параметры
		$user_schema["role"]       = $role;
		$user_schema["created_at"] = time();

		// устанавливаем текущую версию
		$user_schema["version"] = self::_USERS_SCHEMA_VERSION;

		return $user_schema;
	}

	// получить актуальную структуру для users
	protected static function _getUserSchema(array $user_schema):array {

		// если версия не совпадает - дополняем её до текущей
		if ($user_schema["version"] != self::_USERS_SCHEMA_VERSION) {

			$user_schema            = array_merge(self::_USERS_SCHEMA, $user_schema);
			$user_schema["version"] = self::_USERS_SCHEMA_VERSION;
		}

		return $user_schema;
	}

	# endregion
}