<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\RowDuplicationException;
use BaseFrame\Exception\Gateway\RowNotFoundException;

/** Класс для работы с сущностью конференции */
class Domain_Jitsi_Entity_Conference {

	/** @var int длинна генерируемого пароля */
	protected const _PASSWORD_LENGTH = 8;

	public const STATUS_NEW      = 0; // конференция была создана, но комната в jitsi еще не создана
	public const STATUS_ACTIVE   = 1; // конференция активна, в ней ведется общение
	public const STATUS_WAITING  = 2; // конференция создана, но она ожидает вступления первого участника
	public const STATUS_FINISHED = 8; // конференция завершена

	/**
	 * Создаем черновик объекта конференции
	 */
	public static function makeDraft(int $user_id, int $space_id, string $domain, string $conference_url_custom_name, string $description):Struct_Db_JitsiData_Conference {

		return new Struct_Db_JitsiData_Conference(
			space_id: $space_id,
			creator_user_id: $user_id,
			conference_url_custom_name: $conference_url_custom_name,
			description: $description,
			jitsi_instance_domain: $domain,
			data: Domain_Jitsi_Entity_Conference_Data::initData()
		);
	}

	/**
	 * Создаем конференцию
	 *
	 * @throws ParseFatalException
	 * @throws \queryException
	 * @throws Domain_Jitsi_Exception_Conference_ConferenceIdDuplication
	 */
	public static function create(Struct_Db_JitsiData_Conference $conference_draft):Struct_Db_JitsiData_Conference {

		// проверяем заполненные данные в черновике
		self::_assertDraftValues($conference_draft);

		// заполняем пустые данные в черновике
		$conference_draft = self::_fillEmptyValues($conference_draft);

		// устанавливаем статус и время создания
		$conference_draft->status     = self::STATUS_NEW;
		$conference_draft->created_at = time();

		// пытаемся создать конференцию
		try {
			Gateway_Db_JitsiData_ConferenceList::insert($conference_draft);
		} catch (RowDuplicationException) {
			throw new Domain_Jitsi_Exception_Conference_ConferenceIdDuplication();
		}

		return $conference_draft;
	}

	/**
	 * проверяем заполненные данные в черновике
	 *
	 * @throws ParseFatalException
	 */
	protected static function _assertDraftValues(Struct_Db_JitsiData_Conference $conference_draft):void {

		// если не задана jitsi нода
		if ($conference_draft->jitsi_instance_domain === "") {
			throw new ParseFatalException("unexpected jitsi instance domain value");
		}
	}

	/**
	 * заполняем пустые данные в черновике
	 *
	 * @return Struct_Db_JitsiData_Conference
	 */
	protected static function _fillEmptyValues(Struct_Db_JitsiData_Conference $conference_draft):Struct_Db_JitsiData_Conference {

		// если не задан пароль, то генерируем случайный
		if ($conference_draft->password === "") {
			$conference_draft->password = self::generatePassword();
		}

		// если не задан conference_id, то генерируем случайный
		if ($conference_draft->conference_id === "") {
			$conference_draft->conference_id = Domain_Jitsi_Entity_Conference_Id
				::getConferenceId($conference_draft->creator_user_id, Domain_Jitsi_Entity_Conference_Id::generateRandomUniquePart(), $conference_draft->password);
		}

		return $conference_draft;
	}

	/**
	 * генерируем случайный пароль
	 *
	 * @return string
	 */
	public static function generatePassword():string {

		// ВНИМАНИЕ! обязательно используем нижний регистр, так как использование верхнего регистра приводит к фантомным багам со стороны jitsi
		return mb_strtolower(generateRandomString(self::_PASSWORD_LENGTH));
	}

	/**
	 * получаем конференцию
	 *
	 * @param string $conference_id
	 *
	 * @return Struct_Db_JitsiData_Conference
	 * @throws Domain_Jitsi_Exception_Conference_NotFound
	 * @throws ParseFatalException
	 */
	public static function get(string $conference_id):Struct_Db_JitsiData_Conference {

		try {
			return Gateway_Db_JitsiData_ConferenceList::getOne($conference_id);
		} catch (RowNotFoundException) {
			throw new Domain_Jitsi_Exception_Conference_NotFound();
		}
	}

	/**
	 * Получаем несколько конференций
	 *
	 * @throws ParseFatalException
	 */
	public static function getList(array $permanent_conference_id_list):array {

		return Gateway_Db_JitsiData_ConferenceList::getList($permanent_conference_id_list);
	}

	/**
	 * проверяем существование конференции по ссылке и корректность параметров из ссылки (пароль, домен)
	 *
	 * @return Struct_Db_JitsiData_Conference
	 * @throws Domain_Jitsi_Exception_Conference_NotFound
	 * @throws Domain_Jitsi_Exception_Conference_WrongPassword
	 * @throws ParseFatalException
	 */
	public static function verifyConferenceLink(Struct_Jitsi_Conference_ParsedLink $parsed_link):Struct_Db_JitsiData_Conference {

		// получаем запись с конференцией
		$conference = self::get($parsed_link->conference_id);

		// сверяем пароль
		if ($conference->password !== $parsed_link->password) {
			throw new Domain_Jitsi_Exception_Conference_WrongPassword();
		}

		return $conference;
	}

	/**
	 * Определяем статус конференции, при ее завершении
	 */
	public static function resolveStatusOnFinish(Struct_Db_JitsiData_Conference $conference):int {

		return match (Domain_Jitsi_Entity_Conference_Data::getConferenceType($conference->data)) {
			Domain_Jitsi_Entity_Conference_Data::CONFERENCE_TYPE_DEFAULT, Domain_Jitsi_Entity_Conference_Data::CONFERENCE_TYPE_SINGLE => self::STATUS_FINISHED,
			Domain_Jitsi_Entity_Conference_Data::CONFERENCE_TYPE_PERMANENT                                                            => self::STATUS_NEW,
		};
	}

	/**
	 * обновляем статус конференции
	 *
	 * @throws ParseFatalException
	 */
	public static function updateStatus(string $conference_id, int $status):void {

		Gateway_Db_JitsiData_ConferenceList::set($conference_id, [
			"status"     => $status,
			"updated_at" => time(),
		]);
	}

	/**
	 * Устанавливаем опции
	 *
	 * @throws ParseFatalException
	 */
	public static function setOptions(Struct_Db_JitsiData_Conference $conference, bool $is_private, bool $is_lobby):Struct_Db_JitsiData_Conference {

		$conference->is_private = $is_private;
		$conference->is_lobby   = $is_lobby;
		Gateway_Db_JitsiData_ConferenceList::set($conference->conference_id, [
			"is_private" => intval($is_private),
			"is_lobby"   => intval($is_lobby),
		]);

		return $conference;
	}

	/**
	 * устанавливаем дополнительную информацию о конференции
	 *
	 * @param Struct_Db_JitsiData_Conference $conference
	 * @param array                          $data
	 *
	 * @return Struct_Db_JitsiData_Conference
	 * @throws ParseFatalException
	 */
	public static function setData(Struct_Db_JitsiData_Conference $conference, array $data):Struct_Db_JitsiData_Conference {

		Gateway_Db_JitsiData_ConferenceList::set($conference->conference_id, [
			"data"       => $data,
			"updated_at" => time(),
		]);

		return $conference;
	}

	/**
	 * является ли конференция – сингл звонком
	 *
	 * @return bool
	 */
	public static function isSingle(Struct_Db_JitsiData_Conference $conference):bool {

		return Domain_Jitsi_Entity_Conference_Data::getConferenceType($conference->data) === Domain_Jitsi_Entity_Conference_Data::CONFERENCE_TYPE_SINGLE;
	}

	/**
	 * Является ли конференция постоянной
	 */
	public static function isPermanent(Struct_Db_JitsiData_Conference $conference):bool {

		return Domain_Jitsi_Entity_Conference_Data::getConferenceType($conference->data) === Domain_Jitsi_Entity_Conference_Data::CONFERENCE_TYPE_PERMANENT;
	}

	/**
	 * является ли пользователь участником single звонка
	 *
	 * @return bool
	 */
	public static function isUserMemberOfSingle(Struct_Db_JitsiData_Conference $conference, int $user_id):bool {

		return in_array($user_id, [$conference->creator_user_id, Domain_Jitsi_Entity_Conference_Data::getOpponentUserId($conference->data)]);
	}

	/**
	 * Возвращает код конференции. Код является уникальным идентификатором
	 * для сообщения о звонке и отображается в сообщениях
	 */
	public static function getConferenceCode(Struct_Db_JitsiData_Conference $conference):string {

		[, $conference_code] = Domain_Jitsi_Entity_Conference_Id::explodeConferenceId($conference->conference_id);
		return $conference_code;
	}
}