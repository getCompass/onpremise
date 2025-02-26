<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;
use BaseFrame\Exception\Request\ParamException;

/**
 * Класс описывающий апи-методы jitsi/permanent/*
 * @package Compass\Jitsi
 */
class Domain_Jitsi_Scenario_Api_Permanent {

	/**
	 * Создаем конференцию
	 *
	 * @throws Domain_Jitsi_Exception_Conference_ConferenceIdDuplication
	 * @throws Domain_Jitsi_Exception_Conference_CreatorIsNotMemberOfSpace
	 * @throws Domain_Jitsi_Exception_Conference_GuestAccessDenied
	 * @throws Domain_Jitsi_Exception_Conference_NoCreatePermissions
	 * @throws Domain_Jitsi_Exception_PermanentConference_ConferenceExist
	 * @throws Domain_Jitsi_Exception_PermanentConference_ConferenceIdDuplication
	 * @throws Domain_Jitsi_Exception_PermanentConference_ConferenceLimit
	 * @throws Domain_Space_Exception_NotFound
	 * @throws Domain_Space_Exception_UnexpectedStatus
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function createPermanent(int $user_id, int $space_id, bool $is_private, bool $is_lobby, string $conference_url_custom_name, string $description):Struct_Api_Conference_Data {

		// валидируем ссылку
		$description = Domain_Jitsi_Entity_validator::sanitizeDescription($description);
		if (!Domain_Jitsi_Entity_validator::isCorrectDescription($description)) {
			throw new ParamException("incorrect description");
		}

		// валидируем имя конференции
		$conference_url_custom_name = Domain_Jitsi_Entity_validator::sanitizeConferenceUrlCustomName($conference_url_custom_name);
		if (!Domain_Jitsi_Entity_validator::isCorrectConferenceUrlCustomName($conference_url_custom_name)) {
			throw new ParamException("incorrect conference_url_custom_name");
		}

		// проверяем пространство
		Domain_Jitsi_Entity_ConferenceMember_CreationAsserts::assertCreatorMemberOfSpace($user_id, $space_id);

		$space = Domain_Space_Entity_Main::get($space_id);
		Domain_Space_Entity_Status::assertActive($space);

		// проверим, что пользователь имеет права на создание конференции
		Domain_Jitsi_Entity_ConferenceMember_CreationAsserts::assertPermissions($user_id, $space_id);

		// проверяем поля если создаем постоянную конференцию
		Domain_Jitsi_Entity_PermanentConference::assertUserCanCreateConference($user_id, $space_id, $conference_url_custom_name);

		// получаем инстанс доступного jitsi сервера, для создания конференции
		$jitsi_node_config = Domain_Jitsi_Entity_Node::getRandomNode();

		// создаем конференцию
		$conference = Domain_Jitsi_Action_Conference_CreatePermanent::do($user_id, $space_id, $is_private, $is_lobby, $jitsi_node_config, $conference_url_custom_name, $description);

		// формируем ответ
		return Struct_Api_Conference_Data::buildFromDB($conference);
	}

	/**
	 * Устанавливаем параметры постоянной конференции
	 *
	 * @throws Domain_Jitsi_Exception_ConferenceMember_IncorrectMemberId
	 * @throws Domain_Jitsi_Exception_ConferenceMember_NotFound
	 * @throws Domain_Jitsi_Exception_Conference_AllowedOnlyForCreator
	 * @throws Domain_Jitsi_Exception_Conference_ConferenceIdDuplication
	 * @throws Domain_Jitsi_Exception_Conference_IsFinished
	 * @throws Domain_Jitsi_Exception_Conference_NotFound
	 * @throws Domain_Jitsi_Exception_Conference_UnexpectedType
	 * @throws Domain_Jitsi_Exception_Node_NotFound
	 * @throws Domain_Jitsi_Exception_Node_RequestFailed
	 * @throws Domain_Jitsi_Exception_PermanentConference_ConferenceExist
	 * @throws Domain_Jitsi_Exception_PermanentConference_ConferenceIdDuplication
	 * @throws Domain_Jitsi_Exception_PermanentConference_ConferenceIsDeleted
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws RowNotFoundException
	 * @throws \cs_CurlError
	 * @throws \parseException
	 * @throws \queryException
	 */
	public static function change(int    $user_id,
						string $conference_id,
						string $conference_url_custom_name,
						string $description):Struct_Api_Conference_Data {

		// валидируем ссылку
		$description = Domain_Jitsi_Entity_validator::sanitizeDescription($description);
		if (!Domain_Jitsi_Entity_validator::isCorrectDescription($description)) {
			throw new ParamException("incorrect description");
		}

		// валидируем имя конференции
		$conference_url_custom_name = Domain_Jitsi_Entity_validator::sanitizeConferenceUrlCustomName($conference_url_custom_name);
		if (!Domain_Jitsi_Entity_validator::isCorrectConferenceUrlCustomName($conference_url_custom_name)) {
			throw new ParamException("incorrect conference_url_custom_name");
		}

		// получаем данные о конференции
		$conference = Domain_Jitsi_Entity_Conference::get($conference_id);

		// проверяем, что состояние конференции позволяет менять ее опции
		Domain_Jitsi_Entity_Conference_Asserts::init($conference)
			->assertNotFinished()
			->assertPermanent()
			->assertCreator($user_id);

		// проверяем что не удалена
		$permanent_conference = Domain_Jitsi_Entity_PermanentConference::getOne($conference->conference_id);
		Domain_Jitsi_Entity_PermanentConference::assertNotDeleted($permanent_conference);

		// возвращаем обновленные данные о конференции
		return Domain_Jitsi_Action_Conference_Change::do($user_id, $conference, $conference_url_custom_name, $description);
	}

	/**
	 * Получаем все постоянные конференции
	 *
	 * @throws ParseFatalException
	 */
	public static function getList(int $user_id, int $space_id):array {

		$permanent_conference_list    = Domain_Jitsi_Entity_PermanentConference::getList($user_id, $space_id);
		$permanent_conference_id_list = [];
		foreach ($permanent_conference_list as $conference) {
			$permanent_conference_id_list[] = $conference->conference_id;
		}

		$conference_list = Domain_Jitsi_Entity_Conference::getList($permanent_conference_id_list);

		// сортируем по created_at
		usort($conference_list, static function(Struct_Db_JitsiData_Conference $a, Struct_Db_JitsiData_Conference $b) {

			return $b->created_at <=> $a->created_at;
		});

		$conference_data_list = [];
		foreach ($conference_list as $conference) {
			$conference_data_list[] = Struct_Api_Conference_Data::buildFromDB($conference)->format();
		}

		return $conference_data_list;
	}

	/**
	 * Удаляем постоянную комнату
	 *
	 * @throws Domain_Jitsi_Exception_ConferenceMember_IncorrectMemberId
	 * @throws Domain_Jitsi_Exception_ConferenceMember_NotFound
	 * @throws Domain_Jitsi_Exception_Conference_AllowedOnlyForCreator
	 * @throws Domain_Jitsi_Exception_Conference_NotFound
	 * @throws Domain_Jitsi_Exception_Conference_UnexpectedType
	 * @throws Domain_Jitsi_Exception_Node_NotFound
	 * @throws Domain_Jitsi_Exception_Node_RequestFailed
	 * @throws ParseFatalException
	 * @throws \cs_CurlError
	 * @throws \parseException
	 */
	public static function remove(int $user_id, string $conference_id):void {

		// получаем данные о конференции
		$conference = Domain_Jitsi_Entity_Conference::get($conference_id);

		// совершаем проверки
		Domain_Jitsi_Entity_Conference_Asserts::init($conference)
			->assertPermanent()
			->assertCreator($user_id);

		// завершаем постоянную комнату
		Domain_Jitsi_Action_Conference_FinishConference::do($user_id, $conference, true);

		// удаляем постоянную комнату из списка у пользователя
		Domain_Jitsi_Entity_PermanentConference::remove($conference_id);

		// убираем активную конференцию для всех участников, чтобы ни у кого не повисла активная конференция
		Domain_Jitsi_Entity_UserActiveConference::onConferenceFinished($conference_id);
	}
}