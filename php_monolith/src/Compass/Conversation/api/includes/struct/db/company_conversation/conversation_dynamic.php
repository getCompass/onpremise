<?php

namespace Compass\Conversation;

/**
 * класс-структура для таблицы company_conversation.conversation_dynamic
 */
class Struct_Db_CompanyConversation_ConversationDynamic {

	/**
	 * @param string                                                             $conversation_map
	 * @param int                                                                $is_locked
	 * @param int                                                                $last_block_id
	 * @param int                                                                $start_block_id
	 * @param int                                                                $total_message_count
	 * @param int                                                                $total_action_count
	 * @param int                                                                $file_count
	 * @param int                                                                $image_count
	 * @param int                                                                $video_count
	 * @param int                                                                $created_at
	 * @param int                                                                $updated_at
	 * @param int                                                                $messages_updated_at
	 * @param int                                                                $reactions_updated_at
	 * @param int                                                                $threads_updated_at
	 * @param int                                                                $messages_updated_version
	 * @param int                                                                $reactions_updated_version
	 * @param int                                                                $threads_updated_version
	 * @param ?Struct_Db_CompanyConversation_ConversationDynamic_LastReadMessage $last_read_message
	 * @param array                                                              $user_mute_info
	 * @param array                                                              $user_clear_info
	 * @param array                                                              $user_file_clear_info
	 * @param array                                                              $conversation_clear_info
	 */
	public function __construct(
		public string                                                             $conversation_map,
		public int                                                                $is_locked,
		public int                                                                $last_block_id,
		public int                                                                $start_block_id,
		public int                                                                $total_message_count,
		public int                                                                $total_action_count,
		public int                                                                $file_count,
		public int                                                                $image_count,
		public int                                                                $video_count,
		public int                                                                $created_at,
		public int                                                                $updated_at,
		public int                                                                $messages_updated_at,
		public int                                                                $reactions_updated_at,
		public int                                                                $threads_updated_at,
		public int                                                                $messages_updated_version,
		public int                                                                $reactions_updated_version,
		public int                                                                $threads_updated_version,
		public ?Struct_Db_CompanyConversation_ConversationDynamic_LastReadMessage $last_read_message,
		public array                                                              $user_mute_info,
		public array                                                              $user_clear_info,
		public array                                                              $user_file_clear_info,
		public array                                                              $conversation_clear_info
	) {

	}

	/**
	 * Построить объект из массива
	 *
	 * @param array $array
	 *
	 * @return self
	 */
	public static function fromArray(array $array):self {

		return new self(
			$array["conversation_map"],
			$array["is_locked"],
			$array["last_block_id"],
			$array["start_block_id"],
			$array["total_message_count"],
			$array["total_action_count"],
			$array["file_count"],
			$array["image_count"],
			$array["video_count"],
			$array["created_at"],
			$array["updated_at"],
			$array["messages_updated_at"],
			$array["reactions_updated_at"],
			$array["threads_updated_at"],
			$array["messages_updated_version"],
			$array["reactions_updated_version"],
			$array["threads_updated_version"],
			Struct_Db_CompanyConversation_ConversationDynamic_LastReadMessage::fromArray($array["last_read_message"] ?? []),
			$array["user_mute_info"],
			$array["user_clear_info"],
			$array["user_file_clear_info"],
			$array["conversation_clear_info"]
		);
	}
}