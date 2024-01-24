<?php

namespace Compass\Pivot;

/**
 * форматирование сокет ответов
 */
class Socket_Format {

	/**
	 * @param Struct_User_Invite_Info[] $invite_list
	 *
	 */
	public static function inviteInfoList(array $invite_list):array {

		$output = [];
		foreach ($invite_list as $invite) {

			$output[] = [
				"phone_number" => (string) $invite->phone_number,
				"invite_id"    => (int) $invite->invite_id,
				"type"         => (int) $invite->type,
				"full_name"    => (string) $invite->full_name,
			];
		}
		return $output;
	}

	/**
	 * Форматирует список структур последней регистрации.
	 */
	public static function lastRegistrationList(array $last_registration_list):array {

		return array_map(static fn(Struct_Db_PivotData_LastRegisteredUser $el) => static::lastRegistration($el), $last_registration_list);
	}

	/**
	 * Форматирует структуру последней регистрации.
	 */
	#[\JetBrains\PhpStorm\ArrayShape(["user_id" => "int", "registered_at" => "int", "ip_address" => "string", "autonomous_system_name" => "string", "autonomous_system_code" => "string", "autonomous_system_country_code" => "string"])]
	public static function lastRegistration(Struct_Db_PivotData_LastRegisteredUser $last_registration):array {

		[$ip_address, $as_name, $as_code, $as_country_code] = Domain_User_Entity_RegistrationExtra::get($last_registration->extra);

		return [
			"user_id"                        => $last_registration->user_id,
			"registered_at"                  => $last_registration->created_at,
			"ip_address"                     => (string) $ip_address,
			"autonomous_system_name"         => (string) $as_name,
			"autonomous_system_code"         => (string) $as_code,
			"autonomous_system_country_code" => (string) $as_country_code,
		];
	}

	/**
	 * Форматирует структуру пользователя
	 */
	public static function userInfo(Struct_Db_PivotUser_User $user_info, string $avatar_url):array {

		return [
			"user_id"       => (int) $user_info->user_id,
			"is_disabled"   => (int) Type_User_Main::isDisabledProfile($user_info->extra),
			"full_name"     => (string) $user_info->full_name,
			"avatar_link"   => (string) $avatar_url,
			"registered_at" => (int) $user_info->created_at,
		];
	}

	/**
	 * Форматирует структуру пространства
	 */
	public static function spaceInfo(Struct_Db_PivotCompany_Company $space_info, Domain_SpaceTariff_Tariff $space_tariff):array {

		return [
			"space_id"         => (int) $space_info->company_id,
			"space_name"       => (string) $space_info->name,
			"space_size"       => (int) $space_tariff->memberCount()->getLimit(),
			"status"           => (int) $space_info->status,
			"count_member"     => (int) Domain_Company_Entity_Company::getMemberCount($space_info->extra),
			"tariff_type"      => (int) $space_tariff->memberCount()->getData()->plan_type,
			"tariff_expire"    => (int) $space_tariff->memberCount()->getActiveTill(),
			"tariff_status"    => (string) self::_getTariffStatus($space_info, $space_tariff),
			"owner_user_id"    => (int) $space_info->created_by_user_id,
			"space_created_at" => (int) $space_info->created_at,
		];
	}

	/**
	 * Получаем статус тарифа
	 *
	 * @param Struct_Db_PivotCompany_Company $space_info
	 * @param Domain_SpaceTariff_Tariff      $space_tariff
	 *
	 * @return string
	 */
	protected static function _getTariffStatus(Struct_Db_PivotCompany_Company $space_info, Domain_SpaceTariff_Tariff $space_tariff):string {

		$member_count_plan_status = "inactive";

		// если компания удалена, то возвращаем сразу inactive не проверяя тариф
		if ($space_info->status === Domain_Company_Entity_Company::COMPANY_STATUS_DELETED) {
			return $member_count_plan_status;
		}

		// иначе идем проверять тариф
		if ($space_tariff->memberCount()->isTrialAvailable()) {

			// по умолчанию (ни триала, ни оплат не было)
			$member_count_plan_status = "default";
		} elseif ($space_tariff->memberCount()->isTrial(time())) {

			// в пробном периоде
			$member_count_plan_status = "trial";
		} elseif ($space_tariff->memberCount()->isActive(time()) && !$space_tariff->memberCount()->isFree(time())) {

			// активный оплаченный
			$member_count_plan_status = "active";
		} elseif ($space_tariff->memberCount()->isFree(time())) {

			// активен как бесплатный
			$member_count_plan_status = "free";
		}

		return $member_count_plan_status;
	}
}