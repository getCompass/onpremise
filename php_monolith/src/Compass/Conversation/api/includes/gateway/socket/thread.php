<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * класс-интерфейс для работы с модулей thread
 */
class Gateway_Socket_Thread extends Gateway_Socket_Default {

	// чистим в треде meta cache
	public static function doClearMetaThreadCache(string $source_parent_map):void {

		$ar_post = [
			"source_parent_map" => $source_parent_map,
		];
		self::doCall("threads.doClearMetaThreadCache", $ar_post);
	}

	// чистим кэш родительского сообщения треда
	public static function doClearParentMessageCache(string $parent_message_map):void {

		$ar_post = [
			"parent_message_map" => $parent_message_map,
		];
		self::doCall("threads.doClearParentMessageCache", $ar_post);
	}

	// чистим кэш родительского сообщения тредов
	public static function doClearParentMessageListCache(array $parent_message_map_list):void {

		$ar_post = [
			"parent_message_map_list" => $parent_message_map_list,
		];
		self::doCall("threads.doClearParentMessageListCache", $ar_post);
	}

	// чистим кэш родительского сообщения тредов для списка пользователей
	public static function clearConversationForUserIdList(string $conversation_map, int $clear_until, array $user_id_list):void {

		$ar_post = [
			"conversation_map" => $conversation_map,
			"clear_until"      => $clear_until,
			"user_id_list"     => $user_id_list,

		];
		self::doCall("threads.clearConversationForUserIdList", $ar_post);
	}

	/**
	 * Получаем треды
	 */
	public static function getThreadListForFeed(int $user_id, array $need_thread_map_list):array {

		$ar_post = [
			"thread_map_list" => $need_thread_map_list,
		];
		[$status, $response] = self::doCall("threads.getThreadListForFeed", $ar_post, $user_id);

		return [$response["thread_meta_list"], $response["thread_menu_list"]];
	}

	/**
	 * Получаем треды батчингом
	 */
	public static function getThreadListForBatchingFeed(int $user_id, array $need_thread_map_list, array $conversation_dynamic_list, array $conversation_meta_list):array {

		// -------------------------------------------------------
		// !!! ВНИМАНИЕ
		// сокет используется только для метода feed.getBatchingThreads
		// -------------------------------------------------------

		$ar_post = [
			"thread_map_list"                          => $need_thread_map_list,
			"conversation_dynamic_by_conversation_map" => $conversation_dynamic_list,
			"conversation_meta_by_conversation_map"    => $conversation_meta_list,
		];
		[$status, $response] = self::doCall("threads.getThreadListForBatchingFeed", $ar_post, $user_id);

		return [$response["thread_meta_list"], $response["thread_menu_list"]];
	}

	/**
	 * Добавляем тред к заявке найма
	 */
	public static function addThreadForHiringRequest(int $creator_user_id, int $request_id, int $is_company_creator, bool $is_need_thread_attach = false):string {

		$ar_post = [
			"creator_user_id"       => $creator_user_id,
			"request_id"            => $request_id,
			"is_company_creator"    => $is_company_creator,
			"is_need_thread_attach" => $is_need_thread_attach ? 1 : 0,
		];
		[$status, $response] = self::doCall("threads.addThreadForHiringRequest", $ar_post, $creator_user_id);

		return $response["thread_map"];
	}

	/**
	 * Добавляем тред к заявке увольнения
	 */
	public static function addThreadForDismissalRequest(int $creator_user_id, int $request_id, bool $is_need_thread_attach = false):string {

		$ar_post = [
			"creator_user_id"       => $creator_user_id,
			"request_id"            => $request_id,
			"is_need_thread_attach" => $is_need_thread_attach ? 1 : 0,
		];
		[$status, $response] = self::doCall("threads.addThreadForDismissalRequest", $ar_post, $creator_user_id);

		return $response["thread_map"];
	}

	// получаем подпись из массива параметров
	public static function doCall(string $method, array $params, int $user_id = 0):array {

		// формируем сообщение
		$ar_post  = [
			"method"        => $method,
			"company_id"    => COMPANY_ID,
			"user_id"       => $user_id,
			"sender_module" => CURRENT_MODULE,
			"json_params"   => toJson($params),
			"signature"     => "",
		];
		$response = \Application\Entrypoint\Socket::processRequest("Thread", $method, "thread", $ar_post, true);
		$response = fromJson(toJson($response));

		// проверяем, пришел ли нормальный запрос
		if (!isset($response["status"])
			|| !in_array($response["status"], ["ok", "error"])
			|| ($response["status"] === "error" && !isset($response["response"]["error_code"]))) {

			throw new ReturnFatalException($method . ": socket call returns bad response");
		}

		return [$response["status"], $response["response"]];
	}
}
