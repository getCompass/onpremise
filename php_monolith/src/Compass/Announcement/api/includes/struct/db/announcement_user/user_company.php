<?php declare(strict_types = 1);

namespace Compass\Announcement;

/** Класс-сущность для таблицы user_company */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Db_AnnouncementUser_UserCompany extends Struct_Default {

	/** @var int Id пользователя */
	public int $user_id;
	/** @var int Id компании */
	public int $company_id;
	/** @var int Время истечения */
	public int $expires_at;
	/** @var int Время создания */
	public int $created_at;
	/** @var int Время редактирования */
	public int $updated_at;

	/**
	 * Struct_Db_AnnouncementService_UserCompany constructor
	 *
	 * @param int $user_id
	 * @param int $company_id
	 * @param int $expires_at
	 * @param int $created_at
	 * @param int $updated_at
	 */
	public function __construct(int $user_id, int $company_id, int $expires_at, int $created_at, int $updated_at) {

		$this->user_id    = $user_id;
		$this->company_id = $company_id;
		$this->expires_at = $expires_at;
		$this->created_at = $created_at;
		$this->updated_at = $updated_at;
	}
}