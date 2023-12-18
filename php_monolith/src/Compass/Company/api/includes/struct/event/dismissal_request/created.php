<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Базовая структура события «создали заявку на увольенение».
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_DismissalRequest_Created extends Struct_Default {

	/** @var array сущность заявки */
	public array $dismissal_request;

	/** @var string дополнительный комментарий пользователя */
	public string $user_comment;

	/** @var string платформа пользователя при создании заявки */
	public string $user_platform;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(array $dismissal_request, string $user_comment, string $user_platform):static {

		return new static([
			"dismissal_request" => $dismissal_request,
			"user_comment"      => $user_comment,
			"user_platform"     => $user_platform,
		]);
	}
}
