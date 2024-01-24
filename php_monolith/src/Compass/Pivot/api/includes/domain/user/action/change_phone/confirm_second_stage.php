<?php

namespace Compass\Pivot;

/**
 * Подтверждаем второй этап смены телефона и меняем номер
 */
class Domain_User_Action_ChangePhone_ConfirmSecondStage {

	/**
	 * Выполняем процесс подтверждения второго этапа
	 *
	 * @return array
	 * @throws Domain_User_Exception_PhoneNumberBinding
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 */
	public static function do(Domain_User_Entity_ChangePhone_SmsStory $sms_story, Domain_User_Entity_ChangePhone_Story $story):array {

		// подтверждаем смс, завершаем процесс смены номера
		[$updated_story, $updated_sms_story] = self::_confirmSms($sms_story, $story);

		// меняем номер
		self::_changePhoneNumber($sms_story, $story);

		// отправляем задачу на обновление номера в intercom
		Gateway_Socket_Intercom::userPhoneNumberChanged($updated_story->getStoryData()->user_id, $sms_story->getSmsStoryData()->phone_number);

		return [$updated_story, $updated_sms_story];
	}

	/**
	 * Подтверждаем смс, завершаем процесс смены номера
	 *
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 */
	protected static function _confirmSms(Domain_User_Entity_ChangePhone_SmsStory $sms_story, Domain_User_Entity_ChangePhone_Story $story):array {

		// завершаем подтверждение смс
		$sms_story_data       = $sms_story->getSmsStoryData();
		$sms_story_update_set = [
			"status"     => Domain_User_Entity_ChangePhone_SmsStory::STATUS_SUCCESS,
			"updated_at" => time(),
		];
		Gateway_Db_PivotPhone_PhoneChangeViaSmsStory::set(
			$story->getStoryMap(),
			$sms_story_data->phone_number,
			$sms_story_update_set,
		);
		$updated_sms_story = Domain_User_Entity_ChangePhone_SmsStory::createFromAnotherSmsStoryData($sms_story_data, $sms_story_update_set);

		// завершаем процесс смены номера
		$story_update_set = [
			"status"     => Domain_User_Entity_ChangePhone_Story::STATUS_SUCCESS,
			"updated_at" => time(),
		];
		Gateway_Db_PivotPhone_PhoneChangeStory::set(
			$story->getStoryMap(),
			$story_update_set,
		);
		$updated_story = Domain_User_Entity_ChangePhone_Story::createFromAnotherStoryData($story->getStoryData(), $story_update_set);

		return [$updated_story, $updated_sms_story];
	}

	/**
	 * Меняем номер телефона.
	 *
	 * @param Domain_User_Entity_ChangePhone_SmsStory $sms_story
	 * @param Domain_User_Entity_ChangePhone_Story    $story
	 *
	 * @throws Domain_User_Exception_PhoneNumberBinding
	 * @throws \BaseFrame\Exception\Domain\InvalidPhoneNumber
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 * @throws cs_UserPhoneSecurityNotFound
	 * @throws \parseException
	 * @throws \queryException
	 */
	protected static function _changePhoneNumber(Domain_User_Entity_ChangePhone_SmsStory $sms_story, Domain_User_Entity_ChangePhone_Story $story):void {

		$story_data     = $story->getStoryData();
		$sms_story_data = $sms_story->getSmsStoryData();

		$user_id               = $story_data->user_id;
		$new_phone_number_obj  = new \BaseFrame\System\PhoneNumber($sms_story_data->phone_number);
		$new_phone_number_hash = Type_Hash_PhoneNumber::makeHash($new_phone_number_obj->number());
		$old_phone_number      = Domain_User_Entity_Phone::getPhoneByUserId($user_id);
		$old_phone_number_hash = Type_Hash_PhoneNumber::makeHash($old_phone_number);

		self::_updatePhoneUniqs($user_id, $old_phone_number_hash, $new_phone_number_hash);
		self::_updateUserSecurity($user_id, $old_phone_number, $new_phone_number_obj->number(), $story->getStoryMap());
		self::_updateCountryCode($user_id, $new_phone_number_obj->countryCode());
	}

	/**
	 * Вставляем записи о смене номера
	 *
	 * @throws \parseException
	 * @throws \queryException
	 */
	protected static function _updateUserSecurity(int $user_id, string $old_phone_number, string $new_phone_number, string $change_phone_story_map):void {

		Gateway_Db_PivotHistoryLogs_UserChangePhoneHistory::insert(
			$user_id,
			$old_phone_number,
			$new_phone_number,
			$change_phone_story_map,
			time(),
			0,
		);
		Gateway_Db_PivotUser_UserSecurity::set($user_id, [
			"phone_number" => $new_phone_number,
			"updated_at"   => time(),
		]);
	}

	/**
	 * Выставляем старый номер безхозным, вставляем запись о новом
	 * @throws Domain_User_Exception_PhoneNumberBinding
	 */
	protected static function _updatePhoneUniqs(int $user_id, string $old_phone_number_hash, string $new_phone_number_hash):void {

		/** начало транзакции */
		Gateway_Db_PivotPhone_PhoneUniqList::beginTransaction();

		try {

			// обновляем запись для старого номера телефона
			static::_updatePreviousPhoneUniq($user_id, $old_phone_number_hash);
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {

			// такого не должно происходить, если произошло, ошибка где-то выше в логике
			Gateway_Db_PivotPhone_PhoneUniqList::rollback();
			throw new \BaseFrame\Exception\Domain\ParseFatalException("there is no phone number record to update");
		} catch (Domain_User_Exception_PhoneNumberBinding $e) {

			// не удалось отвязать старый номер
			Gateway_Db_PivotPhone_PhoneUniqList::rollback();
			throw $e;
		}

		try {

			// обновляем запись для нового номера телефона
			static::_updateNewPhoneUniq($user_id, $new_phone_number_hash);
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {

			// записи нет, это нормально, скорее всего номер новый
			Gateway_Db_PivotPhone_PhoneUniqList::insertOrUpdate($new_phone_number_hash, $user_id, time(), 0, 1, time(), 0, [$user_id]);
		} catch (Domain_User_Exception_PhoneNumberBinding $e) {

			// новый номер нельзя использовать
			Gateway_Db_PivotPhone_PhoneUniqList::rollback();
			throw $e;
		}

		Gateway_Db_PivotPhone_PhoneUniqList::commitTransaction();
		/** конец транзакции */
	}

	/**
	 * Обновить код страны у пользователя
	 *
	 * @param int    $user_id
	 * @param string $country_code
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 * @throws \parseException
	 */
	protected static function _updateCountryCode(int $user_id, string $country_code):void {

		Gateway_Db_PivotUser_UserList::set($user_id, [
			"country_code" => $country_code,
			"updated_at"   => time(),
		]);

		Gateway_Bus_PivotCache::clearUserCacheInfo($user_id);
	}

	/**
	 * Обновляет запись для старого номера телефона.
	 *
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 * @throws Domain_User_Exception_PhoneNumberBinding
	 */
	protected static function _updatePreviousPhoneUniq(int $user_id, string $old_phone_number_hash):void {

		// получаем запись на чтение с блокировкой
		$phone_uniq = Gateway_Db_PivotPhone_PhoneUniqList::getForUpdate($old_phone_number_hash);

		// проверим на всякий, что можем отвязать номер
		// быть может он вообще не принадлежит пользователю
		if ($phone_uniq->user_id === 0 || $phone_uniq->user_id !== $user_id) {
			throw new Domain_User_Exception_PhoneNumberBinding("phone number doesn't belong to user");
		}

		// фиксируем пользователя в историю и обновляем запись
		Gateway_Db_PivotPhone_PhoneUniqList::set($old_phone_number_hash, [
			"user_id"           => 0,
			"last_unbinding_at" => time(),
			"updated_at"        => time(),
		]);
	}

	/**
	 * Обновляет запись для нового номера телефона.
	 *
	 * @throws \parseException
	 * @throws Domain_User_Exception_PhoneNumberBinding
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	protected static function _updateNewPhoneUniq(int $user_id, string $new_phone_number_hash):void {

		// получаем запись на чтение с блокировкой
		$phone_uniq = Gateway_Db_PivotPhone_PhoneUniqList::getForUpdate($new_phone_number_hash);

		// проверим, что номер на текущим момент ни за кем не закреплен
		if ($phone_uniq->user_id !== 0) {
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
	}
}