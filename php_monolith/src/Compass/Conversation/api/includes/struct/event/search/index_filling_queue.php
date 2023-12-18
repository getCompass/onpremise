<?php declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Структура события «необходимо сохранять сущности в поисковый индекс».
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Search_IndexFillingQueue extends Struct_Default {

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build():static {

		return new static([]);
	}
}
