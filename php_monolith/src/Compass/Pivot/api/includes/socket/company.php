<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\ParamException;

/**
 * контроллер для работы с компанией
 */
class Socket_Company extends \BaseFrame\Controller\Socket {

	// список доступных методов
	public const ALLOW_METHODS = [
		"clearAvatar",
		"changeInfo",
		"getCompanyUserIdList",
		"getTierList",
		"relocateToAnotherDomino",
		"getRelocationList",
		"delete",
		"getAnalyticsInfo",
	];

	/**
	 * Очистить аватар компании
	 */
	public function clearAvatar():array {

		Domain_Company_Scenario_Socket::clearAvatar($this->company_id);

		return $this->ok();
	}

	/**
	 * Изменить данные профиля компании
	 */
	public function changeInfo():array {

		$name            = $this->post(\Formatter::TYPE_STRING, "name", false);
		$avatar_file_key = $this->post(\Formatter::TYPE_STRING, "avatar_file_key", false);

		try {
			[$name, $avatar_file_key] = Domain_Company_Scenario_Socket::changeInfo($this->company_id, $name, $avatar_file_key);
		} catch (cs_UnknownKeyType|\cs_DecryptHasFailed|ParamException) {
			return $this->error(143001, "Incorrect avatar_file_key");
		}

		return $this->ok([
			"name"            => (string) $name,
			"avatar_file_key" => (string) $avatar_file_key,
		]);
	}

	/**
	 * Получить список пользователей в компании (используется только для биллинга)
	 */
	public function getCompanyUserIdList():array {

		$company_id = $this->post(\Formatter::TYPE_INT, "company_id");

		// получаем список пользователей
		$user_id_list = Gateway_Db_PivotCompany_CompanyUserList::getFullUserIdList($company_id);

		return $this->ok([
			"user_id_list" => (array) $user_id_list,
		]);
	}

	/**
	 * Получить список пользователей в компании (используется только для биллинга)
	 */
	public function getTierList():array {

		$limit  = $this->post(\Formatter::TYPE_INT, "limit");
		$offset = $this->post(\Formatter::TYPE_INT, "offset");

		$company_list = Domain_Company_Scenario_Socket::getTierList($limit, $offset);

		return $this->ok([
			"company_list" => (array) $company_list,
		]);
	}

	/**
	 * Начинаем процесс переезда на другое домино
	 */
	public function relocateToAnotherDomino():array {

		$company_id           = $this->post(\Formatter::TYPE_INT, "company_id");
		$expected_domino_tier = $this->post(\Formatter::TYPE_INT, "expected_domino_tier");
		$relocate_at          = $this->post(\Formatter::TYPE_INT, "relocate_at");

		if ($relocate_at < 0) {
			throw new ParamException("passed incorrect params");
		}

		// начинаем процесс перевозки
		try {
			$domino_id = Domain_Company_Scenario_Socket::relocateToAnotherDomino($company_id, $expected_domino_tier, $relocate_at);
		} catch (Domain_Company_Exception_NotExist|Domain_Company_Exception_IsNotServed) {
			return $this->error(1305001);
		} catch (Domain_Domino_Exception_DominoNotFound) {
			return $this->error(1305002);
		} catch (Domain_System_Exception_IsNotAllowedServiceTask) {
			return $this->error(1305003);
		}

		return $this->ok([
			"domino_id" => (string) $domino_id,
		]);
	}

	/**
	 * Получить список пользователей в компании (используется только для биллинга)
	 */
	public function getRelocationList():array {

		$limit  = $this->post(\Formatter::TYPE_INT, "limit");
		$offset = $this->post(\Formatter::TYPE_INT, "offset");

		$company_list = Domain_Company_Scenario_Socket::getRelocationList($limit, $offset);

		return $this->ok([
			"company_list" => (array) $company_list,
		]);
	}

	/**
	 * удаляем компанию
	 *
	 * @throws \BaseFrame\Exception\Request\CompanyIsHibernatedPivotException
	 * @throws \BaseFrame\Exception\Request\CompanyIsRelocatingPivotException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \returnException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public function delete():array {

		try {
			Domain_Company_Scenario_Socket::delete($this->user_id, $this->company_id);
		} catch (\cs_CompanyUserIsNotOwner) {
			return $this->error(1403001, "user is not company owner");
		} catch (cs_UserNotInCompany) {
			return $this->error(1403002, "user is not member of company");
		} catch (cs_CompanyNotExist|cs_CompanyIncorrectCompanyId) {
			return $this->error(1403003, "not exist company");
		} catch (Domain_Company_Exception_IsRelocating) {
			throw new \BaseFrame\Exception\Request\CompanyIsRelocatingPivotException("company is relocating");
		} catch (Domain_Company_Exception_IsHibernated) {
			throw new \BaseFrame\Exception\Request\CompanyIsHibernatedPivotException("company is hibernated");
		} catch (Domain_Company_Exception_IsNotServed) {
			// возвращаем ок если компания уже удалена
		}

		return $this->ok();
	}

	/**
	 * получаем данные для аналитики по компании
	 *
	 * @throws \BaseFrame\Exception\Request\CompanyIsHibernatedPivotException
	 * @throws \BaseFrame\Exception\Request\CompanyIsRelocatingPivotException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 */
	public function getAnalyticsInfo():array {

		try {

			[$created_by_user_id, $tariff_status, $max_member_count, $user_id_payer_list, $space_deleted_at] = Domain_Company_Scenario_Socket::getAnalyticsInfo($this->company_id);
		} catch (cs_CompanyNotExist|cs_CompanyIncorrectCompanyId) {
			return $this->error(1403003, "not exist company");
		} catch (Domain_Company_Exception_IsRelocating) {
			throw new \BaseFrame\Exception\Request\CompanyIsRelocatingPivotException("company is relocating");
		} catch (Domain_Company_Exception_IsHibernated) {
			throw new \BaseFrame\Exception\Request\CompanyIsHibernatedPivotException("company is hibernated");
		} catch (Domain_Company_Exception_IsNotServed) {
			throw new \BaseFrame\Exception\Request\CompanyNotServedException("company is not server");
		}

		return $this->ok([
			"user_id_creator"    => (int) $created_by_user_id,
			"tariff_status"      => (int) $tariff_status,
			"max_member_count"   => (int) $max_member_count,
			"user_id_payer_list" => (array) $user_id_payer_list,
			"space_deleted_at"   => (int) $space_deleted_at,
		]);
	}
}
