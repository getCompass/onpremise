<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\DomainException;

/** пользователь имеет активную конференцию */
class Domain_Jitsi_Exception_UserActiveConference_UserHaveActiveConference extends DomainException {

	public function __construct(public string $active_conference_id, string $message = "user have active conference") {

		parent::__construct($message);
	}

	/**
	 * получаем информацию об активной конференции
	 *
	 * @return Struct_Db_JitsiData_Conference
	 */
	public function getConference():Struct_Db_JitsiData_Conference {

		return Domain_Jitsi_Entity_Conference::get($this->active_conference_id);
	}
}