<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс-структура для файлов чата
 */
class Struct_Conversation_File {

	/**
	 * @param string   $file_uuid
	 * @param int|null $row_id
	 * @param string   $conversation_map
	 * @param string   $file_map
	 * @param int      $user_id
	 * @param int      $is_deleted
	 * @param int      $file_type
	 * @param int      $parent_type
	 * @param string   $parent_map
	 * @param int      $conversation_message_created_at
	 * @param int      $created_at
	 * @param int      $updated_at
	 * @param string   $parent_message_map
	 * @param string   $conversation_message_map
	 * @param array    $extra
	 */
	protected function __construct(
		public string   $file_uuid,
		public int|null $row_id,
		public string   $conversation_map,
		public string   $file_map,
		public int      $file_type,
		public int      $parent_type,
		public string   $parent_map,
		public int      $conversation_message_created_at,
		public int      $is_deleted,
		public int      $user_id,
		public int      $created_at,
		public int      $updated_at,
		public string   $parent_message_map,
		public string   $conversation_message_map,
		public array    $extra,
	) {

	}

	/**
	 * Сформировать структуру файла
	 *
	 * @param Struct_Db_CompanyConversation_ConversationFile $row
	 *
	 * @return Struct_Conversation_File
	 * @throws ParseFatalException
	 * @throws \cs_UnpackHasFailed
	 */
	public static function fromRow(Struct_Db_CompanyConversation_ConversationFile $row):self {

		$parent_map = match ($row->parent_type) {

			Domain_Conversation_Entity_File_Main::PARENT_TYPE_CONVERSATION => \CompassApp\Pack\Message\Conversation::getConversationMap($row->parent_message_map),
			Domain_Conversation_Entity_File_Main::PARENT_TYPE_THREAD       => \CompassApp\Pack\Message\Thread::getThreadMap($row->parent_message_map),
			default                                                        => throw new ParseFatalException("unknown parent type"),
		};

		return new self(
			$row->file_uuid,
			$row->row_id,
			$row->conversation_map,
			$row->file_map,
			$row->file_type,
			$row->parent_type,
			$parent_map,
			$row->conversation_message_created_at,
			$row->is_deleted,
			$row->user_id,
			$row->created_at,
			$row->updated_at,
			$row->parent_message_map,
			$row->conversation_message_map,
			$row->extra
		);
	}
}