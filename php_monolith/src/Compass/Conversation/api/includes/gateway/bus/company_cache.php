<?php

namespace Compass\Conversation;

/**
 * класс для работы с go_event - микросервисом асинхронных задач
 */
class Gateway_Bus_CompanyCache extends \CompassApp\Gateway\Bus\CompanyCache {

	// делаем grpc запрос к указанному методу с переданными данными
	protected static function _doCallGrpc(string $method_name, mixed $request):array {

		return ShardingGateway::rpc("company_cache", \CompanyCacheGrpc\companyCacheClient::class)->callGrpc($method_name, $request);
	}
}