<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;

/** класс содержит логику по работе с активной конференцией пользователя */
class Domain_Jitsi_Entity_UserActiveConference {

	/**
	 * получаем сущность
	 *
	 * @param int $user_id
	 *
	 * @return Struct_Db_JitsiData_UserActiveConference
	 * @throws ParseFatalException
	 * @throws Domain_Jitsi_Exception_UserActiveConference_NotFound
	 */
	public static function get(int $user_id):Struct_Db_JitsiData_UserActiveConference {

		try {
			return Gateway_Db_JitsiData_UserActiveConferenceRel::getOne($user_id);
		} catch (RowNotFoundException) {
			throw new Domain_Jitsi_Exception_UserActiveConference_NotFound();
		}
	}

	/**
	 * получаем несколько сущностей
	 *
	 * @param array $user_id_list
	 *
	 * @return array|Struct_Db_JitsiData_UserActiveConference[]
	 * @throws ParseFatalException
	 */
	public static function getList(array $user_id_list):array {

		return Gateway_Db_JitsiData_UserActiveConferenceRel::getList($user_id_list);
	}

	/**
	 * получаем сущность по id активной конференции
	 *
	 * @param string $conference_id
	 *
	 * @return Struct_Db_JitsiData_UserActiveConference[]
	 * @throws ParseFatalException
	 */
	public static function getByActiveConferenceId(string $conference_id):array {

		return Gateway_Db_JitsiData_UserActiveConferenceRel::getByActiveConferenceId($conference_id);
	}

	/**
	 * выбрасываем исключение, если пользователь имеет активную конференцию
	 *
	 * @throws Domain_Jitsi_Exception_UserActiveConference_UserHaveActiveConference
	 * @throws ParseFatalException
	 */
	public static function throwIfUserHaveActiveConference(int $user_id):void {

		try {
			$user_active_conference = self::get($user_id);
		} catch (Domain_Jitsi_Exception_UserActiveConference_NotFound) {
			return;
		}

		// если активной конференции нет, то ничего не делаем
		if ($user_active_conference->active_conference_id === "") {
			return;
		}

		// иначе выбрасываем исключение
		throw new Domain_Jitsi_Exception_UserActiveConference_UserHaveActiveConference($user_active_conference->active_conference_id);
	}

	/**
	 * выбрасываем исключение, если кто то из пользователей имеет активную конференцию
	 *
	 * @param int $user_id
	 * @param int $opponent_user_id
	 *
	 * @throws Domain_Jitsi_Exception_UserActiveConference_OpponentUserHaveActiveConference
	 * @throws Domain_Jitsi_Exception_UserActiveConference_UserHaveActiveConference
	 * @throws ParseFatalException
	 */
	public static function throwIfUserListHaveActiveConference(int $user_id, int $opponent_user_id):void {

		$user_active_conference_list = self::getList([$user_id, $opponent_user_id]);

		// для каждого участника проверяем, есть ли у него активная конференция
		foreach ($user_active_conference_list as $user_active_conference) {

			if ($user_active_conference->active_conference_id === "") {
				continue;
			}

			if ($user_active_conference->user_id === $user_id) {
				throw new Domain_Jitsi_Exception_UserActiveConference_UserHaveActiveConference($user_active_conference->active_conference_id);
			}

			if ($user_active_conference->user_id === $opponent_user_id) {
				throw new Domain_Jitsi_Exception_UserActiveConference_OpponentUserHaveActiveConference();
			}
		}
	}

	/**
	 * при завершении конференции
	 *
	 * @throws ParseFatalException
	 */
	public static function onConferenceFinished(string $conference_id):void {

		// очищаем ID активной конференции для всех у кого она сейчас стоит активной
		Gateway_Db_JitsiData_UserActiveConferenceRel::setByActiveConferenceId($conference_id, [
			"active_conference_id" => "",
			"updated_at"           => time(),
		]);
	}

	/**
	 * устанавливаем ID активной конференции
	 *
	 * @throws ParseFatalException
	 */
	public static function set(int $user_id, string $active_conference_id):Struct_Db_JitsiData_UserActiveConference {

		$user_active_conference = new Struct_Db_JitsiData_UserActiveConference(
			user_id: $user_id,
			active_conference_id: $active_conference_id,
			created_at: time(),
			updated_at: time(),
		);

		Gateway_Db_JitsiData_UserActiveConferenceRel::insertOrUpdate($user_active_conference);

		return $user_active_conference;
	}
}