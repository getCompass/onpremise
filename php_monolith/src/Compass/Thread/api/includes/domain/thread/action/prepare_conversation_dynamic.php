<?php

namespace Compass\Thread;

/**
 *
 * Подготавливаем dynamic диалога для работы с тредами
 */
class Domain_Thread_Action_PrepareConversationDynamic {

	/**
	 * выполняем
	 */
	public static function do(array $conversation_dynamic, array $conversation_meta):Struct_SourceParentRel_Dynamic {

		return new Struct_SourceParentRel_Dynamic(
			Type_Thread_SourceParentDynamic::getLocationTypeParentString($conversation_meta["type"]),
			$conversation_dynamic["user_clear_info"],
			$conversation_dynamic["user_mute_info"],
			$conversation_dynamic["conversation_clear_info"],
		);
	}
}