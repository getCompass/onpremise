<?php

namespace Compass\Speaker;

/*
 * Type_Janus_Event_Handler класс для обработки входящих событий от Janus WebRTC Server
 */

class Type_Janus_Event_Handler {

	protected const _OFFER_ANSWER_SETTING_EVENT = 8;
	protected const _CONNECTION_EVENT           = 16;
	protected const _NETWORK_EVENT              = 32;
	protected const _ROOM_EVENT                 = 64;

	// точка входа — первичная функция обрабатывающая событие
	// @long
	public static function doHandle(array $event_data):void {

		if (!self::_checkCorrectEventFormat($event_data)) {
			return;
		}

		switch ($event_data["type"]) {

			// события на изменения/установления offer/answer
			case self::_OFFER_ANSWER_SETTING_EVENT:

				Type_Janus_Event_OfferAnswer::doHandle($event_data);
				break;

			// события происходящие с подключениями (webrtcup, hangup)
			case self::_CONNECTION_EVENT:

				Type_Janus_Event_Connection::doHandle($event_data);
				break;

			// события на изменения сетевых параметров
			case self::_NETWORK_EVENT:

				Type_Janus_Event_Network::doHandle($event_data);
				break;

			// события на проишествиях в комнате звонка
			case self::_ROOM_EVENT:

				Type_Janus_Event_Room::doHandle($event_data);
				break;
		}
	}

	// функция проверяет, что данные события имеют корректный формат
	protected static function _checkCorrectEventFormat(array $event_data):bool {

		// если не пришел тип ивента
		if (!isset($event_data["type"]) || !isset($event_data["event"])) {
			return false;
		}

		return true;
	}

	// сортируем список событий по важности
	public static function doSortByImportance(array $event_list):array {

		// двигаем события о локальных sdp offer & answer в начало массива
		$event_list = self::_moveToArrayBeginning($event_list, "offer");
		$event_list = self::_moveToArrayBeginning($event_list, "answer");

		return $event_list;
	}

	// передвигаем в начало массива
	protected static function _moveToArrayBeginning(array $event_list, string $sdp_type):array {

		// проходимся по списку ивентов
		foreach ($event_list as $k => $event_item) {

			// если это не нужный нам ивент
			if (!isset($event_item["event"]["owner"], $event_item["event"]["jsep"])
				|| ($event_item["event"]["owner"] != "local" || $event_item["event"]["jsep"]["type"] != $sdp_type)) {
				continue;
			}

			// убираем со старого места
			unset($event_list[$k]);

			// добавляем в начало массива списка событий
			array_unshift($event_list, $event_item);
		}

		return $event_list;
	}
}