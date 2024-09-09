<?php

declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Класс обработки сценариев событий.
 */
class Domain_Invite_Scenario_Event {

	/**
	 * создаем и отправляем инвайт списку пользователей
	 *
	 * @throws \busException
	 */
	#[Type_Attribute_EventListener(Type_Event_Invite_CreateAndSendInvite::EVENT_TYPE, trigger_extra: ["group" => Type_Attribute_EventListener::SLOW_GROUP])]
	public static function createAndSendInviteForUserIdList(Struct_Event_Invite_CreateAndSendInvite $event_data):void {

		$user_info_list = Gateway_Bus_CompanyCache::getShortMemberList($event_data->user_id_list);

		foreach ($user_info_list as $user_info) {

			// если не человек, то пропускаем
			if (!\CompassApp\Domain\User\Main::isHuman($user_info->npc_type)) {
				continue;
			}

			// если нет диалога, создаем
			$single_meta_row = Helper_Single::createIfNotExist($event_data->sender_user_id, $user_info->user_id, true, false);

			try {
				Helper_Invites::inviteUserFromSingle($event_data->sender_user_id, $user_info->user_id, $event_data->meta_row, $single_meta_row, false, false);
			} catch (cs_InviteIsDuplicated) {
				continue;
			}
		}
	}

	/**
	 * создаем и отправляем инвайт пользователю в список групп
	 *
	 * @param Struct_Event_Invite_CreateAndSendInviteForConversationList $event_data
	 *
	 * @throws \paramException
	 */
	#[Type_Attribute_EventListener(Type_Event_Invite_CreateAndSendInviteForConversationList::EVENT_TYPE)]
	public static function createAndSendInviteForConversationList(Struct_Event_Invite_CreateAndSendInviteForConversationList $event_data):void {

		// если нет диалога, создаем
		$single_meta_row = Helper_Single::createIfNotExist($event_data->sender_user_id, $event_data->user_id);
		foreach ($event_data->conversation_map_list as $conversation_map) {

			$meta_row = Type_Conversation_Meta::get($conversation_map);

			try {
				Helper_Invites::inviteUserFromSingle($event_data->sender_user_id, $event_data->user_id, $meta_row, $single_meta_row, false, false);
			} catch (cs_InviteIsDuplicated) {
				continue;
			}
		}
	}

	/**
	 * создаем и отправляем инвайт списку пользователей
	 *
	 * @throws \busException
	 */
	#[Type_Attribute_EventListener(Type_Event_Invite_CreateAndSendAutoAcceptInvite::EVENT_TYPE, trigger_extra: ["group" => Type_Attribute_EventListener::SLOW_GROUP])]
	public static function createAndSendAutoAcceptedInviteForUserIdList(Struct_Event_Invite_CreateAndSendAutoAcceptInvite $event_data):void {

		$user_info_list = Gateway_Bus_CompanyCache::getShortMemberList($event_data->user_id_list);

		foreach ($user_info_list as $user_info) {

			try {
				Domain_Invite_Action_SendAutoAccepted::run($event_data->sender_user_id, $user_info, $event_data->meta_row, $event_data->platform, false);
			} catch (cs_InviteIsDuplicated) {
				continue;
			}
		}
	}
}
