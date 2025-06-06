<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\ControllerMethodNotFoundException;
use BaseFrame\Exception\Request\ParamException;
use CompassApp\Domain\Member\Entity\Member;
use CompassApp\Domain\Member\Entity\Permission;
use CompassApp\Domain\Member\Exception\IsNotAdministrator;

/**
 * Класс для валидации разрешений пользователя
 */
class Domain_Member_Entity_Permission {

	/**
	 * Проверить наличие прав
	 *
	 * @param int    $user_id
	 * @param string $permission_key
	 *
	 * @return void
	 * @throws BusFatalException
	 * @throws ControllerMethodNotFoundException
	 * @throws Domain_Member_Exception_ActionNotAllowed
	 * @throws \cs_RowIsEmpty
	 */
	public static function check(int $user_id, string $permission_key):void {

		$member = Gateway_Bus_CompanyCache::getMember($user_id);

		try {

			// проверяем роль пользователя
			Member::assertUserAdministrator($member->role);
		} catch (IsNotAdministrator) {

			$member_permission = Gateway_Bus_CompanyCache::getConfigKey($permission_key);
			if ($member_permission->value["value"] === 0) {
				throw new Domain_Member_Exception_ActionNotAllowed("Action not allowed");
			}
		}
	}

	/**
	 * Получить значение права
	 *
	 * @param int    $user_id
	 * @param string $permission_key
	 *
	 * @return bool
	 * @throws BusFatalException
	 * @throws ControllerMethodNotFoundException
	 * @throws \cs_RowIsEmpty
	 */
	public static function get(int $user_id, string $permission_key):bool {

		$member = Gateway_Bus_CompanyCache::getMember($user_id);

		try {

			// проверяем роль пользователя
			Member::assertUserAdministrator($member->role);
		} catch (IsNotAdministrator) {

			$member_permission = Gateway_Bus_CompanyCache::getConfigKey($permission_key);
			return (bool) $member_permission->value["value"];
		}

		return true;
	}

	/**
	 * Проверяем разрешение
	 *
	 * @param int    $user_id
	 * @param int    $method_version
	 * @param string $permission_key
	 * @param array  $client_message_list
	 *
	 * @throws BusFatalException
	 * @throws ControllerMethodNotFoundException
	 * @throws Domain_Member_Exception_ActionNotAllowed
	 * @throws ParamException
	 * @throws \cs_RowIsEmpty
	 * @long Много проверок
	 */
	public static function checkVoice(int $user_id, int $method_version, string $permission_key, array $client_message_list):void {

		if ($method_version < 2) {
			return;
		}

		if (count($client_message_list) > 1) {
			return;
		}

		$client_message_list = array_reverse($client_message_list);
		$message             = array_pop($client_message_list);

		if (!isset($message["file_map"]) || mb_strlen($message["file_map"]) < 1) {

			if (!isset($message["file_key"]) || mb_strlen($message["file_key"]) < 1) {
				return;
			}
			$message["file_map"] = \CompassApp\Pack\File::tryDecrypt($message["file_key"]);
		}

		$file_type = \CompassApp\Pack\File::getFileType($message["file_map"]);

		// если это не голосовое - выходим
		if ($file_type !== FILE_TYPE_VOICE) {
			return;
		}

		$member = Gateway_Bus_CompanyCache::getMember($user_id);

		try {

			// проверяем роль пользователя
			Member::assertUserAdministrator($member->role);
		} catch (IsNotAdministrator) {

			$member_permission = Gateway_Bus_CompanyCache::getConfigKey($permission_key);
			if ($member_permission->value["value"] === 0) {
				throw new Domain_Member_Exception_ActionNotAllowed("Action not allowed");
			}
		}
	}

	/**
	 * Проверяем разрешение
	 *
	 * @throws \cs_RowIsEmpty
	 * @throws Domain_Member_Exception_ActionNotAllowed
	 * @throws BusFatalException
	 * @throws ControllerMethodNotFoundException
	 */
	public static function checkSingle(int $user_id, int $method_version, string $conversation_map):void {

		if ($method_version < 2) {
			return;
		}

		$member = Gateway_Bus_CompanyCache::getMember($user_id);

		try {

			// проверяем роль пользователя
			Member::assertUserAdministrator($member->role);
		} catch (IsNotAdministrator) {

			$member_permission = Gateway_Bus_CompanyCache::getConfigKey(Permission::IS_ADD_SINGLE_ENABLED);

			if ($member_permission->value["value"] === 0) {

				// пробуем получать чат, если он был скрыт - кидаем исключение
				$left_menu_row = Type_Conversation_LeftMenu::get($user_id, $conversation_map);

				if ($left_menu_row["is_hidden"] == 1) {
					throw new Domain_Member_Exception_ActionNotAllowed("Action not allowed");
				}
			}
		}
	}
}
