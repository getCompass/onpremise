<?php

namespace Compass\Speaker;

/**
 * В этом классе обрабатываются все события касаемые соединения:
 * - установление соединения
 * - сброс соединения
 */
class Type_Janus_Event_Connection {

	##########################################################
	# region типы событий connection
	##########################################################

	// соединение установлено
	protected const _CONNECTION_WEBRTCUP = "webrtcup";

	// соединение закрыто, случаи:
	// - Electron CMD + R;
	protected const _CONNECTION_HANGUP = "hangup";

	# endregion
	##########################################################

	// точка входа для обработки connection событий
	// @long
	public static function doHandle(array $event_data):void {

		if (!self::_checkCorrectEventFormat($event_data)) {
			return;
		}

		// получаем соединение, если не найдено - то останавливаем выполнение
		$connection_row = Type_Janus_UserConnection::get($event_data["session_id"], $event_data["handle_id"]);
		if (!isset($connection_row["user_id"])) {
			return;
		}

		switch ($event_data["event"]["connection"]) {

			case self::_CONNECTION_WEBRTCUP:

				self::_onWebrtcUp($connection_row, $event_data);
				break;

			case self::_CONNECTION_HANGUP:

				self::_onHangup($connection_row);
				break;
		}
	}

	// функция проверяет, что данные события имеют корректный формат
	// для того чтобы отсеивать ненужные события
	protected static function _checkCorrectEventFormat(array $event_data):bool {

		// если нет информации о переданных данных, то заканчиваем выполнение
		if (!isset($event_data["event"]["connection"])) {
			return false;
		}

		return true;
	}

	##########################################################
	# region onWebrtcUp логика
	##########################################################

	// функция реагирует на событие установления соединения
	protected static function _onWebrtcUp(array $connection_row, array $event_data):void {

		// если соединение в базе еще не установлено
		if ($connection_row["status"] == Type_Janus_UserConnection::STATUS_ESTABLISHING) {

			$is_use_relay = self::_isUseRelay($event_data);
			Type_Janus_UserConnection::afterConnect($connection_row["session_id"], $connection_row["handle_id"], $is_use_relay);
		}

		self::_onEstablishingPub($connection_row, $event_data);

		if ($connection_row["is_publisher"] == 0) {
			return;
		}

		self::_makeAllowToSpeak($connection_row);
	}

	// функция проверяет, использует ли relay сервер установленное соединение
	protected static function _isUseRelay(array $event_data):bool {

		if (!isset($event_data["event"]["selected_pair"])) {
			return true;
		}

		if (!mb_strpos($event_data["event"]["selected_pair"], "relay")) {
			return false;
		}

		return true;
	}

	// при установлении соединения publisher-подключением
	protected static function _onEstablishingPub(array $connection_row, array $event_data):void {

		if ($connection_row["is_publisher"] == 0) {
			return;
		}

		// получаем мету звонка
		$meta_row = Type_Call_Meta::get($connection_row["call_map"]);

		$subscriber_list = Type_Janus_UserConnection::getSubscriberListByPublisherUserId($connection_row["call_map"], $connection_row["user_id"], 16);
		foreach ($subscriber_list as $v) {

			$sub_connection_data = Type_Janus_Node::init($v["node_id"])->getSubConnectionData($v, [
				"is_enabled_audio" => $connection_row["is_send_audio"],
				"is_enabled_video" => $connection_row["is_send_video"],
			], Type_Call_Users::isNeedRelay($meta_row["users"][$v["user_id"]]));
			$sub_connection_data = Apiv1_Format::subConnectionData($sub_connection_data);

			// отправляем ws событие
			Gateway_Bus_Sender::callMemberPublisherEstablished($v["user_id"], $connection_row["user_id"], $v["call_map"], $sub_connection_data);
		}

		// заводим задачу на замер интернет соединения от janus ноды до устройства клиента
		self::_tryStartUserNetworkTraceroute($connection_row["user_id"], $connection_row["call_map"], $connection_row["node_id"], $event_data);
	}

	// запускаем трасировку сети участника звонка
	protected static function _tryStartUserNetworkTraceroute(int $user_id, string $call_map, int $node_id, array $event_data):void {

		try {

			self::_getClientIpAddress($event_data);
		} catch (cs_Client_IpAddressNotFound) {
			Type_System_Admin::log("cs_Client_IpAddressNotFound", toJson($event_data));
		}
	}

	// функция возвращает ip адресс соединившегося участника звонка
	protected static function _getClientIpAddress(array $event_data):string {

		if (!isset($event_data["event"]["selected_pair"])) {
			throw new cs_Client_IpAddressNotFound();
		}

		// сюда приходит строка вида 172.18.0.2:36975 [host,udp] <-> 172.18.0.2:42730 [host,udp]
		// нужно получить правый ip_address
		$selected_pair = $event_data["event"]["selected_pair"];
		$first_explode = explode("<->", $selected_pair);
		if (count($first_explode) < 2) {
			throw new cs_Client_IpAddressNotFound();
		}

		// получаем только ip_адрес из правой части
		$second_explode = explode(":", $first_explode[1]);
		if (count($second_explode) < 2) {
			throw new cs_Client_IpAddressNotFound();
		}

		return trim($second_explode[0]);
	}

	// проверяем, установил ли пользователь все необходимые соединения
	// в случае single диалога – установил pub & sub соединения
	// в случае group диалога – установил pub & все sub соединения к установленным pub соединениям собеседников :zany_face:
	protected static function _isUserConnectionsEstablished(int $user_id, string $call_map, array $user_connection_list):bool {

		// получаем мета звонка
		$meta_row = Type_Call_Meta::get($call_map);

		// если это single звонок, то проверяем, что установлены pub & sub соединения
		if ($meta_row["type"] == CALL_TYPE_SINGLE) {
			return self::_isUserConnectionsEstablishedInSingleCall($user_connection_list);
		}

		return self::_isUserConnectionsEstablishedInGroupCall($user_id, $user_connection_list, $meta_row["users"]);
	}

	// проверяем, установил ли пользователь все необходимые соединения в single диалога
	protected static function _isUserConnectionsEstablishedInSingleCall(array $user_connection_list):bool {

		// если есть хоть один не соединенный
		foreach ($user_connection_list as $item) {

			if ($item["status"] != Type_Janus_UserConnection::STATUS_CONNECTED) {
				return false;
			}
		}

		return true;
	}

	// проверяем, установил ли пользователь все необходимые соединения в group диалоге
	protected static function _isUserConnectionsEstablishedInGroupCall(int $user_id, array $user_connection_list, array $users):bool {

		// если это group звонок, то проверяем, что установлены pub & все sub соединения к установленным pub соединениям собеседников :zany_face:
		// получаем список пользователей, кто точно соединен в данный момент в звонке
		$need_establish_sub_with_user_id = [];
		foreach ($users as $k => $v) {

			// если это наш user_id
			if ($k == $user_id) {
				continue;
			}

			// если пользователь не соединен или его статус не speaking
			if (Type_Call_Users::getStatus($v) != CALL_STATUS_SPEAKING || Type_Call_Users::isLostConnection($k, $users)) {
				continue;
			}

			$need_establish_sub_with_user_id[$k] = $k;
		}

		// пробегаем по соединениям пользователя
		foreach ($user_connection_list as $v) {

			// если соединение не установлено
			if ($v["status"] != Type_Janus_UserConnection::STATUS_CONNECTED) {

				// если это publisher-соединение, то вовсе ливаем с false
				if ($v["is_publisher"] == 1) {
					return false;
				}

				continue;
			}

			// иначе удаляем элемент
			unset($need_establish_sub_with_user_id[$v["publisher_user_id"]]);
		}

		// если массив пуст, значит все subscriber соединения установлены
		return count($need_establish_sub_with_user_id) == 0;
	}

	// получаем pub соединение из списка пользовательских соединений
	protected static function _getPublisherFromUserConnectionList(array $user_connection_list):array {

		foreach ($user_connection_list as $item) {

			if ($item["is_publisher"] == 1) {
				return $item;
			}
		}

		throw new \returnException(__METHOD__ . ": publisher connection not found");
	}

	// даем возможность пользователю вещать в разговорную комнату и помечаем это в базе
	protected static function _makeAllowToSpeak(array $publisher_row):void {

		// вообще очень странная ситуация конечно
		if (!isset($publisher_row["user_id"])) {
			throw new \paramException(__METHOD__ . ": strange case");
		}

		// устанавливаем статус пользователю, что он разговаривает, если это возможно
		try {
			$meta_row = Type_Call_Main::setSpeakingStatus($publisher_row["user_id"], $publisher_row["call_map"]);
		} catch (cs_Call_IsFinished|cs_Call_ActionIsNotAllowed) {
			return;
		}

		// дальше в зависимости от типа звонка
		if ($meta_row["type"] == CALL_TYPE_SINGLE) {
			self::_enableMediaAndSendSpeakStartedInSingleCallIfNeeded($publisher_row["user_id"], $meta_row);
		} else {
			self::_enableMediaAndSendEventsInGroupCall($publisher_row["user_id"], $meta_row, $publisher_row);
		}
	}

	// включаем передачу медиа и отправляем speak_started, если все пользователи соединились
	protected static function _enableMediaAndSendSpeakStartedInSingleCallIfNeeded(int $user_id, array $meta_row):void {

		// если все пользователи уже разговаривают, то отправляем speak_started
		foreach ($meta_row["users"] as $v) {

			$status = Type_Call_Users::getStatus($v);
			if ($status != CALL_STATUS_SPEAKING) {
				return;
			}
		}

		// получаем publisher-соединения - включаем медиа и отправляем speak_started
		$publisher_list = Type_Janus_UserConnection::getPublisherListByCallMap($meta_row["call_map"]);
		foreach ($meta_row["users"] as $k => $v) {

			// включаем медиа
			self::_configureMedia($publisher_list[$k]);

			Gateway_Bus_Company_Rating::inc(Gateway_Bus_Company_Rating::CALL, $k);
			Type_User_ActionAnalytics::send($k, Type_User_ActionAnalytics::ADD_CALL);

			$temp           = Type_Call_Utils::prepareCallForFormat($meta_row, $k);
			$formatted_call = Apiv1_Format::call($temp);
			Gateway_Bus_Sender::callSpeakStarted($k, $formatted_call, $meta_row["call_map"]);
		}
	}

	// отправляем ws события при установлении соединения пользователем в групповом звонке
	protected static function _enableMediaAndSendEventsInGroupCall(int $user_id, array $meta_row, array $publisher_row):void {

		// включаем медиа
		self::_configureMedia($publisher_row);
		self::_isUserAcceptCallForTheFirstTime($meta_row, $user_id);

		$temp           = Type_Call_Utils::prepareCallForFormat($meta_row, $user_id);
		$formatted_call = Apiv1_Format::call($temp);

		Gateway_Bus_Sender::callSpeakStarted($user_id, $formatted_call, $meta_row["call_map"]);

		// отправляем событие всем участникам, что пользователь установил соединение
		$member_list = Type_Call_Users::getMemberList($meta_row["users"]);
		foreach ($member_list as $k => $v) {

			$temp           = Type_Call_Utils::prepareCallForFormat($meta_row, $k);
			$formatted_call = Apiv1_Format::call($temp);
			Gateway_Bus_Sender::callMemberConnectionEstablished($k, $user_id, $meta_row["call_map"], $formatted_call);
		}
	}

	// пишем статистику по звонку, только если позвонили в первый раз
	protected static function _isUserAcceptCallForTheFirstTime(array $meta_row, int $user_id):void {

		// получаем причину завершения звонка
		$finish_reason = Type_Call_Users::getFinishReason($meta_row["users"][$user_id]);

		// проверяем что звонок не был ранее завершен
		if ($finish_reason == CALL_FINISH_REASON_NONE) {

			Gateway_Bus_Company_Rating::inc(Gateway_Bus_Company_Rating::CALL, $user_id);
			Type_User_ActionAnalytics::send($user_id, Type_User_ActionAnalytics::ADD_CALL);
		}
	}

	# endregion
	##########################################################

	##########################################################
	# region onHangup логика
	##########################################################

	// функция реагирует на событие закрытие соединения
	protected static function _onHangup(array $connection_row):void {

		// проверяем, что это потеря соединения
		if (!self::_isConnectionLost($connection_row)) {
			return;
		}

		// если это электрон и групповой звонок, то моментально кладем трубку; иначе помечаем соединение потерянным
		$meta_row   = Type_Call_Meta::get($connection_row["call_map"]);
		$user_agent = Type_Call_Users::getUserAgent($meta_row["users"][$connection_row["user_id"]]);
		if ($meta_row["type"] == CALL_TYPE_GROUP && Type_Api_Platform::getPlatform($user_agent) == Type_Api_Platform::PLATFORM_ELECTRON) {

			try {
				Helper_Calls::doHangup($connection_row["user_id"], $connection_row["call_map"], CALL_FINISH_REASON_LOSE_CONNECTION, "janus_event");
			} catch (cs_Call_IsFinished) {

			}
		} else {

			// если звонок завершен или пользователь уже ливнул из него
			if ($meta_row["is_finished"] == 1 || Type_Call_Users::getRole($meta_row["users"][$connection_row["user_id"]]) == Type_Call_Users::ROLE_LEAVED) {
				return;
			}

			Helper_Janus::onUserConnectionLoss($connection_row);
		}
	}

	// функция детектит пропажу соединения пользователя
	// проверяем ТОЛЬКО publisher соединение - по умолчанию по audio потоку
	protected static function _isConnectionLost(array $connection_row):bool {

		// если пришел event для не паблишера, то скипаем (subscribe соединения не проверяем, мы им не верим)
		if ($connection_row["is_publisher"] != 1) {
			return false;
		}

		// если соединение закрыто вовсе или итак уже потеряно (на основе информации из базы)
		if ($connection_row["status"] == Type_Janus_UserConnection::STATUS_CLOSED
			|| $connection_row["quality_state"] == Type_Janus_UserConnection::QUALITY_STATE_LOST) {
			return false;
		}

		return true;
	}

	# endregion
	##########################################################

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// меняем передачу медиаданных
	protected static function _configureMedia(array $publisher_row):void {

		$params = [
			"audio" => $publisher_row["is_send_audio"] == 1,
			"video" => $publisher_row["is_send_video"] == 1,
		];
		Type_Janus_Node::init($publisher_row["node_id"])->Api->configure($publisher_row["session_id"], $publisher_row["handle_id"], $params);
	}
}
