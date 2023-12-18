<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;

/**
 * класс для работы с rating
 */
class Gateway_Bus_Rating_Main {

	/**
	 * Добавить время ответа пользователя
	 *
	 * @param string $conversation_key
	 * @param int    $sender_user_id
	 * @param array  $receiver_user_id_list
	 * @param int    $sent_at
	 * @param string $local_sent_at
	 *
	 * @return array
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 */
	public static function updateConversationAnswerState(string $conversation_key, int $sender_user_id, array $receiver_user_id_list, int $sent_at, string $local_sent_at):array {

		$request = new \RatingGrpc\RatingUpdateConversationAnswerStateRequestStruct([
			"conversation_key"      => $conversation_key,
			"sender_user_id"        => $sender_user_id,
			"receiver_user_id_list" => $receiver_user_id_list,
			"sent_at"               => $sent_at,
			"space_id"              => COMPANY_ID,
			"local_sent_at"         => $local_sent_at,
		]);
		[$response, $status] = self::_doCallGrpc("RatingUpdateConversationAnswerState", $request);
		if ($status->code !== \Grpc\STATUS_OK) {
			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}

		return [
			$response->getSpaceId(),
			$response->getConversationKey(),
			$response->getAnswerTime(),
			$response->getCreatedAt(),
			$response->getMicroConversationStartAt(),
			$response->getMicroConversationEndAt(),
		];
	}

	/**
	 * Добавить информацию о полученных сообщениях получателям
	 *
	 * @param string $conversation_key
	 * @param int    $sender_user_id
	 * @param array  $receiver_user_id_list
	 * @param int    $sent_at
	 * @param string $local_sent_at
	 *
	 * @return void
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 */
	public static function updateConversationAnswerStateForReceivers(string $conversation_key, int $sender_user_id, array $receiver_user_id_list, int $sent_at, string $local_sent_at):void {

		$request = new \RatingGrpc\RatingUpdateConversationAnswerStateForReceiversRequestStruct([
			"conversation_key"      => $conversation_key,
			"sender_user_id"        => $sender_user_id,
			"receiver_user_id_list" => $receiver_user_id_list,
			"sent_at"               => $sent_at,
			"space_id"              => COMPANY_ID,
			"local_sent_at"         => $local_sent_at,
		]);
		[$_, $status] = self::_doCallGrpc("RatingUpdateConversationAnswerStateForReceivers", $request);
		if ($status->code !== \Grpc\STATUS_OK) {
			throw new BusFatalException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}
	}

	/**
	 * Добавить экранное время пользователю
	 *
	 * @param int    $user_id
	 * @param string $local_online_at
	 * @param int    $screen_time
	 *
	 * @return void
	 * @throws BusFatalException
	 * @throws ParseFatalException
	 */
	public static function addScreenTime(int $user_id, string $local_online_at, int $screen_time):void {

		$request = new \RatingGrpc\RatingAddScreenTimeRequestStruct([
			"space_id"        => COMPANY_ID,
			"user_id"         => $user_id,
			"screen_time"     => $screen_time,
			"local_online_at" => $local_online_at,
		]);
		[$_, $status] = self::_doCallGrpc("RatingAddScreenTime", $request);
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

		return ShardingGateway::rpc("rating", \RatingGrpc\ratingClient::class)->callGrpc($method_name, $request);
	}
}