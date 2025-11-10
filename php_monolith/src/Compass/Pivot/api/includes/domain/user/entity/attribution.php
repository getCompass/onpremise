<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\DBShardingNotFoundException;
use BaseFrame\Exception\Gateway\QueryFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;
use BaseFrame\Server\ServerProvider;

/**
 * класс для работы с атрибуцией регистрации пользователя в приложении
 */
class Domain_User_Entity_Attribution {

	/** @var float|int максимально допустимый offset в часовых поясах в секундах
	 * https://stackoverflow.com/questions/8131023/what-is-the-maximum-possible-time-zone-difference
	 */
	protected const _MAX_TIMEZONE_UTC_OFFSET_SEC = 26 * HOUR1;

	/** @var int signed int32 max value */
	protected const _MAX_SCREEN_SIDE_SIZE = 2147483647;

	/** @var string ключ по которому данные о цифровой подписи посещения отправляется в php-collector-server */
	protected const _LANDING_VISIT_COLLECTOR_AGENT_KEY = "attribution_landing_visit_log";

	/** @var string ключ по которому данные о цифровой подписи регистрации отправляется в php-collector-server */
	protected const _APP_REGISTRATION_COLLECTOR_AGENT_KEY = "attribution_app_registration_log";

	/** @var string ключ по которому данные о связи пользователь -> посещение отправляются в php-collector-server */
	protected const _USER_VISIT_REL_COLLECTOR_AGENT_KEY = "attribution_user_campaign_rel";

	/** @var int спустя какое время сырые данные атрибуции (цифр. подпись посещения, регистрации) должны удаляться */
	protected const _ATTRIBUTION_RAW_DATA_TIME_LIFE = DAY7;

	/** @var int лимит кол-ва записей удаляемых за раз */
	protected const _DELETE_EXPIRED_LIMIT = 1000;

	/**
	 * проверяем, корректен ли сдвиг часового пояса относительно UTC в секундах
	 *
	 * @param int $timezone_utc_offset
	 *
	 * @return bool
	 */
	public static function isCorrectTimezoneUtcOffset(int $timezone_utc_offset):bool {

		// ЗЫ может быть отрицательным!

		// не превышает максимум (используем модуль потому что – ЗЫ может быть отрицательным!)
		if (abs($timezone_utc_offset) > self::_MAX_TIMEZONE_UTC_OFFSET_SEC) {
			return false;
		}

		return true;
	}

	/**
	 * проверяем, корректен ли размер стороны экрана
	 *
	 * @param int $screen_side_size
	 *
	 * @return bool
	 */
	public static function isCorrectScreenSideSize(int $screen_side_size):bool {

		// не ортрицательное значение
		if ($screen_side_size < 0) {
			return false;
		}

		// не превышает максимум
		if ($screen_side_size > self::_MAX_SCREEN_SIDE_SIZE) {
			return false;
		}

		return true;
	}

	/**
	 * отправляем в коллектор параметры регистрации пользователя
	 */
	public static function saveUserAppRegistrationLog(
		int    $user_id,
		string $ip_address,
		string $platform,
		string $platform_os,
		int    $timezone_utc_offset,
		int    $screen_avail_width,
		int    $screen_avail_height,
		int    $registered_at,
	):Struct_Db_PivotAttribution_UserAppRegistration {

		$user_app_registration = new Struct_Db_PivotAttribution_UserAppRegistration(
			$user_id, $ip_address, $platform, $platform_os, $timezone_utc_offset, $screen_avail_width, $screen_avail_height, $registered_at, time()
		);

		try {

			Gateway_Db_PivotAttribution_UserAppRegistrationLog::insert($user_app_registration);

			// оптравляем данные в php-collector-server
			Gateway_Bus_CollectorAgent::init()->add(self::_APP_REGISTRATION_COLLECTOR_AGENT_KEY, (array) $user_app_registration);
		} catch (cs_RowDuplication) {

			// если словили дубликат, то получаем существующую запись из базы
			$user_app_registration = Gateway_Db_PivotAttribution_UserAppRegistrationLog::get($user_id);
		}

		return $user_app_registration;
	}

	/**
	 * Сохраняем цифровой отпечаток посещения лендинга
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \queryException
	 */
	public static function saveLandingVisit(string $guest_id, string $link, string $utm_tag, string $source_id, string $ip_address, string $platform, string $platform_os, int $timezone_utc_offset, int $screen_avail_width, int $screen_avail_height, int $visited_at):void {

		$langing_visit = new Struct_Db_PivotAttribution_LandingVisit(
			generateUUID(),
			$guest_id,
			$link,
			$utm_tag,
			$source_id,
			$ip_address,
			$platform,
			$platform_os,
			$timezone_utc_offset,
			$screen_avail_width,
			$screen_avail_height,
			$visited_at,
			time()
		);
		Gateway_Db_PivotAttribution_LandingVisitLog::insert($langing_visit);

		// оптравляем данные в php-collector-server
		Gateway_Bus_CollectorAgent::init()->add(self::_LANDING_VISIT_COLLECTOR_AGENT_KEY, (array) $langing_visit);

		// если это посещение join-страницы, то инкрементим кол-во посещений
		if (Domain_Company_Entity_JoinLink_Main::isJoinLink($link)) {
			Gateway_Bus_CollectorAgent::init()->inc("row78");
		}
	}

	/**
	 * сохраняем связь user_id <-> visit_id
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \queryException
	 */
	public static function saveUserVisitRel(Struct_Db_PivotAttribution_UserAppRegistration $user_app_registration_attr, Struct_Db_PivotAttribution_LandingVisit $matched_visit):void {

		$user_visit_rel = new Struct_Db_PivotAttribution_UserCampaignRel(
			$user_app_registration_attr->user_id,
			$matched_visit->visit_id,
			$matched_visit->utm_tag,
			$matched_visit->source_id,
			$matched_visit->link,
			true,
			time()
		);
		Gateway_Db_PivotAttribution_UserCampaignRel::insert($user_visit_rel);

		// оптравляем данные в php-collector-server
		Gateway_Bus_CollectorAgent::init()->add(self::_USER_VISIT_REL_COLLECTOR_AGENT_KEY, (array) $user_visit_rel);
	}

	/**
	 * Проверяем, что ip-адресс корректен
	 */
	public static function assertIpAddress(string $ip_address):void {

		// на тестовом окружении опускаем все проверки
		if (ServerProvider::isTest()) {
			return;
		}

		// список локальных подсетей
		$local_network_list = [
			"10.*.*.*", "172.16.*.*", "172.17.*.*", "172.18.*.*", "172.19.*.*", "172.20.*.*", "172.21.*.*", "172.22.*.*", "172.23.*.*", "172.24.*.*", "172.25.*.*",
			"172.26.*.*", "172.27.*.*", "172.28.*.*", "172.29.*.*", "172.30.*.*", "172.31.*.*", "192.168.*.*",
		];

		// проверяем, что IP адрес не относится к локальной подсети
		foreach ($local_network_list as $local_network) {

			if (isIpEqual($ip_address, $local_network)) {
				throw new Domain_User_Exception_Attribution_LocalNetworkIpAddress();
			}
		}
	}

	/**
	 * Получаем данные об источнике который привел пользователя к регистрации в приложении
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getUserSourceData(int $user_id):array {

		// ответ по-умолчанию
		$source_type  = "user_organic";
		$source_extra = [];

		// получаем связь user_id -> visit_id
		try {
			$user_campaign_rel = Gateway_Db_PivotAttribution_UserCampaignRel::get($user_id);
		} catch (RowNotFoundException) {
			return [$source_type, $source_extra];
		}

		$source_type  = "user_landing";
		$source_extra = [
			"visit_url" => $user_campaign_rel->link,
		];

		return [$source_type, $source_extra];
	}

	/**
	 * Получаем данные о связе user_id -> visit_id
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getUserCampaignRelData(int $user_id):array {

		// ответ по умолчанию
		$link          = "";
		$source_id     = "";
		$is_direct_reg = 0;

		// пытаемся получить связь с рекламной кампанией
		try {
			$user_campaign_rel = Gateway_Db_PivotAttribution_UserCampaignRel::get($user_id);
		} catch (RowNotFoundException) {

			// если ничего не нашли, то возвращаем ответ по умолчанию
			return [$link, $source_id, $is_direct_reg];
		}

		return [$user_campaign_rel->link, $user_campaign_rel->source_id, $user_campaign_rel->is_direct_reg];
	}

	/**
	 * Метод для очистки устаревших данных
	 */
	public static function clearOldData():void {

		self::_clearOldDataLandingVisitLog();
		self::_clearOldDataUserAppRegistrationLog();
	}

	/**
	 * Очищаем старые данные в таблице landing_visit_log
	 * @return void
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 * @throws ParseFatalException
	 */
	protected static function _clearOldDataLandingVisitLog():void {

		$min_created_at   = time() - self::_ATTRIBUTION_RAW_DATA_TIME_LIFE;
		$is_need_optimize = false;
		do {

			// очищаем таблицу
			$deleted_count = Gateway_Db_PivotAttribution_LandingVisitLog::deleteOlder($min_created_at, self::_DELETE_EXPIRED_LIMIT);
			if ($deleted_count > 0) {
				$is_need_optimize = true;
			}
		} while ($deleted_count == self::_DELETE_EXPIRED_LIMIT);

		// если записи не трогали, то и оптимизация таблицы не нужна
		if (!$is_need_optimize) {
			return;
		}

		Gateway_Db_PivotAttribution_LandingVisitLog::optimize();
	}

	/**
	 * Очищаем старые данные в таблице landing_visit_log
	 * @return void
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 * @throws ParseFatalException
	 */
	protected static function _clearOldDataUserAppRegistrationLog():void {

		$min_created_at   = time() - self::_ATTRIBUTION_RAW_DATA_TIME_LIFE;
		$is_need_optimize = false;
		do {

			// очищаем таблицу
			$deleted_count = Gateway_Db_PivotAttribution_UserAppRegistrationLog::deleteOlder($min_created_at, self::_DELETE_EXPIRED_LIMIT);
			if ($deleted_count > 0) {
				$is_need_optimize = true;
			}
		} while ($deleted_count == self::_DELETE_EXPIRED_LIMIT);

		// если записи не трогали, то и оптимизация таблицы не нужна
		if (!$is_need_optimize) {
			return;
		}

		Gateway_Db_PivotAttribution_UserAppRegistrationLog::optimize();
	}
}