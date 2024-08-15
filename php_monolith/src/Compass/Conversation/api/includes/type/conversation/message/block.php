<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс, через который происходит взаимодействие с горячими блоками сообщений
 */
class Type_Conversation_Message_Block {

	public const    MESSAGE_PER_BLOCK_LIMIT = 30;           // лимит количества сообщений в одном блоке

	/**
	 * Добавляет пул новых сообщений в последний блок.
	 * Под пулом подразумевается массив из нарезанных частей одного сообщения, а не самостоятельные сообщения.
	 *
	 * @param array $raw_message_list список еще несохраненных частей сообщения
	 * @param array $dynamic_row      запись для целевого диалога
	 *
	 * @return array
	 * @throws \returnException
	 * @throws \returnException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 */
	public static function addMessageList(string $conversation_map, array $raw_message_list, array $dynamic_row):array {

		// получаем последний горячий блок для диалога
		$last_hot_block_id = self::_getLastHotBlockIdOnAddMessage($conversation_map, $dynamic_row);

		$current_time = time();
		Gateway_Db_CompanyConversation_MessageBlock::beginTransaction();

		$chunk_raw_message_list = array_chunk($raw_message_list, self::MESSAGE_PER_BLOCK_LIMIT);

		// разделенные на чанки сообщения впишем в новые блоки
		$message_list = [];
		foreach ($chunk_raw_message_list as $message_list_for_block) {

			// ищем подходящий горячий блок, в который можно добавить сообщения
			$block_row = self::_getValidBlockOnAddMessage($conversation_map, $last_hot_block_id, count($message_list_for_block), $current_time);
			$block_id  = $block_row["block_id"];

			// обновляем dynamic в зависимости от типа сообщения и его содержимого, все сообщения в списке, кроме последнего имеют тип текст
			self::_doUpdateDynamicRowOnAddMessage($conversation_map, $message_list_for_block, $current_time);

			// добавляем новые сообщения в блок
			[$new_message_list, $dynamic_row] = self::_appendNewMessageListIntoBlock($conversation_map, $message_list_for_block, $block_id, $block_row, $current_time);
			$message_list      = array_merge($message_list, $new_message_list);
			$last_hot_block_id = $block_id;
		}

		Gateway_Db_CompanyConversation_MessageBlock::commitTransaction();

		return [$message_list, $dynamic_row];
	}

	/**
	 * Возвращает ид последнего горячего блока.
	 *
	 **/
	protected static function _getLastHotBlockIdOnAddMessage(string $conversation_map, array $dynamic_row):int {

		// выбираем горячий блок, если такого нет, то создаем
		$block_id = $dynamic_row["last_block_id"];
		if ($block_id == $dynamic_row["start_block_id"]) {
			$block_id = self::_createNextHotBlock($conversation_map, $block_id);
		}

		return $block_id;
	}

	/**
	 * Возвращает подходящий блок за запись при создании нового сообщения.
	 *
	 * @throws \returnException
	 */
	protected static function _getValidBlockOnAddMessage(string $conversation_map, int $block_id, int $message_count, int $current_time):array {

		// лочим на запись
		$block_row = Gateway_Db_CompanyConversation_MessageBlock::getForUpdate($conversation_map, $block_id);

		// смотрим, влезут ли все наши сообщеньки сюда
		if ($block_row["message_count"] + $message_count > self::MESSAGE_PER_BLOCK_LIMIT) {

			// если не влезают, то закрываем текущий блок, создаем новый блок для записи и лочим уже его
			Gateway_Db_CompanyConversation_MessageBlock::set($conversation_map, $block_id, ["closed_at" => $current_time, "updated_at" => $current_time,]);
			Gateway_Db_CompanyConversation_MessageBlock::commitTransaction();

			$block_id = self::_createNextHotBlock($conversation_map, $block_id);

			Gateway_Db_CompanyConversation_MessageBlock::beginTransaction();
			$block_row = Gateway_Db_CompanyConversation_MessageBlock::getForUpdate($conversation_map, $block_id);
		}

		return $block_row;
	}

	// обновляем dynamic в зависимости от типа сообщения и его содержимого
	protected static function _doUpdateDynamicRowOnAddMessage(string $conversation_map, array $message_list, int $add_message_time):void {

		$file_counter = [
			"file"  => 0,
			"image" => 0,
			"video" => 0,
		];

		foreach ($message_list as $v) {

			[$hidden_file_map_list] = Type_Conversation_Message_Main::getHandler($v)::getFileMapAndFileUuidListFromAnyMessage($v);
			$file_counter = self::_getFileCounter($hidden_file_map_list, $file_counter);
		}

		$set = self::_prepareUpdateDynamicRowArray($file_counter, $add_message_time, count($message_list));
		Domain_Conversation_Entity_Dynamic::set($conversation_map, $set);
	}

	// функция подготавливает массив для обновления записи в таблице dynamic сразу после добавления сообщения
	protected static function _prepareUpdateDynamicRowArray(array $file_counter, int $add_message_time, int $message_count):array {

		$set = [
			"total_message_count"      => "total_message_count + $message_count",
			"messages_updated_at"      => $add_message_time,
			"messages_updated_version" => "messages_updated_version + 1",
			"updated_at"               => $add_message_time,
		];

		if ($file_counter["file"] > 0) {
			$set["file_count"] = "file_count + " . $file_counter["file"];
		}

		if ($file_counter["image"] > 0) {
			$set["image_count"] = "image_count + " . $file_counter["image"];
		}

		if ($file_counter["video"] > 0) {
			$set["video_count"] = "video_count + " . $file_counter["video"];
		}

		return $set;
	}

	// добавляем в блок пул новых сообщений
	// под пулом подразумевается массив из нарезанных частей одного сообщения, а не самостоятельные сообщения
	protected static function _appendNewMessageListIntoBlock(string $conversation_map, array $raw_message_list, int $block_id, array $block_row, int $add_message_time):array {

		// данные для записи в таблицу
		$dynamic_row                = Domain_Conversation_Entity_Dynamic::get($conversation_map);
		$conversation_message_index = $dynamic_row["total_message_count"] - count($raw_message_list);
		$block_message_index        = $block_row["message_count"];

		// данные о добавленных сообщениях
		$message_list     = [];
		$inserted_counter = 0;
		foreach ($raw_message_list as $v) {

			// готовим сообщение к вставке
			$message_map    = \CompassApp\Pack\Message\Conversation::doPack($conversation_map, $block_id, ++$block_message_index, ++$conversation_message_index);
			$message        = Type_Conversation_Message_Main::getHandler($v)::prepareForInsert($v, $message_map);
			$message_list[] = $message;

			// добавляем новое сообщение в блок
			$block_row["data"][$message_map] = $message;
			$inserted_counter++;
		}

		self::_setBlockData($conversation_map, $block_id, $block_row, $inserted_counter, $add_message_time);

		return [$message_list, $dynamic_row];
	}

	// Пишет данные блока в базу
	protected static function _setBlockData(string $conversation_map, int $block_id, array $block_row, int $inserted_counter, int $add_message_time):void {

		Gateway_Db_CompanyConversation_MessageBlock::set($conversation_map, $block_id, [
			"data"          => $block_row["data"],
			"message_count" => $block_row["message_count"] + $inserted_counter,
			"updated_at"    => $add_message_time,
		]);
	}

	/**
	 * Отмечает сообщение прочитанным.
	 *
	 * @throws cs_Message_IsDeleted
	 * @throws cs_Message_IsNotAllowToMarkAsRead
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function markMessageAsRead(string $message_map, int $user_id):array {

		// получаем map диалога и идентификатор блока сообщения
		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);
		$block_id         = \CompassApp\Pack\Message\Conversation::getBlockId($message_map);

		try {

			/** начинаем транзакцию **/
			Gateway_Db_CompanyConversation_MessageBlock::beginTransaction();

			// получаем блок на обновление; получаем сообщение из блока
			$block_row = Gateway_Db_CompanyConversation_MessageBlock::getForUpdate($conversation_map, $block_id);
			$message   = Domain_Conversation_Entity_Message_Block_Message::get($message_map, $block_row);

			// проверяем, что сообщение читаемо пользователем
			self::_throwIfUserNotAllowedToMarkMessageAsRead($message);

			// обновляем данные
			$message["data"] = self::_prepareMessageDataOnMarkAsRead($user_id, $message["data"]);
			$block_row       = Domain_Conversation_Entity_Message_Block_Message::set($message_map, $message, $block_row);

			// пишем новые данные
			Gateway_Db_CompanyConversation_MessageBlock::set($conversation_map, $block_id, [
				"data"       => $block_row["data"],
				"updated_at" => time(),
			]);

			Gateway_Db_CompanyConversation_MessageBlock::commitTransaction();
			/** заканчиваем транзакцию **/
		} catch (cs_Message_IsDeleted|cs_Message_IsNotAllowToMarkAsRead $ex) {

			//  сообщение удалено
			Gateway_Db_CompanyConversation_MessageBlock::rollback();
			throw $ex;
		}

		return $message;
	}

	/**
	 * Добавляет в список прочитанности элемент с прочитавшим пользователем и временем прочтения.
	 *
	 */
	protected static function _prepareMessageDataOnMarkAsRead(int $user_id, array $data):array {

		// список тех, кто прочитал
		$data["read_at_by_list"] = $data["read_at_by_list"] ?? [];

		// смотрим, есть ли наш пользователь в списке
		foreach ($data["read_at_by_list"] as $k => $v) {

			if (intval($v["user_id"]) === $user_id) {

				// если есть, то обновляем запись
				$data["read_at_by_list"][$k]["read_at"] = time();
				return $data;
			}
		}

		// если пользователя в списке нет, то добавляем
		$data["read_at_by_list"][] = [
			"user_id" => $user_id,
			"read_at" => time(),
		];

		return $data;
	}

	/**
	 * Выбрасывает исключение, если сообщение не может быть отмечено прочитанным.
	 *
	 * @throws cs_Message_IsDeleted
	 * @throws cs_Message_IsNotAllowToMarkAsRead
	 * @throws \parseException
	 */
	protected static function _throwIfUserNotAllowedToMarkMessageAsRead(array $message):void {

		// проверяем если сообщение было удалено
		if (Type_Conversation_Message_Main::getHandler($message)::isMessageDeleted($message)) {
			throw new cs_Message_IsDeleted();
		}

		// проверяем, что можно пометить прочитанным
		if (!Type_Conversation_Message_Main::getHandler($message)::isAllowedForMarkAsRead($message)) {
			throw new cs_Message_IsNotAllowToMarkAsRead();
		}
	}

	// изменяем текст существующего сообщения
	// @long - множество действий
	public static function editMessageText(string $message_map, int $user_id, int $user_role, string $text, array $mention_user_id_list, bool $is_force_edit = false):array {

		// получаем map диалога и идентификатор блока сообщения
		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);
		$block_id         = \CompassApp\Pack\Message\Conversation::getBlockId($message_map);

		// открываем транзкцию; получаем блок на обновление; получаем сообщение из блока
		Gateway_Db_CompanyConversation_MessageBlock::beginTransaction();
		$block_row = Gateway_Db_CompanyConversation_MessageBlock::getForUpdate($conversation_map, $block_id);
		$message   = Domain_Conversation_Entity_Message_Block_Message::get($message_map, $block_row);

		// проверяем, позволяет ли данное сообщение вставить пустой текст при редактировании
		if ($text === "") {

			if (!Type_Conversation_Message_Main::getHandler($message)::isEditEmptyText($message)) {

				Gateway_Db_CompanyConversation_MessageBlock::rollback();
				throw new cs_Message_IsEmptyText();
			}
		}

		self::_throwIfUserCantEditMessage($message, $user_id, $user_role, $is_force_edit);
		$old_mention_user_id_list    = Type_Conversation_Message_Main::getHandler($message)::getMentionedUsers($message);
		$diff_mentioned_user_id_list = array_diff($mention_user_id_list, $old_mention_user_id_list);

		// удаляем превью, если есть ссылка
		self::_deletePreviewIfLinkExists($message);

		// изменяем текст сообщения и убираем всё лишнее из сообщения
		$edited_message = Type_Conversation_Message_Main::getHandler($message)::editMessageText($message, $text, $mention_user_id_list);

		// обновляем блок с сообщением
		$block_row["data"][$message_map] = $edited_message;
		Gateway_Db_CompanyConversation_MessageBlock::set($conversation_map, $block_id, [
			"data"       => $block_row["data"],
			"updated_at" => time(),
		]);

		$dynamic                           = Gateway_Db_CompanyConversation_ConversationDynamic::getForUpdate($conversation_map);
		$dynamic->messages_updated_version = $dynamic->messages_updated_version + 1;
		$dynamic->messages_updated_at      = time();
		$dynamic->updated_at               = time();

		// обновляем временную метку и версию обновления сообщений в диалоге
		$set = [
			"messages_updated_version" => $dynamic->messages_updated_version,
			"messages_updated_at"      => $dynamic->messages_updated_at,
			"updated_at"               => $dynamic->updated_at,
		];
		Domain_Conversation_Entity_Dynamic::set($conversation_map, $set);

		Gateway_Db_CompanyConversation_MessageBlock::commitTransaction();

		return [$message, $edited_message, $diff_mentioned_user_id_list, $dynamic];
	}

	// проверяем, что пользователь может редактировать сообщение
	protected static function _throwIfUserCantEditMessage(array $message, int $user_id, int $user_role, bool $is_force_edit = false):void {

		$is_new_errors = Type_System_Legacy::isNewErrors();

		// проверяем если сообщение было удалено
		if (Type_Conversation_Message_Main::getHandler($message)::isMessageDeleted($message)) {

			Gateway_Db_CompanyConversation_MessageBlock::rollback();
			throw new cs_Message_IsDeleted();
		}

		// проверяем что пользователь отправитель
		if (Type_Conversation_Message_Main::getHandler($message)::getSenderUserId($message) != $user_id) {

			Gateway_Db_CompanyConversation_MessageBlock::rollback();
			throw new cs_Message_UserHaveNotPermission();
		}

		// проверяем флаг/тип сообщения
		if (!Type_Conversation_Message_Main::getHandler($message)::isFlagsAllowToEdit($message, $user_id)) {

			Gateway_Db_CompanyConversation_MessageBlock::rollback();
			throw new cs_Message_IsNotAllowForEdit();
		}

		// если сообщение имеет additional поля рабочего времени
		$is_worked_hours_message = Type_Conversation_Message_Main::getHandler($message)::isContainAdditionalWorkedHours($message);

		// если дальнейшие проверки не нужны
		if ($is_force_edit === true) {
			return;
		}

		// проверяем что пользователь не админ/создатель группы и время редактирования сообщения истекло
		if (!self::_isUserAdmin($user_role) && !Type_Conversation_Message_Main::getHandler($message)::isTimeAllowToEdit($message, $is_worked_hours_message)) {

			Gateway_Db_CompanyConversation_MessageBlock::rollback();

			if ($is_new_errors) {
				throw new cs_Message_TimeIsOver();
			}

			throw new cs_Message_IsNotAllowForEdit();
		}
	}

	/**
	 * Удаляем превью, если существует ссылка в сообщении
	 *
	 * @param array $message
	 *
	 * @return void
	 * @throws \parseException
	 */
	protected static function _deletePreviewIfLinkExists(array $message):void {

		// если какие то ссылки были прикреплены к сообщению - удаляем превью
		if (Type_Conversation_Message_Main::getHandler($message)::isAttachedLinkList($message)) {

			Domain_Conversation_Entity_Preview_Main::setDeletedList(
				Domain_Conversation_Entity_Preview_Main::PARENT_TYPE_CONVERSATION,
				[Type_Conversation_Message_Main::getHandler($message)::getMessageMap($message)]
			);
		}
	}

	// удаляем сообщения
	public static function deleteMessageList(array $message_map_list, string $conversation_map, int $conversation_type, int $block_id, int $user_id, int $user_role, bool $is_force_delete = false):array {

		Gateway_Db_CompanyConversation_MessageBlock::beginTransaction();

		// получаем блок на обновление и сообщение из блока
		$block_row = Gateway_Db_CompanyConversation_MessageBlock::getForUpdate($conversation_map, $block_id);
		$block_row = self::_setMessageDeletedForDeleteMessageList($conversation_type, $message_map_list, $block_row, $user_id, $user_role, $is_force_delete
		);

		Gateway_Db_CompanyConversation_MessageBlock::set($conversation_map, $block_id, [
			"data"       => $block_row["data"],
			"updated_at" => time(),
		]);

		Gateway_Db_CompanyConversation_MessageBlock::commitTransaction();
		return $block_row;
	}

	// помечаем сообщение удаленными
	protected static function _setMessageDeletedForDeleteMessageList(int   $conversation_type,
											     array $message_map_list, array $block_row, int $user_id, int $user_role,
											     bool  $is_force_delete):array {

		$is_group = Type_Conversation_Meta::isSubtypeOfGroup($conversation_type);

		// определяем является ли пользователь админом/создателем группы
		// ($is_force_delete - если нужно удалить сообщение, несмотря на роль пользователя)
		$is_admin = $is_force_delete === true || self::_isUserAdmin($user_role) === true;

		$message_list = [];
		foreach ($message_map_list as $v) {

			$message = Domain_Conversation_Entity_Message_Block_Message::get($v, $block_row);

			// проверяем, что роль пользователя позволяет удалить сообщение
			self::_throwIfUserRoleNotAllowToDeleteMessage($user_id, $message, $is_admin);

			$message_list[$v] = $message;
		}

		foreach ($message_list as $message_map => $message) {

			// делаем остальные проверки, может ли пользователь удалить сообщение
			self::_throwIfUserCantDeleteMessage($message, $user_id, $is_admin);

			// помечаем сообщение удаленным и добавляем thread map в массив, если сообщение с тредом
			$block_row["data"][$message_map] = Type_Conversation_Message_Main::getHandler($message)::setDeleted($message);
		}

		return $block_row;
	}

	// удаляет файлы после удалния сообщений
	// @long
	public static function onDeleteMessageListWithFile(string $conversation_map, array $message_list):Struct_Db_CompanyConversation_ConversationDynamic {

		$message_list_info = self::_getMessageListInfoForDelete($message_list);

		// получаем created_at последнего сообщения, так как удаляем мы все сообщения разом
		$message_created_at = Type_Conversation_Message_Main::getHandler(end($message_list))::getCreatedAt(end($message_list));

		$total_count = $message_list_info["file_count_list"]["image"] + $message_list_info["file_count_list"]["file"] + $message_list_info["file_count_list"]["video"];
		if ($total_count < 1) {

			Gateway_Db_CompanyConversation_ConversationDynamic::beginTransaction();
			$dynamic = Gateway_Db_CompanyConversation_ConversationDynamic::getForUpdate($conversation_map);

			$dynamic->updated_at               = time();
			$dynamic->messages_updated_version = $dynamic->messages_updated_version + 1;
			$dynamic->messages_updated_at      = time();

			$set = [
				"updated_at"               => $dynamic->updated_at,
				"messages_updated_version" => $dynamic->messages_updated_version,
				"messages_updated_at"      => $dynamic->messages_updated_at,
			];
			Gateway_Db_CompanyConversation_ConversationDynamic::set($conversation_map, $set);
			Gateway_Db_CompanyConversation_ConversationDynamic::commitTransaction();

			return $dynamic;
		}

		// изменяем значения кол-ва скрытых файлов и изображений для пользователя, если успели скрыть или очистить сообщение до его удаления
		$dynamic = Domain_Conversation_Entity_Dynamic::doChangeConversationFileCountInfo(
			$conversation_map,
			$message_list_info["hidden_message_user_list"],
			$message_list_info["file_count_list"]["file"],
			$message_list_info["file_count_list"]["image"],
			$message_list_info["file_count_list"]["video"],
			$message_created_at
		);

		Domain_Conversation_Entity_File_Main::setDeletedList($message_list_info["file_uuid_list"]);

		return $dynamic;
	}

	// получаем ифнормацию об удаляемых файлах и считаем кол-во
	protected static function _getMessageListInfoForDelete(array $message_list):array {

		$file_count_list          = [
			"image" => 0,
			"video" => 0,
			"file"  => 0,
		];
		$file_uuid_list           = [];
		$hidden_message_user_list = [];
		foreach ($message_list as $v) {

			[$file_count_list, $temp_file_uuid_list] = self::_considerFileCount($v, $file_count_list);

			$file_uuid_list = array_merge($file_uuid_list, $temp_file_uuid_list);

			// находим пользователей, которые скрыли файл и считаем общее кол-во скрытых файлов и изображений
			$hidden_message_user_list = array_merge($hidden_message_user_list, Type_Conversation_Message_Main::getHandler($v)::getHiddenByUserIdList($v));
		}
		$hidden_message_user_list = array_unique($hidden_message_user_list);

		return [
			"file_uuid_list"           => $file_uuid_list,
			"file_count_list"          => $file_count_list,
			"hidden_message_user_list" => $hidden_message_user_list,
		];
	}

	// считаем кол-во файлов
	protected static function _considerFileCount(array $message, array $file_count_list):array {

		// проверяем, если сообщение удалено, то возвращаем тип и считаем файлы
		if (Type_Conversation_Message_Main::getHandler($message)::getType($message) === CONVERSATION_MESSAGE_TYPE_DELETED) {
			$message["type"] = Type_Conversation_Message_Main::getHandler($message)::getOriginalType($message);
		}
		[$hidden_file_map_list, $hidden_file_uuid_list] = Type_Conversation_Message_Main::getHandler($message)::getFileMapAndFileUuidListFromAnyMessage($message);

		// получаем и считаем кол-во файлов и изображений, которые были удалены
		$file_count_list = self::_getFileCounter($hidden_file_map_list, $file_count_list);
		return [$file_count_list, $hidden_file_uuid_list];
	}

	// помечаем список сообщений как удаленные системой
	// работает только для диалогов типа CONVERSATION_TYPE_PUBLIC_DEFAULT
	// работает только для владельца этого диалога
	public static function setSystemDeletedMessageListInPublicConversation(int $conversation_type, int $user_id, int $user_role, array $message_map_list):array {

		// проверяем тип диалога
		if (!Type_Conversation_Meta::isSubtypeOfPublicGroup($conversation_type)) {
			throw new ParseFatalException(__METHOD__ . ": this method allowed only for CONVERSATION_TYPE_PUBLIC_DEFAULT");
		}

		// проверяем, что пользователец владелец
		if ($user_role != Type_Conversation_Meta_Users::ROLE_OWNER) {
			throw new ParseFatalException(__METHOD__ . ": this method allowed only for owner of conversation type public");
		}

		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map_list[0]);
		$block_id         = \CompassApp\Pack\Message\Conversation::getBlockId($message_map_list[0]);

		// получаем блок на обновление
		Gateway_Db_CompanyConversation_MessageBlock::beginTransaction();
		$block_row = Gateway_Db_CompanyConversation_MessageBlock::getForUpdate($conversation_map, $block_id);

		// помечаем сообщения удаленными системой
		foreach ($message_map_list as $message_map) {

			$message = Domain_Conversation_Entity_Message_Block_Message::get($message_map, $block_row);

			// делаем остальные проверки, может ли пользователь удалить сообщение
			self::_throwIfUserCantDeleteMessage($message, $user_id, false, true);

			$message   = Type_Conversation_Message_Main::getHandler($message)::setSystemDeleted($message);
			$block_row = Domain_Conversation_Entity_Message_Block_Message::set($message_map, $message, $block_row);
		}

		Gateway_Db_CompanyConversation_MessageBlock::set($conversation_map, $block_id, [
			"data"       => $block_row["data"],
			"updated_at" => time(),
		]);

		Gateway_Db_CompanyConversation_MessageBlock::commitTransaction();
		return $block_row;
	}

	// скрываем сообщение для пользователя
	public static function hideMessageList(array $message_map_list, int $user_id, string $conversation_map, int $block_id):array {

		// открываем транзкцию
		Gateway_Db_CompanyConversation_MessageBlock::beginTransaction();

		// получаем блок на обновление и сообщение из блока
		$block_row = Gateway_Db_CompanyConversation_MessageBlock::getForUpdate($conversation_map, $block_id);
		foreach ($message_map_list as $v) {

			$message = Domain_Conversation_Entity_Message_Block_Message::get($v, $block_row);

			// добавляем пользователя в список скрывших сообщение и обновляем блок с сообщением
			$block_row["data"][$v] = Type_Conversation_Message_Main::getHandler($message)::addToHiddenBy($message, $user_id);
		}
		Gateway_Db_CompanyConversation_MessageBlock::set($conversation_map, $block_id, [
			"data"       => $block_row["data"],
			"updated_at" => time(),
		]);

		// выполняем транзакцию
		Gateway_Db_CompanyConversation_MessageBlock::commitTransaction();
		return $block_row;
	}

	// соединяем два массива так, чтобы индексы сохранились
	public static function mergeBlockRowList(array $first_array, array $second_array):array {

		if (count($first_array) < 1 && count($second_array) < 1) {
			return [];
		}

		if (count($first_array) < 1) {
			return $second_array;
		}

		if (count($second_array) < 1) {
			return $first_array;
		}

		foreach ($second_array as $k => $v) {
			$first_array[$k] = $v;
		}

		return $first_array;
	}

	// получаем map последнего сообщения в блоке
	public static function getLastMessageMap(array $block_row_data, bool $for_left_menu = false):string {

		// обьявляем переменные
		$last_conversation_message_index = 0;
		$last_message_map                = "";

		// проходимся по всем блокам
		foreach ($block_row_data as $v) {

			// получаем message_map каждого сообщения и его индекс
			$message_map = Type_Conversation_Message_Main::getHandler($v)::getMessageMap($v);
			$temp        = \CompassApp\Pack\Message\Conversation::getConversationMessageIndex($message_map);

			// ищем индекс последнего сообщения
			if ($last_conversation_message_index < $temp) {

				if ($for_left_menu && !Type_Conversation_Message_Main::getHandler($v)::isNeedUpdateLeftMenu($v)) {
					continue;
				}
				$last_conversation_message_index = $temp;
				$last_message_map                = $message_map;
			}
		}

		// возвращаем message_map самого последнего сообщения
		return $last_message_map;
	}

	// меняем время написания сообщения
	public static function changeMessageCreatedAt(string $message_map, int $created_at):void {

		// получаем map диалога и идентификатор блока сообщения
		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);
		$block_id         = \CompassApp\Pack\Message\Conversation::getBlockId($message_map);

		// открываем транзкцию
		Gateway_Db_CompanyConversation_MessageBlock::beginTransaction();

		// получаем блок на обновление и сообщение из него
		$block_row = Gateway_Db_CompanyConversation_MessageBlock::getForUpdate($conversation_map, $block_id);
		$message   = Domain_Conversation_Entity_Message_Block_Message::get($message_map, $block_row);

		// меняем время написания сообщения
		$block_row["data"][$message_map] = Type_Conversation_Message_Main::getHandler($message)::changeCreatedAt($message, $created_at);
		Gateway_Db_CompanyConversation_MessageBlock::set($conversation_map, $block_id, [
			"data"       => $block_row["data"],
			"updated_at" => time(),
		]);

		Gateway_Db_CompanyConversation_MessageBlock::commitTransaction();
	}

	// ищем файлы в удаленных сообщениях, если находим уменьшаем общее количество файлов в диалоге
	public static function onDeleteMessageWithFile(string $conversation_map, array $message):void {

		// получаем список file_map файлов, которые были в удаленном сообщении, затем считаем количество скрытых файлов и изображений
		[$hidden_file_map_list, $hidden_file_uuid_list] = Type_Conversation_Message_Main::getHandler($message)::getFileMapAndFileUuidListFromAnyMessage($message);
		$hidden_file_count = self::_getFileCounter($hidden_file_map_list);

		$hidden_message_user_list = Type_Conversation_Message_Main::getHandler($message)::getHiddenByUserIdList($message);
		$message_created_at       = Type_Conversation_Message_Main::getHandler($message)::getCreatedAt($message);

		if ($hidden_file_count["image"] + $hidden_file_count["file"] + $hidden_file_count["video"] < 1) {
			return;
		}

		// изменяем значения скрытых для пользователей файлов и изображений, если успели скрыть или очистить сообщение до его удаления
		Domain_Conversation_Entity_Dynamic::doChangeConversationFileCountInfo(
			$conversation_map,
			$hidden_message_user_list,
			$hidden_file_count["file"],
			$hidden_file_count["image"],
			$hidden_file_count["video"],
			$message_created_at
		);
		Domain_Conversation_Entity_File_Main::setDeletedList($hidden_file_uuid_list);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Создаем следующий по счету горячий блок.
	 *
	 * @retun int
	 */
	protected static function _createNextHotBlock(string $conversation_map, int $previous_block_id):int {

		$new_block_id = $previous_block_id + 1;
		Gateway_Db_CompanyConversation_MessageBlock::insert($conversation_map, [
			"conversation_map" => $conversation_map,
			"block_id"         => $new_block_id,
			"message_count"    => 0,
			"created_at"       => time(),
			"updated_at"       => 0,
			"closed_at"        => 0,
			"data"             => [],
		]);

		// обновляем last_block_id в таблице dynamic
		Domain_Conversation_Entity_Dynamic::set($conversation_map, [
			"last_block_id" => $new_block_id,
			"updated_at"    => time(),
		]);

		return $new_block_id;
	}

	// проверяем, является ли пользователь админом
	protected static function _isUserAdmin(int $user_role):bool {

		return in_array($user_role, [Type_Conversation_Meta_Users::ROLE_ADMIN, Type_Conversation_Meta_Users::ROLE_OWNER]);
	}

	// проверяем, что роль пользователя позволяет удалять сообещение
	protected static function _throwIfUserRoleNotAllowToDeleteMessage(int $user_id, array $message, bool $is_admin):void {

		// проверка что пользователь не является админом/создателем группы и отправителем сообщения
		if (!$is_admin && Type_Conversation_Message_Main::getHandler($message)::getSenderUserId($message) != $user_id) {

			Gateway_Db_CompanyConversation_MessageBlock::rollback();
			throw new cs_Message_UserHaveNotPermission();
		}
	}

	// проверяем, что пользователь может удалить сообщение
	protected static function _throwIfUserCantDeleteMessage(array $message, int $user_id, bool $is_admin, bool $is_unusual_message = false):void {

		// проверяем флаги/тип сообщения
		if (!Type_Conversation_Message_Main::getHandler($message)::isFlagsAllowToDelete($message, $user_id)) {

			Gateway_Db_CompanyConversation_MessageBlock::rollback();
			throw new cs_Message_IsNotAllowForDelete();
		}

		// проверяем роль пользователя и время отправки сообщения
		if (!$is_admin && !Type_Conversation_Message_Main::getHandler($message)::isTimeAllowToDelete($message, $is_unusual_message)) {

			Gateway_Db_CompanyConversation_MessageBlock::rollback();
			throw new cs_Message_IsNotAllowForDelete();
		}
	}

	// подсчитываем количество файлов, изображений и видео
	protected static function _getFileCounter(array $file_map_list, array $file_counter = []):array {

		$file_count  = 0;
		$image_count = 0;
		$video_count = 0;
		foreach ($file_map_list as $v) {

			$file_count++;
			if (\CompassApp\Pack\File::getFileType($v) == FILE_TYPE_IMAGE) {
				$image_count++;
			}

			if (\CompassApp\Pack\File::getFileType($v) == FILE_TYPE_VIDEO) {
				$video_count++;
			}
		}
		if (count($file_counter) < 1) {

			$file_counter = [
				"file"  => 0,
				"image" => 0,
				"video" => 0,
			];
		}
		$file_counter["file"]  += $file_count;
		$file_counter["image"] += $image_count;
		$file_counter["video"] += $video_count;

		return $file_counter;
	}
}
