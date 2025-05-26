<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Gateway\QueryFatalException;
use CompassApp\Domain\Member\Entity\Permission;
use CompassApp\Domain\Member\Struct\Short;
use CompassApp\Pack\Message\Thread;

/**
 * Получить список прочитавших сообщение
 */
class Domain_Thread_Action_GetMessageReadParticipants {

	/**
	 * Получить список прочитавших сообщение
	 *
	 * @param array  $meta_row
	 * @param int    $location_type
	 * @param string $message_map
	 *
	 * @return Struct_Db_CompanyThread_MessageReadParticipant_Participant[]
	 * @throws Domain_Thread_Exception_Message_ExpiredForGetReadParticipants
	 * @throws QueryFatalException
	 * @throws \parseException
	 */
	public static function do(array $meta_row, int $location_type, string $message_map):array {

		$thread_map = $meta_row["thread_map"];

		$message                = Domain_Thread_Action_Message_Get::do($message_map);
		$sender_user_id         = Type_Thread_Message_Main::getHandler($message)::getSenderUserId($message);
		$remind_creator_user_id = Type_Thread_Message_Main::getHandler($message)::getRemindCreatorUserId($message);

		if (Type_Thread_Message_Main::getHandler($message)::getMessageCreatedAt($message) < time() - DAY14) {
			throw new Domain_Thread_Exception_Message_ExpiredForGetReadParticipants("message sent 2 weeks ago");
		}

		$thread_message_index      = Thread::getThreadMessageIndex($message_map);
		$message_read_participants = Gateway_Db_CompanyThread_MessageReadParticipants::getReadParticipants($thread_map, $thread_message_index);

		// удаляем из списка создателя сообщения и создателя напоминания в сингле
		unset($message_read_participants[$sender_user_id]);

		if (Type_Thread_SourceParentDynamic::isSubtypeOfSingle($location_type)) {
			unset($message_read_participants[$remind_creator_user_id]);
		}

		// сортируем по дате прочтения в порядке убывания
		usort($message_read_participants, function(Struct_Db_CompanyThread_MessageReadParticipant_Participant $a,
									 Struct_Db_CompanyThread_MessageReadParticipant_Participant $b) {

			return $b->read_at <=> $a->read_at;
		});

		// отдаем список прочитавших
		return $message_read_participants;
	}
}