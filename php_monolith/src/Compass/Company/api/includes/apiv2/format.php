<?php

namespace Compass\Company;

use CompassApp\Domain\Member\Entity\Member;
use CompassApp\Domain\Member\Entity\Permission;
use JetBrains\PhpStorm\ArrayShape;
use \CompassApp\Domain\Member\Struct\Main;

/**
 * Класс для форматирования сущностей под формат API (для api/v2)
 *
 * в коде мы оперируем своими структурами и понятиями
 * к этому классу обращаемся строго перед отдачей результата в API
 * для форматирования стандартных сущностей
 *
 */
class Apiv2_Format {

	/**
	 * форматирум инвайт ссылку
	 *
	 * @throws cs_IncorrectType
	 */
	public static function inviteLink(Struct_Db_CompanyData_JoinLink $invite_link, string $link, int $method_version = 1):array {

		if ($method_version == 1) {
			$invite_link = self::_prepareJoinLinkForLegacy($invite_link);
		}

		return [
			"invite_link_uniq"   => (string) $invite_link->join_link_uniq,
			"type"               => (string) Domain_JoinLink_Entity_Main::convertIntToStringType($invite_link->type),
			"is_postmoderation"  => (int) Domain_JoinLink_Entity_Main::isPostModerationEnabled($invite_link->entry_option),
			"entry_option"       => (int) $invite_link->entry_option,
			"created_by_user_id" => (int) $invite_link->creator_user_id,
			"created_at"         => (int) $invite_link->created_at,
			"expires_at"         => (int) $invite_link->expires_at,
			"updated_at"         => (int) $invite_link->updated_at,
			"can_use_count"      => (int) $invite_link->can_use_count,
			"status"             => (int) $invite_link->status,
			"link"               => (string) $link,
		];
	}

	/**
	 * форматируем инвайт ссылку
	 *
	 * @throws cs_IncorrectType
	 */
	public static function joinLink(Struct_Db_CompanyData_JoinLink $join_link, string $link, array $entry_user_id_list = [], int $method_version = 1):array {

		if ($method_version == 1) {
			$join_link = self::_prepareJoinLinkForLegacy($join_link);
		}

		return [
			"join_link_uniq"     => (string) $join_link->join_link_uniq,
			"type"               => (string) Domain_JoinLink_Entity_Main::convertIntToStringType($join_link->type),
			"is_postmoderation"  => (int) Domain_JoinLink_Entity_Main::isPostModerationEnabled($join_link->entry_option),
			"entry_option"       => (int) $join_link->entry_option,
			"created_by_user_id" => (int) $join_link->creator_user_id,
			"created_at"         => (int) $join_link->created_at,
			"expires_at"         => (int) $join_link->expires_at,
			"updated_at"         => (int) $join_link->updated_at,
			"can_use_count"      => (int) $join_link->can_use_count,
			"status"             => (int) $join_link->status,
			"link"               => (string) $link,
			"entry_user_id_list" => (array) $entry_user_id_list,
		];
	}

	/**
	 * преобразуем ссылку-приглашение для поддержки legacy
	 */
	protected static function _prepareJoinLinkForLegacy(Struct_Db_CompanyData_JoinLink $join_link):Struct_Db_CompanyData_JoinLink {

		// если ссылка безгранична по времени
		if ($join_link->expires_at == 0) {
			$join_link->expires_at = time() + DAY1 * Domain_JoinLink_Entity_Validator::METHOD_VERSION_PARAMS[METHOD_VERSION_1]["lives_day_count"];
		}

		// если ссылка безгранична по количеству использования
		if (Domain_JoinLink_Entity_Main::isLinkWithoutCanUseLimit($join_link)) {
			$join_link->can_use_count = 100;
		}

		return $join_link;
	}

	/**
	 * приводим к формату данные о боте
	 */
	public static function userbot(Struct_Db_CloudCompany_Userbot $userbot):array {

		$command_list = [];
		if ($userbot->status_alias != Domain_Userbot_Entity_Userbot::STATUS_DELETE) {
			$command_list = Domain_Userbot_Entity_Userbot::getCommandList($userbot->extra);
		}

		return [
			"userbot_id"      => (string) $userbot->userbot_id,
			"status"          => (int) $userbot->status_alias,
			"avatar_color_id" => (int) Domain_Userbot_Entity_Userbot::getAvatarColorId($userbot->extra),
			"command_list"    => (array) $command_list,
			"disabled_at"     => (int) Domain_Userbot_Entity_Userbot::getDisabledAt($userbot->extra),
			"deleted_at"      => (int) Domain_Userbot_Entity_Userbot::getDeletedAt($userbot->extra),
		];
	}

	/**
	 * приводим к формату секуьюрные данные бота
	 */
	public static function userbotSensitiveData(Struct_Domain_Userbot_SensitiveData $sensitive_data):array {

		return [
			"token"            => (string) $sensitive_data->token,
			"secret_key"       => (string) $sensitive_data->secret_key,
			"is_react_command" => (int) $sensitive_data->is_react_command,
			"webhook"          => (string) $sensitive_data->webhook,
			"group_info_list"  => (array) $sensitive_data->group_info_list,
			"avatar_color_id"  => (int) $sensitive_data->avatar_color_id,
		];
	}

	/**
	 * получаем отформатированную связь userbot_id бота c user_id пользователя, за которым тот закреплён
	 */
	public static function userbotUserRel(Struct_Db_CloudCompany_Userbot $userbot):array {

		return [
			"userbot_id" => (string) $userbot->userbot_id,
			"user_id"    => (int) $userbot->user_id,
		];
	}

	/**
	 * приводим к формату список данных участников для Премиума в компании
	 */
	public static function premiumMemberStatusList(array $member_list, array $premium_active, array $payment_request_list, array $deleted_user_id_list):array {

		$formatted_premium_member_status = [];

		foreach ($member_list as $member) {

			$user_id = $member->user_id;

			$formatted_premium_member_status[] = self::premiumMemberStatus(
				$member,
				$premium_active[$user_id] ?? 0,
				$payment_request_list[$user_id] ?? null,
				$deleted_user_id_list
			);
		}

		return $formatted_premium_member_status;
	}

	/**
	 * приводим к формату данные участника для Премиума в компании
	 */
	#[ArrayShape(["user_id" => "int", "is_payment_requested" => "int", "role" => "string", "active_till" => "int"])]
	public static function premiumMemberStatus(
		Main $member, int $premium_active, Struct_Db_CompanyData_PremiumPaymentRequest|null $payment_request, array $deleted_user_id_list):array {

		$is_payment_request = (!isset($payment_request) || $payment_request->is_payed === 1) ? 0 : 1;

		// если пользователь среди удалённых
		$role = Domain_Premium_Entity_Premium::getRoleTitle($member);
		if (in_array($member->user_id, $deleted_user_id_list)) {

			$role               = Domain_Premium_Entity_Premium::ACCOUNT_DELETED_ROLE_TITLE;
			$premium_active     = 0;
			$is_payment_request = 0;
		}

		return [
			"user_id"              => $member->user_id,
			"is_payment_requested" => $is_payment_request,
			"role"                 => $role,
			"active_till"          => $premium_active,
		];
	}

	/**
	 * Подготавливаем ответ для запроса данных активной компании
	 *
	 * @param Struct_Domain_Company_ActivityData $activity_data
	 *
	 * @return array
	 */
	public static function activityData(Struct_Domain_Company_ActivityData $activity_data):array {

		return [
			"owner"  => $activity_data->owner_activity_data ? self::ownerActivityData($activity_data->owner_activity_data) : null,
			"common" => $activity_data->common_activity_data ? self::commonActivityData($activity_data->common_activity_data) : null,
		];
	}

	/**
	 * Подготавливаем ответ для запроса данных активной компании для всех пользователей
	 *
	 * @param Struct_Domain_Company_CommonActivityData $common_activity_data
	 *
	 * @return array
	 */
	public static function commonActivityData(Struct_Domain_Company_CommonActivityData $common_activity_data):array {

		return [
			"premium_payment_request_active_till" => (int) $common_activity_data->premium_payment_request_active_till,
			"general_group_conversation_key"      => (string) $common_activity_data->general_group_conversation_key,
		];
	}

	/**
	 * Подготавливаем ответ для запроса данных активной компании для владельца
	 *
	 * @param Struct_Domain_Company_OwnerActivityData $owner_activity_data
	 *
	 * @return array
	 */
	public static function ownerActivityData(Struct_Domain_Company_OwnerActivityData $owner_activity_data):array {

		return [
			"premium_payment_request_count" => (int) $owner_activity_data->premium_payment_request_count,
		];
	}

	/**
	 * Форматируем тарифный план числа участников.
	 * Здесь есть немного логики, для привода наших значений к клиентским.
	 *
	 * @param \Tariff\Plan\MemberCount\MemberCount $member_count_plan
	 * @param int                                  $member_count
	 *
	 * @return array
	 */
	public static function memberCountPlan(\Tariff\Plan\MemberCount\MemberCount $member_count_plan, int $member_count):array {

		// эту логику нужно держать в синхронизации с витриной пивота
		// ----------------------------------------------------------
		if (($member_count_plan->isActive(time()) && !$member_count_plan->isFree(time())) || ($member_count_plan->isTrial(time()) && $member_count_plan->getLimit() > 10)) {
			$allowed_action_list[] = "prolong";
		} elseif ((!$member_count_plan->isActive(time()) || $member_count_plan->isFree(time())) && !$member_count_plan->isTrial(time())) {
			$allowed_action_list[] = "activate";
		}

		if ($member_count_plan->isTrial(time()) || !$member_count_plan->isFree(time())) {
			$allowed_action_list[] = "change";
		}
		// ----------------------------------------------------------

		$active_till   = $member_count_plan->getActiveTill();
		$extend_policy = $member_count_plan->getExtendPolicyRule();

		// если пробный истек, то клиентам говорим, то политика NEVER
		if ($extend_policy === \Tariff\Plan\MemberCount\OptionExtendPolicy::TRIAL && $active_till < time()) {
			$extend_policy = \Tariff\Plan\MemberCount\OptionExtendPolicy::NEVER;
		}

		// если бесплатный, но не триальный, то отдаем бесконечную длительность
		if ($member_count_plan->isFree(time()) && !$member_count_plan->isTrial(time())) {
			$active_till = 0;
		}

		return [
			"active_till"         => $active_till,
			"limit"               => $member_count_plan->getLimit(),
			"current"             => $member_count,
			"extend_policy"       => $extend_policy,
			"allowed_action_list" => $allowed_action_list,
		];
	}

	/**
	 * Подготавливаем ответ для запроса тарифа
	 *
	 * @param array $member_count_output
	 *
	 * @return array
	 */
	public static function tariff(array $member_count_output):array {

		return [
			"plan_info" => [
				"member_count"      => $member_count_output,
				"file_storage_size" => [],

			],
		];
	}

	/**
	 * Форматирует заявку на вступление
	 */
	#[ArrayShape(["join_request_id" => "int", "joined_by_user_id" => "int", "created_at" => "int", "updated_at" => "int", "status" => "string", "candidate_user_id" => "int", "thread_key" => "string", "message_key" => "string", "data" => "object"])]
	public static function joinRequest(Struct_Db_CompanyData_HiringRequest $join_request, array $data = []):array {

		$thread_map  = Domain_HiringRequest_Entity_Request::getThreadMap($join_request->extra);
		$message_map = Domain_HiringRequest_Entity_Request::getMessageMap($join_request->extra);

		return [
			"join_request_id"   => (int) $join_request->hiring_request_id,
			"joined_by_user_id" => (int) $join_request->hired_by_user_id,
			"created_at"        => (int) $join_request->created_at,
			"updated_at"        => (int) $join_request->updated_at,
			"status"            => (string) Domain_HiringRequest_Entity_Request::HIRING_REQUEST_TYPE_SCHEMA[$join_request->status],
			"candidate_user_id" => (int) $join_request->candidate_user_id,
			"thread_key"        => (string) !isEmptyString($thread_map) ? Type_Pack_Thread::doEncrypt($thread_map) : "",
			"message_key"       => (string) !isEmptyString($message_map) ? Type_Pack_Message::doEncrypt($message_map) : "",
			"data"              => (object) self::_formatJoinRequestData($data),
		];
	}

	/**
	 * Форматируем дату для заявки
	 */
	#[ArrayShape(["invited_comment" => "string", "candidate_user_info" => "object"])]
	protected static function _formatJoinRequestData(array $data):array {

		$formatted_data = [
			"invited_comment" => (string) $data["invited_comment"],
		];

		if (isset($data["candidate_user_info"])) {

			$formatted_data["candidate_user_info"] = (object) [
				"full_name"       => (string) $data["candidate_user_info"]["full_name"],
				"avatar_file_key" => (string) $data["candidate_user_info"]["avatar_file_key"],
				"avatar_color"    => (string) \BaseFrame\Domain\User\Avatar::getColorOutput($data["candidate_user_info"]["avatar_color_id"]),
			];
		}
		return $formatted_data;
	}

	/**
	 * Приводим к формату данные о диалоге с пользователем
	 */
	public static function singleConversation(array $single_conversation):array {

		return [
			"conversation_key" => (string) $single_conversation["conversation_key"],
			"is_muted"         => (int) $single_conversation["is_muted"],
		];
	}

	/**
	 * Приводим к формату данные о диалоге с пользователем
	 */
	public static function permissions(int $user_id, int $role, int $permissions, int $permissions_output_version = Permission::CURRENT_PERMISSIONS_OUTPUT_SCHEMA_VERSION):array {

		return [
			"user_id"     => (int) $user_id,
			"role"        => (string) Member::getRoleOutputType($role),
			"permissions" => (object) Permission::formatToOutput($role, $permissions, $permissions_output_version),
		];
	}
}
