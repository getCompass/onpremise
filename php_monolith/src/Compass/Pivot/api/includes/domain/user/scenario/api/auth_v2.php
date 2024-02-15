<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\ParamException;

/**
 * класс описывает сценарии api-методов группы api/v2/auth/
 */
class Domain_User_Scenario_Api_AuthV2 {

	/** Все возможные варианты для значения action в методе /auth/sendAttributionOfRegistration/ */
	public const ATTRIBUTION_ACTION_OPEN_DASHBOARD     = "open_dashboard";
	public const ATTRIBUTION_ACTION_OPEN_ENTERING_LINK = "open_entering_link";
	public const ATTRIBUTION_ACTION_OPEN_JOIN_LINK     = "open_join_link";

	/**
	 * Фиксируем цифровой отпечаток, с которым зарегистрировался пользователь
	 * Заодно определяем имеется ли 100% совпадение в цифровых отпечатках с посещением /join/ страницы ранее
	 *
	 * @return array
	 * @throws Domain_Company_Exception_IsHibernated
	 * @throws Domain_Company_Exception_IsRelocating
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\EndpointAccessDeniedException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws \userAccessException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyNotExist
	 * @throws cs_IncorrectJoinLink
	 * @throws cs_JoinLinkIsNotActive
	 * @throws cs_JoinLinkIsUsed
	 * @throws cs_JoinLinkNotFound
	 * @throws cs_PlatformNotFound
	 * @throws cs_UserAlreadyInCompany
	 * @throws cs_UserNotFound
	 */
	public static function sendAttributionOfRegistration(int $user_id, string $session_uniq, int $timezone_utc_offset, int $screen_avail_width, int $screen_avail_height):array {

		self::assertAttributionParameters($timezone_utc_offset, $screen_avail_width, $screen_avail_height);

		Type_Phphooker_Main::sendUserAccountLog($user_id, Type_User_Analytics::FIRST_PROFILE_SET);

		// получим и сохраним в Bitrix данные по рекламной кампании, с которой пользователь пришел в приложение
		// делаем это с задержкой в 120 секунд, чтобы наверняка
		Type_Phphooker_Main::sendBitrixUserCampaignData($user_id, 120);

		// получаем информацию о пользователе – возьмем его время регистрации
		$user_info = Gateway_Bus_PivotCache::getUserInfo($user_id);

		// получаем platform пользователя
		$platform = Type_Api_Platform::getPlatform();

		// получаем platform_os пользователя
		$platform_os = Type_Api_Platform::getElectronPlatformOS();

		// получаем ip_address пользователя
		$ip_address = getIp();

		// сохраняем цифровой отпечаток пользователя
		$user_app_registration_attr = Domain_User_Entity_Attribution::saveUserAppRegistrationLog(
			$user_id, $ip_address, $platform, $platform_os, $timezone_utc_offset, $screen_avail_width, $screen_avail_height, $user_info->created_at
		);

		// определяем тип трафика
		$user_attribution_traffic = Domain_User_Entity_Attribution_Traffic_TypeDetector::detect($user_app_registration_attr);

		// определяем нужно ли собирать аналитику по пользователю
		$user_attribution_traffic->setCollectAnalyticsFlag(Domain_User_Entity_Attribution_JoinSpaceAnalytics::shouldCollectAnalytics($user_info));

		// определяем посещение с которого пришел пользователь по правилам типа трафика
		$user_attribution_traffic->detectMatchedVisit();

		// получаем действие, которое нужно выполнить клиентскому приложению
		$client_action = $user_attribution_traffic->getClientAction();

		// в зависимости от действия формируем соответствующий ответ
		return match ($client_action) {
			Domain_User_Entity_Attribution_Traffic_Abstract::CLIENT_ACTION_OPEN_DASHBOARD     => ["action" => self::ATTRIBUTION_ACTION_OPEN_DASHBOARD, "data" => (object) []],
			Domain_User_Entity_Attribution_Traffic_Abstract::CLIENT_ACTION_OPEN_ENTERING_LINK => ["action" => self::ATTRIBUTION_ACTION_OPEN_ENTERING_LINK, "data" => (object) []],
			Domain_User_Entity_Attribution_Traffic_Abstract::CLIENT_ACTION_OPEN_JOIN_LINK     => self::_prepareOpenJoinLinkOutput($user_id, $session_uniq, $user_attribution_traffic->getMatchedVisit()),
			default                                                                           => throw new ParseFatalException("unexpected behaviour"),
		};
	}

	/**
	 * Удостоверяемся, что присланы корректные параметры
	 *
	 * @throws ParamException
	 */
	public static function assertAttributionParameters(int $timezone_utc_offset, int $screen_avail_width, int $screen_avail_height):void {

		// проверяем, что передали корректные параметры
		if (!Domain_User_Entity_Attribution::isCorrectTimezoneUtcOffset($timezone_utc_offset)) {
			throw new ParamException("incorrect timezone_utc_offset");
		}

		// проверяем, что передали корректный $screen_avail_width
		if (!Domain_User_Entity_Attribution::isCorrectScreenSideSize($screen_avail_width)) {
			throw new ParamException("incorrect screen_avail_width");
		}

		// проверяем, что передали корректный $screen_avail_height
		if (!Domain_User_Entity_Attribution::isCorrectScreenSideSize($screen_avail_height)) {
			throw new ParamException("incorrect $screen_avail_height");
		}
	}

	/**
	 * @return array
	 * @throws Domain_Company_Exception_IsHibernated
	 * @throws Domain_Company_Exception_IsRelocating
	 * @throws ParseFatalException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws \userAccessException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyNotExist
	 * @throws cs_IncorrectJoinLink
	 * @throws cs_JoinLinkIsNotActive
	 * @throws cs_JoinLinkIsUsed
	 * @throws cs_JoinLinkNotFound
	 * @throws cs_UserAlreadyInCompany
	 * @throws cs_UserNotFound
	 */
	protected static function _prepareOpenJoinLinkOutput(int $user_id, string $session_uniq, Struct_Db_PivotAttribution_LandingVisit $join_page_visit):array {

		// если это не join-ссылка, то валимся – мы не должны были попасть сюда
		if (!Domain_Company_Entity_JoinLink_Main::isJoinLink($join_page_visit->link)) {
			throw new ParseFatalException("unexpected behaviour");
		}

		// получаем информацию по ссылке
		$join_link_data = Domain_Link_Scenario_Api::validateJoinLink($user_id, $join_page_visit->link, $session_uniq);

		return [
			"action" => (string) self::ATTRIBUTION_ACTION_OPEN_JOIN_LINK,
			"data"   => (object) [
				"join_link" => (object) $join_link_data,
			],
		];
	}
}