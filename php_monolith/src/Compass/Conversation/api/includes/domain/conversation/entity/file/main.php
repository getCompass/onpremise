<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для работы с прикрепленными к диалогу файлами, например при отправке сообщения с файлом
 */
class Domain_Conversation_Entity_File_Main {

	// соотношение type (присылаемым клиентом) и file_type
	public const AVAILABLE_TYPE_LIST = [
		self::_ALL_FILES      => [FILE_TYPE_DEFAULT, FILE_TYPE_IMAGE, FILE_TYPE_VIDEO, FILE_TYPE_AUDIO, FILE_TYPE_DOCUMENT, FILE_TYPE_ARCHIVE],
		self::_MEDIA_FILES    => [FILE_TYPE_IMAGE, FILE_TYPE_VIDEO, FILE_TYPE_AUDIO],
		self::_ARCHIVE_FILES  => [FILE_TYPE_ARCHIVE],
		self::_DOCUMENT_FILES => [FILE_TYPE_DOCUMENT],
		self::_OTHER_FILES    => [FILE_TYPE_DEFAULT],
		self::_IMAGES         => [FILE_TYPE_IMAGE],
		self::_VIDEO          => [FILE_TYPE_VIDEO],
	];

	/** @var array типы файлов, принимаемых с клиента */
	public const FILE_CLIENT_SCHEMA = [
		FILE_TYPE_IMAGE    => "image",
		FILE_TYPE_VIDEO    => "video",
		FILE_TYPE_AUDIO    => "audio",
		FILE_TYPE_ARCHIVE  => "archive",
		FILE_TYPE_DOCUMENT => "document",
		FILE_TYPE_DEFAULT  => "other",
		FILE_TYPE_VOICE    => "voice",
	];

	// константы для клиентского type
	protected const _ALL_FILES      = 0;
	protected const _MEDIA_FILES    = 1;
	protected const _ARCHIVE_FILES  = 2;
	protected const _DOCUMENT_FILES = 3;
	protected const _OTHER_FILES    = 4;
	protected const _IMAGES         = 5;
	protected const _VIDEO          = 6;

	public const PARENT_TYPE_CONVERSATION = 0;
	public const PARENT_TYPE_THREAD       = 1;

	public const PARENT_TYPE_TO_STRING_SCHEMA = [
		self::PARENT_TYPE_CONVERSATION => "conversation",
		self::PARENT_TYPE_THREAD       => "thread",
	];

	// записывает массив в базу
	public static function addFileList(array $insert_message_map_list):void {

		Gateway_Db_CompanyConversation_ConversationFile::insertArray($insert_message_map_list);
	}

	// проверяет, доступный ли type передал клиент
	public static function isTypeAvailable(int $type):bool {

		// проверяем на сущетсование переданного типа
		if (!isset(self::AVAILABLE_TYPE_LIST[$type])) {
			return false;
		}

		return true;
	}

	// прикрепляем файл за диалогом
	public static function addFile(Struct_Db_CompanyConversation_ConversationFile $file):void {

		Gateway_Db_CompanyConversation_ConversationFile::insert($file);
	}

	// прикрепляем файл за диалогом
	public static function createStructForFileFromConversation(string $conversation_map, string $file_map, string $file_uuid, int $created_at, string $message_map, int $user_id):Struct_Db_CompanyConversation_ConversationFile {

		$parent_type = self::PARENT_TYPE_CONVERSATION;
		return self::_createFileStruct($file_uuid, $conversation_map, $file_map, $user_id, $parent_type, $created_at, $message_map, $message_map, []);
	}

	// прикрепляем файл за тредом
	public static function createStructForFileFromThread(string $conversation_map, string $file_map, string $file_uuid, int $conversation_message_created_at, string $parent_message_map, string $conversation_message_map, int $user_id):Struct_Db_CompanyConversation_ConversationFile {

		$parent_type = self::PARENT_TYPE_THREAD;
		return self::_createFileStruct($file_uuid, $conversation_map, $file_map, $user_id, $parent_type, $conversation_message_created_at, $parent_message_map, $conversation_message_map, []);
	}

	// создаем insert для вставки в базу с файлами
	// @long
	protected static function _createFileStruct(
		string $file_uuid,
		string $conversation_map,
		string $file_map,
		int    $user_id,
		int    $parent_type,
		int    $conversation_message_created_at,
		string $parent_message_map,
		string $conversation_message_map,
		array  $extra = [],
	):Struct_Db_CompanyConversation_ConversationFile {

		// создаем запись
		return new Struct_Db_CompanyConversation_ConversationFile(
			$file_uuid,
			null,
			$conversation_map,
			$file_map,
			\CompassApp\Pack\File::getFileType($file_map),
			$parent_type,
			$conversation_message_created_at,
			0,
			$user_id,
			time(),
			0,
			$parent_message_map,
			$conversation_message_map,
			$extra
		);
	}

	// пометить список файлов удаленными по списку родительских сообщений
	public static function setDeletedList(array $file_uuid_list):void {

		Gateway_Db_CompanyConversation_ConversationFile::setDeletedListByParentMapList($file_uuid_list);
	}

	// пометить файлы удаленными по списку сообщений
	public static function setDeletedListByMessageMapList(string $conversation_map, array $message_map_list, int $count):void {

		Gateway_Db_CompanyConversation_ConversationFile::setDeletedListByConversationMessageMapList($conversation_map, $message_map_list, $count);
	}

	/**
	 * получаем файлы
	 *
	 * @param string $conversation_map
	 * @param int    $user_id
	 * @param bool   $only_my_files
	 * @param bool   $is_thread_file_enabled
	 * @param int    $type
	 * @param int    $user_clear_until_at
	 * @param int    $count
	 * @param int    $offset
	 *
	 * @return array
	 *
	 * @throws ParseFatalException
	 */
	public static function getFilesList(string $conversation_map, int $user_id, bool $only_my_files, bool $is_thread_file_enabled, int $type, int $user_clear_until_at, int $count, int $offset = 0):array {

		// получаем массив нужных
		$file_type_in = self::_getFileTypes($type);

		$parent_type_list = [self::PARENT_TYPE_CONVERSATION];
		if ($is_thread_file_enabled) {
			$parent_type_list[] = self::PARENT_TYPE_THREAD;
		}

		if ($only_my_files) {
			$file_list = Gateway_Db_CompanyConversation_ConversationFile::getUserFiles($conversation_map, $user_id, $file_type_in, $parent_type_list, $user_clear_until_at, $count, $offset);
		} else {
			$file_list = Gateway_Db_CompanyConversation_ConversationFile::getList($conversation_map, $file_type_in, $parent_type_list, $user_clear_until_at, $count, $offset);
		}

		$output = [];
		foreach ($file_list as $file_row) {

			// если пользователь скрыл файл -> пропускаем
			if (Domain_Conversation_Entity_File_Extra::isHiddenBy($file_row->extra, $user_id)) {
				continue;
			}
			$output[] = $file_row;
		}
		return $output;
	}

	/**
	 * Получаем файлы
	 *
	 * @param int    $user_id
	 * @param string $conversation_map
	 * @param int    $count
	 * @param int    $below_id
	 * @param bool   $filter_self_only
	 * @param array  $type_list
	 * @param int    $user_clear_until_at
	 * @param array  $parent_type_list
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws \cs_UnpackHasFailed
	 */
	public static function getFilesListWitIdSort(int $user_id, string $conversation_map, int $count, int $below_id, bool $filter_self_only, array $type_list, int $user_clear_until_at, array $parent_type_list):array {

		// если ничего не передали - просто берем все доступные для клиента типы
		if ($type_list === []) {
			$type_list = array_keys(self::FILE_CLIENT_SCHEMA);
		}

		// если ничего не передали в типах родителей - просто берем все доступные для клиента типы
		if ($parent_type_list === []) {
			$parent_type_list = array_keys(self::PARENT_TYPE_TO_STRING_SCHEMA);
		}

		$conversation_file_list = $filter_self_only
			? Domain_Conversation_Repository_File::getSortedListByUserId(
				$conversation_map, $type_list, $parent_type_list, $user_clear_until_at, $count + 1, $below_id, $user_id)

			: Domain_Conversation_Repository_File::getSortedList(
				$conversation_map, $type_list, $parent_type_list, $user_clear_until_at, $count + 1, $below_id);

		// следующий id, который должен получить клиент, обязательно должен быть получен ДО фильтрации
		// чтобы в следующем запросе клиент не запросил те же превью
		$next_below_id          = (count($conversation_file_list) > $count) ? end($conversation_file_list)->row_id : 0;
		$conversation_file_list = array_slice($conversation_file_list, 0, $count);

		return [self::_filterFiles($user_id, $conversation_file_list), $next_below_id];
	}

	/**
	 * Фильтруем полученные файлы
	 *
	 * @param int                        $user_id
	 * @param Struct_Conversation_File[] $conversation_file_list
	 *
	 * @return Struct_Conversation_File[]
	 */
	protected static function _filterFiles(int $user_id, array $conversation_file_list):array {

		$output_file_list                  = [];
		$not_hidden_conversation_file_list = [];

		foreach ($conversation_file_list as $file_row) {

			// если пользователь скрыл файл -> пропускаем
			if (!Domain_Conversation_Entity_File_Extra::isHiddenBy($file_row->extra, $user_id)) {
				$not_hidden_conversation_file_list[] = $file_row;
			}
		}

		if ($not_hidden_conversation_file_list === []) {
			return [];
		}

		// собираем message_map отобранных файлов
		$message_map_list = [];
		foreach ($not_hidden_conversation_file_list as $v) {
			$message_map_list[$v->conversation_message_map] = true;
		}

		// оставляем только уникальные ключи
		$message_map_list        = array_keys($message_map_list);
		$hidden_message_map_list = Gateway_Db_CompanyConversation_MessageUserHiddenRel::getMessageMapList($user_id, $message_map_list);
		$hidden_message_map_list = array_flip($hidden_message_map_list);

		foreach ($not_hidden_conversation_file_list as $conversation_file) {

			if (!isset($hidden_message_map_list[$conversation_file->conversation_message_map])) {
				$output_file_list[] = $conversation_file;
			}
		}

		return $output_file_list;
	}

	// скрываем файл для пользователя
	public static function doHideThreadFileForUser(string $file_uuid, int $user_id):bool {

		// открываем транзакцию
		Gateway_Db_CompanyConversation_ConversationFile::beginTransaction();
		$file_row = Gateway_Db_CompanyConversation_ConversationFile::getForUpdate($file_uuid);

		// если пользователь уже скрыл файл -> пропускаем
		if (Domain_Conversation_Entity_File_Extra::isHiddenBy($file_row->extra, $user_id)) {

			Gateway_Db_CompanyConversation_ConversationFile::rollback();
			return false;
		}

		// помечаем файл скрытым для пользователя
		$extra = Domain_Conversation_Entity_File_Extra::addToHiddenBy($file_row->extra, $user_id);

		// обновляем список скрывших файл в таблице диалога
		Gateway_Db_CompanyConversation_ConversationFile::set($file_row->file_uuid, [
			"extra"      => $extra,
			"updated_at" => time(),
		]);

		Gateway_Db_CompanyConversation_ConversationFile::commitTransaction();
		return true;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// возвращает file_types для type, присылаемого клиентом
	protected static function _getFileTypes(int $type):array {

		// проверяем на существование переданного типа
		if (!isset(self::AVAILABLE_TYPE_LIST[$type])) {
			throw new ParseFatalException("not available type. Developer did not check this before");
		}

		return self::AVAILABLE_TYPE_LIST[$type];
	}
}