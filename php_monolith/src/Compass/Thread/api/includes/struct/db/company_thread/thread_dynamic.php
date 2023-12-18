<?php

declare(strict_types = 1);

namespace Compass\Thread;

/**
 * Структура для dynamic-данных тредов
 */
class Struct_Db_CompanyThread_ThreadDynamic {

	public string $thread_map;
	public int    $is_locked;
	public int    $last_block_id;
	public int    $start_block_id;
	public int    $created_at;
	public int    $updated_at;
	public array  $user_mute_info;
	public array  $user_hide_list;

	/**
	 * Struct_Db_CompanyThread_ThreadDynamic constructor.
	 *
	 */
	public function __construct(string $thread_map, int $is_locked, int $last_block_id, int $start_block_id, int $created_at, int $updated_at, array $user_mute_info, array $user_hide_list) {

		$this->thread_map              = $thread_map;
		$this->is_locked               = $is_locked;
		$this->last_block_id           = $last_block_id;
		$this->start_block_id          = $start_block_id;
		$this->created_at              = $created_at;
		$this->updated_at              = $updated_at;
		$this->user_mute_info          = $user_mute_info;
		$this->user_hide_list          = $user_hide_list;
	}
}