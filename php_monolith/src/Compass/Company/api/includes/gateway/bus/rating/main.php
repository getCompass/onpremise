<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;

/**
 * Класс для работы с rating
 */
class Gateway_Bus_Rating_Main {

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