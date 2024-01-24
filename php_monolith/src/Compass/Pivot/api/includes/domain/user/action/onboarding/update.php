<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;

/**
 * Действие обновления онбординга
 *
 * Class Domain_User_Action_Onboarding_Update
 */
class Domain_User_Action_Onboarding_Update {

	/**
	 * Обновляем
	 *
	 * @param Struct_Db_PivotUser_User $user
	 * @param Struct_User_Onboarding   $set_onboarding
	 *
	 * @throws Domain_User_Exception_Onboarding_NotAllowedStatus
	 * @throws Domain_User_Exception_Onboarding_NotAllowedType
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws BusFatalException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 */
	public static function do(Struct_Db_PivotUser_User $user, Struct_User_Onboarding $set_onboarding):void {

		Domain_User_Entity_Onboarding::assertAllowedStatus($set_onboarding->status);
		Domain_User_Entity_Onboarding::assertAllowedType($set_onboarding->type);

		// обновляем базу и получаем обновленный список онбордингов
		$onboarding_list = self::_updateDb($user->user_id, $set_onboarding);

		// сбрасываем кэш пользователя
		Gateway_Bus_PivotCache::clearUserCacheInfo($user->user_id);

		// если до этого у онбордингов был статус "Активен" или "Завершен", то отсылать ws не надо
		$activated_onboarding_list = array_map(
			static fn(Struct_User_Onboarding $onboarding) => in_array(
				$onboarding->status,
				[Domain_User_Entity_Onboarding::STATUS_ACTIVE, Domain_User_Entity_Onboarding::STATUS_FINISHED],
				true),
			$onboarding_list);

		// изначально отправляем ws
		$need_send_ws = true;

		// если у пользователя образовалось 2 или больше активированных онбординга - не нужно отсылать ws клиенту
		if ($set_onboarding->status === Domain_User_Entity_Onboarding::STATUS_ACTIVE && count($activated_onboarding_list) > 1) {
			$need_send_ws = false;
		}

		$formatted_onboarding = Domain_User_Entity_Onboarding::formatOutput($set_onboarding);

		// отсылаем ws с обновлением
		$need_send_ws && Gateway_Bus_SenderBalancer::onboardingUpdated($user->user_id, $formatted_onboarding);
	}

	/**
	 * Обновить онбординги
	 *
	 * @param int                    $user_id
	 * @param Struct_User_Onboarding $set_onboarding
	 *
	 * @return Struct_User_Onboarding[]
	 * @throws ReturnFatalException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 */
	protected static function _updateDb(int $user_id, Struct_User_Onboarding $set_onboarding):array {


		Gateway_Db_PivotUser_UserList::beginTransaction($user_id);

		$user            = Gateway_Db_PivotUser_UserList::getForUpdate($user_id);
		$onboarding_list = Type_User_Main::getOnboardingList($user->extra);

		$onboarding_list = self::_updateOnboardingList($onboarding_list, $set_onboarding);
		$extra           = Type_User_Main::setOnboardingList($user->extra, $onboarding_list);

		Gateway_Db_PivotUser_UserList::set($user_id, [
			"extra"      => $extra,
			"updated_at" => time(),
		]);

		Gateway_Db_PivotUser_UserList::commitTransaction($user_id);

		return $onboarding_list;
	}

	/**
	 * Обновляем список онбордингов
	 *
	 * @param Struct_User_Onboarding[] $onboarding_list
	 * @param Struct_User_Onboarding   $set_onboarding
	 *
	 * @return array
	 */
	protected static function _updateOnboardingList(array $onboarding_list, Struct_User_Onboarding $set_onboarding):array {

		$is_updated = false;

		// если существует такой онбординг, то перезаписываем новым
		foreach ($onboarding_list as $key => $onboarding) {

			if ($onboarding->type === $set_onboarding->type) {

				$onboarding_list[$key] = $set_onboarding;
				$is_updated            = true;
				break;
			}
		}

		// если не перезаписали - добавляем в конец массива
		if ($is_updated === false) {
			$onboarding_list[] = $set_onboarding;
		}

		// удаляем неактивные онбординги из массива
		return array_filter(
			$onboarding_list,
			static fn(Struct_User_Onboarding $onboarding) => $onboarding->status !== Domain_User_Entity_Onboarding::STATUS_UNAVAILABLE,
		);
	}
}