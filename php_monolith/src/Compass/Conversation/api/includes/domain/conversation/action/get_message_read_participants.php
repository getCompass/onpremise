<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Gateway\QueryFatalException;
use CompassApp\Domain\Member\Entity\Permission;
use CompassApp\Domain\Member\Struct\Short;
use CompassApp\Pack\Message\Conversation;

/**
 * Получить просмотревших пользователей
 */
class Domain_Conversation_Action_GetMessageReadParticipants {

	/**
	 * Получить просмотревших пользователей
	 *
	 * @param array  $meta_row
	 * @param string $message_map
	 *
	 * @return Struct_Db_CompanyConversation_MessageReadParticipant_Participant[]
	 * @throws Domain_Conversation_Exception_Message_ExpiredForGetReadParticipants
	 * @throws QueryFatalException
	 * @throws ParseFatalException
	 * @throws \cs_UnpackHasFailed
	 */
	public static function do(array $meta_row, string $message_map):array {

		$conversation_map = $meta_row["conversation_map"];

		$message                = Domain_Conversation_Action_Message_Get::do($message_map);
		$sender_user_id         = Type_Conversation_Message_Main::getHandler($message)::getSenderUserId($message);
		$remind_creator_user_id = Type_Conversation_Message_Main::getHandler($message)::getRemindCreatorUserId($message);

		if (Type_Conversation_Message_Main::getHandler($message)::getCreatedAt($message) < time() - DAY14) {
			throw new Domain_Conversation_Exception_Message_ExpiredForGetReadParticipants("message sent 2 weeks ago");
		}

		// получаем список прочитавших
		$conversation_message_index = Conversation::getConversationMessageIndex($message_map);
		$message_read_participants  = Gateway_Db_CompanyConversation_MessageReadParticipants::getReadParticipants($conversation_map, $conversation_message_index);

		// удаляем из списка создателя сообщения и создателя напоминания в сингле
		unset($message_read_participants[$sender_user_id]);

		if (Type_Conversation_Meta::isSubtypeOfSingle($meta_row["type"])) {
			unset($message_read_participants[$remind_creator_user_id]);
		}

		// сортируем по дате прочтения в порядке убывания
		usort($message_read_participants, function(Struct_Db_CompanyConversation_MessageReadParticipant_Participant $a,
									 Struct_Db_CompanyConversation_MessageReadParticipant_Participant $b) {

			return $b->read_at <=> $a->read_at;
		});

		// отдаем список прочитавших
		return $message_read_participants;
	}
}