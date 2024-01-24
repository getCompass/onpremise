<?php

namespace Compass\Speaker;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;

/**
 * класс для работы с rating
 */
class Gateway_Bus_Rating_Main {

	/**
	 * Закрываем микро-диалог
	 *
	 * @param string $conversation_key
	 * @param int    $sender_user_id
	 * @param array  $receiver_user_id_list
	 *
	 * @return void
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 */
	public static function closeMicroConversation(string $conversation_key, int $sender_user_id, array $receiver_user_id_list):void {

		$request = new \RatingGrpc\RatingCloseMicroConversationRequestStruct([
			"conversation_key"      => $conversation_key,
			"sender_user_id"        => $sender_user_id,
			"receiver_user_id_list" => $receiver_user_id_list,
			"space_id"              => COMPANY_ID,
		]);
		[$_, $status] = self::_doCallGrpc("RatingCloseMicroConversation", $request);
		if ($status->code !== \Grpc\STATUS_OK) {
			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}
	}

	/**
	 * Инкрементим количество действий
	 *
	 * @param int    $user_id
	 * @param string $conversation_map
	 * @param string $action
	 * @param bool   $is_human
	 *
	 * @return void
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 */
	public static function incActionCount(int $user_id, string $conversation_map, string $action, bool $is_human):void {

		$request = new \RatingGrpc\RatingIncActionCountRequestStruct([
			"space_id"         => COMPANY_ID,
			"user_id"          => $user_id,
			"conversation_map" => $conversation_map,
			"action"           => $action,
			"is_human"         => (int) $is_human,
		]);
		[$_, $status] = self::_doCallGrpc("RatingIncActionCount", $request);
		if ($status->code !== \Grpc\STATUS_OK) {
			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}
	}

	/**
	 * делаем grpc запрос к указанному методу с переданными данными
	 *
	 * @return array
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 */
	protected static function _doCallGrpc(string $method_name, \Google\Protobuf\Internal\Message $request):array {

		$connection = ShardingGateway::rpc("rating", \RatingGrpc\ratingClient::class);

		return $connection->callGrpc($method_name, $request);
	}
}