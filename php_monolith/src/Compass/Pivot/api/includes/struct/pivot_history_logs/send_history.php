<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_history_logs_{Y}.send_history
 */
class Struct_PivotHistoryLogs_SendHistory {

	public int              $row_id;
	public string           $sms_id;
	public int              $is_success;
	public int              $task_created_at_ms;
	public int              $send_to_provider_at_ms;
	public int              $sms_sent_at_ms;
	public int              $created_at;
	public string           $provider_id;
	public int              $provider_response_code;
	public int|string|array $provider_response;
	public array            $extra_alias;

	/**
	 * Struct_PivotSmsService_Provider constructor.
	 *
	 * @mixed
	 */
	public function __construct(
		int              $row_id,
		string           $sms_id,
		int              $is_success,
		int              $task_created_at_ms,
		int              $send_to_provider_at_ms,
		int              $sms_sent_at_ms,
		int              $created_at,
		string           $provider_id,
		int              $provider_response_code,
		int|string|array $provider_response,
		array            $extra_alias
	) {

		$this->row_id                 = $row_id;
		$this->sms_id                 = $sms_id;
		$this->is_success             = $is_success;
		$this->task_created_at_ms     = $task_created_at_ms;
		$this->send_to_provider_at_ms = $send_to_provider_at_ms;
		$this->sms_sent_at_ms         = $sms_sent_at_ms;
		$this->created_at             = $created_at;
		$this->provider_id            = $provider_id;
		$this->provider_response_code = $provider_response_code;
		$this->provider_response      = $provider_response;
		$this->extra_alias            = $extra_alias;
	}
}