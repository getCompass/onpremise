<?php

namespace Compass\Conversation;

/**
 * класс для работы с company
 */
class Gateway_Bus_Company_Main {

	protected const _QUEUE_NAME    = GO_COMPANY_QUEUE;
	protected const _EXCHANGE_NAME = GO_COMPANY_EXCHANGE;

	/**
	 * делаем grpc запрос к указанному методу с переданными данными
	 *
	 * @throws \busException
	 * @throws \parseException
	 */
	protected static function _doCallGrpc(string $method_name, \Google\Protobuf\Internal\Message $request):array {

		return ShardingGateway::rpc("company", \CompanyGrpc\companyClient::class)->callGrpc($method_name, $request);
	}
}