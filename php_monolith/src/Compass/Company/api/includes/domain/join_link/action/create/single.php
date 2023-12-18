<?php

namespace Compass\Company;

/**
 * Класс для создания разовых ссылок-инвайтов
 */
class Domain_JoinLink_Action_Create_Single extends Domain_JoinLink_Action_Create_Default {

	protected const _JOIN_LINK_TYPE = Domain_JoinLink_Entity_Main::TYPE_SINGLE;

	protected const _ENTRY_OPTION           = Domain_JoinLink_Entity_Main::ENTRY_OPTION_JOIN_AS_MEMBER; // вступаем сразу как участник
	protected const _CAN_USE_COUNT          = 1;     // количество использований ссылки
	protected const _LIVES_DAY_COUNT_LEGACY = 14;    // сколько по времени в днях действует ссылка (старая версия)
	protected const _LIVES_DAY_COUNT        = 7;     // сколько по времени в днях действует ссылка

	/**
	 * выполняем
	 *
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws cs_ExceededCountActiveInvite
	 */
	public static function do(int $creator_user_id, int|false $lives_day_count, int|false $lives_hour_count, int $method_version, int $entry_option = self::_ENTRY_OPTION):Struct_Db_CompanyData_JoinLink {

		$live_time = self::_getLiveTime($lives_day_count, $lives_hour_count, $method_version);

		$count = Gateway_Db_CompanyData_JoinLinkList::getCountByTypeAndStatus([Domain_JoinLink_Entity_Main::TYPE_SINGLE, Domain_JoinLink_Entity_Main::TYPE_REGULAR],
			Domain_JoinLink_Entity_Main::STATUS_ACTIVE, time());
		if ($count >= Domain_JoinLink_Entity_Main::getRegularMaxCount()) {
			throw new cs_ExceededCountActiveInvite();
		}

		return self::_do($creator_user_id, self::_JOIN_LINK_TYPE, $live_time, self::_CAN_USE_COUNT, $entry_option);
	}

	/**
	 * получаем время жизни для ссылки
	 */
	protected static function _getLiveTime(int|false $lives_day_count, int|false $lives_hour_count, int $method_version):int {

		// если нужно отдать значение по умолчанию
		if ($lives_day_count === false && $lives_hour_count === false) {

			if ($method_version == METHOD_VERSION_2) {
				return Domain_JoinLink_Entity_Main::getLiveTimeByDayCount(self::_LIVES_DAY_COUNT_LEGACY);
			} else {
				return Domain_JoinLink_Entity_Main::getLiveTimeByDayCount(self::_LIVES_DAY_COUNT);
			}
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
