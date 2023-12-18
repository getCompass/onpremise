<?php

namespace Compass\Pivot;

/**
 * класс-агрегат для компании пользователя
 */
class Struct_User_Company {

	public const ACTIVE_STATUS        = "active";
	public const POSTMODERATED_STATUS = "postmoderation";
	public const FIRED_STATUS         = "fired";
	public const REJECTED_STATUS      = "rejected";
	public const DELETED_STATUS       = "deleted";

	/**
	 * Struct_User_Company constructor.
	 *
	 * @param int    $company_id
	 * @param string $client_company_id
	 * @param string $name
	 * @param string $status
	 * @param int    $avatar_color_id
	 * @param int    $created_by_user_id
	 * @param int    $member_count
	 * @param int    $guest_count
	 * @param int    $created_at
	 * @param int    $updated_at
	 * @param string $url
	 * @param int    $order
	 * @param string $avatar_file_map
	 * @param array  $data
	 */
	public function __construct(
		public int    $company_id,
		public string $client_company_id,
		public string $name,
		public string $status,
		public int    $avatar_color_id,
		public int    $created_by_user_id,
		public int    $member_count,
		public int    $guest_count,
		public int    $created_at,
		public int    $updated_at,
		public string $url,
		public int    $order,
		public string $avatar_file_map,
		public array  $data
	) {

	}

	/**
	 * Статический конструктор
	 *
	 * @throws \busException
	 * @throws cs_UserNotFound
	 * @throws \parseException
	 * @throws \userAccessException
	 *
	 * @long столбики
	 */
	public static function createFromCompanyStruct(Struct_Db_PivotCompany_Company $company, string $status, int $order, int $inviter_user_id = 0):self {

		$member_count      = Domain_Company_Entity_Company::getMemberCount($company->extra);
		$guest_count       = Domain_Company_Entity_Company::getGuestCount($company->extra);
		$client_company_id = Domain_Company_Entity_Company::getClientCompanyId($company->extra);

		$status = self::_getUserCompanyStatus($company, $status);

		$data = [];
		if ($inviter_user_id > 0) {

			$inviter_user_info               = Gateway_Bus_PivotCache::getUserInfo($inviter_user_id);
			$inviter_user_info               = Struct_User_Info::createStruct($inviter_user_info);
			$data["inviter_user_id"]         = $inviter_user_id;
			$data["inviter_full_name"]       = $inviter_user_info->full_name;
			$data["inviter_avatar_file_key"] = isEmptyString($inviter_user_info->avatar_file_map) ? "" : Type_Pack_File::doEncrypt($inviter_user_info->avatar_file_map);
			$data["inviter_avatar_color"]    = \BaseFrame\Domain\User\Avatar::getColorOutput($inviter_user_info->avatar_color_id);
		}

		return new self(
			$company->company_id,
			$client_company_id,
			$company->name,
			$status,
			$company->avatar_color_id,
			$company->created_by_user_id,
			$member_count,
			$guest_count,
			$company->created_at,
			$company->updated_at,
			$company->url,
			$order,
			$company->avatar_file_map,
			$data
		);
	}

	/**
	 * получаем статус компании для пользователя
	 */
	protected static function _getUserCompanyStatus(Struct_Db_PivotCompany_Company $company, string $status):string {

		if ($status == Struct_User_Company::ACTIVE_STATUS) {
			return Domain_Company_Entity_Company::SYSTEM_COMPANY_STATUS_SCHEMA[$company->status];
		}

		// !!! для кейса: если пользователь висит на постмодерации, но компания уже удалена
		if ($status == Struct_User_Company::POSTMODERATED_STATUS && $company->status == Domain_Company_Entity_Company::COMPANY_STATUS_DELETED) {
			return Struct_User_Company::REJECTED_STATUS;
		}

		return $status;
	}

	/**
	 * добавляем информацию по пользователю который одобрил заявку
	 *
	 * @param Struct_User_Company $company
	 * @param int                 $approved_user_id
	 *
	 * @return Struct_User_Company
	 * @throws \busException
	 * @throws cs_UserNotFound
	 * @throws \parseException
	 * @throws \userAccessException
	 */
	public static function addApprovedUserInfo(Struct_User_Company $company, int $approved_user_id):self {

		$approved_user_info                        = Gateway_Bus_PivotCache::getUserInfo($approved_user_id);
		$approved_user_info                        = Struct_User_Info::createStruct($approved_user_info);
		$company->data["approved_user_id"]         = $approved_user_info->user_id;
		$company->data["approved_full_name"]       = $approved_user_info->full_name;
		$company->data["approved_avatar_file_key"] = isEmptyString($approved_user_info->avatar_file_map) ? "" : Type_Pack_File::doEncrypt($approved_user_info->avatar_file_map);
		$company->data["approved_avatar_color"]    = \BaseFrame\Domain\User\Avatar::getColorOutput($approved_user_info->avatar_color_id);
		return $company;
	}
}
