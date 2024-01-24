<?php

namespace Compass\Company;

/**
 * Класс для работы с company
 */
class Gateway_Bus_Company_Main {

	protected const _QUEUE_NAME    = GO_COMPANY_QUEUE;
	protected const _EXCHANGE_NAME = GO_COMPANY_EXCHANGE;

	/**
	 * делаем grpc запрос к указанному методу с переданными данными
	 *
	 * @param string                            $method_name
	 * @param \Google\Protobuf\Internal\Message $request
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 */
	protected static function _doCallGrpc(string $method_name, \Google\Protobuf\Internal\Message $request):array {

		return ShardingGateway::rpc("company", \CompanyGrpc\companyClient::class)->callGrpc($method_name, $request);
	}
}