<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Базовая структура события «Запрошено файловое сообщение от системного бота».
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Testing_SystemBotFileMessageRequested extends Struct_Default {

	/** @var int кому выслать сообщение */
	public int $user_id;

	/** @var string файл-мап для пересылки */
	public string $file_map;

	/** @var string имя файла */
	public string $file_name;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(int $user_id, string $file_map, string $file_name):static {

		return new static([
			"user_id"   => $user_id,
			"file_map"  => $file_map,
			"file_name" => $file_name,
		]);
	}
}
