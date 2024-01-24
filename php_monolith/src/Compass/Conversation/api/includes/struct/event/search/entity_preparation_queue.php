<?php declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Структура события «необходимо начать подготовку сущностей перед индексаций».
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Search_EntityPreparationQueue extends Struct_Default {

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build():static {

		return new static([]);
	}
}
