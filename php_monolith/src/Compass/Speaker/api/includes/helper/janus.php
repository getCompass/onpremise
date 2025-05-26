<?php

namespace Compass\Speaker;

use JetBrains\PhpStorm\ArrayShape;

/**
 * Helper для работы с сущностью "разговорная комната" в Janus WebRTC Server
 */
class Helper_Janus {

	// функция создает разговорную комнату, если она еще не существует; иначе возвращает существующую
	public static function createRoomIfNotExist(int $user_id, string $call_map, array $meta_row):array {

		// проверяем существование разговорной комнаты
		$room_row = Type_Janus_Room::getByCallMap($call_map);
		if (isset($room_row["call_map"])) {
			return $room_row;
		}

		// определяем оптимальную node_id
		$node_id = Helper_Janus::getOptimalNodeId($meta_row, $user_id);

		// создаем сущность разговорной комнаты в Janus
		$janus_room_info = self::_createRoom($node_id);

		// создаем разговорную комнату, если она не существует
		try {
			$room_row = Type_Janus_Room::insert($call_map, $node_id, $janus_room_info["room_id"], $janus_room_info["session_id"], $janus_room_info["handle_id"]);
		} catch (cs_Janus_CallRoomAlreadyExist) {

			// удаляем созданную нами комнату
			self::_destroyRoom($node_id, $janus_room_info["session_id"], $janus_room_info["handle_id"], $janus_room_info["room_id"]);

			// получаем существующую; если вдруг не существует, то звонок уже завершен
			$room_row = Type_Janus_Room::getByCallMap($call_map);
			if (!isset($room_row["call_map"])) {
				throw new cs_Call_IsFinished();
			}

			return $room_row;
		}

		// вступаем создателем звонка как publisher
		$report_call_id = Type_Call_Meta::getReportCallId($meta_row["extra"]);
		self::joinAsPublisher($meta_row["creator_user_id"], $call_map, $room_row, $report_call_id);

		return $room_row;
	}

	// функция создает сущность разговорной комнаты в Janus
	protected static function _createRoom(int $node_id):array {

		// инициализируем объект для работы с нодой Janus
		$output               = [];
		$output["session_id"] = Type_Janus_Node::init($node_id)->Api->initSession();
		$output["handle_id"]  = Type_Janus_Node::init($node_id)->Api->initPluginHandle($output["session_id"]);

		// создаем комнату
		$output["room_id"] = Type_Janus_Node::init($node_id)->Api->createRoom($output["session_id"], $output["handle_id"]);

		return $output;
	}

	// вступаем в разговорную комнату как publisher; subscriber's
	#[ArrayShape(["publisher_row" => "array", "subscriber_list" => "array", "opponents_media_data_list" => "array"])]
	public static function joinIntoCallRoom(int $user_id, string $call_map, int $report_call_id, array $room_row):array {

		// вступаем как publisher; получаем актуальную мету
		$pub_connection_row = self::joinAsPublisher($user_id, $call_map, $room_row, $report_call_id);
		$meta_row           = Type_Call_Meta::get($call_map);

		// получаем все записи publisher-соединений звонка
		$call_pub_connection_list  = Type_Janus_UserConnection::getPublisherListByCallMap($call_map);
		$opponents_media_data_list = Type_Janus_UserConnection::getOpponentsMediaDataList($call_pub_connection_list);

		// пробегаемся по всем участникам звонка чтобы между новым и существующими участниками создать subscriber соединения
		$sub_connection_list = [];
		$node_id             = $room_row["node_id"];
		$room_id             = $room_row["room_id"];
		foreach ($meta_row["users"] as $k => $v) {

			// пропускаем если набрели на себя или для пользователя еще нет pub-соединения (это редкий случай когда >2 пользователей одновременно приняли звонок
			// и в meta-звонка они уже числятся как участники, но записи с pub-соединением все еще нет)
			if ($k == $user_id || !isset($call_pub_connection_list[$k])) {
				continue;
			}

			// если собеседник не участвует в звонке, то пропускаем
			$status = Type_Call_Users::getStatus($v);
			if (!in_array($status, [CALL_STATUS_ESTABLISHING, CALL_STATUS_SPEAKING])) {
				continue;
			}

			// создаем subscriber-соединения чтобы новый пользователь слышал имеющихся участников и наоборот
			$sub_connection_list[] = self::createSubscriber($user_id, $call_map, $node_id, $pub_connection_row["session_id"], $room_id, $k);
			self::createSubscriber($k, $call_map, $node_id, $call_pub_connection_list[$k]["session_id"], $room_id, $user_id);
		}

		if (count($sub_connection_list) == 0) {
			throw new cs_Call_IsFinished();
		}

		return [
			"publisher_row"             => $pub_connection_row,
			"subscriber_list"           => $sub_connection_list,
			"opponents_media_data_list" => $opponents_media_data_list,
		];
	}

	// удаляем все пользовательские соединения
	public static function doRemoveUserConnectionList(int $user_id, string $call_map, int $number_of_members):void {

		$destroyed_session_list    = [];
		$call_user_connection_list = [];

		Gateway_Db_CompanyCall_Main::beginTransaction();
		$call_connection_list = Type_Janus_UserConnection::getAllByCallMap($call_map, $number_of_members);

		// удаляем из базы записи о соединениях пользователя
		foreach ($call_connection_list as $key => $row) {

			// если соединение никак не связано с нами
			if ($row["user_id"] != $user_id && $row["publisher_user_id"] != $user_id) {

				unset($call_connection_list[$key]);
				continue;
			}

			Type_Janus_UserConnection::doRemove($row["session_id"], $row["handle_id"]);
		}
		Gateway_Db_CompanyCall_Main::commitTransaction();

		// разрываем соединение в janus
		foreach ($call_connection_list as $row) {

			// если соединение нашего пользователя
			if ($row["user_id"] == $user_id) {

				// если такую сессию уже удаляли, то пропускаем
				if (isset($destroyed_session_list[$row["session_id"]])) {
					continue;
				}

				$destroyed_session_list[$row["session_id"]] = $row["session_id"];
				self::_destroySession($row["node_id"], $row["session_id"], $row["handle_id"], $row["room_id"]);
				$call_user_connection_list[] = $row;
			} else {
				self::_detachHandle($row["node_id"], $row["session_id"], $row["handle_id"], $row["room_id"]);
			}
		}

		self::_incStatisticsOfUseRelay($call_user_connection_list);
	}

	// уничтожаем handle соединения в janus
	protected static function _detachHandle(int $node_id, int $session_id, int $handle_id, int $room_id):void {

		try {
			Type_Janus_Node::init($node_id)->Api->detachPluginHandle($session_id, $handle_id);
		} catch (cs_FailedJanusGatewayAPIRequest $e) {
			self::_doLogOnDestroySession($e->getResponse(), $room_id, $session_id, $handle_id);
		}
	}

	// функция для вступления в разговорную комнату пользователем как publisher
	public static function joinAsPublisher(int $user_id, string $call_map, array $room_row, int $report_call_id):array {

		$session_id = Type_Janus_Node::init($room_row["node_id"])->Api->initSession();
		$handle_id  = Type_Janus_Node::init($room_row["node_id"])->Api->initPluginHandle($session_id);

		// так как соединение для publisher, то для него используем participant идентификатор равный user_id в Compass
		$participant_id = $user_id;

		// заносим запись в janus_user_call_connection
		$connection_row = self::_preparePubInsertArrayJanusUserCallConnection($user_id, $call_map, $session_id, $handle_id, $room_row["node_id"],
			$room_row["room_id"], $participant_id);
		Gateway_Db_CompanyCall_JanusConnectionList::insert($connection_row);

		// вступаем в комнату
		Type_Janus_Node::init($room_row["node_id"])->Api->joinRoomAsPublisher($session_id, $handle_id, $room_row["room_id"], $room_row["bitrate"], $participant_id);

		// запускаем аналитику
		Type_Janus_UserConnection::startAnalyticsIfNeeded($call_map, $user_id, $report_call_id);

		return $connection_row;
	}

	// функция подготавливает insert массив для publisher соединения в таблицу janus_user_call_connection
	protected static function _preparePubInsertArrayJanusUserCallConnection(int $user_id, string $call_map, int $session_id, int $handle_id, int $node_id, int $room_id, int $participant_id):array {

		return [
			"session_id"        => $session_id,
			"handle_id"         => $handle_id,
			"user_id"           => $user_id,
			"connection_uuid"   => Type_Janus_UserConnection::generateConnectionUUID(),
			"status"            => Type_Janus_UserConnection::STATUS_ESTABLISHING,
			"quality_state"     => Type_Janus_UserConnection::QUALITY_STATE_PERFECT,
			"is_publisher"      => 1,
			"is_send_video"     => 0,
			"is_send_audio"     => 1,
			"node_id"           => $node_id,
			"call_map"          => $call_map,
			"room_id"           => $room_id,
			"participant_id"    => $participant_id,
			"created_at"        => time(),
			"updated_at"        => 0,
			"publisher_user_id" => 0,
		];
	}

	// получаем оптимальную ноду
	public static function getOptimalNodeId(array $meta_row, int $user_id):int {

		$ping_result = [];
		foreach ($meta_row["users"] as $k => $_) {

			if ($meta_row["creator_user_id"] == $k || $user_id == $k) {
				$ping_result[] = Type_Call_Users::getPingResult($k, $meta_row["users"]);
			}
		}

		// получаем ноду исходя из результатов
		$janus_config = Type_Call_Config::getJanusList();
		return Type_Janus_Node::getOptimalNode($ping_result[0], $ping_result[1], $janus_config);
	}

	// функция для создания сабскрайбер-соединений
	public static function createSubscriber(int $user_id, string $call_map, int $node_id, int $session_id, int $room_id, int $publisher_user_id):array {

		$handle_id = Type_Janus_Node::init($node_id)->Api->initPluginHandle($session_id);

		// так как соединение для subscriber, то для него генерируем случайный идентификатор
		$participant_id = self::_genSubParticipantID();

		// заносим запись в janus_user_call_connection
		$insert = self::_prepareSubInsertArrayJanusUserCallConnection(
			$user_id,
			$call_map,
			$session_id,
			$handle_id,
			$node_id,
			$room_id,
			$participant_id,
			$publisher_user_id
		);
		Gateway_Db_CompanyCall_JanusConnectionList::insert($insert);

		return $insert;
	}

	// функция подготавливает insert массив для subscriber соединения в таблицу janus_user_call_connection
	protected static function _prepareSubInsertArrayJanusUserCallConnection(int $user_id, string $call_map, int $session_id, int $handle_id, int $node_id, int $room_id, int $participant_id, int $publisher_user_id):array {

		return [
			"session_id"        => $session_id,
			"handle_id"         => $handle_id,
			"user_id"           => $user_id,
			"connection_uuid"   => Type_Janus_UserConnection::generateConnectionUUID(),
			"status"            => Type_Janus_UserConnection::STATUS_ESTABLISHING,
			"quality_state"     => Type_Janus_UserConnection::QUALITY_STATE_PERFECT,
			"is_publisher"      => 0,
			"is_send_video"     => 0,
			"is_send_audio"     => 0,
			"node_id"           => $node_id,
			"call_map"          => $call_map,
			"room_id"           => $room_id,
			"participant_id"    => $participant_id,
			"created_at"        => time(),
			"updated_at"        => 0,
			"publisher_user_id" => $publisher_user_id,
		];
	}

	// функция уничтожает все сессии и соединения разговорной комнаты
	public static function destroyRoomAndConnections(string $call_map, int $call_members_count):void {

		$room_row = Type_Janus_Room::getByCallMap($call_map);
		if (!isset($room_row["node_id"])) {
			return;
		}

		self::_deleteAllConnections($call_map, $call_members_count);

		// пытаемся уничтожить комнату (если вернулась ошибка - логируем и выясняем)
		self::_destroyRoom($room_row["node_id"], $room_row["session_id"], $room_row["handle_id"], $room_row["room_id"]);
	}

	// удаляем все соединения
	protected static function _deleteAllConnections(string $call_map, int $call_members_count):void {

		Gateway_Db_CompanyCall_Main::beginTransaction();
		$destroyed_session_list    = [];
		$call_user_connection_list = Type_Janus_UserConnection::getAllByCallMap($call_map, $call_members_count);
		foreach ($call_user_connection_list as $row) {

			// удаляем запись и уничтожаем сессию соединения, если не сделали это ранее
			Type_Janus_UserConnection::doRemove($row["session_id"], $row["handle_id"]);
			if (isset($destroyed_session_list[$row["session_id"]])) {
				continue;
			}
			self::_destroySession($row["node_id"], $row["session_id"], $row["handle_id"], $row["room_id"]);
			$destroyed_session_list[$row["session_id"]] = $row["session_id"];
		}
		Gateway_Db_CompanyCall_Main::commitTransaction();

		// ведем статистику по частоте использования relay серверов
		self::_incStatisticsOfUseRelay($call_user_connection_list);
	}

	// функция инкрементит статистику насколько часто используются relay-сервера
	protected static function _incStatisticsOfUseRelay(array $call_user_connection_list):void {

		// считаем статистику
		$row_list = self::_countUserConnectionStatistics($call_user_connection_list);

		// инкрементим
		Gateway_Bus_Statholder::inc("calls", "row320", count($call_user_connection_list)); // connection
		Gateway_Bus_Statholder::inc("calls", "row321", $row_list["publishers_count"]); // publisher
		Gateway_Bus_Statholder::inc("calls", "row322", count($call_user_connection_list) - $row_list["publishers_count"]); // subscriber
		Gateway_Bus_Statholder::inc("calls", "row323", $row_list["relay_uses_count"]); // relay
		Gateway_Bus_Statholder::inc("calls", "row324", $row_list["publisher_relay_uses_count"]); // relay publisher
		Gateway_Bus_Statholder::inc("calls", "row325", $row_list["relay_uses_count"] - $row_list["publisher_relay_uses_count"]); // relay subscriber
		Gateway_Bus_Statholder::inc("calls", "row326", count($call_user_connection_list) - $row_list["relay_uses_count"]); // no relay
		Gateway_Bus_Statholder::inc("calls", "row327", $row_list["publishers_count"] - $row_list["publisher_relay_uses_count"]); // publisher no relay
		Gateway_Bus_Statholder::inc("calls", "row328", (count($call_user_connection_list) - $row_list["publishers_count"]) -
			($row_list["relay_uses_count"] - $row_list["publisher_relay_uses_count"])); // subscriber no relay
	}

	// функция подсчитывает статистику по соединениям
	#[ArrayShape(["publishers_count" => "int", "relay_uses_count" => "int", "publisher_relay_uses_count" => "int"])]
	protected static function _countUserConnectionStatistics(array $call_user_connection_list):array {

		$row_list = [
			"publishers_count"           => 0,
			"relay_uses_count"           => 0,
			"publisher_relay_uses_count" => 0,
		];
		foreach ($call_user_connection_list as $item) {

			if ($item["is_publisher"] == 1) {
				$row_list["publishers_count"]++;
			}

			if ($item["is_use_relay"] == 1) {

				$row_list["relay_uses_count"]++;
				if ($item["is_publisher"] == 1) {
					$row_list["publisher_relay_uses_count"]++;
				}
			}
		}

		return $row_list;
	}

	/**
	 * функция переключает передаваемые пользователем медиа данные
	 *
	 * @throws cs_FailedJanusGatewayAPIRequest
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function doUpgradeConnection(int $user_id, string $call_map, int $session_id, int $handle_id, bool $audio, bool $video, array $offer, int $node_id, int $call_members_count):void {

		// проходимся по всем соединениям и проинкрементим значение publisher_upgrade_count для всех сабов текущего пользователя
		self::_incPublisherUpgradeCountForSubs($user_id, $call_map, $call_members_count);

		// отправляем запрос для изменения соединения
		Type_Janus_Node::init($node_id)->Api->makeConfigure($session_id, $handle_id, $audio, $video, $offer);

		// обновляем соединение пользователя
		Type_Janus_UserConnection::upgradeConnection($session_id, $handle_id, $audio, $video);

		// получаем мету звонка
		$meta_row    = Type_Call_Meta::get($call_map);
		$member_list = Type_Call_Users::getMemberList($meta_row["users"]);

		// отправляем собеседникам событие о переключении аудио/видео
		self::_sendEventOnUpgradeConnection($user_id, $call_map, $member_list, $audio, $video);

		// если это групповой диалог, то пересчитываем bitrate для комнаты
		if ($meta_row["type"] == CALL_TYPE_GROUP) {
			Type_Phphooker_Main::changeCallBitrateIfNeeded($call_map);
		}
	}

	// функция проходится по всем соединениям и инкрементит значение publisher_upgrade_count для сабов переключаемого пользователя
	protected static function _incPublisherUpgradeCountForSubs(int $publisher_user_id, string $call_map, int $call_members_count):void {

		// проходимся по всем соединениям и инкрементим значение publisher_upgrade_count
		$user_connection_list = Type_Janus_UserConnection::getAllByCallMap($call_map, $call_members_count);
		foreach ($user_connection_list as $v) {

			// если соединение не является сабскрайбером для текущего пользователя
			if ($v["publisher_user_id"] != $publisher_user_id) {
				continue;
			}

			Type_Janus_UserConnection::incPublisherUpgradeCount($v["session_id"], $v["handle_id"]);
		}
	}

	// функция отправляет участникам звонка событие о переключении аудио/видео пользователя
	protected static function _sendEventOnUpgradeConnection(int $user_id, string $call_map, array $member_list, bool $audio, bool $video):void {

		// отправляем WS события о вкл/откл аудио/видео собеседником
		$talking_user_list = [];
		foreach ($member_list as $k => $v) {

			if ($user_id == $k) {
				continue;
			}
			$talking_user_list[] = Gateway_Bus_Sender::makeTalkingUserItem($k, false);
		}
		Gateway_Bus_Sender::callMediaChanged($talking_user_list, $call_map, $user_id, $audio, $video);
	}

	// функция для смены битрейта комнаты
	public static function doChangeBitrate(string $call_map, array $room_row, int $bitrate):void {

		// обновляем битрейт комнаты
		$set = [
			"bitrate"    => $bitrate,
			"updated_at" => time(),
		];
		Type_Janus_Room::updateRoom($room_row["room_id"], $set);

		// редактируем комнату, указывая актуальный bitrate
		$params = [
			"new_bitrate" => (int) $bitrate,
			"room"        => (int) $room_row["room_id"],
		];
		Type_Janus_Node::init($room_row["node_id"])->Api->editRoom($room_row["session_id"], $room_row["handle_id"], $params);

		// меняем битрей для участников звонка
		self::_changeBitrateForMembersOfCall($call_map, $bitrate, $room_row["node_id"]);
	}

	// меняем битрей для участников звонка
	protected static function _changeBitrateForMembersOfCall(string $call_map, int $bitrate, int $node_id):void {

		// получаем все pub-соединения звонка
		$connection_list = Type_Janus_UserConnection::getPublisherListByCallMap($call_map);

		// для каждого паблишера обновляем bitrate
		$params = [
			"bitrate" => $bitrate,
		];
		foreach ($connection_list as $connection_row) {
			Type_Janus_Node::init($node_id)->Api->configure($connection_row["session_id"], $connection_row["handle_id"], $params);
		}
	}

	// функция высчитывает bitrate
	public static function doCountOptimalBitrate(array $publisher_list, array $users, int $current_bitrate):int {

		$numbers_of_enabled_video = 0;
		$numbers_of_enabled_audio = count($publisher_list);
		foreach ($publisher_list as $v) {

			if (!Type_Call_Users::isMember($v["user_id"], $users)) {
				continue;
			}

			// инкрементим количество медиа включенное в разговоре
			if ($v["is_send_video"] == 1) {
				$numbers_of_enabled_video++;
			}
		}

		// высчитываем оптимальную ширину канала
		return self::_recountOptimalBitrate($current_bitrate, $numbers_of_enabled_audio, $numbers_of_enabled_video);
	}

	// высчитываем новый оптимальный bitrate — чтобы он был меньше оптимальной пропускной способности и был самый оптимальный для ситуации
	protected static function _recountOptimalBitrate(int $current_bitrate, int $numbers_of_enabled_audio, int $numbers_of_enabled_video):int {

		$bitrate_list = [];
		foreach (JANUS_ROOM_BITRATE_LIST as $v) {

			// если bandwidth превышает оптимальный
			$bandwidth = self::_doCountBandwidth($v, $numbers_of_enabled_video, $numbers_of_enabled_audio);
			if ($bandwidth > JANUS_CLIENT_OPTIMAL_BANDWIDTH) {
				continue;
			}

			// добавляем его в массивчик
			$bitrate_list[] = $v;
		}

		// если вдруг не оказалось ничего в массивчике — возвращаем текущее
		if (count($bitrate_list) == 0) {
			return $current_bitrate;
		}

		return max($bitrate_list);
	}

	// функция считает bandwidth на основе bitrate и количества включенных медиа
	protected static function _doCountBandwidth(int $bitrate, int $numbers_of_enabled_video, int $numbers_of_enabled_audio):int {

		// считаем ширину канала; 70000 - bitsPerSecond для аудио
		return $numbers_of_enabled_video * $bitrate + $numbers_of_enabled_audio * 70000;
	}

	// функция помечает пользователя, что он потерял соединение
	public static function onUserConnectionLoss(array $publisher_row):void {

		// помечаем подключение — потерявшим соединение
		Type_Janus_UserConnection::afterLostConnection($publisher_row["session_id"], $publisher_row["handle_id"]);
		$meta_row = Type_Call_Meta::setUserLostConnection($publisher_row["call_map"], $publisher_row["user_id"], true);

		// если потерял соединение на этапе установления подключения
		if (Type_Call_Users::getStatus($meta_row["users"][$publisher_row["user_id"]]) === CALL_STATUS_ESTABLISHING) {

			$ip_address = Type_Call_Users::getIpAddress($meta_row["users"][$publisher_row["user_id"]]);
			Type_Call_ConnectionIssue::save($ip_address);
		}

		Type_Janus_UserConnection::doMonitorEstablishingConnectTimeout($publisher_row["call_map"], $publisher_row["user_id"]);

		// отправляем ws-ивент о потере соединения
		self::_sendEventAboutLostConnection($publisher_row["user_id"], $publisher_row["connection_uuid"], $meta_row);
	}

	// отправляем ws-ивент о потере соединения
	protected static function _sendEventAboutLostConnection(int $user_id, string $connection_uuid, array $meta_row):void {

		// отправляем участникам звонка ws-ивент о том, что соединение потеряно
		$talking_user_list = [];
		foreach ($meta_row["users"] as $k => $v) {
			$talking_user_list[] = Gateway_Bus_Sender::makeTalkingUserItem($k, false);
		}
		Gateway_Bus_Sender::callConnectionLost($talking_user_list, $meta_row["call_map"], $user_id, $connection_uuid, 1);
	}

	/**
	 * разговорная комната существует?
	 *
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function isRoomExists(array $room_row):bool {

		$params = [
			"room" => (int) $room_row["room_id"],
		];
		return Type_Janus_Node::init($room_row["node_id"])->Api->isRoomExists($room_row["session_id"], $room_row["handle_id"], $params);
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	// функция генерирует случайный идентификатор для subscribe участника
	protected static function _genSubParticipantID():int {

		return random_int(1, 999999999);
	}

	// уничтожаем сессию пользователя в janus
	protected static function _destroySession(int $node_id, int $session_id, int $handle_id, int $room_id):void {

		try {
			Type_Janus_Node::init($node_id)->Api->destroySession($session_id);
		} catch (cs_FailedJanusGatewayAPIRequest $e) {
			self::_doLogOnDestroySession($e->getResponse(), $room_id, $session_id, $handle_id);
		}
	}

	// функция логирует ошибку при destroy session
	protected static function _doLogOnDestroySession(array $janus_response, int $room_id, int $session_id, int $handle_id):void {

		Type_System_Admin::log("destroy_session", [
			"janus_response"        => $janus_response,
			"connection_session_id" => $session_id,
			"connection_handle_id"  => $handle_id,
			"room_id"               => $room_id,
		]);
	}

	// уничтожаем комнату и сессию через которую была создана комната в janus
	protected static function _destroyRoom(int $node_id, int $session_id, int $handle_id, int $room_id):void {

		try {

			Type_Janus_Node::init($node_id)->Api->destroyRoom($session_id, $handle_id, $room_id);
			Type_Janus_Node::init($node_id)->Api->destroySession($session_id);
		} catch (cs_FailedJanusGatewayAPIRequest $e) {
			self::_doLogOnDestroyRoomFail($e->getResponse(), $node_id, $session_id, $handle_id, $room_id);
		}
	}

	// функция логирует ошибку при destroy room
	protected static function _doLogOnDestroyRoomFail(array $janus_response, int $node_id, int $session_id, int $handle_id, int $room_id):void {

		Type_System_Admin::log("destroy_room_fail", [
			"node_id"         => $node_id,
			"janus_response"  => $janus_response,
			"room_session_id" => $session_id,
			"room_handle_id"  => $handle_id,
			"room_id"         => $room_id,
		]);
	}
}