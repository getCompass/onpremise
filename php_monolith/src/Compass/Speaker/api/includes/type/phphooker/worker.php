<?php

namespace Compass\Speaker;

/**
 * крон для исполнения задач
 */
class Type_Phphooker_Worker {

	// выполнить задачу
	// @long switch
	public function doTask(int $task_type, array $params):bool {

		// развилка по типу задачи
		switch ($task_type) {

			// отправляем в диалог сообщение в диалог
			case Type_Phphooker_Main::TASK_TYPE_ADD_MESSAGE_CALL:

				return self::_addCallMessage($params["conversation_map"], $params["call_map"], $params["sender_user_id"], $params["platform"]);

			// создаем записи в таблице history
			case Type_Phphooker_Main::TASK_TYPE_INSERT_TO_HISTORY:

				return self::_insertToHistory(
					$params["users"],
					$params["creator_user_id"],
					$params["call_map"],
					$params["type"]
				);

			// завершаем звонок между пользователями
			case Type_Phphooker_Main::TASK_TYPE_DO_FINISH_SINGLE_CALL:

				return self::_doFinishCall($params["call_map"], $params["user_id"], $params["opponent_user_id"]);

			// изменяем битрейт разговорной комнаты, если ситуация того требует
			case Type_Phphooker_Main::TASK_TYPE_CHANGE_CALL_BITRATE_IF_NEEDED:

				self::_changeCallBitrateIfNeeded($params["call_map"]);
				return true;

			case Type_Phphooker_Main::TASK_TYPE_DIALING_MONITORING:
			case Type_Phphooker_Main::TASK_TYPE_ESTABLISHING_MONITORING:
				return true;

			default:

				throw new \parseException("Unhandled task_type [{$task_type}] in " . __METHOD__);
		}
	}

	// -------------------------------------------------------
	// ЛОГИКА ВЫПОЛНЕНИЯ ЗАДАЧ
	// -------------------------------------------------------

	// отправляем в диалог сообщение о звонке
	protected static function _addCallMessage(string $conversation_map, string $call_map, int $user_id, string $platform):bool {

		try {
			$result = Gateway_Socket_Conversation::addCallMessage($conversation_map, $call_map, $user_id, $platform);
		} catch (Domain_Member_Exception_AttemptInitialCall) {
			return true;
		} catch (Exception $e) {

			// логируем исключение
			$exception_message = \BaseFrame\Exception\ExceptionUtils::makeMessage($e, 0);
			\BaseFrame\Exception\ExceptionUtils::writeExceptionToLogs($e, $exception_message);

			return false;
		}

		return $result;
	}

	// создаем записи в таблице history
	protected static function _insertToHistory(array $users, int $creator_user_id, string $call_map, int $type):bool {

		$insert_array = [];
		foreach ($users as $k => $v) {

			$insert_array[] = [
				"user_id"         => $k,
				"call_map"        => $call_map,
				"type"            => $type,
				"creator_user_id" => $creator_user_id,
				"created_at"      => time(),
				"updated_at"      => 0,
				"started_at"      => Type_Call_Users::getStartedAt($v),
				"finished_at"     => Type_Call_Users::getFinishedAt($v),
			];
		}
		Gateway_Db_CompanyCall_CallHistory::insertArray($insert_array);

		return true;
	}

	// завершаем звонок между пользователями
	protected static function _doFinishCall(string $call_map, int $user_id, int $opponent_user_id):bool {

		// если это групповой диалог, то пропускаем
		$meta_row = Type_Call_Meta::get($call_map);
		if ($meta_row["type"] == CALL_TYPE_GROUP) {
			return true;
		}

		// проверяем, что собеседник является участником звонка
		if (!Type_Call_Users::isMember($opponent_user_id, $meta_row["users"])) {
			return true;
		}

		// получаем причину завершения (если звонок на стадии гудков - звонок отменен)
		$finish_reason = CALL_FINISH_REASON_HANGUP;
		$status        = Type_Call_Users::getStatus($meta_row["users"][$user_id]);
		if ($status == CALL_STATUS_DIALING) {
			$finish_reason = CALL_FINISH_REASON_CANCELED;
		}

		// завершаем звонок
		Helper_Calls::doHangup($user_id, $call_map, $finish_reason, "conversation");
		return true;
	}

	// изменяем битрейт разговорной комнаты, если ситуация того требует
	protected static function _changeCallBitrateIfNeeded(string $call_map):void {

		// получаем мета звонка; проверяем завершился ли звонок
		$meta_row = Type_Call_Meta::get($call_map);
		if ($meta_row["is_finished"] == 1) {
			return;
		}

		// получаем запись разговорной комнаты; проверяем, что она существует
		$room_row = Type_Janus_Room::getByCallMap($call_map);
		if (!isset($room_row["call_map"])) {
			return;
		}

		// получаем все publisher-соединения звонка; завершаем, если соединений недостаточно
		$publisher_list = Type_Janus_UserConnection::getPublisherListByCallMap($call_map);
		if (count($publisher_list) < 2) {
			return;
		}

		$new_bitrate = Helper_Janus::doCountOptimalBitrate($publisher_list, $meta_row["users"], $room_row["bitrate"]);
		if ($new_bitrate == $room_row["bitrate"]) {
			return;
		}
		Helper_Janus::doChangeBitrate($call_map, $room_row, $new_bitrate);
	}
}