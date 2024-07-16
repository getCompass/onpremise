<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\InvalidPhoneNumber;
use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;

/**
 * Завершаем добавление номера
 */
class Domain_User_Action_Security_AddPhone_ConfirmFirstStage {

	/**
	 * Завершаем процесс добавления номера телефона
	 *
	 * @throws BusFatalException
	 * @throws Domain_User_Exception_PhoneNumberBinding
	 * @throws InvalidPhoneNumber
	 * @throws ReturnFatalException
	 * @throws ParseFatalException
	 * @throws \busException
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 */
	public static function do(int $user_id, Domain_User_Entity_Security_AddPhone_SmsStory $sms_story, Domain_User_Entity_Security_AddPhone_Story $story):array {

		// добавляем номер
		self::_addPhoneNumber($sms_story, $story);

		// подтверждаем смс, завершаем добавления номера
		[$updated_story, $updated_sms_story] = self::_confirmSms($sms_story, $story);

		// отправляем задачу на обновление номера в intercom
		// такого функционала нет на on premise (возможно что только ока)

		// отправляем ивент пользователю о добавлении номера телефона
		Gateway_Bus_SenderBalancer::phoneAdded($user_id, $sms_story->getSmsStoryData()->phone_number);

		return [$updated_story, $updated_sms_story];
	}

	/**
	 * Добавляем номер телефона
	 *
	 * @throws ReturnFatalException
	 * @throws InvalidPhoneNumber
	 * @throws \busException
	 * @throws \parseException
	 * @throws BusFatalException
	 * @throws Domain_User_Exception_PhoneNumberBinding
	 */
	protected static function _addPhoneNumber(Domain_User_Entity_Security_AddPhone_SmsStory $sms_story, Domain_User_Entity_Security_AddPhone_Story $story):void {

		$story_data     = $story->getStoryData();
		$sms_story_data = $sms_story->getSmsStoryData();

		$user_id           = $story_data->user_id;
		$phone_number_obj  = new \BaseFrame\System\PhoneNumber($sms_story_data->phone_number);
		$phone_number_hash = Type_Hash_PhoneNumber::makeHash($phone_number_obj->number());

		self::_updatePhoneUniq($user_id, $phone_number_hash);
		self::_updateUserSecurity($user_id, $phone_number_obj->number());
		self::_updateCountryCode($user_id, $phone_number_obj->countryCode());
	}

	/**
	 * Добавляем запись о номере телефона
	 *
	 * @throws \parseException
	 * @throws Domain_User_Exception_PhoneNumberBinding
	 * @throws ReturnFatalException
	 */
	protected static function _updatePhoneUniq(int $user_id, string $new_phone_number_hash):void {

		/** начало транзакции */
		Gateway_Db_PivotPhone_PhoneUniqList::beginTransaction();

		try {

			$phone_uniq = Gateway_Db_PivotPhone_PhoneUniqList::getForUpdate($new_phone_number_hash);

			// проверим, что номер на текущий момент ни за кем не закреплен
			if ($phone_uniq->user_id !== 0) {

				Gateway_Db_PivotPhone_PhoneUniqList::rollback();
				throw new Domain_User_Exception_PhoneNumberBinding("phone number belong to another user");
			}

			// фиксируем пользователя в историю и обновляем запись
			$phone_uniq->previous_user_list[] = $user_id;
			Gateway_Db_PivotPhone_PhoneUniqList::set($new_phone_number_hash, [
				"user_id"            => $user_id,
				"binding_count"      => $phone_uniq->binding_count + 1,
				"last_binding_at"    => time(),
				"updated_at"         => time(),
				"previous_user_list" => $phone_uniq->previous_user_list,
			]);
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {

			// записи нет, это нормально, скорее всего номер новый
			Gateway_Db_PivotPhone_PhoneUniqList::insertOrUpdate($new_phone_number_hash, $user_id, false, time(), 0, 1, time(), 0, [$user_id]);
		}

		Gateway_Db_PivotPhone_PhoneUniqList::commitTransaction();
		/** конец транзакции */
	}

	/**
	 * Вставляем записи о смене номера
	 *
	 * @throws \parseException
	 */
	protected static function _updateUserSecurity(int $user_id, string $phone_number):void {

		Gateway_Db_PivotUser_UserSecurity::set($user_id, [
			"phone_number" => $phone_number,
			"updated_at"   => time(),
		]);
	}

	/**
	 * Обновить код страны у пользователя
	 *
	 * @throws \parseException
	 * @throws BusFatalException
	 * @throws \busException
	 */
	protected static function _updateCountryCode(int $user_id, string $country_code):void {

		Gateway_Db_PivotUser_UserList::set($user_id, [
			"country_code" => $country_code,
			"updated_at"   => time(),
		]);

		Gateway_Bus_PivotCache::clearUserCacheInfo($user_id);
	}

	/**
	 * Подтверждаем смс
	 *
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 */
	protected static function _confirmSms(Domain_User_Entity_Security_AddPhone_SmsStory $sms_story, Domain_User_Entity_Security_AddPhone_Story $story):array {

		// завершаем подтверждение смс
		$sms_story_data       = $sms_story->getSmsStoryData();
		$sms_story_update_set = [
			"status"     => Domain_User_Entity_Security_AddPhone_SmsStory::STATUS_SUCCESS,
			"updated_at" => time(),
		];
		Gateway_Db_PivotPhone_PhoneAddViaSmsStory::set(
			$story->getStoryMap(),
			$sms_story_data->phone_number,
			$sms_story_update_set,
		);
		$updated_sms_story = Domain_User_Entity_Security_AddPhone_SmsStory::createFromAnotherSmsStoryData($sms_story_data, $sms_story_update_set);

		// завершаем процесс смены номера
		$story_update_set = [
			"stage"      => Domain_User_Entity_Security_AddPhone_Story::STAGE_SECOND,
			"status"     => Domain_User_Entity_Security_AddPhone_Story::STATUS_SUCCESS,
			"updated_at" => time(),
		];
		Gateway_Db_PivotPhone_PhoneAddStory::set(
			$story->getStoryMap(),
			$story_update_set,
		);
		$updated_story = Domain_User_Entity_Security_AddPhone_Story::createFromAnotherStoryData($story->getStoryData(), $story_update_set);

		return [$updated_story, $updated_sms_story];
	}
}