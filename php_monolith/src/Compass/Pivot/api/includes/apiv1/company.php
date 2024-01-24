<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Exception\Request\ParamException;

/**
 * контроллер для работы с компаниями
 */
class Apiv1_Company extends \BaseFrame\Controller\Api {

	// поддерживаемые методы. регистр не имеет значение
	public const ALLOW_METHODS = [
		"add",
		"getList",
		"setCompanyListOrder",
		"removeFromList",
		"delete",
		"getUserStatus",
		"getBatching",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * Метод для добавления (создания) компании
	 */
	public function add():array {

		$avatar_color_id   = $this->post(\Formatter::TYPE_INT, "avatar_color_id", Domain_Company_Entity_Company::AVATAR_COLOR_GREEN_ID);
		$name              = $this->post(\Formatter::TYPE_STRING, "name");
		$client_company_id = $this->post(\Formatter::TYPE_STRING, "client_company_id");
		$avatar_file_key   = $this->post(\Formatter::TYPE_STRING, "avatar_file_key", false);

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::COMPANY_ADD);

		try {

			Gateway_Bus_CollectorAgent::init()->inc("row27");
			$user_company = Domain_Company_Scenario_Api::create($this->user_id, $avatar_color_id, $name, $client_company_id, $avatar_file_key);
		} catch (cs_CompanyIncorrectName) {

			Gateway_Bus_CollectorAgent::init()->inc("row28");
			return $this->error(650, "Incorrect company name");
		} catch (cs_CompanyIncorrectAvatarColorId) {

			Gateway_Bus_CollectorAgent::init()->inc("row29");
			return $this->error(651, "Incorrect company avatar_color_id");
		} catch (cs_NoFreeCompanyFound) {

			Gateway_Bus_CollectorAgent::init()->inc("row31");
			return $this->error(652, "There is no free company");
		} catch (cs_CompanyCreateExceededLimit) {

			Gateway_Bus_CollectorAgent::init()->inc("row32");
			return $this->error(670, "User has reached the company creation limit");
		} catch (cs_CompanyIncorrectClientCompanyId) {

			Gateway_Bus_CollectorAgent::init()->inc("row69");
			return $this->error(2505, "Incorrect client company id");
		}

		Gateway_Bus_CollectorAgent::init()->inc("row33");
		return $this->ok(Apiv1_Format::userCompany($user_company));
	}

	/**
	 * Метод для получения списка компании
	 *
	 * @return array
	 * @throws ParamException
	 */
	public function getList():array {

		$limit       = $this->post(\Formatter::TYPE_INT, "limit", Domain_Company_Entity_Filter::MAX_GET_USER_COMPANY_LIST);
		$min_order   = $this->post(\Formatter::TYPE_INT, "min_order", 0);
		$only_active = $this->post(\Formatter::TYPE_INT, "only_active", 1);

		try {
			[$company_list, $min_order] = Domain_Company_Scenario_Api::getUserCompanyList($this->user_id, $limit, $min_order, $only_active == 1);
		} catch (cs_CompanyIncorrectLimit) {
			return $this->error(657, "Incorrect limit");
		} catch (cs_CompanyIncorrectMinOrder) {
			return $this->error(658, "Incorrect min_order");
		}

		return $this->ok(Apiv1_Format::userCompanyList($company_list, $min_order));
	}

	/**
	 * Метод для установки порядка компаний
	 *
	 * @return array
	 * @throws ParamException
	 * @throws BlockException
	 * @throws \parseException
	 * @throws \returnException
	 * @throws cs_MissedValue
	 * @throws cs_blockException
	 */
	public function setCompanyListOrder():array {

		$company_order_list = $this->post(\Formatter::TYPE_ARRAY, "company_order");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::COMPANY_SET_POSITION);

		try {
			Domain_Company_Scenario_Api::setCompanyListOrder($this->user_id, $company_order_list);
		} catch (cs_DuplicateOrder) {
			return $this->error(1100, "Duplicate company order");
		} catch (cs_DuplicateCompanyId) {
			return $this->error(1101, "Duplicate company id");
		} catch (cs_FoundExtraValue) {
			return $this->error(1102, "Found extra company");
		} catch (cs_WrongValue) {
			return $this->error(1104, "Found wrong company");
		} catch (\cs_RowIsEmpty) {
			return $this->error(1105, "User companies not found");
		}

		return $this->ok();
	}

	/**
	 * удаляем компанию из списка
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \busException
	 * @throws \cs_SocketRequestIsFailed
	 * @throws cs_UserNotFound
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 * @throws \userAccessException
	 */
	public function removeFromList():array {

		$company_id = $this->post(\Formatter::TYPE_INT, "company_id");

		try {
			Domain_Company_Scenario_Api::removeFromList($this->user_id, $company_id);
		} catch (cs_CompanyNotExist) {
			throw new ParamException("not exist company");
		} catch (cs_HiringRequestNotPostmoderation) {
			throw new ParamException("hiring request not postmoderation");
		} catch (cs_CompanyIsNotLobby) {
			throw new ParamException("company is not in lobby");
		} catch (cs_CompanyIncorrectCompanyId) {
			throw new ParamException("invalid company_id");
		} catch (cs_UserAlreadyInCompany) {
			return $this->error(1103001, "user already in company");
		}

		return $this->ok();
	}

	/**
	 * Удалить компанию
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Request\CompanyIsHibernatedPivotException
	 * @throws \BaseFrame\Exception\Request\CompanyIsRelocatingPivotException
	 */
	public function delete():array {

		// версия метода
		return match ($this->method_version) {
			2       => $this->_deleteV2(),
			default => $this->_deleteV1(),
		};
	}

	/**
	 * удаление компании v1
	 *
	 * @long чтобы поместился весь api-метод
	 */
	protected function _deleteV1():array {

		$company_id = $this->post(\Formatter::TYPE_INT, "company_id");
		$two_fa_key = $this->post(\Formatter::TYPE_STRING, "two_fa_key", false);

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::TRY_COMPANY_DELETE);

		try {
			Domain_Company_Scenario_Api::delete($this->user_id, $company_id, $two_fa_key);
		} catch (\cs_CompanyUserIsNotOwner) {
			return $this->error(655, "user is not company owner");
		} catch (cs_UserNotInCompany) {
			return $this->error(1002, "user is not member of company");
		} catch (cs_CompanyNotExist) {
			return $this->error(1102, "not exist company");
		} catch (cs_TwoFaIsInvalid|cs_WrongTwoFaKey|cs_UnknownKeyType|cs_TwoFaTypeIsInvalid|cs_TwoFaInvalidUser|cs_TwoFaInvalidCompany) {
			return $this->error(2302, "2fa key is not valid");
		} catch (cs_TwoFaIsNotActive) {
			return $this->error(2303, "2fa key is not active. You need to confirm phone number");
		} catch (cs_CompanyIncorrectCompanyId) {
			throw new ParamException("invalid company id");
		} catch (Domain_Company_Exception_IsRelocating) {
			throw new \BaseFrame\Exception\Request\CompanyIsRelocatingPivotException("company is relocating");
		} catch (Domain_Company_Exception_IsHibernated) {
			throw new \BaseFrame\Exception\Request\CompanyIsHibernatedPivotException("company is hibernated");
		} catch (Domain_Company_Exception_IsNotServed) {

			// возвращаем ок если компания уже удалена
			return $this->ok();
		}

		return $this->ok();
	}

	/**
	 * удаление компании v2
	 * (отличия от прошлой версии: добавился error_code 1103002)
	 *
	 * @long чтобы поместился весь api-метод
	 */
	protected function _deleteV2():array {

		$company_id = $this->post(\Formatter::TYPE_INT, "company_id");
		$two_fa_key = $this->post(\Formatter::TYPE_STRING, "two_fa_key", false);

		try {
			Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::TRY_COMPANY_DELETE, true);
		} catch (cs_blockException) {
			return $this->error(1103002, "User was blocked due to excessive attempts to delete the company");
		}

		try {
			Domain_Company_Scenario_Api::delete($this->user_id, $company_id, $two_fa_key);
		} catch (\cs_CompanyUserIsNotOwner) {
			return $this->error(655, "user is not company owner");
		} catch (cs_UserNotInCompany) {
			return $this->error(1002, "user is not member of company");
		} catch (cs_CompanyNotExist) {
			return $this->error(1102, "not exist company");
		} catch (cs_TwoFaIsInvalid|cs_WrongTwoFaKey|cs_UnknownKeyType|cs_TwoFaTypeIsInvalid|cs_TwoFaInvalidUser|cs_TwoFaInvalidCompany) {
			return $this->error(2302, "2fa key is not valid");
		} catch (cs_TwoFaIsNotActive) {
			return $this->error(2303, "2fa key is not active. You need to confirm phone number");
		} catch (cs_CompanyIncorrectCompanyId) {
			throw new ParamException("invalid company id");
		} catch (Domain_Company_Exception_IsRelocating) {
			throw new \BaseFrame\Exception\Request\CompanyIsRelocatingPivotException("company is relocating");
		} catch (Domain_Company_Exception_IsHibernated) {
			throw new \BaseFrame\Exception\Request\CompanyIsHibernatedPivotException("company is hibernated");
		} catch (Domain_Company_Exception_IsNotServed) {

			// возвращаем ок если компания уже удалена
			return $this->ok();
		}

		return $this->ok();
	}

	/**
	 * получаем статус пользователя в компании
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function getUserStatus():array {

		$company_id = $this->post(\Formatter::TYPE_INT, "company_id");

		try {
			$user_status = Domain_Company_Scenario_Api::getUserStatus($this->user_id, $company_id);
		} catch (cs_CompanyNotExist) {
			return $this->error(1102, "not exist company");
		} catch (cs_CompanyIncorrectCompanyId) {
			throw new ParamException("invalid company id");
		}

		return $this->ok([
			"status" => (string) Apiv1_Format::formatUserCompanyStatus($user_status),
		]);
	}

	/**
	 * Метод для получения списка компании по массиву id
	 *
	 * @return array
	 * @throws \paramException
	 * @throws ParamException
	 */
	public function getBatching():array {

		$company_id_list = $this->post(\Formatter::TYPE_ARRAY_INT, "company_id_list");
		$only_active     = $this->post(\Formatter::TYPE_INT, "only_active", 1);

		try {
			$company_list = Domain_Company_Scenario_Api::getBatching($this->user_id, $company_id_list, $only_active == 1);
		} catch (cs_CompanyIncorrectCompanyIdList) {
			throw new ParamException("invalid company_id_list");
		}

		return $this->ok(Apiv1_Format::companyList($company_list));
	}
}
