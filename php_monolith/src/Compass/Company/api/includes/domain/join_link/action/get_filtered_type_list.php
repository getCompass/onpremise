<?php

namespace Compass\Company;

/**
 * Класс для получения списка типов для фильтрации ссылок-приглашений
 */
class Domain_JoinLink_Action_GetFilteredTypeList {

	public const MASS_FILTER_TYPE   = "mass";
	public const SINGLE_FILTER_TYPE = "single";
	public const ALL_FILTER_TYPE    = "all";

	/**
	 * выполняем
	 */
	public static function do(string $type):array {

		return match ($type) {
			self::MASS_FILTER_TYPE   => [Domain_JoinLink_Entity_Main::TYPE_REGULAR, Domain_JoinLink_Entity_Main::TYPE_MAIN],
			self::SINGLE_FILTER_TYPE => [Domain_JoinLink_Entity_Main::TYPE_SINGLE],
			self::ALL_FILTER_TYPE    => [Domain_JoinLink_Entity_Main::TYPE_REGULAR, Domain_JoinLink_Entity_Main::TYPE_MAIN, Domain_JoinLink_Entity_Main::TYPE_SINGLE],
			default                  => throw new cs_IncorrectType(),
		};
	}
}
