<?php

namespace Compass\Pivot;

/**
 *
 *
 * Class Struct_User_Action_Create_Store
 */
class Struct_User_Action_Create_Store {

	/**
	 * @var Struct_Db_PivotUser_User чистовая версия пользователя
	 */
	public Struct_Db_PivotUser_User $user;

	/**
	 * @var Struct_User_Action_Create_Prepare данные с этапа подготовки
	 */
	public Struct_User_Action_Create_Prepare $prepare_data;

	/**
	 * Struct_User_Action_Create_Store constructor.
	 *
	 * @param Struct_Db_PivotUser_User          $user
	 * @param Struct_User_Action_Create_Prepare $prepare_data
	 */
	public function __construct(Struct_Db_PivotUser_User          $user,
					    Struct_User_Action_Create_Prepare $prepare_data) {

		$this->user         = $user;
		$this->prepare_data = $prepare_data;
	}
}