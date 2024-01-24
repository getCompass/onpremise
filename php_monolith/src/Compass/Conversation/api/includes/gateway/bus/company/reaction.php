<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;

/**
 * класс для работы с go_reaction - промежуточный микросервис между приложением PHP и MySQL базой, где хранятся реакции
 *
 * его главная задача накапливать в себе изменения (поставленные/убранные) реакций сообщения и с интервалом в N времени производить обновление
 * записей в базе
 */
class Gateway_Bus_Company_Reaction extends Gateway_Bus_Company_Main {

	// поставить реакцию от пользователя в диалоге
	public static function addInConversation(string $message_map, string $reaction_name, int $user_id, int $updated_at_ms, array $ws_user_list, array $ws_event_version_list):void {

		// формируем массив пост параметров для запроса
		$conversation_reaction = self::_makeConversationPostArray($message_map, $reaction_name, $user_id, $updated_at_ms, $ws_user_list, $ws_event_version_list);

		$request = new \CompanyGrpc\ReactionsAddInConversationRequestStruct([
			"conversation_reaction" => $conversation_reaction,
			"company_id"            => COMPANY_ID,
		]);
		[, $status] = self::_doCallGrpc("ReactionsAddInConversation", $request);
		if ($status->code !== \Grpc\STATUS_OK) {

			if ($status->code == 906) {
				throw new ReturnFatalException("db in reaction not working");
			}
			if ($status->code == 907) {
				throw new cs_Message_ReactionLimit();
			}
			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}
	}

	// удалить реакцию от пользователя в диалоге
	public static function removeInConversation(string $message_map, string $reaction_name, int $user_id, int $updated_at_ms, array $ws_user_list, array $ws_event_version_list):void {

		// формируем массив пост параметров для запроса
		$conversation_reaction = self::_makeConversationPostArray($message_map, $reaction_name, $user_id, $updated_at_ms, $ws_user_list, $ws_event_version_list);

		$request = new \CompanyGrpc\ReactionsRemoveInConversationRequestStruct([
			"conversation_reaction" => $conversation_reaction,
			"company_id"            => COMPANY_ID,
		]);
		[, $status] = self::_doCallGrpc("ReactionsRemoveInConversation", $request);
		if ($status->code !== \Grpc\STATUS_OK) {
			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// формируем массив параметров для диалога
	protected static function _makeConversationPostArray(string $message_map, string $reaction_name, int $user_id, int $updated_at_ms, array $ws_user_list, array $event_version_list):\CompanyGrpc\ConversationReactionStruct {

		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);
		$output           = [
			"conversation_map"   => $conversation_map,
			"message_map"        => $message_map,
			"shard_id"           => \CompassApp\Pack\Conversation::getShardId($conversation_map),
			"block_id"           => \CompassApp\Pack\Message\Conversation::getBlockId($message_map),
			"reaction_name"      => $reaction_name,
			"user_id"            => $user_id,
			"updated_at_ms"      => $updated_at_ms,
			"ws_user_list"       => toJson($ws_user_list),
			"event_version_list" => $event_version_list,
		];

		// конвертим версии событий
		$output["event_version_list"] = self::_convertEventVersionListToGrpcStructure($event_version_list);

		return new \CompanyGrpc\ConversationReactionStruct($output);
	}

	/**
	 * конвертируем event_version_list в структуру понятную grpc
	 *
	 * @param array $event_version_list
	 *
	 * @return \CompanyGrpc\EventVersionItem[]
	 */
	protected static function _convertEventVersionListToGrpcStructure(array $event_version_list):array {

		$output = [];
		foreach ($event_version_list as $event_version_item) {

			$output[] = new \CompanyGrpc\EventVersionItem([
				"version" => $event_version_item["version"],
				"data"    => toJson($event_version_item["data"]),
			]);
		}

		return $output;
	}
}
