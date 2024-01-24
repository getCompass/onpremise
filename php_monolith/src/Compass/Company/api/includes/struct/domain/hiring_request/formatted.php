<?php

namespace Compass\Company;

/**
 * Структура для форматированной заявки
 */
class Struct_Domain_HiringRequest_Formatted {

	public int    $hiring_request_id;
	public int    $hired_by_user_id;
	public int    $created_at;
	public int    $updated_at;
	public string $status;
	public int    $candidate_user_id;
	public string $thread_map;
	public string $message_map;
	public array  $data;

	/**
	 * Struct_Domain_HiringRequest_Formatted constructor.
	 */
	public function __construct(int    $hiring_request_id,
					    int    $hired_by_user_id,
					    int    $created_at,
					    int    $updated_at,
					    string $status,
					    int    $candidate_user_id,
					    string $thread_map,
					    string $message_map,
					    array  $data) {

		$this->hiring_request_id = $hiring_request_id;
		$this->hired_by_user_id  = $hired_by_user_id;
		$this->created_at        = $created_at;
		$this->updated_at        = $updated_at;
		$this->status            = $status;
		$this->candidate_user_id = $candidate_user_id;
		$this->thread_map        = $thread_map;
		$this->message_map       = $message_map;
		$this->data              = $data;
	}
}