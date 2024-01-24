<?php

namespace Compass\Thread;

/**
 * класс-структура для socket запроса conversation.getDynamicForThread
 */
class Struct_SourceParentRel_Dynamic {

	/**
	 * @param string $location_type
	 * @param array  $user_mute_info
	 * @param array  $user_clear_info
	 * @param array  $conversation_clear_info
	 */
	public function __construct(
		public string $location_type,
		public array  $user_mute_info,
		public array  $user_clear_info,
		public array  $conversation_clear_info
	) {

	}
}