<?php

namespace Compass\Pivot;

/**
 * Class Struct_User_Action_Create_Human_Store
 */
class Struct_User_Action_Create_Human_Store extends Struct_User_Action_Create_Store {

	// запись уникального номера из базы
	public Struct_Db_PivotPhone_PhoneUniq $phone_uniq;

	// был ли номер переиспользован при регистрации
	public bool $is_phone_number_reused;

	/**
	 * Конструктор.
	 */
	public function __construct(Struct_Db_PivotUser_User $user, Struct_Db_PivotPhone_PhoneUniq $phone_uniq, bool $is_phone_number_reused, Struct_User_Action_Create_Prepare $prepare_data) {

		$this->phone_uniq             = $phone_uniq;
		$this->is_phone_number_reused = $is_phone_number_reused;

		parent::__construct($user, $prepare_data);
	}
}