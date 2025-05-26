<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\ControllerMethodNotFoundException;
use CompassApp\Domain\Member\Entity\Member;
use CompassApp\Domain\Member\Entity\Permission;
use CompassApp\Domain\Member\Struct\Short;

/**
 * Подготовить последние прочитанные сообщения
 */
class Domain_Thread_Action_PrepareLastReadMessages {

	/**
	 * Подготовить последние прочитанные сообщения
	 *
	 * @param Struct_Db_CompanyThread_ThreadDynamic[] $dynamic_list
	 *
	 * @return Struct_Thread_LastReadMessage[]
	 */
	public static function do(array $dynamic_list):array {

		$dynamic_list = self::_filterDynamicLastReadMessages($dynamic_list);

		// готовим список последних сообщений
		return array_map(function(Struct_Db_CompanyThread_ThreadDynamic $dynamic) {

			return is_null($dynamic->last_read_message)
				? self::_makeEmptyLastMessage()
				: self::_makeLastMessage($dynamic->last_read_message);
		}, $dynamic_list);
	}

	/**
	 * Отфильтровать последниее сообщения в dynamic
	 *
	 * @param array $dynamic_list
	 *
	 * @return array
	 * @long
	 */
	protected static function _filterDynamicLastReadMessages(array $dynamic_list):array {

		// если запрещено показывать статус прочитанности сообщений, убираем плашки везде
		if (!Domain_Company_Action_Config_GetShowMessageReadStatus::do()) {
			return self::_removeLastReadMessages($dynamic_list);
		}

		return $dynamic_list;
	}

	/**
	 * Удалить последние сообщения из всех dynamic записей
	 *
	 * @param array $dynamic_list
	 *
	 * @return array
	 */
	protected static function _removeLastReadMessages(array $dynamic_list):array {

		return array_map(
			function(Struct_Db_CompanyThread_ThreadDynamic $dynamic) {

				$dynamic->last_read_message = null;
				return $dynamic;
			},
			$dynamic_list);
	}

	/**
	 * Вернуть пустое последнее сообщение
	 *
	 * @return Struct_Thread_LastReadMessage
	 */
	protected static function _makeEmptyLastMessage():Struct_Thread_LastReadMessage {

		return new Struct_Thread_LastReadMessage(
			"",
			0,
			0,
			[]
		);
	}

	/**
	 * Подготовить последнее сообщение для чата
	 *
	 * @param Struct_Db_CompanyThread_ThreadDynamic_LastReadMessage $dynamic_last_read_message
	 *
	 * @return Struct_Thread_LastReadMessage
	 */
	protected static function _makeLastMessage(Struct_Db_CompanyThread_ThreadDynamic_LastReadMessage $dynamic_last_read_message):Struct_Thread_LastReadMessage {

		// сортируем по дате прочтения в порядке возрастания
		uasort($dynamic_last_read_message->read_participants, static function(int $a, int $b) {

			return $a <=> $b;
		});

		$first_read_participants_list = array_slice(array_keys($dynamic_last_read_message->read_participants), 0, 5);

		return new Struct_Thread_LastReadMessage(
			$dynamic_last_read_message->message_map,
			$dynamic_last_read_message->thread_message_index,
			count($dynamic_last_read_message->read_participants),
			$first_read_participants_list
		);
	}
}