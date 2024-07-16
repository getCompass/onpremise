<?php declare(strict_types = 1);

namespace Compass\Jitsi;

/**
 * Структура события разблокировки пространства
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Jitsi_NeedCheckSingleConference extends Struct_Event_Default {

	/** @var string $conference_id id конференции */
	public string $conference_id;

	/**
	 * Статический конструктор.
	 *
	 * @param string $conference_id
	 * @param string $unique_key
	 *
	 * @return static
	 * @throws \parseException
	 */
	public static function build(string $conference_id, string $unique_key = ""):static {

		return new static([
			"unique_key"    => $unique_key,
			"conference_id" => $conference_id,
		]);
	}
}
