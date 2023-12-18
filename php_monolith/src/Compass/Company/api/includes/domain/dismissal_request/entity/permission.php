<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;
use CompassApp\Domain\Member\Entity\Extra;

/**
 * Класс для работы с логикой разрешений на действия увольнения
 */
class Domain_DismissalRequest_Entity_Permission {

	/** @var array таблица прав на увольнение */
	const _DISMISS_ACCESS_ROLE_LIST = [

		// администраторы могут увольнять только участников
		\CompassApp\Domain\Member\Entity\Member::ROLE_ADMINISTRATOR => [\CompassApp\Domain\Member\Entity\Member::ROLE_MEMBER],
		\CompassApp\Domain\Member\Entity\Member::ROLE_MEMBER        => [],
	];

	/**
	 * Проверяем обычный ли сотрудник пытается создавать заявки на увольенение
	 *
	 * @param \CompassApp\Domain\Member\Struct\Short $creator_user
	 *
	 * @return bool
	 */
	public static function isAllowToCreateDismissalRequest(\CompassApp\Domain\Member\Struct\Short $creator_user):bool {

		// если тот кто увольняет обычный пользователь - запрещаем
		try {
			\CompassApp\Domain\Member\Entity\Permission::assertCanKickMember($creator_user->role, $creator_user->permissions);
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			return false;
		}

		return true;
	}

	/**
	 * Проверяем что пытаемся уволить себя
	 *
	 * @param int $creator_user_id
	 * @param int $dismissal_user_id
	 *
	 * @return bool
	 */
	public static function isDismissingMySelf(int $creator_user_id, int $dismissal_user_id):bool {

		if ($creator_user_id == $dismissal_user_id) {
			return true;
		}

		return false;
	}

	/**
	 * Проверяем что можем уволить овнера
	 *
	 * @param int  $dismissal_user_id
	 * @param bool $is_legacy
	 *
	 * @return bool
	 * @throws \parseException
	 * @throws \returnException
	 * @throws ParamException
	 * @throws \apiAccessException
	 * @throws \busException
	 */
	public static function isAllowToDismissOwner(int $dismissal_user_id, bool $is_legacy = true):bool {

		// получаем пользователя на увольнение
		$user_info_list = Gateway_Bus_CompanyCache::getMemberList([$dismissal_user_id]);
		if (!isset($user_info_list[$dismissal_user_id])) {
			throw new ParamException("dont found user in company cache");
		}
		$dismissal_user = $user_info_list[$dismissal_user_id];

		// если пользователь овнер и не удалён аккаунт
		if ($dismissal_user->role == \CompassApp\Domain\Member\Entity\Member::ROLE_ADMINISTRATOR) {

			if ($is_legacy || !Extra::getIsDeleted($dismissal_user->extra)) {
				return false;
			}
		}

		// если заявка была не на овнера - разрешаем
		return true;
	}

	/**
	 * Проверяем нет ли уже созданной заявки на увольнение
	 *
	 * @param int $dismissal_user_id
	 *
	 * @return bool
	 */
	public static function isRequestToDismissAlreadyExist(int $dismissal_user_id):bool {

		try {
			Domain_DismissalRequest_Entity_Request::getByDismissalUserId($dismissal_user_id);
		} catch (cs_DismissalRequestNotExist) {
			return false;
		}

		return true;
	}

	/**
	 * Проверям что взаимодействуем с заявкой на нас
	 *
	 * @param int                                    $creator_user_id
	 * @param Struct_Db_CompanyData_DismissalRequest $dismissal_request
	 *
	 * @return bool
	 */
	public static function isRequestToMe(int $creator_user_id, Struct_Db_CompanyData_DismissalRequest $dismissal_request):bool {

		if ($creator_user_id == $dismissal_request->dismissal_user_id) {
			return true;
		}

		return false;
	}

	/**
	 * Проверям что можем апрувить/отклонять заявки в общем случае
	 *
	 * @param \CompassApp\Domain\Member\Struct\Short $creator_user
	 *
	 * @return bool
	 */
	public static function isAllowToApproveDismissalAndRejectRequest(\CompassApp\Domain\Member\Struct\Short $creator_user):bool {

		// если тот кто увольняет обычный пользователь - запрещаем
		try {
			\CompassApp\Domain\Member\Entity\Permission::assertCanKickMember($creator_user->role, $creator_user->permissions);
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {
			return false;
		}

		return true;
	}

	/**
	 * Проверям что можем апрувить заявки на овнера/лидера
	 *
	 * @param int                                   $dismissal_user_id
	 *
	 * @return bool
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function isAllowToApproveOrRejectDismissOwnerRequest(int $dismissal_user_id):bool {

		// получаем пользователя на увольнение
		$user_info_list = Gateway_Bus_CompanyCache::getShortMemberList([$dismissal_user_id]);
		if (!isset($user_info_list[$dismissal_user_id])) {
			throw new ParamException("dont found user in company cache");
		}
		$dismissal_user = $user_info_list[$dismissal_user_id];

		// если пользователь овнер
		if ($dismissal_user->role == \CompassApp\Domain\Member\Entity\Member::ROLE_ADMINISTRATOR) {
			return false;
		}

		// если заявка была не на овнера - разрешаем
		return true;
	}

	/**
	 * Выбрасываем исключение если заявка уже принята
	 *
	 * @param Struct_Db_CompanyData_DismissalRequest $hiring_request
	 *
	 * @throws cs_DismissalRequestAlreadyApproved
	 */
	public static function assertDismissalRequestAlreadyApproved(Struct_Db_CompanyData_DismissalRequest $hiring_request):void {

		if ($hiring_request->status == Domain_DismissalRequest_Entity_Request::STATUS_APPROVED) {
			throw new cs_DismissalRequestAlreadyApproved();
		}
	}

	/**
	 * Выбрасываем исключение если заявка уже отклонена
	 *
	 * @param Struct_Db_CompanyData_DismissalRequest $hiring_request
	 *
	 * @throws cs_DismissalRequestAlreadyRejected
	 */
	public static function assertDismissalRequestAlreadyRejected(Struct_Db_CompanyData_DismissalRequest $hiring_request):void {

		if ($hiring_request->status == Domain_DismissalRequest_Entity_Request::STATUS_REJECTED) {
			throw new cs_DismissalRequestAlreadyRejected();
		}
	}

	/**
	 * Проверям что можем читать заявки на увольнение
	 *
	 * @param \CompassApp\Domain\Member\Struct\Short $creator_user
	 *
	 * @return bool
	 */
	public static function isAllowToGetDismissalRequest(\CompassApp\Domain\Member\Struct\Short $creator_user):bool {

		if (!\CompassApp\Domain\Member\Entity\Permission::canKickMember($creator_user->role, $creator_user->permissions)
			&& !\CompassApp\Domain\Member\Entity\Permission::canInviteMember($creator_user->role, $creator_user->permissions)) {

			return false;
		}

		return true;
	}

	/**
	 * проверяем доступ пользователя к заявке найма
	 *
	 * @param int                                    $user_id
	 * @param Struct_Db_CompanyData_DismissalRequest $dismissal_request
	 *
	 * @throws cs_UserHasNotRightsToDismiss
	 * @throws \cs_UserIsNotMember|\busException
	 */
	public static function checkRequestAllowedForUser(int $user_id, Struct_Db_CompanyData_DismissalRequest $dismissal_request):void {

		try {

			$user = Domain_User_Action_Member_GetShort::do($user_id);
			\CompassApp\Domain\Member\Entity\Permission::assertCanKickMember($user->role, $user->permissions);
		} catch (\cs_RowIsEmpty) {
			throw new \cs_UserIsNotMember();
		} catch (\CompassApp\Domain\Member\Exception\ActionNotAllowed) {

			// если пользователь обычный, проверяем кто создавал заявку
			if ($dismissal_request->creator_user_id != $user_id) {
				throw new cs_UserHasNotRightsToDismiss();
			}
		}
	}

	/**
	 * Возвращает список доступных для увольнения пользователей.
	 *
	 * @param int $user_id
	 *
	 * @return array
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function getAllowedToDismissRoleList(int $user_id):array {

		$user_info_list = Gateway_Bus_CompanyCache::getShortMemberList([$user_id]);
		if (!isset($user_info_list[$user_id])) {
			throw new ParamException("dont found user in company cache");
		}
		$user_info = $user_info_list[$user_id];

		if (!isset(static::_DISMISS_ACCESS_ROLE_LIST[$user_info->role])) {
			throw new ParamException("passed incorrect role");
		}

		return static::_DISMISS_ACCESS_ROLE_LIST[$user_info->role];
	}
}