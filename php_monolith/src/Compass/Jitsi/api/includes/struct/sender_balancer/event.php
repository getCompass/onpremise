<?php

namespace Compass\Jitsi;

/**
 * класс описывает структуру ws ивента
 */
class Struct_SenderBalancer_Event {

	/**
	 * Struct_Sender_Event constructor.
	 *
	 * @param string $event
	 * @param int    $version
	 * @param array  $ws_data
	 */
	public function __construct(
		public string $event,
		public int    $version,
		public array  $ws_data
	) {
	}
}