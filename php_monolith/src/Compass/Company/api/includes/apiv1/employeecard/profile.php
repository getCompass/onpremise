<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;
use CompassApp\Domain\Member\Entity\Member;

/**
 * Контроллер для методов карточки профиля
 */
class Apiv1_EmployeeCard_Profile extends \BaseFrame\Controller\Api {

	const ALLOW_METHODS = [
		"get",
		"setEmployeePlan",
	];

	public const MEMBER_ACTIVITY_METHOD_LIST = [
		"setEmployeePlan",
	];

	// список запрещенных методов по ролям
	public const RESTRICTED_METHOD_LIST_BY_ROLE = [

		// железобетонно зашиваем гостя
		Member::ROLE_GUEST => [
			"setEmployeePlan",
		],
	];

	/**
	 * получаем данные о сотруднике компании
	 *
	 * @return array
	 * @throws paramException
	 * @throws \parseException
	 */
	public function get():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		try {

			[$prepared_employee_card, $user_rating, $member_info, $member_count] = Domain_EmployeeCard_Scenario_Api::getProfile($this->user_id, $user_id);
		} catch (cs_IncorrectUserId) {
			throw new ParamException("incorrect param user_id");
		} catch (\cs_RowIsEmpty) {
			throw new ParamException("member not found");
		}

		// передаем пользователя в action users
		$this->action->users([$user_id]);

		$employee_card_version = match ($this->method_version) {
			1       => 1,
			default => 2,
		};

		return $this->ok([
			"employee_card" => (object) Apiv1_Format::employeeCard($prepared_employee_card, $user_rating, $member_info->created_at, $member_count, $employee_card_version),
		]);
	}

	/**
	 * устанавливаем план (по требовательности/респекту) для пользователя на текущий месяц
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws ParamException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function setEmployeePlan():array {

		$user_id            = $this->post(\Formatter::TYPE_INT, "user_id");
		$respect_count      = $this->post(\Formatter::TYPE_INT, "respect_count");
		$exactingness_count = $this->post(\Formatter::TYPE_INT, "exactingness_count");

		// проверяем параметры на корректность
		if ($user_id < 1 || $respect_count < 0 || $exactingness_count < 0 || $respect_count > 99 || $exactingness_count > 99) {
			throw new ParamException("incorrect params");
		}

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::PROFILE_SETEMPLOYEEPLAN);

		// проверяем, что такой пользователь существует
		$user_info = $this->_tryGetUserInfoAndThrowIfIncorrectUserId($user_id);
		if (Member::isDisabledProfile($user_info->role)) {
			return $this->error(532, "this user left company");
		}

		// если пользователь удалил аккаунт
		if (\CompassApp\Domain\Member\Entity\Extra::getIsDeleted($user_info->extra)) {
			return $this->error(2106001, "User delete his account");
		}

		// получаем запись редактора пользователя
		$editors_obj = Type_User_Card_EditorList::get($user_id);

		// проверяем, что наш пользователь имеет право редактировать карточку
		if (!Type_User_Card_EditorList::isHavePrivileges($this->user_id, $this->role, $this->permissions, $editors_obj->editor_list)) {
			return $this->error(930, "you do not belong to the list of administration or team-lead for this action");
		}

		// устанавливаем план на требовательность и респект на текущий месяц
		$month_start_at = monthStart();
		Type_User_Card_MonthPlan::insertOrUpdate($user_id, Type_User_Card_MonthPlan::MONTH_PLAN_RESPECT_TYPE, $month_start_at, $respect_count);
		Type_User_Card_MonthPlan::insertOrUpdate($user_id, Type_User_Card_MonthPlan::MONTH_PLAN_EXACTINGNESS_TYPE, $month_start_at, $exactingness_count);

		return $this->ok();
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * получить информацию о пользователе, но в случае некорректных данных — возвращать экзепшн
	 *
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 * @throws \returnException
	 * @throws \apiAccessException
	 */
	protected function _tryGetUserInfoAndThrowIfIncorrectUserId(int $user_id):\CompassApp\Domain\Member\Struct\Main {

		if ($user_id < 1) {
			throw new ParamException("incorrect param user_id");
		}

		// получаем информацию о пользователе
		try {
			$user_info = Gateway_Bus_CompanyCache::getMember($user_id);
		} catch (\cs_RowIsEmpty) {
			throw new ParamException("dont found user in company cache");
		}

		// если это бот
		if (Type_User_Main::isBot($user_info->npc_type)) {
			throw new ParamException("you can't do this action on bot-user");
		}

		return $user_info;
	}
}
