<?php

namespace Compass\Speaker;

/**
 * класс для исполнения задач через phphooker
 * ВАЖНО! для каждого типа задачи должна быть функция здесь
 */
class Type_Phphooker_Main {

	##########################################################
	# region типы задач
	##########################################################

	const TASK_TYPE_ADD_MESSAGE_CALL              = 1; // добавление сообщения о звонке в диалог
	const TASK_TYPE_INSERT_TO_HISTORY             = 2; // создаем записи в таблице company_call.call_history
	const TASK_TYPE_DO_FINISH_SINGLE_CALL         = 3; // завершаем звонок
	const TASK_TYPE_CHANGE_CALL_BITRATE_IF_NEEDED = 4; // изменяем битрейт разговорной комнаты, если ситуация того требует
	const TASK_TYPE_DIALING_MONITORING            = 5;
	const TASK_TYPE_ESTABLISHING_MONITORING       = 6;

	# endregion
	##########################################################

	##########################################################
	# region методы для добавления задач
	##########################################################

	// добавляем сообщение в диалог
	public static function addCallMessage(string $conversation_map, string $call_map, int $user_id):void {

		self::_addFromApi(self::TASK_TYPE_ADD_MESSAGE_CALL, [
			"conversation_map" => $conversation_map,
			"call_map"         => $call_map,
			"sender_user_id"   => $user_id,
			"platform"         => Type_Api_Platform::getPlatform(),
		]);
	}

	// добавляем записи в таблицу history для каждого участника звонка
	public static function insertToHistory(int $creator_user_id, string $call_map, int $type, array $users):void {

		self::_addFromApi(self::TASK_TYPE_INSERT_TO_HISTORY, [
			"users"           => $users,
			"call_map"        => $call_map,
			"type"            => $type,
			"creator_user_id" => $creator_user_id,
		]);
	}

	// завершаем звонок между пользователями
	public static function doFinishSingleCall(string $call_map, int $user_id, int $opponent_user_id):void {

		self::_addFromApi(self::TASK_TYPE_DO_FINISH_SINGLE_CALL, [
			"call_map"         => $call_map,
			"user_id"          => $user_id,
			"opponent_user_id" => $opponent_user_id,
		]);
	}

	// изменяем битрейт разговорной комнаты, если ситуация того требует
	public static function changeCallBitrateIfNeeded(string $call_map):void {

		self::_addFromApi(self::TASK_TYPE_CHANGE_CALL_BITRATE_IF_NEEDED, [
			"call_map" => $call_map,
		]);
	}

	// мониторинг подключениея
	public static function dialingMonitoring(int $user_id, string $call_map):void {

		self::_addFromApi(self::TASK_TYPE_DIALING_MONITORING, [
			"call_map" => $call_map,
			"user_id"  => $user_id,
		]);
	}

	// мониторинг соединения
	public static function establishingMonitoring(int $user_id, string $call_map):void {

		self::_addFromApi(self::TASK_TYPE_ESTABLISHING_MONITORING, [
			"call_map" => $call_map,
			"user_id"  => $user_id,
		]);
	}

	# endregion
	##########################################################

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// добавить задачу в очередь из API запроса пользователя
	protected static function _addFromApi(int $task_type, array $params):void {

		$event_data = Type_Event_Task_TaskAddedSpeaker::create($task_type, $params);

		Gateway_Event_Dispatcher::dispatch($event_data);
	}
}
