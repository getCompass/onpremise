<?php

declare(strict_types = 1);

namespace Compass\Company;

use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ParamException;
use CompassApp\Domain\Member\Entity\Member;

/**
 * контроллер для работы с пользовательским ботом
 */
class Apiv2_Userbot_Developer extends \BaseFrame\Controller\Api {

	// все методы контроллера
	public const ALLOW_METHODS = [
		"create",
		"list",
		"edit",
		"refreshSecretKey",
		"refreshToken",
		"enable",
		"disable",
		"delete",
		"getSensitiveData",
		"addToGroup",
		"removeFromGroup",
	];

	// список запрещенных методов по ролям
	public const RESTRICTED_METHOD_LIST_BY_ROLE = [

		// железобетонно зашиваем гостя
		Member::ROLE_GUEST => self::ALLOW_METHODS,
	];

	/**
	 * метод для создания бота
	 *
	 * @throws ParamException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws \blockException
	 * @throws CaseException
	 */
	public function create():array {

		$name              = $this->post(\Formatter::TYPE_STRING, "name");
		$short_description = $this->post(\Formatter::TYPE_STRING, "short_description");
		$avatar_color_id   = $this->post(\Formatter::TYPE_INT, "avatar_color_id");
		$is_react_command  = $this->post(\Formatter::TYPE_INT, "is_react_command");
		$webhook           = $this->post(\Formatter::TYPE_STRING, "webhook", false);

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::USERBOT_CREATE);

		try {

			[$userbot, $sensitive_data] = Domain_Userbot_Scenario_Api::create(
				$this->role, $this->permissions, $name, $avatar_color_id, $short_description, $is_react_command, $webhook
			);
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			throw new CaseException(2216006, "user is not a developer");
		} catch (\cs_InvalidProfileName | Domain_Userbot_Exception_IncorrectParam) {
			throw new CaseException(2217001, "incorrect params");
		} catch (Domain_Userbot_Exception_EmptyWebhook) {
			throw new CaseException(2217003, "empty webhook");
		} catch (Domain_Userbot_Exception_CreateLimit) {
			throw new CaseException(2217010, "limit is exceeded for create");
		}

		$this->action->users([$userbot->user_id]);

		return $this->ok([
			"userbot"        => (object) Apiv2_Format::userbot($userbot),
			"sensitive_data" => (object) Apiv2_Format::userbotSensitiveData($sensitive_data),
		]);
	}

	/**
	 * метод для получения списка ботов
	 *
	 * @throws CaseException
	 * @throws ParamException
	 */
	public function list():array {

		$filter_active = $this->post(\Formatter::TYPE_INT, "filter_active");

		try {
			$list = Domain_Userbot_Scenario_Api::getList($this->role, $this->permissions, $filter_active);
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed|\CompassApp\Domain\Member\Exception\UserIsGuest) {
			throw new CaseException(2216006, "user is not have permissions for this action");
		}

		$this->action->users(array_column($list, "user_id"));

		return $this->ok([
			"list" => (array) $list,
		]);
	}

	/**
	 * редактируем бота
	 *
	 * @throws \blockException
	 * @throws CaseException
	 * @throws ParamException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \returnException
	 * @long - список параметров и ошибок длинный
	 */
	public function edit():array {

		$userbot_id        = $this->post(\Formatter::TYPE_STRING, "userbot_id");
		$name              = $this->post(\Formatter::TYPE_STRING, "name", false);
		$short_description = $this->post(\Formatter::TYPE_STRING, "short_description", false);
		$avatar_color_id   = $this->post(\Formatter::TYPE_INT, "avatar_color_id", false);
		$is_react_command  = $this->post(\Formatter::TYPE_INT, "is_react_command", false);
		$webhook           = $this->post(\Formatter::TYPE_STRING, "webhook", false);

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::USERBOT_EDIT);

		try {

			$user_id = Domain_Userbot_Scenario_Api::edit(
				$this->user_id, $this->role, $this->permissions, $userbot_id, $name, $short_description, $avatar_color_id, $is_react_command, $webhook
			);
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			throw new CaseException(2216006, "user is not have permissions for this action");
		} catch (\cs_InvalidProfileName | Domain_Userbot_Exception_IncorrectParam) {
			throw new CaseException(2217001, "incorrect param name");
		} catch (Domain_Userbot_Exception_EmptyParam) {
			throw new CaseException(2217002, "empty param");
		} catch (Domain_Userbot_Exception_EmptyWebhook) {
			throw new CaseException(2217003, "empty webhook");
		} catch (Domain_Userbot_Exception_UserbotNotFound) {
			throw new CaseException(2217004, "not found userbot");
		} catch (Domain_Userbot_Exception_DisabledStatus) {
			throw new CaseException(2217005, "userbot is disabled");
		} catch (Domain_Userbot_Exception_DeletedStatus) {
			throw new CaseException(2217008, "userbot is deleted");
		}

		$this->action->users([$user_id]);

		return $this->ok();
	}

	/**
	 * обновляем ключ шифрования
	 *
	 * @throws \blockException
	 * @throws CaseException
	 * @throws ParamException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function refreshSecretKey():array {

		$userbot_id = $this->post(\Formatter::TYPE_STRING, "userbot_id");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::USERBOT_REFRESH_SECRET_KEY);

		try {
			$secret_key = Domain_Userbot_Scenario_Api::refreshSecretKey($this->user_id, $this->role, $this->permissions, $userbot_id);
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			throw new CaseException(2216006, "user is not have permissions for this action");
		} catch (Domain_Userbot_Exception_UserbotNotFound) {
			throw new CaseException(2217004, "not found userbot");
		} catch (Domain_Userbot_Exception_DisabledStatus) {
			throw new CaseException(2217005, "userbot is disabled");
		} catch (Domain_Userbot_Exception_DeletedStatus) {
			throw new CaseException(2217008, "userbot is deleted");
		}

		return $this->ok([
			"secret_key" => (string) $secret_key,
		]);
	}

	/**
	 * обновляем токен бота
	 *
	 * @throws CaseException
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \blockException
	 * @throws \parseException
	 * @throws \blockException
	 */
	public function refreshToken():array {

		$userbot_id = $this->post(\Formatter::TYPE_STRING, "userbot_id");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::USERBOT_REFRESH_SECRET_KEY);

		try {
			$token = Domain_Userbot_Scenario_Api::refreshToken($this->user_id, $this->role, $this->permissions, $userbot_id);
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			throw new CaseException(2216006, "user is not have permissions for this action");
		} catch (Domain_Userbot_Exception_UserbotNotFound) {
			throw new CaseException(2217004, "not found userbot");
		} catch (Domain_Userbot_Exception_DisabledStatus) {
			throw new CaseException(2217005, "userbot is disabled");
		} catch (Domain_Userbot_Exception_DeletedStatus) {
			throw new CaseException(2217008, "userbot is deleted");
		}

		return $this->ok([
			"token" => (string) $token,
		]);
	}

	/**
	 * активируем бота
	 *
	 * @throws \blockException
	 * @throws CaseException
	 * @throws ParamException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function enable():array {

		$userbot_id = $this->post(\Formatter::TYPE_STRING, "userbot_id");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::USERBOT_ENABLE);

		try {
			Domain_Userbot_Scenario_Api::enable($this->user_id, $this->role, $this->permissions, $userbot_id);
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			throw new CaseException(2216006, "user is not have permissions for this action");
		} catch (Domain_Userbot_Exception_UserbotNotFound) {
			throw new CaseException(2217004, "not found userbot");
		} catch (Domain_Userbot_Exception_DeletedStatus) {
			throw new CaseException(2217008, "userbot is deleted");
		}

		return $this->ok();
	}

	/**
	 * деактивируем бота
	 *
	 * @throws \blockException
	 * @throws CaseException
	 * @throws ParamException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function disable():array {

		$userbot_id = $this->post(\Formatter::TYPE_STRING, "userbot_id");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::USERBOT_DISABLE);

		try {
			$disabled_at = Domain_Userbot_Scenario_Api::disable($this->user_id, $this->role, $this->permissions, $userbot_id);
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			throw new CaseException(2216006, "user is not have permissions for this action");
		} catch (Domain_Userbot_Exception_UserbotNotFound) {
			throw new CaseException(2217004, "not found userbot");
		} catch (Domain_Userbot_Exception_DeletedStatus) {
			throw new CaseException(2217008, "userbot is deleted");
		}

		return $this->ok([
			"disabled_at" => (int) $disabled_at,
		]);
	}

	/**
	 * удаляем бота
	 *
	 * @throws \blockException
	 * @throws CaseException
	 * @throws ParamException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function delete():array {

		$userbot_id = $this->post(\Formatter::TYPE_STRING, "userbot_id");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::USERBOT_DELETE);

		try {
			$deleted_at = Domain_Userbot_Scenario_Api::delete($this->user_id, $this->role, $this->permissions, $userbot_id);
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			throw new CaseException(2216006, "user is not have permissions for this action");
		} catch (Domain_Userbot_Exception_UserbotNotFound) {
			throw new CaseException(2217004, "not found userbot");
		} catch (Domain_Userbot_Exception_IncorrectStatus) {
			throw new CaseException(2217009, "userbot is enabled");
		}

		return $this->ok([
			"deleted_at" => (int) $deleted_at,
		]);
	}

	/**
	 * метод для получения чувствительной информации по боту
	 *
	 * @throws \blockException
	 * @throws CaseException
	 * @throws ParamException
	 * @throws \cs_DecryptHasFailed
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 */
	public function getSensitiveData():array {

		$userbot_id = $this->post(\Formatter::TYPE_STRING, "userbot_id");

		try {
			$sensitive_data = Domain_Userbot_Scenario_Api::getSensitiveData($this->user_id, $this->role, $this->permissions, $userbot_id);
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			throw new CaseException(2216006, "user is not have permissions for this action");
		} catch (Domain_Userbot_Exception_UserbotNotFound) {
			throw new CaseException(2217004, "not found userbot");
		} catch (Domain_Userbot_Exception_DeletedStatus) {
			throw new CaseException(2217008, "userbot is deleted");
		}

		return $this->ok([
			"sensitive_data" => (object) Apiv2_Format::userbotSensitiveData($sensitive_data),
		]);
	}

	/**
	 * добавить ботов в группу
	 *
	 * @throws CaseException
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \blockException
	 * @throws \cs_DecryptHasFailed
	 */
	public function addToGroup():array {

		$conversation_key = $this->post(\Formatter::TYPE_STRING, "conversation_key");
		$userbot_id_list  = $this->post(\Formatter::TYPE_ARRAY, "userbot_id_list");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::USERBOT_ADD_TO_GROUP);

		try {
			$conversation_map = \CompassApp\Pack\Conversation::doDecrypt($conversation_key);
		} catch (\cs_DecryptHasFailed) {
			throw new ParamException("incorrect conversation key");
		}

		try {
			Domain_Userbot_Scenario_Api::addToGroup($this->user_id, $this->role, $this->permissions, $userbot_id_list, $conversation_map);
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			throw new CaseException(2216006, "user is not have permissions for this action");
		} catch (Domain_Userbot_Exception_UserbotNotFound) {
			throw new CaseException(2217004, "not found userbot");
		} catch (Domain_Userbot_Exception_IncorrectStatus $e) {

			$extra = $e->getExtra();
			throw new CaseException(2217009, "one of userbot list is not enabled", [
				"deleted_userbot_id_list"  => $extra["deleted_userbot_id_list"] ?? [],
				"disabled_userbot_id_list" => $extra["disabled_userbot_id_list"] ?? [],
			]);
		} catch (Domain_Conversation_Exception_User_NotMember) {
			throw new CaseException(2218002, "user is not conversation member");
		}

		return $this->ok();
	}

	/**
	 * удаляем бота из группы
	 *
	 * @throws CaseException
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \blockException
	 * @throws \blockException
	 */
	public function removeFromGroup():array {

		$conversation_key = $this->post(\Formatter::TYPE_STRING, "conversation_key");
		$userbot_id       = $this->post(\Formatter::TYPE_STRING, "userbot_id");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::USERBOT_REMOVE_FROM_GROUP);

		try {
			$conversation_map = \CompassApp\Pack\Conversation::doDecrypt($conversation_key);
		} catch (\cs_DecryptHasFailed) {
			throw new ParamException("incorrect conversation key");
		}

		try {
			Domain_Userbot_Scenario_Api::removeFromGroup($this->user_id, $this->role, $this->permissions, $userbot_id, $conversation_map);
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			throw new CaseException(2216006, "user is not have permissions for this action");
		} catch (Domain_Userbot_Exception_UserbotNotFound) {
			throw new CaseException(2217004, "not found userbot");
		} catch (Domain_Userbot_Exception_DeletedStatus) {
			throw new CaseException(2217008, "userbot is deleted");
		}

		return $this->ok();
	}
}