<?php

namespace Compass\Conversation;

use BaseFrame\System\Locale;

/**
 * класс для работы с очередью url preview
 */
class Type_Preview_Producer {

	// функция для обработки ссылки в тексте и последующей отправки в нужный крон
	public static function addTaskIfLinkExistInMessage(int $user_id, string $text, string $message_map, array $user_list, bool $is_preview_parse = true):void {

		// если URL Preview сейчас отключено - просто выходим
		if (Type_Preview_Config::isPreviewEnabled() === false) {
			return;
		}

		$text = Type_Preview_Main::doCheckAllMentions($text);

		[$link_list, $count_words] = Type_Preview_Main::doFindAllLinks($text);

		// если в сообщении нет ссылок - выходим
		if (count($link_list) < 1) {
			return;
		}

		// если отправитель сообщения есть в списке пользователей, ссылки от которых не нужно парсить
		if (defined("SKIP_PREVIEW_SENDER_LIST") && in_array($user_id, SKIP_PREVIEW_SENDER_LIST)) {
			return;
		}

		// добавляем задачу в очередь для парсинга ссылок
		self::_addTaskForParse($message_map, $user_id, $user_list, $text, $count_words, $link_list, $is_preview_parse);
	}

	// добавляем задачу в очередь для парсинга ссылок
	protected static function _addTaskForParse(string $message_map, int $user_id, array $user_list, string $text, int $count_words, array $link_list, bool $is_preview_parse):void {

		$need_full_preview = false;

		if ($is_preview_parse && $count_words === 1 && Type_Preview_Main::checkIsTextAreUrl($text, $link_list[0])) {
			$need_full_preview = true;
		}

		// добавляем язык, на котором надо спарсить ссылку
		$lang = Locale::getLang();

		// отправляем в очередь на парсинг
		$event_data = Type_Event_Conversation_LinkParseRequired::create($message_map, $user_id, $link_list, $lang, $user_list, [], $need_full_preview);
		Gateway_Event_Dispatcher::dispatch($event_data);
	}

	// функция для парса ссылок в тексте, отличном от структуры сообщени
	public static function addTaskIfLinkExistInText(int $creator_user_id, int $opposite_user_id, array $user_list, string $text, int $entity_type, int $entity_id):array {

		// парсим ссылки
		[$link_list] = Type_Preview_Main::doFindAllLinks($text);

		// если в сообщении нет ссылок - выходим
		if (count($link_list) < 1) {
			return [];
		}

		// если отправитель сообщения есть в списке юзеров, ссылки от которых не нужно парсить
		if (defined("SKIP_PREVIEW_SENDER_LIST") && in_array($creator_user_id, SKIP_PREVIEW_SENDER_LIST)) {
			return [];
		}

		// если пришла почта, вместо ссылки
		if (Type_Preview_Utils::isEmail($text)) {
			return [];
		}

		$lang        = Locale::getLang();
		$entity_info = [
			"opposite_user_id" => $opposite_user_id,
			"entity_type"      => $entity_type,
			"entity_id"        => $entity_id,
		];

		// отправляем в очередь для определения редиректов
		$event_data = Type_Event_Conversation_LinkParseRequired::create(
			"", $creator_user_id, $link_list, $lang, $user_list, $entity_info, false);
		Gateway_Event_Dispatcher::dispatch($event_data);

		return $link_list;
	}
}