<?php

namespace Compass\Speaker;

/**
 * класс для работы с company
 */
class Gateway_Bus_Company_Main {

	protected const _QUEUE_NAME    = "go_company_queue";
	protected const _EXCHANGE_NAME = "go_company_exchange";

	/**
	 * делаем grpc запрос к указанному методу с переданными данными
	 *
	 * @throws \busException
	 * @throws \parseException
	 */
	protected static function _doCallGrpc(string $method_name, \Google\Protobuf\Internal\Message $request):array {

		$connection = ShardingGateway::rpc("company", \CompanyGrpc\companyClient::class);

		return $connection->callGrpc($method_name, $request);
	}
}