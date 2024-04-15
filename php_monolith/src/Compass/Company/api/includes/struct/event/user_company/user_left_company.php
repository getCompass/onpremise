<?php

declare(strict_types = 1);

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Структура события «покинул компанию».
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_UserCompany_UserLeftCompany extends Struct_Default {

	/** @var int ид пользователя */
	public int $user_id;

	/**
	 * Статический конструктор.
	 *
	 * @param int $user_id
	 *
	 * @return Struct_Event_UserCompany_UserLeftCompany
	 * @throws ParseFatalException
	 */
	public static function build(int $user_id):static {

		return new static([
			"user_id" => $user_id,
		]);
	}
}
