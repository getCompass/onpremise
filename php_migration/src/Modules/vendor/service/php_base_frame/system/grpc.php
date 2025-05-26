<?php

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;

/**
 * класс для работы с grpc в проекте
 * реализует в себе общение по протоколом grpc
 */
class Grpc {

	/** @var \Grpc\BaseStub|null */
	protected ?\Grpc\BaseStub $_grpc_connection = null;

	protected string $_key            = "";
	protected string $_classname      = "";
	protected string $_ca_certificate = "";
	protected string $_token          = "";

	// -------------------------------------------------------
	// gRPC
	// -------------------------------------------------------

	protected const _GRPC_CONNECTION_TIMEOUT = 1 * 400000; // равно 0.4 от секунды (400ms) — gRPC требует значение в микросекундах

	// возвращает grpc клиент для соединения
	public function __construct(string $host, string $port, string $ca_certificate, string $token, string $classname) {

		$this->_key            = "$host:$port";
		$this->_classname      = $classname;
		$this->_ca_certificate = $ca_certificate;
		$this->_token          = $token;
		$this->_openConnection();
	}

	// открываем коннект
	protected function _openConnection():void {

		$credentials = $this->_ca_certificate !== "" ? \Grpc\ChannelCredentials::createSsl($this->_ca_certificate) : \Grpc\ChannelCredentials::createInsecure();

		// создаем объект соединения
		/** @var \Grpc\BaseStub */
		$client = new $this->_classname($this->_key, [
			"grpc_target_persist_bound"       => 1,
			"grpc.max_send_message_length"    => 1024 * 1024 * 10,
			"grpc.max_receive_message_length" => 1024 * 1024 * 10,
			"credentials"                     => $credentials,
			"update_metadata"                 => function(array $metadata) {

				if ($this->_token !== "") {
					$metadata["Authorization"] = [$this->_token];
				}

				return $metadata;
			},
		]);

		$this->_grpc_connection = $client;
	}

	// при удалении объекта
	public function __destruct() {

		// закрываем все соединения
		self::end();
	}

	// исполняется при завершении работы скрипта, для закрытия всех соединений
	public function end():void {
	}

	// выполняет любой запрос к tcp демону на go
	public function callGrpc(string $method_name, \Google\Protobuf\Internal\Message $request, int $error_count = 0):array {

		// проверяем, что передано корректное название метода для обращения
		// и оно описано в классе сервиса
		if (!method_exists($this->_grpc_connection, $method_name)) {
			throw new ParseFatalException(__METHOD__ . ": passed method is not declared in service class");
		}

		$this->_waitReady();

		// делаем запрос
		[$response, $status] = $this->_grpc_connection->$method_name($request)->wait();

		// если достигли таймаута
		if ($status->code == Grpc\STATUS_DEADLINE_EXCEEDED) {
			throw new BusFatalException("request timeout exceeded in " . __METHOD__);
		}

		// если достигли таймаута
		if ($status->code == Grpc\STATUS_UNAVAILABLE) {

			$this->_grpc_connection->close();
			$error_count++;

			if ($error_count <= 1) {

				$this->_openConnection();
				return $this->callGrpc($method_name, $request, $error_count);
			}
		}

		return [$response, $status];
	}

	// ждем готовности канала
	protected function _waitReady():void {

		try {
			$this->_grpc_connection->waitForReady(self::_GRPC_CONNECTION_TIMEOUT);
		} catch (Exception) {
		}
	}
}
