<?php

namespace Compass\Company;

/**
 * Класс для работы с аналитикой статуса пространства
 */
class Type_Space_Analytics {

	protected const _EVENT_KEY = "space";

	public const CREATED                 = 1; // создано новое пространство
	public const PAYED_FOR_SPACE         = 2; // произведена оплата за пространство
	public const END_SPACE_TARIFF        = 3; // у пространства закончилась подписка
	public const MEMBER_COUNT_CHANGED    = 4; // изменилось число участников пространства
	public const DELETED                 = 5; // пространство удалено
	public const NEW_ADMINISTRATOR       = 6; // назначен новый администратор пространства
	public const DISMISS_ADMINISTRATOR   = 7; // администратор разжалован до пользователя
	public const LAST_UPDATED_AT_EXPIRED = 8; // с последнего обновления прошло 24ч (альтернативный вариант - раз в 24ч по крону)

	public const ANALYTICS_ACTIVE_SPACE_STATUS  = 1;
	public const ANALYTICS_DELETED_SPACE_STATUS = 0;

	/**
	 * Пишем аналитику по действиям пользователя
	 */
	public static function send(int   $space_id, int $action, int $space_status, int $tariff_status,
					    int   $max_member_count, int $member_count, int $space_created_at, int $space_deleted_at, int $last_active_at,
					    array $user_id_members, int $user_id_creator, array $user_id_admin_list, array $user_id_payer_list):void {

		Gateway_Bus_CollectorAgent::init()->log(self::_EVENT_KEY, [
			"space_id"           => $space_id,
			"action"             => $action,
			"tariff_status"      => $tariff_status,
			"space_status"       => $space_status,
			"max_member_count"   => $max_member_count,
			"member_count"       => $member_count,
			"user_id_creator"    => $user_id_creator,
			"space_created_at"   => $space_created_at,
			"space_deleted_at"   => $space_deleted_at,
			"created_at"         => time(),
			"last_active_at"     => $last_active_at,
			"user_id_members"    => $user_id_members,
			"user_id_admin_list" => $user_id_admin_list,
			"user_id_payer_list" => $user_id_payer_list,
		]);
	}
}