<?php

namespace Compass\Pivot;

class Onpremiseweb_Format {

	/**
	 * Форматируем данные об аутентификации
	 */
	public static function authInfo(Struct_User_Auth_Info $auth_info, string $phone_number):array {

		return [
			"auth_map"           => (string) $auth_info->auth_map,
			"next_resend"        => (int) $auth_info->next_resend,
			"available_attempts" => (int) $auth_info->available_attempts,
			"expire_at"          => (int) $auth_info->expire_at,
			"phone_mask"         => (string) $auth_info->phone_mask,
			"type"               => (int) $auth_info->type,
			"phone_number"       => (string) $phone_number,
		];
	}

	/**
	 * Форматирует данные для вступления по приглашению
	 */
	public static function joinLinkInfo(Struct_Link_ValidationResult $link_validation_result):array {

		return [
			"join_link_uniq"                => $link_validation_result->invite_link_rel->join_link_uniq,

			"company_id"                    => $link_validation_result->company->company_id,
			"company_name"                  => $link_validation_result->company->name,

			"inviter_user_id"               => (int) $link_validation_result->inviter_user_info->user_id,
			"inviter_full_name"             => $link_validation_result->inviter_user_info->full_name,

			"entry_option"                  => $link_validation_result->entry_option,
			"role"                          => ($link_validation_result->entry_option === 2 ? "guest" : "member"),

			"is_postmoderation"             => (int) $link_validation_result->is_postmoderation,
			"is_waiting_for_postmoderation" => (int) $link_validation_result->is_waiting_for_postmoderation,

			"was_member_before"             => (int) $link_validation_result->was_member,
			"is_exit_status_in_progress"    => (int) $link_validation_result->is_exit_status_in_progress,
		];
	}
}
