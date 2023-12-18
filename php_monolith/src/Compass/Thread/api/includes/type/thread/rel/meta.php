<?php

namespace Compass\Thread;

use JetBrains\PhpStorm\ArrayShape;
use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для работы с сущностью, на основе которой строится вся бизнес логика по доступу к взаимодействию треда
 */
class Type_Thread_Rel_Meta {

	protected const _CACHE_VERSION        = 1;
	protected const _CACHE_EXPIRE_TIME    = 5 * 60;
	protected const _DEFAULT_CACHE_SCHEMA = [
		"user_list"           => [],
		"not_found_user_list" => [],
		"parent_meta_users"   => false,
	];

	public const CONVERSATION_ROLE_ADMIN = 2; // участник с правами администратора
	public const CONVERSATION_ROLE_OWNER = 3; // создатель диалога/верховный

	// массив из ролей, которые могут управлять группой
	public const CONVERSATION_MANAGED_ROLES = [
		self::CONVERSATION_ROLE_ADMIN,
		self::CONVERSATION_ROLE_OWNER,
	];

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	// сохранить участников треда в memcache
	public static function setCache(string $source_parent_map, array $value):void {

		// получаем ключ
		$key = self::_getKey($source_parent_map);
		ShardingGateway::cache()->set($key, $value, self::_CACHE_EXPIRE_TIME);
	}

	// получить участников треда из memcache
	public static function getCache(string $source_parent_map):array {

		// получаем ключ
		$key = self::_getKey($source_parent_map);
		return ShardingGateway::cache()->get($key, self::_DEFAULT_CACHE_SCHEMA);
	}

	// очистить кэш (например при изменении количества участников диалога)
	public static function clearCache(string $source_parent_map):void {

		// получаем ключ
		$key = self::_getKey($source_parent_map);
		ShardingGateway::cache()->delete($key);
	}

	// получить участников сущностей, на основе которой строится бизнес логика треда
	public static function getUsersForMetaList(array $meta_list_by_source_parent_rel, int $user_id):array {

		foreach ($meta_list_by_source_parent_rel as $meta_list) {

			foreach ($meta_list as $meta_row) {

				// получаем тип сущности, за которым закреплен тред
				$source_parent_type = Type_Thread_SourceParentRel::getType($meta_row["source_parent_rel"]);

				// проверяем что тред закреплен за диалогом (поменять на switch..case когда появятся новые сущности)
				if ($source_parent_type != SOURCE_PARENT_ENTITY_TYPE_CONVERSATION) {

					throw new ParseFatalException("Parent meta type {$source_parent_type} is not supported");
				}
			}
		}

		// пробуем получить юзеров списка диалогов
		$conversation_map_list = array_keys($meta_list_by_source_parent_rel);
		$data_packet           = self::_tryGetUsersFromConversationList($conversation_map_list, $user_id);

		return self::_doPrepareData($data_packet, $meta_list_by_source_parent_rel);
	}

	// получаем юзеров из диалогов
	protected static function _tryGetUsersFromConversationList(array $conversation_map_list, int $user_id):array {

		// работаем со списком диалогов
		$conversation_user_list           = [];
		$not_access_conversation_map_list = [];

		// отправляем запрос для получения юзеров списка диалогов
		[$status, $response] = Gateway_Socket_Conversation::getUsersByConversationList($conversation_map_list, $user_id);

		// мерджим результаты сокет-запроса со списками, полученными ранее
		$conversation_user_list           = array_merge($response["users_by_conversation"], $conversation_user_list);
		$not_access_conversation_map_list = array_merge($response["not_access_conversation_map_list"], $not_access_conversation_map_list);

		return [
			"users_by_conversation"            => $conversation_user_list,
			"not_access_conversation_map_list" => $not_access_conversation_map_list,
		];
	}

	// подготавливаем данные
	#[ArrayShape(["users_by_thread_map" => "array", "not_access_parent_meta_list" => "array"])]
	protected static function _doPrepareData(array $data_packet, array $meta_list_by_source_parent_rel):array {

		// получаем список юзеров, раскиданных по сущностям source_parent_rel, и список тех диалогов, которые недоступны для текущего пользователя
		$users_by_conversation            = $data_packet["users_by_conversation"];
		$not_access_conversation_map_list = $data_packet["not_access_conversation_map_list"];

		$output = [
			"users_by_thread_map"         => [],
			"not_access_parent_meta_list" => [],
		];

		// раскидываем юзеров по тредам, и собираем список мет тредов, доступ к родителям-диалогам которых недоступен
		foreach ($meta_list_by_source_parent_rel as $parent_meta_map => $meta_list) {

			foreach ($meta_list as $meta_row) {

				// если диалог имеется в том списке, где были получены пользователи диалога
				if (isset($users_by_conversation[$parent_meta_map])) {

					$users = self::_makeThreadMetaUsersFromConversationUsers($meta_row["users"], $users_by_conversation[$parent_meta_map]);

					// закрепляем юзеров за тредом
					$output["users_by_thread_map"][$meta_row["thread_map"]] = $users;
				}
			}

			// если диалог среди недоступных для пользователя
			if (in_array($parent_meta_map, $not_access_conversation_map_list)) {
				$output["not_access_parent_meta_list"] = array_merge($output["not_access_parent_meta_list"], $meta_list);
			}
		}

		return $output;
	}

	// получить участников сущности, на основе которой строится бизнес логика треда
	public static function getUsers(array $meta_row, int $user_id):array {

		// получаем указатели на родительскую сущность
		$source_parent_map  = Type_Thread_SourceParentRel::getMap($meta_row["source_parent_rel"]);
		$source_parent_type = Type_Thread_SourceParentRel::getType($meta_row["source_parent_rel"]);

		// в зависимости от типа родительской сущности треда
		switch ($source_parent_type) {

			case SOURCE_PARENT_ENTITY_TYPE_CONVERSATION:

				$parent_users = self::_getUsersFromConversation($source_parent_map, $user_id, $meta_row);
				return self::_makeThreadMetaUsersFromConversationUsers($meta_row["users"], $parent_users);

			default:
				throw new ParseFatalException("Parent meta type $source_parent_type is not supported");
		}
	}

	// получаем юзеров из диалога
	protected static function _getUsersFromConversation(string $conversation_map, int $user_id, array $meta_row):array {

		try {
			$response = Gateway_Socket_Conversation::getUsers($user_id, $conversation_map);
		} catch (Gateway_Socket_Exception_Conversation_IsBlockedOrDisabled $e) {
			$meta_row["is_readonly"] = true;
			throw new cs_Conversation_IsBlockedOrDisabled($e->getExtra()["allow_status"], $meta_row);
		}

		return $response["users"];
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	// получить ключ для обращения в кэш
	protected static function _getKey(string $source_parent_map):string {

		return __CLASS__ . "_" . $source_parent_map . "_" . self::_CACHE_VERSION;
	}

	// получаем users для меты треда из меты родительской сущности
	protected static function _makeThreadMetaUsersFromConversationUsers(array $thread_meta_users, array $parent_users):array {

		$output = [];

		foreach ($parent_users as $v) {

			$user_id   = $v["user_id"];

			// устанавливаем по умолчанию количество скрытых = 0
			$count_hidden_message = 0;

			// получаем количество скрытых сообщений пользователя если пользователь есть в массиве
			if (isset($thread_meta_users[$user_id])) {
				$count_hidden_message = Type_Thread_Meta_Users::getCountHiddenMessage($thread_meta_users[$user_id]);
			}

			$access_mask   = THREAD_MEMBER_ACCESS_ALL;

			if (in_array($v["role"], self::CONVERSATION_MANAGED_ROLES)) {
				$access_mask |= THREAD_MEMBER_ACCESS_MANAGE;
			}

			// формируем структуру
			$output[$user_id] = Type_Thread_Meta_Users::initUserSchema($access_mask, $count_hidden_message);
		}

		return $output;
	}
}
