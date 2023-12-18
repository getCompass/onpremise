<?php

namespace Compass\Pivot;

/**
 * Контроллер для работы с модулем php_intercom
 */
class Socket_Intercom extends \BaseFrame\Controller\Socket {

	public const ALLOW_METHODS = [
		"createOrUpdateOperator",
		"getCompanyInfo",
		"getUserInfo",
		"addOperatorToCompany",
	];

	/**
	 * Создаем или обновляем оператора
	 */
	public function createOrUpdateOperator():array {

		$full_name       = $this->post(\Formatter::TYPE_STRING, "full_name");
		$avatar_file_key = $this->post(\Formatter::TYPE_STRING, "avatar_file_key");

		// создаем или обновляем оператора
		$user_id = Domain_Intercom_Scenario_Socket::createOrUpdateOperator($this->user_id, $full_name, $avatar_file_key);

		return $this->ok([
			"user_id" => (int) $user_id,
		]);
	}

	/**
	 * Получаем данные по компании
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	public function getCompanyInfo():array {

		$company_id = $this->post(\Formatter::TYPE_INT, "company_id");

		try {
			$company = Domain_Intercom_Scenario_Socket::getCompanyInfo($company_id);
		} catch (cs_CompanyNotExist) {
			return $this->error(1307001);
		}

		return $this->ok([
			"company" => (object) $company,
		]);
	}

	/**
	 * Получаем данные по пользователю
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \userAccessException|\cs_RowIsEmpty
	 */
	public function getUserInfo():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		try {

			$user_info = Gateway_Bus_PivotCache::getUserInfo($user_id);
			if (mb_strlen($user_info->avatar_file_map) > 0) {
				$file_list = Gateway_Socket_PivotFileBalancer::getFileList([$user_info->avatar_file_map]);
			}
			$phone_row = Gateway_Db_PivotUser_UserSecurity::getOne($user_id);
		} catch (cs_UserNotFound) {
			return $this->error(1308001);
		}

		return $this->ok([
			"user" => (object) [
				"name"         => $user_info->full_name,
				"avatar_url"   => $file_list[0]["url"] ?? "",
				"created_at"   => $user_info->created_at,
				"phone_number" => $phone_row->phone_number,
			],
		]);
	}

	/**
	 * Добавляем оператора в компанию
	 *
	 * @return array
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \busException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyNotExist
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws \userAccessException
	 */
	public function addOperatorToCompany():array {

		$user_id    = $this->post(\Formatter::TYPE_INT, "user_id");
		$company_id = $this->post(\Formatter::TYPE_INT, "company_id");

		try {
			Domain_Intercom_Scenario_Socket::addOperatorToCompany($user_id, $company_id);
		} catch (cs_UserNotFound) {
			return $this->error(1308001);
		} catch (cs_CompanyIsNotActive) {
			return $this->error(1308002);
		} catch (cs_UserAlreadyInCompany) {
			// все ок, добавлять не нужно
		}

		return $this->ok();
	}
}