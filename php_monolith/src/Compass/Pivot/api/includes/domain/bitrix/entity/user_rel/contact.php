<?php

namespace Compass\Pivot;

/**
 * класс описывает работу с данными сущности "Контакт", которые сохранены в поле bitrix_entity_list
 * в таблице bitrix_user_entity_rel
 */
class Domain_Bitrix_Entity_UserRel_Contact {

	public const ENTITY_TYPE = "contact";

	protected const _SCHEME_CURRENT_VERSION = 1;
	protected const _SCHEME_LIST_BY_VERSION = [
		1 => [
			"id" => 0,
		],
	];

	/**
	 * Инициализируем структуру данных entity_data контакта
	 *
	 * @return array
	 */
	public static function init(int $contact_id):array {

		$entity_data            = self::_SCHEME_LIST_BY_VERSION[self::_SCHEME_CURRENT_VERSION];
		$entity_data["id"]      = $contact_id;
		$entity_data["version"] = self::_SCHEME_CURRENT_VERSION;

		return [
			"entity_type" => self::ENTITY_TYPE,
			"entity_data" => $entity_data,
		];
	}

	/**
	 * Получаем ID контакта
	 *
	 * @return int
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getContactID(array $entity_item):int {

		if (!isset($entity_item["entity_type"], $entity_item["entity_data"]) || $entity_item["entity_type"] !== self::ENTITY_TYPE) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("unexpected item");
		}

		// актуализируем entity_data
		$entity_item["entity_data"] = self::_actualizeEntityData($entity_item["entity_data"]);

		return $entity_item["entity_data"]["id"];
	}

	/**
	 * Актуализируем структуру entity_data
	 *
	 * @return array
	 */
	protected static function _actualizeEntityData(array $entity_item):array {

		// сравниваем версию пришедшей data с текущей
		if ($entity_item["version"] != self::_SCHEME_CURRENT_VERSION) {

			// сливаем текущую версию data и ту, что пришла
			$entity_item            = array_merge(self::_SCHEME_LIST_BY_VERSION[self::_SCHEME_CURRENT_VERSION], $entity_item);
			$entity_item["version"] = self::_SCHEME_CURRENT_VERSION;
		}

		return $entity_item;
	}
}