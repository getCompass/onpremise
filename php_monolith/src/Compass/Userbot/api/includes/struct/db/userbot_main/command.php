<?php

namespace Compass\Userbot;

/**
 * Class Struct_Db_UserbotMain_Command
 */
class Struct_Db_UserbotMain_Command {

	public int   $task_id;
	public int   $error_count;
	public int   $need_work;
	public int   $created_at;
	public array $params;

	/**
	 * Struct_Db_UserbotMain_Command constructor.
	 *
	 */
	public function __construct(int $task_id, int $error_count, int $need_work, int $created_at, array $params) {

		$this->task_id     = $task_id;
		$this->error_count = $error_count;
		$this->need_work   = $need_work;
		$this->created_at  = $created_at;
		$this->params      = $params;
	}
}