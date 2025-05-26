<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ReturnFatalException;
use JetBrains\PhpStorm\ArrayShape;

/**
 * класс для взаимодействия с горячими блоками сообщений
 */
class Type_Thread_Message_Block {

	public const    MESSAGE_PER_BLOCK_LIMIT = 30; // лимит количества сообщений в одном блоке

	// добавляет пул сообщений в блок
	// под пулом подразумевается массив из нарезанных частей одного сообщения, а не самостоятельные сообщения
	#[ArrayShape(["meta_row" => "array", "message_list" => "array"])]
	public static function addMessageList(string $thread_map, array $raw_message_list, Struct_Db_CompanyThread_ThreadDynamic $dynamic_obj):array {

		// получаем последний горячий блок для диалога
		$last_hot_block_id = self::_getLastHotBlockIdOnAddMessage($thread_map, $dynamic_obj);

		$current_time = time();
		Gateway_Db_CompanyThread_Main::beginTransaction();

		// обновляем сначала мету треда
		$last_raw_message = end($raw_message_list);
		$sender_user_id   = Type_Thread_Message_Main::getHandler($last_raw_message)::getSenderUserId($last_raw_message);
		$meta_row         = Gateway_Db_CompanyThread_ThreadMeta::updateThreadMetaOnMessageAdd($thread_map, $sender_user_id, count($raw_message_list));

		$chunk_raw_message_list = array_chunk($raw_message_list, self::MESSAGE_PER_BLOCK_LIMIT);

		// разделенные на чанки сообщения впишем в новые блоки
		$message_list = [];
		foreach ($chunk_raw_message_list as $message_list_for_block) {

			$block_row = self::_getValidBlockOnAddMessage($thread_map, $last_hot_block_id, count($message_list_for_block), $current_time);
			$block_id  = $block_row["block_id"];

			$message_list      = array_merge($message_list, self::_appendMessageListIntoBlock($thread_map, $block_id, $block_row, $message_list_for_block, $meta_row["message_count"] - count($raw_message_list)));
			$last_hot_block_id = $block_id;
		}

		Gateway_Db_CompanyThread_Main::commitTransaction();

		return [
			"meta_row"     => $meta_row,
			"message_list" => $message_list,
		];
	}

	// получаем последний горяий блок
	protected static function _getLastHotBlockIdOnAddMessage(string $thread_map, Struct_Db_CompanyThread_ThreadDynamic $dynamic_obj):int {

		// если последний блок заархивирован либо их вообще нет – сразу создаем новый
		$block_id = $dynamic_obj->last_block_id;
		if ($block_id == $dynamic_obj->start_block_id) {
			$block_id = self::_createNextHotBlock($thread_map, $block_id);
		}

		return $block_id;
	}

	// получаем подходящий блок за запись при создании нового сообщения
	protected static function _getValidBlockOnAddMessage(string $thread_map, int $block_id, int $message_count, int $current_time):array {

		// лочим на запись
		$block_row = Gateway_Db_CompanyThread_MessageBlock::getForUpdate($thread_map, $block_id);

		// смотрим, влезут ли все наши сообщеньки сюда
		if ($block_row["message_count"] + $message_count > self::MESSAGE_PER_BLOCK_LIMIT) {

			// если не влезают, то закрываем текущий блок, создаем новый блок для записи и лочим уже его
			Gateway_Db_CompanyThread_MessageBlock::set($thread_map, $block_id, ["closed_at" => $current_time, "updated_at" => $current_time,]);
			Gateway_Db_CompanyThread_Main::commitTransaction();

			$block_id = self::_createNextHotBlock($thread_map, $block_id);

			Gateway_Db_CompanyThread_Main::beginTransaction();
			$block_row = Gateway_Db_CompanyThread_MessageBlock::getForUpdate($thread_map, $block_id);
		}

		return $block_row;
	}

	// добавляем новый пул сообщений в блок
	// под пулом подразумевается массив из нарезанных частей одного сообщения, а не самостоятельные сообщения
	protected static function _appendMessageListIntoBlock(string $thread_map, int $block_id, array $block_row, array $message_list, int $thread_message_count):array {

		$output              = [];
		$block_message_index = $block_row["message_count"];

		foreach ($message_list as $v) {

			// формируем message_map и готовим сообщение к вставке
			$message_map = \CompassApp\Pack\Message\Thread::doPack($thread_map, $block_id, ++$block_message_index, ++$thread_message_count);
			$message     = Type_Thread_Message_Main::getHandler($v)::prepareForInsert($v, $message_map, $thread_message_count);

			// добавляем новое сообщение в блок и добавляем к результату
			$block_row["data"][$message_map] = $message;
			$output[]                        = $message;
		}

		// обновляем запись
		Gateway_Db_CompanyThread_MessageBlock::set($thread_map, $block_id, [
			"data"          => $block_row["data"],
			"message_count" => $block_message_index, // = общему количеству сообщений в блоке
			"updated_at"    => time(),
		]);
		return $output;
	}

	// скрываем сообщение для пользователя
	public static function hideMessageList(array $message_map_list, int $user_id, string $thread_map, int $block_id):array {

		Gateway_Db_CompanyThread_Main::beginTransaction();
		$block_row = Gateway_Db_CompanyThread_MessageBlock::getForUpdate($thread_map, $block_id);

		$hidden_message_list = [];
		foreach ($message_map_list as $v) {

			// проверяем было ли сообщение ранее скрыто пользователем
			$message = self::getMessage($v, $block_row);
			if (Type_Thread_Message_Main::getHandler($message)::isHiddenByUser($message, $user_id)) {
				continue;
			}

			// добавляем пользователя в список скрывших сообщение и обновляем блок с сообщением
			$block_row["data"][$v] = Type_Thread_Message_Main::getHandler($message)::addToHiddenBy($message, $user_id);
			$hidden_message_list[] = $message;
		}

		// обновляем блок с сообщением
		Gateway_Db_CompanyThread_MessageBlock::set($thread_map, $block_id, [
			"data"       => $block_row["data"],
			"updated_at" => time(),
		]);
		Gateway_Db_CompanyThread_Main::commitTransaction();

		return $hidden_message_list;
	}

	// изменяем текст существующего сообщения
	public static function editMessageText(string $message_map, int $user_id, string $text, array $users, array $mention_user_id_list):array {

		// начинаем транзакцию - получаем блок на обновление, получаем сообщение из блока
		$thread_map = \CompassApp\Pack\Message\Thread::getThreadMap($message_map);
		Gateway_Db_CompanyThread_Main::beginTransaction();
		$block_row = Gateway_Db_CompanyThread_MessageBlock::getForUpdateByMessageMap($message_map);
		$message   = self::getMessage($message_map, $block_row);

		// проверяем, позволяет ли данное сообщение вставить пустой текст при редактировании
		if (mb_strlen($text) < 1) {

			if (!Type_Thread_Message_Main::getHandler($message)::isEditEmptyText($message)) {

				Gateway_Db_CompanyThread_Main::rollback();
				throw new cs_Message_IsEmptyText();
			}
		}

		// совершаем все необходимые проверки - выплевываем exception, если не можем осуществлять действие
		self::_throwIfUserCantEditMessage($message, $user_id, $users);
		$old_mention_user_id_list = Type_Thread_Message_Main::getHandler($message)::getMentionedUsers($message);

		// получаем тех, кого добавили в упомянувших при редактировании
		$diff_added_mentioned_user_id_list = array_diff($mention_user_id_list, $old_mention_user_id_list);

		// получаем тех, кого убрали из упоминании при редактировании
		$diff_removed_mentioned_user_id_list = array_diff($old_mention_user_id_list, $mention_user_id_list);

		$edited_message = self::_editMessageText($message, $thread_map, $block_row, $message_map, $text, $mention_user_id_list);
		Gateway_Db_CompanyThread_Main::commitTransaction();

		// удаляем превью, если существует ссылка
		self::_deletePreviewIfLinkExists($message);

		return [$edited_message, $diff_added_mentioned_user_id_list, $diff_removed_mentioned_user_id_list];
	}

	// проверяем может ли пользователь редактировать конкретное сообщение - выплевываем exception, если не можем осуществлять действие
	protected static function _throwIfUserCantEditMessage(array $message, int $user_id, array $users):void {

		$is_new_errors = Type_System_Legacy::isNewErrors();

		// сообщение было удалено
		if (Type_Thread_Message_Main::getHandler($message)::isMessageDeleted($message)) {

			Gateway_Db_CompanyThread_Main::rollback();
			throw new cs_Message_IsDeleted();
		}

		// user_id не является отправителем сообщения
		if (Type_Thread_Message_Main::getHandler($message)::getSenderUserId($message) != $user_id) {

			Gateway_Db_CompanyThread_Main::rollback();
			throw new cs_Message_UserNotSender();
		}

		// проверяем, что флаги позволяют редактировать сообщение
		if (!Type_Thread_Message_Main::getHandler($message)::isFlagsAllowToEdit($message)) {

			Gateway_Db_CompanyThread_Main::rollback();
			throw new cs_Message_IsNotAllowForEdit();
		}

		// проверяем управляет ли тредом пользователь; позволяет ли время совершать действие
		if (!Type_Thread_Meta_Users::isCanManage($user_id, $users) && !Type_Thread_Message_Main::getHandler($message)::isTimeAllowToEdit($message)) {

			// если разрешено безлимитное редактирование сообщений
			$is_unlimited_messages_editing_enabled = Type_Company_Config::init()->get(Domain_Company_Entity_Config::UNLIMITED_MESSAGES_EDITING)["value"];
			if ($is_unlimited_messages_editing_enabled) {
				return;
			}

			Gateway_Db_CompanyThread_Main::rollback();

			if ($is_new_errors) {
				throw new cs_Message_IsTimeNotAllowForDoAction();
			}

			throw new cs_Message_IsNotAllowForEdit();
		}
	}

	/**
	 * Удаляем превью, если существовала ссылка
	 *
	 * @param array $message
	 *
	 * @return void
	 * @throws ReturnFatalException
	 * @throws \parseException
	 */
	protected static function _deletePreviewIfLinkExists(array $message):void {

		// удаляем превью, если существует ссылка
		if (Type_Thread_Message_Main::getHandler($message)::isAttachedLinkList($message)) {
			Gateway_Socket_Conversation::deletePreviewListFromConversation([Type_Thread_Message_Main::getHandler($message)::getMessageMap($message)]);
		}
	}

	// редактируем текст сообщения
	protected static function _editMessageText(array $message, string $thread_map, array $block_row, string $message_map, string $text, array $mention_user_id_list):array {

		// изменяем текст сообщения
		$edited_message = Type_Thread_Message_Main::getHandler($message)::editMessageText($message, $text, $mention_user_id_list);

		// обновляем блок с сообщением
		self::_updateThreadMessage($thread_map, $block_row, $message_map, $edited_message);
		return $edited_message;
	}

	// удаляем сообщение системой
	public static function setMessageSystemDeleted(string $thread_map, string $message_map, int $block_id):void {

		// открываем транзакцию
		Gateway_Db_CompanyThread_Main::beginTransaction();

		// получаем блок на обновление
		$block_row = Gateway_Db_CompanyThread_MessageBlock::getForUpdate($thread_map, $block_id);

		// помечаем сообщение удаленным
		$block_row["data"][$message_map] = Type_Thread_Message_Main::getHandler($block_row["data"][$message_map])::setSystemDeleted($block_row["data"][$message_map]);

		// обновляем блок с сообщением
		Gateway_Db_CompanyThread_MessageBlock::set($thread_map, $block_id, [
			"data"       => $block_row["data"],
			"updated_at" => time(),
		]);

		// выполняем транзакцию
		Gateway_Db_CompanyThread_Main::commitTransaction();
	}

	// удаляем все сообщения
	public static function deleteMessageList(array $message_map_list, string $thread_map, int $block_id, int $user_id,
							     array $users, bool $is_new_try_delete_message_error, bool $is_forced):array {

		Gateway_Db_CompanyThread_Main::beginTransaction();

		// получаем блок на обновление и сообщение из блока
		$block_row = Gateway_Db_CompanyThread_MessageBlock::getForUpdate($thread_map, $block_id);
		[$block_row, $message_list] = self::_setMessageDeletedForDeleteMessageList(
			$message_map_list, $block_row, $user_id, $users, $is_new_try_delete_message_error, $is_forced);

		Gateway_Db_CompanyThread_MessageBlock::set($thread_map, $block_id, [
			"data"       => $block_row["data"],
			"updated_at" => time(),
		]);

		Gateway_Db_CompanyThread_Main::commitTransaction();

		return $message_list;
	}

	// помечаем сообщения удаленными
	#[ArrayShape(["block_row" => "array", "message_list" => "array"])]
	protected static function _setMessageDeletedForDeleteMessageList(array $message_map_list, array $block_row, int $user_id, array $users,
											     bool  $is_new_try_delete_message_error, bool $is_forced):array {

		// получаем флаг может ли пользователь управлять сообщениями
		$is_manage = Type_Thread_Meta_Users::isCanManage($user_id, $users) || $is_forced;

		$for_delete_message_list = [];
		foreach ($message_map_list as $v) {

			$message = self::getMessage($v, $block_row);

			// проверяем, что роль пользователя позволяет удалить сообщение
			self::_throwIfUserRoleNotAllowToDeleteMessage($user_id, $message, $is_manage);

			$for_delete_message_list[$v] = $message;
		}

		$deleted_message_list = [];
		foreach ($for_delete_message_list as $message_map => $message) {

			self::_throwIfUserCantDeleteMessage($message, $is_manage, $is_new_try_delete_message_error);

			// помечаем сообщение удаленным и добавяем в массив
			$block_row["data"][$message_map]    = Type_Thread_Message_Main::getHandler($message)::setDeleted($message);
			$deleted_message_list[$message_map] = $block_row["data"][$message_map];
		}

		return [$block_row, $deleted_message_list];
	}

	// проверяем, что роль пользователя позволяет удалить сообщение
	protected static function _throwIfUserRoleNotAllowToDeleteMessage(int $user_id, array $message, bool $is_manage):void {

		// user_id не является отправителем сообщения и не имеет права админа
		if (!$is_manage && Type_Thread_Message_Main::getHandler($message)::getSenderUserId($message) != $user_id) {

			Gateway_Db_CompanyThread_Main::rollback();
			throw new cs_Message_UserNotSender();
		}
	}

	// проверяем может ли пользователь удалить конкретное сообщение - выплевываем exception, если не можем осуществлять действие
	protected static function _throwIfUserCantDeleteMessage(array $message, bool $is_manage, bool $is_new_try_delete_message_error):void {

		// флаги/тип сообщения не позволяют удалять сообщение
		if (!Type_Thread_Message_Main::getHandler($message)::isFlagsAllowToDelete($message)) {

			Gateway_Db_CompanyThread_Main::rollback();
			throw new cs_Message_IsNotAllowForDelete();
		}

		// время и роль не позволяет удалить сообщение
		if (!$is_manage && !Type_Thread_Message_Main::getHandler($message)::isTimeAllowToDelete($message)) {

			Gateway_Db_CompanyThread_Main::rollback();

			if (!$is_new_try_delete_message_error) {
				throw new cs_Message_IsNotAllowForDelete();
			}

			throw new cs_Message_IsTimeNotAllowToDelete();
		}
	}

	// проверяем, что блок активный/горячий
	public static function isActive(Struct_Db_CompanyThread_ThreadDynamic $dynamic_obj, int $block_id):bool {

		return true;
	}

	// проверяем, что блок открыт для новых сообщений (не был закрыт)
	public static function isOpen(array $block_row):bool {

		return $block_row["closed_at"] == 0;
	}

	// получает блок из базы
	public static function get(string $thread_map, int $block_id):array {

		return Gateway_Db_CompanyThread_MessageBlock::getOne($thread_map, $block_id);
	}

	// получает последние горячие блоки треда
	public static function getList(string $thread_map, array $block_id_list):array {

		$block_list = Gateway_Db_CompanyThread_MessageBlock::getList($thread_map, $block_id_list);

		return self::_formatBlockList($block_list);
	}

	// формируем список block_row по ключу block_id
	protected static function _formatBlockList(array $block_list):array {

		$format_block_list = [];

		foreach ($block_list as $v) {
			$format_block_list[$v["block_id"]] = $v;
		}

		return $format_block_list;
	}

	// проверяем существование блок
	public static function isExist(Struct_Db_CompanyThread_ThreadDynamic $dynamic_obj, int $block_id):bool {

		if ($block_id > $dynamic_obj->start_block_id && $block_id <= $dynamic_obj->last_block_id) {
			return true;
		}

		return false;
	}

	// возвращает сообщение из записи с блоком
	public static function getMessage(string $message_map, array $block_row):array {

		return $block_row["data"][$message_map];
	}

	// получить map последнего сообщения
	public static function getLastMessageMap(array $block_row):string {

		// обьявляем переменные
		$last_message_index = 0;
		$last_message_map   = "";

		// проходимся по всем блокам
		foreach ($block_row["data"] as $v) {

			// получаем message_map каждого сообщения и его индекс
			$message_map = Type_Thread_Message_Main::getHandler($v)::getMessageMap($v);
			$temp        = \CompassApp\Pack\Message\Thread::getThreadMessageIndex($message_map);

			// ищем индекс последнего сообщения
			if ($last_message_index < $temp) {

				$last_message_index = $temp;
				$last_message_map   = $message_map;
			}
		}

		// возвращаем message_map самого последнего сообщения
		return $last_message_map;
	}

	// меняем время написания сообщения
	public static function changeMessageCreatedAt(string $message_map, int $created_at):void {

		// получаем map треда и идентификатор блока сообщения
		$thread_map = \CompassApp\Pack\Message\Thread::getThreadMap($message_map);
		$block_id   = \CompassApp\Pack\Message\Thread::getBlockId($message_map);

		// открываем транзакцию
		Gateway_Db_CompanyThread_Main::beginTransaction();

		// получаем блок на обновление и сообщение из него
		$block_row = Gateway_Db_CompanyThread_MessageBlock::getForUpdate($thread_map, $block_id);
		$message   = Type_Thread_Message_Block::getMessage($message_map, $block_row);

		// меняем время написания сообщения
		$block_row["data"][$message_map] = Type_Thread_Message_Main::getHandler($message)::changeCreatedAt($message, $created_at);
		Gateway_Db_CompanyThread_MessageBlock::set($thread_map, $block_id, [
			"data"       => $block_row["data"],
			"updated_at" => time(),
		]);

		// выполняем транзакцию
		Gateway_Db_CompanyThread_Main::commitTransaction();
	}

	// получаем все горячие блоки из базы за раз
	public static function getActiveBlockRowList(string $thread_map, array $active_block_id_list):array {

		// оставляем только уникальные блоки
		$active_block_id_list = array_unique($active_block_id_list);

		$active_block_row_list = [];
		if (count($active_block_id_list) > 0) {
			$active_block_row_list = Type_Thread_Message_Block::getList($thread_map, $active_block_id_list);
		}

		return $active_block_row_list;
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

	// удаляет файлы после удалния сообщений
	public static function onDeleteMessageListWithFile(array $meta_row, array $message_list):void {

		$file_uuid_list = [];
		foreach ($message_list as $message) {

			$deleted_file_uuid_list = Type_Thread_Message_Main::getHandler($message)::getFileUuidListFromAnyMessage($message);
			$file_uuid_list         = array_merge($file_uuid_list, $deleted_file_uuid_list);
		}
		if (count($file_uuid_list) < 1) {
			return;
		}

		$conversation_map = Type_Thread_SourceParentRel::getMap($meta_row["source_parent_rel"]);
		Gateway_Socket_Conversation::deleteThreadFileList($conversation_map, $file_uuid_list);
	}

	// удаляет файлы после удалния сообщений
	public static function onHideMessageListWithFile(array $meta_row, array $message_list, int $user_id):void {

		$file_uuid_list = [];
		foreach ($message_list as $message) {

			$hidded_file_uuid_list = Type_Thread_Message_Main::getHandler($message)::getFileUuidListFromAnyMessage($message);
			$file_uuid_list        = array_merge($file_uuid_list, $hidded_file_uuid_list);
		}

		if (count($file_uuid_list) < 1) {
			return;
		}

		$conversation_map = Type_Thread_SourceParentRel::getMap($meta_row["source_parent_rel"]);
		Gateway_Socket_Conversation::hideThreadFileList($conversation_map, $file_uuid_list, $user_id);
	}

	/**
	 * Удаляем превью у сообщений
	 *
	 * @param array $message_map_list
	 *
	 * @return void
	 * @throws ReturnFatalException
	 */
	public static function onDeleteMessageListWithPreview(array $message_map_list):void {

		Gateway_Socket_Conversation::deletePreviewListFromConversation($message_map_list);
	}

	/**
	 * Прячем превью у сообщений
	 *
	 * @param int   $user_id
	 * @param array $message_list
	 *
	 * @return void
	 * @throws ReturnFatalException
	 * @throws \parseException
	 */
	public static function onHideMessageListWithPreview(int $user_id, array $message_list):void {

		$message_map_list = array_map(static fn(array $message) => Type_Thread_Message_Main::getHandler($message)::getMessageMap($message), $message_list);

		Gateway_Socket_Conversation::hidePreviewListFromConversation($user_id, $message_map_list);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// создаем новый горячий блок
	protected static function _createNextHotBlock(string $thread_map, int $block_id):int {

		// вставляем новый блок
		$block_id++;
		Gateway_Db_CompanyThread_MessageBlock::insert($thread_map, [
			"thread_map"    => $thread_map,
			"block_id"      => $block_id,
			"message_count" => 0,
			"created_at"    => time(),
			"updated_at"    => 0,
			"closed_at"     => 0,
			"data"          => [],
		]);

		// обновляем last_block_id в таблице dynamic
		Gateway_Db_CompanyThread_ThreadDynamic::set($thread_map, [
			"last_block_id" => $block_id,
			"updated_at"    => time(),
		]);

		return $block_id;
	}

	// обновляем блок с сообщением
	protected static function _updateThreadMessage(string $thread_map, array $block_row, string $message_map, array $message):void {

		// получаем id блока
		$block_id = \CompassApp\Pack\Message\Thread::getBlockId($message_map);

		// обновляем
		$block_row["data"][$message_map] = $message;
		Gateway_Db_CompanyThread_MessageBlock::set($thread_map, $block_id, [
			"data"       => $block_row["data"],
			"updated_at" => time(),
		]);
	}
}
