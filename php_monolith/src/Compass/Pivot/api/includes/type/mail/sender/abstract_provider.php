<?php

namespace Compass\Pivot;

/**
 * класс описывающий абстрактного провайдера отправки писем
 * @package Compass\Pivot
 */
abstract class Type_Mail_Sender_AbstractProvider {

	public function __construct(
		protected string $_host,
		protected int    $_port,
		protected string $_encryption,
		protected string $_username,
		protected string $_password,
		protected string $_from_address,
		protected string $_from_name
	) {
	}

	/**
	 * отправляем письмо
	 *
	 * @return bool
	 */
	abstract public function send(string $subject, string $body_html_content, string $receiver_address, string $receiver_name = ""):bool;
}