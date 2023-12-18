<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Базовая структура события «изменения имени».
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Member_NameChanged extends Struct_Default {

	public int    $user_id;
	public string $full_name;

	/**
	 * Статический конструктор.
	 *
	 * @param int    $user_id
	 * @param string $full_name
	 *
	 * @return static
	 * @throws \parseException
	 */
	public static function build(int $user_id, string $full_name):static {

		return new static([
			"user_id"   => $user_id,
			"full_name" => $full_name,
		]);
	}
}
