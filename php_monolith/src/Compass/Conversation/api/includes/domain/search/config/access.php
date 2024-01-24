<?php

namespace Compass\Conversation;

/**
 * Класс, описывающий настройки доступа к поиску.
 */
class Domain_Search_Config_Access {

	/** @var string доступ только для указанного списка пространств */
	public const RULE_ONLY_ALLOWED = "only_allowed";

	/** @var string ограничений для поиска нет */
	public const RULE_ANY = "any";

	/**
	 * Проверяет, возможна ли работа поиска в указанном пространстве.
	 */
	public static function isSpaceAllowed(int $space_id):bool {

		$config = static::_load();

		if ($config["rule"] === static::RULE_ONLY_ALLOWED) {
			return in_array($space_id, $config["space_id_list"], true);
		}

		if ($config["rule"] === static::RULE_ANY) {
			return true;
		}

		throw new \BaseFrame\Exception\Domain\ReturnFatalException("bad search config approach");
	}

	/**
	 * Загружает конфиг доступа к поиску.
	 */
	protected static function _load():array {

		$config = getConfig("SEARCH");
		return $config["access"];
	}
}