<?php

namespace Compass\Pivot;

/**
 * Класс для взаимодействия с рангами компаний
 */
class Domain_Company_Entity_Tier {

	protected const _EXTRA_VERSION = 1; // версия упаковщика
	protected const _EXTRA_SCHEMA  = [  // схема extra

		1 => [
			"can_relocate_after" => 0, // время после которого можно начинать процесс релокации
			"is_sent_notice"     => 0, // 1 если отправлено уведомление о том, что компанию через 2 дня можно перевезти
			"is_relocating"      => 0, // 1 если компания начала процесс релокации
			"relocating_task_id" => 0, // id таска текущего шага переезда
		],
	];

	/**
	 * Создать новую структуру для extra
	 *
	 * @return array
	 */
	public static function initExtra():array {

		return [
			"version" => self::_EXTRA_VERSION,
			"extra"   => self::_EXTRA_SCHEMA[self::_EXTRA_VERSION],
		];
	}

	/**
	 * Устанавливаем после какого времени можно начинать процесс релокации
	 *
	 * @param array $extra
	 * @param int   $can_relocate_after
	 *
	 * @return array
	 */
	public static function setCanRelocateAfter(array $extra, int $can_relocate_after):array {

		$extra                                = self::_getExtra($extra);
		$extra["extra"]["can_relocate_after"] = $can_relocate_after;

		return $extra;
	}

	/**
	 * Получаем После какого времени можно начинать процесс релокации
	 *
	 * @param array $extra
	 *
	 * @return int
	 */
	public static function getCanRelocateAfter(array $extra):int {

		$extra = self::_getExtra($extra);

		return $extra["extra"]["can_relocate_after"];
	}

	/**
	 *  Добавляем is_sent_notice в extra
	 *
	 * @param array $extra
	 * @param int   $is_sent_notice
	 *
	 * @return array
	 */
	public static function setIsSentNotice(array $extra, int $is_sent_notice):array {

		$extra                            = self::_getExtra($extra);
		$extra["extra"]["is_sent_notice"] = $is_sent_notice;

		return $extra;
	}

	/**
	 * Получаем is_sent_notice из extra
	 *
	 * @param array $extra
	 *
	 * @return int
	 */
	public static function getIsSentNotice(array $extra):int {

		$extra = self::_getExtra($extra);

		return $extra["extra"]["is_sent_notice"];
	}

	/**
	 *  Добавляем is_relocating в extra
	 *
	 * @param array $extra
	 * @param int   $is_relocating
	 *
	 * @return array
	 */
	public static function setIsRelocating(array $extra, int $is_relocating):array {

		$extra                           = self::_getExtra($extra);
		$extra["extra"]["is_relocating"] = $is_relocating;

		return $extra;
	}

	/**
	 * Получаем is_relocating из extra
	 *
	 * @param array $extra
	 *
	 * @return int
	 */
	public static function getIsRelocating(array $extra):int {

		$extra = self::_getExtra($extra);

		return $extra["extra"]["is_relocating"];
	}

	/**
	 *  Добавляем relocating_task_id в extra
	 *
	 * @param array $extra
	 * @param int   $relocating_task_id
	 *
	 * @return array
	 */
	public static function setRelocatingTaskId(array $extra, int $relocating_task_id):array {

		$extra                                = self::_getExtra($extra);
		$extra["extra"]["relocating_task_id"] = $relocating_task_id;

		return $extra;
	}

	/**
	 * Получаем relocating_task_id из extra
	 *
	 * @param array $extra
	 *
	 * @return int
	 */
	public static function getRelocatingTaskId(array $extra):int {

		$extra = self::_getExtra($extra);

		return $extra["extra"]["relocating_task_id"];
	}

	// получаем tier компании, на котором она должна быть
	public static function getExpectedCompanyTier(array $member_activity_count_list):int {

		// пробегаем по статистике каждого дня
		$min_active_user_count = $member_activity_count_list[0] ?? 0; // минимальное количество активных пользователей, берем первый элемент массива за основу
		foreach ($member_activity_count_list as $day_member_active_count) {

			// записываем минимальное значение
			if ($day_member_active_count < $min_active_user_count) {
				$min_active_user_count = $day_member_active_count;
			}
		}

		// пробегаем по всем конфигам
		$output_tier = DOMINO_TIER_1; // по дефолту tier = 1 у всех
		foreach (AVAILABLE_DOMINO_TIER_CONFIG_LIST as $tier => $need_min_active_user_count) {

			// если прошли по требованиям
			if ($min_active_user_count >= $need_min_active_user_count) {
				$output_tier = $tier;
			}
		}

		return $output_tier;
	}

	// помечаем релокацию начатой
	public static function markRelocatingStarted(Struct_Db_PivotCompany_CompanyTierObserve $company_tier, int $relocating_task_id):void {

		// почаем в extra, что завершился процесс переезда
		$company_tier->extra = self::setIsRelocating($company_tier->extra, 1);
		$company_tier->extra = self::setRelocatingTaskId($company_tier->extra, $relocating_task_id);
		Gateway_Db_PivotCompany_CompanyTierObserve::set($company_tier->company_id, ["extra" => $company_tier->extra]);
	}

	// помечаем релокацию завершенной
	public static function markRelocatingCompleted(Struct_Db_PivotCompany_CompanyTierObserve $company_tier, int $current_domino_tier):void {

		// если завершили процесс переезда - уведомляем в чатик
		$before_domino_tier = $company_tier->current_domino_tier;
		if ($company_tier->expected_domino_tier > 0 && $current_domino_tier != $before_domino_tier
			&& Domain_Company_Entity_Tier::getIsRelocating($company_tier->extra) == 1) {

			$text = ":large_green_circle: Завершили переезд компании {$company_tier->company_id} с ранга {$before_domino_tier} на {$current_domino_tier} ранг";
			Domain_Company_Entity_Tier::sendNotice($text);
		}

		// почаем в extra, что завершился процесс переезда
		$company_tier->extra = self::setIsRelocating($company_tier->extra, 0);
		$company_tier->extra = self::setRelocatingTaskId($company_tier->extra, 0);
		$company_tier->extra = self::setIsSentNotice($company_tier->extra, 0);
		$company_tier->extra = self::setCanRelocateAfter($company_tier->extra, 0);

		$set = [
			"current_domino_tier"  => $current_domino_tier,
			"expected_domino_tier" => 0,
			"extra"                => $company_tier->extra,
		];
		Gateway_Db_PivotCompany_CompanyTierObserve::set($company_tier->company_id, $set);
	}

	// помечаем следующий этап релокации
	public static function setRelocationNextStep(Struct_Db_PivotCompany_CompanyTierObserve $company_tier, int $task_id):void {

		// почаем в extra следующий шаг релокации
		$company_tier->extra = self::setRelocatingTaskId($company_tier->extra, $task_id);

		$set = [
			"extra" => $company_tier->extra,
		];
		Gateway_Db_PivotCompany_CompanyTierObserve::set($company_tier->company_id, $set);
	}

	/**
	 * отправляем уведомление в compass
	 */
	public static function sendNotice(string $text):void {

		// если не заданы константы, то не отправляем!
		if (mb_strlen(COMPANY_TIER_COMPASS_NOTICE_PROJECT) < 1
			|| mb_strlen(COMPANY_TIER_COMPASS_NOTICE_TOKEN) < 1
			|| mb_strlen(COMPANY_TIER_COMPASS_NOTICE_GROUP_ID) < 1) {
			return;
		}

		Type_Notice_Compass::sendGroup(
			COMPANY_TIER_COMPASS_NOTICE_PROJECT,
			COMPANY_TIER_COMPASS_NOTICE_TOKEN,
			COMPANY_TIER_COMPASS_NOTICE_GROUP_ID,
			$text
		);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Получить актуальную структуру для extra
	 *
	 * @param array $extra
	 *
	 * @return array
	 */
	protected static function _getExtra(array $extra):array {

		// если версия не совпадает - дополняем её до текущей
		if ($extra["version"] != self::_EXTRA_VERSION) {

			$extra["extra"]   = array_merge(self::_EXTRA_SCHEMA[self::_EXTRA_VERSION], $extra["extra"]);
			$extra["version"] = self::_EXTRA_VERSION;
		}

		return $extra;
	}
}
