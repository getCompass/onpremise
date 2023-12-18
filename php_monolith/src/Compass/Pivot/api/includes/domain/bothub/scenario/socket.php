<?php

namespace Compass\Pivot;

/**
 * Сокет сценарии для хаба ботов
 */
class Domain_Bothub_Scenario_Socket {

	/**
	 * Получаем информацию по user_id пользователя
	 *
	 * @param int $user_id
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\EndpointAccessDeniedException
	 * @throws cs_UserNotFound
	 * @throws cs_UserPhoneSecurityNotFound
	 */
	public static function getUserInfoByUserId(int $user_id):array {

		if ($user_id < 1) {
			throw new cs_UserNotFound();
		}

		// получаем информацию о пользователе и его номер
		$user_info    = Gateway_Bus_PivotCache::getUserInfo($user_id);
		$phone_number = Domain_User_Entity_Phone::getPhoneByUserId($user_id);

		return self::_makeOutputUserInfo($user_info->user_id, $user_info->full_name, $phone_number,
			$user_info->created_at, Type_User_Main::isDisabledProfile($user_info->extra));
	}

	/**
	 * Получаем информацию по phone_number пользователя
	 *
	 * @param string $phone_number
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\EndpointAccessDeniedException
	 * @throws cs_PhoneNumberNotFound
	 * @throws cs_UserNotFound
	 */
	public static function getUserInfoByPhoneNumber(string $phone_number):array {

		if (mb_strlen($phone_number) < 1) {
			throw new cs_UserNotFound();
		}

		// получаем пользователя и информацию о нем
		$user_id   = Domain_User_Entity_Phone::getUserIdByPhone($phone_number);
		$user_info = Gateway_Bus_PivotCache::getUserInfo($user_id);

		return self::_makeOutputUserInfo($user_info->user_id, $user_info->full_name, $phone_number,
			$user_info->created_at, Type_User_Main::isDisabledProfile($user_info->extra));
	}

	/**
	 * Получаем список пространств пользователя
	 *
	 * @param int $user_id
	 *
	 * @return array
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\EndpointAccessDeniedException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \cs_SocketRequestIsFailed
	 * @throws cs_UserNotFound
	 */
	public static function getUserSpaceList(int $user_id):array {

		if ($user_id < 1) {
			throw new cs_UserNotFound();
		}

		// проверяем что пользователь существует
		Gateway_Bus_PivotCache::getUserInfo($user_id);

		$output = [];

		// получаем список пространств
		[$user_space_list, $_] = Domain_User_Action_GetOrderedCompanyList::do($user_id, 0, 100, true);
		$space_id_list = array_column($user_space_list, "company_id");
		$space_list    = Gateway_Db_PivotCompany_CompanyList::getList($space_id_list, true);
		foreach ($user_space_list as $space_item) {

			// получаем тарифф пространства
			$space_id     = $space_item->company_id;
			$space_tariff = Domain_SpaceTariff_Repository_Tariff::get($space_id);

			$is_creator = $user_id == $space_item->created_by_user_id;
			$is_admin   = false;
			if (!$is_creator) {

				// проверяем админ ли в пространстве
				$private_key = Domain_Company_Entity_Company::getPrivateKey($space_list[$space_id]->extra);
				$is_admin    = self::_isUserSpaceAdmin($user_id, $space_id, $space_list[$space_id]->domino_id, $space_list[$space_id]->status, $private_key);
			}

			$is_trial = $space_tariff->memberCount()->isTrial(time());
			$is_payed = $space_tariff->memberCount()->isActive(time()) && !$space_tariff->memberCount()->isFree(time());
			$output[] = self::_makeOutputUserSpaceItem($space_id, $space_list[$space_id]->status, $space_item->name, $space_item->member_count, $is_creator,
				$is_admin, $is_trial, $is_payed);
		}

		return $output;
	}

	/**
	 * Формируем output с информацией о пространстве пользователя
	 *
	 * @param int    $space_id
	 * @param int    $status
	 * @param string $name
	 * @param int    $member_count
	 * @param bool   $is_creator
	 * @param bool   $is_admin
	 * @param bool   $is_trial
	 * @param bool   $is_payed
	 *
	 * @return array
	 */
	protected static function _makeOutputUserSpaceItem(int $space_id, int $status, string $name, int $member_count, bool $is_creator, bool $is_admin, bool $is_trial, bool $is_payed):array {

		return [
			"space_id"     => (int) $space_id,
			"status"       => (int) $status,
			"name"         => (string) $name,
			"member_count" => (int) $member_count,
			"is_creator"   => (int) $is_creator,
			"is_admin"     => (int) $is_admin,
			"tariff"       => [
				"is_trial" => (int) $is_trial,
				"is_payed" => (int) $is_payed,
			],
		];
	}

	/**
	 * Получаем информацию по space_id пространства
	 *
	 * @param int $space_id
	 *
	 * @return array
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyNotExist
	 */
	public static function getSpaceInfo(int $space_id):array {

		if ($space_id < 1) {
			throw new cs_CompanyNotExist();
		}

		try {
			$space_info = Gateway_Db_PivotCompany_CompanyList::getOne($space_id);
		} catch (\cs_RowIsEmpty) {
			throw new cs_CompanyNotExist("space not found");
		}

		// получаем тариф
		$space_tariff = Domain_SpaceTariff_Repository_Tariff::get($space_id);

		$member_count        = Domain_Company_Entity_Company::getMemberCount($space_info->extra);
		$guest_count         = Domain_Company_Entity_Company::getGuestCount($space_info->extra);
		$is_trial            = $space_tariff->memberCount()->isTrial(time());
		$is_payed            = $space_tariff->memberCount()->isActive(time()) && !$space_tariff->memberCount()->isFree(time());
		$tariff_member_count = $space_tariff->memberCount()->getLimit();
		$tariff_active_till  = $space_tariff->memberCount()->getActiveTill();

		$output_space_info = self::_makeOutputSpaceInfo($space_info->company_id, $space_info->created_by_user_id, $space_info->status, $space_info->name,
			$member_count, $guest_count, $space_info->created_at, $is_trial, $is_payed, $tariff_member_count, $tariff_active_till);

		// получаем список участников пространства
		$space_user_id_list = Gateway_Db_PivotCompany_CompanyUserList::getFullUserIdList($space_id);

		return [$output_space_info, $space_user_id_list];
	}

	/**
	 * Получаем список участников пространства
	 *
	 * @param int   $space_id
	 * @param array $space_user_id_list
	 *
	 * @return array
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\EndpointAccessDeniedException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyNotExist
	 * @throws \cs_SocketRequestIsFailed
	 * @throws cs_UserNotFound
	 * @long
	 */
	public static function getSpaceMemberList(int $space_id, array $space_user_id_list):array {

		if ($space_id < 1) {
			throw new cs_CompanyNotExist();
		}

		try {
			$space_info = Gateway_Db_PivotCompany_CompanyList::getOne($space_id);
		} catch (\cs_RowIsEmpty) {
			throw new cs_CompanyNotExist("space not found");
		}

		// обязательно получаем записи пользователей для этого пространства, чтобы не присылали некорректные параметры
		// если о ком-то записи не оказалось - его не обработаем
		$space_user_list    = Gateway_Db_PivotUser_CompanyList::getListForCompany($space_user_id_list, $space_id);
		$space_user_id_list = array_column($space_user_list, "user_id");

		$output         = [];
		$user_list_info = Gateway_Bus_PivotCache::getUserListInfo($space_user_id_list);
		foreach ($space_user_id_list as $user_id) {

			try {

				$phone_number = Domain_User_Entity_Phone::getPhoneByUserId($user_id);

				$is_creator = $user_id == $space_info->created_by_user_id;
				$is_admin   = false;
				if (!$is_creator) {

					// проверяем админ ли в пространстве
					$private_key = Domain_Company_Entity_Company::getPrivateKey($space_info->extra);
					$is_admin    = self::_isUserSpaceAdmin($user_id, $space_id, $space_info->domino_id, $space_info->status, $private_key);
				}

				$output[] = self::_makeOutputSpaceMemberItem($user_id, $user_list_info[$user_id]->full_name, $phone_number, $is_creator, $is_admin);
			} catch (cs_UserNotFound|cs_UserPhoneSecurityNotFound) {
			}
		}

		return $output;
	}

	/**
	 * Формируем output с информацией об участнике пространства
	 *
	 * @param int    $user_id
	 * @param string $full_name
	 * @param string $phone_number
	 * @param bool   $is_creator
	 * @param bool   $is_admin
	 *
	 * @return array
	 */
	protected static function _makeOutputSpaceMemberItem(int $user_id, string $full_name, string $phone_number, bool $is_creator, bool $is_admin):array {

		return [
			"user_id"      => (int) $user_id,
			"full_name"    => (string) $full_name,
			"phone_number" => (string) $phone_number,
			"is_creator"   => (int) $is_creator,
			"is_admin"     => (int) $is_admin,
		];
	}

	/**
	 * Получаем самое большое пространство пользователя (по количеству участников)
	 *
	 * @param int $user_id
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\EndpointAccessDeniedException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_UserNotFound
	 * @throws cs_UserNotInCompany
	 * @long
	 */
	public static function getBiggestUserSpace(int $user_id):array {

		if ($user_id < 1) {
			throw new cs_UserNotFound();
		}

		// проверяем что пользователь существует
		Gateway_Bus_PivotCache::getUserInfo($user_id);

		// получаем список пространств
		[$user_space_list, $_] = Domain_User_Action_GetOrderedCompanyList::do($user_id, 0, 100, true);
		if (count($user_space_list) < 1) {
			throw new cs_UserNotInCompany();
		}

		$space_id_list = array_column($user_space_list, "company_id");
		$space_list    = Gateway_Db_PivotCompany_CompanyList::getList($space_id_list, true);
		$biggest_space = [];
		$member_count  = 0;
		$guest_count   = 0;
		foreach ($space_list as $space_item) {

			$temp = Domain_Company_Entity_Company::getMemberCount($space_item->extra);
			if ($temp > $member_count) {

				$biggest_space = $space_item;
				$member_count  = $temp;
				$guest_count   = Domain_Company_Entity_Company::getGuestCount($space_item->extra);
			}
		}

		// получаем тариф
		$space_tariff        = Domain_SpaceTariff_Repository_Tariff::get($biggest_space->company_id);
		$is_trial            = $space_tariff->memberCount()->isTrial(time());
		$is_payed            = $space_tariff->memberCount()->isActive(time()) && !$space_tariff->memberCount()->isFree(time());
		$tariff_member_count = $space_tariff->memberCount()->getLimit();
		$tariff_active_till  = $space_tariff->memberCount()->getActiveTill();

		return self::_makeOutputSpaceInfo($biggest_space->company_id, $biggest_space->created_by_user_id, $biggest_space->status, $biggest_space->name,
			$member_count, $guest_count, $biggest_space->created_at, $is_trial, $is_payed, $tariff_member_count, $tariff_active_till);
	}

	/**
	 * Получаем ключ диалога со службой поддержки
	 *
	 * @param int $user_id
	 * @param int $space_id
	 *
	 * @return string
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\EndpointAccessDeniedException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyIsHibernate
	 * @throws cs_CompanyIsNotActive
	 * @throws cs_CompanyNotExist
	 * @throws \cs_SocketRequestIsFailed
	 * @throws cs_UserNotFound
	 * @throws cs_UserNotInCompany
	 * @long
	 */
	public static function getUserSupportConversationKey(int $user_id, int $space_id):string {

		if ($user_id < 1 || $space_id < 1) {
			throw new \BaseFrame\Exception\Request\ParamException("incorrect params");
		}

		// проверяем что пользователь существует
		Gateway_Bus_PivotCache::getUserInfo($user_id);

		try {
			$space = Gateway_Db_PivotCompany_CompanyList::getOne($space_id);
		} catch (\cs_RowIsEmpty) {
			throw new cs_CompanyNotExist();
		}

		if ($space->status !== Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE) {
			throw new cs_CompanyIsNotActive("space not active");
		}

		// проверяем состоит ли в пространстве
		try {
			Gateway_Db_PivotUser_CompanyList::getOne($user_id, $space_id);
		} catch (\cs_RowIsEmpty) {
			throw new cs_UserNotInCompany();
		}

		$private_key = Domain_Company_Entity_Company::getPrivateKey($space->extra);
		return Gateway_Socket_Conversation::getUserSupportConversationKey($user_id, $space->company_id, $space->domino_id, $private_key);
	}

	/**
	 * Получаем информацию о действиях в пространстве
	 *
	 * @param int $space_id
	 *
	 * @return array
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyIsHibernate
	 * @throws cs_CompanyIsNotActive
	 * @throws cs_CompanyNotExist
	 * @throws cs_SocketRequestIsFailed
	 * @long
	 */
	public static function getEventCountInfo(int $space_id):array {

		if ($space_id < 1) {
			throw new \BaseFrame\Exception\Request\ParamException("incorrect params");
		}

		try {
			$space = Gateway_Db_PivotCompany_CompanyList::getOne($space_id);
		} catch (cs_RowIsEmpty) {
			throw new cs_CompanyNotExist();
		}

		if ($space->status !== Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE) {
			throw new cs_CompanyIsNotActive("space not active");
		}

		$private_key = Domain_Company_Entity_Company::getPrivateKey($space->extra);
		return Gateway_Socket_Company::getEventCountInfo($space->company_id, $space->domino_id, $private_key);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Формируем output с информацией о пользователе
	 *
	 * @param int    $user_id
	 * @param string $full_name
	 * @param string $phone_number
	 * @param int    $created_at
	 * @param bool   $is_deleted
	 *
	 * @return array
	 */
	protected static function _makeOutputUserInfo(int $user_id, string $full_name, string $phone_number, int $created_at, bool $is_deleted):array {

		return [
			"user_id"      => (int) $user_id,
			"full_name"    => (string) $full_name,
			"phone_number" => (string) $phone_number,
			"created_at"   => (int) $created_at,
			"is_deleted"   => (int) $is_deleted,
		];
	}

	/**
	 * Проверяем админ ли пользователь в пространстве
	 *
	 * @param int    $user_id
	 * @param int    $space_id
	 * @param string $domino_id
	 * @param int    $space_status
	 * @param string $private_key
	 *
	 * @return bool
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \cs_SocketRequestIsFailed
	 */
	protected static function _isUserSpaceAdmin(int $user_id, int $space_id, string $domino_id, int $space_status, string $private_key):bool {

		$is_admin = false;
		if ($space_status == Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE) {

			try {
				$is_admin = Gateway_Socket_Company::checkCanEditSpaceSettings($user_id, $space_id, $domino_id, $private_key);
			} catch (cs_CompanyIsHibernate) {
				// ничего не делаем
			}
		}

		return $is_admin;
	}

	/**
	 * Формируем output с информацией о пространстве
	 *
	 * @param int    $space_id
	 * @param int    $created_by_user_id
	 * @param int    $status
	 * @param string $name
	 * @param int    $space_member_count
	 * @param int    $guest_count
	 * @param int    $created_at
	 * @param bool   $is_trial
	 * @param bool   $is_payed
	 * @param int    $tariff_member_count
	 * @param int    $tariff_active_till
	 *
	 * @return array
	 */
	protected static function _makeOutputSpaceInfo(int  $space_id, int $created_by_user_id, int $status, string $name, int $space_member_count, int $guest_count, int $created_at,
								     bool $is_trial, bool $is_payed, int $tariff_member_count, int $tariff_active_till):array {

		return [
			"space_id"           => (int) $space_id,
			"status"             => (int) $status,
			"name"               => (string) $name,
			"member_count"       => (int) $space_member_count,
			"guest_count"        => (int) $guest_count,
			"created_by_user_id" => (int) $created_by_user_id,
			"created_at"         => (int) $created_at,
			"is_trial"           => (int) $is_trial,
			"is_payed"           => (int) $is_payed,
			"tariff"             => [
				"member_count" => (int) $tariff_member_count,
				"active_till"  => (int) $tariff_active_till,
			],
		];
	}
}
