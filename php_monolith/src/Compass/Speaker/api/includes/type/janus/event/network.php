<?php

namespace Compass\Speaker;

/**
 * Type_Janus_Event_Network — класс для обработки событий связанных с изменениями
 * качества связи и подобными событиями от Janus WebRTC Server
 */
class Type_Janus_Event_Network {

	// количество детектов потери медиа пакетов подряд, после которого принимаем решение, что соединение потеряно
	protected const _MAX_MEDIA_PACKET_LOSS_COUNTER = 5;

	// точка входа для обработки network событий
	public static function doHandle(array $event_data):void {

		if (!self::_checkCorrectEventFormat($event_data)) {
			return;
		}

		$connection_row = self::_getUserConnectionRow($event_data["session_id"], $event_data["handle_id"]);
		if (self::_isConnectionLost($connection_row)) {

			Helper_Janus::onUserConnectionLoss($connection_row);
			return;
		}

		// для паблишера анализируем была ли потеря соединения и если да - пересчитываем (audio/video)_loss_counter
		if ($connection_row["is_publisher"] == 1) {
			self::_doAnalyzeConnectionLoss($event_data, $connection_row);
		}

		// оповещаем, если сменилось качество связи
		self::_doCheckConnectionQualityChange($event_data, $connection_row);
	}

	// функция проверяет, что данные события имеют корректный формат
	protected static function _checkCorrectEventFormat(array $event_data):bool {

		// если нет информации о переданных данных, то заканчиваем выполнение
		if (!isset($event_data["event"]["bytes-received-lastsec"]) || !isset($event_data["event"]["bytes-sent-lastsec"])) {
			return false;
		}

		return true;
	}

	// функция получает запись о пользовательском соединении
	protected static function _getUserConnectionRow(int $session_id, int $handle_id):array {

		$user_connection_row = Type_Janus_UserConnection::get($session_id, $handle_id);
		if (!isset($user_connection_row["session_id"])) {
			throw new \paramException(__METHOD__ . ": user connection not found");
		}

		return $user_connection_row;
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

		// если счетчик потерянных медиа данных превысил границу, то считаем соединение потерянным
		if ($connection_row["audio_loss_counter"] >= self::_MAX_MEDIA_PACKET_LOSS_COUNTER
			|| $connection_row["video_loss_counter"] >= self::_MAX_MEDIA_PACKET_LOSS_COUNTER) {
			return true;
		}

		return false;
	}

	// анализируем, есть ли потеря соединения
	protected static function _doAnalyzeConnectionLoss(array $data, array $connection_row):void {

		$media_type = $data["event"]["media"];
		if (!in_array($media_type, ["audio", "video"])) { // если пришел неизвестный meida type
			throw new \returnException(__METHOD__ . ": passed unhandled media type");
		}

		// если речь про видео, но пользователь его не шлет
		if ($media_type == "video" && $connection_row["is_send_video"] == 0) {
			return;
		}

		$is_send_empty = true;
		if ($data["event"]["bytes-received-lastsec"] > 0) { // если есть передача данных
			$is_send_empty = false;
		} else { // если передачи данных нет

			// если клиент пингует сервер, то все окей
			if ($connection_row["last_ping_at"] >= time() - Type_Janus_UserConnection::CONNECTION_LOST_TIME_OUT) {
				$is_send_empty = false;
			}
		}

		// обновляем счетчик
		self::_setMediaLossCounter($data["session_id"], $data["handle_id"], $media_type, $connection_row["{$media_type}_loss_counter"], $is_send_empty);
	}

	// функция для обновления (audio/video)_loss_counter полей
	protected static function _setMediaLossCounter(int $session_id, int $handle_id, string $media, int $original_loss_counter, bool $is_send_empty):void {

		$field_name = "{$media}_loss_counter";
		$new_value  = $original_loss_counter;
		if ($is_send_empty) {
			$new_value++;
		} else {
			$new_value = 0;
		}

		if ($original_loss_counter == $new_value || $new_value > self::_MAX_MEDIA_PACKET_LOSS_COUNTER) {
			return;
		}

		// если в событии от janus получили, что передача медиа данных имеется — то обнуляем счетчик
		// иначе инкрементим на единичку
		Gateway_Db_CompanyCall_JanusConnectionList::set($session_id, $handle_id, [
			$field_name  => $new_value == 0 ? 0 : "{$field_name} + 1",
			"updated_at" => (int) time(),
		]);
	}

	// проверяем смену качества соединения
	protected static function _doCheckConnectionQualityChange(array $event_data, array $connection_row):void {

		// если это не publisher соединение, то завершаем функцию
		if ($connection_row["is_publisher"] != 1) {
			return;
		}

		// если медиа-данные по которым пришло событие отключены, то завершаем функцию
		$media_type = $event_data["event"]["media"];
		if ($connection_row["is_send_{$media_type}"] == 0) {
			return;
		}

		// получаем текущее качество соединения
		$current_quality_state = self::_getCurrentQualityState($event_data, $connection_row);

		// проверяем, есть ли динамика смены качества связи
		if (!self::_isConnectionQualityChanging($event_data, $connection_row, $current_quality_state)) {

			// обновляем audio/video_packet_loss, если нужно
			self::_doUpdatePacketLossIfNeeded($event_data, $connection_row);
			return;
		}

		// обновляем запись соединения, если оно было восстановлено
		self::_doUpdateIfConnectionWasRestored($connection_row);

		// если динамика все же есть, то обновляем запись соединения, а быть может и уведомляем о смене качества соединения
		self::_onConnectionQualityChanging($event_data, $connection_row, $current_quality_state);
	}

	// функция для получения качества соединения
	protected static function _getCurrentQualityState(array $event_data, array $connection_row):int {

		// временный массив, куда сложим различные оценки качества соединения
		$temp = [];

		// получаем качество соединения на основе параметра in/out-link-quality
		$temp[] = self::_getCurrentQualityStateByLinkQuality($event_data);

		// получаем качество соединения на основе имеющихся потерь пакетов
		$temp[] = self::_getCurrentQualityStateByPacketLost($event_data, $connection_row);

		// выбираем минимальное, его и возвращаем
		return min($temp);
	}

	// функция для получения качества соединения на основе параметра in/out-link-quality
	protected static function _getCurrentQualityStateByLinkQuality(array $event_data):int {

		// получаем самую меньшую метку о качестве соединения на основе параметра in/out-link-quality
		$quality = min($event_data["event"]["in-link-quality"], $event_data["event"]["in-media-link-quality"]);

		// определяем уровень качества в зависимости от отметки красной линии
		return $quality < Type_Janus_UserConnection::QUALITY_RED_LINE ?
			Type_Janus_UserConnection::QUALITY_STATE_BAD : Type_Janus_UserConnection::QUALITY_STATE_PERFECT;
	}

	// функция для получения качества соединения на основе имеющихся потерь пакетов
	protected static function _getCurrentQualityStateByPacketLost(array $event_data, array $connection_row):int {

		$total_packet_loss = $event_data["event"]["lost"];

		$media_type                 = $event_data["event"]["media"];
		$packet_loss_from_last_time = $total_packet_loss - $connection_row["{$media_type}_packet_loss"];

		// если есть хотя бы один потерянный пакет — помечаем в системе, что соединение не стабильное
		// это не значит, что пользователю с ходу придет плашка плохое подключение
		// оно придет только в том случае, если будут наблюдаться потери пакетов равное Type_Janus_UserConnection::BAD_QUALITY_COUNTER_LIMIT раз
		return $packet_loss_from_last_time > 0 ?
			Type_Janus_UserConnection::QUALITY_STATE_BAD : Type_Janus_UserConnection::QUALITY_STATE_PERFECT;
	}

	// проверяем, есть ли динамика смены качества связи
	protected static function _isConnectionQualityChanging(array $event_data, array $connection_row, int $current_quality_state):bool {

		$media_type = $event_data["event"]["media"];

		// если текущее соединение хорошее и оно не ухудшалось за последнее время
		if ($current_quality_state == Type_Janus_UserConnection::QUALITY_STATE_PERFECT && $connection_row["{$media_type}_bad_quality_counter"] == 0) {
			return false;
		}

		// если текущее соединение плохое и оно не восстанавливалось за последнее время
		if ($current_quality_state == Type_Janus_UserConnection::QUALITY_STATE_BAD
			&& $connection_row["{$media_type}_bad_quality_counter"] >= Type_Janus_UserConnection::BAD_QUALITY_COUNTER_LIMIT) {
			return false;
		}

		// в любом другом случае динамика качества связи существует —> возможна смена качества связи
		return true;
	}

	// обновляем audio/video_packet_loss, если нужно
	protected static function _doUpdatePacketLossIfNeeded(array $event_data, array $connection_row):void {

		$media_type = $event_data["event"]["media"];
		if ($connection_row["{$media_type}_packet_loss"] == $event_data["event"]["lost"]) {
			return;
		}

		$set = [
			"{$media_type}_packet_loss" => min((int) $event_data["event"]["lost"], 100000),
			"updated_at"                => (int) time(),
		];
		Type_Janus_UserConnection::set($connection_row["session_id"], $connection_row["handle_id"], $set);
	}

	// обновляем запись соединения, если оно было восстановлено
	protected static function _doUpdateIfConnectionWasRestored(array $connection_row):void {

		// если у пользователя восстановилось соединение (после reconnect)
		if ($connection_row["quality_state"] == Type_Janus_UserConnection::QUALITY_STATE_LOST) {

			// обновляем флаг is_lost_connection пользователя в meta_{D} звонка
			Type_Call_Meta::setUserLostConnection($connection_row["call_map"], $connection_row["user_id"], false);

			// удаляем задачу на отслеживания установления соединения
			Type_Janus_UserConnection::stopMonitorEstablishingConnectTimeout($connection_row["call_map"], $connection_row["user_id"]);
		}
	}

	// функция вызывается при наличии динамики смены качества связи
	protected static function _onConnectionQualityChanging(array $event_data, array $user_connection_row, int $current_connection_quality):void {

		$media_type = $event_data["event"]["media"];

		// в зависимости от текущего качества соединения инкрементим или декрементим
		if ($current_connection_quality == Type_Janus_UserConnection::QUALITY_STATE_PERFECT) {

			$user_connection_row["{$media_type}_bad_quality_counter"]--;

			// чтобы не ушло в отрицательное значение
			$user_connection_row["{$media_type}_bad_quality_counter"] = max($user_connection_row["{$media_type}_bad_quality_counter"], 0);
		} else {

			$user_connection_row["{$media_type}_bad_quality_counter"]++;

			// чтобы не превысило лимит
			$user_connection_row["{$media_type}_bad_quality_counter"] = min($user_connection_row["{$media_type}_bad_quality_counter"],
				Type_Janus_UserConnection::BAD_QUALITY_COUNTER_LIMIT);
		}

		// нужно ли менять качество связи
		$is_need_change_quality = false;
		if (in_array($user_connection_row["{$media_type}_bad_quality_counter"], [0, Type_Janus_UserConnection::BAD_QUALITY_COUNTER_LIMIT]) &&
			$current_connection_quality != $user_connection_row["quality_state"]) {
			$is_need_change_quality = true;
		}

		// обновляем запись соединения
		self::_doUpdateConnectionRow($event_data, $user_connection_row, $current_connection_quality, $is_need_change_quality);
	}

	// обновляем запись соединения
	protected static function _doUpdateConnectionRow(array $event_data, array $user_connection_row, int $current_connection_quality, bool $is_need_change_quality):void {

		$set = [
			"status"        => (int) Type_Janus_UserConnection::STATUS_CONNECTED,
			"quality_state" => (int) ($is_need_change_quality ? $current_connection_quality : $user_connection_row["quality_state"]),
			"updated_at"    => (int) time(),
		];

		$media_type                               = $event_data["event"]["media"];
		$set["{$media_type}_bad_quality_counter"] = (int) $user_connection_row["{$media_type}_bad_quality_counter"];

		// количество потерянных пакетов
		$set["{$media_type}_packet_loss"] = min((int) $event_data["event"]["lost"], 100000);

		Type_Janus_UserConnection::set($user_connection_row["session_id"], $user_connection_row["handle_id"], $set);
	}
}
