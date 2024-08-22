<?php

namespace Compass\Company;

use BaseFrame\Server\ServerProvider;

/**
 * Класс для создания обычных ссылок-инвайтов
 */
class Domain_JoinLink_Action_Create_Regular extends Domain_JoinLink_Action_Create_Default {

	protected const _JOIN_LINK_TYPE = Domain_JoinLink_Entity_Main::TYPE_REGULAR;

	protected const _CAN_USE_COUNT_SAAS      = 50;  // количество использований ссылки (дефолтное значение на saas)
	protected const _CAN_USE_COUNT_ONPREMISE = 500; // количество использований ссылки (дефолтное значение на onpremise)
	protected const _LIVES_DAY_COUNT         = 7;   // сколько по времени в днях действует ссылка
	protected const _ENTRY_OPTION            = Domain_JoinLink_Entity_Main::ENTRY_OPTION_JOIN_AS_MEMBER; // по умолчанию вступать "Сразу как участник"

	/**
	 * выполняем
	 *
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws cs_ExceededCountActiveInvite
	 */
	public static function do(int $creator_user_id, int|false $lives_day_count, int|false $lives_hour_count, int|false $can_use_count, int|bool $entry_option, bool $ignore_limit = false):Struct_Db_CompanyData_JoinLink {

		$live_time = self::_getLiveTime($lives_day_count, $lives_hour_count);

		if ($entry_option === false) {
			$entry_option = self::_ENTRY_OPTION;
		}

		if ($can_use_count === false) {

			$can_use_count = self::_CAN_USE_COUNT_SAAS;
			if (ServerProvider::isOnPremise()) {
				$can_use_count = self::_CAN_USE_COUNT_ONPREMISE;
			}
		}

		// если не требуется игнорировать лимит кол-ва ссылок, то проверяем достижения лимита
		if (!$ignore_limit) {

			$count = Gateway_Db_CompanyData_JoinLinkList::getCountByTypeAndStatus(
				[Domain_JoinLink_Entity_Main::TYPE_REGULAR, Domain_JoinLink_Entity_Main::TYPE_SINGLE],
				Domain_JoinLink_Entity_Main::STATUS_ACTIVE, time());
			if ($count >= Domain_JoinLink_Entity_Main::getRegularMaxCount()) {
				throw new cs_ExceededCountActiveInvite();
			}
		}

		return self::_do($creator_user_id, self::_JOIN_LINK_TYPE, $live_time, $can_use_count, $entry_option);
	}

	/**
	 * получаем время жизни для ссылки
	 */
	protected static function _getLiveTime(int|false $lives_day_count, int|false $lives_hour_count):int {

		// если нужно отдать значение по умолчанию
		if ($lives_day_count === false && $lives_hour_count === false) {
			return Domain_JoinLink_Entity_Main::getLiveTimeByDayCount(self::_LIVES_DAY_COUNT);
		}

		// если передано количество дней жизни
		if ($lives_day_count !== false) {

			// если ссылка не должна протухать по времени
			if ($lives_day_count === 0) {
				return $lives_day_count;
			}

			return Domain_JoinLink_Entity_Main::getLiveTimeByDayCount($lives_day_count);
		}

		// если передано количество часов жизни
		if ($lives_hour_count !== false) {
			return Domain_JoinLink_Entity_Main::getLiveTimeByHourCount($lives_hour_count);
		}

		return 0;
	}
}
