<?php

namespace Compass\Federation;

/**
 * Класс шардинга для работы с rabbit
 */
class Gateway_Bus_Rabbit {

	/**
	 * отправляем сообщение в очередь
	 *
	 * @param string $queue_name
	 * @param array  $ar_post
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function sendMessage(string $queue_name, array $ar_post, int $company_id = 0):void {

		$rabbit = ShardingGateway::rabbit();
		$rabbit->setPostfixQueue($company_id % 10);
		$rabbit->sendMessage($queue_name, $ar_post);
	}

	/**
	 * отправляем сообщение в exchange
	 *
	 * @param string $exchange_name
	 * @param array  $ar_post
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function sendMessageToExchange(string $exchange_name, array $ar_post, int $company_id = 0):void {

		$rabbit = ShardingGateway::rabbit();
		$rabbit->setPostfixQueue($company_id % 10);
		$rabbit->sendMessageToExchange($exchange_name, $ar_post);;
	}
}
