<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Структура события «разлогинился из компании».
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_UserCompany_UserLogoutCompany extends Struct_Default {

	/** @var int ид пользователя */
	public int $user_id;

	/** @var string сессия пользователя */
	public string $session_uniq;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(int $user_id, string $session_uniq):static {

		return new static([
			"user_id"      => $user_id,
			"session_uniq" => $session_uniq,
		]);
	}
}
