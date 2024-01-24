<?php

namespace Compass\Speaker;

/**
 * Type_Janus_Event_Room — класс для обработки событий связанных с проишествиями
 * в разговорных комнатах
 */
class Type_Janus_Event_Room {

	// точка входа для обработки room событий
	public static function doHandle(array $event_data):void {

		if (!self::_checkCorrectEventFormat($event_data)) {
			return;
		}

		// при конфигурации подключения; сюда попадают только publisher подключения!
		if (self::_isConnectionConfigured($event_data)) {

			$connection_row = Type_Janus_UserConnection::get($event_data["session_id"], $event_data["handle_id"]);
			if (!isset($connection_row["user_id"])) {
				return;
			}

			// если это не новое подключение и событие говорит о переключении аудио/видео
			if (self::_isConnectionUpgrade($event_data, $connection_row) && !self::_isNewConnection($connection_row)) {
				self::_onConnectionUpgrade($event_data, $connection_row);
			}
		}
	}

	// функция проверяет, что данные события имеют корректный формат
	protected static function _checkCorrectEventFormat(array $event_data):bool {

		if (!isset($event_data["event"]["data"]["event"])) {
			return false;
		}

		return true;
	}

	// функция проверяет, что событие о подключении нового участника
	protected static function _isConnectionConfigured(array $event_data):bool {

		if ($event_data["event"]["data"]["event"] != "configured") {
			return false;
		}

		return true;
	}

	// функция проверяет, что это случай нового подключения
	protected static function _isNewConnection(array $connection_row):bool {

		// если саб, то пропускаем
		if ($connection_row["is_publisher"] != 1) {
			return false;
		}

		// если новое подключение
		if ($connection_row["status"] == Type_Janus_UserConnection::STATUS_ESTABLISHING) {
			return true;
		}

		return false;
	}

	// функция проверяет, что это случай switch аудио/видео потоков
	protected static function _isConnectionUpgrade(array $event_data, array $connection_row):bool {

		// проверяем, что изменился флаг audio/video
		if ($connection_row["is_send_video"] == intval($event_data["event"]["data"]["video_active"])
			&& $connection_row["is_send_audio"] == intval($event_data["event"]["data"]["audio_active"])) {
			return false;
		}

		return true;
	}

	// функция выполняется при изменении передачи аудио/видео потоков
	protected static function _onConnectionUpgrade(array $event_data, array $user_connection_row):void {

		// обновляем запись connection_row, выставляя актуальные параметры
		Gateway_Db_CompanyCall_JanusConnectionList::set($user_connection_row["session_id"], $user_connection_row["session_id"], [
			"is_send_video" => $event_data["event"]["data"]["video_active"],
			"is_send_audio" => $event_data["event"]["data"]["audio_active"],
		]);
	}
}
