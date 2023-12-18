<?php

namespace Compass\Pivot;

/**
 * Класс содержит всю необходимую логику для работы со ссылками приглашениями
 */
class Domain_Partner_Entity_InviteLink {

	/**
	 * Создаем запись с инвайтом
	 */
	public static function create(string $invite_code, string $invite_code_hash, int $partner_id, int $discount, int $can_reuse_after, int $expires_at):void {

		// создаем новую запись или перезаписываем старую
		$invite_code_object = new Struct_Db_PartnerData_InviteCode(
			$invite_code_hash,
			$invite_code,
			$partner_id,
			$discount,
			$can_reuse_after,
			$expires_at,
			time(),
			0
		);
		Gateway_Db_PartnerData_InviteCodeList::insertOrUpdate($invite_code_object);
	}

	/**
	 * Обновляем запись с инвайтом
	 */
	public static function edit(string $invite_code_hash, int $discount):void {

		Gateway_Db_PartnerData_InviteCodeList::set($invite_code_hash, [
			"discount"   => $discount,
			"updated_at" => time(),
		]);
	}

	/**
	 * Удаляем запись с инвайтом
	 *
	 * в таблице должны быть по большей части активные инвайты, за исключением тех, что истекли – это в основном цифровые инвайт-коды
	 * их будем перезаписывать по требованию
	 */
	public static function remove(string $invite_code_hash):void {

		Gateway_Db_PartnerData_InviteCodeList::delete($invite_code_hash);
	}

	/**
	 * Создаем запись с инвайтом
	 */
	public static function add(string $invite_code, int $partner_id):void {

		// создаем новую запись или перезаписываем старую
		$invite_code_object = new Struct_Db_PartnerInviteLink_InviteCodeMirror(
			$invite_code,
			$partner_id,
			time(),
		);
		Gateway_Db_PartnerInviteLink_InviteCodeListMirror::insert($invite_code_object);
	}

	/**
	 * Удаляем запись с инвайтом
	 */
	public static function delete(string $invite_code):void {

		Gateway_Db_PartnerInviteLink_InviteCodeListMirror::delete($invite_code);
	}

}