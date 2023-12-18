<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Класс для форматирования сущностей под формат API
 *
 * в коде мы оперируем своими структурами и понятиями
 * к этому классу обращаемся строго отдачей результата в API
 * для форматирования стандартных сущностей
 *
 */
class Apiv2_Format {

	/**
	 * Получить ответ со статусом компании
	 *
	 * @throws ParseFatalException
	 */
	#[\JetBrains\PhpStorm\ArrayShape(["status_company" => "string"])]
	public static function companySystemStatus(int $company_system_status):array {

		if (!isset(Domain_Company_Entity_Company::SYSTEM_COMPANY_STATUS_SCHEMA[$company_system_status])) {
			throw new ParseFatalException("unknown company system type");
		}
		return [
			"status_company" => (string) Domain_Company_Entity_Company::SYSTEM_COMPANY_STATUS_SCHEMA[$company_system_status],
		];
	}

	/**
	 * формируем ответ для служебных данных клиента
	 */
	public static function formatStartData(array $start_data):array {

		$output = [];

		// отдаем конфиг эмоджи если есть
		$output["emoji_keywords_list"] = isset($start_data["emoji_keywords_list"]) ? (array) $start_data["emoji_keywords_list"] : [];

		// отдаем видео-онбординг для чата наймы и увольнения
		$output["onboarding_videos_list"] = isset($start_data["onboarding_videos_list"])
			? (array) $start_data["onboarding_videos_list"] : [];

		// отдаем конфиг приложения
		$output["app_config_list"] = isset($start_data["app_config_list"])
			? (array) $start_data["app_config_list"] : [];

		// отдаем список фич
		$output["feature_list"] = isset($start_data["feature_list"])
			? (array) $start_data["feature_list"] : [];

		return $output;
	}

	/**
	 * Форматируем тарифный план числа участников.
	 * Здесь есть немного логики, для привода наших значений к клиентским.
	 *
	 * @param \Tariff\Plan\MemberCount\MemberCount $member_count_plan
	 * @param int                                  $member_count
	 *
	 * @return array
	 */
	#[ArrayShape(["active_till" => "int", "limit" => "int", "current" => "int", "extend_policy" => "string", "allowed_action_list" => "array"])]
	public static function memberCountPlan(\Tariff\Plan\MemberCount\MemberCount $member_count_plan, int $member_count):array {

		// эту логику нужно держать в синхронизации с методом tariff/get пространства
		// --------------------------------------------------------------------------
		if (($member_count_plan->isActive(time()) && !$member_count_plan->isFree(time())) || ($member_count_plan->isTrial(time()) && $member_count_plan->getLimit() > 10)) {
			$allowed_action_list[] = "prolong";
		} elseif ((!$member_count_plan->isActive(time()) || $member_count_plan->isFree(time())) && !$member_count_plan->isTrial(time())) {
			$allowed_action_list[] = "activate";
		}

		if ($member_count_plan->isTrial(time()) || !$member_count_plan->isFree(time())) {
			$allowed_action_list[] = "change";
		}
		// --------------------------------------------------------------------------

		$active_till   = $member_count_plan->getActiveTill();
		$extend_policy = $member_count_plan->getExtendPolicyRule();

		if ($extend_policy === \Tariff\Plan\MemberCount\OptionExtendPolicy::TRIAL && $active_till < time()) {
			$extend_policy = \Tariff\Plan\MemberCount\OptionExtendPolicy::NEVER;
		}

		// а если бесплатный, но не триальный, то отдаем бесконечную длительность
		if ($member_count_plan->isFree(time()) && !$member_count_plan->isTrial(time())) {
			$active_till = 0;
		}

		return [
			"active_till"         => $active_till,
			"limit"               => $member_count_plan->getLimit(),
			"current"             => $member_count,
			"extend_policy"       => $extend_policy,
			"allowed_action_list" => $allowed_action_list,
		];
	}
}