<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Структура события «необходимо обновить мап сообщения для заявок»
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_DismissalRequest_MessageMapFixRequired extends Struct_Default {

	/** @var int[] список id заявок */
	public array $dismissal_request_id_list;

	/** @var int дата создания самой ранеей заявки */
	public int $date_from;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(array $dismissal_request_id_list, int $date_from):static {

		return new static([
			"dismissal_request_id_list" => $dismissal_request_id_list,
			"date_from"                 => $date_from,
		]);
	}
}
