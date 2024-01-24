<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * класс-интерфейс для работы с модулей profile
 */
class Gateway_Socket_Company extends Gateway_Socket_Default {

	// добавляем Требовательность в карточку пользователя
	public static function addExactingnessToEmployeeCard(int $sender_user_id, array $user_id_list, int $created_at = null):array {

		$created_at = is_null($created_at) ? time() : $created_at;
		$ar_post    = [
			"user_id_list" => $user_id_list,
			"created_at"   => $created_at,
		];
		[$status, $response] = self::doCall("employeecard.entity.addExactingness", $ar_post, $sender_user_id);

		// если сокет-запрос не вернул ok
		if ($status != "ok") {

			$txt = toJson($response);
			throw new ReturnFatalException("Socket request employeecard.entity.addExactingness status != ok. Response: {$txt}");
		}

		if (!isset($response["exactingness_id_list_by_user_id"])) {
			throw new ReturnFatalException("Not found expected response for socket-request employeecard.entity.addExactingness");
		}

		return [$response["exactingness_id_list_by_user_id"], $response["week_count"], $response["month_count"]];
	}

	// удаляем список сущностей карточки из карточки
	public static function deleteCardEntityOnEmployeeCard(string $entity_type, array $card_entity_id_list_by_receiver_id):void {

		$ar_post = [
			"entity_type"                   => $entity_type,
			"entity_id_list_by_receiver_id" => $card_entity_id_list_by_receiver_id,
		];
		[$status, $response] = self::doCall("employeecard.entity.deleteCardEntityList", $ar_post);

		// если сокет-запрос не вернул ok
		if ($status != "ok") {

			$txt = toJson($response);
			throw new ReturnFatalException("Socket request employeecard.entity.deleteCardEntityList status != ok. Response: {$txt}");
		}
	}

	// закрепляем message_map сообщений за каждой из требовательностей
	public static function setMessageMapListForExactingnessList(array $exactingness_data_list_by_user_id, int $created_at):void {

		$ar_post = [
			"exactingness_data_list_by_user_id" => $exactingness_data_list_by_user_id,
			"created_at"                        => $created_at,
		];
		[$status, $response] = self::doCall("employeecard.entity.setMessageMapListForExactingnessList", $ar_post);

		// если сокет-запрос не вернул ok
		if ($status != "ok") {

			$txt = toJson($response);
			throw new ReturnFatalException("Socket request employeecard.entity.setMessageMapListForExactingnessList status != ok. Response: {$txt}");
		}
	}

	/**
	 * Получает список id пользователей с ролями
	 *
	 * @param array $roles
	 *
	 * @return array
	 * @throws \returnException
	 */
	public static function getUserRoleList(array $roles):array {

		$ar_post = [
			"roles" => $roles,
		];

		[$status, $response] = self::doCall("company.member.getUserRoleList", $ar_post);

		// если сокет-запрос не вернул ok
		if ($status != "ok") {

			$txt = toJson($response);
			throw new ReturnFatalException("Socket request member.getUserRoleList status != ok. Response: {$txt}");
		}

		return $response["user_role"];
	}

	/**
	 * Получить значение конфига компании по ключу
	 *
	 * @param string $key
	 *
	 * @return array
	 * @throws \returnException
	 */
	public static function getConfigByKey(string $key):array {

		[$status, $response] = self::doCall("company.config.getConfigByKey", [
			"key" => $key,
		]);

		// если сокет-запрос не вернул ok
		if ($status != "ok") {

			$txt = toJson($response);
			throw new ReturnFatalException("Socket request member.getUserRoleList status != ok. Response: {$txt}");
		}

		return $response["config"];
	}

	/**
	 * получаем userbot_id бота по его user_id бота как пользователя
	 *
	 * @throws \returnException
	 */
	public static function getUserbotIdByUserId(int $user_id):string {

		[$status, $response] = self::doCall("company.userbot.getUserbotIdByUserId", [
			"user_id" => $user_id,
		]);

		if ($status != "ok") {
			throw new ReturnFatalException(__METHOD__ . ": socket company.userbot.getUserbotIdByUserId fail");
		}

		return $response["userbot_id"];
	}

	/**
	 * получаем статус бота по user_id бота как пользователя
	 *
	 * @throws \returnException
	 */
	public static function getUserbotStatusByUserId(int $user_id):int {

		[$status, $response] = self::doCall("company.userbot.getStatusByUserId", [
			"user_id" => $user_id,
		]);

		if ($status != "ok") {
			throw new ReturnFatalException(__METHOD__ . ": socket company.userbot.getStatusByUserId fail");
		}

		return $response["status"];
	}

	/**
	 * кикаем бота из группы
	 *
	 * @throws \returnException
	 */
	public static function kickUserbotFromGroup(int $user_id, string $conversation_map):string {

		[$status, $response] = self::doCall("company.userbot.kickFromGroup", [
			"user_id"          => $user_id,
			"conversation_map" => $conversation_map,
		]);

		if ($status != "ok") {
			throw new ReturnFatalException(__METHOD__ . ": socket company.userbot.kickFromGroup fail");
		}

		return $response["userbot_id"];
	}

	// получаем подпись из массива параметров
	public static function doCall(string $method, array $params, int $user_id = 0):array {

		// получаем url и подпись
		$url = self::_getSocketCompanyUrl("company");
		return self::_doCall($url, $method, $params, $user_id);
	}
}
