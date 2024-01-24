<?php

namespace Compass\Conversation;

/**
 * Класс для работы с текстом сообщений в рамках поиска.
 */
class Domain_Search_Helper_MessageText {

	/**
	 * Удаляем все упоминания из текста сообщения,
	 * заменяя из строкой отображения упоминания.
	 */
	public static function replaceMentions(string $text):string {

		return preg_replace("#\[\"@\"\|\d+\|\"(.*?)\"]#", "@$1", $text);
	}
}