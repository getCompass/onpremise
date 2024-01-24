<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Структура события «необходимо обновить мап сообщения для заявок»
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_HiringRequest_MessageMapFixRequired extends Struct_Default {

	/** @var int[] список id заявок */
	public array $hiring_request_id_list;

	/** @var int дата создания самой ранеей заявки */
	public int $date_from;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(array $hiring_request_id_list, int $date_from):static {

		return new static([
			"hiring_request_id_list" => $hiring_request_id_list,
			"date_from"              => $date_from,
		]);
	}
}
