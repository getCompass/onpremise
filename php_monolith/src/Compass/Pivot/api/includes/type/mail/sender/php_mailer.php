<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

/**
 * класс для отправки писем через php-mailer
 * @package Compass\Pivot
 */
class Type_Mail_Sender_PhpMailer extends Type_Mail_Sender_AbstractProvider {

	public const ENCRYPTION_TLS = "tls";
	public const ENCRYPTION_SSL = "ssl";

	/** @var int уровень дебага */
	protected int $_debug_level = SMTP::DEBUG_OFF;

	/** @var string кодировка */
	protected const _CHARSET = PHPMailer::CHARSET_UTF8;

	/** @var PHPMailer клиент для отправки письма */
	protected PHPMailer $_mailer;

	/**
	 * @noinspection PhpMissingParentConstructorInspection
	 */
	public function __construct(string $host, int $port, string $encryption, string $username, string $password, string $from_address, string $from_name) {

		if (!in_array($encryption, [self::ENCRYPTION_TLS, self::ENCRYPTION_SSL])) {
			throw new ParseFatalException("passed incorrect encryption [$encryption] parameter");
		}

		$mailer            = new PHPMailer(true);
		$mailer->SMTPDebug = $this->_debug_level;
		$mailer->isSMTP();
		$mailer->CharSet    = self::_CHARSET;
		$mailer->Host       = $host;
		$mailer->SMTPAuth   = true;
		$mailer->Username   = $username;
		$mailer->Password   = $password;
		$mailer->SMTPSecure = $encryption;
		$mailer->Port       = $port;
		$mailer->setFrom($from_address, $from_name);
		$mailer->isHTML(true);

		$this->_mailer = $mailer;
	}

	/**
	 * инициализируем объект класса
	 *
	 * @return static
	 */
	public static function init(PHPMailer $mailer):self {

		$output          = new self("", "", "", "", "", "", "");
		$output->_mailer = $mailer;

		return $output;
	}

	/**
	 * устанавливаем дебаг левел
	 *
	 * @param int $debug_level
	 */
	public function setDebugLevel(int $debug_level):self {

		$this->_debug_level = $debug_level;

		return $this;
	}

	/**
	 * отправляем письмо
	 *
	 * @throws Exception
	 */
	public function send(string $subject, string $body_html_content, string $receiver_address, string $receiver_name = ""):bool {

		$this->_clearMailer();

		$this->_mailer->addAddress($receiver_address, $receiver_name);
		$this->_mailer->Subject = $subject;
		$this->_mailer->Body    = $body_html_content;

		try {
			$result = $this->_mailer->send();
		} catch (Exception|\Exception $e) {

			Type_System_Admin::log("mail_sender", ["message" => "mail sending failed", "exception" => $e->getMessage()]);
			return false;
		}

		return $result;
	}

	/**
	 * Очищаем клиент отправителя
	 */
	protected function _clearMailer():void {

		$this->_mailer->clearAddresses();
		$this->_mailer->clearAllRecipients();
		$this->_mailer->clearAttachments();
		$this->_mailer->clearBCCs();
		$this->_mailer->clearCCs();
		$this->_mailer->clearCustomHeaders();
		$this->_mailer->clearReplyTos();
		$this->_mailer->Subject = "";
		$this->_mailer->Body    = "";
	}

}