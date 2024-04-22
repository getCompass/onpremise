<?php

namespace Compass\Premise;

use CompassApp\Domain\Member\Entity\Member;
use CompassApp\Domain\Member\Entity\Permission;

class Domain_PersonalCode_Action_PrepareSpace {

	public static function do(Struct_Db_PremiseUser_Space $user_space, Struct_Db_PivotCompany_Company $space):Struct_PersonalCode_Space {

		$permissions     = Permission::formatToOutput($user_space->role_alias, $user_space->permissions_alias);
		$permission_list = [];

		foreach ($permissions as $permission => $value) {

			if ($value === 0) {
				continue;
			}

			$permission_list[] = $permission;
		}

		return new Struct_PersonalCode_Space(
			$space->name,
			$space->extra["extra"]["member_count"],
			Member::getRoleOutputType($user_space->role_alias),
			$permission_list,
		);
	}
}