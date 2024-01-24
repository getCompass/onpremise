<?php

namespace Compass\Company;

/**
 * Класс для получения информации о пользователе из заявки
 */
class Domain_HiringRequest_Action_GetUserInfoFromRequest {

	/**
	 * Выполняем action
	 *
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \cs_RowIsEmpty
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function do(Struct_Db_CompanyData_HiringRequest $hiring_request):Struct_User_Info|false {

		// если заявка на пост_модерации, то идем в pivot за информацией
		if (in_array($hiring_request->status, Domain_HiringRequest_Entity_Request::ALLOW_HIRING_GET_USER_INFO_LIST)) {

			[$user_info] = Gateway_Socket_Pivot::getUserInfo($hiring_request->candidate_user_id);
			return $user_info;
		}

		// если имеются данные в заявке найма, то достаем их
		if (Domain_HiringRequest_Entity_Request::isExistCandidateUserInfo($hiring_request->extra)) {
			return Domain_HiringRequest_Entity_Request::getCandidateUserInfo($hiring_request->extra, $hiring_request->candidate_user_id);
		}

		// если заявка имеет статус о том, что пользователь уже есть/был в компании, то достаем из company_cache
		if (in_array($hiring_request->status, Domain_HiringRequest_Entity_Request::ALLOW_HIRING_GET_COMPANY_USER_INFO_LIST)) {

			$member_info = Gateway_Bus_CompanyCache::getMember($hiring_request->candidate_user_id);
			return new Struct_User_Info($member_info->user_id, $member_info->full_name, $member_info->avatar_file_key,
				\CompassApp\Domain\Member\Entity\Extra::getAvatarColorId($member_info->extra));
		}

		return false;
	}
}