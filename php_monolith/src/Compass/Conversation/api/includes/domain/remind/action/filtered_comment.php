<?php

namespace Compass\Conversation;

/**
 * Фильтруем комментарий для Напоминания
 */
class Domain_Remind_Action_FilteredComment {

	/**
	 * выполняем
	 *
	 * @throws cs_Message_IsTooLong
	 */
	public static function do(string $comment):string {

		$comment = Type_Api_Filter::replaceEmojiWithShortName($comment);
		if (mb_strlen($comment) > Type_Api_Filter::MAX_REMIND_TEXT_LENGTH) {
			throw new cs_Message_IsTooLong("comment for remind is too long");
		}
		return Type_Api_Filter::sanitizeMessageText($comment);
	}
}
