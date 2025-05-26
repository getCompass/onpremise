<?php

namespace Compass\Conversation;

/**
 * класс-структура для последнего
 */
class Struct_Conversation_LastReadMessage {

	/**
	 * Конструктор
	 * @param string $message_map
	 * @param int    $conversation_message_index
	 * @param int    $read_participants_count
	 * @param array  $first_read_participant_list
	 */
	public function __construct(
		public string $message_map,
		public int    $conversation_message_index,
		public int    $read_participants_count,
		public array  $first_read_participant_list,
	) {

	}

	/**
	 * Превратить в массив
	 *
	 * @return array
	 */
	public function toArray():array {

		return [
			"message_map"                 => $this->message_map,
			"conversation_message_index"  => $this->conversation_message_index,
			"read_participants_count"     => $this->read_participants_count,
			"first_read_participant_list" => $this->first_read_participant_list,
		];
	}
}