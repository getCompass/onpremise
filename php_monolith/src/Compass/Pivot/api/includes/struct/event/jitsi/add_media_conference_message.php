<?php declare(strict_types = 1);

namespace Compass\Pivot;

/**
 * Структура события отправки сообщения
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Jitsi_AddMediaConferenceMessage extends Struct_Event_Default {

	public int    $space_id;
	public int    $user_id;
	public string $conversation_map;
	public string $conference_id;
	public string $accept_status;
	public string $link;

	/**
	 * Статический конструктор.
	 *
	 * @param int    $space_id
	 * @param int    $user_id
	 * @param string $conversation_map
	 * @param string $conference_id
	 * @param string $accept_status
	 * @param string $link
	 * @param string $unique_key
	 *
	 * @return static
	 * @throws \parseException
	 */
	public static function build(int $space_id, int $user_id, string $conversation_map, string $conference_id, string $accept_status, string $link, string $unique_key = ""):static {

		return new static([
			"unique_key"       => $unique_key,
			"conversation_map" => $conversation_map,
			"link"             => $link,
			"accept_status"    => $accept_status,
			"conference_id"    => $conference_id,
			"space_id"         => $space_id,
			"user_id"          => $user_id,
		]);
	}
}
