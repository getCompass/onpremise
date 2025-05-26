<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ParamException;
use CompassApp\Domain\Member\Entity\Member;

/**
 * Контроллер для методов изменения настроек пространства
 */
class Apiv2_Space_Config extends \BaseFrame\Controller\Api {

	const ALLOW_METHODS = [
		"setMemberPermissions",
		"getMemberPermissions",
		"setAddToGeneralChatOnHiring",
		"setShowMessageReadStatus",
	];

	public const MEMBER_ACTIVITY_METHOD_LIST = [
		"setMemberPermissions",
		"getMemberPermissions",
		"setAddToGeneralChatOnHiring",
		"setShowMessageReadStatus",
	];

	// список запрещенных методов по ролям
	public const RESTRICTED_METHOD_LIST_BY_ROLE = [

		// железобетонно зашиваем гостя
		Member::ROLE_GUEST => self::ALLOW_METHODS,
	];

	/**
	 * Изменяем ограничения роли участника
	 *
	 * @throws \Exception
	 */
	public function setMemberPermissions():array {

		$member_permission_list = $this->post(\Formatter::TYPE_ARRAY, "member_permission_list");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::SET_MEMBER_PERMISSIONS);

		try {
			Domain_Company_Scenario_Api::setMemberPermissions($this->user_id, $member_permission_list);
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			return $this->error(2238002, "User is not a company owner");
		} catch (cs_InvalidConfigValue) {
			throw new ParamException("invalid config value");
		}

		return $this->ok();
	}

	/**
	 * Получаем список ограничений участника пространства
	 *
	 * @throws \Exception
	 */
	public function getMemberPermissions():array {

		$member_permission_list = Domain_Company_Scenario_Api::getMemberPermissions();

		return $this->ok([
			"member_permission_list" => (object) $member_permission_list,
		]);
	}

	/**
	 * Изменяем настройки добавлять ли пользователя в Главный чат при вступлении в пространство
	 */
	public function setAddToGeneralChatOnHiring():array {

		$is_add_to_general_chat_on_hiring = $this->post(\Formatter::TYPE_INT, "is_add_to_general_chat_on_hiring");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::SET_ADD_TO_GENERAL_CHAT_ON_HIRING);

		try {
			Domain_Company_Scenario_Api::setAddToGeneralChatOnHiring($this->role, $this->permissions, $is_add_to_general_chat_on_hiring);
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			throw new CaseException(2235001, "User is not a company owner");
		} catch (cs_InvalidConfigValue) {
			throw new \BaseFrame\Exception\Request\ParamException("Incorrect params");
		}

		return $this->ok();
	}

	/**
	 * Изменяем настройки позволять смотреть статус просмотра сообщения
	 */
	public function setShowMessageReadStatus():array {

		$is_add_to_general_chat_on_hiring = $this->post(\Formatter::TYPE_INT, "show_message_read_status");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::SET_SHOW_MESSAGE_READ_STATUS);

		try {
			Domain_Company_Scenario_Api::setShowMessageReadStatus($this->role, $this->permissions, $is_add_to_general_chat_on_hiring);
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			throw new CaseException(2235001, "User is not a company owner");
		} catch (cs_InvalidConfigValue) {
			throw new \BaseFrame\Exception\Request\ParamException("Incorrect params");
		}

		return $this->ok();
	}
}