<?php

namespace Compass\Speaker;

// класс для взаиможействия с системными событиями
class Type_SystemEvent_Main {

	// текущая версия формата события
	protected const _VERSION = 1;

	// необходимые поля для события
	protected const _STRUCTURE = ["event_type", "source_type", "source_identifier"];

	// генерирует базовый шаблон события
	public static function make(string $event_category, string $event_type, string $source_type, string $source_id, array $data = []):array {

		// получаем класс-резолвер
		$category_handler = "Type_SystemEvent_" . ucfirst(strtolower($event_category));

		// получаем шаблон данных события
		$event_data_structure = $category_handler::getStructure($event_type);

		if ($event_data_structure === false) {
			throw new cs_InvalidEventArgumentsException("incorrect event type");
		}

		// формируем событие
		return [
			"event_type"        => "{$event_category}.{$event_type}",
			"source_type"       => $category_handler::validateSourceType($source_type),
			"source_identifier" => $category_handler::validateSourceId($source_id),
			"data_version"      => $event_data_structure["version"],
			"version"           => self::_VERSION,
			"uuid"              => generateUUID(),
			"event_data"        => self::_makeEventData($event_data_structure, $data),
		];
	}

	// создает событие и пушит его в сервис событий
	public static function push(string $event_category, string $event_type, string $source_type, string $source_id, array $data = []):bool {

		try {

			// создаем событие и тут же пушим его
			$event = self::make($event_category, $event_type, $source_type, $source_id, $data);
			\Bus::systemevent()->pushEvent($event);

			return true;
		} catch (cs_InvalidEventArgumentsException) {
			return false;
		}
	}

	// проводит проверку события и преобразовывает его при необходимости
	public static function validate(array $event):array {

		// проверяем, что это вообще событие
		foreach (self::_STRUCTURE as $v) {

			if (!isset($event[$v])) {
				throw new cs_InvalidEventArgumentsException("incorrect event structure");
			}
		}

		// разбиваем на категорию - тип
		$split = explode(".", $event["event_type"]);

		// получаем класс-резолвер и структуру данных события
		$category_handler     = "Type_SystemEvent_" . ucfirst(strtolower($split[0]));
		$event_data_structure = $category_handler::getStructure($split[1]);

		if ($event_data_structure === false) {
			throw new cs_InvalidEventArgumentsException("incorrect event type");
		}

		// формируем новые данные события
		$event["event_data"] = self::_makeEventData($event_data_structure, $event["event_data"] ?: []);
		return $event;
	}

	// -----------------
	// PROTECTED
	// -----------------

	// создает данные события по шаблону
	protected static function _makeEventData(array $structure, array $data):array {

		$output = [];

		// собираем требуемые поля
		foreach ($structure["required"] as $v) {

			if (!isset($data[$v])) {
				throw new cs_InvalidEventArgumentsException("bad data structure");
			}

			$output[$v] = $data[$v];
		}

		// собираем поля, для которых возможно дефолтное значение
		foreach ($structure["default"] as $k => $v) {
			$output[$k] = isset($data[$k]) ? $data[$k] : $v;
		}

		return $output;
	}
}