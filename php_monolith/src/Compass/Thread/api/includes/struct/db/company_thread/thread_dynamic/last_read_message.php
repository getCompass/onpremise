<?php

namespace Compass\Thread;

/**
 * Класс-структура для поля last_read_message в таблице company_thread.thread_dynamic
 */
class Struct_Db_CompanyThread_ThreadDynamic_LastReadMessage {

	/**
	 * @param string $message_map
	 * @param int    $thread_message_index
	 * @param array  $read_participants
	 */
	public function __construct(
		public string $message_map,
		public int    $thread_message_index,
		public array  $read_participants,
	) {

	}

	/**
	 * Построить объект из массива
	 *
	 * @param array $array
	 *
	 * @return Struct_Db_CompanyThread_ThreadDynamic_LastReadMessage
	 */
	public static function fromArray(array $array):self {

		return new self(
			$array["message_map"] ?? "",
			$array["thread_message_index"] ?? 0,
			$array["read_participants"] ?? [],
		);
	}
}