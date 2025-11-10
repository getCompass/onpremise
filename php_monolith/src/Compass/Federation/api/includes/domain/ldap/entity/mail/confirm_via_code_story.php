<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\DBShardingNotFoundException;
use BaseFrame\Exception\Gateway\QueryFatalException;
use BaseFrame\Exception\Gateway\RowDuplicationException;
use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * класс для работы с подтверждениями почты через код подтверждения
 * @package Compass\Federation
 */
class Domain_Ldap_Entity_Mail_ConfirmViaCodeStory {

	private const _STATUS_NOT_FINISHED = 0; // процесс подтверждения не закончен
	private const _STATUS_SUCCESS      = 1; // почта подтверждена успешно
	private const _STATUS_FAILED       = 2; // процесс подтверждения закончен неудачей

	public const MAX_ERROR_COUNT  = 7; // максимальное количество ошибок ввода кода
	public const MAX_RESEND_COUNT = 7; // максимальное количество переотправок кода

	/** список всех существующих шаблонов, нужно синхронизировать с php_pivot */
	private const _TEMPLATE_MAIL_AUTHORIZATION = "mail_authorization";
	private const _TEMPLATE_MAIL_ADD           = "mail_add";
	private const _TEMPLATE_MAIL_CHANGE        = "mail_change";

	private const _STAGE_TEMPLATE_MAIL_MAP = [
		Domain_Ldap_Entity_Mail_ConfirmStory::STAGE_CONFIRM_NEW_MAIL      => self::_TEMPLATE_MAIL_ADD,
		Domain_Ldap_Entity_Mail_ConfirmStory::STAGE_CONFIRM_CHANGING_MAIL => self::_TEMPLATE_MAIL_CHANGE,
		Domain_Ldap_Entity_Mail_ConfirmStory::STAGE_CONFIRM_CURRENT_MAIL  => self::_TEMPLATE_MAIL_AUTHORIZATION,
	];
	private const _RESEND_TIME             = 60;

	private ?string $_confirm_code = null;
	private bool    $_is_code_sent = false;

	public ?int   $id;
	public int    $status;
	public int    $resend_count;
	public int    $error_count;
	public int    $created_at;
	public int    $updated_at;
	public int    $next_resend_at;
	public int    $mail_confirm_story_id;
	public string $message_id;
	public string $code_hash;
	public string $mail;

	protected function __construct() {
	}

	/**
	 * Получить все попытки отправки кода
	 *
	 * @param int $mail_confirm_story_id
	 *
	 * @return self[]
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 */
	public static function getByMailConfirmStory(int $mail_confirm_story_id):array {

		$db_mail_confirm_via_code_story_list = Gateway_Db_LdapData_MailConfirmViaCodeStory::getByMailConfirmStoryId($mail_confirm_story_id);
		$mail_confirm_via_code_story_list    = [];

		foreach ($db_mail_confirm_via_code_story_list as $db_mail_confirm_via_code_story) {

			$mail_confirm_via_code_story                        = new self();
			$mail_confirm_via_code_story->id                    = $db_mail_confirm_via_code_story->id;
			$mail_confirm_via_code_story->status                = $db_mail_confirm_via_code_story->status;
			$mail_confirm_via_code_story->resend_count          = $db_mail_confirm_via_code_story->resend_count;
			$mail_confirm_via_code_story->error_count           = $db_mail_confirm_via_code_story->error_count;
			$mail_confirm_via_code_story->created_at            = $db_mail_confirm_via_code_story->created_at;
			$mail_confirm_via_code_story->updated_at            = $db_mail_confirm_via_code_story->updated_at;
			$mail_confirm_via_code_story->next_resend_at        = $db_mail_confirm_via_code_story->next_resend_at;
			$mail_confirm_via_code_story->mail_confirm_story_id = $db_mail_confirm_via_code_story->mail_confirm_story_id;
			$mail_confirm_via_code_story->message_id            = $db_mail_confirm_via_code_story->message_id;
			$mail_confirm_via_code_story->code_hash             = $db_mail_confirm_via_code_story->code_hash;
			$mail_confirm_via_code_story->mail                  = $db_mail_confirm_via_code_story->mail;

			$mail_confirm_via_code_story_list[] = $mail_confirm_via_code_story;
		}

		return $mail_confirm_via_code_story_list;
	}

	/**
	 * Создать новую сущность подтверждения почты через код
	 *
	 * @param int    $mail_confirm_story_id
	 * @param string $mail
	 *
	 * @return self
	 * @throws DBShardingNotFoundException
	 * @throws ParseFatalException
	 * @throws QueryFatalException
	 */
	public static function create(int $mail_confirm_story_id, string $mail):self {

		$mail_confirm_via_code_story = new self();

		$mail_confirm_via_code_story->id                    = null;
		$mail_confirm_via_code_story->status                = self::_STATUS_NOT_FINISHED;
		$mail_confirm_via_code_story->resend_count          = 0;
		$mail_confirm_via_code_story->error_count           = 0;
		$mail_confirm_via_code_story->created_at            = time();
		$mail_confirm_via_code_story->updated_at            = 0;
		$mail_confirm_via_code_story->next_resend_at        = time() + self::_RESEND_TIME;
		$mail_confirm_via_code_story->mail_confirm_story_id = $mail_confirm_story_id;
		$mail_confirm_via_code_story->message_id            = "";
		$mail_confirm_via_code_story->code_hash             = "";
		$mail_confirm_via_code_story->mail                  = $mail;

		// вставляем запись в базу и отдаем пользователю
		return $mail_confirm_via_code_story->_insertToDb();
	}

	/**
	 * Отправить код подтверждения
	 *
	 * @return $this
	 * @throws DBShardingNotFoundException
	 * @throws ParseFatalException
	 * @throws QueryFatalException
	 * @throws ReturnFatalException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function sendConfirmCode(int $stage):self {

		// проверяем, что код еще не отправлялся
		$this->_assertCodeNotSent();

		$this->_confirm_code = generateConfirmCode();

		$message_id = Gateway_Socket_Pivot::sendMailConfirmCode(
			$this->mail,
			$this->_confirm_code,
			self::_STAGE_TEMPLATE_MAIL_MAP[$stage]
		);

		$set = [
			"code_hash"      => sha1($this->_confirm_code),
			"resend_count"   => ++$this->resend_count,
			"next_resend_at" => time() + self::_RESEND_TIME,
			"message_id"     => $message_id,
		];

		$this->_updateEntity($set);

		// помечаем, что код отправили
		$this->_is_code_sent = true;

		return $this;
	}

	/**
	 * Проверить, что введеный код верный
	 *
	 * @param int $code
	 *
	 * @return Domain_Ldap_Entity_Mail_ConfirmViaCodeStory
	 * @throws DBShardingNotFoundException
	 * @throws Domain_Ldap_Exception_Mail_ConfirmCodeIsIncorrect
	 * @throws ParseFatalException
	 * @throws QueryFatalException
	 */
	public function assertValidCode(int $code):self {

		if (sha1($code) !== $this->code_hash) {

			// увеличиваем счетчик ошибок
			$set["error_count"] = ++$this->error_count;
			$this->_updateEntity($set);

			throw new Domain_Ldap_Exception_Mail_ConfirmCodeIsIncorrect();
		}

		$set = [
			"status" => self::_STATUS_SUCCESS,
		];

		return $this->_updateEntity($set);
	}

	/**
	 * Проверяем, что попытка активная
	 *
	 * @return $this
	 * @throws Domain_Ldap_Exception_Mail_CodeIsNotActive
	 */
	public function assertActive():self {

		if ($this->status !== self::_STATUS_NOT_FINISHED) {
			throw new Domain_Ldap_Exception_Mail_CodeIsNotActive();
		}

		return $this;
	}

	/**
	 * Проверяем, что по времени можно переотрпавить код
	 *
	 * @return self
	 * @throws Domain_Ldap_Exception_Mail_IsBeforeNextResendAt
	 */
	public function assertIsAfterNextResendAt():self {

		if ($this->next_resend_at > time()) {
			throw new Domain_Ldap_Exception_Mail_IsBeforeNextResendAt();
		}

		return $this;
	}

	/**
	 * Отметить попытку проваленной
	 *
	 * @return $this
	 * @throws DBShardingNotFoundException
	 * @throws ParseFatalException
	 * @throws QueryFatalException
	 */
	public function setFailed():self {

		$set["status"] = self::_STATUS_FAILED;

		return $this->_updateEntity($set);
	}

	/**
	 * Проверяем, что код еще не был отправлен в этом запросе
	 *
	 * @return void
	 * @throws ReturnFatalException
	 */
	private function _assertCodeNotSent():void {

		if ($this->_is_code_sent) {
			throw new ReturnFatalException("cant send one code twice per request");
		}
	}

	/**
	 * Пишем сущность в БД
	 *
	 * @return $this
	 * @throws ParseFatalException
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 */
	private function _insertToDb():self {

		if (!is_null($this->id)) {
			throw new ParseFatalException("row is already inserted");
		}

		$this->id = Gateway_Db_LdapData_MailConfirmViaCodeStory::insert(
			$this->_prepareForDb()
		);

		return $this;
	}

	/**
	 * Готовим сущность для БД
	 *
	 * @return Struct_Db_LdapData_MailConfirmViaCodeStory
	 */
	private function _prepareForDb():Struct_Db_LdapData_MailConfirmViaCodeStory {

		return new Struct_Db_LdapData_MailConfirmViaCodeStory(
			$this->id,
			$this->status,
			$this->resend_count,
			$this->error_count,
			$this->created_at,
			$this->updated_at,
			$this->next_resend_at,
			$this->mail_confirm_story_id,
			$this->message_id,
			$this->code_hash,
			$this->mail,
		);
	}

	/**
	 * Обновить сущность
	 *
	 * @param array $set
	 *
	 * @return $this
	 * @throws ParseFatalException
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 */
	private function _updateEntity(array $set):self {

		$set["updated_at"] = time();

		foreach ($set as $field => $value) {

			if (!property_exists($this, $field)) {
				throw new ParseFatalException("set invalid field");
			}
			$this->$field = $value;
		}

		Gateway_Db_LdapData_MailConfirmViaCodeStory::set($this->id, $set);

		return $this;
	}
}