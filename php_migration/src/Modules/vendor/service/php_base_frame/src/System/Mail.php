<?php

namespace BaseFrame\System;

use BaseFrame\Exception\Domain\InvalidMail;

/**
 * Класс для работы с электронными почтовыми адресами
 */
class Mail {

	private string $_mail; // значение почты

	/**
	 * @param string $mail
	 *
	 * @throws InvalidMail
	 */
	public function __construct(string $mail) {

		$this->_mail = $this->_validate($mail);
	}

	/**
	 * Валидируем почту
	 *
	 * @param string $mail
	 *
	 * @return string
	 * @throws InvalidMail
	 */
	private function _validate(string $mail):string {

		// нормализуем почту
		$mail = $this->_normalize($mail);

		if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
			throw new InvalidMail("invalid mail");
		}

		return $mail;
	}

	/**
	 * Приводим почту в нормальный вид
	 *
	 * @param string $mail
	 *
	 * @return string
	 */
	private function _normalize(string $mail):string {

		return mb_strtolower(trim($mail));
	}

	/**
	 * Вернуть нормализованную почту
	 *
	 * @return string
	 */
	public function mail():string {

		return $this->_mail;
	}

	/**
	 * Вернуть часть с доменом из почты
	 *
	 * @return string
	 */
	public function getDomain():string {

		return mb_substr($this->_mail, strpos($this->_mail, "@") + 1);
	}

	/**
	 * Обфусцировать почту
	 *
	 * @return string
	 */
	public function obfuscate():string {

		$parts = explode("@", $this->_mail);

		$name   = $parts[0];
		$domain = $parts[1];

		$name_length = mb_strlen($name);

		if ($name_length == 1) {
			$name_mask = "*";
		} elseif ($name_length == 2) {
			$name_mask = "*" . $name[1];
		} elseif ($name_length == 3) {
			$name_mask = $name[0] . "*" . $name[2];
		} else {
			$name_mask = mb_substr($name, 0, 2) . str_repeat("*", $name_length - 3) . mb_substr($name, -1);
		}

		return $name_mask . "@" . $domain;
	}
}