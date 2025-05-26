<?php

namespace Compass\Thread;

/**
 * класс-структура для последнего прочитанногосообщения в треде
 */
class Struct_Thread_LastReadMessage {

	/**
	 * Конструктор
	 *
	 * @param string $message_map
	 * @param int    $thread_message_index
	 * @param int    $read_participants_count
	 * @param array  $first_read_participant_list
	 */
	public function __construct(
		public string $message_map,
		public int    $thread_message_index,
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
			"thread_message_index"        => $this->thread_message_index,
			"read_participants_count"     => $this->read_participants_count,
			"first_read_participant_list" => $this->first_read_participant_list,
		];
	}
}