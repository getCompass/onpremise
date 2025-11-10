<?php

namespace Compass\Pivot;
/*
 * модель для работы с настройками уведомлений пользователя
 *
 * через неё происходит добавление/удаление токенов
 * и изменений состояния токенов пользователя
*/

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\ParamException;

/**
 * Class Type_User_Notifications
 */
class Type_User_Notifications {

	// список типов токенов всех токенов приложении
	// для firebase токен общий на обычные пуши и для входящих звонков
	// для apns токены раздельные
	public const TOKEN_TYPE_FIREBASE_LEGACY = 1;
	public const TOKEN_TYPE_APNS_MESSAGE    = 2;
	public const TOKEN_TYPE_APNS_VIOP       = 3;
	public const TOKEN_TYPE_HUAWEI          = 4;
	public const TOKEN_TYPE_FIRERBASE_V1    = 5;

	// список типов звука
	public const SOUND_TYPE_1 = 0;
	public const SOUND_TYPE_2 = 1;
	public const SOUND_TYPE_3 = 2;
	public const SOUND_TYPE_4 = 3;
	public const SOUND_TYPE_5 = 4;
	public const SOUND_TYPE_6 = 5;

	// типы токенов на которые можно отправить push
	protected const _ALLOW_TOKEN_TYPE = [
		self::TOKEN_TYPE_APNS_MESSAGE,
		self::TOKEN_TYPE_FIREBASE_LEGACY,
		self::TOKEN_TYPE_APNS_VIOP,
		self::TOKEN_TYPE_HUAWEI,
		self::TOKEN_TYPE_FIRERBASE_V1,
	];

	// доступные типы звуков
	protected const _ALLOW_SOUND_TYPE = [
		self::SOUND_TYPE_1,
		self::SOUND_TYPE_2,
		self::SOUND_TYPE_3,
		self::SOUND_TYPE_4,
		self::SOUND_TYPE_5,
		self::SOUND_TYPE_6,
	];

	// доступные эвенты для постоянного отключения
	protected const _ALLOW_TO_DISABLE_EVENT = [
		EVENT_TYPE_CONVERSATION_MESSAGE_MASK,
		EVENT_TYPE_THREAD_MESSAGE_MASK,
		EVENT_TYPE_INVITE_MESSAGE_MASK,
	];

	// доступные эвенты для отключения по таймеру
	protected const _ALLOW_TO_SNOOZE_EVENT = [
		EVENT_TYPE_BELONGS_TO_GROUP_CONVERSATION_MASK,
	];

	// текущая версия token_item
	protected const _TOKEN_ITEM_VERSION = 2;

	// массив с версиями token_item
	protected const _TOKEN_ITEM_SCHEMA = [
		1 => [
			"token"                => "",
			"token_type"           => 0,
			"created_at"           => 0,
			"session_uniq"         => "",
			"device_id"            => "",
			"sound_type"           => self::SOUND_TYPE_1,
			"is_new_firebase_push" => 0,
		],
		2 => [
			"token"                => "",
			"token_type"           => 0,
			"created_at"           => 0,
			"session_uniq"         => "",
			"device_id"            => "",
			"sound_type"           => self::SOUND_TYPE_1,
			"is_new_firebase_push" => 0,
			"app_name"             => self::_DEFAULT_APP_NAME,
		],
	];

	protected const _DEFAULT_APP_NAME = "comteam";

	protected const _ALLOWED_APP_NAME_LIST = [
		"compass",
		"comteam",
	];

	/**
	 * доступен ли такой тип токена
	 *
	 */
	public static function isTokenTypeAllowed(int $token_type):bool {

		return in_array($token_type, self::_ALLOW_TOKEN_TYPE);
	}

	/**
	 * добавляет токен push-уведомлений для пользователя
	 *
	 * @throws \cs_RowIsEmpty
	 * @throws \queryException
	 * @throws \returnException
	 * @long
	 */
	public static function addToken(int $user_id, int $token_type, string $token, string $device_id, string $user_agent):void {

		if ($user_id < 1) {
			return;
		}
		$app_name = Type_Api_Platform::getAppNameByUserAgent($user_agent);

		if ($token_type == self::TOKEN_TYPE_APNS_VIOP) {
			self::updateUserForTokenIfDuplicate($token, $user_id, $device_id);
		}

		// добавляем девайс для пользователя
		self::addDeviceForUser($user_id, $device_id);

		// добавляем токен для девайса
		self::_addTokenForDevice($device_id, $user_id, $token, $token_type, $app_name);
	}

	/**
	 * добавляем девайс для пользователя
	 *
	 * @throws \queryException
	 * @throws \returnException|\cs_RowIsEmpty
	 */

	public static function addDeviceForUser(int $user_id, string $device_id):void {

		// добавляем запись для хранения девайсов пользователя, если запись отсутствует
		$user_notification_row = self::_addUserNotificationRowIfNotExist($user_id, $device_id);

		// если такой девайс уже числится за пользователем
		if (in_array($device_id, $user_notification_row->device_list)) {
			return;
		}

		// добавляем device_id в список девайсов пользователя
		Gateway_Db_PivotUser_NotificationList::beginTransaction($user_id);
		$user_notification_row                = Gateway_Db_PivotUser_NotificationList::getForUpdate($user_id);
		$user_notification_row->device_list[] = $device_id;

		Gateway_Db_PivotUser_NotificationList::set($user_id, [
			"device_list" => array_values($user_notification_row->device_list),
			"updated_at"  => time(),
		]);

		Gateway_Db_PivotUser_NotificationList::commitTransaction($user_id);
	}

	/**
	 * создает запись в таблице user_notification_list, если нужно и возвращает её
	 *
	 * @param int          $user_id
	 * @param string|false $device_id
	 *
	 * @return Struct_Db_PivotUser_Notification
	 * @throws \paramException
	 * @throws \queryException
	 */
	protected static function _addUserNotificationRowIfNotExist(int $user_id, string|false $device_id = false):Struct_Db_PivotUser_Notification {

		// если передали левак вместо device id, выкидываем ошибку
		if ($device_id !== false && !checkUuid($device_id) && !checkGuid($device_id)) {
			throw new ParamException("invalid device id");
		}

		$device_list = $device_id !== false ? [$device_id] : [];

		// получаем запись из базы для пользователя; создаем если ее нет
		try {
			return Gateway_Db_PivotUser_NotificationList::getOne($user_id);
		} catch (\cs_RowIsEmpty) {

			$created_at = time();
			$extra      = Type_User_Notifications_UserExtra::initExtra();

			$insert = [
				"user_id"       => $user_id,
				"snoozed_until" => 0,
				"created_at"    => $created_at,
				"updated_at"    => 0,
				"device_list"   => $device_list,
				"extra"         => $extra,
			];
			Gateway_Db_PivotUser_NotificationList::insert($user_id, $insert);

			return new Struct_Db_PivotUser_Notification($user_id, 0, $created_at, 0, $device_list, $extra);
		}
	}

	/**
	 * добавляем токен для девайса пользователя
	 *
	 * @throws \queryException
	 * @throws \returnException
	 */
	protected static function _addTokenForDevice(string $device_id, int $user_id, string $token, int $token_type, string $app_name):void {

		// добавляем данные о девайсе пользователя, если запись отсутствует
		$device_row = self::_addDeviceRowIfNotExist($device_id, $user_id);

		// проверяем, имеется ли такой токен у данного девайса
		$token_list = Type_User_Notifications_DeviceExtra::getTokenList($device_row["extra"]);

		if (self::_isTokenExist($token_list, $token)) {

			// если имеется но девайс принадлежит не нашему пользователю
			if ($device_row["user_id"] != $user_id) {

				Gateway_Db_PivotData_DeviceList::set($device_id, [
					"user_id"    => $user_id,
					"updated_at" => time(),
				]);
			}

			// если токен и девайс для пользователя совпадают, то ничего не трогаем
			return;
		}

		Gateway_Db_PivotData_DeviceList::beginTransaction();
		$device_row = Gateway_Db_PivotData_DeviceList::getForUpdate($device_id);
		$token_list = Type_User_Notifications_DeviceExtra::getTokenList($device_row["extra"]);

		// получаем тип звука для такого же типа токена
		$sound_type = self::_getSoundTypeByTokenType($token_list, $token_type);

		// добавляем токен в список токенов устройства
		$token_list   = self::_addTokenInTokenList($token_list, $device_id, $token, $token_type, $sound_type, $app_name);
		$device_extra = Type_User_Notifications_DeviceExtra::setTokenList($device_row["extra"], $token_list);

		Gateway_Db_PivotData_DeviceList::set($device_id, [
			"user_id"    => $user_id,
			"extra"      => $device_extra,
			"updated_at" => time(),
		]);

		Gateway_Db_PivotData_DeviceList::commitTransaction();
	}

	/**
	 * создает запись в таблице device_list, если нужно и возвращает её
	 *
	 * @throws \queryException
	 */
	protected static function _addDeviceRowIfNotExist(string $device_id, int $user_id):array {

		self::_addUserNotificationRowIfNotExist($user_id, $device_id);

		try {
			$device_row = Gateway_Db_PivotData_DeviceList::getOne($device_id);
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {

			$insert = [
				"device_id"  => $device_id,
				"user_id"    => $user_id,
				"created_at" => time(),
				"updated_at" => 0,
				"extra"      => Type_User_Notifications_DeviceExtra::init(),
			];

			Gateway_Db_PivotData_DeviceList::insert($device_id, $insert);

			return $insert;
		}

		return $device_row;
	}

	/**
	 * проверяет, существует ли токен push-уведомлений в token_list
	 *
	 */
	protected static function _isTokenExist(array $token_list, string $token):bool {

		$token_uuid_list = array_column($token_list, "token");

		return in_array($token, $token_uuid_list);
	}

	/**
	 * метод для получения типа звука по типу токена
	 *
	 */
	protected static function _getSoundTypeByTokenType(array $token_list, int $token_type):int {

		// бежим циклом по всем токенам и ищем токен с нужным нам типом и возващаем его звук
		foreach ($token_list as $v) {

			// актуализируем структуру токена
			$v = self::_getTokenItem($v);

			if ($v["token_type"] == $token_type) {
				return $v["sound_type"];
			}
		}

		return self::SOUND_TYPE_1;
	}

	/**
	 * добавляем токен в список токенов
	 *
	 */
	protected static function _addTokenInTokenList(array $token_list, string $device_id, string $token, int $token_type, int $sound_type, string $app_name):array {

		// создаем структуру для токена
		$new_token_item = self::_initTokenItem($device_id, $token, $token_type, $sound_type, $app_name);

		foreach ($token_list as $index => $item) {

			// актуализируем структуру
			$item = self::_getTokenItem($item);

			// если в списке нашли токен с таким же типом, то заменяем
			if ($item["token_type"] == $token_type && $app_name == $item["app_name"]) {

				$token_list[$index] = $new_token_item;
				return $token_list;
			}
		}

		// добавляем токен к остальным в списке
		$token_list[] = $new_token_item;

		return $token_list;
	}

	/**
	 * добавляем токен компании в список токенов
	 *
	 */
	protected static function _addUserCompanyPushTokenInList(array $token_list, string $token):array {

		if (in_array($token, $token_list)) {
			return $token_list;
		}

		$token_list[] = $token;

		return $token_list;
	}

	/**
	 * добавляем id компании в список
	 *
	 */
	protected static function _addUserCompanyIdInList(array $company_id_list, int $company_id):array {

		if (in_array($company_id, $company_id_list)) {
			return $company_id_list;
		}

		$company_id_list[] = $company_id;

		return $company_id_list;
	}

	/**
	 * инициализирует схему token_item
	 *
	 */
	protected static function _initTokenItem(string $device_id, string $token, int $token_type, int $sound_type, string $app_name):array {

		$output = self::_TOKEN_ITEM_SCHEMA[self::_TOKEN_ITEM_VERSION];

		$output["token"]      = $token;
		$output["token_type"] = $token_type;
		$output["created_at"] = time();
		$output["device_id"]  = $device_id;
		$output["sound_type"] = $sound_type;
		$output["version"]    = self::_TOKEN_ITEM_VERSION;

		$output["app_name"] = in_array($app_name, self::_ALLOWED_APP_NAME_LIST)
			? $app_name
			: self::_DEFAULT_APP_NAME;

		return $output;
	}

	/**
	 * убираем девайсы у пользователя
	 *
	 * @throws \returnException
	 */
	public static function deleteDevicesForUser(int $user_id):void {

		Gateway_Db_PivotUser_NotificationList::beginTransaction($user_id);

		try {
			$user_notification_row = Gateway_Db_PivotUser_NotificationList::getForUpdate($user_id);
		} catch (\cs_RowIsEmpty) {

			Gateway_Db_PivotUser_NotificationList::rollback($user_id);
			return;
		}

		// если записи нет или в записи отсутствуют девайсы, то ничего не делаем
		if (!isset($user_notification_row->user_id) || count($user_notification_row->device_list) < 1) {

			Gateway_Db_PivotUser_NotificationList::rollback($user_id);
			return;
		}

		// открепляем device_id от пользователя
		Gateway_Db_PivotUser_NotificationList::set($user_id, [
			"device_list" => [],
			"updated_at"  => time(),
		]);

		Gateway_Db_PivotUser_NotificationList::commitTransaction($user_id);
	}

	/**
	 * убираем выбранный device_id у пользователя
	 *
	 * @throws \returnException
	 */
	public static function deleteDeviceForUser(int $user_id, string $device_id):void {

		Gateway_Db_PivotUser_NotificationList::beginTransaction($user_id);

		try {
			$user_notification_row = Gateway_Db_PivotUser_NotificationList::getForUpdate($user_id);
		} catch (\cs_RowIsEmpty) {

			Gateway_Db_PivotUser_NotificationList::rollback($user_id);
			return;
		}

		// если записи нет или в записи отсутствует девайс пользователя, то ничего не делаем
		if (!isset($user_notification_row->user_id) || !in_array($device_id, $user_notification_row->device_list)) {

			Gateway_Db_PivotUser_NotificationList::rollback($user_id);
			return;
		}

		// открепляем device_id от пользователя
		$device_list = array_diff($user_notification_row->device_list, [$device_id]);
		Gateway_Db_PivotUser_NotificationList::set($user_id, [
			"device_list" => array_values($device_list),
			"updated_at"  => time(),
		]);

		Gateway_Db_PivotUser_NotificationList::commitTransaction($user_id);
	}

	/**
	 * убираем выбранный список device_id у пользователя
	 *
	 * @throws \returnException
	 */
	public static function deleteDeviceListForUser(int $user_id, array $device_id_list):void {

		Gateway_Db_PivotUser_NotificationList::beginTransaction($user_id);

		try {
			$user_notification_row = Gateway_Db_PivotUser_NotificationList::getForUpdate($user_id);
		} catch (\cs_RowIsEmpty) {

			Gateway_Db_PivotUser_NotificationList::rollback($user_id);
			return;
		}

		if (!isset($user_notification_row->user_id)) {

			Gateway_Db_PivotUser_NotificationList::rollback($user_id);
			return;
		}

		// если в записи отсутствует девайс пользователя, то ничего не делаем
		if (count($user_notification_row->device_list) != 1 && count(array_diff($user_notification_row->device_list, $device_id_list)) == 0) {

			Gateway_Db_PivotUser_NotificationList::rollback($user_id);
			return;
		}

		// открепляем device_id от пользователя
		$device_list = array_diff($user_notification_row->device_list, $device_id_list);
		Gateway_Db_PivotUser_NotificationList::set($user_id, [
			"device_list" => array_values($device_list),
			"updated_at"  => time(),
		]);

		Gateway_Db_PivotUser_NotificationList::commitTransaction($user_id);
	}

	/**
	 * очищаем список токенов компании для данного устройства
	 *
	 * @throws \returnException
	 */
	public static function clearUserCompanyPushTokenList(string $device_id):void {

		if (strlen($device_id) < 1) {
			return;
		}

		Gateway_Db_PivotData_DeviceList::beginTransaction();

		// достаем запись девайса пользователя
		try {
			$device_row = Gateway_Db_PivotData_DeviceList::getForUpdate($device_id);
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {

			Gateway_Db_PivotData_DeviceList::rollback();
			return;
		}

		$device_extra = Type_User_Notifications_DeviceExtra::setUserCompanyPushTokenList($device_row["extra"], []);
		$device_extra = Type_User_Notifications_DeviceExtra::setCompanyIdList($device_extra, []);

		Gateway_Db_PivotData_DeviceList::set($device_id, [
			"extra"      => $device_extra,
			"updated_at" => time(),
		]);

		Gateway_Db_PivotData_DeviceList::commitTransaction();
	}

	/**
	 * обновляем user_id для устройства
	 *
	 */
	public static function updateUserIdForDevice(int $user_id, string $device_id):void {

		if (strlen($device_id) < 1 || $user_id < 1) {
			return;
		}

		try {
			$device_row = Gateway_Db_PivotData_DeviceList::getOne($device_id);
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {
			return;
		}

		// если и так принадлежит нашему пользователю
		if ($device_row["user_id"] == $user_id) {
			return;
		}

		Gateway_Db_PivotData_DeviceList::set($device_id, [
			"user_id"    => $user_id,
			"updated_at" => time(),
		]);
	}

	/**
	 * включает/отключает отправку уведомлений на определенное событие
	 *
	 * @throws \parseException|\returnException|\queryException|\cs_RowIsEmpty
	 */
	public static function setForEvent(int $user_id, int $event_type, bool $is_enabled):void {

		// проверяем, что пришел корректный event_type
		if (!in_array($event_type, self::_ALLOW_TO_DISABLE_EVENT)) {
			throw new ParseFatalException("Trying to set value for event which is not exists");
		}

		// создаем запись в таблице, если её нет
		self::_addUserNotificationRowIfNotExist($user_id, getDeviceId());

		Gateway_Db_PivotUser_NotificationList::beginTransaction($user_id);

		// получаем запись на обновление
		$user_notification_row = Gateway_Db_PivotUser_NotificationList::getForUpdate($user_id);

		// включаем/отключаем уведомления для нужного эвента
		$extra = Type_User_Notifications_UserExtra::setEventMask($is_enabled, $event_type, $user_notification_row->extra);

		Gateway_Db_PivotUser_NotificationList::set($user_id, [
			"extra"      => $extra,
			"updated_at" => time(),
		]);

		Gateway_Db_PivotUser_NotificationList::commitTransaction($user_id);
	}

	/**
	 * отключает все уведомления на время
	 *
	 * @throws \queryException
	 */
	public static function snooze(int $user_id, int $time):void {

		// создаем запись в таблице если нужно
		self::_addUserNotificationRowIfNotExist($user_id, getDeviceId());

		// обновляем запись
		Gateway_Db_PivotUser_NotificationList::set($user_id, [
			"snoozed_until" => $time,
			"updated_at"    => time(),
		]);
	}

	/**
	 * включить уведомления обратно
	 *
	 * @throws \queryException
	 */
	public static function unsnooze(int $user_id):void {

		// создаем запись в таблице если нужно
		self::_addUserNotificationRowIfNotExist($user_id, getDeviceId());

		// обновляем запись
		Gateway_Db_PivotUser_NotificationList::set($user_id, [
			"snoozed_until" => 0,
			"updated_at"    => time(),
		]);
	}

	/**
	 * включает/отключает (с таймером) отправку уведомлений на определенное событие
	 *
	 * @throws \parseException|\returnException|\queryException|\cs_RowIsEmpty
	 */
	public static function snoozeForEvent(int $user_id, int $event_mask, bool $is_snoozed):void {

		// поскольку для мьюта дефолтно состояние, когда ивент отключен
		// тогда устанавливаем, что - в маске — ивент замьючен, единица — не замьючен
		// поскольку при добавлении нового события, маски в старой экстре должны его мьютить по дефолту

		// проверяем, что пришел корректный event_type
		if (!in_array($event_mask, self::_ALLOW_TO_SNOOZE_EVENT)) {
			throw new ParseFatalException("incorrect event to snooze");
		}

		// создаем запись в таблице, если её нет
		self::_addUserNotificationRowIfNotExist($user_id, getDeviceId());

		Gateway_Db_PivotUser_NotificationList::beginTransaction($user_id);

		// получаем запись на обновление
		$user_notification_row = Gateway_Db_PivotUser_NotificationList::getForUpdate($user_id);

		// формируем экстра данные
		$extra = Type_User_Notifications_UserExtra::prepareSnoozeMask($event_mask, $is_snoozed, $user_notification_row->extra);

		Gateway_Db_PivotUser_NotificationList::set($user_id, [
			"extra"      => $extra,
			"updated_at" => time(),
		]);

		Gateway_Db_PivotUser_NotificationList::commitTransaction($user_id);
	}

	/**
	 * получает состояние уведомлений для пользователя
	 *
	 * @return int[]
	 *
	 * @throws \queryException
	 */
	public static function getPreferences(int $user_id):array {

		// получаем запись из таблицы, создаем её если нужно
		$user_notification_row = self::_addUserNotificationRowIfNotExist($user_id);
		$device_row            = self::_addDeviceRowIfNotExist(getDeviceId(), $user_id);

		// получаем актуальную extra
		$event_mask  = Type_User_Notifications_UserExtra::getEventMask($user_notification_row->extra);
		$snooze_mask = Type_User_Notifications_UserExtra::getSnoozeMask($user_notification_row->extra);

		// получаем статус токенов для пользователя
		$has_notification_token = self::_hasNotificationToken($user_notification_row, $device_row);
		$has_voip_token         = self::_hasVoipToken($user_notification_row, $device_row);

		// возвращаем настройки уведомлений
		return [

			// таймер временного отключения
			"snoozed_until"                 => $user_notification_row->snoozed_until,

			// временно отключенные события
			// если все события отключены, то отвечаем, что snoozed события отсуствуют
			"is_snoozed_for_group_messages" => $snooze_mask !== 0 ? (int) $snooze_mask & EVENT_TYPE_BELONGS_TO_GROUP_CONVERSATION_MASK ? 0 : 1 : 0,

			// постоянно отключенные события
			"is_enabled_for_messages"       => (int) $event_mask & EVENT_TYPE_CONVERSATION_MESSAGE_MASK ? 1 : 0,
			"is_enabled_for_threads"        => (int) $event_mask & EVENT_TYPE_THREAD_MESSAGE_MASK ? 1 : 0,
			"is_enabled_for_invites"        => (int) $event_mask & EVENT_TYPE_INVITE_MESSAGE_MASK ? 1 : 0,

			// флаги
			"has_notification_token"        => (int) $has_notification_token ? 1 : 0,
			"has_voip_token"                => (int) $has_voip_token ? 1 : 0,
		];
	}

	/**
	 * проверяем есть ли у пользователя зарегистрированный токен для пушей
	 *
	 */
	protected static function _hasNotificationToken(Struct_Db_PivotUser_Notification $user_notification_row, array $device_token_row):bool {

		if (count($user_notification_row->device_list) < 1) {
			return false;
		}

		// проверяем что такой пользователь есть в базе и что у него есть токены
		if (!isset($device_token_row["user_id"])) {
			return false;
		}

		$token_list = Type_User_Notifications_DeviceExtra::getTokenList($device_token_row["extra"]);

		// проверям что у пользователя есть токен для переданного устройства
		foreach ($token_list as $item) {

			if (in_array($item["token_type"], [
				self::TOKEN_TYPE_FIREBASE_LEGACY,
				self::TOKEN_TYPE_APNS_MESSAGE,
				self::TOKEN_TYPE_HUAWEI,
				self::TOKEN_TYPE_FIRERBASE_V1,
			])) {
				return true;
			}
		}

		return false;
	}

	/**
	 * проверяем что у пользователя есть зарегистрированный токен для звонков
	 *
	 */
	protected static function _hasVoipToken(Struct_Db_PivotUser_Notification $user_notification_row, array $device_token_row):bool {

		if (count($user_notification_row->device_list) < 1) {
			return false;
		}

		// проверяем что такой пользователь есть в базе и что у него есть токены
		if (!isset($device_token_row["user_id"])) {
			return false;
		}

		$token_list = Type_User_Notifications_DeviceExtra::getTokenList($device_token_row["extra"]);

		// проверям что у пользователя есть токен для переданного устройства
		foreach ($token_list as $item) {

			if (in_array($item["token_type"], [
				self::TOKEN_TYPE_FIREBASE_LEGACY,
				self::TOKEN_TYPE_APNS_MESSAGE,
				self::TOKEN_TYPE_HUAWEI,
				self::TOKEN_TYPE_FIRERBASE_V1,
			])) {
				return true;
			}
		}

		return false;
	}

	/**
	 * доступен ли такой тип звуков
	 *
	 */
	public static function isSoundTypeAllowed(int $sound_type):bool {

		return in_array($sound_type, self::_ALLOW_SOUND_TYPE);
	}

	/**
	 * получаем тип звука для текущего устройства
	 *
	 */
	public static function getSoundType(string $device_id):int {

		try {
			$device_row = Gateway_Db_PivotData_DeviceList::getOne($device_id);
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {
			return self::SOUND_TYPE_1;
		}

		$token_list = Type_User_Notifications_DeviceExtra::getTokenList($device_row["extra"]);
		foreach ($token_list as $token_item) {

			$token_item = self::_getTokenItem($token_item);

			if (in_array($token_item["token_type"], [self::TOKEN_TYPE_FIREBASE_LEGACY, self::TOKEN_TYPE_FIRERBASE_V1, self::TOKEN_TYPE_APNS_MESSAGE])) {
				return $token_item["sound_type"];
			}
		}

		return self::SOUND_TYPE_1;
	}

	/**
	 * метод для установки типа звука по session_uniq
	 *
	 * @throws cs_UserNotHaveToken
	 * @throws \returnException
	 */
	public static function setSoundType(string $device_id, int $sound_type):void {

		Gateway_Db_PivotData_DeviceList::beginTransaction();

		try {
			$device_row = Gateway_Db_PivotData_DeviceList::getForUpdate($device_id);
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {

			Gateway_Db_PivotData_DeviceList::rollback();
			throw new cs_UserNotHaveToken();
		}

		$token_list = Type_User_Notifications_DeviceExtra::getTokenList($device_row["extra"]);

		foreach ($token_list as $index => $token_item) {

			$token_item = self::_getTokenItem($token_item);

			if (in_array($token_item["token_type"],
				[self::TOKEN_TYPE_FIREBASE_LEGACY, self::TOKEN_TYPE_APNS_MESSAGE, self::TOKEN_TYPE_HUAWEI, self::TOKEN_TYPE_FIRERBASE_V1])) {

				$token_item["sound_type"] = $sound_type;
			}

			$token_list[$index] = $token_item;
		}
		$device_extra = Type_User_Notifications_DeviceExtra::setTokenList($device_row["extra"], $token_list);

		// обновляем запись
		Gateway_Db_PivotData_DeviceList::set($device_id, [
			"extra"      => $device_extra,
			"updated_at" => time(),
		]);

		Gateway_Db_PivotData_DeviceList::commitTransaction();
	}

	/**
	 * получаем итем токен по device_id
	 *
	 * @throws cs_UserNotHaveToken
	 * @throws \paramException
	 */
	public static function getTokenByDeviceId(string $device_id, string $user_agent = ""):array {

		// получаем список токенов пользователя из базы
		$app_name = Type_Api_Platform::getAppNameByUserAgent($user_agent);

		try {
			$device_row = Gateway_Db_PivotData_DeviceList::getOne($device_id);
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {
			throw new cs_UserNotHaveToken();
		}

		if (!in_array($app_name, self::_ALLOWED_APP_NAME_LIST)) {
			throw new ParamException("invalid user agent");
		}
		$token_list = Type_User_Notifications_DeviceExtra::getTokenList($device_row["extra"]);

		// ищем токен для отправки пуша
		foreach ($token_list as $token_item) {

			$token_item = self::_getTokenItem($token_item);

			if ((in_array($token_item["token_type"],
					[self::TOKEN_TYPE_FIREBASE_LEGACY, self::TOKEN_TYPE_APNS_MESSAGE, self::TOKEN_TYPE_HUAWEI, self::TOKEN_TYPE_FIRERBASE_V1]))
				&& $token_item["app_name"] == $app_name) {
				return $token_item;
			}
		}

		throw new cs_UserNotHaveToken();
	}

	/**
	 * метод возвращает token_hash от токена
	 *
	 */
	public static function getTokenHash(string $token):string {

		return sha1($token);
	}

	/**
	 * обновляем владельца для токена если дубликат токена
	 *
	 * @throws \queryException|\returnException|\cs_RowIsEmpty
	 */
	public static function updateUserForTokenIfDuplicate(string $token, int $user_id, string $device_id):void {

		$token_hash = self::getTokenHash($token);
		try {
			$token_uniq_row = Gateway_Db_PivotData_DeviceTokenVoipList::getOne($token_hash);
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {

			// если не нашли токен, то добавляем новый
			self::_addTokenUniq($token_hash, $user_id, $device_id);
			return;
		}

		if ($token_uniq_row->device_id == $device_id && $token_uniq_row->user_id == $user_id) {
			return;
		}

		// ставим владельцем токена текущего пользователя и удаляем токен у предыдущего владельца, если токен закреплен за пользователем
		Gateway_Db_PivotData_DeviceTokenVoipList::set($token_hash, [
			"user_id"    => $user_id,
			"device_id"  => $device_id,
			"updated_at" => time(),
		]);

		if ($token_uniq_row->user_id != 0) {
			Type_User_Notifications::deleteTokenForUser($token_uniq_row->user_id, $token);
		}
	}

	/**
	 * добавляем новый хэш токена
	 *
	 * @throws \queryException
	 */
	protected static function _addTokenUniq(string $token_hash, int $user_id, string $device_id):void {

		$token = new Struct_Db_PivotData_DeviceTokenVoipList(
			$token_hash,
			$user_id,
			time(),
			0,
			$device_id,
		);
		Gateway_Db_PivotData_DeviceTokenVoipList::insert($token);
	}

	/**
	 * удаляем определенный токен со всех устройств пользователя
	 *
	 * @throws \returnException|\cs_RowIsEmpty
	 * @long
	 */
	public static function deleteTokenForUser(int $user_id, string $token):void {

		// получаем все девайсы пользователя
		$user_notification = Gateway_Db_PivotUser_NotificationList::getOne($user_id);
		$device_list       = Gateway_Db_PivotData_DeviceList::getAllByDeviceIdList($user_notification->device_list);

		// ищем среди них те, что имеют нужный токен
		$need_device_list = [];
		foreach ($device_list as $device_row) {

			$token_list = Type_User_Notifications_DeviceExtra::getTokenList($device_row["extra"]);

			if (self::_isTokenExist($token_list, $token)) {
				$need_device_list[] = $device_row;
			}
		}

		// если ничего не нашли, то стопим выполнение
		if (count($need_device_list) < 1) {
			return;
		}

		// удаляем из списка токенов устройств нужный токен
		foreach ($need_device_list as $device_row) {

			Gateway_Db_PivotData_DeviceList::beginTransaction();

			try {
				Gateway_Db_PivotData_DeviceList::getForUpdate($device_row["device_id"]);
			} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {

				Gateway_Db_PivotData_DeviceList::rollback();
				continue;
			}

			$new_token_list = [];

			$token_list = Type_User_Notifications_DeviceExtra::getTokenList($device_row["extra"]);
			foreach ($token_list as $token_item) {

				if ($token_item["token"] == $token) {
					continue;
				}

				$new_token_list[] = $token_item;
			}

			$device_extra = Type_User_Notifications_DeviceExtra::setTokenList($device_row["extra"], $new_token_list);

			Gateway_Db_PivotData_DeviceList::set($device_row["device_id"], [
				"extra"      => $device_extra,
				"updated_at" => time(),
			]);

			Gateway_Db_PivotData_DeviceList::commitTransaction();
		}
	}

	/**
	 * добавляем наш токен для пользователя в компании
	 *
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function addUserCompanyPushNotificationToken(string $device_id, int $user_id, string $token, int $company_id):void {

		if (mb_strlen($device_id) < 1) {

			self::_addUserNotificationRowIfNotExist($user_id);
			return;
		}
		self::_addDeviceRowIfNotExist($device_id, $user_id);

		Gateway_Db_PivotData_DeviceList::beginTransaction();

		try {
			$device_row = Gateway_Db_PivotData_DeviceList::getForUpdate($device_id);
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {

			Gateway_Db_PivotData_DeviceList::rollback();
			return;
		}

		$user_company_push_token_list = Type_User_Notifications_DeviceExtra::getUserCompanyPushTokenList($device_row["extra"]);
		$company_id_list              = Type_User_Notifications_DeviceExtra::getCompanyIdList($device_row["extra"]);

		$user_company_push_token_list = self::_addUserCompanyPushTokenInList($user_company_push_token_list, $token);
		$company_id_list              = self::_addUserCompanyIdInList($company_id_list, $company_id);

		$device_row["extra"] = Type_User_Notifications_DeviceExtra::setUserCompanyPushTokenList($device_row["extra"], $user_company_push_token_list);
		$device_row["extra"] = Type_User_Notifications_DeviceExtra::setCompanyIdList($device_row["extra"], $company_id_list);

		Gateway_Db_PivotData_DeviceList::set($device_id, [
			"extra"      => $device_row["extra"],
			"updated_at" => time(),
		]);
		Gateway_Db_PivotData_DeviceList::commitTransaction();
	}

	/**
	 * удаляем компанейский токен для пользователя в компании
	 *
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function deleteUserCompanyPushNotificationToken(string $device_id, int $user_id, string $token, int $company_id):void {

		if (mb_strlen($device_id) < 1) {

			self::_addUserNotificationRowIfNotExist($user_id);
			return;
		}

		Gateway_Db_PivotData_DeviceList::beginTransaction();

		try {
			$device_row = Gateway_Db_PivotData_DeviceList::getForUpdate($device_id);
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {

			Gateway_Db_PivotData_Main::rollback();
			return;
		}

		$device_extra = self::_deleteUserCompanyToken($device_row["extra"], $company_id, $token);

		Gateway_Db_PivotData_DeviceList::set($device_id, [
			"extra"      => $device_extra,
			"updated_at" => time(),
		]);
		Gateway_Db_PivotData_DeviceList::commitTransaction();
	}
	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Удалить команейский токен из экстры
	 *
	 */
	protected static function _deleteUserCompanyToken(array $device_extra, int $company_id, string $token):array {

		$user_company_push_token_list = Type_User_Notifications_DeviceExtra::getUserCompanyPushTokenList($device_extra);
		$key_token                    = array_search($token, $user_company_push_token_list);
		if ($key_token !== false) {

			unset($user_company_push_token_list[$key_token]);
			$user_company_push_token_list = array_values($user_company_push_token_list);
		}

		$company_id_list = Type_User_Notifications_DeviceExtra::getCompanyIdList($device_extra);
		$key_company_id  = array_search($company_id, $company_id_list);
		if ($key_token !== false) {

			unset($company_id_list[$key_company_id]);
			$company_id_list = array_values($company_id_list);
		}
		$device_extra = Type_User_Notifications_DeviceExtra::setUserCompanyPushTokenList($device_extra, $user_company_push_token_list);
		return Type_User_Notifications_DeviceExtra::setCompanyIdList($device_extra, $company_id_list);
	}

	/**
	 * актуализирует структуру token_item
	 *
	 */
	protected static function _getTokenItem(array $token_item):array {

		// сравниваем версию пришедшей token_item с текущей
		if ($token_item["version"] != self::_TOKEN_ITEM_VERSION) {

			// сливаем текущую версию token_item и ту, что пришла
			$token_item            = array_merge(self::_TOKEN_ITEM_SCHEMA[self::_TOKEN_ITEM_VERSION], $token_item);
			$token_item["version"] = self::_TOKEN_ITEM_VERSION;
		}
		return $token_item;
	}

	/**
	 * Удалить iOS девайс с одинаковым voip токеном
	 *
	 */
	public static function deleteDuplicateTokenDevice(string $token, int $user_id, string $device_id, string $user_agent):void {

		$app_name = Type_Api_Platform::getAppNameByUserAgent($user_agent);

		// получаем список девайсов пользователя
		try {
			$user_notification_row = Gateway_Db_PivotUser_NotificationList::getOne($user_id);
		} catch (\cs_RowIsEmpty) {
			return;
		}

		$device_id_list = $user_notification_row->device_list;

		$device_list = Gateway_Db_PivotData_DeviceList::getAllByDeviceIdList($device_id_list);

		// смотрим, если ли среди девайсов юзера с точно таким же voip токеном, и удаляем, если да
		foreach ($device_list as $device) {

			if ($device["device_id"] == $device_id) {
				continue;
			}

			$token_list = Type_User_Notifications_DeviceExtra::getTokenList($device["extra"]);

			foreach ($token_list as $token_item) {

				if ($token_item["token_type"] == self::TOKEN_TYPE_APNS_VIOP && $token_item["token"] == $token && $token_item["app_name"] == $app_name) {

					Gateway_Db_PivotData_DeviceList::delete($device["device_id"]);
					break;
				}
			}
		}
	}
}
