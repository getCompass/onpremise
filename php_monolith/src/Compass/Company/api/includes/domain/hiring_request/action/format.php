<?php

namespace Compass\Company;

use BaseFrame\Domain\User\Avatar;

/**
 * Класс для форматирования заявки
 */
class Domain_HiringRequest_Action_Format {

	/**
	 * Выполняем action
	 */
	public static function do(Struct_Db_CompanyData_HiringRequest $hiring_request, Struct_User_Info|false $user_info, array $not_allowed_conversation_key_list = []):array {

		$data = [
			"autojoin"        => [
				"group_conversation_autojoin_item_list"  => self::_getGroupAutoJoin($hiring_request, $not_allowed_conversation_key_list),
				"single_conversation_autojoin_item_list" => self::_getSingleAutoJoin($hiring_request),
			],
			"invited_comment" => Domain_HiringRequest_Entity_Request::getComment($hiring_request->extra),
		];
		if ($user_info !== false) {

			$data["candidate_user_info"] = [
				"full_name"       => $user_info->full_name,
				"avatar_file_key" => $user_info->avatar_file_key,
				"avatar_color_id" => $user_info->avatar_color_id,
			];
		}
		$action_user_id_list = self::_getActionUserIdList($hiring_request);

		return [
			static::getHiringRequestFormatted($hiring_request, $data), $action_user_id_list,
		];
	}

	/**
	 * Создаем новую форматировнную заявку
	 */
	protected static function getHiringRequestFormatted(
		Struct_Db_CompanyData_HiringRequest $hiring_request,
		array                               $data
	):Struct_Domain_HiringRequest_Formatted {

		return new Struct_Domain_HiringRequest_Formatted(
			$hiring_request->hiring_request_id,
			$hiring_request->hired_by_user_id,
			$hiring_request->created_at,
			$hiring_request->updated_at,
			$hiring_request->status,
			$hiring_request->candidate_user_id,
			Domain_HiringRequest_Entity_Request::getThreadMap($hiring_request->extra),
			Domain_HiringRequest_Entity_Request::getMessageMap($hiring_request->extra),
			$data,
		);
	}

	/**
	 * получаем group диалоги для авто подключения
	 */
	protected static function _getGroupAutoJoin(Struct_Db_CompanyData_HiringRequest $hiring_request, array $not_allowed_conversation_key_list):array {

		$conversation_key_list_to_join = Domain_HiringRequest_Entity_Request::getConversationKeyListToJoin($hiring_request->extra);

		$group_conversation_autojoin_item_list = [];
		foreach ($conversation_key_list_to_join as $v) {

			// если диалог находится среди тех, кто не подходит для вступления
			if (in_array($v["conversation_key"], $not_allowed_conversation_key_list)) {
				continue;
			}

			$order                                   = $v["order"] ?? 0;
			$group_conversation_autojoin_item_list[] = [
				"conversation_key" => $v["conversation_key"],
				"status"           => $v["status"],
				"order"            => $order,
			];
		}
		return $group_conversation_autojoin_item_list;
	}

	/**
	 * получаем single диалоги для авто подключения
	 */
	protected static function _getSingleAutoJoin(Struct_Db_CompanyData_HiringRequest $hiring_request):array {

		$single_list_to_create = Domain_HiringRequest_Entity_Request::getSingleListToCreate($hiring_request->extra);

		$single_conversation_autojoin_item_list = [];

		foreach ($single_list_to_create as $v) {

			$order                                    = $v["order"] ?? 0;
			$single_conversation_autojoin_item_list[] = [
				"user_id" => $v["user_id"],
				"status"  => $v["status"],
				"order"   => $order,
			];
		}
		return $single_conversation_autojoin_item_list;
	}

	/**
	 * получаем action users
	 */
	protected static function _getActionUserIdList(Struct_Db_CompanyData_HiringRequest $hiring_request):array {

		$single_list_to_create = Domain_HiringRequest_Entity_Request::getSingleListToCreate($hiring_request->extra);

		$action_user_id_list = [];
		foreach ($single_list_to_create as $v) {
			$action_user_id_list[] = $v["user_id"];
		}

		if ($hiring_request->candidate_user_id != 0) {
			$action_user_id_list[] = $hiring_request->candidate_user_id;
		}

		return $action_user_id_list;
	}
}