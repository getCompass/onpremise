<?php

namespace Compass\Premise;

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
	 */
	public static function createFromCompanyStruct(Struct_Db_PivotCompany_Company $company, string $status, int $order):self {

		$member_count      = Domain_Company_Entity_Company::getMemberCount($company->extra);
		$guest_count       = Domain_Company_Entity_Company::getGuestCount($company->extra);
		$client_company_id = Domain_Company_Entity_Company::getClientCompanyId($company->extra);

		$status = self::_getUserCompanyStatus($company, $status);

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
			[]
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
}
