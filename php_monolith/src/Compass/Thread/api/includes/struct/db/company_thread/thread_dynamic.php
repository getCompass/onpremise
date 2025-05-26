<?php

declare(strict_types = 1);

namespace Compass\Thread;

/**
 * Структура для dynamic-данных тредов
 */
class Struct_Db_CompanyThread_ThreadDynamic {

	/**
	 * Конструктор
	 *
	 * @param string                                                     $thread_map
	 * @param int                                                        $is_locked
	 * @param int                                                        $last_block_id
	 * @param int                                                        $start_block_id
	 * @param int                                                        $created_at
	 * @param int                                                        $updated_at
	 * @param Struct_Db_CompanyThread_ThreadDynamic_LastReadMessage|null $last_read_message
	 * @param array                                                      $user_mute_info
	 * @param array                                                      $user_hide_list
	 */
	public function __construct(
		public string                                                     $thread_map,
		public int                                                        $is_locked,
		public int                                                        $last_block_id,
		public int                                                        $start_block_id,
		public int                                                        $created_at,
		public int                                                        $updated_at,
		public Struct_Db_CompanyThread_ThreadDynamic_LastReadMessage|null $last_read_message,
		public array                                                      $user_mute_info,
		public array                                                      $user_hide_list,
	) {
	}

	/**
	 * Построить объект из массива
	 *
	 * @param array $array
	 *
	 * @return self
	 */
	public static function fromArray(array $array):self {

		return new self(
			$array["thread_map"],
			$array["is_locked"],
			$array["last_block_id"],
			$array["start_block_id"],
			$array["created_at"],
			$array["updated_at"],
			Struct_Db_CompanyThread_ThreadDynamic_LastReadMessage::fromArray($array["last_read_message"] ?? []),
			$array["user_mute_info"],
			$array["user_hide_list"],
		);
	}
}