<?php

namespace Compass\Pivot;

/**
 * Класс сущности "количество действий"
 */
class Domain_Rating_Entity_Action {

	protected const _GROUPS_CREATED                = "groups_created";
	protected const _CONVERSATIONS_READ            = "conversations_read";
	protected const _CONVERSATION_MESSAGES_SENT    = "conversation_messages_sent";
	protected const _CONVERSATION_REACTIONS_ADDED  = "conversation_reactions_added";
	protected const _CONVERSATIONS_REMINDS_CREATED = "conversation_reminds_created";
	protected const _CALLS                         = "calls";
	protected const _THREADS_CREATED               = "threads_created";
	protected const _THREADS_READ                  = "threads_read";
	protected const _THREAD_MESSAGES_SENT          = "thread_messages_sent";
	protected const _THREAD_REACTIONS_ADDED        = "thread_reactions_added";
	protected const _THREAD_REMINDS_CREATED        = "thread_reminds_created";

	protected const _DEFAULT_ACTION_LIST = [
		self::_GROUPS_CREATED                => 0,
		self::_CONVERSATIONS_READ            => 0,
		self::_CONVERSATION_MESSAGES_SENT    => 0,
		self::_CONVERSATION_REACTIONS_ADDED  => 0,
		self::_CONVERSATIONS_REMINDS_CREATED => 0,
		self::_CALLS                         => 0,
		self::_THREADS_CREATED               => 0,
		self::_THREADS_READ                  => 0,
		self::_THREAD_MESSAGES_SENT          => 0,
		self::_THREAD_REACTIONS_ADDED        => 0,
		self::_THREAD_REMINDS_CREATED        => 0,
	];

	/**
	 * Дефолтный список действий
	 *
	 * @return int[]
	 */
	public static function makeDefaultActionList():array {

		return self::_DEFAULT_ACTION_LIST;
	}

	/**
	 * Инкрементим количество действий
	 *
	 * @param array $action_list
	 * @param array $inc_action_list
	 *
	 * @return array
	 */
	public static function incActionList(array $action_list, array $inc_action_list):array {

		// инкрементим
		foreach ($inc_action_list as $action => $action_count) {

			// если не оказалось за этот день такого поля - добавляем
			if (!isset($action_list[$action])) {
				$action_list[$action] = 0;
			}

			// инкрементим
			$action_list[$action] += $action_count;
		}

		return $action_list;
	}

	/**
	 * Получаем общее количество действий из массива действий
	 *
	 * @param array $action_list
	 *
	 * @return int
	 */
	public static function getTotalActionCount(array $action_list):int {

		// инкрементим
		$total_action_count = 0;
		foreach ($action_list as $action_count) {
			$total_action_count += $action_count;
		}

		return $total_action_count;
	}
}