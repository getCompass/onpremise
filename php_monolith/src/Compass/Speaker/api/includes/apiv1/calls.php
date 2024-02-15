<?php

namespace Compass\Speaker;

use CompassApp\Domain\Member\Entity\Permission;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use BaseFrame\Exception\Request\ParamException;

/**
 * контроллер для работы звонков
 */
class ApiV1_Calls extends \BaseFrame\Controller\Api {

	// Поддерживаемые методы. Регистр не имеет значение */
	public const ALLOW_METHODS = [
		"tryInit",
		"doSendPingResult",
		"doAccept",
		"doHangUp",
		"get",
		"getBatching",
		"getPreferences",
		"getOpponentsCondition",
		"doUpgradeConnection",
		"doReportOnConnection",
		"doPing",
		"tryEstablish",
		"tryInviteBatching",
		"tryKick",
	];

	public const MEMBER_ACTIVITY_METHOD_LIST = [
		"tryInit",
		"doAccept",
	];

	// максимальное количество звонков в запросе
	protected const _MAX_CALLS_COUNT = 50;

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * Инициализируем звонок
	 */
	public function tryInit():array {

		$opponent_user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		$this->_throwIfParameterUserIdIncorrect($opponent_user_id);
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::CALLS_TRYINIT);

		$user_info_list = Gateway_Bus_CompanyCache::getShortMemberList([$this->user_id, $opponent_user_id]);
		if (count($user_info_list) < 2) {
			throw new ParamException("users info not found in cache");
		}

		// пытаемся инициализировать single-звонок
		try {

			Domain_Member_Entity_Permission::check($this->user_id, $this->method_version, Permission::IS_CALL_ENABLED);
			[$meta_row, $node_list] = Helper_Calls::tryInit($this->user_id, $this->method_version, $this->session_uniq, getIp(), $opponent_user_id);
		} catch (cs_Call_ConversationNotExist) {
			return $this->error(603, "single conversation between users not found");
		} catch (cs_Call_MemberIsDisabled) {
			return $this->error(605, "opponent is blocked in our system");
		} catch (cs_Call_LineIsBusy $e) {
			return $this->_onLineIsBusyAtSingleCall($e->getBusyLineUserId(), $e->getCallMap(), $e->getConversationMap());
		} catch (Domain_Member_Exception_ActionNotAllowed) {
			return $this->error(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		} catch (Domain_Member_Exception_AttemptInitialCall $e) {
			return $this->error($e->getErrorCode(), "action not allowed");
		}

		// отправляем пользователю need_ping, в котором находятся ноды для пинга
		$this->action->needPing($meta_row["call_map"], $node_list);

		$prepared_call  = Type_Call_Utils::prepareCallForFormat($meta_row, $this->user_id);
		$formatted_call = Apiv1_Format::call($prepared_call);

		return $this->ok([
			"call" => (object) $formatted_call,
		]);
	}

	/**
	 * функция срабатывает в случае, если телефонная линия пользователя занята
	 *
	 */
	protected function _onLineIsBusyAtSingleCall(int $busy_line_user_id, null|string $inited_call_map, string $conversation_map):array {

		// если активный звонок имеет наш же пользователь
		if ($busy_line_user_id == $this->user_id) {
			return $this->error(608, "you have not finished call");
		}

		// добавляем задачу в phphooker для отправки в диалог сообщения для звонка
		Type_Phphooker_Main::addCallMessage($conversation_map, $inited_call_map, $this->user_id);

		// помечаем звонок законченным
		try {
			$meta_row = Type_Call_Main::doHangup($this->user_id, $inited_call_map, CALL_FINISH_REASON_LINE_IS_BUSY);
		} catch (cs_Call_IsFinished) {

			// такая ситуация вряд ли возможна, но «Anything that can go wrong will go wrong»
			$meta_row = Type_Call_Meta::get($inited_call_map);
		}

		$prepared_call  = Type_Call_Utils::prepareCallForFormat($meta_row, $this->user_id);
		$formatted_call = Apiv1_Format::call($prepared_call);

		return $this->ok([
			"call" => (object) $formatted_call,
		]);
	}

	/**
	 * отправить результаты пинга отдельной ноды
	 */
	public function doSendPingResult():array {

		$call_key = $this->post(\Formatter::TYPE_STRING, "call_key");
		$node_id  = $this->post(\Formatter::TYPE_INT, "node_id");
		$latency  = (int) $this->post(\Formatter::TYPE_STRING, "latency");

		$call_map = Type_Pack_Call::doDecrypt($call_key);

		Gateway_Bus_Statholder::inc("calls", "row200");

		$this->_throwIfIncorrectLatency($latency);
		$this->_throwIfNotFoundNode($node_id);

		// получаем мету звонка и проверяем, что пользователь является участником звонка
		$meta_row = Type_Call_Meta::get($call_map);
		$this->_throwIfUserIsNotMemberOfCall($this->user_id, $meta_row["users"], "calls", "row202");

		// проверяем, что статус звонка позволяет начать соединение
		$status = Type_Call_Users::getStatus($meta_row["users"][$this->user_id]);
		if ($status != CALL_STATUS_DIALING) {
			return $this->error(606, "incorrect status call for this action");
		}

		// добавляем в поле users результаты пинга ноды
		Type_Call_Meta::setPingResult($call_map, $this->user_id, $node_id, $latency);

		Gateway_Bus_Statholder::inc("calls", "row204");
		return $this->ok();
	}

	/**
	 * проверяем, что параметр latency корректен
	 *
	 * @throws ParamException
	 */
	protected function _throwIfIncorrectLatency(int $latency):void {

		if ($latency < 0) {

			Gateway_Bus_Statholder::inc("calls", "row203");
			throw new ParamException("incorrect param latency");
		}
	}

	/**
	 * проверяем, что такая нода существует
	 *
	 * @throws ParamException
	 */
	protected function _throwIfNotFoundNode(int $node_id):void {

		// получаем список нод из конфига
		$config_node_list = Type_Call_Config::getJanusList();

		// проверяем, что такая нода имеется в конфиге, иначе выбрасываем исключение
		foreach ($config_node_list as $v) {

			if ($node_id == $v["node_id"]) {
				return;
			}
		}

		Gateway_Bus_Statholder::inc("calls", "row205");
		throw new ParamException("this node not found");
	}

	/**
	 * принимаем звонок
	 */
	public function doAccept():array {

		$call_key               = $this->post(\Formatter::TYPE_STRING, "call_key");
		$need_read_conversation = $this->post(\Formatter::TYPE_BOOL, "need_read_conversation", true);
		Gateway_Bus_Statholder::inc("calls", "row20");

		$call_map = Type_Pack_Call::doDecrypt($call_key);

		// получаем мету звонка; проверяем, что пользователь является участником звонка
		$meta_row = Type_Call_Meta::get($call_map);
		$this->_throwIfUserIsNotMemberOfCall($this->user_id, $meta_row["users"], "calls", "row22");

		// если звонок групповой и принимают с мобильного устройства
		if ($meta_row["type"] == CALL_TYPE_GROUP && Type_Api_Platform::getPlatform() != Type_Api_Platform::PLATFORM_ELECTRON) {
			throw new ParamException(__METHOD__ . ": mobile device cant accept group call");
		}

		// принимаем звонок и создаем разговорную комнату
		try {

			$meta_row = Helper_Calls::tryAccept($this->user_id, $this->session_uniq, getIp(), $call_map);
			$room_row = Helper_Janus::createRoomIfNotExist($this->user_id, $call_map, $meta_row);

			// вступаем в разговорную комнату как publisher; subscriber's
			$report_call_id       = Type_Call_Meta::getReportCallId($meta_row["extra"]);
			$user_connection_list = Helper_Janus::joinIntoCallRoom($this->user_id, $call_map, $report_call_id, $room_row);
		} catch (cs_Call_IsFinished) {

			Gateway_Bus_Statholder::inc("calls", "row23");
			throw new ParamException(__METHOD__ . ": call is finished");
		} catch (cs_Call_UserAlreadyAcceptedCall) {

			Gateway_Bus_Statholder::inc("calls", "row25");
			throw new ParamException(__METHOD__ . ": user already accept this call");
		} catch (cs_Call_LineIsBusy) {

			Gateway_Bus_Statholder::inc("calls", "row26");
			return $this->error(608, "you have not finished call");
		}

		$conversation_map = Type_Call_Users::getConversationMap($this->user_id, $meta_row["users"]);
		if (mb_strlen($conversation_map) > 0 && $need_read_conversation) {

			$conversation_key = Type_Pack_Conversation::doEncrypt($conversation_map);
			[$local_date, $local_time, $_] = getLocalClientTime();
			Gateway_Socket_Conversation::doReadMessage($this->user_id, $conversation_key, $local_date, $local_time);
		}

		return $this->_getDoAcceptOutput(
			$meta_row, $user_connection_list["publisher_row"], $user_connection_list["subscriber_list"], $user_connection_list["opponents_media_data_list"]
		);
	}

	// собираем ответ для метода doAccept
	protected function _getDoAcceptOutput(array $meta_row, array $publisher_row, array $subscriber_list, array $opponents_media_data_list):array {

		$prepared_call = Type_Call_Utils::prepareCallForFormat($meta_row, $this->user_id);

		// получаем всю информацию с помощью которой клиенты будут обращаться к janus [SINGLE]
		$subscriber_row                  = $subscriber_list[0];
		$janus_communication_info_single = Type_Janus_Node::init($publisher_row["node_id"])->getJanusCommunicationSingle($publisher_row, $subscriber_row);

		// получаем объект содержащий все необходимые клиенту параметры для создания peer_connection соединения
		$is_need_relay   = Type_Call_Users::isNeedRelay($meta_row["users"][$this->user_id]);
		$ice_server_data = Type_Janus_Node::init($publisher_row["node_id"])->getIceServerData($publisher_row["user_id"], $is_need_relay);

		// получаем всю информацию с помощью которой клиенты будут обращаться к janus [NEW]
		$janus_communication_data = Type_Janus_Node::init($publisher_row["node_id"])->getJanusCommunicationData(
			$publisher_row, $subscriber_list, $opponents_media_data_list, $is_need_relay
		);

		$call = Apiv1_Format::call($prepared_call);
		$this->action->users($prepared_call["users"]);
		return $this->ok([
			"call"                     => (object) $call,
			"janus_communication"      => (object) Apiv1_Format::janusCommunicationSingle($janus_communication_info_single),
			"janus_communication_data" => (object) Apiv1_Format::janusCommunicationData($janus_communication_data),
			"ice_server_data"          => (object) Apiv1_Format::iceServerData($ice_server_data),
			"pub_connection_uuid"      => (string) $publisher_row["connection_uuid"],
			"sub_connection_uuid"      => (string) $subscriber_row["connection_uuid"],
		]);
	}

	/**
	 * положить трубку
	 *
	 * @throws ParamException
	 */
	public function doHangUp():array {

		$call_key = $this->post(\Formatter::TYPE_STRING, "call_key");
		Gateway_Bus_Statholder::inc("calls", "row40");

		$call_map = Type_Pack_Call::doDecrypt($call_key);

		// получаем meta звонка; проверяем что пользователь участник звонка
		$meta_row = Type_Call_Meta::get($call_map);

		// если пользователь не участник звонка
		if (!isset($meta_row["users"][$this->user_id])) {
			throw new ParamException(__METHOD__ . ": user should be member of call");
		}

		// если пользователь покинул звонок и тот для него считается завершенным
		if (Type_Call_Users::getRole($meta_row["users"][$this->user_id]) == Type_Call_Users::ROLE_LEAVED) {

			$prepared_call  = Type_Call_Utils::prepareCallForFormat($meta_row, $this->user_id);
			$formatted_call = Apiv1_Format::call($prepared_call);

			return $this->error(604, "call is finished", ["call" => (object) $formatted_call]);
		}

		// звонок в целом уже завершен?
		if ($meta_row["is_finished"] == 1) {

			$prepared_call  = Type_Call_Utils::prepareCallForFormat($meta_row, $this->user_id);
			$formatted_call = Apiv1_Format::call($prepared_call);

			Gateway_Bus_Statholder::inc("calls", "row43");
			return $this->error(604, "call is finished", ["call" => (object) $formatted_call]);
		}

		// если звонок не в статусе DIALING
		$status = Type_Call_Users::getStatus($meta_row["users"][$this->user_id]);
		if ($status != CALL_STATUS_DIALING) {
			$this->_throwIfCallStartedFromAnotherSession($meta_row["users"], "calls", "row45");
		}

		// выбираем статус завершения звонка; если разговор на стадии гудков, то считаем звонок отмененным
		$finish_reason = CALL_FINISH_REASON_HANGUP;
		if ($status == CALL_STATUS_DIALING) {
			$finish_reason = CALL_FINISH_REASON_CANCELED;
		}

		// кладем трубку
		return $this->_doHangupAction($meta_row, $finish_reason);
	}

	/**
	 * Кладем трубку в звонке
	 *
	 */
	protected function _doHangupAction(array $meta_row, int $finish_reason):array {

		try {
			$meta_row = Helper_Calls::doHangup($this->user_id, $meta_row["call_map"], $finish_reason);
		} catch (cs_Call_IsFinished) {

		}

		$prepared_call  = Type_Call_Utils::prepareCallForFormat($meta_row, $this->user_id);
		$formatted_call = Apiv1_Format::call($prepared_call);

		Gateway_Bus_Statholder::inc("calls", "row44");
		return $this->ok([
			"call" => (object) $formatted_call,
		]);
	}

	/**
	 * метод для получения информации о звонке
	 */
	public function get():array {

		$call_key = $this->post("?s", "call_key");
		Gateway_Bus_Statholder::inc("calls", "row100");

		$call_map = Type_Pack_Call::doDecrypt($call_key);

		// получаем meta звонка; проверяем, что пользователь является участником звонка
		$meta_row = Type_Call_Meta::get($call_map);
		$this->_throwIfUserWasNotMemberOfCall($this->user_id, $meta_row["users"], "calls", "row102");

		// подготавливем сущность call под формат api
		$prepared_call = Type_Call_Utils::prepareCallForFormat($meta_row, $this->user_id);

		Gateway_Bus_Statholder::inc("calls", "row103");
		return $this->ok([
			"call" => (object) Apiv1_Format::call($prepared_call),
		]);
	}

	/**
	 * метод для возвращения состояния звонка
	 */
	public function getPreferences():array {

		// собираем ответ
		$output = (array) ["constants" => (array) [],];
		Gateway_Bus_Statholder::inc("calls", "row120");

		// добавляем к ответу константу с максимальным количеством участников
		$output["constants"][] = [
			"name"  => (string) "max_member_limit",
			"value" => (int) CALL_MAX_MEMBER_LIMIT,
		];

		// получаем последний звонок из кластера; проверяем, что запись найдена
		$last_call_row = Gateway_Socket_Pivot::getUserLastCall($this->user_id);
		if ($last_call_row === false) {

			Gateway_Bus_Statholder::inc("calls", "row121");
			return $this->ok($output);
		}

		// если имеется активный звонок, то добавляем его к ответу
		if ($last_call_row->is_finished == 0) {

			$output["active_call"] = (object) [
				"call_map" => (string) $last_call_row->call_map,
			];
		}

		Gateway_Bus_Statholder::inc("calls", "row122");
		return $this->ok($output);
	}

	/**
	 * метод для получения состояния собеседников
	 */
	public function getOpponentsCondition():array {

		$call_key = $this->post("?s", "call_key");

		$call_map = Type_Pack_Call::doDecrypt($call_key);

		Gateway_Bus_Statholder::inc("calls", "row240");

		// получаем мету звонка
		$meta_row = Type_Call_Meta::get($call_map);

		// если пользователь не участник звонка
		$this->_throwIfUserIsNotMemberOfCall($this->user_id, $meta_row["users"], "calls", "row244");

		// если звонок завершён
		$status = Type_Call_Users::getStatus($meta_row["users"][$this->user_id]);
		if ($status == CALL_STATUS_HANGUP) {

			Gateway_Bus_Statholder::inc("calls", "row245");
			return $this->error(604, "call is finished");
		}

		return $this->_getOpponentsCondition($call_map);
	}

	/**
	 * получаем состояния собеседников
	 *
	 */
	protected function _getOpponentsCondition(string $call_map):array {

		$publisher_connection_list = Type_Janus_UserConnection::getPublisherListByCallMap($call_map);
		$quality_state_list        = $this->_getUserQualityStateList($publisher_connection_list);
		$output                    = (array) [];
		foreach ($publisher_connection_list as $call_connection_row) {

			// если соединение текущего пользователя
			if ($call_connection_row["user_id"] == $this->user_id) {
				continue;
			}

			$quality_state                   = min($quality_state_list[$call_connection_row["user_id"]]);
			$output["user_condition_list"][] = (object) $this->_prepareUserConditionItem($call_connection_row, $quality_state);
		}
		Gateway_Bus_Statholder::inc("calls", "row242");

		return $this->ok($output);
	}

	/**
	 * формируем объект для user_condition_list
	 *
	 * @param array $call_connection_row
	 * @param int   $quality_state
	 *
	 * @return array
	 */
	#[ArrayShape(["user_id" => "int", "is_send_video" => "int", "is_send_audio" => "int", "is_enabled_video" => "int", "is_enabled_audio" => "int", "quality_state" => "string", "status" => "string"])]
	protected function _prepareUserConditionItem(array $call_connection_row, int $quality_state):array {

		return [
			"user_id"          => (int) $call_connection_row["user_id"],
			"is_send_video"    => (int) $call_connection_row["is_send_video"],
			"is_send_audio"    => (int) $call_connection_row["is_send_audio"],
			"is_enabled_video" => (int) $call_connection_row["is_send_video"],
			"is_enabled_audio" => (int) $call_connection_row["is_send_audio"],
			"quality_state"    => (string) Type_Janus_UserConnection::QUALITY_STATE_LIST[$quality_state],
			"status"           => (string) Type_Janus_UserConnection::STATUS_TITLE_LIST[$call_connection_row["status"]],
		];
	}

	/**
	 * метод для обновления соединения пользователя
	 */
	public function doUpgradeConnection():array {

		$call_key = $this->post(\Formatter::TYPE_STRING, "call_key");
		$offer    = $this->post(\Formatter::TYPE_STRING, "offer");
		$audio    = $this->post(\Formatter::TYPE_INT, "audio", false);
		$video    = $this->post(\Formatter::TYPE_INT, "video", false);

		$call_map = Type_Pack_Call::doDecrypt($call_key);

		// проверяем offer на валидность
		$offer = fromJson($offer);
		$this->_throwIfIncorrectParams($offer, $video, $audio);

		// проверяем audio & video на корректность
		$media_params = $this->_checkMediaParams($audio, $video);

		// получаем паблишер-соединение
		$publisher_connection_row = $this->_getPublisherConnection($this->user_id, $call_map);

		// если передан лишь один параметр, то получаем второй из записи бд
		if (count($media_params) == 1) {
			$media_params = $this->_getSecondParam($media_params, $publisher_connection_row);
		}

		// проверяем, что пользователь является участником звонка
		$meta_row = Type_Call_Meta::get($call_map);
		$this->_throwIfUserIsNotMemberOfCall($this->user_id, $meta_row["users"]);
		$this->_throwIfCallStartedFromAnotherSession($meta_row["users"]);

		// совершаем doUpgradeConnection
		$call_members_count = Type_Call_Users::getCount($meta_row["users"]);
		return $this->_doUpgradeConnection($call_map, $publisher_connection_row, $media_params["audio"], $media_params["video"], $offer, $call_members_count);
	}

	/**
	 * проверяем, корректен ли offer
	 *
	 * @throws ParamException
	 */
	protected function _throwIfIncorrectParams(array $offer, bool|int $video, bool|int $audio):void {

		if (!Type_Api_Validator::isOffer($offer)) {

			Gateway_Bus_Statholder::inc("calls", "row224");
			throw new ParamException(__METHOD__ . ": incorrect SDP packet type offer");
		}

		// не передан ни один параметр?
		if ($audio === false && $video === false) {

			Gateway_Bus_Statholder::inc("calls", "row227");
			throw new ParamException("empty params audio & video");
		}
	}

	/**
	 * проверяем параметры медиа
	 *
	 * @throws ParamException
	 */
	protected function _checkMediaParams(bool|int $audio, bool|int $video):array {

		$params = [];
		if ($audio !== false) {
			$params["audio"] = $audio;
		}
		if ($video !== false) {
			$params["video"] = $video;
		}

		// проверяем собранные параметры на корректность
		foreach ($params as $k => $v) {
			if (!in_array($v, [0, 1])) {

				throw new ParamException("incorrect param: {$k}");
			}
		}

		return $params;
	}

	/**
	 * получаем паблишер-соединение пользователя
	 *
	 * @throws ParamException
	 */
	protected function _getPublisherConnection(int $user_id, string $call_map):array {

		$publisher_connection_row = Type_Janus_UserConnection::getPublisherByCallMap($user_id, $call_map);

		// проверяем существование пользовательского подключения
		if (!isset($publisher_connection_row["user_id"])) {

			Gateway_Bus_Statholder::inc("calls", "row225");
			throw new ParamException(__METHOD__ . ": user connection is not found");
		}

		// проверяем, что соединение принадлежит текущему пользователю
		if ($this->user_id != $publisher_connection_row["user_id"]) {

			Gateway_Bus_Statholder::inc("calls", "row226");
			throw new ParamException(__METHOD__ . ": passed connection_uuid not belong this user");
		}

		return $publisher_connection_row;
	}

	// получаем второй параметр из записи соединения
	protected function _getSecondParam(array $params, array $connection_row):array {

		// если от пользователя получили параметр аудио, то достаем из записи сохраненный параметр видео
		if (isset($params["audio"])) {

			$params["video"] = $connection_row["is_send_video"] == 1;
			return $params;
		}

		// иначе получаем из записи параметр аудио
		$params["audio"] = $connection_row["is_send_audio"] == 1;

		return $params;
	}

	/**
	 * метод совершает основную логику doUpgradeConnection
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected function _doUpgradeConnection(string $call_map, array $publisher_connection_row, bool $audio, bool $video, array $offer, int $call_members_count):array {

		try {

			// переключаем потоки медиа-данных
			Helper_Janus::doUpgradeConnection(
				$this->user_id, $call_map,
				$publisher_connection_row["session_id"], $publisher_connection_row["handle_id"],
				$audio, $video,
				$offer,
				$publisher_connection_row["node_id"],
				$call_members_count
			);
		} catch (cs_FailedJanusGatewayAPIRequest) {
			return $this->error(611, "failed janus request");
		}

		return $this->ok();
	}

	/**
	 * метод для создания жалобы на плохую связь
	 */
	public function doReportOnConnection():array {

		$call_key = $this->post("?s", "call_key");
		$reason   = $this->post("?s", "reason", "");
		$network  = $this->post("?s", "network", "");
		Gateway_Bus_Statholder::inc("calls", "row180");

		$call_map = Type_Pack_Call::doDecrypt($call_key);

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::CALLS_DOREPORTONCONNECTION, "calls", "row183");

		// проверяем, что пользователь является участником звонка
		$meta_row = Type_Call_Meta::get($call_map);
		$this->_throwIfUserWasNotMemberOfCall($this->user_id, $meta_row["users"], "calls", "row182");

		// фильтруем причину жалобы и сеть пользователя
		$reason  = Type_Api_Filter::sanitizeReason($reason);
		$network = Type_Api_Filter::sanitizeNetwork($network);

		// добавляем жалобу в базу
		$call_member_count    = Type_Call_Users::getCount($meta_row["users"]);
		$call_connection_list = Type_Janus_UserConnection::getAllByCallMap($call_map, $call_member_count);
		$report_call_id       = Gateway_Db_CompanyCall_CallMeta::getReportCallId($meta_row["extra"]);
		Type_Call_Report::add($call_map, $report_call_id, $this->user_id, $reason, $network, $call_connection_list);

		Gateway_Bus_Statholder::inc("calls", "row184");
		return $this->ok();
	}

	/**
	 * метод для отправки сигнала, который означает, что клиент все еще на связи
	 */
	public function doPing():array {

		$call_key = $this->post("?s", "call_key");

		$call_map = Type_Pack_Call::doDecrypt($call_key);

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::CALLS_DOPING, "calls", "row260");

		$meta_row = Type_Call_Meta::get($call_map);
		$this->_throwIfUserWasNotMemberOfCall($this->user_id, $meta_row["users"]);

		// проверяем, что звонок завершен или в статусе DIALING
		$status = Type_Call_Users::getStatus($meta_row["users"][$this->user_id]);
		if ($meta_row["is_finished"] == 1 || in_array($status, [CALL_STATUS_DIALING, CALL_STATUS_HANGUP])) {
			return $this->_getEmptyResponseDoPing($status, true, 0, []);
		}

		// получаем список соединений звонка и пробегаемся по каждому
		$publisher_connection_list = Type_Janus_UserConnection::getPublisherListByCallMap($call_map);

		$output = [
			"establish_step"      => 0,
			"user_condition_list" => [],
		];
		return $this->_getExtraCallData($publisher_connection_list, $meta_row["is_finished"] ? true : false, $status, $output);
	}

	// подготавливаем пустой ответ для метода doPing
	protected function _getEmptyResponseDoPing(int $status, bool $is_finished, int $establish_step, array $user_condition_list):array {

		return $this->ok([
			"status"              => (string) Type_Call_Utils::getStatusTitle($status, $is_finished),
			"is_need_establish"   => (int) $establish_step > 0 ? 1 : 0,
			"establish_step"      => (int) $establish_step,
			"user_condition_list" => (array) $user_condition_list,
		]);
	}

	// получаем дополнительные данные по звонку
	protected function _getExtraCallData(array $publisher_connection_list, bool $is_finished, int $status, array $output):array {

		$quality_state_list = $this->_getUserQualityStateList($publisher_connection_list);
		foreach ($publisher_connection_list as $connection_row) {

			// если паблишер собеседника, то получаем данные о нем; если нашего юзера - обновляем время последнего пинга пользователя
			if ($connection_row["user_id"] != $this->user_id) {

				$output["user_condition_list"][] = (object) $this->_getOpponentCondition($connection_row, $quality_state_list);
				continue;
			}

			Type_Janus_UserConnection::setLastPingAt($connection_row["session_id"], $connection_row["handle_id"]);
		}

		return $this->ok([
			"status"              => (string) Type_Call_Utils::getStatusTitle($status, $is_finished),
			"is_need_establish"   => (int) 0,
			"establish_step"      => (int) 0,
			"user_condition_list" => (array) $output["user_condition_list"],
		]);
	}

	// получаем состояние собеседника
	#[ArrayShape(["user_id" => "int", "is_send_video" => "int", "is_send_audio" => "int", "is_enabled_video" => "int", "is_enabled_audio" => "int", "quality_state" => "string", "status" => "string"])]
	protected function _getOpponentCondition(array $connection_row, array $quality_state_list):array {

		$quality_state = min($quality_state_list[$connection_row["user_id"]]);
		return [
			"user_id"          => (int) $connection_row["user_id"],
			"is_send_video"    => (int) $connection_row["is_send_video"],
			"is_send_audio"    => (int) $connection_row["is_send_audio"],
			"is_enabled_video" => (int) $connection_row["is_send_video"],
			"is_enabled_audio" => (int) $connection_row["is_send_audio"],
			"quality_state"    => (string) Type_Janus_UserConnection::QUALITY_STATE_LIST[$quality_state],
			"status"           => (string) Type_Janus_UserConnection::STATUS_TITLE_LIST[$connection_row["status"]],
		];
	}

	/**
	 * возвращаем информацию о звонках по batching списку
	 */
	public function getBatching():array {

		$call_key_list = $this->post("?a", "call_key_list");
		Gateway_Bus_Statholder::inc("calls", "row280");

		// бросаем ошибку, если пришел некорректный массив звонков
		$this->_throwIfCallListIsIncorrect($call_key_list);

		// преобразуем все key в map
		$call_map_list = $this->_doDecryptCallKeyList($call_key_list);

		// получаем звонки из базы
		$call_meta_list = Type_Call_Meta::getAll($call_map_list);

		$output                    = [];
		$user_creator_call_id_list = [];
		foreach ($call_meta_list as $item) {

			$this->_throwIfUserWasNotMemberOfCall($this->user_id, $item["users"], "calls", "row284");

			// приводим сущность call под формат frontend
			$prepared_call               = Type_Call_Utils::prepareCallForFormat($item, $this->user_id);
			$output[]                    = Apiv1_Format::call($prepared_call);
			$user_creator_call_id_list[] = (int) $prepared_call["creator_user_id"];
		}

		Gateway_Bus_Statholder::inc("calls", "row285");

		// прикрепляем создателей звонков к action
		$this->action->users($user_creator_call_id_list);

		return $this->ok([
			"call_list" => (array) $output,
		]);
	}

	// выбрасываем ошибку, если массив key звонков некорректный
	protected function _throwIfCallListIsIncorrect(array $call_list):void {

		// если пришел пустой массив звонков
		if (count($call_list) < 1) {

			Gateway_Bus_Statholder::inc("calls", "row281");
			throw new ParamException("passed empty call_list");
		}

		// если пришел слишком большой массив
		if (count($call_list) > self::_MAX_CALLS_COUNT) {

			Gateway_Bus_Statholder::inc("calls", "row282");
			throw new ParamException("passed call_list biggest than max");
		}
	}

	// преобразуем пришедшие ключи в map
	protected function _doDecryptCallKeyList(array $call_key_list):array {

		$call_map_list = [];
		foreach ($call_key_list as $item) {

			// преобразуем key в map
			try {
				$call_map = Type_Pack_Call::doDecrypt($item);
			} catch (\cs_DecryptHasFailed) {
				throw new ParamException("passed wrong call key");
			}

			// добавляем звонок в массив
			$call_map_list[] = $call_map;
		}

		return $call_map_list;
	}

	/**
	 * метод для получения данных чтобы начать устанавливать соединение
	 */
	public function tryEstablish():array {

		$call_key = $this->post("?s", "call_key");

		$call_map = Type_Pack_Call::doDecrypt($call_key);

		// получаем meta звонка; проверяем что пользователь участник звонка
		$meta_row = Type_Call_Meta::get($call_map);
		$this->_throwIfUserIsNotMemberOfCall($this->user_id, $meta_row["users"]);

		// возвращаем ошибку, если звонок завершен
		if ($meta_row["is_finished"] == 1) {
			return $this->error(604, "call is finished");
		}

		// получаем соединения звонка
		$member_list              = Type_Call_Users::getMemberList($meta_row["users"]);
		$user_connection_list     = Type_Janus_UserConnection::getAllByCallMap($call_map, count($member_list));
		$filtered_connection_list = $this->_filterConnectionList($user_connection_list);

		// проверяем, что пользователь готов к установлению соединения
		if (!$this->_isReadyToEstablish($filtered_connection_list)) {
			return $this->ok(["is_ready" => (int) 0]);
		}

		return $this->_getOutputForTryEstablish($meta_row, $filtered_connection_list);
	}

	// фильтруем соединения
	#[ArrayShape(["user_publisher_row" => "array|mixed", "publisher_list" => "array", "user_subscriber_list" => "array"])]
	protected function _filterConnectionList(array $user_connection_list):array {

		$output = [
			"user_publisher_row"   => [],
			"publisher_list"       => [],
			"user_subscriber_list" => [],
		];

		foreach ($user_connection_list as $item) {

			if ($item["is_publisher"] == 1) {

				$output["publisher_list"][] = $item;
				if ($item["user_id"] == $this->user_id) {
					$output["user_publisher_row"] = $item;
				}
				continue;
			}

			if ($item["user_id"] == $this->user_id) {
				$output["user_subscriber_list"][] = $item;
			}
		}
		return $output;
	}

	// проверяем, что пользователь готов к установлению соединения?
	#[Pure]
	protected function _isReadyToEstablish(array $filtered_connection_list):bool {

		// если совсем не оказалось записи с publisher соединением
		if (count($filtered_connection_list["user_publisher_row"]) < 1) {
			return false;
		}

		// если совсем не оказалось записи с subscriber соединением
		if (count($filtered_connection_list["user_subscriber_list"]) < 1) {
			return false;
		}

		return true;
	}

	// собираем данные для ответа
	protected function _getOutputForTryEstablish(array $meta_row, array $filtered_connection_list):array {

		$publisher_row             = $filtered_connection_list["user_publisher_row"];
		$subscriber_row_legacy     = $filtered_connection_list["user_subscriber_list"][0];
		$opponents_media_data_list = Type_Janus_UserConnection::getOpponentsMediaDataList($filtered_connection_list["publisher_list"]);
		$user_subscriber_list      = $filtered_connection_list["user_subscriber_list"];

		$is_need_relay            = Type_Call_Users::isNeedRelay($meta_row["users"][$this->user_id]);
		$janus_communication_data = Type_Janus_Node::init($publisher_row["node_id"])->getJanusCommunicationData(
			$publisher_row, $user_subscriber_list, $opponents_media_data_list, $is_need_relay
		);

		// получаем всю информацию с помощью которой клиенты будут обращаться к janus
		$janus_communication_info = Type_Janus_Node::init($publisher_row["node_id"])->getJanusCommunicationSingle($publisher_row, $subscriber_row_legacy);
		$ice_server_data          = Type_Janus_Node::init($publisher_row["node_id"])->getIceServerData($publisher_row["user_id"], $is_need_relay);

		$prepared_call = Type_Call_Utils::prepareCallForFormat($meta_row, $this->user_id);
		return $this->ok([
			"is_ready"                 => (int) 1,
			"call"                     => (object) Apiv1_Format::call($prepared_call),
			"janus_communication"      => (object) Apiv1_Format::janusCommunicationSingle($janus_communication_info),
			"ice_server_data"          => (object) Apiv1_Format::iceServerData($ice_server_data),
			"janus_communication_data" => (array) Apiv1_Format::janusCommunicationData($janus_communication_data),
			"pub_connection_uuid"      => (string) $publisher_row["connection_uuid"],
			"sub_connection_uuid"      => (string) $subscriber_row_legacy["connection_uuid"],
		]);
	}

	/**
	 * Приглашаем пользователя в звонок
	 *
	 * @long
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\ControllerMethodNotFoundException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_RowIsEmpty
	 * @throws \returnException
	 */
	public function tryInviteBatching():array {

		$call_key        = $this->post("?s", "call_key");
		$batch_user_list = $this->post("?a", "batch_user_list");

		$call_map = Type_Pack_Call::doDecrypt($call_key);

		Gateway_Bus_Statholder::inc("calls", "row340");
		$this->_throwIfIncorrectBatchUserList($batch_user_list);

		// получаем мету звонка; проверяем, что пользователь является участником звонка
		$meta_row = Type_Call_Meta::get($call_map);
		$this->_throwIfUserIsNotMemberOfCall($this->user_id, $meta_row["users"], "calls", "row343");

		// проверяем права пользователя
		if (!Type_Call_Users::isCanInvite($this->user_id, $meta_row["users"])) {

			Gateway_Bus_Statholder::inc("calls", "row344");
			throw new ParamException(__METHOD__ . ": user is have no rights");
		}

		// проверяем статус звонка
		if ($meta_row["is_finished"] == 1) {

			Gateway_Bus_Statholder::inc("calls", "row346");
			return $this->error(604, "call is finished");
		}

		try {
			Domain_Member_Entity_Permission::check($this->user_id, $this->method_version, Permission::IS_CALL_ENABLED);
		} catch (Domain_Member_Exception_ActionNotAllowed) {
			return $this->error(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		}

		// проверяем, что приглашаемые пользователи существуют и приглашаем
		$user_list_info = $this->_tryGetBatchUserListInfo($batch_user_list);
		try {
			return $this->_tryInviteBatching($user_list_info, $call_map, $meta_row["users"]);
		} catch (Domain_Member_Exception_ActionNotAllowed) {
			return $this->error(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		}
	}

	/**
	 * проверяем, что передали корректный batch_user_list
	 *
	 * @param array $batch_user_list
	 *
	 * @return void
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws ParamException
	 * @throws \returnException
	 * @long много проверок
	 */
	protected function _throwIfIncorrectBatchUserList(array $batch_user_list):void {

		$number_of_item = count($batch_user_list);

		if ($number_of_item < 1) {
			throw new ParamException(__METHOD__ . ": number of users less than 1 in batch_user_list");
		}

		if ($number_of_item > CALL_MAX_MEMBER_LIMIT) {
			throw new ParamException(__METHOD__ . ": the number of users in batch_user_list exceeds the limit");
		}

		$temp = array_unique($batch_user_list);

		if (count($temp) != $number_of_item) {
			throw new ParamException(__METHOD__ . ": duplicates found in batch_user_list");
		}

		foreach ($batch_user_list as $user_id) {

			if ($user_id < 1 || $user_id == $this->user_id) {
				throw new ParamException(__METHOD__ . ": sent incorrect users to batch_user_list");
			}
		}

		// проверяем что пользователи в списке не уволены
		$user_list_info = Gateway_Bus_CompanyCache::getShortMemberList($batch_user_list);

		foreach ($user_list_info as $user_info_item) {

			if ($user_info_item->role == Type_User_Main::USER_ROLE_KICKED) {
				throw new ParamException(__METHOD__ . ": sent kicked users in batch_user_list");
			}
		}
	}

	// пытаемся получить информацию по каждому пользователю
	protected function _tryGetBatchUserListInfo(array $batch_user_list):array {

		$user_list_info = Gateway_Bus_CompanyCache::getShortMemberList($batch_user_list);

		if (count($batch_user_list) != count($user_list_info)) {

			Gateway_Bus_Statholder::inc("calls", "row347");
			throw new ParamException("one of passed user_id incorrect");
		}

		return $user_list_info;
	}

	// пытаемся пригласить пользователей в звонок
	// @long — try ... catch ...
	protected function _tryInviteBatching(array $user_list_info, string $call_map, array $users):array {

		// проходим по списку пользователей - каждому отправляем инвайт
		$output = $this->_makeOutputForTryInviteBatching();
		foreach ($user_list_info as $user_info) {

			// пытаемся пригласить пользователей в звонок
			$is_need_send_message = $this->_isNeedSendMessageOnInvite($user_info->user_id, $users);
			try {
				Helper_Calls::tryInvite($this->user_id, $user_info->user_id, $call_map, $is_need_send_message, $this->method_version);
			} catch (cs_Call_NumberOfMembersExceeded) {

				$output["list_error"][] = $this->_makeErrorItemForTryInviteBatching($user_info->user_id, 609, "number of members exceeded");
				continue;
			} catch (cs_Call_IsFinished) {

				$output["list_error"][] = $this->_makeErrorItemForTryInviteBatching($user_info->user_id, 604, "call is finished");
				continue;
			} catch (cs_Call_ConversationNotExist) {

				$output["list_error"][] = $this->_makeErrorItemForTryInviteBatching($user_info->user_id, 603, "conversation with opponent not exist");
				continue;
			} catch (cs_Call_MemberIsDisabled) {

				$output["list_error"][] = $this->_makeErrorItemForTryInviteBatching($user_info->user_id, 605, "user is blocked in system");
				continue;
			} catch (cs_Call_LineIsBusy $e) {

				// отправляем сообщение о входящем звонке
				if ($is_need_send_message) {
					Type_Phphooker_Main::addCallMessage($e->getConversationMap(), $e->getCallMap(), $this->user_id);
				}
				$output["list_error"][] = $this->_makeErrorItemForTryInviteBatching($user_info->user_id, 602, "opponent line is busy");
				continue;
			} catch (cs_Call_UserAlreadyAcceptedCall) {

				$output["list_ok"][] = $this->_makeListOkItemForTrySendBatching($user_info->user_id);
				continue;
			}

			$output["list_ok"][] = $this->_makeListOkItemForTrySendBatching($user_info->user_id);
		}

		Gateway_Bus_Statholder::inc("calls", "row348");
		return $this->ok($output);
	}

	// создаем output для метода tryInviteBatching
	#[ArrayShape(["list_ok" => "array", "list_error" => "array"])]
	protected function _makeOutputForTryInviteBatching():array {

		return [
			"list_ok"    => (array) [],
			"list_error" => (array) [],
		];
	}

	// проверяем, нужно ли слать сообщение о входящем звонке приглашаемому пользователю
	protected function _isNeedSendMessageOnInvite(int $user_id, array $users):bool {

		// если пользователя ранее не приглашали в звонок
		if (isset($users[$user_id])) {
			return false;
		}

		return true;
	}

	// создаем item ошибки при приглашении в звонок
	protected function _makeErrorItemForTryInviteBatching(int $user_id, int $error_code, string $error_message, array $extra = []):array {

		$output = [
			"user_id"    => (int) $user_id,
			"error_code" => (int) $error_code,
			"message"    => (string) $error_message,
		];

		// если пришла экстра, то мерджим в основной массив
		if (count($extra) > 0) {
			$output = array_merge($extra, $output);
		}

		return $output;
	}

	// создаем item успешного выполнения метода
	#[ArrayShape(["user_id" => "int"])]
	protected function _makeListOkItemForTrySendBatching(int $user_id):array {

		return [
			"user_id" => $user_id,
		];
	}

	/**
	 * исключаем пользователя из группового звонка
	 */
	public function tryKick():array {

		$user_id  = $this->post("?i", "user_id");
		$call_key = $this->post("?s", "call_key");

		$call_map = Type_Pack_Call::doDecrypt($call_key);

		Gateway_Bus_Statholder::inc("calls", "row360");
		$this->_throwIfParameterUserIdIncorrect($user_id, "row362", "row363");

		// получаем мету звонка; проверяем, что пользователь является участником звонка
		$meta_row = Type_Call_Meta::get($call_map);
		$this->_throwIfUserIsNotMemberOfCall($this->user_id, $meta_row["users"], "calls", "row364");
		$this->_throwIfUserNotSpeakingInCall($this->user_id, $meta_row["users"]);

		// если участник, которого собираемся кикнуть — уже кикнут, то возвращаем окей
		if (!Type_Call_Users::isMember($user_id, $meta_row["users"])) {
			return $this->ok();
		}

		// проверяем права пользователя
		$this->_throwIfUserHaveNoRightsToKick($this->user_id, $meta_row["users"]);

		// проверяем статус звонка
		if ($meta_row["is_finished"] == 1) {

			Gateway_Bus_Statholder::inc("calls", "row366");
			return $this->error(604, "call is finished");
		}

		$finish_reason = $this->_getFinishReasonForKick($this->user_id, $meta_row["users"]);
		try {
			Helper_Calls::tryKick($this->user_id, $user_id, $call_map, $finish_reason);
		} catch (cs_Call_IsFinished) {

			Gateway_Bus_Statholder::inc("calls", "row366");
			return $this->error(604, "call is finished");
		}

		Gateway_Bus_Statholder::inc("calls", "row367");
		return $this->ok();
	}

	// выбрасываем ParamException, если у пользователя нет прав на изгнание участника
	protected function _throwIfUserHaveNoRightsToKick(int $user_id, array $users):void {

		if (!Type_Call_Users::isCanKick($user_id, $users)) {

			Gateway_Bus_Statholder::inc("calls", "row365");
			throw new ParamException(__METHOD__ . ": user is have no rights");
		}
	}

	// получаем finish_reason при кике пользователя
	protected function _getFinishReasonForKick(int $user_id, array $users):int {

		// если у пользователя гудки
		if (Type_Call_Users::getStatus($users[$user_id]) == CALL_STATUS_DIALING) {
			return CALL_FINISH_REASON_CANCELED;
		}

		return CALL_FINISH_REASON_HANGUP;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// проверяем, являлся ли пользователь участником звонка в ПРОШЛОМ
	protected function _throwIfUserWasNotMemberOfCall(int $user_id, array $users, string $namespace = null, string $row = null):void {

		// если пользователь никогда не являлся участником звонка
		if (!isset($users[$user_id])) {

			if (!is_null($namespace) && !is_null($row)) {
				Gateway_Bus_Statholder::inc($namespace, $row);
			}

			throw new ParamException(__METHOD__ . ": user should be member of call");
		}
	}

	// проверяем, является ли пользователь участником звонка в ТЕКУЩИЙ МОМЕНТ
	protected function _throwIfUserIsNotMemberOfCall(int $user_id, array $users, string $namespace = null, string $row = null):void {

		// если пользователь никогда не являлся участником звонка
		if (!Type_Call_Users::isMember($user_id, $users)) {

			if (!is_null($namespace) && !is_null($row)) {
				Gateway_Bus_Statholder::inc($namespace, $row);
			}

			throw new ParamException(__METHOD__ . ": user should be member of call");
		}
	}

	/**
	 * Проверяем что пользователь разговаривает
	 *
	 * @param int   $user_id
	 * @param array $users
	 *
	 * @return void
	 * @throws ParamException
	 */
	protected function _throwIfUserNotSpeakingInCall(int $user_id, array $users):void {

		if (!Type_Call_Users::isSpeaking($user_id, $users)) {
			throw new ParamException(__METHOD__ . ": user not speaking in call");
		}
	}

	// проверяем, что запрос осуществляется с того устройства, с которого запустился звонок
	protected function _throwIfCallStartedFromAnotherSession(array $users, string $namespace = null, string $row = null):void {

		if (!Type_Call_Users::isCallStartSessionUniq($this->user_id, $this->session_uniq, $users)
			|| !Type_Call_Users::isCallStartDeviceId($this->user_id, getDeviceId(), $users)) {

			if (!is_null($namespace) && !is_null($row)) {
				Gateway_Bus_Statholder::inc($namespace, $row);
			}

			throw new ParamException(__METHOD__ . ": user should make requests from the device which he started the call");
		}
	}

	// получаем список качеств всех соединений звонка
	protected function _getUserQualityStateList(array $call_connection_list):array {

		$quality_state_list = [];
		foreach ($call_connection_list as $call_connection_row) {

			$user_id                        = $call_connection_row["user_id"];
			$quality_state_list[$user_id][] = $call_connection_row["quality_state"];
		}

		return $quality_state_list;
	}

	// проверяем что переданный user_id корректный
	protected function _throwIfParameterUserIdIncorrect(int $user_id, string $row1 = "", string $row2 = ""):void {

		// user_id валиден?
		if ($user_id < 0) {

			if (mb_strlen($row1) > 0) {
				Gateway_Bus_Statholder::inc("calls", $row1);
			}
			throw new ParamException("Incorrect user id");
		}

		// пользователь пытается позвонить самому себе?
		if ($this->user_id == $user_id) {

			if (mb_strlen($row2) > 0) {
				Gateway_Bus_Statholder::inc("calls", $row2);
			}
			throw new ParamException("User try call himself");
		}
	}
}
