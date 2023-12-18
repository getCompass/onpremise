<?php declare(strict_types = 1);

namespace Compass\Announcement;

/** Класс-сущность для таблицы анонсов */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Db_AnnouncementMain_Announcement extends Struct_Default {

	/** @var int Id анонса */
	public int $announcement_id;
	/** @var bool Является ли анонс глобальным */
	public bool $is_global;
	/** @var int Тип анонса */
	public int $type;
	/** @var int Статус анонса */
	public int $status;
	/** @var int Id компании */
	public int $company_id;
	/** @var int Приоритет анонса */
	public int $priority;
	/** @var int Время создания анонса */
	public int $created_at;
	/** @var int Время редактирования анонса */
	public int $updated_at;
	/** @var int Время истечения анонса */
	public int $expires_at;
	/** @var int Время повторной отправки анонса */
	public int $resend_repeat_time;
	/** @var array Пользователи, которые получат анонс */
	public array $receiver_user_id_list;
	/** @var array Пользователи которые не должны получить анонс */
	public array $excluded_user_id_list;
	/** @var array Доп. данные */
	public array $extra;

	/**
	 * Struct_Db_Announcement_Announcement constructor
	 *
	 * @param int   $announcement_id
	 * @param bool  $is_global
	 * @param int   $type
	 * @param int   $status
	 * @param int   $company_id
	 * @param int   $priority
	 * @param int   $created_at
	 * @param int   $updated_at
	 * @param int   $expires_at
	 * @param int   $resend_repeat_time
	 * @param array $receiver_user_id_list
	 * @param array $excluded_user_id_list
	 * @param array $extra
	 */
	public function __construct(int $announcement_id, bool $is_global, int $type, int $status, int $company_id, int $priority, int $created_at, int $updated_at, int $expires_at, int $resend_repeat_time, array $receiver_user_id_list, array $excluded_user_id_list, array $extra) {

		$this->announcement_id       = $announcement_id;
		$this->is_global             = $is_global;
		$this->type                  = $type;
		$this->status                = $status;
		$this->company_id            = $company_id;
		$this->priority              = $priority;
		$this->created_at            = $created_at;
		$this->updated_at            = $updated_at;
		$this->expires_at            = $expires_at;
		$this->resend_repeat_time    = $resend_repeat_time;
		$this->receiver_user_id_list = $receiver_user_id_list;
		$this->excluded_user_id_list = $excluded_user_id_list;
		$this->extra                 = $extra;
	}
}