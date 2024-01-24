<?php

declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Структура события включения расширенной карточки
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Company_ExtendedEmployeeCardEnabled extends Struct_Default {

	public int   $creator_user_id;
	public array $user_id_list;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(int $creator_user_id, array $user_id_list):static {

		return new static([
			"creator_user_id" => $creator_user_id,
			"user_id_list"    => $user_id_list,
		]);
	}
}
