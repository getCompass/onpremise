<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_sms_service.send_queue
 */
class Struct_PivotSmsService_SendQueue {

	public string $sms_id;
	public int    $stage;
	public int    $need_work;
	public int    $error_count;
	public int    $created_at_ms;
	public int    $updated_at;
	public int    $expires_at;
	public string $phone_number;
	public string $text;
	public string $provider_id;
	public array  $extra;

	/**
	 * Struct_PivotSmsService_Provider constructor.
	 *
	 */
	public function __construct(
		string $sms_id,
		int    $stage,
		int    $need_work,
		int    $error_count,
		int    $created_at_ms,
		int    $updated_at,
		int    $expires_at,
		string $phone_number,
		string $text,
		string $provider_id,
		array  $extra
	) {

		$this->sms_id        = $sms_id;
		$this->stage         = $stage;
		$this->need_work     = $need_work;
		$this->error_count   = $error_count;
		$this->created_at_ms = $created_at_ms;
		$this->updated_at    = $updated_at;
		$this->expires_at    = $expires_at;
		$this->phone_number  = $phone_number;
		$this->text          = $text;
		$this->provider_id   = $provider_id;
		$this->extra         = $extra;
	}

	// функция для конвертации row из базы в структуру
	public static function convertRowToStruct(array $row):Struct_PivotSmsService_SendQueue {

		return new self(
			$row["sms_id"],
			$row["stage"],
			$row["need_work"],
			$row["error_count"],
			$row["created_at_ms"],
			$row["updated_at"],
			$row["expires_at"],
			$row["phone_number"],
			$row["text"],
			$row["provider_id"],
			fromJson($row["extra"])
		);
	}
}