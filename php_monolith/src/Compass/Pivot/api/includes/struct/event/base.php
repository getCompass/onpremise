<?php declare(strict_types = 1);

namespace Compass\Pivot;

/**
 * Class Struct_Event_Base
 * Базовая структура события.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Base extends Struct_Event_Default {

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
	public int    $data_version;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(string $uuid, string $event_type, int $created_at, int $version, Struct_Default $event_data):static {

		// рекурсивно проходим по данным события и заменяем null на пустую строку
		array_walk_recursive($event_data, function(mixed &$value):void {

			$value = $value === null ? "" : $value;
		});

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
