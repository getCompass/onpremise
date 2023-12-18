<?php

namespace Compass\Pivot;

/**
 * класс для работы с сущностью бота
 */
class Domain_User_Entity_Bot {

	/** @var string ключ для конфига */
	public const PIVOT_BOT_LIST = "PIVOT_BOT_LIST";

	/**
	 * получаем ключ для конфига по npc-типу
	 *
	 * @throws \parseException
	 */
	public static function getConfigKeyByNpcType(int $npc_type):string {

		return Type_User_Main::getUserType($npc_type);
	}
}