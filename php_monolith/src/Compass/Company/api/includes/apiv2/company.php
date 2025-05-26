<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ParamException;
use CompassApp\Domain\Member\Entity\Member;

/**
 * Контроллер для методов изменения профиля компании
 */
class Apiv2_Company extends \BaseFrame\Controller\Api {

	// доступные методы контроллера
	public const ALLOW_METHODS = [
		"changeInfo",
		"clearAvatar",
		"setGeneralChatNotifications",
		"getActivityData",
		"setUnlimitedMessagesEditing",
	];

	// список запрещенных методов по ролям
	public const RESTRICTED_METHOD_LIST_BY_ROLE = [

		// железобетонно зашиваем гостя
		Member::ROLE_GUEST => [
			"changeInfo",
			"clearAvatar",
			"setGeneralChatNotifications",
			"setUnlimitedMessagesEditing",
		],
	];

	/**
	 * Изменяем информацию компании
	 */
	public function changeInfo():array {

		$name            = $this->post(\Formatter::TYPE_STRING, "name", false);
		$avatar_file_key = $this->post(\Formatter::TYPE_STRING, "avatar_file_key", false);

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::COMPANY_SET_PROFILE);

		try {
			[$name, $avatar_file_key] = Domain_Company_Scenario_Api::changeInfo($this->user_id, $this->role, $this->permissions, $name, $avatar_file_key);
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			throw new CaseException(2235001, "User is not a company owner");
		} catch (cs_CompanyIncorrectName) {
			throw new CaseException(2235002, "Incorrect company name");
		} catch (Domain_Company_Exception_IncorrectAvatarFileKey) {
			throw new CaseException(2235003, "Incorrect avatar_file_key");
		} catch (Domain_Company_Exception_ParamsIsEmpty) {
			throw new \BaseFrame\Exception\Request\ParamException("Params is empty");
		}

		return $this->ok([
			"name"            => (string) $name,
			"avatar_file_key" => (string) $avatar_file_key,
		]);
	}

	/**
	 * Удаляем аватар компании
	 */
	public function clearAvatar():array {

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::COMPANY_CLEAR_AVATAR);

		try {
			Domain_Company_Scenario_Api::clearAvatar($this->user_id, $this->role, $this->permissions);
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			throw new CaseException(2235001, "User is not a company owner");
		}

		return $this->ok();
	}

	/**
	 * Изменяем настройки оповещений в главный чат
	 */
	public function setGeneralChatNotifications():array {

		$is_general_chat_notification_enabled = $this->post(\Formatter::TYPE_INT, "is_general_chat_notification_enabled");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::COMPANY_SET_GENERAL_CHAT_NOTIFICATIONS);

		try {
			Domain_Company_Scenario_Api::setGeneralChatNotifications($this->role, $this->permissions, $is_general_chat_notification_enabled);
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			throw new CaseException(2235001, "User is not a company owner");
		} catch (cs_InvalidConfigValue) {
			throw new \BaseFrame\Exception\Request\ParamException("Incorrect params");
		}

		return $this->ok();
	}

	/**
	 * Получить данные активной компании
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public function getActivityData():array {

		$activity_data = Domain_Company_Scenario_Api::getActivityData($this->user_id, $this->role, $this->permissions);

		return $this->ok(Apiv2_Format::activityData($activity_data));
	}

	/**
	 * Изменяем настройки ограничения редактирования сообщений
	 *
	 * @throws CaseException
	 * @throws ParseFatalException
	 * @throws BlockException
	 * @throws ParamException
	 * @throws \queryException
	 */
	public function setUnlimitedMessagesEditing():array {

		$is_unlimited_messages_editing_enabled = $this->post(\Formatter::TYPE_INT, "is_unlimited_messages_editing_enabled");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::COMPANY_SET_UNLIMITED_MESSAGES_EDITING);

		try {
			Domain_Company_Scenario_Api::setUnlimitedMessagesEditing($this->role, $this->permissions, $is_unlimited_messages_editing_enabled);
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			throw new CaseException(2235001, "User is not a company owner");
		} catch (cs_InvalidConfigValue) {
			throw new ParamException("Incorrect params");
		}

		return $this->ok();
	}
}
