<?php

namespace Compass\Speaker;

/**
 * класс для работы с информацией о пользовательских соединениях
 * каждый участник звонка любого типа имеет:
 * - как минимум одно sendonly соединение
 * - а также N соединений, где N - количество остальных участников звонка
 */
class Type_Janus_UserConnection {

	// -------------------------------------------------------
	// статус соединения
	// -------------------------------------------------------

	public const STATUS_ESTABLISHING = 0; // установление соединения
	public const STATUS_CONNECTED    = 1; // соединение установленно
	public const STATUS_CLOSED       = 2; // соединение закончено

	// список статусов соединения и тайтлов для каждого из них
	public const STATUS_TITLE_LIST = [
		self::STATUS_ESTABLISHING => "establishing",
		self::STATUS_CONNECTED    => "connected",
		self::STATUS_CLOSED       => "closed",
	];

	// -------------------------------------------------------
	// качество соединения
	// -------------------------------------------------------

	public const QUALITY_STATE_LOST    = 0; // статус, сигнализирующий о потере соединения
	public const QUALITY_STATE_BAD     = 1; // статус для среднего качества связи
	public const QUALITY_STATE_PERFECT = 2; // статус для высокого качества связи

	// список качеств соединения
	public const QUALITY_STATE_LIST = [
		self::QUALITY_STATE_LOST    => "low",
		self::QUALITY_STATE_BAD     => "middle",
		self::QUALITY_STATE_PERFECT => "perfect",
	];

	public const QUALITY_RED_LINE          = 60;  // красная линия качества связи в процентах, после пересещения которой меняем мнение о качестве
	public const BAD_QUALITY_COUNTER_LIMIT = 5;   // лимит bad_quality_counter счетчика, достигнув которого дальнейший инкремент не происходит

	public const CONNECTION_LOST_TIME_OUT = 15;   // время в секундах, через которое считаем пользователя отвалившимся если его клиент не пингует сервер

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	public const CONNECTING_TIMEOUT = 30; // timeout на установление соединения в секундах

	// функция генерирует connection_uuid для publisher соединения
	public static function generateConnectionUUID():string {

		return generateUUID();
	}

	// функция добавляет задачу на отслеживание timeout процесса установления соединения пользователем
	// простым языком — добавляет задачу на мониторинг в крон
	public static function doMonitorEstablishingConnectTimeout(string $call_map, int $user_id):void {

		$insert = [
			"call_map"    => $call_map,
			"user_id"     => $user_id,
			"error_count" => 0,
			"created_at"  => time(),
			"need_work"   => time() + self::CONNECTING_TIMEOUT,
		];
		Gateway_Db_CompanyCall_CallMonitoringEstablishingConnect::insert($insert);
	}

	// функция останавливает отслеживание timeout процесса установления соединения пользователем
	// простым языком — удаляет задачу на мониторинг в крон
	public static function stopMonitorEstablishingConnectTimeout(string $call_map, int $user_id):void {

		Gateway_Db_CompanyCall_CallMonitoringEstablishingConnect::delete($call_map, $user_id);
	}

	// получить информацию о пользовательском соединении
	// по его connection_uuid
	public static function getByUUID(string $connection_uuid):array {

		return Gateway_Db_CompanyCall_JanusConnectionList::getOneByConnectionUUID($connection_uuid);
	}

	// получаем паблишер соединение определенного пользователя
	public static function getPublisherByCallMap(int $user_id, string $call_map):array {

		return Gateway_Db_CompanyCall_JanusConnectionList::getPublisherByCallMap($call_map, $user_id);
	}

	// получить соединение по его session_id & handle_id
	public static function get(int $session_id, int $handle_id):array {

		return Gateway_Db_CompanyCall_JanusConnectionList::get($session_id, $handle_id);
	}

	// обновляем соединение пользователя
	public static function upgradeConnection(int $session_id, int $handle_id, bool $audio, bool $video):void {

		$set = [
			"is_send_audio" => $audio ? 1 : 0,
			"is_send_video" => $video ? 1 : 0,
		];
		Gateway_Db_CompanyCall_JanusConnectionList::set($session_id, $handle_id, $set);
	}

	// инкрементим поле publisher_upgrade_count для сабскрайбер-соединения
	public static function incPublisherUpgradeCount(int $session_id, int $handle_id):void {

		$set = [
			"publisher_upgrade_count" => "publisher_upgrade_count + 1",
		];
		Gateway_Db_CompanyCall_JanusConnectionList::set($session_id, $handle_id, $set);
	}

	// получить все соединения пользователя
	public static function getAllUserConnectionsByCallMap(int $user_id, string $call_map):array {

		return Gateway_Db_CompanyCall_JanusConnectionList::getAllUserConnectionsByCallMap($call_map, $user_id);
	}

	// обновить качество соединения
	public static function updateQuality(int $session_id, int $handle_id, int $connection_quality):void {

		$set = [
			"status"        => self::STATUS_CONNECTED,
			"quality_state" => $connection_quality,
		];
		Gateway_Db_CompanyCall_JanusConnectionList::set($session_id, $handle_id, $set);
	}

	// удалить запись о соединении
	public static function doRemove(int $session_id, int $handle_id):void {

		Gateway_Db_CompanyCall_JanusConnectionList::delete($session_id, $handle_id);
	}

	// получить все publisher-соединения разговора
	public static function getPublisherListByCallMap(string $call_map):array {

		return Gateway_Db_CompanyCall_JanusConnectionList::getPublisherListByCallMap($call_map);
	}

	// получить все подключения комнаты
	// в качестве второго аргумента принимает количество участников звонка в настоящее время
	// для вычисления максимального количества записей при выборке
	public static function getAllByCallMap(string $call_map, int $call_member_count):array {

		$limit = $call_member_count * $call_member_count;
		return Gateway_Db_CompanyCall_JanusConnectionList::getAllByCallMap($call_map, $limit);
	}

	// функция обновляет все необходимые флаги в записи соединения, после осуществления соединения
	public static function afterConnect(int $session_id, int $handle_id, bool $is_use_relay):void {

		Gateway_Db_CompanyCall_JanusConnectionList::set($session_id, $handle_id, [
			"status"       => self::STATUS_CONNECTED,
			"is_use_relay" => $is_use_relay ? 1 : 0,
		]);
	}

	// функция обновляет все необходиомые флаги в записи соединения, после потери соединения
	public static function afterLostConnection(int $session_id, int $handle_id):void {

		Gateway_Db_CompanyCall_JanusConnectionList::set($session_id, $handle_id, [
			"status"        => self::STATUS_CLOSED,
			"quality_state" => self::QUALITY_STATE_LOST,
		]);
	}

	// функция запускает аналитику для соединения, если это нужно
	public static function startAnalyticsIfNeeded(string $call_map, int $user_id, int $report_call_id):void {

		if (!ANALYTICS_IS_ENABLED) {
			return;
		}

		// создаем запись для сбора аналитики
		$task_id = Gateway_Db_CompanyCall_AnalyticQueue::insert($call_map, $user_id);
		if ($task_id < 1) {
			return;
		}

		// пушим задачу, которая будет собирать аналитику для пользователя
		Gateway_Bus_Event::pushTask(Type_Event_Call_GetCallAnalytics::EVENT_TYPE);

		// создаём dynamic запись по аналитике звонка
		Gateway_Db_CompanyCall_AnalyticList::insert($call_map, $user_id, $report_call_id, $task_id);
	}

	// функция обновляет поле last_ping_at
	public static function setLastPingAt(int $session_id, int $handle_id):void {

		Gateway_Db_CompanyCall_JanusConnectionList::set($session_id, $handle_id, [
			"last_ping_at" => time(),
			"updated_at"   => time(),
		]);
	}

	// функция обновляет запись соединения
	public static function set(int $session_id, int $handle_id, array $set):void {

		Gateway_Db_CompanyCall_JanusConnectionList::set($session_id, $handle_id, $set);
	}

	// получаем массив opponents_media_data_list - массив содержащий is_enabled_audio/video флаги
	public static function getOpponentsMediaDataList(array $publisher_list):array {

		$output = [];
		foreach ($publisher_list as $item) {

			$output[$item["user_id"]] = [
				"is_enabled_audio" => $item["is_send_audio"],
				"is_enabled_video" => $item["is_send_video"],
			];
		}

		return $output;
	}

	// получить все subscriber-соединения которые слушают конкретного publisher_user_id
	public static function getSubscriberListByPublisherUserId(string $call_map, int $publisher_user_id, int $number_of_members):array {

		return Gateway_Db_CompanyCall_JanusConnectionList::getSubscriberListByPublisherUserId($call_map, $publisher_user_id, $number_of_members);
	}
}
