<?php

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * для работы с rabbit
 * класс для управления очередью
 * хорошие примеры почитать:
 * http://ruseller.com/lessons.php?rub=37&id=2172
 * https://habrahabr.ru/post/236221/
 */
class Rabbit {

	//
	protected const _DELIVERY_MODE = AMQPMessage::DELIVERY_MODE_NON_PERSISTENT;
	protected const _DURABLE       = false;      // храним очердь на диске
	protected const _EXCHANGE_TYPE = "fanout";

	/** @property AMQPStreamConnection */
	protected ?AMQPStreamConnection $_connection          = null; // храним экземпляр соединения
	protected array                 $_declared_queue_list = [];

	protected string $postfix_queue = "";

	protected ?\PhpAmqpLib\Channel\AMQPChannel $_channel = null; // храним экземпляр канала

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

		$queue_name = static::_getQueue($queue_name);

		// проверяем существует ли очередь, если нет то создаем
		$this->_declareQueueIfNeed($queue_name);
	}

	// отвязываем очередь
	public function unbindQueueFromExchange(string $queue_name, string $exchange_name):void {

		$queue_name = static::_getQueue($queue_name);

		// создаем очередь если ее нет
		$this->_declareQueueIfNeed($queue_name);

		// создаем exchange если его нет (мало ли)
		$this->_channel->exchange_declare($exchange_name, self::_EXCHANGE_TYPE, false, self::_DURABLE, false, false);

		$this->_channel->queue_unbind($queue_name, $exchange_name);
	}

	// сколько сообщений в очереди
	public function getQueueSize(string $queue_name):int {

		$info = $this->_channel->queue_declare($queue_name, false, self::_DURABLE, false, false);
		return $info[1] ?? 0;
	}

	/**
	 * отправить сообщение
	 *
	 * @param string $queue_name
	 * @param        $txt
	 *
	 * @mixed
	 */
	public function sendMessage(string $queue_name, $txt):void {

		$queue_name = static::_getQueue($queue_name);
		$this->_declareQueueIfNeed($queue_name);

		if (is_array($txt)) {
			$txt = toJson($txt);
		}

		$msg = new AMQPMessage($txt, [
			"delivery_mode" => self::_DELIVERY_MODE,
		]);
		$this->_channel->basic_publish($msg, "", $queue_name);
	}

	/**
	 * отправляет сообщение на все очереди подключенные к обменнику amq.fanout
	 *
	 * @param string $exchange_name
	 * @param        $txt
	 *
	 * @mixed
	 */
	public function sendMessageToExchange(string $exchange_name, $txt):void {

		$exchange_name = static::_getQueue($exchange_name);

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

	/**
	 * получить сообщение
	 *
	 * @param string   $queue_name
	 * @param          $exchange_name
	 * @param callable $callback
	 * @param int      $max_queue_size
	 *
	 * @mixed
	 */
	public function waitMessages(string $queue_name, $exchange_name, callable $callback, int $max_queue_size = 5, int $timeout = 0):void {

		$queue_name = static::_getQueue($queue_name);

		$this->_declareQueueIfNeed($queue_name);

		// если exchange_name не пустой, то биндим
		if (!is_null($exchange_name)) {

			$this->bindQueueToExchange($queue_name, $exchange_name);
		}

		$this->_channel->basic_qos(null, $max_queue_size, null);

		// потребляем очередь
		try {
			$this->_doCallbackLoop($queue_name, $callback, $timeout);
		} catch (AMQPTimeoutException) {
			// ничего не делаем
		}
	}

	/**
	 * получить только одно сообщение и отключится
	 *
	 * @param string $queue_name
	 *
	 * @return mixed
	 */
	public function getOneMessage(string $queue_name):mixed {

		$queue_name = static::_getQueue($queue_name);
		$response   = $this->_channel->basic_get($queue_name, true)->body;

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
	protected function _doCallbackLoop(string $queue_name, callable $callback, int $timeout = 0):void {

		$this->_channel->basic_consume($queue_name, $queue_name, false, false, false, false,
			function(AMQPMessage $msg) use ($callback, $queue_name) {

				// полезная работа
				$message = $msg->body;

				if (substr($message, 0, 1) == "[" || substr($message, 0, 1) == "{") {
					$message = fromJson($message);
				}

				// сообщить что очередь можно удалить
				$msg->getChannel()->basic_ack($msg->getDeliveryTag());

				$result = $callback($message);

				if ($result == "die") {
					$this->_channel->basic_cancel($queue_name);
				}
			}
		);

		while (count($this->_channel->callbacks)) {
			$this->_channel->wait(null, false, $timeout);
		}
	}

	/**
	 * очистить очередь
	 *
	 * @param string $queue_name
	 *
	 * @return void
	 */
	public function purgeQueue(string $queue_name):void {

		$queue_name = static::_getQueue($queue_name);

		$this->_channel->queue_purge($queue_name, true);
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

	// получаем очередь
	public function setPostfixQueue(string $prefix):string {

		return $this->postfix_queue = $prefix;
	}

	// получаем очередь
	protected function _getQueue(string $queue_name):string {

		if (mb_strlen($this->postfix_queue) > 0) {
			return $queue_name . "_" . $this->postfix_queue;
		}
		return $queue_name;
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
}
