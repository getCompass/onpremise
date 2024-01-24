<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Удалить пользователя из диалогов
 */
class Domain_Conversation_Action_ClearConversations {

	/**
	 * Удалить пользователя из диалогов
	 *
	 * @throws \busException
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function run(int $user_id, int $limit, int $offset):bool {

		$conversation_list = Domain_Conversation_Entity_ConversationsUser::getConversationsByUserId($user_id, $limit, $offset);

		// проходимся по каждому диалогу и удаляем
		foreach ($conversation_list as $conversation_map) {

			$left_menu_row = Type_Conversation_LeftMenu::get($user_id, $conversation_map);

			try {
				self::_clearConversationByType($left_menu_row, $conversation_map, $user_id);
			} catch (cs_OwnerTryToLeaveGeneralConversation $e) {

				if (isTestServer()) {

					Type_System_Admin::log("cs_OwnerTryToLeaveGeneralConversation", [
						"user_info"     => Gateway_Bus_CompanyCache::getShortMemberList([$user_id]),
						"left_menu_row" => $left_menu_row,
					]);
				}

				throw $e;
			}
		}

		// если диалогов меньше чем лимит - значит почистили последние
		if (count($conversation_list) < $limit) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Удалим пользователя из диалога
	 *
	 * @throws \paramException
	 * @throws \parseException
	 */
	protected static function _clearConversationByType(array $left_menu_row, string $conversation_map, int $user_id):void {

		// обрабатываем случаи сингл и груп диалогов
		switch ($left_menu_row["type"]) {

			// случай для single диалога
			case CONVERSATION_TYPE_SINGLE_DEFAULT:
			case CONVERSATION_TYPE_SINGLE_WITH_SYSTEM_BOT:

				Domain_Conversation_Action_DoLeaveSingle::do($conversation_map, $user_id, $left_menu_row["opponent_user_id"]);
				break;

			// случай для group дилога
			case CONVERSATION_TYPE_GROUP_DEFAULT:
			case CONVERSATION_TYPE_GROUP_RESPECT:
			case CONVERSATION_TYPE_GROUP_GENERAL:

				self::_clearConversationGroupDefault($conversation_map, $user_id);
				break;

			// случай для группы отдела поддержки
			case CONVERSATION_TYPE_GROUP_SUPPORT:

				self::_clearConversationGroupSupport($conversation_map, $user_id);
				break;

			// случай для hiring дилога
			case CONVERSATION_TYPE_GROUP_HIRING:

				self::_clearConversationGroupHiring($conversation_map, $user_id);
				break;

			// случай для чата заметок
			case CONVERSATION_TYPE_SINGLE_NOTES:

				// скрываем чат
				Helper_Single::remove($user_id, $conversation_map, 0);
				break;

			default:
				throw new ParseFatalException(__METHOD__ . ": undefined conversation type");
		}
	}

	/**
	 * Удалим пользователя из группового диалога
	 *
	 * @param string $conversation_map
	 * @param int    $user_id
	 *
	 * @throws \paramException
	 * @throws cs_OwnerTryToLeaveGeneralConversation
	 */
	protected static function _clearConversationGroupDefault(string $conversation_map, int $user_id):void {

		$meta_row = Type_Conversation_Meta::get($conversation_map);

		// нужно ли системное сообщение о том, что пользователь покинул компанию
		$is_need_system_message_about_company_left = Type_Conversation_Meta_Extra::isNeedSystemMessageOnDismissal($meta_row["extra"]);

		// получаем флаг нужно ли системное сообщение о том, что пользователь покинул группу в зависимости от другого флага
		$is_need_system_message_about_group_left = $is_need_system_message_about_company_left ? false : true;

		// покидаем групповой диалог
		Helper_Groups::doLeave(
			$conversation_map,
			$user_id,
			$meta_row,
			$is_need_system_message_about_group_left,
			false,
			$is_need_system_message_about_company_left
		);
	}

	/**
	 * Удалим пользователя из отдела поддержки
	 *
	 * @param string $conversation_map
	 * @param int    $user_id
	 *
	 * @throws \paramException
	 * @throws cs_OwnerTryToLeaveGeneralConversation
	 */
	protected static function _clearConversationGroupSupport(string $conversation_map, int $user_id):void {

		$meta_row = Type_Conversation_Meta::get($conversation_map);

		// покидаем групповой диалог
		Helper_Groups::doLeave(
			$conversation_map,
			$user_id,
			$meta_row,
			false,
			false,
			false
		);
	}

	/**
	 * Удалим пользователя из чата Найма
	 *
	 * @throws \paramException
	 * @throws cs_OwnerTryToLeaveGeneralConversation
	 */
	protected static function _clearConversationGroupHiring(string $conversation_map, int $user_id):void {

		// получаем мету
		$meta_row = Type_Conversation_Meta::get($conversation_map);

		// пользователь является участником диалога?
		if (!Type_Conversation_Meta_Users::isMember($user_id, $meta_row["users"])) {
			return;
		}

		// покидаем групповой диалог
		Helper_Groups::doLeave($conversation_map, $user_id, $meta_row, false);

		if (defined("IS_HIRING_SYSTEM_MESSAGES_ENABLED") && (bool) IS_HIRING_SYSTEM_MESSAGES_ENABLED) {

			// отправляем системное сообщение в диалог, что пользователь покинул группу
			$system_message = Type_Conversation_Message_Main::getLastVersionHandler()::makeSystemUserKickedFromGroup($user_id);
			Type_Phphooker_Main::addMessage(
				$conversation_map, $system_message, $meta_row["users"], $meta_row["type"], $meta_row["conversation_name"], $meta_row["extra"]
			);
		}
	}
}
