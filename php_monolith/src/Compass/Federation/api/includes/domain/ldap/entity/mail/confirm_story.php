<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\DBShardingNotFoundException;
use BaseFrame\Exception\Gateway\QueryFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * класс для работы с подтверждениями почты
 * @package Compass\Federation
 */
class Domain_Ldap_Entity_Mail_ConfirmStory {

	private const _STATUS_NOT_FINISHED = 0; // процесс подтверждения не закончен
	private const _STATUS_SUCCESS      = 1; // почта подтверждена успешно
	private const _STATUS_FAILED       = 2; // процесс подтверждения закончен неудачей

	public const STAGE_ENTER_NEW_MAIL        = 1; // этап ввода новой почты
	public const STAGE_CONFIRM_NEW_MAIL      = 2; // этап подтверждения новой почты
	public const STAGE_CONFIRM_CHANGING_MAIL = 3; // этап подтверждения изменяемой почты
	public const STAGE_CONFIRM_CURRENT_MAIL  = 4; // этап подтверждения текущей почты
	public const STAGE_GET_LDAP_AUTH_TOKEN   = 5; // этап получения токена авторизации LDAP

	private const _EXPIRE_TIME = 20 * 60; // время истекания попытки

	/** @var string[] строковые значения этапов для фронтенда */
	private const _STAGE_FRONTEND_MAP = [
		self::STAGE_ENTER_NEW_MAIL        => "enter_new_mail",
		self::STAGE_CONFIRM_NEW_MAIL      => "confirm_new_mail",
		self::STAGE_CONFIRM_CHANGING_MAIL => "confirm_changing_mail",
		self::STAGE_CONFIRM_CURRENT_MAIL  => "confirm_current_mail",
		self::STAGE_GET_LDAP_AUTH_TOKEN   => "get_ldap_auth_token",
	];

	/** @var int[] список следующих этапов для каждого */
	private const _NEXT_STAGE_MAP = [
		self::STAGE_ENTER_NEW_MAIL        => self::STAGE_CONFIRM_NEW_MAIL,
		self::STAGE_CONFIRM_NEW_MAIL      => self::STAGE_GET_LDAP_AUTH_TOKEN,
		self::STAGE_CONFIRM_CHANGING_MAIL => self::STAGE_ENTER_NEW_MAIL,
		self::STAGE_CONFIRM_CURRENT_MAIL  => self::STAGE_GET_LDAP_AUTH_TOKEN,
	];

	private const _SEND_CONFIRM_CODE_STAGE_LIST = [
		self::STAGE_CONFIRM_NEW_MAIL,
		self::STAGE_CONFIRM_CURRENT_MAIL,
		self::STAGE_CONFIRM_CHANGING_MAIL,
	];

	public ?int    $mail_confirm_story_id;
	public ?string $mail_confirm_story_map;

	public int    $status;
	public int    $stage;
	public int    $created_at;
	public int    $updated_at;
	public int    $expires_at;
	public string $ldap_auth_token;
	public string $uid;

	protected function __construct() {
	}

	/**
	 * Инициализируем сущность из записи БД
	 *
	 * @param string $mail_confirm_story_map
	 *
	 * @return self
	 * @throws DBShardingNotFoundException
	 * @throws Domain_Ldap_Exception_Mail_ConfirmStoryNotFound
	 * @throws QueryFatalException
	 */
	public static function get(string $mail_confirm_story_map):self {

		$mail_confirm_story_id = Type_Pack_MailConfirmStory::getId($mail_confirm_story_map);

		try {
			$db_mail_confirm_story = Gateway_Db_LdapData_MailConfirmStory::getOne($mail_confirm_story_id);
		} catch (RowNotFoundException) {
			throw new Domain_Ldap_Exception_Mail_ConfirmStoryNotFound();
		}

		$mail_confirm_story = new self();

		$mail_confirm_story->mail_confirm_story_map = $mail_confirm_story_map;
		$mail_confirm_story->mail_confirm_story_id  = $db_mail_confirm_story->mail_confirm_story_id;
		$mail_confirm_story->status                 = $db_mail_confirm_story->status;
		$mail_confirm_story->stage                  = $db_mail_confirm_story->stage;
		$mail_confirm_story->created_at             = $db_mail_confirm_story->created_at;
		$mail_confirm_story->updated_at             = $db_mail_confirm_story->updated_at;
		$mail_confirm_story->expires_at             = $db_mail_confirm_story->expires_at;
		$mail_confirm_story->ldap_auth_token        = $db_mail_confirm_story->ldap_auth_token;
		$mail_confirm_story->uid                    = $db_mail_confirm_story->uid;

		return $mail_confirm_story;
	}

	/**
	 * Создать новую сущность подтверждения почты
	 *
	 * @param string $ldap_auth_token
	 * @param string $uid
	 * @param int    $stage
	 *
	 * @return self
	 * @throws DBShardingNotFoundException
	 * @throws ParseFatalException
	 * @throws QueryFatalException
	 * @throws \parseException
	 */
	public static function create(string $ldap_auth_token, string $uid, int $stage):self {

		$mail_confirm_story                        = new self();
		$mail_confirm_story->mail_confirm_story_id = null;
		$mail_confirm_story->status                = self::_STATUS_NOT_FINISHED;
		$mail_confirm_story->stage                 = $stage;
		$mail_confirm_story->created_at            = time();
		$mail_confirm_story->updated_at            = 0;
		$mail_confirm_story->expires_at            = time() + self::_EXPIRE_TIME;
		$mail_confirm_story->ldap_auth_token       = $ldap_auth_token;
		$mail_confirm_story->uid                   = $uid;

		$mail_confirm_story = $mail_confirm_story->_insertToDb();

		$mail_confirm_story->mail_confirm_story_map = Type_Pack_MailConfirmStory::doPack(
			$mail_confirm_story->mail_confirm_story_id,
			$mail_confirm_story->created_at
		);

		return $mail_confirm_story;
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

		if (!is_null($this->mail_confirm_story_id)) {
			throw new ParseFatalException("row is already inserted");
		}
		$this->mail_confirm_story_id = Gateway_Db_LdapData_MailConfirmStory::insert(
			$this->_prepareForDb()
		);

		return $this;
	}

	/**
	 * Переместить на новый этап
	 *
	 * @return $this
	 * @throws ParseFatalException
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 */
	public function moveToNextStage():self {

		if (!isset(self::_NEXT_STAGE_MAP[$this->stage])) {
			throw new ParseFatalException("stage is last in flow");
		}

		$set = [
			"stage" => self::_NEXT_STAGE_MAP[$this->stage],
		];

		// если дошли до получения токена - пользователь успешно подтвердил почту
		if (self::_NEXT_STAGE_MAP[$this->stage] === self::STAGE_GET_LDAP_AUTH_TOKEN) {
			$set["status"] = self::_STATUS_SUCCESS;
		}

		return $this->_updateEntity($set);
	}

	/**
	 * Установить необходимый этап
	 *
	 * @return $this
	 * @throws DBShardingNotFoundException
	 * @throws ParseFatalException
	 * @throws QueryFatalException
	 */
	public function setStage(int $stage):self {

		$set = [
			"stage" => $stage,
		];

		// если дошли до получения токена - пользователь успешно подтвердил почту
		if ($stage === self::STAGE_GET_LDAP_AUTH_TOKEN) {
			$set["status"] = self::_STATUS_SUCCESS;
		}

		return $this->_updateEntity($set);
	}

	/**
	 * Проверить, что почта была подтверждена
	 *
	 * @return Domain_Ldap_Entity_Mail_ConfirmStory
	 * @throws Domain_Ldap_Exception_Mail_ConfirmIsNotActive
	 */
	public function assertIsActive():self {

		if ($this->status !== self::_STATUS_NOT_FINISHED) {
			throw new Domain_Ldap_Exception_Mail_ConfirmIsNotActive();
		}

		return $this;
	}

	/**
	 * Проверяем, что попытка еще не протухла
	 *
	 * @return Domain_Ldap_Entity_Mail_ConfirmStory
	 * @throws Domain_Ldap_Exception_Mail_ConfirmIsExpired
	 */
	public function assertNotExpired():self {

		if ($this->expires_at < time()) {
			throw new Domain_Ldap_Exception_Mail_ConfirmIsExpired();
		}

		return $this;
	}

	/**
	 * Проверяем, что находимся на верном этапе
	 *
	 * @param int $stage
	 *
	 * @return void
	 * @throws Domain_Ldap_Exception_Mail_StageIsInvalid
	 */
	public function assertValidStage(int $stage):void {

		if ($stage !== $this->stage) {
			throw new Domain_Ldap_Exception_Mail_StageIsInvalid();
		}
	}

	/**
	 * Проверяем, что находимся на этапе, позволенном в добавлении почты
	 *
	 * @param int $stage
	 *
	 * @return void
	 * @throws Domain_Ldap_Exception_Mail_StageIsInvalid
	 */
	public function assertAddMailStage():void {

		$add_mail_stage_list = [
			self::STAGE_ENTER_NEW_MAIL,
			self::STAGE_CONFIRM_NEW_MAIL,
		];

		if (!in_array($this->stage, $add_mail_stage_list)) {
			throw new Domain_Ldap_Exception_Mail_StageIsInvalid();
		}
	}

	/**
	 * Проверяем, что попытка находится на этапе подтверждения
	 *
	 * @return void
	 * @throws Domain_Ldap_Exception_Mail_StageIsInvalid
	 */
	public function assertConfirmStage():void {

		if (!in_array($this->stage, self::_SEND_CONFIRM_CODE_STAGE_LIST)) {
			throw new Domain_Ldap_Exception_Mail_StageIsInvalid();
		}
	}

	/**
	 * Получить отформатированный stage
	 * @return string
	 */
	public function getFormattedStage():string {

		return self::_STAGE_FRONTEND_MAP[$this->stage];
	}

	/**
	 * Готовим сущность для БД
	 *
	 * @return Struct_Db_LdapData_MailConfirmStory
	 */
	private function _prepareForDb():Struct_Db_LdapData_MailConfirmStory {

		return new Struct_Db_LdapData_MailConfirmStory(
			$this->mail_confirm_story_id,
			$this->status,
			$this->stage,
			$this->created_at,
			$this->updated_at,
			$this->expires_at,
			$this->ldap_auth_token,
			$this->uid,
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

		Gateway_Db_LdapData_MailConfirmStory::set($this->mail_confirm_story_id, $set);

		return $this;
	}
}