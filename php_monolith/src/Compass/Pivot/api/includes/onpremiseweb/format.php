<?php

namespace Compass\Pivot;

/**
 * класс форматирования сущностей в ответе для onpremise web
 * @package Compass\Pivot
 */
class Onpremiseweb_Format {

	/**
	 * Форматируем данные об аутентификации
	 */
	public static function authInfo(Struct_User_Auth_Info $auth_info):array {

		return [
			"auth_map" => (string) $auth_info->auth_map,
			"type"     => (int) $auth_info->auth->type,
			"data"     => (object) self::_formatAuthInfoData($auth_info),
		];
	}

	/**
	 * форматируем поле data для сущности auth_info
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\InvalidPhoneNumber
	 */
	protected static function _formatAuthInfoData(Struct_User_Auth_Info $auth_info):array {

		return match ($auth_info->auth->type) {
			Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_REGISTER_BY_PHONE_NUMBER,
			Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_LOGIN_BY_PHONE_NUMBER  => [
				"next_resend"        => (int) $auth_info->getAuthPhoneEntity()->getNextResendAt(),
				"available_attempts" => (int) $auth_info->getAuthPhoneEntity()->getAvailableAttempts(Domain_User_Entity_AuthStory_MethodHandler_PhoneNumber::ON_PREMISE_ERROR_COUNT_LIMIT),
				"expire_at"          => (int) $auth_info->auth->expires_at,
				"phone_mask"         => (string) (new \BaseFrame\System\PhoneNumber($auth_info->getAuthPhoneEntity()->getAuthParameter()))->obfuscate(),
				"phone_number"       => (string) $auth_info->getAuthPhoneEntity()->getPhoneNumber(),
			],
			Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_REGISTER_BY_MAIL,
			Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_LOGIN_BY_MAIL,
			Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_RESET_PASSWORD_BY_MAIL => [
				"next_resend"                 => (int) $auth_info->getAuthMailEntity()->getNextResendAt(),
				"password_available_attempts" => (int) $auth_info->getAuthMailEntity()->getAvailablePasswordEnteringAttempts(),
				"code_available_attempts"     => (int) $auth_info->getAuthMailEntity()->getAvailableCodeEnteringAttempts(),
				"expire_at"                   => (int) $auth_info->auth->expires_at,
				"mail"                        => (string) $auth_info->getAuthMailEntity()->getMail(),
				"stage"                       => (string) $auth_info->getAuthMailEntity()->resolveStage($auth_info->auth->type),
				"scenario"                    => (string) Domain_User_Scenario_OnPremiseWeb_Auth_Mail::resolveScenario($auth_info->auth->type),
			]
		};
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

	/**
	 * Форматируем сущность пользователя
	 *
	 * @return array
	 */
	public static function userInfo(Struct_Db_PivotUser_User $user_info):array {

		return [
			"user_id"   => (int) $user_info->user_id,
			"full_name" => (string) $user_info->full_name,
		];
	}
}
