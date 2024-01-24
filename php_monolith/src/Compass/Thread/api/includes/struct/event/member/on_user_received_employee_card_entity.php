<?php

declare(strict_types = 1);

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Базовая структура события «пользователь получил сущность карточки (спасибо/требовательность/достижение)».
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Member_OnUserReceivedEmployeeCardEntity extends Struct_Default {

	public string $entity_type;
	public string $message_map;
	public int    $sender_user_id;
	public int    $received_user_id;
	public int    $week_count;
	public int    $month_count;

	/**
	 * Статический конструктор.
	 *
	 * @throws ParseFatalException
	 */
	public static function build(string $entity_type, string $message_map, int $sender_user_id, int $received_user_id, int $week_count, int $month_count):static {

		return new static([
			"entity_type"      => $entity_type,
			"message_map"      => $message_map,
			"sender_user_id"   => $sender_user_id,
			"received_user_id" => $received_user_id,
			"week_count"       => $week_count,
			"month_count"      => $month_count,
		]);
	}
}
