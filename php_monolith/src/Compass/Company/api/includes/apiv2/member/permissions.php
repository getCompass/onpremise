<?php

namespace Compass\Company;

use CompassApp\Domain\Member\Entity\Member;
use \CompassApp\Domain\Member\Entity\Permission;
use CompassApp\Domain\Member\Exception\IsNotAdministrator;

/**
 * Класс для управление правами участников
 */
class Apiv2_Member_Permissions extends \BaseFrame\Controller\Api {

	// доступные методы контроллера
	public const ALLOW_METHODS = [
		"get",
		"getBatching",
		"set",
		"setProfileCard",
		"upgradeGuest",
	];

	// методы, которые считаем за активность
	public const MEMBER_ACTIVITY_METHOD_LIST = [
		"set",
		"setProfileCard",
		"upgradeGuest",
	];

	// методы, требующие премиум доступа
	public const ALLOW_WITH_PREMIUM_ONLY_METHODS = [];

	// список запрещенных методов по ролям
	public const RESTRICTED_METHOD_LIST_BY_ROLE = [

		// железобетонно зашиваем гостя
		Member::ROLE_GUEST => self::ALLOW_METHODS,
	];

	/**
	 * Получить права участника
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CaseException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function get():array {

		$member_id = $this->post(\Formatter::TYPE_INT, "user_id");

		// проверяем, что пользователь может вызвать метод
		try {
			Member::assertUserAdministrator($this->role);
		} catch (IsNotAdministrator) {
			throw new \BaseFrame\Exception\Request\CaseException(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		}

		try {
			$formatted_permissions = Domain_Member_Scenario_Api::getPermissions($member_id, $this->method_version);
		} catch (\cs_RowIsEmpty) {
			throw new \BaseFrame\Exception\Request\CaseException(2209006, "member not found");
		}

		return $this->ok($formatted_permissions);
	}

	/**
	 * Получить права участника
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Request\CaseException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function getBatching():array {

		$member_id_list = $this->post(\Formatter::TYPE_ARRAY_INT, "user_id_list");

		// если ничего не передали - возвращаем ошибку
		if (count($member_id_list) < 1) {
			throw new \BaseFrame\Exception\Request\ParamException("empty list");
		}

		// если передали невалидное значение - возвращаем ошибку
		foreach ($member_id_list as $member_id) {

			if ($member_id < 1) {
				throw new \BaseFrame\Exception\Request\ParamException("invalid member_id");
			}
		}

		// проверяем, что пользователь может вызвать метод
		try {
			Member::assertUserAdministrator($this->role);
		} catch (IsNotAdministrator) {
			throw new \BaseFrame\Exception\Request\CaseException(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		}

		$member_permissions_list = Domain_Member_Scenario_Api::getPermissionsBatching($member_id_list, $this->method_version);

		return $this->ok([
			"member_permissions_list" => (array) $member_permissions_list,
		]);
	}

	/**
	 * Устанавливаем права участнику
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \BaseFrame\Exception\Request\CaseException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \busException
	 * @throws \parseException
	 * @long несколько try-catch
	 */
	public function set():array {

		$member_id   = $this->post(\Formatter::TYPE_INT, "user_id");
		$role        = $this->post(\Formatter::TYPE_STRING, "role", false);
		$permissions = $this->post(\Formatter::TYPE_JSON, "permissions", []);

		// во второй версии метода и выше не обращаем внимание на поле role
		if ($this->method_version >= 2) {
			$role = false;
		}

		// проверяем, что пользователь может вызвать метод
		try {
			Permission::assertCanManageAdministrators($this->role, $this->permissions);
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			throw new \BaseFrame\Exception\Request\CaseException(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		}

		// проверяем блокировку
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::SET_PERMISSIONS);

		try {

			// устанавливаем права
			Domain_Member_Scenario_Api::setPermissions($this->user_id, $member_id, $role, $permissions, $this->method_version);
		} catch (\cs_CompanyUserIncorrectRole) {
			throw new \BaseFrame\Exception\Request\ParamException("incorrect role");
		} catch (\cs_RowIsEmpty|\CompassApp\Domain\Member\Exception\IsLeft) {
			throw new \BaseFrame\Exception\Request\CaseException(2209006, "member not found");
		} catch (Domain_Member_Exception_SelfSetPermissions) {
			throw new \BaseFrame\Exception\Request\CaseException(2209002, "tried to set permissions for self");
		} catch (\CompassApp\Domain\Member\Exception\AccountDeleted) {
			throw new \BaseFrame\Exception\Request\CaseException(2209007, "member deleted account");
		} catch (Domain_Member_Exception_IncorrectUserId) {
			throw new \BaseFrame\Exception\Request\ParamException("incorrect user_id");
		} catch (\CompassApp\Domain\Member\Exception\UserIsGuest) {
			throw new \BaseFrame\Exception\Request\CaseException(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "not access for action");
		}

		return $this->ok();
	}

	/**
	 * Устанавливаем права в карточке пользователя
	 *
	 * @long
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \BaseFrame\Exception\Request\CaseException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \busException
	 * @throws \parseException
	 */
	public function setProfileCard():array {

		$member_id   = $this->post(\Formatter::TYPE_INT, "user_id");
		$permissions = $this->post(\Formatter::TYPE_JSON, "permissions");

		// проверяем, что пользователь может вызвать метод
		try {
			Member::assertUserAdministrator($this->role);
		} catch (IsNotAdministrator) {
			throw new \BaseFrame\Exception\Request\CaseException(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		}

		// проверяем блокировку
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::SET_PERMISSIONS_PROFILE_CARD);

		try {

			// устанавливаем права
			Domain_Member_Scenario_Api::setPermissionsProfileCard($this->user_id, $this->role, $this->permissions, $member_id, $permissions);
		} catch (\cs_CompanyUserIncorrectRole) {
			throw new \BaseFrame\Exception\Request\ParamException("incorrect role");
		} catch (\cs_RowIsEmpty|\CompassApp\Domain\Member\Exception\IsLeft) {
			throw new \BaseFrame\Exception\Request\CaseException(2209006, "member not found");
		} catch (Domain_Member_Exception_SelfSetPermissions) {
			throw new \BaseFrame\Exception\Request\CaseException(2209002, "tried to set permissions for self");
		} catch (\CompassApp\Domain\Member\Exception\AccountDeleted) {
			throw new \BaseFrame\Exception\Request\CaseException(2209007, "member deleted account");
		} catch (Domain_Member_Exception_IncorrectUserId) {
			throw new \BaseFrame\Exception\Request\ParamException("incorrect user_id");
		} catch (\CompassApp\Domain\Member\Exception\PermissionNotAllowedSetAnotherAdministrator) {
			throw new \BaseFrame\Exception\Request\CaseException(2209009, "permission can't be set to another administrator");
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			throw new \BaseFrame\Exception\Request\CaseException(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		}

		return $this->ok();
	}

	/**
	 * Повысить пользователя Гостя до Участника в пространстве
	 *
	 * @return array
	 * @throws Domain_Space_Exception_ActionRestrictedByTariff
	 * @throws \Throwable
	 * @throws \BaseFrame\Exception\Domain\LocaleTextNotFound
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \BaseFrame\Exception\Request\CaseException
	 * @throws \BaseFrame\Exception\Request\CompanyIsHibernatedException
	 * @throws \BaseFrame\Exception\Request\CompanyIsRelocatingException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \busException
	 * @throws \parseException
	 */
	public function upgradeGuest():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		try {
			Domain_Member_Scenario_Api::upgradeGuest($this->user_id, $this->role, $this->permissions, $user_id, \BaseFrame\System\Locale::getLocale());
		} catch (\CompassApp\Domain\Member\Exception\AccountDeleted) {
			throw new \BaseFrame\Exception\Request\CaseException(2209007, "member deleted account");
		} catch (\CompassApp\Domain\Member\Exception\IsLeft) {
			throw new \BaseFrame\Exception\Request\CaseException(2209006, "member not found");
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			throw new \BaseFrame\Exception\Request\CaseException(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		} catch (cs_IncorrectUserId|\cs_UserChangeSelfRole|\cs_RowIsEmpty) {
			throw new \BaseFrame\Exception\Request\ParamException("passed incorrect user_id");
		} catch (Domain_Space_Exception_ActionRestrictedByTariff) {
			throw new \BaseFrame\Exception\Request\PaymentRequiredException(2209011, "cant add new members");
		} catch (Domain_Member_Exception_UserHaveNotGuestRole) {
			// не возвращаем ошибку – считаем сценарий позитивным
		}

		$this->action->users([$user_id]);

		return $this->ok();
	}
}