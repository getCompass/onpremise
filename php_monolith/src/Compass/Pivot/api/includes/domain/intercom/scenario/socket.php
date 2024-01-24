<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Server\ServerProvider;

/**
 * Сценарии сокет-intercom.
 */
class Domain_Intercom_Scenario_Socket {

	/**
	 * Создаем или обновляем данные оператора
	 *
	 * @param int    $user_id
	 * @param string $full_name
	 * @param string $avatar_file_key
	 *
	 * @return int
	 * @throws Domain_User_Exception_AvatarIsDeleted
	 * @throws \BaseFrame\Exception\Domain\InvalidPhoneNumber
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \busException
	 * @throws cs_DamagedActionException
	 * @throws \cs_DecryptHasFailed
	 * @throws cs_FileIsNotImage
	 * @throws cs_InvalidAvatarFileMap
	 * @throws \cs_InvalidProfileName
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws \userAccessException
	 */
	public static function createOrUpdateOperator(int $user_id, string $full_name, string $avatar_file_key):int {

		if (ServerProvider::isOnPremise()) {
			throw new ParseFatalException("action is not allowed on this environment");
		}

		// если нужно обновить существующего оператора
		if ($user_id > 0) {
			return self::_updateOperator($user_id, $full_name, $avatar_file_key);
		}

		return self::_createOperator($full_name, $avatar_file_key);
	}

	/**
	 * Создаем оператора
	 *
	 * @param string $full_name
	 * @param string $avatar_file_key
	 *
	 * @return int
	 * @throws Domain_User_Exception_AvatarIsDeleted
	 * @throws \BaseFrame\Exception\Domain\InvalidPhoneNumber
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws cs_DamagedActionException
	 * @throws \cs_DecryptHasFailed
	 * @throws cs_InvalidAvatarFileMap
	 */
	protected static function _createOperator(string $full_name, string $avatar_file_key):int {

		if (mb_strlen($full_name) < 1 || mb_strlen($avatar_file_key) < 1) {
			throw new \BaseFrame\Exception\Request\ParamException("incorrect params");
		}

		$avatar_file_map = Type_Pack_File::doDecrypt($avatar_file_key);
		Domain_User_Entity_Validator::assertValidAvatarFileMap($avatar_file_map);

		// получаем аватар, чтобы убедиться, что он не удален
		$is_deleted = Gateway_Socket_PivotFileBalancer::checkIsDeleted($avatar_file_key);
		if ($is_deleted) {
			throw new Domain_User_Exception_AvatarIsDeleted("avatar is deleted");
		}

		// создаем оператора
		$user_info = Domain_User_Action_Create_Operator::do("", "", getIp(), $full_name, $avatar_file_map, []);
		return $user_info->user_id;
	}

	/**
	 * Обновляем оператора
	 *
	 * @param int    $user_id
	 * @param string $full_name
	 * @param string $avatar_file_key
	 *
	 * @return int
	 * @throws Domain_User_Exception_AvatarIsDeleted
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \busException
	 * @throws \cs_DecryptHasFailed
	 * @throws cs_FileIsNotImage
	 * @throws cs_InvalidAvatarFileMap
	 * @throws \cs_InvalidProfileName
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws \userAccessException
	 */
	protected static function _updateOperator(int $user_id, string $full_name, string $avatar_file_key):int {

		if (mb_strlen($full_name) < 1 && mb_strlen($avatar_file_key) < 1) {
			throw new \BaseFrame\Exception\Request\ParamException("incorrect params");
		}

		// проверяем, что пользователь существует
		try {
			$user_info = Gateway_Bus_PivotCache::getUserInfo($user_id);
		} catch (cs_UserNotFound) {
			throw new \BaseFrame\Exception\Request\ParamException("user not found");
		}

		// разрешаем обновлять только операторов
		if (!Type_User_Main::isOperator($user_info->npc_type)) {
			throw new \BaseFrame\Exception\Request\ParamException("action not allowed");
		}

		$full_name       = mb_strlen($full_name) < 1 ? false : $full_name;
		$avatar_file_map = false;
		if (mb_strlen($avatar_file_key) > 0) {
			$avatar_file_map = Type_Pack_File::doDecrypt($avatar_file_key);
		}
		$user_info = Domain_User_Scenario_Api::setProfile($user_id, $full_name, $avatar_file_map, true);
		return $user_info->user_id;
	}

	/**
	 * Получаем данные о компании
	 *
	 * @param int $company_id
	 *
	 * @return array
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyNotExist
	 */
	public static function getCompanyInfo(int $company_id):array {

		if (ServerProvider::isOnPremise()) {
			throw new ParseFatalException("action is not allowed on this environment");
		}

		try {
			$company_row    = Gateway_Db_PivotCompany_CompanyList::getOne($company_id);
			$entrypoint_url = self::_getEntryPoint($company_row->domino_id);
		} catch (\cs_RowIsEmpty|\BaseFrame\Exception\Request\CompanyNotServedException) {
			throw new cs_CompanyNotExist();
		}

		return [
			"company_id"     => $company_row->company_id,
			"name"           => $company_row->name,
			"member_count"   => Domain_Company_Entity_Company::getMemberCount($company_row->extra),
			"created_at"     => $company_row->created_at,
			"url"            => $company_row->url,
			"private_key"    => Domain_Company_Entity_Company::getPrivateKey($company_row->extra),
			"entrypoint_url" => $entrypoint_url,
		];
	}

	/**
	 * получаем url
	 *
	 * @param string $domino
	 *
	 * @return string
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 */
	protected static function _getEntryPoint(string $domino):string {

		$domino_entrypoint_config = getConfig("DOMINO_ENTRYPOINT");

		if (!isset($domino_entrypoint_config[$domino])) {
			throw new \BaseFrame\Exception\Request\CompanyNotServedException("company not served");
		}

		return $domino_entrypoint_config[$domino]["private_entrypoint"];
	}

	/**
	 * Добавляем оператора в компанию если нужно
	 *
	 * @param int $user_id
	 * @param int $company_id
	 *
	 * @return void
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \busException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyIsNotActive
	 * @throws cs_CompanyNotExist
	 * @throws \cs_SocketRequestIsFailed
	 * @throws cs_UserAlreadyInCompany
	 * @throws cs_UserNotFound
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws \userAccessException
	 * @long
	 */
	public static function addOperatorToCompany(int $user_id, int $company_id):void {

		if (ServerProvider::isOnPremise()) {
			throw new ParseFatalException("action is not allowed on this environment");
		}

		// проверяем что пользователь существует и пользователь оператор
		$user_info = Gateway_Bus_PivotCache::getUserInfo($user_id);
		if (!Type_User_Main::isOperator($user_info->npc_type)) {
			throw new \BaseFrame\Exception\Request\ParamException("incorrect user");
		}

		try {
			$company = Gateway_Db_PivotCompany_CompanyList::getOne($company_id);
		} catch (\cs_RowIsEmpty) {
			throw new cs_CompanyNotExist();
		}

		// работает только с активными компаниями
		if ($company->status !== Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE) {
			throw new cs_CompanyIsNotActive();
		}

		Domain_Company_Entity_User_Member::assertUserIsNotMemberOfCompany($user_id, $company_id);

		$operator_info = [
			"user_id"         => $user_info->user_id,
			"full_name"       => $user_info->full_name,
			"avatar_file_key" => mb_strlen($user_info->avatar_file_map) > 0 ? Type_Pack_File::doEncrypt($user_info->avatar_file_map) : "",
			"npc_type"        => $user_info->npc_type,
		];
		[$role, $permissions] = Gateway_Socket_Company::addOperator(
			$operator_info,
			$company_id,
			$company->domino_id,
			Domain_Company_Entity_Company::getPrivateKey($company->extra)
		);

		// добавляем пользователя в компанию на пивоте
		$order = Domain_Company_Entity_User_Order::getMaxOrder($user_id);
		$order++;

		Domain_Company_Entity_User_Member::add($user_id, $role, $permissions, $user_info->created_at, $company_id, $order, $user_info->npc_type);
	}
}