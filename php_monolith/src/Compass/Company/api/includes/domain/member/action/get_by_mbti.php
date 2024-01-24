<?php

namespace Compass\Company;

/**
 * Действие получения списка пользователей с идентичным типом личности
 *
 * Class Domain_User_Action_Member_GetByMbti
 */
class Domain_Member_Action_GetByMbti {

	/**
	 * Получаем список пользователей с определенным типом личности
	 */
	public static function do(string $mbti_type, int $offset, int $count):array {

		$role_list = [\CompassApp\Domain\Member\Entity\Member::ROLE_ADMINISTRATOR, \CompassApp\Domain\Member\Entity\Member::ROLE_MEMBER];

		return Gateway_Db_CompanyData_MemberList::getListByMbti($mbti_type, $role_list, $offset, $count);
	}
}