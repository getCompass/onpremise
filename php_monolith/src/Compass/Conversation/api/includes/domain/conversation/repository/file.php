<?php

namespace Compass\Conversation;

/**
 * Класс-репозиторий для работы с файлами чата
 */
class Domain_Conversation_Repository_File {

	/**
	 * Получить отсортированный список файлов для пользователя
	 *
	 * @param string $conversation_map
	 * @param array  $type_list
	 * @param array  $parent_type_list
	 * @param int    $user_clear_until_at
	 * @param int    $count
	 * @param int    $below_id
	 * @param int    $user_id
	 *
	 * @return Struct_Conversation_File[]
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \cs_UnpackHasFailed
	 */
	public static function getSortedListByUserId(string $conversation_map, array $type_list, array $parent_type_list, int $user_clear_until_at, int $count,
								   int $below_id, int $user_id):array {

		$conversation_file_list = Gateway_Db_CompanyConversation_ConversationFile::getSortedListByUserId(
			$conversation_map, $type_list, $parent_type_list, $user_clear_until_at, $count, $below_id, $user_id);

		return array_map(
			static fn(Struct_Db_CompanyConversation_ConversationFile $conversation_file) => Struct_Conversation_File::fromRow($conversation_file),
		$conversation_file_list);
	}

	/**
	 * Получить отсортированный список файлов
	 *
	 * @param string $conversation_map
	 * @param array  $type_list
	 * @param array  $parent_type_list
	 * @param int    $user_clear_until_at
	 * @param int    $count
	 * @param int    $below_id
	 *
	 * @return Struct_Conversation_File[]
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \cs_UnpackHasFailed
	 */
	public static function getSortedList(string $conversation_map, array $type_list, array $parent_type_list, int $user_clear_until_at, int $count,
							 int $below_id):array {

		$conversation_file_list = Gateway_Db_CompanyConversation_ConversationFile::getSortedList(
			$conversation_map, $type_list, $parent_type_list, $user_clear_until_at, $count, $below_id);

		return array_map(
			static fn(Struct_Db_CompanyConversation_ConversationFile $conversation_file) => Struct_Conversation_File::fromRow($conversation_file),
			$conversation_file_list);
	}

}
