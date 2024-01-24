<?php

namespace Compass\FileBalancer;

// для работы с rabbit
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// класс для управления учередью
// хорошие примеры почитать:
// http://ruseller.com/lessons.php?rub=37&id=2172
// https://habrahabr.ru/post/236221/

class Rabbit {

	//
	protected const _DELIVERY_MODE = AMQPMessage::DELIVERY_MODE_PERSISTENT;
	protected const _DURABLE       = true;      // храним очердь на диске
	protected const _EXCHANGE_TYPE = "fanout";

	/** @property AMQPStreamConnection */
	protected $_connection          = null; // храним экземпляр соединения
	protected $_declared_queue_list = [];

	/** @property AMQPChannel */
	protected $_channel = null; // храним экземпляр канала

	public function __construct(string $host, string $port, string $user, string $password) {

		// устанавилваем соединение и получаем канал
		$this->_connection = new AMQPStreamConnection($host, $port, $user, $password);
		$this->_channel    = $this->_connection->channel();
	}

	// --------------------------------------------------
	// РАБОЧИЕ ФУНКЦИИ
	// --------------------------------------------------

	// создаем очередь
	public function declareQueue(string $queue_name):void {

		// проверяем существует ли очередь, если нет то создаем
		$this->_declareQueueIfNeed($queue_name);
	}

	// биндим очередь к обменнику
	public function bindQueueToExchange(string $queue_name, string $exchange_name):void {

		// создаем очередь если ее нет
		$this->_declareQueueIfNeed($queue_name);

		// создаем exchange если его нет
		$this->_channel->exchange_declare($exchange_name, self::_EXCHANGE_TYPE, false, self::_DURABLE, false, false);

		// биндим
		$this->_channel->queue_bind($queue_name, $exchange_name);
	}

	// отвязываем очередь
	public function unbindQueueFromExchange(string $queue_name, string $exchange_name):void {

		// создаем очередь если ее нет
		$this->_declareQueueIfNeed($queue_name);

		// создаем exchange если его нет (мало ли)
		$this->_channel->exchange_declare($exchange_name, self::_EXCHANGE_TYPE, false, self::_DURABLE, false, false);

		$this->_channel->queue_unbind($queue_name, $exchange_name);
	}

	// сколько сообщений в очереди
	public function getQueueSize(string $queue_name):int {

		$info = $this->_channel->queue_declare($queue_name, false, self::_DURABLE, false, false);
		return isset($info[1]) ? $info[1] : 0;
	}

	// отправить сообщение
	// @mixed
	public function sendMessage(string $queue_name, $txt):void {

		$this->_declareQueueIfNeed($queue_name);

		if (is_array($txt)) {
			$txt = toJson($txt);
		}

		$msg = new AMQPMessage($txt, [
			"delivery_mode" => self::_DELIVERY_MODE,
		]);
		$this->_channel->basic_publish($msg, "", $queue_name);
	}

	// отправляет сообщение на все очереди подключенные к обменнику amq.fanout
	// @mixed
	public function sendMessageToExchange(string $exchange_name, $txt):void {

		// создаем очередь
		$this->_channel->exchange_declare($exchange_name, self::_EXCHANGE_TYPE, false, self::_DURABLE, false, false);

		if (is_array($txt)) {
			$txt = toJson($txt);
		}

		$msg = new AMQPMessage($txt, [
			"delivery_mode" => self::_DELIVERY_MODE,
		]);
		$this->_channel->basic_publish($msg, $exchange_name, "");
	}

	// получить сообщение
	// @mixed
	public function waitMessages(string $queue_name, $exchange_name, callable $callback, int $max_queue_size = 5):void {

		$this->_declareQueueIfNeed($queue_name);

		// если exchange_name не пустой, то биндим
		if (!is_null($exchange_name)) {

			$this->bindQueueToExchange($queue_name, $exchange_name);
		}

		$this->_channel->basic_qos(null, $max_queue_size, null);

		// потребляем очередь
		$this->_doCallbackLoop($queue_name, $callback);
	}

	// получить только одно сообщение и отключится
	// @mixed
	public function getOneMessage(string $queue_name) {

		$response = $this->_channel->basic_get($queue_name, true)->body;

		// проверяем на пустоту
		if (is_null($response)) {
			return [];
		}

		return $response;
	}

	// закрываем все соединения
	public function closeAll():void {

		$this->_channel->close();
		$this->_connection->close();
	}

	// --------------------------------------------------
	// PROTECTED
	// --------------------------------------------------

	// потребляем очередь
	// @mixed
	protected function _doCallbackLoop(string $queue_name, callable $callback):void {

		$this->_channel->basic_consume($queue_name, $queue_name, false, false, false, false, function($msg) use ($callback, $queue_name) {

			// полезная работа
			$message = $msg->body;

			if (substr($message, 0, 1) == "[" || substr($message, 0, 1) == "{") {
				$message = fromJson($message);
			}

			// сообщить что очередь можно удалить
			$msg->delivery_info["channel"]->basic_ack($msg->delivery_info["delivery_tag"]);

			$result = $callback($message);

			if ($result == "die") {
				$this->_channel->basic_cancel($queue_name);
			}
		});

		while (count($this->_channel->callbacks)) {
			$this->_channel->wait();
		}
	}

	// создаем очередь, если не существует
	protected function _declareQueueIfNeed(string $queue_name):void {

		// проверяем существуе ли очередь, если нет то создаем
		if (!isset($this->_declared_queue_list[$queue_name])) {

			// создаем очередь
			$this->_channel->queue_declare($queue_name, false, self::_DURABLE, false, false);

			// отмечаем, что создали очередь
			$this->_declared_queue_list[$queue_name] = 1;
		}
	}
}
