<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\RowDuplicationException;
use BaseFrame\Exception\Gateway\RowNotFoundException;

/** Класс для работы с сущностью постоянной конференции */
class Domain_Jitsi_Entity_PermanentConference {

	protected const _PERMANENT_CONFERENCE_LIMIT = 100;

	/**
	 * Создаем постоянную конференцию
	 *
	 * @throws ParseFatalException
	 * @throws \queryException
	 * @throws Domain_Jitsi_Exception_PermanentConference_ConferenceIdDuplication
	 */
	public static function create(Struct_Db_JitsiData_Conference $conference_draft):void {

		// проверяем что конференция постоянная, если нет - просто выходим
		if ($conference_draft->description === "" || $conference_draft->conference_url_custom_name === "") {
			return;
		}

		$permanent_conference = new Struct_Db_JitsiData_PermanentConference(
			conference_id: $conference_draft->conference_id,
			space_id: $conference_draft->space_id,
			is_deleted: 0,
			creator_user_id: $conference_draft->creator_user_id,
			conference_url_custom_name: $conference_draft->conference_url_custom_name,
			created_at: time(),
			updated_at: 0,
		);

		try {
			Gateway_Db_JitsiData_PermanentConferenceList::insert($permanent_conference);
		} catch (RowDuplicationException) {
			throw new Domain_Jitsi_Exception_PermanentConference_ConferenceIdDuplication();
		}
	}

	/**
	 * Устанавливаем опции
	 *
	 * @throws ParseFatalException
	 */
	public static function change(Struct_Db_JitsiData_Conference $conference, string $description):Struct_Db_JitsiData_Conference {

		$conference->description = $description;

		$set = [
			"description" => $description,
			"updated_at"  => time(),
		];

		Gateway_Db_JitsiData_ConferenceList::set($conference->conference_id, $set);

		return $conference;
	}

	/**
	 * Получаем список комнат, созданных пользователем
	 *
	 * @throws ParseFatalException
	 */
	public static function getList(int $user_id, int $space_id):array {

		return Gateway_Db_JitsiData_PermanentConferenceList::getListByUser($user_id, $space_id);
	}

	/**
	 * Получаем одну конференцию
	 *
	 * @throws RowNotFoundException
	 * @throws ParseFatalException
	 */
	public static function getOne(string $conference_id):Struct_Db_JitsiData_PermanentConference {

		return Gateway_Db_JitsiData_PermanentConferenceList::getOne($conference_id);
	}

	/**
	 * Проверяем что можем создать постоянную конференцию
	 *
	 * @throws Domain_Jitsi_Exception_PermanentConference_ConferenceLimit
	 * @throws ParseFatalException
	 * @throws Domain_Jitsi_Exception_PermanentConference_ConferenceExist
	 */
	public static function assertUserCanCreateConference(int $user_id, int $space_id, string $conference_url_custom_name):void {

		// проверяем лимит пользователя на частные конференции
		if (Gateway_Db_JitsiData_PermanentConferenceList::getActiveCount($user_id, $space_id) >= self::_PERMANENT_CONFERENCE_LIMIT) {
			throw new Domain_Jitsi_Exception_PermanentConference_ConferenceLimit();
		}

		// проверяем что конференции с такой же ссылкой не было создано ранее
		self::assertLinkNotUsedByUser($user_id, $space_id, $conference_url_custom_name);
	}

	/**
	 * Проверяем что конференция с такой ссылкой от этого пользователя не была создана ранее
	 *
	 * @throws Domain_Jitsi_Exception_PermanentConference_ConferenceExist
	 * @throws ParseFatalException
	 */
	public static function assertLinkNotUsedByUser(int $user_id, int $space_id, string $conference_url_custom_name):void {

		try {
			Gateway_Db_JitsiData_PermanentConferenceList::getByLinkForUser($user_id, $space_id, $conference_url_custom_name);
		} catch (RowNotFoundException) {
			// не нашли все ок
			return;
		}

		// если дошли, значит нашли, значит уже есть
		throw new Domain_Jitsi_Exception_PermanentConference_ConferenceExist();
	}

	/**
	 * Проверяем, что конференция не удалена
	 *
	 * @throws Domain_Jitsi_Exception_PermanentConference_ConferenceIsDeleted
	 */
	public static function assertNotDeleted(Struct_Db_JitsiData_PermanentConference $permanent_conference):void {

		if ($permanent_conference->is_deleted === true) {
			throw new Domain_Jitsi_Exception_PermanentConference_ConferenceIsDeleted();
		}
	}

	/**
	 * Помечаем постоянную конференцию в списке удаленной
	 *
	 * @throws ParseFatalException
	 */
	public static function remove(string $conference_id):void {

		Gateway_Db_JitsiData_PermanentConferenceList::set($conference_id, [
			"is_deleted" => 1,
			"updated_at" => time(),
		]);
	}
}