<?php

namespace Compass\Pivot;

/**
 * Класс для взаимодействия с записью принятия ссылок-инвайтов
 */
class Domain_Company_Entity_JoinLink_UserRel {

	public const JOIN_LINK_REL_POSTMODERATION = 1; // заявку создали
	public const JOIN_LINK_REL_ACCEPTED       = 2; // заявку приняли
	public const JOIN_LINK_REL_REVOKE         = 3; // заявку отозвал сам кандидат
	public const JOIN_LINK_REL_REJECTED       = 4; // заявку отклонили в компании

	public const USED_LINK_STATUS_LIST = [
		self::JOIN_LINK_REL_ACCEPTED,
		self::JOIN_LINK_REL_REJECTED,
	];

	/**
	 * Добавляем в базу
	 *
	 * @throws \queryException
	 * @throws cs_RowDuplication
	 */
	public static function add(string $join_link_uniq, int $user_id, int $company_id, int $entry_id, int $is_postmoderation):void {

		$status = $is_postmoderation == 1 ? self::JOIN_LINK_REL_POSTMODERATION : self::JOIN_LINK_REL_ACCEPTED;
		Gateway_Db_PivotData_CompanyJoinLinkUserRel::insert($join_link_uniq, $user_id, $company_id, $entry_id, $status);
	}

	/**
	 * Получаем одну запись
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getByInviteLink(string $join_link_uniq, int $user_id, int $company_id):Struct_Db_PivotData_CompanyJoinLinkUserRel {

		return Gateway_Db_PivotData_CompanyJoinLinkUserRel::getByInviteLink($join_link_uniq, $user_id, $company_id);
	}

	/**
	 * Обновляем поле статус
	 */
	public static function setStatus(string $entry_id, int $user_id, int $company_id, int $status):void {

		$set = [
			"status"     => $status,
			"updated_at" => time(),
		];
		Gateway_Db_PivotData_CompanyJoinLinkUserRel::set($entry_id, $user_id, $company_id, $set);
	}

	/**
	 * Обновляем поле статус
	 */
	public static function insertOrUpdate(string $join_link_uniq, int $user_id, int $company_id, int $entry_id, int $is_postmoderation, bool $is_need_insert):void {

		if ($is_need_insert) {

			self::add($join_link_uniq, $user_id, $company_id, $entry_id, $is_postmoderation);
			return;
		}

		$set = [
			"entry_id"   => $entry_id,
			"status"     => $is_postmoderation == 1 ? self::JOIN_LINK_REL_POSTMODERATION : self::JOIN_LINK_REL_ACCEPTED,
			"updated_at" => time(),
		];
		Gateway_Db_PivotData_CompanyJoinLinkUserRel::setByJoinLink($join_link_uniq, $user_id, $company_id, $set);
	}

	/**
	 * Роняем ошибку если пользователь ранее принимал инвайт
	 *
	 * @throws cs_JoinLinkIsNotActive
	 */
	public static function throwIfInviteAlreadyUsed(Struct_Db_PivotData_CompanyJoinLinkUserRel $invite_link_row):void {

		if (in_array($invite_link_row->status, self::USED_LINK_STATUS_LIST)) {
			throw new cs_JoinLinkIsNotActive();
		}
	}

	/**
	 * Проверяем, был ли инвайт ранее уже использован (отозван пользователем)
	 */
	public static function isInviteRevoked(Struct_Db_PivotData_CompanyJoinLinkUserRel $invite_link_row):bool {

		if ($invite_link_row->status === self::JOIN_LINK_REL_REVOKE) {
			return true;
		}
		return false;
	}
}