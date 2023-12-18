<?php

namespace Compass\Speaker;

/**
 * основной класс для работы со звонком
 */
class Type_Call_Main {

	// инициируем звонок
	public static function tryInit(int $user_id, int $opponent_user_id, string $session_uniq, string $ip_address, string $user_agent, string $device_id, string $conversation_map):array {

		$user_call_list = Gateway_Socket_Pivot::getBusyUsers($user_id, [$user_id, $opponent_user_id]);

		if (count($user_call_list) != 0) {

			// проверим, что звонок ещё работает
			Domain_Call_Action_ValidateWorkingCall::do($user_call_list);

			// кидаем exception, что линия пользователя занята
			self::_throwOnLineIsBusy($user_id, $opponent_user_id, $user_call_list, $session_uniq, $ip_address, $user_agent, $device_id, $conversation_map);
		}

		// проверяем IP адрес инициатора в списке проблемных
		// чтобы в будущем использовать TURN сервер если IP оказался проблемным
		$is_user_need_relay = Type_Call_ConnectionIssue::isIpHaveIssue($ip_address);

		$meta_row = Type_Call_Meta::create($user_id, $session_uniq, $ip_address, $user_agent, $device_id, $is_user_need_relay, $opponent_user_id, $conversation_map);
		self::_createOrUpdateLastCallsOfUsers([$user_id, $opponent_user_id], $meta_row["call_map"]);

		return $meta_row;
	}

	// выбрасываем exception cs_Call_LineIsBusy
	protected static function _throwOnLineIsBusy(int $user_id, int $opponent_user_id, array $user_call_list, string $session_uniq, string $ip_address, string $user_agent, string $device_id, string $conversation_map):void {

		$busy_line_user_id_list = [];
		foreach ($user_call_list as $user_id_list) {
			$busy_line_user_id_list = array_merge($busy_line_user_id_list, $user_id_list);
		}

		// если незавершенный звонок имеется у собеседника
		$call_map          = null;
		$busy_line_user_id = $user_id;
		if (in_array($opponent_user_id, $busy_line_user_id_list)) {

			$busy_line_user_id = $opponent_user_id;
			$meta_row          = Type_Call_Meta::create(
				$user_id, $session_uniq, $ip_address, $user_agent, $device_id, false, $opponent_user_id, $conversation_map
			);
			$call_map          = $meta_row["call_map"];
		}
		throw new cs_Call_LineIsBusy($busy_line_user_id, $call_map, $conversation_map);
	}

	// создаем или обноляем последник звонок участников звонка
	protected static function _createOrUpdateLastCallsOfUsers(array $user_id_list, string $call_map):void {

		Gateway_Socket_Pivot::setLastCall($user_id_list, $call_map);
	}

	// добавляем задачу на отслеживание dialing-звонка
	public static function addToDialingQueue(int $user_id, string $call_map):void {

		// добавляется запись в call_monitoring
		Gateway_Db_CompanyCall_CallMonitoringDialing::addTask($user_id, $call_map);

		// пушим задачу, которая запускает проверку состояния звонка
		Gateway_Bus_Event::pushTask(Type_Event_Call_MonitoringCheckRequired::EVENT_TYPE);
	}

	/**
	 * помечаем, что пользователь ответил на звонок
	 *
	 * @return array
	 * @throws cs_Call_IsFinished
	 * @throws cs_Call_LineIsBusy
	 * @throws cs_Call_UserAlreadyAcceptedCall
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function tryAccept(string $call_map, string $user_session_uniq, string $ip_address, string $user_agent, string $device_id, int $user_id):array {

		Gateway_Db_CompanyCall_Main::beginTransaction();

		$meta_row      = Gateway_Db_CompanyCall_CallMeta::getOneForUpdate($call_map);
		$last_call_row = Gateway_Socket_Pivot::getUserLastCall($user_id);

		// проверка, что звонок не завершен
		self::_throwIfCallIsFinished($meta_row["is_finished"]);

		// проверка, что звонок уже принят
		self::_throwIfUserAlreadyAcceptedCall($user_id, $meta_row["users"]);

		// проверяем, что у пользователя нет другого активного звонка
		self::_throwIfUserLineIsBusy($user_id, $call_map, $last_call_row);

		// обновляем записи в meta звонка и last_call_row пользователя
		$meta_row = self::_updateOnTryAccept($meta_row, $user_session_uniq, $ip_address, $user_agent, $device_id, $call_map, $user_id);
		Gateway_Db_CompanyCall_Main::commitTransaction();

		return $meta_row;
	}

	// проверяем, что у пользователя нет другого активного звонка
	protected static function _throwIfUserLineIsBusy(int $user_id, string $call_map, Struct_Socket_Pivot_UserLastCall|false $last_call_row):void {

		// если у пользователя имеется другой активный звонок, то закрываем транзакцию и кидаем исключение
		if ($last_call_row !== false && $last_call_row->is_finished == 0 && $last_call_row->call_map != $call_map) {

			Gateway_Db_CompanyCall_Main::rollback();
			throw new cs_Call_LineIsBusy($user_id, $call_map);
		}
	}

	/**
	 * обновляем мету при принятии звонка
	 *
	 * @return array
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected static function _updateOnTryAccept(array $meta_row, string $user_session_uniq, string $ip_address, string $user_agent, string $device_id, string $call_map, int $user_id):array {

		// проверяем IP адрес инициатора в списке проблемных
		// чтобы в будущем использовать TURN сервер если IP оказался проблемным
		$is_user_need_relay = Type_Call_ConnectionIssue::isIpHaveIssue($ip_address);

		// помечаем, что пользователь принял звонок
		$meta_row["updated_at"]      = time();
		$meta_row["users"][$user_id] = Type_Call_Users::setSpeaking($meta_row["users"][$user_id], true);
		$meta_row["users"][$user_id] = Type_Call_Users::setSessionUniq($meta_row["users"][$user_id], $user_session_uniq);
		$meta_row["users"][$user_id] = Type_Call_Users::setUserAgent($meta_row["users"][$user_id], $user_agent);
		$meta_row["users"][$user_id] = Type_Call_Users::setDeviceId($meta_row["users"][$user_id], $device_id);
		$meta_row["users"][$user_id] = Type_Call_Users::setStatus($meta_row["users"][$user_id], CALL_STATUS_ESTABLISHING);
		$meta_row["users"][$user_id] = Type_Call_Users::setAcceptedAt($meta_row["users"][$user_id], round(microtime(true) * 1000));
		$meta_row["users"][$user_id] = Type_Call_Users::setNeedRelay($meta_row["users"][$user_id], $is_user_need_relay);
		$meta_row["users"][$user_id] = Type_Call_Users::setIpAddress($meta_row["users"][$user_id], $ip_address);
		$meta_row["users"]           = self::_setAcceptedAtForCreatorIfNeeded($meta_row, round(microtime(true) * 1000));

		// устанавливаем создателю звонка статус CALL_STATUS_ESTABLISHING, если это single звонок
		$creator_user_schema = $meta_row["users"][$meta_row["creator_user_id"]];
		$creator_user_id     = $meta_row["creator_user_id"];
		if (Type_Call_Users::isSpeaking($creator_user_id, $meta_row["users"]) && Type_Call_Users::getStatus($creator_user_schema) == CALL_STATUS_DIALING) {

			$meta_row["users"][$creator_user_id] = Type_Call_Users::setAcceptedAt($meta_row["users"][$creator_user_id], round(microtime(true) * 1000));
			$meta_row["users"][$creator_user_id] = Type_Call_Users::setStatus($meta_row["users"][$creator_user_id], CALL_STATUS_ESTABLISHING);
		}

		Gateway_Db_CompanyCall_CallMeta::set($call_map, [
			"updated_at" => $meta_row["updated_at"],
			"users"      => $meta_row["users"],
			"extra"      => $meta_row["extra"],
		]);

		Gateway_Socket_Pivot::setLastCall([$user_id], $call_map);

		return $meta_row;
	}

	// устанавливаем временную метку принятия звонка для создателя звонка, если она не была установлена ранее
	protected static function _setAcceptedAtForCreatorIfNeeded(array $meta_row, int $accepted_at_ms):array {

		// если у создателя звонка уже установлен accepted_at, то ничего обновлять не нужно
		if (Type_Call_Users::getAcceptedAt($meta_row["users"][$meta_row["creator_user_id"]]) > 0) {
			return $meta_row["users"];
		}

		// иначе устанавливаем переданный accepted_at
		$meta_row["users"][$meta_row["creator_user_id"]] = Type_Call_Users::setAcceptedAt($meta_row["users"][$meta_row["creator_user_id"]], $accepted_at_ms);

		return $meta_row["users"];
	}

	// удаляем задачу на отслеживание dialing-звонка
	public static function removeFromDialingQueue(int $user_id, string $call_map):void {

		Gateway_Db_CompanyCall_CallMonitoringDialing::delete($user_id, $call_map);
	}

	// кладем трубку от лица пользователя
	public static function doHangup(int $user_id, string $call_map, int $finish_reason, string $finish_subject = "platform"):array {

		Gateway_Db_CompanyCall_Main::beginTransaction();
		$meta_row = Gateway_Db_CompanyCall_CallMeta::getOneForUpdate($call_map);

		self::_throwIfCallIsFinished($meta_row["is_finished"]);

		// если положили трубку из-за проблем с соединением
		self::_ifHangupDueConnectionIssue($user_id, $meta_row, $finish_reason);

		// проверяем - кладем трубку для звонка (если осталось 2 участника) или для пользователя
		$online_user_list = self::_getOnlineUserListOnHangup($meta_row["users"]);
		$is_need_finish   = self::_isNeedFinish($user_id, $online_user_list);
		if ($is_need_finish) {
			$meta_row = self::_updateMetaOnFinish($user_id, $call_map, $meta_row, $finish_reason);
		} else {
			$meta_row = self::_updateMetaOnHangup($user_id, $call_map, $meta_row, $finish_reason);
		}

		Gateway_Db_CompanyCall_Main::commitTransaction();

		Type_System_Admin::log("do_hang_up_call", [
			"user_id"       => $user_id, "call_map" => $call_map,
			"finish_reason" => $finish_reason, "finish_subject" => $finish_subject,
		]);

		// обновляем cluster_user_call_{10m}.last_call_{ceil}
		self::_updateLastCallUsersOnHangup($user_id, $finish_reason, $meta_row, $online_user_list, $call_map);

		return $meta_row;
	}

	/**
	 * если положили трубку из-за проблем с соединением
	 */
	protected static function _ifHangupDueConnectionIssue(int $user_id, array $meta_row, int $finish_reason):void {

		// если пользователь не находится в статусе ESTABLISHING
		if (Type_Call_Users::getStatus($meta_row["users"][$user_id]) !== CALL_STATUS_ESTABLISHING) {
			return;
		}

		$is_connection_issue = false;

		// если это кейс когда звонок завершают на этапе установления соединения спустя 3 секунды
		if (Type_Call_Users::getAcceptedAt($meta_row["users"][$user_id]) + 3000 < timeMs()) {
			$is_connection_issue |= true;
		}

		// если это кейс когда звонок завершают с finish_reason CALL_FINISH_REASON_LOSE_CONNECTION (потеряно соединение)
		if ($finish_reason === CALL_FINISH_REASON_LOSE_CONNECTION) {
			$is_connection_issue |= true;
		}

		// если ни один из вышеперечисленных кейсов, то ливаем
		if ($is_connection_issue === false) {
			return;
		}

		// иначе сохраняем случай
		$ip_address = Type_Call_Users::getIpAddress($meta_row["users"][$user_id]);
		Type_Call_ConnectionIssue::save($ip_address);
	}

	// получаем список пользователей, которые сейчас на линии в звонке
	protected static function _getOnlineUserListOnHangup(array $users):array {

		$online_user_list = [];
		foreach ($users as $k => $v) {

			$status = Type_Call_Users::getStatus($v);
			if (in_array($status, [CALL_STATUS_DIALING, CALL_STATUS_ESTABLISHING, CALL_STATUS_SPEAKING])) {
				$online_user_list[] = $k;
			}
		}

		return $online_user_list;
	}

	// проверяем - кладем трубку для звонка (если осталось 2 участника) или для пользователя
	protected static function _isNeedFinish(int $user_id, array $online_user_list):bool {

		$number_of_online_users = 0;
		foreach ($online_user_list as $v) {

			if ($v == $user_id) {
				continue;
			}
			$number_of_online_users++;
		}

		// если количество оставшихся пользователей меньше двух, то завершаем звонок
		if ($number_of_online_users < 2) {
			return true;
		}

		return false;
	}

	// обновляем meta_row при завершении звонка
	protected static function _updateMetaOnFinish(int $user_id, string $call_map, array $meta_row, int $finish_reason):array {

		// если это single звонок и соединение участников не было установлено до конца
		$meta_row["finished_at"] = time();
		if (self::_isSingleNotEstablishedCall($meta_row)) {
			$meta_row["started_at"] = $meta_row["finished_at"];
		}

		// обновляем все необходимые поля
		$meta_row["is_finished"] = 1;
		$meta_row["updated_at"]  = time();
		$meta_row["extra"]       = Gateway_Db_CompanyCall_CallMeta::setHangUpUserId($meta_row["extra"], $user_id);
		foreach ($meta_row["users"] as $k => $v) {
			$meta_row["users"][$k] = self::_updateUserSchemaOnFinish($v, $finish_reason, $meta_row["finished_at"]);
		}

		Gateway_Db_CompanyCall_CallMeta::set($call_map, [
			"is_finished" => $meta_row["is_finished"],
			"finished_at" => $meta_row["finished_at"],
			"updated_at"  => $meta_row["updated_at"],
			"started_at"  => $meta_row["started_at"],
			"users"       => $meta_row["users"],
			"extra"       => $meta_row["extra"],
		]);
		return $meta_row;
	}

	// если это single звонок и соединение участников не было установлено до конца
	protected static function _isSingleNotEstablishedCall(array $meta_row):bool {

		// если не single
		if ($meta_row["type"] != CALL_TYPE_SINGLE) {
			return false;
		}

		foreach ($meta_row["users"] as $v) {

			if (Type_Call_Users::getStatus($v) == CALL_STATUS_ESTABLISHING) {
				return true;
			}
		}

		return false;
	}

	// обновляем каждую user_schema участника при завершении звонка
	protected static function _updateUserSchemaOnFinish(array $user_schema, int $finish_reason, int $finished_at):array {

		// если пользователь не покидал звонок
		if (Type_Call_Users::getRole($user_schema) != Type_Call_Users::ROLE_LEAVED) {
			$user_schema = Type_Call_Users::setFinishedAt($user_schema, $finished_at);
		}

		// если пользователь не положил трубку, а все еще активен в разговоре то записываем ему finish_reason
		if (Type_Call_Users::getStatus($user_schema) != CALL_STATUS_HANGUP) {
			$user_schema = Type_Call_Users::setFinishReason($user_schema, $finish_reason);
		}

		$user_schema = Type_Call_Users::setStatus($user_schema, CALL_STATUS_HANGUP);
		$user_schema = Type_Call_Users::setSpeaking($user_schema, false);

		// если звонок был на стадии установление соединения, то устанавливаем время начала разговора равным времени завершения
		if (Type_Call_Users::getStartedAt($user_schema) < 1) {
			$user_schema = Type_Call_Users::setStartedAt($user_schema, $finished_at);
		}

		return $user_schema;
	}

	// кладем трубку конкретного пользователя
	protected static function _updateMetaOnHangup(int $user_id, string $call_map, array $meta_row, int $finish_reason):array {

		// устанавливаем статус, что пользователь положил трубку
		$meta_row["users"][$user_id] = Type_Call_Users::setStatus($meta_row["users"][$user_id], CALL_STATUS_HANGUP);
		$meta_row["users"][$user_id] = Type_Call_Users::setFinishReason($meta_row["users"][$user_id], $finish_reason);
		$meta_row["users"][$user_id] = Type_Call_Users::setSpeaking($meta_row["users"][$user_id], false);
		$meta_row["users"][$user_id] = Type_Call_Users::setLostConnection($meta_row["users"], $user_id, false);
		$meta_row["updated_at"]      = time();

		// если это групповой звонок, то помечаем, что участник покинул его
		if ($meta_row["type"] == CALL_TYPE_GROUP) {

			$meta_row["users"][$user_id] = Type_Call_Users::setFinishedAt($meta_row["users"][$user_id], time());
			$meta_row["users"][$user_id] = Type_Call_Users::setRole($meta_row["users"][$user_id], Type_Call_Users::ROLE_LEAVED);
		}

		// если звонок для пользователя так и не начался
		if (Type_Call_Users::getStartedAt($meta_row["users"][$user_id]) < 1) {
			$meta_row["users"][$user_id] = Type_Call_Users::setStartedAt($meta_row["users"][$user_id], time());
		}

		Gateway_Db_CompanyCall_CallMeta::set($call_map, [
			"updated_at" => $meta_row["updated_at"],
			"users"      => $meta_row["users"],
		]);
		return $meta_row;
	}

	// обновляем cluster_user_call_{10m}.last_call_{ceil} при hangup
	protected static function _updateLastCallUsersOnHangup(int $user_id, int $finish_reason, array $meta_row, array $online_user_list, string $call_map):void {

		// если звонок завершился
		$need_update_user_list = [];
		if ($meta_row["is_finished"] == 1) {

			// и причина завершения не LINE_IS_BUSY
			if ($finish_reason != CALL_FINISH_REASON_LINE_IS_BUSY) {

				// обновляем last_call всем активным участникам звонка
				$need_update_user_list = $online_user_list;
			}
		} else {

			// иначе обновляем только конкретному пользователю, который положил трубку
			$need_update_user_list[] = $user_id;
		}

		Gateway_Socket_Pivot::setLastCall($need_update_user_list, $call_map, 1);
	}

	// устанавливаем статус CALL_STATUS_SPEAKING для участника звонка
	public static function setSpeakingStatus(int $user_id, string $call_map):array {

		Gateway_Db_CompanyCall_Main::beginTransaction();
		$meta_row = Gateway_Db_CompanyCall_CallMeta::getOneForUpdate($call_map);

		self::_throwIfCallIsFinished($meta_row["is_finished"]);
		self::_throwIfUserCantSpeaking($user_id, $meta_row);

		// в зависимости от типа звонка
		if ($meta_row["type"] == CALL_TYPE_SINGLE) {
			$meta_row = self::_setSpeakingStatusForSingleCall($user_id, $meta_row);
		} else {
			$meta_row = self::_setSpeakingStatusForGroupCall($user_id, $meta_row);
		}

		$set = [
			"users"      => $meta_row["users"],
			"started_at" => $meta_row["started_at"],
		];
		Gateway_Db_CompanyCall_CallMeta::set($call_map, $set);
		Gateway_Db_CompanyCall_Main::commitTransaction();

		return $meta_row;
	}

	// выбрасываем exception, если состояние звонка или пользователя в нем не позволяет находиться ему в нем
	protected static function _throwIfUserCantSpeaking(int $user_id, array $meta_row):void {

		$user_schema = $meta_row["users"][$user_id];

		// если у пользователя противоречящий статус
		$status = Type_Call_Users::getStatus($user_schema);
		if ($status != CALL_STATUS_ESTABLISHING) {

			Gateway_Db_CompanyCall_Main::rollback();
			throw new cs_Call_ActionIsNotAllowed();
		}

		// если пользователя кикнули
		$role = Type_Call_Users::getRole($user_schema);
		if ($role == Type_Call_Users::ROLE_LEAVED) {

			Gateway_Db_CompanyCall_Main::rollback();
			throw new cs_Call_ActionIsNotAllowed();
		}
	}

	// изменяем meta_row при установке статуса CALL_STATUS_SPEAKING в single звонке
	protected static function _setSpeakingStatusForSingleCall(int $user_id, array $meta_row):array {

		$meta_row["users"][$user_id] = Type_Call_Users::setStatus($meta_row["users"][$user_id], CALL_STATUS_SPEAKING);
		$meta_row["users"][$user_id] = Type_Call_Users::setStartedAt($meta_row["users"][$user_id], time());
		$meta_row["users"][$user_id] = Type_Call_Users::setEstablishedAt($meta_row["users"][$user_id], round(microtime(true) * 1000));

		$opponent_user_id = Type_Call_Users::getOpponentFromSingleCall($user_id, $meta_row["users"]);
		$opponent_status  = Type_Call_Users::getStatus($meta_row["users"][$opponent_user_id]);
		if ($opponent_status != CALL_STATUS_SPEAKING) {
			return $meta_row;
		}

		$meta_row["users"][$opponent_user_id] = Type_Call_Users::setStartedAt($meta_row["users"][$opponent_user_id], time());
		if ($meta_row["started_at"] < 1) {
			$meta_row["started_at"] = time();
		}

		return $meta_row;
	}

	// изменяем meta_row при установке статуса CALL_STATUS_SPEAKING в group звонке
	protected static function _setSpeakingStatusForGroupCall(int $user_id, array $meta_row):array {

		$meta_row["users"][$user_id] = Type_Call_Users::setStatus($meta_row["users"][$user_id], CALL_STATUS_SPEAKING);
		$meta_row["users"][$user_id] = Type_Call_Users::setEstablishedAt($meta_row["users"][$user_id], round(microtime(true) * 1000));

		// если не устанавливали started_at для пользователя
		if (Type_Call_Users::getStartedAt($meta_row["users"][$user_id]) < 1) {
			$meta_row["users"][$user_id] = Type_Call_Users::setStartedAt($meta_row["users"][$user_id], time());
		}
		if ($meta_row["started_at"] < 1) {
			$meta_row["started_at"] = time();
		}

		return $meta_row;
	}

	// приглашаем участника в звонок
	public static function tryInvite(int $user_id, int $opponent_user_id, string $call_map, string $conversation_map):array {

		$opponent_last_call_row = Gateway_Socket_Pivot::getUserLastCall($opponent_user_id);
		if ($opponent_last_call_row !== false && $opponent_last_call_row->is_finished == 0 && $opponent_last_call_row->call_map != $call_map) {

			// привязываем пользователя к звонку, но помечаем как кикнутого, чтобы он мог дернуть инфу по звонку; выбрасываем exception
			self::_tryAttachUserOnLineIsBusy($user_id, $opponent_user_id, $call_map, $conversation_map);
			throw new cs_Call_LineIsBusy($opponent_user_id, $call_map, $conversation_map);
		}

		Gateway_Db_CompanyCall_Main::beginTransaction();
		$meta_row = Gateway_Db_CompanyCall_CallMeta::getOneForUpdate($call_map);

		// пытаемся привязать пользователя к звонку
		$meta_row = self::_tryInviteUser($meta_row, $user_id, $opponent_user_id, $call_map, $conversation_map);

		// коммитим все ранее открытые транзакции к базам
		Gateway_Db_CompanyCall_Main::commitTransaction();

		return $meta_row;
	}

	// привязываем пользователя к звонку, но помечаем как кикнутого, чтобы он мог дернуть инфу по звонку
	protected static function _tryAttachUserOnLineIsBusy(int $user_id, int $opponent_user_id, string $call_map, string $conversation_map):void {

		Gateway_Db_CompanyCall_Main::beginTransaction();
		$meta_row = Gateway_Db_CompanyCall_CallMeta::getOneForUpdate($call_map);

		$current_time                         = time();
		$meta_row["users"][$opponent_user_id] = Type_Call_Users::initUserSchema($conversation_map, $user_id);
		$meta_row["users"][$opponent_user_id] = Type_Call_Users::setJoinedAt($meta_row["users"][$opponent_user_id], $current_time);
		$meta_row["users"][$opponent_user_id] = Type_Call_Users::setFinishedAt($meta_row["users"][$opponent_user_id], $current_time);
		$meta_row["users"][$opponent_user_id] = Type_Call_Users::setStartedAt($meta_row["users"][$opponent_user_id], $current_time);
		$meta_row["users"][$opponent_user_id] = Type_Call_Users::setRole($meta_row["users"][$opponent_user_id], Type_Call_Users::ROLE_LEAVED);
		$meta_row["users"][$opponent_user_id] = Type_Call_Users::setFinishReason($meta_row["users"][$opponent_user_id], CALL_FINISH_REASON_LINE_IS_BUSY);
		$meta_row["users"][$opponent_user_id] = Type_Call_Users::setStatus($meta_row["users"][$opponent_user_id], CALL_STATUS_HANGUP);
		$meta_row["users"][$opponent_user_id] = Type_Call_Users::setSpeaking($meta_row["users"][$opponent_user_id], false);

		Gateway_Db_CompanyCall_CallMeta::set($call_map, [
			"type"       => CALL_TYPE_GROUP,
			"users"      => $meta_row["users"],
			"updated_at" => $current_time,
		]);
		Gateway_Db_CompanyCall_Main::commitTransaction();
	}

	// пытаемся привязать пользователя к звонку при инвайте
	protected static function _tryInviteUser(array $meta_row, int $user_id, int $opponent_user_id, string $call_map, string $conversation_map):array {

		// ряд проверок с rollback внутри, чтобы сделать их как можно унивресальнее
		self::_throwIfCallIsFinished($meta_row["is_finished"]);
		self::_throwIfUserAlreadyAcceptedCall($opponent_user_id, $meta_row["users"]);
		self::_throwIfNumberOfMembersExceeded($opponent_user_id, $meta_row["users"]);

		// обновляем meta и занимаем разговорную линию собеседника
		$meta_row = self::_updateMetaOnInvite($user_id, $opponent_user_id, $conversation_map, $meta_row);
		self::_insertOrUpdateLastCall([$opponent_user_id], $call_map);

		return $meta_row;
	}

	// выбрасываем exception, если превышается количество участников
	protected static function _throwIfNumberOfMembersExceeded(int $user_id, array $users):void {

		// проверяем, что текущий пользователь является участником звонка (например положил трубку и ему снова звонят)
		if (Type_Call_Users::isMember($user_id, $users)) {
			return;
		}

		// считаем количество не кикнутых пользователей
		$number_of_members = 0;
		foreach ($users as $v) {

			if (Type_Call_Users::getRole($v) == Type_Call_Users::ROLE_DEFAULT) {
				$number_of_members++;
			}
		}
		if ($number_of_members < CALL_MAX_MEMBER_LIMIT) {
			return;
		}

		// иначе exception
		Gateway_Db_CompanyCall_Main::rollback();
		throw new cs_Call_NumberOfMembersExceeded();
	}

	// обновляем meta запись при отправке приглашения
	protected static function _updateMetaOnInvite(int $user_id, int $opponent_user_id, string $conversation_map, array $meta_row):array {

		$meta_row               = self::_createOrUpdateUserSchemaOnInvite($user_id, $opponent_user_id, $conversation_map, $meta_row);
		$meta_row["updated_at"] = time();
		$meta_row["type"]       = CALL_TYPE_GROUP;

		Gateway_Db_CompanyCall_CallMeta::set($meta_row["call_map"], [
			"type"       => $meta_row["type"],
			"users"      => $meta_row["users"],
			"updated_at" => $meta_row["updated_at"],
		]);

		return $meta_row;
	}

	// создаем или обновляем существующую user_schema приглашаемого участника
	protected static function _createOrUpdateUserSchemaOnInvite(int $user_id, int $opponent_user_id, string $conversation_map, array $meta_row):array {

		if (isset($meta_row["users"][$opponent_user_id])) {

			if (Type_Call_Users::getRole($meta_row["users"][$opponent_user_id]) == Type_Call_Users::ROLE_LEAVED) {

				// перезаписываем временные метки
				$meta_row["users"][$opponent_user_id] = Type_Call_Users::setJoinedAt($meta_row["users"][$opponent_user_id], time());
				$meta_row["users"][$opponent_user_id] = Type_Call_Users::setStartedAt($meta_row["users"][$opponent_user_id], 0);
			}

			$meta_row["users"][$opponent_user_id] = Type_Call_Users::setRole($meta_row["users"][$opponent_user_id], Type_Call_Users::ROLE_DEFAULT);
			$meta_row["users"][$opponent_user_id] = Type_Call_Users::setStatus($meta_row["users"][$opponent_user_id], CALL_STATUS_DIALING);
			$meta_row["users"][$opponent_user_id] = Type_Call_Users::setInvitedByUserId($meta_row["users"][$opponent_user_id], $user_id);
		} else {

			$meta_row["users"][$opponent_user_id] = Type_Call_Users::initUserSchema($conversation_map, $user_id);
			$meta_row["users"][$opponent_user_id] = Type_Call_Users::setJoinedAt($meta_row["users"][$opponent_user_id], time());
		}

		return $meta_row;
	}

	// получаем запись с последним звонком пользователя
	public static function getUserLastCall(int $user_id):Struct_Socket_Pivot_UserLastCall|false {

		return Gateway_Socket_Pivot::getUserLastCall($user_id);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// выбрасываем exception, если звонок уже завершен
	protected static function _throwIfCallIsFinished(int $is_finished):void {

		// если звонок не завершен, то делать здесь нечего :)
		if ($is_finished == 0) {
			return;
		}

		Gateway_Db_CompanyCall_Main::rollback();
		throw new cs_Call_IsFinished();
	}

	// выбрасываем exception, если звонок уже принят пользователем
	protected static function _throwIfUserAlreadyAcceptedCall(int $user_id, array $users):void {

		// если вообще не участник
		if (!Type_Call_Users::isMember($user_id, $users)) {
			return;
		}

		// если участник уже разговаривает
		if (Type_Call_Users::isSpeaking($user_id, $users)) {

			Gateway_Db_CompanyCall_Main::rollback();
			throw new cs_Call_UserAlreadyAcceptedCall();
		}
	}

	// добавляем новую запись последнего звонка либо обновляем её
	protected static function _insertOrUpdateLastCall(array $user_call_list, string $call_map):void {

		Gateway_Socket_Pivot::setLastCall($user_call_list, $call_map);
	}
}
