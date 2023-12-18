<?php

namespace Compass\Pivot;

/**
 * Класс для работы с аналитикой пользователя
 */
class Type_User_Analytics {

	protected const _EVENT_KEY = "user";

	public const REGISTERED              = 1; // Создан новый аккаунт
	public const DELETED                 = 2; // Аккаунт удален
	public const JOINED_SPACE            = 3; // Аккаунт вступил в новое пространство
	public const LEFT_SPACE              = 4; // Аккаунт больше не является участником пространства
	public const GOT_ADMINISTRATOR_ROLE  = 5; // Аккаунт стал администратором пространства
	public const LOST_ADMINISTRATOR_ROLE = 6; // Аккаунт больше не администратор пространства
	public const ADD_SPACE               = 7; // Аккаунтом создано новое пространство
	public const PAYED_FOR_SPACE         = 8; // Аккаунт произвел оплату за пространство
	public const CRON_UPDATE             = 9; // Обновление по крону
	public const FIRST_PROFILE_SET       = 10; // Заполнил профиль после регистрации

	/**
	 * Пишем аналитику по пользователю
	 */
	public static function send(int $user_id, int $update_reason, int $account_status, int $created_at, int $registered_at, int $last_active_at, int $premium_active_till, string $join_link, string $country_name, array $space_id_list, array $space_id_admin_list, array $space_id_creator_list, array $space_id_payed_list):void {

		Gateway_Bus_CollectorAgent::init()->log(self::_EVENT_KEY, [
			"user_id"               => $user_id,
			"update_reason"         => $update_reason,
			"account_status"        => $account_status,
			"created_at"            => $created_at,
			"registered_at"         => $registered_at,
			"last_active_at"        => $last_active_at,
			"premium_active_till"   => $premium_active_till,
			"join_link"             => $join_link,
			"country_name"          => $country_name,
			"space_id_list"         => toJson($space_id_list),
			"space_id_admin_list"   => toJson($space_id_admin_list),
			"space_id_creator_list" => toJson($space_id_creator_list),
			"space_id_payed_list"   => toJson($space_id_payed_list),
		]);
	}
}