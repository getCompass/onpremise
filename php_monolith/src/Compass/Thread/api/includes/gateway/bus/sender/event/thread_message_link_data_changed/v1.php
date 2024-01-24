<?php

namespace Compass\Thread;

/**
 * класс описывающий событие action.thread_message_link_data_changed версии 1
 */
class Gateway_Bus_Sender_Event_ThreadMessageLinkDataChanged_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "action.thread_message_link_data_changed";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"message_map"             => \Entity_Validator_Structure::TYPE_STRING,
		"link_list"               => \Entity_Validator_Structure::TYPE_ARRAY,
		"threads_updated_version" => \Entity_Validator_Structure::TYPE_INT,
		"?preview_map"            => \Entity_Validator_Structure::TYPE_STRING,
		"?preview_type"           => \Entity_Validator_Structure::TYPE_STRING,
		"?preview_image"          => \Entity_Validator_Structure::TYPE_OBJECT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(string $message_map,
						   array $link_list,
						   int $threads_updated_version,
						   string $preview_map = null,
						   int $preview_type = null,
						   array $preview_image = []):Struct_Sender_Event {

		// собираем данные запроса
		$ws_data = [
			"message_map"             => (string) $message_map,
			"link_list"               => (array) $link_list,
			"threads_updated_version" => (int) $threads_updated_version,
		];

		// если preview_map был передан
		if (!is_null($preview_map) && !is_null($preview_type)) {

			// добавляем его к событию
			$ws_data["preview_map"]  = (string) $preview_map;
			$ws_data["preview_type"] = (string) Type_Preview_Main::PREVIEW_TYPE_SCHEMA[$preview_type];

			// если у превью есть изображение
			if (count($preview_image) > 0) {
				$ws_data["preview_image"] = (object) $preview_image;
			}
		}

		return self::_buildEvent($ws_data);
	}
}