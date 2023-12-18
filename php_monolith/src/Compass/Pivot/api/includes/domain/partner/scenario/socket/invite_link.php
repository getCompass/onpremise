<?php

namespace Compass\Pivot;

/**
 * Класс для работы со ссылками-приглашениями для собственников
 */
class Domain_Partner_Scenario_Socket_InviteLink {

	/**
	 * Создаем ссылку-приглашение
	 */
	public static function create(string $invite_code, string $invite_code_hash, int $partner_id, int $discount, int $can_reuse_after, int $expires_at):void {

		Domain_Partner_Entity_InviteLink::create($invite_code, $invite_code_hash, $partner_id, $discount, $can_reuse_after, $expires_at);
	}

	/**
	 * Редактируем ссылку-приглашение
	 */
	public static function edit(string $invite_code_hash, int $discount):void {

		Domain_Partner_Entity_InviteLink::edit($invite_code_hash, $discount);
	}

	/**
	 * Удаляем ссылку-приглашение
	 */
	public static function remove(string $invite_code_hash):void {

		Domain_Partner_Entity_InviteLink::remove($invite_code_hash);
	}

	/**
	 * Создаем ссылку-приглашение
	 */
	public static function add(string $invite_code, int $partner_id):void {

		Domain_Partner_Entity_InviteLink::add($invite_code, $partner_id);
	}

	/**
	 * Удаляем ссылку-приглашение
	 */
	public static function delete(string $invite_code_hash):void {

		Domain_Partner_Entity_InviteLink::delete($invite_code_hash);
	}
}