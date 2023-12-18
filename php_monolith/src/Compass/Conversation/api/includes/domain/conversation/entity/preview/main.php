<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * класс для работы с прикрепленными к диалогу превью
 */
class Domain_Conversation_Entity_Preview_Main {

	public const PARENT_TYPE_CONVERSATION = 0;
	public const PARENT_TYPE_THREAD       = 1;

	public const PARENT_TYPE_TO_STRING_SCHEMA = [
		self::PARENT_TYPE_CONVERSATION => "conversation",
		self::PARENT_TYPE_THREAD       => "thread",
	];

	// прикрепляем превью
	public static function add(Struct_Db_CompanyConversation_ConversationPreview $preview):void {

		Gateway_Db_CompanyConversation_ConversationPreview::insert($preview);
	}

	// прикрепляем превью за чатом
	public static function createStructForConversation(int $user_id, string $conversation_map, string $message_map, string $preview_map, int $conversation_message_created_at, array $link_list):Struct_Db_CompanyConversation_ConversationPreview {

		$parent_type = self::PARENT_TYPE_CONVERSATION;
		return self::_createPreviewStruct(
			$user_id,
			$conversation_map,
			$parent_type,
			$message_map,
			$message_map,
			$preview_map,
			$conversation_message_created_at,
			$conversation_message_created_at,
			$link_list,
		);
	}

	// прикрепляем превью за тредом
	public static function createStructForThread(int $user_id, string $conversation_map, string $thread_message_map, string $conversation_message_map, string $preview_map, int $conversation_message_created_at, int $thread_message_created_at, array $link_list):Struct_Db_CompanyConversation_ConversationPreview {

		$parent_type = self::PARENT_TYPE_THREAD;
		return self::_createPreviewStruct(
			$user_id,
			$conversation_map,
			$parent_type,
			$thread_message_map,
			$conversation_message_map,
			$preview_map,
			$conversation_message_created_at,
			$thread_message_created_at,
			$link_list,
		);
	}

	/**
	 * Создаем insert для вставки в базу с превью
	 *
	 * @param int    $user_id
	 * @param string $conversation_map
	 * @param int    $parent_type
	 * @param string $parent_message_map
	 * @param string $conversation_message_map
	 * @param string $preview_map
	 * @param int    $conversation_message_created_at
	 * @param int    $parent_message_created_at
	 * @param array  $link_list
	 * @param array  $hidden_by_user_list
	 *
	 * @return Struct_Db_CompanyConversation_ConversationPreview
	 * @long
	 */
	protected static function _createPreviewStruct(
		int    $user_id,
		string $conversation_map,
		int    $parent_type,
		string $parent_message_map,
		string $conversation_message_map,
		string $preview_map,
		int    $conversation_message_created_at,
		int    $parent_message_created_at,
		array  $link_list,
		array  $hidden_by_user_list = [],
	):Struct_Db_CompanyConversation_ConversationPreview {

		// создаем запись
		return new Struct_Db_CompanyConversation_ConversationPreview(
			$parent_type,
			$parent_message_map,
			0,
			$conversation_message_created_at,
			$parent_message_created_at,
			time(),
			0,
			$user_id,
			$preview_map,
			$conversation_map,
			$conversation_message_map,
			$link_list,
			$hidden_by_user_list
		);
	}

	/**
	 * Получить отсортированный список превью
	 *
	 * @param int    $user_id
	 * @param string $conversation_map
	 * @param int    $user_clear_until_at
	 * @param int    $count
	 * @param int    $offset
	 * @param bool   $filter_self_only
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws \cs_UnpackHasFailed
	 */
	public static function getSortedList(int $user_id, string $conversation_map, int $user_clear_until_at, int $count, int $offset, bool $filter_self_only):array {

		$parent_type_list = [self::PARENT_TYPE_CONVERSATION, self::PARENT_TYPE_THREAD];

		$conversation_preview_list = $filter_self_only
			? Gateway_Db_CompanyConversation_ConversationPreview::getSortedListByUserid(
				$conversation_map, $parent_type_list, $user_clear_until_at, $count + 1, $offset, $user_id)
			: Gateway_Db_CompanyConversation_ConversationPreview::getSortedList(
				$conversation_map, $parent_type_list, $user_clear_until_at, $count + 1, $offset);

		$has_next                  = count($conversation_preview_list) > $count;
		$conversation_preview_list = array_slice($conversation_preview_list, 0, $count);

		$grouped_conversation_preview_list = self::_filterPreviews($user_id, $conversation_preview_list);

		return [self::_sortPreviews($grouped_conversation_preview_list), $has_next];
	}

	/**
	 * Фильтруем полученные превью
	 *
	 * @param int   $user_id
	 * @param array $conversation_preview_list
	 *
	 * @return array
	 * @throws \cs_UnpackHasFailed
	 * @long
	 */
	protected static function _filterPreviews(int $user_id, array $conversation_preview_list):array {

		/** @var Struct_Db_CompanyConversation_ConversationPreview[] $output_preview_list */
		$not_hidden_preview_list = [];

		/** @var Struct_Db_CompanyConversation_ConversationPreview[] $output_preview_list */
		$grouped_output_preview_list = [];

		foreach ($conversation_preview_list as $preview_row) {

			// если пользователь скрыл превью -> пропускаем
			if (!in_array($user_id, $preview_row->hidden_by_user_list, true)) {
				$not_hidden_preview_list[] = $preview_row;
			}
		}

		if (count($not_hidden_preview_list) < 1) {
			return [];
		}

		// собираем message_map отобранных превью
		$message_map_list = [];
		foreach ($not_hidden_preview_list as $v) {
			$message_map_list[$v->conversation_message_map] = true;
		}

		// оставляем только уникальные ключи
		$message_map_list = array_keys($message_map_list);

		$hidden_message_map_list = Gateway_Db_CompanyConversation_MessageUserHiddenRel::getMessageMapList($user_id, $message_map_list);
		$hidden_message_map_list = array_flip($hidden_message_map_list);

		foreach ($not_hidden_preview_list as $conversation_preview_row) {

			if (!isset($hidden_message_map_list[$conversation_preview_row->conversation_message_map])) {

				$parent_source_map = match ($conversation_preview_row->parent_type) {
					self::PARENT_TYPE_CONVERSATION => \CompassApp\Pack\Message\Conversation::getConversationMap($conversation_preview_row->parent_message_map),
					self::PARENT_TYPE_THREAD => \CompassApp\Pack\Message\Thread::getThreadMap($conversation_preview_row->parent_message_map)
				};

				$grouped_output_preview_list[$parent_source_map][] = $conversation_preview_row;
			}
		}

		return $grouped_output_preview_list;
	}

	/**
	 * Сортируем превью
	 *
	 * @param array $grouped_conversation_preview_list
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws \cs_UnpackHasFailed
	 */
	protected static function _sortPreviews(array $grouped_conversation_preview_list):array {

		$output_conversation_preview_list = [];
		foreach ($grouped_conversation_preview_list as $conversation_preview_list) {

			$conversation_preview_list        = self::_sortParentMessageGroup($conversation_preview_list);
			$output_conversation_preview_list = array_merge($output_conversation_preview_list, $conversation_preview_list);
		}

		$callback_fn = static function(Struct_Db_CompanyConversation_ConversationPreview $a, Struct_Db_CompanyConversation_ConversationPreview $b):int {

			if ($a->parent_message_created_at !== $b->parent_message_created_at) {
				return $a->parent_message_created_at < $b->parent_message_created_at ? 1 : -1;
			}
			return 0;
		};

		usort($output_conversation_preview_list, $callback_fn);

		return $output_conversation_preview_list;
	}

	/**
	 * Отсортировать группу сообщений
	 *
	 * @param array $conversation_preview_list
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws \cs_UnpackHasFailed
	 */
	protected static function _sortParentMessageGroup(array $conversation_preview_list):array {

		$callback_fn = static function(
			Struct_Db_CompanyConversation_ConversationPreview $a, Struct_Db_CompanyConversation_ConversationPreview $b):int {

			// если родитель у них разный - попробуй догадайся, что было раньше. Никак не влияет на пользовательский опыт
			// если время разное - то и сортировать не надо вообще
			if ($a->parent_message_created_at !== $b->parent_message_created_at) {
				return $a->parent_message_created_at < $b->parent_message_created_at ? 1 : -1;
			}

			// сравниваем индексы сообщения, чтобы понять, что должно быть раньше
			return match ($a->parent_type) {
				self::PARENT_TYPE_CONVERSATION => self::_compareConversationPreviews($a, $b),
				self::PARENT_TYPE_THREAD => self::_compareThreadPreviews($a, $b),
				default => throw new ParseFatalException("unknown parent type")
			};
		};

		usort($conversation_preview_list, $callback_fn);

		return $conversation_preview_list;
	}

	/**
	 * Сравнить превью из чата
	 *
	 * @param Struct_Db_CompanyConversation_ConversationPreview $a
	 * @param Struct_Db_CompanyConversation_ConversationPreview $b
	 *
	 * @return int
	 * @throws \cs_UnpackHasFailed
	 */
	protected static function _compareConversationPreviews(Struct_Db_CompanyConversation_ConversationPreview $a, Struct_Db_CompanyConversation_ConversationPreview $b):int {


		$a_block_id = \CompassApp\Pack\Message\Conversation::getBlockId($a->parent_message_map);
		$b_block_id = \CompassApp\Pack\Message\Conversation::getBlockId($b->parent_message_map);

		$a_message_index = \CompassApp\Pack\Message\Conversation::getBlockMessageIndex($a->parent_message_map);
		$b_message_index = \CompassApp\Pack\Message\Conversation::getBlockMessageIndex($b->parent_message_map);

		if ($a_block_id !== $b_block_id) {
			return $a_block_id < $b_block_id ? 1 : -1;
		}

		if ($a_message_index !== $b_message_index) {
			return $a_message_index < $b_message_index ? 1 : -1;
		}

		return 0;
	}

	/**
	 * Сравнить превью из треда
	 *
	 * @param Struct_Db_CompanyConversation_ConversationPreview $a
	 * @param Struct_Db_CompanyConversation_ConversationPreview $b
	 *
	 * @return int
	 */
	protected static function _compareThreadPreviews(Struct_Db_CompanyConversation_ConversationPreview $a, Struct_Db_CompanyConversation_ConversationPreview $b):int {

		$a_block_id = \CompassApp\Pack\Message\Thread::getBlockId($a->parent_message_map);
		$b_block_id = \CompassApp\Pack\Message\Thread::getBlockId($b->parent_message_map);

		$a_message_index = \CompassApp\Pack\Message\Thread::getBlockMessageIndex($a->parent_message_map);
		$b_message_index = \CompassApp\Pack\Message\Thread::getBlockMessageIndex($b->parent_message_map);

		if ($a_block_id !== $b_block_id) {
			return $a_block_id < $b_block_id ? 1 : -1;
		}

		if ($a_message_index !== $b_message_index) {
			return $a_message_index < $b_message_index ? 1 : -1;
		}

		return 0;
	}

	/**
	 * Обновить мапу превью
	 *
	 * @param int    $parent_type
	 * @param string $parent_message_map
	 * @param string $preview_map
	 *
	 * @return void
	 */
	public static function updatePreviewAndLinkList(int $parent_type, string $parent_message_map, string $preview_map, array $link_list):void {

		Gateway_Db_CompanyConversation_ConversationPreview::set($parent_type, $parent_message_map, [
			"is_deleted"  => 0,
			"preview_map" => $preview_map,
			"link_list"   => $link_list,
		]);
	}

	/**
	 * Пометить список удаленных сообщений
	 *
	 * @param int   $parent_type
	 * @param array $parent_message_map_list
	 *
	 * @return void
	 */
	public static function setDeletedList(int $parent_type, array $parent_message_map_list):void {

		Gateway_Db_CompanyConversation_ConversationPreview::setDeletedListByParentMapList($parent_type, $parent_message_map_list);
	}

	/**
	 * Пометить список удаленных сообщений по сообщению-родителю в чате
	 *
	 * @param array $conversation_message_map_list
	 *
	 * @return void
	 */
	public static function setDeletedByConversationMessageList(array $conversation_message_map_list):void {

		$count = Gateway_Db_CompanyConversation_ConversationPreview::getCountByConversationMessageMapList($conversation_message_map_list);
		Gateway_Db_CompanyConversation_ConversationPreview::setDeletedListByConversationMessageMapList($conversation_message_map_list, $count);
	}

	/**
	 * Скрыть список для пользователя
	 *
	 * @param int   $user_id
	 * @param int   $parent_type
	 * @param array $parent_message_map_list
	 *
	 * @return void
	 * @throws ReturnFatalException
	 * @throws RowNotFoundException
	 */
	public static function hideList(int $user_id, int $parent_type, array $parent_message_map_list):void {

		$preview_list = Gateway_Db_CompanyConversation_ConversationPreview::getList($parent_type, $parent_message_map_list);

		foreach ($preview_list as $preview) {

			Gateway_Db_CompanyConversation_Main::beginTransaction();
			$preview = Gateway_Db_CompanyConversation_ConversationPreview::getForUpdate($preview->parent_type, $preview->parent_message_map);

			Gateway_Db_CompanyConversation_ConversationPreview::set($preview->parent_type, $preview->parent_message_map, [
				"hidden_by_user_list" => array_merge($preview->hidden_by_user_list, [$user_id]),
			]);

			Gateway_Db_CompanyConversation_Main::commitTransaction();
		}
	}
}