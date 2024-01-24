<?php declare(strict_types = 1);

namespace Compass\Pivot;

/**
 * Структура события разблокировки пространства
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_SpaceTariff_SpaceUnblock extends Struct_Event_Default {

	/** @var int $space_id id пространства */
	public int $space_id;
	public int $check_until;

	/**
	 * Статический конструктор.
	 *
	 * @param int    $space_id
	 * @param int    $check_until
	 * @param string $unique_key
	 *
	 * @return static
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function build(int $space_id, int $check_until, string $unique_key = ""):static {

		return new static([
			"unique_key"  => $unique_key,
			"space_id"    => $space_id,
			"check_until" => $check_until,
		]);
	}
}
