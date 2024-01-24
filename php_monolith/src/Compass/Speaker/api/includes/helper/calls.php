<?php

namespace Compass\Speaker;

/**
 * Helper для всего, что связано со звонками
 */
class Helper_Calls {

	// -------------------------------------------------------
	// SINGLE-ЗВОНКИ
	// -------------------------------------------------------

	/**
	 * Функция пытается инициализировать single-звонок
	 *
	 * @return array
	 * @throws Domain_Member_Exception_ActionNotAllowed
	 * @throws Domain_Member_Exception_AttemptInitialCall
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws cs_Call_ConversationNotExist
	 * @throws cs_Call_MemberIsDisabled
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function tryInit(int $user_id, int $method_version, string $session_uniq, string $ip_address, int $opponent_user_id):array {

		// совершаем проверку — может ли user_id позвонить opponent_user_id
		$conversation_map = Gateway_Socket_Conversation::checkIsAllowedForCall($user_id, $opponent_user_id, $method_version);

		// пытаемся инициализировать звонок; проверяем что телефонные линии обоих пользователей не заняты
		// создаем мету звонка
		// здесь выбрасывается исключение cs_Call_LineIsBusy, которое отлавливается в контроллере
		$meta_row = Type_Call_Main::tryInit($user_id, $opponent_user_id, $session_uniq, $ip_address, getUa(), getDeviceId(), $conversation_map);

		// добавляем задачу в phphooker для отправки в диалог сообщения для звонка
		Type_Phphooker_Main::addCallMessage($conversation_map, $meta_row["call_map"], $user_id);

		// начинаем отслеживать этап гудков, чтобы он не длился дольше N секунд — после чего сбрасываем трубку
		Type_Call_Main::addToDialingQueue($opponent_user_id, $meta_row["call_map"]);

		// получаем список с нодами, которые включены и доступны для новых звонков
		$node_list = Type_Call_Config::getJanusAvailableNodes();

		// отправляем события иницатору звонка
		self::_sendInitiatorEventsOnCallInit($user_id, $meta_row);

		// отправляем события собеседнику
		self::_sendOpponentEventsOnCallInit($user_id, $opponent_user_id, $meta_row, $node_list);

		// инкрементим количество звонков
		Domain_User_Action_IncActionCount::incCall($user_id, $conversation_map);

		// закрываем микро-диалог
		Domain_User_Action_MessageAnswerTime::closeMicroConversation(Type_Pack_Conversation::doEncrypt($conversation_map), $user_id, [$opponent_user_id]);

		return [$meta_row, $node_list];
	}

	// функция отправляет события об инициации звонка инициатору
	protected static function _sendInitiatorEventsOnCallInit(int $user_id, array $meta_row):void {

		$temp           = Type_Call_Utils::prepareCallForFormat($meta_row, $user_id);
		$formatted_call = Apiv1_Format::call($temp);

		Gateway_Bus_Sender::callInited($user_id, $formatted_call);
	}

	// функция отправляет события о входящем звонке собеседнику
	protected static function _sendOpponentEventsOnCallInit(int $user_id, int $opponent_user_id, array $meta_row, array $node_list):void {

		// подготавливаем объект call
		$temp           = Type_Call_Utils::prepareCallForFormat($meta_row, $opponent_user_id);
		$formatted_call = Apiv1_Format::call($temp);

		// отправляем VoIP пуш
		$status    = Type_Call_Users::getStatus($meta_row["users"][$user_id]);
		$push_data = Gateway_Bus_Pusher::makeVoIPPushData(
			$formatted_call,
			Type_Call_Utils::getVoIPPushBody($status, $node_list, $user_id),
		);

		Gateway_Bus_Sender::sendIncomingCall($opponent_user_id, $user_id, $formatted_call, $push_data, $node_list, [$user_id]);
	}

	/**
	 * функция принимает входящий звонок single-звонка
	 *
	 * @return array
	 * @throws cs_Call_IsFinished
	 * @throws cs_Call_LineIsBusy
	 * @throws cs_Call_UserAlreadyAcceptedCall
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function tryAccept(int $user_id, string $user_session_uniq, string $ip_address, string $call_map):array {

		// помечаем, что single звонок принят от лица пользователя
		$meta_row = Type_Call_Main::tryAccept($call_map, $user_session_uniq, $ip_address, getUa(), getDeviceId(), $user_id);

		// удаляем задачу из крона с dialing звонками и добавляем задачу на отслеживание установки соединения
		Type_Janus_UserConnection::doMonitorEstablishingConnectTimeout($call_map, $user_id);
		Type_Call_Main::removeFromDialingQueue($user_id, $call_map);
		Type_Janus_UserConnection::doMonitorEstablishingConnectTimeout($call_map, $meta_row["creator_user_id"]);

		// отправляем WS всем участникам
		$member_list       = Type_Call_Users::getMemberList($meta_row["users"]);
		$user_id_list      = array_keys($member_list);
		$talking_user_list = Type_Call_Users::makeTalkingUserList($user_id_list);
		Gateway_Bus_Sender::callAccepted($talking_user_list, $user_id, $call_map);

		// если это групповой диалог, то пересчитываем bitrate для комнаты
		if ($meta_row["type"] == CALL_TYPE_GROUP) {
			Type_Phphooker_Main::changeCallBitrateIfNeeded($call_map);
		}

		// инкрементим количество звонков
		$conversation_map = Type_Call_Users::getConversationMap($user_id, $meta_row["users"]);
		if (mb_strlen($conversation_map) > 0) {
			Domain_User_Action_IncActionCount::incCall($user_id, $conversation_map);
		}

		return $meta_row;
	}

	// кладем трубку от лица пользователя
	public static function doHangup(int $user_id, string $call_map, int $finish_reason, string $finish_subject = "platform"):array {

		$meta_row = Type_Call_Main::doHangup($user_id, $call_map, $finish_reason, $finish_subject);

		// в зависимости от текущего состояния звонка завершаем работу метода
		if ($meta_row["is_finished"] == 1) {
			self::_doActionOnFinishCall($user_id, $meta_row);
		} else {
			self::_doActionOnHangupCall($user_id, $meta_row, true);
		}

		return $meta_row;
	}

	// совершаем действия когда звонок завершается
	protected static function _doActionOnFinishCall(int $user_id, array $meta_row):void {

		// отправляем WS событие и VoIP-пуш о завершении звонка
		self::_sendEventsOnFinishCall($user_id, $meta_row);

		// отправляем задачу phphooker'у на добавление записи в историю звонков участников
		Type_Phphooker_Main::insertToHistory(
			$meta_row["creator_user_id"],
			$meta_row["call_map"],
			$meta_row["type"],
			$meta_row["users"]
		);

		// уничтожаем все janus сессии инициализированные для звонка
		$call_members_count = Type_Call_Users::getCount($meta_row["users"]);
		Helper_Janus::destroyRoomAndConnections($meta_row["call_map"], $call_members_count);

		// удаляем задачу из крона с dialing звонками
		Gateway_Db_CompanyCall_CallMonitoringDialing::deleteByCallMap($meta_row["call_map"]);

		// удаляем задачу из крона establishing_monitoring
		Gateway_Db_CompanyCall_CallMonitoringEstablishingConnect::deleteByCallMap($meta_row["call_map"]);
	}

	// отправляем WS событие и VoIP-пуш о завершении звонка
	protected static function _sendEventsOnFinishCall(int $user_id, array $meta_row):void {

		$member_list           = Type_Call_Users::getMemberList($meta_row["users"]);
		$member_list[$user_id] = $meta_row["users"][$user_id];
		foreach ($member_list as $k => $v) {

			// отправляем ws событие
			$temp           = Type_Call_Utils::prepareCallForFormat($meta_row, $k);
			$formatted_call = Apiv1_Format::call($temp);
			Gateway_Bus_Sender::callFinished($k, $formatted_call);

			// формируем пуш
			$status    = Type_Call_Users::getStatus($v);
			$push_data = Gateway_Bus_Pusher::makeVoIPPushData($formatted_call, Type_Call_Utils::getVoIPPushBody($status), false);
			Gateway_Bus_Sender::sendVoIPPush($k, $push_data);
		}
	}

	// совершаем действия когда пользователь кладет трубку
	protected static function _doActionOnHangupCall(int $user_id, array $meta_row, bool $is_need_kick):void {

		// отправляем WS событие и VoIP-пуш о том что пользователь положил трубку
		self::_sendEventsOnHangupCall($user_id, $meta_row, $is_need_kick);

		// уничтожаем janus сессии инициализированные для пользователя
		$call_members_count = Type_Call_Users::getCount($meta_row["users"]);
		Helper_Janus::doRemoveUserConnectionList($user_id, $meta_row["call_map"], $call_members_count);

		// удаляем задачу из крона с dialing звонком
		Gateway_Db_CompanyCall_CallMonitoringDialing::delete($user_id, $meta_row["call_map"]);

		// удаляем задачу из крона establishing_monitoring
		Gateway_Db_CompanyCall_CallMonitoringEstablishingConnect::delete($meta_row["call_map"], $user_id);

		// добавляем задачу на обновление bitrate разговорной комнаты
		Type_Phphooker_Main::changeCallBitrateIfNeeded($meta_row["call_map"]);
	}

	// отправляем WS событие и VoIP-пуш о том что пользователь положил трубку
	protected static function _sendEventsOnHangupCall(int $user_id, array $meta_row, bool $is_need_kick):void {

		$member_list           = Type_Call_Users::getMemberList($meta_row["users"]);
		$member_list[$user_id] = $meta_row["users"][$user_id];
		foreach ($member_list as $k => $v) {

			$temp           = Type_Call_Utils::prepareCallForFormat($meta_row, $k);
			$formatted_call = Apiv1_Format::call($temp);

			// если кикнули
			if ($is_need_kick && $k == $user_id) {
				Gateway_Bus_Sender::callFinished($k, $formatted_call);
			} else {

				Gateway_Bus_Sender::callHangup($k, $user_id, $meta_row["call_map"], $formatted_call);
				Gateway_Bus_Sender::callMemberKicked($k, $user_id, $user_id, $meta_row["call_map"], $formatted_call);
			}
		}

		// формируем пуш
		$temp           = Type_Call_Utils::prepareCallForFormat($meta_row, $user_id);
		$formatted_call = Apiv1_Format::call($temp);
		$user_schema    = $meta_row["users"][$user_id];
		$status         = Type_Call_Users::getStatus($user_schema);
		$push_data      = Gateway_Bus_Pusher::makeVoIPPushData($formatted_call, Type_Call_Utils::getVoIPPushBody($status), false);
		Gateway_Bus_Sender::sendVoIPPush($user_id, $push_data);
	}

	// приглашаем пользователя в звонок
	public static function tryInvite(int $user_id, int $opponent_user_id, string $call_map, bool $is_need_send_message, int $method_version):array {

		// совершаем проверку — может ли user_id позвонить opponent_user_id
		$conversation_map = Gateway_Socket_Conversation::checkIsAllowedForCall($user_id, $opponent_user_id, $method_version);

		// приглашаем пользователя, проверяя что его линия не занята
		$meta_row = Type_Call_Main::tryInvite($user_id, $opponent_user_id, $call_map, $conversation_map);

		// добавляем задачу в phphooker для отправки в диалог сообщения для звонка, если нужно
		if ($is_need_send_message) {
			Type_Phphooker_Main::addCallMessage($conversation_map, $meta_row["call_map"], $user_id);
		}

		// начинаем отслеживать этап гудков, чтобы он не длился дольше N секунд — после чего сбрасываем трубку
		Type_Call_Main::addToDialingQueue($opponent_user_id, $call_map);

		// отправляем события участникам звонка
		self::_sendMembersEventsOnInvite($user_id, $opponent_user_id, $call_map, $meta_row);

		// отправляем события пользователю, которого приглашаем
		self::_sendOpponentEventsOnInvite($opponent_user_id, $user_id, $meta_row);

		return $meta_row;
	}

	// отправляем событие о приглашении пользоваетля всем участникам звонка
	protected static function _sendMembersEventsOnInvite(int $user_id, int $opponent_user_id, string $call_map, array $meta_row):void {

		$member_list = Type_Call_Users::getMemberList($meta_row["users"]);
		foreach ($member_list as $k => $v) {

			$temp           = Type_Call_Utils::prepareCallForFormat($meta_row, $k);
			$formatted_call = Apiv1_Format::call($temp);
			Gateway_Bus_Sender::callMemberInvited($k, $user_id, $opponent_user_id, $call_map, $formatted_call);
		}
	}

	// отправляем событие о входящем звонке пользователю
	protected static function _sendOpponentEventsOnInvite(int $user_id, int $caller_user_id, array $meta_row):void {

		// подготавливаем объект call
		$temp = Type_Call_Utils::prepareCallForFormat($meta_row, $user_id);

		// только в том случае, если это приглашение в групповой звонок!
		// подменяем creator_user_id на caller_user_id, чтобы отобразилось имя того пользователя, кто звонит!
		// так нужно делать потому что мобильные клиенты отображают в окно о входящем звонке имя собеседника под creator_user_id
		$temp["creator_user_id"] = $caller_user_id;

		$formatted_call = Apiv1_Format::call($temp);

		$member_list = Type_Call_Users::getMemberList($meta_row["users"]);
		Gateway_Bus_Sender::sendIncomingCallEvent($user_id, $caller_user_id, $formatted_call, [], array_keys($member_list));
	}

	// исключаем пользователя из участников звонка
	public static function tryKick(int $user_id, int $opponent_user_id, string $call_map, int $finish_reason):array {

		// кладем трубочку
		$meta_row = self::doHangup($opponent_user_id, $call_map, $finish_reason);

		// отправляем событие участникам звонка
		self::_sendMembersEventsOnKick($user_id, $opponent_user_id, $call_map, $meta_row);

		return $meta_row;
	}

	// отправляем событие об исключении пользователя из звонка
	protected static function _sendMembersEventsOnKick(int $user_id, int $opponent_user_id, string $call_map, array $meta_row):void {

		$member_list                    = Type_Call_Users::getMemberList($meta_row["users"]);
		$member_list[$opponent_user_id] = $meta_row["users"][$user_id];
		foreach ($member_list as $k => $v) {

			$temp           = Type_Call_Utils::prepareCallForFormat($meta_row, $k);
			$formatted_call = Apiv1_Format::call($temp);
			Gateway_Bus_Sender::callMemberKicked($k, $user_id, $opponent_user_id, $call_map, $formatted_call);
		}
	}
}
