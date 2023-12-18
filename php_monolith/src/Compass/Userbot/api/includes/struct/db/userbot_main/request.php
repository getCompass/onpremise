<?php

namespace Compass\Userbot;

/**
 * Class Struct_Db_UserbotMain_Request
 */
class Struct_Db_UserbotMain_Request {

	public string $request_id;
	public string $token;
	public int    $status;
	public int    $error_count;
	public int    $need_work;
	public int    $created_at;
	public int    $updated_at;
	public array  $request_data;
	public array  $result_data;

	/**
	 * Struct_Db_UserbotMain_Request constructor.
	 *
	 */
	public function __construct(string $request_id, string $token, int $status,
					    int $error_count, int $need_work, int $created_at, int $updated_at,
					    array $request_data, array $result_data) {

		$this->request_id   = $request_id;
		$this->token        = $token;
		$this->status       = $status;
		$this->error_count  = $error_count;
		$this->need_work    = $need_work;
		$this->created_at   = $created_at;
		$this->updated_at   = $updated_at;
		$this->request_data = $request_data;
		$this->result_data  = $result_data;
	}
}