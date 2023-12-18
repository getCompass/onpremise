<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Структура события «покинул компанию».
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_UserCompany_UserLeftCompany extends Struct_Default {

	/** @var int ид пользователя */
	public int $user_id;

	/** @var int дата вступления в компанию */
	public int $left_at;

	/** @var string причина ухода */
	public string $reason;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(int $user_id, int $left_at, string $reason):static {

		return new static([
			"user_id" => $user_id,
			"left_at" => $left_at,
			"reason"  => $reason,
		]);
	}
}
