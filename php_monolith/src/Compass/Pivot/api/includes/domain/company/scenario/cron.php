<?php

namespace Compass\Pivot;

/**
 * Сценарии для кронов.
 */
class Domain_Company_Scenario_Cron {

	protected const _OBSERVE_MAX_LIMIT = 1000;

	/**
	 * Выполняет observe рангов компаний
	 *
	 * @param int $cron_exec_at
	 *
	 * @throws \Exception
	 * @long
	 */
	public static function tierObserve(int $cron_exec_at):void {

		foreach (range(1, 10_000_000, 1_000_000) as $shard_company_id) {

			$limit  = static::_OBSERVE_MAX_LIMIT;
			$offset = 0;

			do {

				// получаем записи, если есть
				$observe_list = self::_getListForObserve($shard_company_id, $cron_exec_at, $limit, $offset);
				if (count($observe_list) < 1) {
					break;
				}

				// получаем информацию по компаниям
				$company_id_list    = array_column($observe_list, "company_id");
				$assoc_company_list = Gateway_Db_PivotCompany_CompanyList::getList($company_id_list, true);

				$ready_to_relocate_list = [];
				$need_relocate_list     = [];
				foreach ($observe_list as $observe_item) {

					// если компания переезжает, то не обзервим
					if (Domain_Company_Entity_Tier::getIsRelocating($observe_item->extra) == 1) {
						continue;
					}

					// если компания уже не активная (начала засыпать после взятия записей)
					$company = $assoc_company_list[$observe_item->company_id];
					if ($company->status != Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE) {
						continue;
					}

					// получаем статистику компании, которая нужна для определения ранга
					$member_activity_count_list = self::_getCompanyStats($observe_item, $company);

					// чекаем под какой тир подходит и добавляем в нужный массив
					$expected_tier = Domain_Company_Entity_Tier::getExpectedCompanyTier($member_activity_count_list);
					[$ready_to_relocate_list, $need_relocate_list] = self::_addToRelocateList($ready_to_relocate_list, $need_relocate_list,
						$observe_item, $expected_tier);
				}

				// работаем с компаниями, у которых изменился ранг
				self::_workWithReadyToRelocateList($ready_to_relocate_list);

				// работаем с компаниями, которые пора перевозить
				self::_workWithNeedRelocateList($need_relocate_list);

				$offset += $limit;
			} while (count($observe_list) >= $limit);
		}
	}

	/**
	 * получаем записи на обновление
	 *
	 * @param int $shard_company_id
	 * @param int $cron_exec_at
	 * @param int $limit
	 * @param int $offset
	 *
	 * @return Struct_Db_PivotCompany_CompanyTierObserve[]
	 * @throws \Exception
	 */
	protected static function _getListForObserve(int $shard_company_id, int $cron_exec_at, int $limit, int $offset):array {

		// обзервим записи, если есть
		$observe_list = Gateway_Db_PivotCompany_CompanyTierObserve::getForObserve($shard_company_id, $cron_exec_at, $limit, $offset);
		if (count($observe_list) < 1) {
			return $observe_list;
		}

		// сдвигаем need_work
		$company_id_list = array_column($observe_list, "company_id");
		$need_work       = time() + DAY1 + random_int(0, 3600); // делаем random_int до часу, чтобы все компании не брались разом, а размазывались
		Gateway_Db_PivotCompany_CompanyTierObserve::updateNeedWorkList($shard_company_id, $company_id_list, $need_work);

		return $observe_list;
	}

	/**
	 * получаем информацию по компании, которая нужна для определения ранга
	 *
	 * @param Struct_Db_PivotCompany_CompanyTierObserve $observe_item
	 *
	 * @return array
	 */
	protected static function _getCompanyStats(Struct_Db_PivotCompany_CompanyTierObserve $observe_item, Struct_Db_PivotCompany_Company $company):array {

		// получаем количество активных пользователей за последние 7 дней
		$to_date              = dayStart(); // сегодняшний день не считаем, т.к валидные цифры будут только по итогам дня
		$from_date            = $to_date - DAY7; // берем за последнюю неделю
		$private_key          = Domain_Company_Entity_Company::getPrivateKey($company->extra);
		$member_activity_list = Gateway_Socket_Company::getMemberActivityCountList(
			$observe_item->company_id, $from_date, $to_date, $company->domino_id, $private_key);

		// наполняем ответ за каждый день датой
		$output = [];
		for ($temp_date = $from_date; $temp_date < $to_date; $temp_date += DAY1) {
			$output[] = $member_activity_list[$temp_date] ?? 0;
		}
		return $output;
	}

	/**
	 * добавляем компанию в список релокации
	 *
	 * @param array                                     $ready_to_relocate_list
	 * @param array                                     $need_relocate_list
	 * @param Struct_Db_PivotCompany_CompanyTierObserve $observe_item
	 * @param int                                       $expected_tier
	 *
	 * @return array
	 */
	protected static function _addToRelocateList(array $ready_to_relocate_list, array $need_relocate_list, Struct_Db_PivotCompany_CompanyTierObserve $observe_item, int $expected_tier):array {

		// если никуда не нужно перевозить, то ничего не делаем
		if ($observe_item->expected_domino_tier < 1 && $observe_item->current_domino_tier == $expected_tier) {
			return [$ready_to_relocate_list, $need_relocate_list];
		}

		// если хотели перевезти, но компания перестала быть достойной
		// при этом учитываем флаг is_relocating, на случай если компания уже/еще переезжает
		if ($observe_item->expected_domino_tier > 0 && $observe_item->current_domino_tier == $expected_tier
			&& Domain_Company_Entity_Tier::getIsRelocating($observe_item->extra) != 1) {

			Domain_Company_Entity_Tier::markRelocatingCompleted($observe_item, $observe_item->current_domino_tier);
			return [$ready_to_relocate_list, $need_relocate_list];
		}

		// если tier не изменился
		if ($observe_item->expected_domino_tier == $expected_tier) {

			// если пришло время делать релокацию и процесс еще не начался
			$extra              = $observe_item->extra;
			$can_relocate_after = Domain_Company_Entity_Tier::getCanRelocateAfter($extra);
			if ($can_relocate_after != 0 && $can_relocate_after < time() && Domain_Company_Entity_Tier::getIsRelocating($extra) != 1) {
				$need_relocate_list[] = $observe_item;
			}

			return [$ready_to_relocate_list, $need_relocate_list];
		}

		// если tier изменился - отправляем на подготовку к релокации
		$observe_item->expected_domino_tier = $expected_tier;
		$observe_item->extra                = Domain_Company_Entity_Tier::setCanRelocateAfter($observe_item->extra, time() + DAY2);
		$ready_to_relocate_list[]           = $observe_item;

		return [$ready_to_relocate_list, $need_relocate_list];
	}

	/**
	 * работаем с компаниями, у которых изменился ранг
	 *
	 * @param Struct_Db_PivotCompany_CompanyTierObserve[] $ready_to_relocate_list
	 *
	 * @return void
	 */
	protected static function _workWithReadyToRelocateList(array $ready_to_relocate_list):void {

		if (count($ready_to_relocate_list) < 1) {
			return;
		}

		// идем по каждой компании, которая изменила ранг
		foreach ($ready_to_relocate_list as $observe_item) {

			// шлем нотис и оборачиваем в try catch, чтобы ничего не сломать
			try {

				$company_id         = $observe_item->company_id;
				$expected_tier      = $observe_item->expected_domino_tier;
				$can_relocate_after = date(DATE_FORMAT_FULL, Domain_Company_Entity_Tier::getCanRelocateAfter($observe_item->extra));
				$text               = self::_getTextForReadyToRelocateMessage($company_id, $observe_item->current_domino_tier, $expected_tier,
					$can_relocate_after, false);
				Domain_Company_Entity_Tier::sendNotice($text);
				$observe_item->extra = Domain_Company_Entity_Tier::setIsSentNotice($observe_item->extra, 1);

				// помечаем в таблице, когда компания будет готова к релокации
				$set = [
					"expected_domino_tier" => $observe_item->expected_domino_tier,
					"extra"                => $observe_item->extra,
				];
				Gateway_Db_PivotCompany_CompanyTierObserve::set($observe_item->company_id, $set);
			} catch (\Throwable $e) {

				Type_System_Admin::log("compass_notice", [
					"result"  => "unsuccessful request while sending notice about company tier",
					"message" => formatArgs($e->getMessage()),
				]);
			}
		}
	}

	/**
	 * работаем с компаниями, которые пора перевозить
	 *
	 * @param Struct_Db_PivotCompany_CompanyTierObserve[] $need_relocate_list
	 *
	 * @return void
	 */
	protected static function _workWithNeedRelocateList(array $need_relocate_list):void {

		if (count($need_relocate_list) < 1) {
			return;
		}

		// идем по каждой компании, для которой нужно начать процесс релокации
		foreach ($need_relocate_list as $observe_item) {

			// шлем нотис и оборачиваем в try catch, чтобы ничего не сломать
			try {

				$company_id    = $observe_item->company_id;
				$expected_tier = $observe_item->expected_domino_tier;
				$text          = self::_getTextForReadyToRelocateMessage($company_id, $observe_item->current_domino_tier, $expected_tier,
					0, true);
				Domain_Company_Entity_Tier::sendNotice($text);
			} catch (\Throwable $e) {

				Type_System_Admin::log("compass_notice", [
					"result"  => "unsuccessful request while sending notice about company tier",
					"message" => formatArgs($e->getMessage()),
				]);
			}
		}
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * получаем основной текст для нотиса
	 *
	 * @param int    $company_id
	 * @param int    $current_tier
	 * @param int    $expected_tier
	 * @param string $can_relocate_after
	 * @param bool   $is_ready_to_start
	 *
	 * @return string
	 */
	protected static function _getTextForReadyToRelocateMessage(int $company_id, int $current_tier, int $expected_tier, string $can_relocate_after, bool $is_ready_to_start):string {

		// формируем текст для нотиса
		$tier_state = $expected_tier > $current_tier ? "повышения" : "понижения";
		$text       = "Компания {$company_id} достигла {$tier_state} ранга с {$current_tier} на {$expected_tier}";
		$text       .= $is_ready_to_start ? ", пора запускать процесс переезда :rocket:" : ", нужно перевезти после {$can_relocate_after}";

		return $text;
	}
}
