<?php

namespace Compass\Thread;

use JetBrains\PhpStorm\ArrayShape;

/**
 * класс для форматирование сущностей под формат API
 * в коде мы оперируем своими структурами и понятиями
 * к этому классу обращаемся строго отдачей результата в API для форматирования стандартных сущностей
 */
class Apiv2_Format {

	/**
	 * приводит к формату Напоминание
	 */
	#[ArrayShape(["remind_id" => "int", "creator_user_id" => "int", "comment" => "string", "remind_at" => "int"])]
	public static function remind(int $remind_id, int $remind_at, int $creator_user_id, string $comment):array {

		return [
			"remind_id"       => (int) $remind_id,
			"creator_user_id" => (int) $creator_user_id,
			"comment"         => (string) $comment,
			"remind_at"       => (int) $remind_at,
		];
	}
}

