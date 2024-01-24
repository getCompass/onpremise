<?php

namespace Compass\Conversation;

/**
 * Событие — начат процесс изменения имени
 *
 * @event_category conversation
 * @event_name     leave_conversation_started
 */
class Type_Event_Conversation_UpdateAllowStatusAliasInLeftMenu {

	/** @var string тип события */
	public const EVENT_TYPE = "conversation.update_allow_status_alias_in_left_menu";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(int $allow_status, array $extra, string $conversation_map, int $user_id, int $opponent_user_id):Struct_Event_Base {

		$event_data = Struct_Event_Conversation_UpdateAllowStatusAliasInLeftMenu::build($allow_status, $extra, $conversation_map, $user_id, $opponent_user_id);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_Conversation_UpdateAllowStatusAliasInLeftMenu {

		return Struct_Event_Conversation_UpdateAllowStatusAliasInLeftMenu::build(...$event["event_data"]);
	}
}
