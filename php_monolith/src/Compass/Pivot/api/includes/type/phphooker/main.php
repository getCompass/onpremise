<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

/**
 * класс для исполнения задач через phphooker
 * ВАЖНО! для каждого типа задачи должна быть функция здесь
 */
class Type_Phphooker_Main {

	##########################################################
	# region типы задач
	##########################################################

	public const TASK_TYPE_UPDATE_USER_COMPANY_INFO            = 3;  // обновление пользовательских данных во всех компаниях
	public const TASK_TYPE_LOGOUT_USER                         = 4;  // разлогиниваем пользователя во всех компаниях
	public const TASK_TYPE_KICK_USER_FROM_COMPANY              = 5;  // блокировка пользователя в системе, шаг второй
	public const TASK_TYPE_DELETE_COMPANY                      = 13; // удаление компании
	public const TASK_TYPE_DELETE_PROFILE                      = 14; // удаление аккаунта профиля
	public const TASK_TYPE_COUNT_COMPANY                       = 15; // обновить число компаний
	public const TASK_TYPE_UPDATE_PREMIUM_STATUS               = 21; // обновить премиум статусы в компаниях
	public const TASK_TYPE_SMS_RESEND_NOTICE                   = 31; // отреагировать на повторный запрос смс-сообщения
	public const TASK_TYPE_INCORRECT_INVITE_LINK               = 43; // отреагировать на некорректную invite-ссылку
	public const TASK_TYPE_ON_AUTH_STORY_EXPIRE                = 44; // при истечении срока жизни попытки логина/регистрации
	public const TASK_TYPE_ON_CONFIRMATION_STORY_EXPIRE        = 45; // при истечении срока жизни попытки two_fa
	public const TASK_TYPE_ON_PHONE_CHANGE_STORY_EXPIRE        = 46; // при истечении срока жизни попытки смены номера
	public const TASK_TYPE_SEND_ACCOUNT_STATUS_LOG             = 47; // задача отправки лога по статусу пользователя до введения новой модели оплаты
	public const TASK_TYPE_SEND_SPACE_STATUS_LOG               = 48; // задача отправки лога по статусу компании до введения новой модели оплаты
	public const TASK_TYPE_SEND_BITRIX_ON_USER_REGISTERED      = 49; // задача отправки в Bitrix информации о новом зарегистрированном пользователе
	public const TASK_TYPE_SEND_BITRIX_ON_USER_CHANGE_INFO     = 50; // задача отправки в Bitrix актуальной информации о ранее зарегистрированном пользователе
	public const TASK_TYPE_SEND_BITRIX_USER_CAMPAIGN_DATA      = 51; // задача получения и отправки в Bitrix данных по рекламной кампании, с которой пользователь пришел в приложение
	public const TASK_TYPE_ACCEPT_FIRST_JOIN_LINK              = 52; // при принятии первой ссылки-приглашения в команду
	public const TASK_TYPE_ON_USER_LEFT_SPACE_EARLY            = 53; // пользователь покинул пространство слишком рано
	public const TASK_TYPE_USER_ENTERING_FIRST_SPACE           = 54; // пользователь вступил в первую команду
	public const TASK_TYPE_ON_PHONE_ADD_STORY_EXPIRE           = 55; // при истечении срока жизни попытки добавления номера телефона
	public const TASK_TYPE_KICK_USER_FROM_ALL_COMPANIES        = 56; // исключаем пользователя из всех команд
	public const TASK_TYPE_UPDATE_MEMBER_INFO_ON_ALL_COMPANIES = 57; // обновляем данные пользователя во всех его командах
	public const TASK_TYPE_ON_SUCCESS_DEVICE_LOGIN             = 58; // при успешной авторизации устройства

	# endregion
	##########################################################

	##########################################################
	# region методы для добавления задачи
	##########################################################

	/**
	 * событие обновлении пользовательских данных
	 *
	 */
	public static function onUserInfoChange(int $user_id, string $client_launch_uuid = ""):void {

		self::_addFromApi(self::TASK_TYPE_UPDATE_USER_COMPANY_INFO, 0, [
			"user_id"            => $user_id,
			"client_launch_uuid" => $client_launch_uuid,
		]);
	}

	/**
	 * Событие удаления компании
	 *
	 */
	public static function onCompanyDelete(int $deleted_by_user_id, int $company_id):void {

		self::_addFromApi(self::TASK_TYPE_DELETE_COMPANY, 0, [
			"deleted_by_user_id" => $deleted_by_user_id,
			"company_id"         => $company_id,
		]);
	}

	/**
	 * событие при успешной авторизации
	 *
	 */
	public static function onSuccessDeviceLogin(int $user_id, string $login_type, string $device_name, string $app_version, string $server_version, string $locale):void {

		self::_addFromApi(self::TASK_TYPE_ON_SUCCESS_DEVICE_LOGIN, 0, [
			"user_id"        => $user_id,
			"login_type"     => $login_type,
			"device_name"    => $device_name,
			"app_version"    => $app_version,
			"server_version" => $server_version,
			"locale"         => $locale,
		]);
	}

	/**
	 * событие при разлогине пользователя
	 *
	 */
	public static function onUserLogout(int $user_id, array $session_uniq_list):void {

		self::_addFromApi(self::TASK_TYPE_LOGOUT_USER, 0, [
			"user_id"           => $user_id,
			"session_uniq_list" => $session_uniq_list,
		]);
	}

	/**
	 * Запрос на исключение пользователя из компании.
	 *
	 */
	public static function onKickUserFromCompanyRequested(int $user_id, int $user_role, int $company_id):void {

		self::_addFromApi(self::TASK_TYPE_KICK_USER_FROM_COMPANY, 0, [
			"user_id"    => $user_id,
			"user_role"  => $user_role,
			"company_id" => $company_id,
		]);
	}

	/**
	 * Событие удаления аккаунта пользователя
	 *
	 */
	public static function onProfileDelete(int $deleted_user_id):void {

		self::_addFromApi(self::TASK_TYPE_DELETE_PROFILE, 0, [
			"deleted_user_id" => $deleted_user_id,
		]);
	}

	/**
	 * Действие по исключению пользователя из всех его команд
	 */
	public static function kickUserFromAllCompanies(int $user_id):void {

		self::_addFromApi(self::TASK_TYPE_KICK_USER_FROM_ALL_COMPANIES, 0, [
			"user_id" => $user_id,
		]);
	}

	/**
	 * Событие пересчета количества компаний
	 *
	 */
	public static function onCountCompany(int $need_work):void {

		self::_addFromApi(self::TASK_TYPE_COUNT_COMPANY, $need_work, []);
	}

	/**
	 * Пользователь запросил переотправку смс-сообщения.
	 */
	public static function onSmsResent(int $user_id, string $phone_number, int $remain_attempt_count, string $action, string $country_name, string $sms_id):void {

		$need_work = time();
		if (!isTestServer()) {

			// отправляем спустя 2 минуты, чтобы наверняка была история по отправке смс
			// делаем это только не на тестовых серверах, иначе в хукере всегда будут весеть задачи
			$need_work += 60 * 2;
		}

		self::_addFromApi(self::TASK_TYPE_SMS_RESEND_NOTICE, $need_work, [
			"user_id"              => $user_id,
			"remain_attempt_count" => $remain_attempt_count,
			"phone_number"         => $phone_number,
			"action"               => $action,
			"country_name"         => $country_name,
			"sms_id"               => $sms_id,
		]);
	}

	/**
	 * Пользователь ввел некорректную invite-ссылку
	 */
	public static function onTryValidateIncorrectLink(int $user_id, string $link):void {

		self::_addFromApi(self::TASK_TYPE_INCORRECT_INVITE_LINK, time(), [
			"user_id" => $user_id,
			"link"    => $link,
		]);
	}

	/**
	 * Попытка логина/регистрации истекла
	 */
	public static function onAuthStoryExpire(string $auth_map, int $expires_at):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		// берем с небольшим запасом, чтобы наверняка
		// точность до секунды здесь не важна
		$need_work = $expires_at + 5;

		// но если это тестовый сервер, то не медлим с выполнением, иначе тестам хана :c
		if (isTestServer()) {
			$need_work = time();
		}

		self::_addFromApi(self::TASK_TYPE_ON_AUTH_STORY_EXPIRE, $need_work, [
			"auth_map" => $auth_map,
		]);
	}

	/**
	 * Попытка подтверждения two_fa действия истекла
	 */
	public static function onTwoFaStoryExpire(string $two_fa_map, int $expires_at):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		// берем с небольшим запасом, чтобы наверняка
		// точность до секунды здесь не важна
		$need_work = $expires_at + 5;

		// но если это тестовый сервер, то не медлим с выполнением, иначе тестам хана :c
		if (isTestServer()) {
			$need_work = time();
		}

		self::_addFromApi(self::TASK_TYPE_ON_CONFIRMATION_STORY_EXPIRE, $need_work, [
			"two_fa_map" => $two_fa_map,
		]);
	}

	/**
	 * Попытка добавления номера телефона истекла
	 */
	public static function onPhoneAddStoryExpire(int $user_id, string $add_change_map, int $expires_at):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		// берем с небольшим запасом, чтобы наверняка
		// точность до секунды здесь не важна
		$need_work = $expires_at + 5;

		// но если это тестовый сервер, то не медлим с выполнением, иначе тестам хана :c
		if (isTestServer()) {
			$need_work = time();
		}

		self::_addFromApi(self::TASK_TYPE_ON_PHONE_ADD_STORY_EXPIRE, $need_work, [
			"user_id"        => $user_id,
			"add_change_map" => $add_change_map,
		]);
	}

	/**
	 * Попытка смены номера истекла
	 */
	public static function onPhoneChangeStoryExpire(int $user_id, string $phone_change_map, int $expires_at):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		// берем с небольшим запасом, чтобы наверняка
		// точность до секунды здесь не важна
		$need_work = $expires_at + 5;

		// но если это тестовый сервер, то не медлим с выполнением, иначе тестам хана :c
		if (isTestServer()) {
			$need_work = time();
		}

		self::_addFromApi(self::TASK_TYPE_ON_PHONE_CHANGE_STORY_EXPIRE, $need_work, [
			"user_id"          => $user_id,
			"phone_change_map" => $phone_change_map,
		]);
	}

	/**
	 * Отправка лога статуса пользователя
	 */
	public static function sendUserAccountLog(int $user_id, int $action, int $delay = 0):void {

		if ((isTestServer() && !isBackendTest() && !isLocalServer()) || ServerProvider::isOnPremise()) {
			return;
		}

		// на тестовых не ставим задержку, чтобы тесты не ломать
		if (isTestServer()) {
			$delay = 0;
		}

		self::_addFromApi(self::TASK_TYPE_SEND_ACCOUNT_STATUS_LOG, time() + $delay, [
			"user_id" => $user_id,
			"action"  => $action,
		]);
	}

	/**
	 * Отправка лога статуса пространства
	 */
	public static function sendSpaceLog(int $action, int $company_id):void {

		if (isTestServer() && !isBackendTest() && !isLocalServer() || ServerProvider::isOnPremise()) {
			return;
		}

		self::_addFromApi(self::TASK_TYPE_SEND_SPACE_STATUS_LOG, time(), [
			"action"     => $action,
			"company_id" => $company_id,
		]);
	}

	/**
	 * Отправка в Bitrix информации о новом зарегистрированном пользователе
	 */
	public static function sendBitrixOnUserRegistered(int $user_id, string|null $force_stage_id = null):void {

		if ((isTestServer() && !isBackendTest() && !isLocalServer()) || !IS_BITRIX_USER_ANALYTICS_ENABLED || ServerProvider::isOnPremise()) {
			return;
		}

		self::_addFromApi(self::TASK_TYPE_SEND_BITRIX_ON_USER_REGISTERED, time(), [
			"user_id"        => $user_id,
			"force_stage_id" => $force_stage_id,
		]);
	}

	/**
	 * Отправки в Bitrix актуальной информации о ранее зарегистрированном пользователе
	 */
	public static function sendBitrixOnUserChangedInfo(int $user_id, array $changed_data):void {

		if ((isTestServer() && !isBackendTest() && !isLocalServer()) || !IS_BITRIX_USER_ANALYTICS_ENABLED || ServerProvider::isOnPremise()) {
			return;
		}

		self::_addFromApi(self::TASK_TYPE_SEND_BITRIX_ON_USER_CHANGE_INFO, time(), [
			"user_id"      => $user_id,
			"changed_data" => $changed_data,
		]);
	}

	/**
	 * получим и сохраним в Bitrix данные по рекламной кампании, с которой пользователь пришел в приложение
	 */
	public static function sendBitrixUserCampaignData(int $user_id, int $delay):void {

		if ((isTestServer() && !isBackendTest() && !isLocalServer()) || !IS_BITRIX_USER_ANALYTICS_ENABLED || ServerProvider::isOnPremise()) {
			return;
		}

		// если уж вдруг запустится на тестовых серверах, то не ставим задержку, чтобы тесты не ломать
		if (isTestServer()) {
			$delay = 0;
		}

		self::_addFromApi(self::TASK_TYPE_SEND_BITRIX_USER_CAMPAIGN_DATA, time() + $delay, [
			"user_id" => $user_id,
		]);
	}

	/**
	 * совершаем некоторые действия в случае если пользователь покинул пространство слишком рано
	 */
	public static function onUserLeftSpaceEarly(int $user_id, int $company_id, int $entry_id):void {

		self::_addFromApi(self::TASK_TYPE_ON_USER_LEFT_SPACE_EARLY, time(), [
			"user_id"    => $user_id,
			"company_id" => $company_id,
			"entry_id"   => $entry_id,
		]);
	}

	/**
	 * Событие, когда пользователь попал в свою первую команду
	 */
	public static function onUserEnteringFirstCompany(int $user_id, int $company_id, int $entry_id):void {

		self::_addFromApi(self::TASK_TYPE_USER_ENTERING_FIRST_SPACE, time(), [
			"user_id"    => $user_id,
			"company_id" => $company_id,
			"entry_id"   => $entry_id,
		]);
	}

	/**
	 * обновляем данные на каждой из компаний пользователя
	 */
	public static function updateMemberInfoOnAllCompanies(int $user_id, int $need_work, false|string $badge_content, false|string $status, false|string $description):void {

		self::_addFromApi(self::TASK_TYPE_UPDATE_MEMBER_INFO_ON_ALL_COMPANIES, $need_work, [
			"user_id"       => $user_id,
			"badge_content" => $badge_content,
			"status"        => $status,
			"description"   => $description,
		]);
	}

	# endregion
	##########################################################

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// добавить задачу в очередь из API запроса пользователя
	protected static function _addFromApi(int $task_type, int $need_work, array $params):void {

		// если задача отложенного исполнения - не будем портить статистику created_at
		$created_at = time();
		if ($need_work > time()) {
			$created_at = $need_work;
		}

		$insert = [
			"task_id"         => null,
			"task_type"       => $task_type,
			"error_count"     => 0,
			"need_work"       => $need_work,
			"created_at"      => $created_at,
			"task_global_key" => self::_generateTaskGlobalKey(),
			"params"          => $params,
		];
		ShardingGateway::database("pivot_system")->insert("phphooker_queue", $insert);
	}

	// генерирует уникальный идентификатор для задачи
	protected static function _generateTaskGlobalKey():string {

		return sha1(getUniqId(128));
	}
}