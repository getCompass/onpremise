<?php

namespace Compass\Speaker;

/**
 * Type_Janus_Event_OfferAnswer — класс для обработки событий связанных с изменениями/установлениями answer & offer
 */
class Type_Janus_Event_OfferAnswer {

	// точка входа для обработки offer/answer событий
	public static function doHandle(array $event_data):void {

		if (!self::_checkCorrectEventFormat($event_data)) {
			return;
		}

		// при offer событии — случается когда publisher заходит в разговорную комнату или обновляет sdp offer
		if (self::_isOfferReceive($event_data)) {
			self::_onOfferReceive($event_data["session_id"], $event_data["handle_id"]);
		}
	}

	// функция проверяет, что данные события имеют корректный формат
	protected static function _checkCorrectEventFormat(array $event_data):bool {

		if (!isset($event_data["event"]["jsep"])) {
			return false;
		}

		return true;
	}

	// -------------------------------------------------------
	// OFFER СОБЫТИЕ
	// -------------------------------------------------------

	// функция проверяет, что пришедшее событие — offer
	protected static function _isOfferReceive(array $event_data):bool {

		if ($event_data["event"]["jsep"]["type"] == "offer" && $event_data["event"]["owner"] == "local") {
			return true;
		}

		return false;
	}

	// функция обрабатывает пришедшее offer событие
	protected static function _onOfferReceive(int $session_id, int $handle_id):void {

		// получаем запись соединения пользователя
		$user_call_connection_row = Gateway_Db_CompanyCall_JanusConnectionList::get($session_id, $handle_id);
		if (!isset($user_call_connection_row["user_id"])) {

			Type_System_Admin::log("undefined_user_id (offeranswer)", [$session_id, $handle_id]);
			return;
		}

		// декрементим publisher_upgrade_count т.к. это случай, когда участник звонка переключил аудио/видео
		if ($user_call_connection_row["publisher_upgrade_count"] > 0) {

			Type_Janus_UserConnection::set($session_id, $handle_id, [
				"publisher_upgrade_count" => "publisher_upgrade_count - 1",
			]);
		}
	}
}
