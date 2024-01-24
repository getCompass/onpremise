<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * класс для работы с dynamic ДИАЛОГА
 */
class Domain_Conversation_Entity_Dynamic {

	// получает запись из dynamic
	public static function get(string $conversation_map):array {

		return Gateway_Db_CompanyConversation_ConversationDynamicLegacy::getOne($conversation_map);
	}

	// получаем запись из dynamic на обновление
	public static function getForUpdate(string $conversation_map):array {

		return Gateway_Db_CompanyConversation_ConversationDynamicLegacy::getForUpdate($conversation_map);
	}

	// обновляет запись в dynamic
	public static function set(string $conversation_map, array $set):void {

		// обновляем запись
		Gateway_Db_CompanyConversation_ConversationDynamicLegacy::set($conversation_map, $set);
	}

	// обновляет запись в сообщениями
	public static function updateMessagesUpdatedAt(string $conversation_map, int|false $time = false):void {

		if ($time == false) {
			$time = time();
		}
		$set = [
			"messages_updated_at" => $time,
		];

		// обновляем запись
		Gateway_Db_CompanyConversation_ConversationDynamic::set($conversation_map, $set);
	}

	// обновляет метку с тредами
	public static function updateThreadsUpdatedAt(string $conversation_map, int|false $time = false):void {

		if ($time == false) {
			$time = time();
		}
		$set = [
			"threads_updated_at" => $time,
		];

		// обновляем запись
		Gateway_Db_CompanyConversation_ConversationDynamic::set($conversation_map, $set);
	}

	// обновляет user_clear_info для пользователя в диалоге
	public static function setClearUntil(string $conversation_map, int $user_id, int $clear_until):void {

		Gateway_Db_CompanyConversation_ConversationDynamicLegacy::beginTransaction();
		$dynamic_row = self::getForUpdate($conversation_map);

		// получаем значения скрытых для пользователя файлов и изображений
		$user_file_clear_item = $dynamic_row["user_file_clear_info"][$user_id] ?? self::initUserFileClearInfoItem();

		// инкрементим количество скрытых файлов оно равно текущим значениям файлов и изображений в диалоге
		$user_file_clear_item                          = self::setHiddenFileCount($user_file_clear_item, $dynamic_row["file_count"]);
		$user_file_clear_item                          = self::setHiddenImageCount($user_file_clear_item, $dynamic_row["image_count"]);
		$user_file_clear_item                          = self::setHiddenVideoCount($user_file_clear_item, $dynamic_row["video_count"]);
		$dynamic_row["user_file_clear_info"][$user_id] = $user_file_clear_item;

		// устанавливаем новое время очистки диалога
		$user_clear_info_item                     = $dynamic_row["user_clear_info"][$user_id] ?? self::initUserClearInfoItem();
		$user_clear_info_item                     = self::setClearInfoUntil($user_clear_info_item, $clear_until);
		$dynamic_row["user_clear_info"][$user_id] = $user_clear_info_item;

		self::set($conversation_map, [
			"user_clear_info"      => $dynamic_row["user_clear_info"],
			"user_file_clear_info" => $dynamic_row["user_file_clear_info"],
			"updated_at"           => time(),
		]);
		Gateway_Db_CompanyConversation_ConversationDynamicLegacy::commitTransaction();
	}

	/**
	 * Обновляет total_action_count для диалога
	 *
	 * @param string $conversation_map
	 * @param int    $total_action_count
	 *
	 * @return void
	 * @throws ReturnFatalException
	 * @throws \cs_RowIsEmpty
	 */
	public static function setTotalActionCount(string $conversation_map, int $total_action_count):void {

		Gateway_Db_CompanyConversation_ConversationDynamic::beginTransaction();
		$dynamic = Gateway_Db_CompanyConversation_ConversationDynamic::getForUpdate($conversation_map);

		// обновляем
		$dynamic->total_action_count = $total_action_count;
		self::set($conversation_map, [
			"total_action_count" => $dynamic->total_action_count,
		]);

		Gateway_Db_CompanyConversation_ConversationDynamic::commitTransaction();
	}

	// возвращаем очищенные conversation_clear_info для пользователя в диалоге
	public static function setUnclearUntilConversation(string $conversation_map, int $user_id):void {

		Gateway_Db_CompanyConversation_ConversationDynamicLegacy::beginTransaction();
		$dynamic_row = self::getForUpdate($conversation_map);

		// убираем запись о очистке диалога у пользователя
		unset($dynamic_row["conversation_clear_info"][$user_id]);

		self::set($conversation_map, [
			"conversation_clear_info" => $dynamic_row["conversation_clear_info"],
			"updated_at"              => time(),
		]);

		Gateway_Db_CompanyConversation_ConversationDynamicLegacy::commitTransaction();
	}

	// обновляет user_file_clear_info для пользователя в диалоге
	public static function setFileClearInfo(string $conversation_map, int $user_id, int $hidden_file_count, int $hidden_image_count, int $hidden_video_count):Struct_Db_CompanyConversation_ConversationDynamic {

		// открываем транзакцию и получаем запись на обновление
		Gateway_Db_CompanyConversation_ConversationDynamicLegacy::beginTransaction();
		$dynamic = Gateway_Db_CompanyConversation_ConversationDynamic::getForUpdate($conversation_map);

		// получаем и изменяем текущие значения скрытых для пользователя файлов и изображений
		$user_file_clear_item = $dynamic->user_file_clear_info[$user_id] ?? self::initUserFileClearInfoItem();
		$user_file_clear_item = self::incHiddenFileCount($user_file_clear_item, $hidden_file_count);
		$user_file_clear_item = self::incHiddenImageCount($user_file_clear_item, $hidden_image_count);
		$user_file_clear_item = self::incHiddenVideoCount($user_file_clear_item, $hidden_video_count);

		// устанавливаем новые значения скрытых для пользователя файлов и изображений и обновляем запись
		$dynamic->user_file_clear_info[$user_id] = $user_file_clear_item;
		$dynamic->updated_at                     = time();
		$dynamic->messages_updated_at            = time();
		$dynamic->messages_updated_version       = $dynamic->messages_updated_version + 1;
		self::set($conversation_map, [
			"user_file_clear_info"     => $dynamic->user_file_clear_info,
			"updated_at"               => $dynamic->updated_at,
			"messages_updated_at"      => $dynamic->messages_updated_at,
			"messages_updated_version" => $dynamic->messages_updated_version,
		]);

		Gateway_Db_CompanyConversation_ConversationDynamicLegacy::commitTransaction();

		return $dynamic;
	}

	// при удалении сообщения с файлами функция:
	//    - устанавливает новые текущие значения файлов и изображений
	//    - обновляет значения скрытых для пользователей файлов и изображений, если пользователя успели скрыть или очистить сообщение до его удаления
	public static function doChangeConversationFileCountInfo(string $conversation_map, array $hidden_message_user_list, int $file_count, int $image_count, int $video_count, int $created_at):Struct_Db_CompanyConversation_ConversationDynamic {

		// открываем транзакцию; получаем запись на обновление
		Gateway_Db_CompanyConversation_ConversationDynamic::beginTransaction();
		$dynamic = Gateway_Db_CompanyConversation_ConversationDynamic::getForUpdate($conversation_map);

		// список идентификаторов пользователей скрывших или очистивших сообщение с файлом
		$clear_message_user_list = array_keys($dynamic->user_clear_info);

		// получаем список пользователей, кто успел скрыть или очистить сообщение с файлом до того как оно было удалено
		$user_list = self::_getUserListWhoHiddenOrClearedMessageBeforeDelete(
			$clear_message_user_list,
			$dynamic->user_clear_info,
			$dynamic->conversation_clear_info,
			$created_at
		);
		$user_list = array_unique(array_merge($user_list, $hidden_message_user_list));

		// уменьшаем счетчики скрытых файлов и изображений пользователей
		foreach ($user_list as $user_id) {

			// декрементим количество файлов и изображения скрытых для пользователя
			$user_file_clear_item = self::_decHiddenFileAndImageCount($dynamic->user_file_clear_info, $user_id, $file_count, $image_count, $video_count);

			// устанавливаем новые значения количиства скрытых для пользователя изображений
			$dynamic->user_file_clear_info[$user_id] = $user_file_clear_item;
		}

		$dynamic = self::_updateDynamicOnChangeConversationFileCountInfo($conversation_map, $dynamic, $file_count, $image_count, $video_count);
		Gateway_Db_CompanyConversation_ConversationDynamic::commitTransaction();

		return $dynamic;
	}

	// декрементим количество файлов и изображения скрытых для пользователя
	protected static function _decHiddenFileAndImageCount(array $user_file_clear_info, int $user_id, int $file_count, int $image_count, int $video_count):array {

		// получаем количество скрытых для пользователя файлов и изображений
		$user_file_clear_item = $user_file_clear_info[$user_id] ?? self::initUserFileClearInfoItem();

		// декрементим количество файлов и изображений скрытых для пользователя
		$user_file_clear_item = self::decHiddenFileCount($user_file_clear_item, $file_count);
		$user_file_clear_item = self::decHiddenImageCount($user_file_clear_item, $image_count);
		return self::decHiddenVideoCount($user_file_clear_item, $video_count);
	}

	// метод для получения списка пользователей, кто успел скрыть или очистить сообщение раньше, чем оно было удалено
	protected static function _getUserListWhoHiddenOrClearedMessageBeforeDelete(array $clear_message_user_list, array $user_clear_info, array $conversation_clear_info, int $created_at):array {

		$user_list = [];
		foreach ($clear_message_user_list as $user_id) {

			// получаем время, когда пользователь очистил диалог
			$clear_until = self::getClearUntil($user_clear_info, $conversation_clear_info, $user_id);

			// если пользователь очистил диалог раньше чем было создано сообщение - пропускаем его
			if ($clear_until < $created_at) {
				continue;
			}

			// получили пользователей, кто очистил сообщение раньше чем оно удалено
			$user_list[] = $user_id;
		}

		return $user_list;
	}

	// обновляем таблице dynamic при изменении количества файлов в диалоге
	protected static function _updateDynamicOnChangeConversationFileCountInfo(string $conversation_map, Struct_Db_CompanyConversation_ConversationDynamic $dynamic, int $file_count, int $image_count, int $video_count):Struct_Db_CompanyConversation_ConversationDynamic {

		$dynamic->file_count               = $dynamic->file_count - $file_count;
		$dynamic->image_count              = $dynamic->image_count - $image_count;
		$dynamic->video_count              = $dynamic->video_count - $video_count;
		$dynamic->updated_at               = time();
		$dynamic->messages_updated_version = $dynamic->messages_updated_version + 1;
		$dynamic->messages_updated_at      = time();

		$set = [
			"file_count"               => $dynamic->file_count,
			"image_count"              => $dynamic->image_count,
			"video_count"              => $dynamic->video_count,
			"user_file_clear_info"     => $dynamic->user_file_clear_info,
			"updated_at"               => $dynamic->updated_at,
			"messages_updated_version" => $dynamic->messages_updated_version,
			"messages_updated_at"      => $dynamic->messages_updated_at,
		];
		self::set($conversation_map, $set);

		return $dynamic;
	}

	// обновляет user_mute_info для пользователя в диалоге
	public static function setMuted(string $conversation_map, int $user_id, int $is_muted_permanent, int $interval_minutes, int $max_time_limit, int $mute_time_at):int {

		// открываем транзакцию
		Gateway_Db_CompanyConversation_ConversationDynamicLegacy::beginTransaction();

		// получаем запись на обновление и получаем новое значение muted_until
		$dynamic_row = self::getForUpdate($conversation_map);

		// получаем muted_until пользователя
		$user_mute_info_item = $dynamic_row["user_mute_info"][$user_id] ?? self::initUserMuteInfoItem();
		$muted_until         = self::getMutedUntil($user_mute_info_item);

		// получаем новое значение muted_until
		$new_muted_until = self::_getNewMutedUntil($muted_until, $mute_time_at, $interval_minutes, $max_time_limit);

		$dynamic_row["user_mute_info"][$user_id] = self::setMuteInfo($user_mute_info_item, $is_muted_permanent, $new_muted_until);

		self::set($conversation_map, [
			"user_mute_info" => $dynamic_row["user_mute_info"],
			"updated_at"     => time(),
		]);
		Gateway_Db_CompanyConversation_ConversationDynamicLegacy::commitTransaction();

		return $new_muted_until;
	}

	// получаем новый muted_until
	protected static function _getNewMutedUntil(int $muted_until, int $current_time_at, int $interval_minutes, int $max_time_limit):int {

		// мьютим уведомления, стартуя от текущего времени, если у пользователя уведомления не отключены
		if ($muted_until < $current_time_at) {
			$muted_until = $current_time_at;
		}

		// получаем новое время до скольки нужно отключить уведомления, если больше максимального, то ограничиваем максимумом
		$muted_until = $muted_until + $interval_minutes * 60;
		if ($muted_until > $max_time_limit) {
			$muted_until = $max_time_limit;
		}

		return $muted_until;
	}

	// обнуляет user_mute_info для пользователя в диалоге
	public static function setUnmuted(string $conversation_map, int $user_id):void {

		// открываем транзакцию
		Gateway_Db_CompanyConversation_ConversationDynamicLegacy::beginTransaction();

		// получаем запись на обновление
		$dynamic_row = self::getForUpdate($conversation_map);

		// добавляем/обнуляем значения user_mute_info
		$user_mute_info_item = $dynamic_row["user_mute_info"][$user_id] ?? self::initUserMuteInfoItem();
		$user_mute_info_item = self::clearMuteInfo($user_mute_info_item);

		$dynamic_row["user_mute_info"][$user_id] = $user_mute_info_item;

		// обновляем запись
		self::set($conversation_map, [
			"user_mute_info" => $dynamic_row["user_mute_info"],
			"updated_at"     => time(),
		]);

		// коммитим транзакцию
		Gateway_Db_CompanyConversation_ConversationDynamicLegacy::commitTransaction();
	}

	// получаем количество скрытых видео
	public static function getHiddenCountFromUserFileClearInfo(array $user_file_clear_info, array $user_clear_info, array $conversation_clear_info, int $user_id):array {

		// если пользователь не скрывал видео то отдаем 0
		if (!self::isHasUserFileClearInfoItem($user_file_clear_info, $user_id) && !isset($conversation_clear_info[$user_id])) {

			return [
				"hidden_file_count"  => 0,
				"hidden_image_count" => 0,
				"hidden_video_count" => 0,
			];
		}

		// получаем clear_until
		$conversation_clear_until = $conversation_clear_info[$user_id]["clear_until"] ?? 0;
		$user_clear_until         = $user_clear_info[$user_id]["clear_until"] ?? 0;

		if ($user_clear_until >= $conversation_clear_until) {

			return [
				"hidden_file_count"  => self::getHiddenFileCount($user_file_clear_info[$user_id]),
				"hidden_image_count" => self::getHiddenImageCount($user_file_clear_info[$user_id]),
				"hidden_video_count" => self::getHiddenVideoCount($user_file_clear_info[$user_id]),
			];
		}

		return [
			"hidden_file_count"  => self::getHiddenFileCountForConversation($conversation_clear_info[$user_id]),
			"hidden_image_count" => self::getHiddenImageCountForConversation($conversation_clear_info[$user_id]),
			"hidden_video_count" => self::getHiddenVideoCountForConversation($conversation_clear_info[$user_id]),
		];
	}

	// -------------------------------------------------------
	// методы для работы с JSON полем user_mute_info
	// -------------------------------------------------------

	/*
	 * структура user_mute_info
	 *
	   * Array
	   * (
	   *     [1] => Array
	   *         (
	   *             [version] => 2
	   *             [is_muted] => 1
	 * 		   [muted_until] => 0
	   *         )
	   *
	   *     [2] => Array
	   *         (
	   *             [version] => 1
	   *             [is_muted] => 0
	   *         )
	 * 	 [3] => Array
	   *         (
	   *             [version] => 2
	   *             [is_muted] => 0
	 * 		   [muted_until] => 0
	   *         )
	   *
	   * )
	 */

	// версия упаковщика
	protected const _CURRENT_USER_MUTE_INFO_VERSION = 2;

	// схема каждого элемента в массиве
	protected const _USER_MUTE_INFO_ITEM_SCHEMA = [
		"is_muted"    => 0,
		"muted_until" => 0,
	];

	// получить user_mute_info в формате удобном для threads.addThread
	/*
	 * Array
	 * (
	 * 	[1] => 0
	 * 	[2] => 1
	 * )
	 */
	public static function getMuteInfoFormattedForThread(array $user_mute_info, int $time_at):array {

		$output = [];

		foreach ($user_mute_info as $k => $_) {
			$output[$k] = self::isMuted($user_mute_info, $k, $time_at) ? 1 : 0;
		}

		return $output;
	}

	// узнать в муте ли диалог
	public static function isMuted(array $user_mute_info, int $user_id, int $time_at):bool {

		if (!isset($user_mute_info[$user_id])) {
			return false;
		}

		// актуализируем версию
		$user_mute_info_item = $user_mute_info[$user_id] ?? self::initUserMuteInfoItem();
		$user_mute_info_item = self::_getUserMuteInfoItem($user_mute_info_item);

		// если диалог перманентном муте
		if ($user_mute_info_item["is_muted"] == 1) {
			return true;
		}

		// если диалог в муте на время
		if ($time_at < $user_mute_info_item["muted_until"]) {
			return true;
		}

		return false;
	}

	// обновляет информацию в user_mute_info_item
	public static function setMuteInfo(array $user_mute_info_item, int $is_muted, int $new_muted_until):array {

		// актуализируем версию
		$user_mute_info_item = self::_getUserMuteInfoItem($user_mute_info_item);

		// устанавливаем значение
		$user_mute_info_item["is_muted"]    = $is_muted;
		$user_mute_info_item["muted_until"] = $new_muted_until;

		return $user_mute_info_item;
	}

	// получает значение muted_until
	public static function getMutedUntil(array $user_mute_info_item):int {

		// актуализируем версию
		$user_mute_info_item = self::_getUserMuteInfoItem($user_mute_info_item);

		return $user_mute_info_item["muted_until"];
	}

	// обнуляет поля is_muted и muted_until в базе user_mute_info_item
	public static function clearMuteInfo(array $user_mute_info_item):array {

		// актуализируем версию
		$user_mute_info_item = self::_getUserMuteInfoItem($user_mute_info_item);

		// устанавливаем значение
		$user_mute_info_item["is_muted"]    = 0;
		$user_mute_info_item["muted_until"] = 0;

		return $user_mute_info_item;
	}

	// создать новую структуру для элемента в user_mute_info
	public static function initUserMuteInfoItem():array {

		$user_mute_info_item            = self::_USER_MUTE_INFO_ITEM_SCHEMA;
		$user_mute_info_item["version"] = self::_CURRENT_USER_MUTE_INFO_VERSION;

		return $user_mute_info_item;
	}

	// получить актуальную структуру для элемента в user_mute_info
	protected static function _getUserMuteInfoItem(array $user_mute_info_item):array {

		// если версия не совпадает - дополняем её до текущей
		if ($user_mute_info_item["version"] != self::_CURRENT_USER_MUTE_INFO_VERSION) {

			$user_mute_info_item = array_merge(self::_USER_MUTE_INFO_ITEM_SCHEMA, $user_mute_info_item);

			$user_mute_info_item["version"] = self::_CURRENT_USER_MUTE_INFO_VERSION;
		}

		return $user_mute_info_item;
	}

	// -------------------------------------------------------
	// методы для работы с JSON полем user_clear_info
	// -------------------------------------------------------

	/*
	 * стуктура поля user_clear_info
	 *
	   * Array
	   * (
	   *     [1] => Array
	   *         (
	   *             [version] => 1
	   *             [clear_until] => 1530162893
	   *         )
	   *
	   *     [2] => Array
	   *         (
	   *             [version] => 1
	   *             [clear_until] => 1530767693
	   *         )
	   *
	   * )
	 */

	// версия упаковщика
	protected const _CURRENT_USER_CLEAR_INFO_VERSION = 1;

	// схема каждого элемента в массиве
	protected const _USER_CLEAR_INFO_ITEM_SCHEMA = [
		"clear_until" => 0,
	];

	// схема каждого элемента в массиве
	protected const _CONVERSATION_CLEAR_INFO_ITEM_SCHEMA = [
		"clear_until"        => 0,
		"hidden_file_count"  => 0,
		"hidden_image_count" => 0,
		"hidden_video_count" => 0,
	];

	// получить user_clear_info в формате удобном для threads.addThread
	/*
	 * Array
	 * (
	 * 	[1] => 1530162893
	 *
	 * 	[2] => 1530767693
	 * )
	 */
	public static function getClearInfoFormattedForThread(array $user_clear_info, array $conversation_clear_info):array {

		$output = [];

		foreach ($user_clear_info as $k => $_) {
			$output[$k] = self::getClearUntil($user_clear_info, $conversation_clear_info, $k);
		}

		return $output;
	}

	// получить время до которого пользователь очистил диалог
	public static function getClearUntil(array $user_clear_info, array $conversation_clear_info, int $user_id):int {

		if (!isset($user_clear_info[$user_id]) && !isset($conversation_clear_info[$user_id])) {
			return 0;
		}

		// где пользователь очистил сам
		$user_clear_info_item["clear_until"] = 0;
		if (isset($user_clear_info[$user_id])) {

			$user_clear_info_item = $user_clear_info[$user_id];
			$user_clear_info_item = self::_getUserClearInfoItem($user_clear_info_item);
		}

		// где у пользователя очистили
		$conversation_clear_info_item["clear_until"] = 0;
		if (isset($conversation_clear_info[$user_id])) {

			// актуализируем версию
			$conversation_clear_info_item = $conversation_clear_info[$user_id];
			$conversation_clear_info_item = self::_getConversationClearInfoItem($conversation_clear_info_item);
		}

		// получаем последний clear_until
		if ($user_clear_info_item["clear_until"] >= $conversation_clear_info_item["clear_until"]) {
			$clear_until = $user_clear_info_item["clear_until"];
		} else {
			$clear_until = $conversation_clear_info_item["clear_until"];
		}

		return $clear_until;
	}

	// устанавливаем user_clear_until
	public static function setClearInfoUntil(array $user_clear_info_item, int $clear_until):array {

		// актуализируем версию
		$user_clear_info_item = self::_getUserClearInfoItem($user_clear_info_item);

		// устанавливаем значение
		$user_clear_info_item["clear_until"] = $clear_until;

		return $user_clear_info_item;
	}

	// устанавливаем conversation_clear_until
	public static function setClearInfoUntilConversation(array $conversation_clear_info_item, int $clear_until):array {

		// актуализируем версию
		$conversation_clear_info_item = self::_getUserClearInfoItem($conversation_clear_info_item);

		// устанавливаем значение
		$conversation_clear_info_item["clear_until"] = $clear_until;

		return $conversation_clear_info_item;
	}

	// создать новую структуру для элемента в user_clear_info
	public static function initUserClearInfoItem():array {

		$user_clear_info_item            = self::_USER_CLEAR_INFO_ITEM_SCHEMA;
		$user_clear_info_item["version"] = self::_CURRENT_USER_CLEAR_INFO_VERSION;

		return $user_clear_info_item;
	}

	// создать новую структуру для элемента в user_clear_info
	public static function initConversationClearInfoItem():array {

		$user_clear_info_item            = self::_CONVERSATION_CLEAR_INFO_ITEM_SCHEMA;
		$user_clear_info_item["version"] = self::_CURRENT_USER_CLEAR_INFO_VERSION;

		return $user_clear_info_item;
	}

	// обновляет conversation_clear_info для списка пользователей в диалоге
	public static function setClearUntilConversationForUserIdList(string $conversation_map, array $user_id_list, int $clear_until, bool $is_clear_for_all):Struct_Db_CompanyConversation_ConversationDynamic {

		Gateway_Db_CompanyConversation_ConversationDynamic::beginTransaction();
		$dynamic = Gateway_Db_CompanyConversation_ConversationDynamic::getForUpdate($conversation_map);

		$conversation_clear_info = self::_setConversationClearInfoForUserIdList($user_id_list, (array) $dynamic, $clear_until);

		$time = time();

		$dynamic->conversation_clear_info  = $conversation_clear_info;
		$dynamic->updated_at               = $time;
		$dynamic->messages_updated_version = $dynamic->messages_updated_version + 1;
		$dynamic->messages_updated_at      = $time;

		$set = [
			"conversation_clear_info"  => $dynamic->conversation_clear_info,
			"updated_at"               => $dynamic->updated_at,
			"messages_updated_version" => $dynamic->messages_updated_version,
			"messages_updated_at"      => $dynamic->messages_updated_at,
		];

		// если очищаем для всех, то еще сбрасываем количество действий до 1 (создали группу)
		if ($is_clear_for_all) {

			$dynamic->total_action_count = 1;
			$set["total_action_count"]   = $dynamic->total_action_count;
		}

		// обновляем
		self::set($conversation_map, $set);

		Gateway_Db_CompanyConversation_ConversationDynamic::commitTransaction();

		return $dynamic;
	}

	/**
	 * Устанавливаем conversation_clear_info для списка пользователей
	 *
	 */
	protected static function _setConversationClearInfoForUserIdList(array $user_id_list, array $dynamic_row, int $clear_until):array {

		// за основу берем существующие данные
		$conversation_clear_info = $dynamic_row["conversation_clear_info"];

		foreach ($user_id_list as $user_id) {

			// получаем значения скрытых для пользователя файлов и изображений
			$conversation_clear_info_item = $conversation_clear_info[$user_id] ?? self::initConversationClearInfoItem();

			// инкрементим количество скрытых файлов оно равно текущим значениям файлов и изображений в диалоге
			$conversation_clear_info_item = self::setHiddenFileCount(
				$conversation_clear_info_item,
				$dynamic_row["file_count"]
			);
			$conversation_clear_info_item = self::setHiddenImageCount(
				$conversation_clear_info_item,
				$dynamic_row["image_count"]
			);
			$conversation_clear_info_item = self::setHiddenVideoCount(
				$conversation_clear_info_item,
				$dynamic_row["video_count"]
			);

			// устанавливаем новое время очистки диалога
			$conversation_clear_info_item      = self::setClearInfoUntilConversation($conversation_clear_info_item, $clear_until);
			$conversation_clear_info[$user_id] = $conversation_clear_info_item;
		}

		return $conversation_clear_info;
	}

	// получить актуальную структуру для элемента в user_clear_info
	protected static function _getUserClearInfoItem(array $user_clear_info_item):array {

		// если версия не совпадает - дополняем её до текущей
		if ($user_clear_info_item["version"] != self::_CURRENT_USER_CLEAR_INFO_VERSION) {

			$user_clear_info_item            = array_merge(self::_USER_CLEAR_INFO_ITEM_SCHEMA, $user_clear_info_item);
			$user_clear_info_item["version"] = self::_CURRENT_USER_CLEAR_INFO_VERSION;
		}

		return $user_clear_info_item;
	}

	// получить актуальную структуру для элемента в user_clear_info
	protected static function _getConversationClearInfoItem(array $user_clear_info_item):array {

		// если версия не совпадает - дополняем её до текущей
		if ($user_clear_info_item["version"] != self::_CURRENT_USER_CLEAR_INFO_VERSION) {

			$user_clear_info_item            = array_merge(self::_USER_CLEAR_INFO_ITEM_SCHEMA, $user_clear_info_item);
			$user_clear_info_item["version"] = self::_CURRENT_USER_CLEAR_INFO_VERSION;
		}

		return $user_clear_info_item;
	}

	// -------------------------------------------------------
	// методы для работы с JSON полем user_file_clear_info
	// -------------------------------------------------------

	/*
	 * стуктура поля user_file_clear_info
	 *
	   * Array
	   * (
	   *     [1] => Array // id пользователя
	   *         (
	   *             [version] => 2
	   *             [hidden_file_count]  => 15
	 *             [hidden_image_count] => 10
	 *             [hidden_video_count] => 4
	   *         )
	   *
	   *     [2] => Array
	   *         (
	   *             [version] => 2
	   *             [hidden_file_count]  => 28	// количество скрытых пользователем файлов в диалоге
	 *             [hidden_image_count] => 17	// количество скрытых пользователем изображений в диалоге
	 *             [hidden_video_count] => 3	// количество скрытых пользователем видео в диалоге
	   *         )
	   * )
	 */

	// версия JSON поля user_file_clear_info
	protected const _CURRENT_USER_FILE_CLEAR_INFO_VERSION = 2;

	// схема JSON поля user_file_clear_info
	protected const _USER_FILE_CLEAR_INFO_ITEM_SCHEMA = [
		1 => [
			"hidden_file_count"  => 0,
			"hidden_image_count" => 0,
		],
		2 => [
			"hidden_file_count"  => 0,
			"hidden_image_count" => 0,
			"hidden_video_count" => 0,
		],
	];

	// создаем актуальную структуру для элемента в user_file_clear_info_item
	public static function initUserFileClearInfoItem():array {

		$user_file_clear_info_item            = self::_USER_FILE_CLEAR_INFO_ITEM_SCHEMA[self::_CURRENT_USER_FILE_CLEAR_INFO_VERSION];
		$user_file_clear_info_item["version"] = self::_CURRENT_USER_FILE_CLEAR_INFO_VERSION;

		return $user_file_clear_info_item;
	}

	// получаем счетчик скрытых файлов пользователя
	public static function getHiddenFileCount(array $user_file_clear_item):int {

		// актуализируем версию
		$user_file_clear_item = self::_getUserFileClearInfoItem($user_file_clear_item);

		return $user_file_clear_item["hidden_file_count"];
	}

	// получаем счетчик скрытых файлов пользователя в диалоге
	public static function getHiddenFileCountForConversation(array $conversation_clear_item):int {

		return $conversation_clear_item["hidden_file_count"];
	}

	// получаем счетчик скрытых картинок пользователя
	public static function getHiddenImageCount(array $user_file_clear_item):int {

		// актуализируем версию
		$user_file_clear_item = self::_getUserFileClearInfoItem($user_file_clear_item);

		return $user_file_clear_item["hidden_image_count"];
	}

	// получаем счетчик скрытых картинок пользователя в диалоге
	public static function getHiddenImageCountForConversation(array $conversation_clear_item):int {

		return $conversation_clear_item["hidden_image_count"];
	}

	// получаем счетчик скрытых видео пользователя
	public static function getHiddenVideoCount(array $user_file_clear_item):int {

		// актуализируем версию
		$user_file_clear_item = self::_getUserFileClearInfoItem($user_file_clear_item);

		return $user_file_clear_item["hidden_video_count"];
	}

	// получаем счетчик скрытых видео пользователя в диалоге
	public static function getHiddenVideoCountForConversation(array $conversation_clear_item):int {

		return $conversation_clear_item["hidden_video_count"];
	}

	// обновляем счетчик скрытых файлов пользователя
	public static function setHiddenFileCount(array $user_file_clear_item, int $hidden_file_count):array {

		// актуализируем версию
		$user_file_clear_item = self::_getUserFileClearInfoItem($user_file_clear_item);

		// устанавливаем новое количество скрытых файлов
		$user_file_clear_item["hidden_file_count"] = $hidden_file_count;

		return $user_file_clear_item;
	}

	// обновляем счетчик скрытых изображений пользователя
	public static function setHiddenImageCount(array $user_file_clear_item, int $hidden_image_count):array {

		// актуализируем версию
		$user_file_clear_item = self::_getUserFileClearInfoItem($user_file_clear_item);

		// устанавливаем новое количество скрытых изображений
		$user_file_clear_item["hidden_image_count"] = $hidden_image_count;

		return $user_file_clear_item;
	}

	// обновляем счетчик скрытых видео пользователя
	public static function setHiddenVideoCount(array $user_file_clear_item, int $hidden_video_count):array {

		// актуализируем версию
		$user_file_clear_item = self::_getUserFileClearInfoItem($user_file_clear_item);

		// устанавливаем новое количество скрытых видео
		$user_file_clear_item["hidden_video_count"] = $hidden_video_count;

		return $user_file_clear_item;
	}

	// инкрементим счетчик скрытых файлов пользователя
	public static function incHiddenFileCount(array $user_file_clear_item, int $hidden_file_count):array {

		// актуализируем версию
		$user_file_clear_item = self::_getUserFileClearInfoItem($user_file_clear_item);

		// увеличиваем количество скрытых для пользователя файлов
		$user_file_clear_item["hidden_file_count"] += $hidden_file_count;

		return $user_file_clear_item;
	}

	// инкрементим счетчик скрытых изображений пользователя
	public static function incHiddenImageCount(array $user_file_clear_item, int $hidden_image_count):array {

		// актуализируем версию
		$user_file_clear_item = self::_getUserFileClearInfoItem($user_file_clear_item);

		// увеличиваем количество скрытых для пользователя изображений
		$user_file_clear_item["hidden_image_count"] += $hidden_image_count;

		return $user_file_clear_item;
	}

	// инкрементим счетчик скрытых видео пользователя
	public static function incHiddenVideoCount(array $user_file_clear_item, int $hidden_video_count):array {

		// актуализируем версию
		$user_file_clear_item = self::_getUserFileClearInfoItem($user_file_clear_item);

		// увеличиваем количество скрытых для пользователя видео
		$user_file_clear_item["hidden_video_count"] += $hidden_video_count;

		return $user_file_clear_item;
	}

	// декрементим счетчик скрытых файлов пользователя
	public static function decHiddenFileCount(array $user_file_clear_item, int $hidden_file_count):array {

		// актуализируем версию
		$user_file_clear_item = self::_getUserFileClearInfoItem($user_file_clear_item);

		// устанавливаем новое количество скрытых файлов
		$user_file_clear_item["hidden_file_count"] -= $hidden_file_count;

		return $user_file_clear_item;
	}

	// декрементим счетчик скрытых изображений пользователя
	public static function decHiddenImageCount(array $user_file_clear_item, int $hidden_image_count):array {

		// актуализируем версию
		$user_file_clear_item = self::_getUserFileClearInfoItem($user_file_clear_item);

		// устанавливаем новое количество скрытых изображений
		$user_file_clear_item["hidden_image_count"] -= $hidden_image_count;

		return $user_file_clear_item;
	}

	// декрементим счетчик скрытых видео пользователя
	public static function decHiddenVideoCount(array $user_file_clear_item, int $hidden_video_count):array {

		// актуализируем версию
		$user_file_clear_item = self::_getUserFileClearInfoItem($user_file_clear_item);

		// устанавливаем новое количество скрытых виодео
		$user_file_clear_item["hidden_video_count"] -= $hidden_video_count;

		return $user_file_clear_item;
	}

	// проверяем есть ли запись о количестве скрытых полльзователем файлов и изображений
	public static function isHasUserFileClearInfoItem(array $user_file_clear_info, int $user_id):bool {

		// если записи для пользователя не существует
		if (!isset($user_file_clear_info[$user_id])) {
			return false;
		}

		return true;
	}

	// получаем актуальную структуру для элемента в user_file_clear_info_item
	protected static function _getUserFileClearInfoItem(array $user_file_clear_info_item):array {

		// если версия не совпадает - дополняем её до текущей
		if ($user_file_clear_info_item["version"] != self::_CURRENT_USER_FILE_CLEAR_INFO_VERSION) {

			$user_file_clear_info_item = array_merge(
				self::_USER_FILE_CLEAR_INFO_ITEM_SCHEMA[self::_CURRENT_USER_FILE_CLEAR_INFO_VERSION],
				$user_file_clear_info_item
			);

			$user_file_clear_info_item["version"] = self::_CURRENT_USER_FILE_CLEAR_INFO_VERSION;
		}

		return $user_file_clear_info_item;
	}
}