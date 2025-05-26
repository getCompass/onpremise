<?php

namespace BaseFrame\Exception\Request;

use BaseFrame\Exception\RequestException;

/**
 * Пользователь заблокирован
 */
class BlockException extends RequestException {

	const HTTP_CODE = 423;

	private int $expire = 0;

	/**
	 * blockException constructor.
	 *
	 * @param string $message
	 * @param int    $expire
	 */
	public function __construct(string $message = "", int $expire = 0) {

		$this->expire = $expire;
		parent::__construct($message);
	}

	/**
	 * Получить время истечения блокировки
	 *
	 * @return int
	 */
	public function getExpire():int {

		return $this->expire;
	}

	/**
	 * Установить время истечения блокировки
	 *
	 * @param int $expire
	 */
	public function setExpire(int $expire):void {

		$this->expire = $expire;
	}
}