<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * скрываем несколько сообщений
 */
class Domain_Conversation_Action_Message_HideList {

	public static function do(int $user_id, array $message_map_list, string $conversation_map, string $previous_message_map):void {

		$dynamic_row                          = Domain_Conversation_Entity_Dynamic::get($conversation_map);
		$message_map_list_grouped_by_block_id = [];
		[$block_row_list, $not_exist_message_map_list] = Domain_Conversation_Entity_Message_Block_Get::getBlockListRowByMessageMapList(
			$conversation_map,
			$dynamic_row,
			$message_map_list,
		);

		foreach ($message_map_list as $v) {

			$block_id = \CompassApp\Pack\Message\Conversation::getBlockId($v);

			// если сообщение в списке ненайденных то что то пошло не так
			if (in_array($v, $not_exist_message_map_list)) {
				throw new ParseFatalException("Unknown message");
			}

			self::_throwIfMessageIsNotAllowedToHide($v, $block_row_list[$block_id]);
			$message_map_list_grouped_by_block_id[$block_id][] = $v;
		}

		// скрываем все сообщения
		$dynamic = self::_doHideMessageList($user_id, $conversation_map, $message_map_list_grouped_by_block_id);

		// обновялем последнее сообщение в левом меню
		$max_message_index = self::_getMaxMessageIndex($message_map_list);
		$left_menu_row     = self::_updateLastMessageOnMessageHide($user_id, $conversation_map, $max_message_index, $previous_message_map, $dynamic_row);

		// форматируем запись левого меню
		$prepared_left_menu_row  = Type_Conversation_Utils::prepareLeftMenuForFormat($left_menu_row);
		$formatted_left_menu_row = Apiv1_Format::leftMenu($prepared_left_menu_row);

		$talking_user_schema = Gateway_Bus_Sender::makeTalkingUserItem($user_id, false);
		Gateway_Bus_Sender::conversationMessageHiddenList(
			[$talking_user_schema], $message_map_list, $conversation_map, $formatted_left_menu_row, $dynamic->messages_updated_version
		);

		// отправляем сообщение на скрытие сообщения
		Domain_Search_Entity_ConversationMessage_Task_Hide::queueList($message_map_list, [$user_id]);
	}

	// проверяем, что в списке нет сообщений, тип которых запрещен к удалению
	protected static function _throwIfMessageIsNotAllowedToHide(string $message_map, array $block_row):void {

		$message = Domain_Conversation_Entity_Message_Block_Message::get($message_map, $block_row);
		if (!Type_Conversation_Message_Main::getHandler($message)::isAllowToHide($message)) {
			throw new ParamException("you have not permissions to hide this message");
		}
	}

	// скрываем список сообщение и отписываем от тредов в них и скрываем файлы
	protected static function _doHideMessageList(int $user_id, string $conversation_map, array $message_map_list_grouped_by_block_id):Struct_Db_CompanyConversation_ConversationDynamic {

		// проходимся по всем сообщениям сгруппированным по block_id
		$block_row_grouped_by_block_id       = [];
		$message_list_grouped_by_message_map = [];
		$list_of_all_message_map             = [];
		$insert_array                        = [];
		foreach ($message_map_list_grouped_by_block_id as $block_id => $message_map_list) {

			$block_row                                = Type_Conversation_Message_Block::hideMessageList($message_map_list, $user_id, $conversation_map, $block_id);
			$block_row_grouped_by_block_id[$block_id] = $block_row;

			foreach ($message_map_list as $message_map) {

				$message_list_grouped_by_message_map[$message_map] = Domain_Conversation_Entity_Message_Block_Message::get(
					$message_map,
					$block_row_grouped_by_block_id[$block_id]
				);

				$list_of_all_message_map[]                         = $message_map;

				$insert_array[] = [
					"user_id"     => $user_id,
					"message_map" => $message_map,
					"created_at"  => time(),
				];
			}
		}

		// добавляем в список скрытых файлов
		Gateway_Db_CompanyConversation_MessageUserHiddenRel::insertArray($insert_array);

		// отписываемся от тредов
		self::_hideAndUnfollowFromThreadList($conversation_map, $list_of_all_message_map, $user_id, true);

		// скрываем файлы
		return self::_onHideMessageListWithFile($conversation_map, $message_list_grouped_by_message_map, $user_id);
	}

	// если у скрываемых сообщений есть треды - отписываемся от них и скрываем, чтобы не приходило пушей
	protected static function _hideAndUnfollowFromThreadList(string $conversation_map, array $message_map_list, int $user_id, bool $hide_parent_thread):void {

		// получаем треды из списка релейшенов по списку message map
		$thread_map_list = Type_Conversation_ThreadRel::getThreadRelByMessageMapList($conversation_map, $message_map_list);

		if (count($thread_map_list) < 1) {
			return;
		}

		// отправляем задачу на отписку от тредов
		Type_Phphooker_Main::doUnfollowThreadList($user_id, $thread_map_list);

		// только если скрываемое сообщение является инициализирующим тред
		Type_Phphooker_Main::doHideParentThreadOnHideConversation($user_id, $thread_map_list, $hide_parent_thread);
	}

	// если в скрываемом сообщении есть файл, помечаем файл скрытым для пользователя
	// увеличиваем счетчик файлов с изображениями
	protected static function _onHideMessageListWithFile(string $conversation_map, array $message_list_grouped_by_message_map, int $user_id):Struct_Db_CompanyConversation_ConversationDynamic {

		// получаем список скрытых файлов
		$hidden_file_map_list_grouped_by_message_map = self::_getHiddenFileMapListGroupedByMessageMap($message_list_grouped_by_message_map);

		$hidden_file_count  = 0;
		$hidden_image_count = 0;
		$hidden_video_count = 0;

		foreach ($hidden_file_map_list_grouped_by_message_map as $v1) {

			foreach ($v1 as $v2) {

				$file_type = \CompassApp\Pack\File::getFileType($v2);

				// инкрементим количество скрытых файлов, изображений & видео, это нужно только для карусели
				$hidden_file_count++;
				$hidden_image_count = $file_type == FILE_TYPE_IMAGE ? $hidden_image_count + 1 : $hidden_image_count;
				$hidden_video_count = $file_type == FILE_TYPE_VIDEO ? $hidden_video_count + 1 : $hidden_video_count;
			}
		}

		// устанавливаем новые значения скрытых для пользвателя файлов и изображений
		return Domain_Conversation_Entity_Dynamic::setFileClearInfo($conversation_map, $user_id, $hidden_file_count, $hidden_image_count, $hidden_video_count);
	}

	// получаем hidden_file_map_list_grouped_by_message_map из списка сообщений
	protected static function _getHiddenFileMapListGroupedByMessageMap(array $message_list_grouped_by_message_map):array {

		$hidden_file_map_list_grouped_by_message_map = [];
		foreach ($message_list_grouped_by_message_map as $k => $v) {

			[$hidden_file_map_list] = Type_Conversation_Message_Main::getHandler($v)::getFileMapAndFileUuidListFromAnyMessage($v);
			if (count($hidden_file_map_list) > 0) {
				$hidden_file_map_list_grouped_by_message_map[$k] = $hidden_file_map_list;
			}
		}

		return $hidden_file_map_list_grouped_by_message_map;
	}

	// получаем индекс из списка сообщений
	protected static function _getMaxMessageIndex(array $message_map_list):int {

		$max_index = 0;
		foreach ($message_map_list as $v) {
			$max_index = max(\CompassApp\Pack\Message\Conversation::getConversationMessageIndex($v), $max_index);
		}

		return $max_index;
	}

	// актуализируем left_menu после скрытия сообщения
	protected static function _updateLastMessageOnMessageHide(int $user_id, string $conversation_map, int $conversation_message_index, string $previous_message_map, array $dynamic_row):array {

		// обьявляем изначально пустой
		$previous_message = [];

		// если передан previous_message_map
		if ($previous_message_map != "") {

			$block_row        = Domain_Conversation_Entity_Message_Block_Get::getBlockRow($conversation_map, $previous_message_map, $dynamic_row);
			$previous_message = Domain_Conversation_Entity_Message_Block_Message::get($previous_message_map, $block_row);
		}

		return Type_Conversation_LeftMenu::updateLastMessageOnMessageHide($user_id, $conversation_map, $conversation_message_index, $previous_message);
	}
}