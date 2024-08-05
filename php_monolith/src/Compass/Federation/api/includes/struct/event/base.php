<?php

declare(strict_types = 1);

namespace Compass\Federation;

/**
 * Class Struct_Event_Base
 * Базовая структура события.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Base extends Struct_Default {

	/** @var string тип события */
	public string $event_type;

	/** @var Struct_Default данные события */
	public Struct_Default $event_data;

	/** @var string идентификатор события */
	public string $uuid;

	/** @var int время создания события */
	public int $created_at;

	/** @var int версия события */
	public int $version;

	public string $source_type;

	public string $source_identifier;

	public int $data_version;

	/**
	 * Статический конструктор.
	 *
	 * @param string         $uuid
	 * @param string         $event_type
	 * @param int            $created_at
	 * @param int            $version
	 * @param Struct_Default $event_data
	 *
	 * @return Struct_Event_Base
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function build(string $uuid, string $event_type, int $created_at, int $version, Struct_Default $event_data):static {

		return new static([
			"uuid"              => $uuid,
			"event_type"        => $event_type,
			"source_type"       => "service",
			"source_identifier" => "php_" . CURRENT_MODULE,
			"created_at"        => $created_at,
			"version"           => $version,
			"event_data"        => $event_data,
			"data_version"      => 1,
		]);
	}
}
