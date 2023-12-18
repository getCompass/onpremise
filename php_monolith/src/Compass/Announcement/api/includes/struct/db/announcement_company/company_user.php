<?php declare(strict_types = 1);

namespace Compass\Announcement;

/** Класс-сущность для таблицы company_user */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Db_AnnouncementCompany_CompanyUser extends Struct_Default {

	/** @var int Id компании */
	public int $company_id;
	/** @var int Id пользователя */
	public int $user_id;
	/** @var int Время истечения */
	public int $expires_at;
	/** @var int Время создания */
	public int $created_at;
	/** @var int Время редактирования */
	public int $updated_at;

	/**
	 * Struct_Db_Announcement_service_CompanyUser constructor
	 *
	 * @param int $company_id
	 * @param int $user_id
	 * @param int $expires_at
	 * @param int $created_at
	 * @param int $updated_at
	 */
	public function __construct(int $company_id, int $user_id, int $expires_at, int $created_at, int $updated_at) {

		$this->company_id = $company_id;
		$this->user_id    = $user_id;
		$this->expires_at = $expires_at;
		$this->created_at = $created_at;
		$this->updated_at = $updated_at;
	}
}