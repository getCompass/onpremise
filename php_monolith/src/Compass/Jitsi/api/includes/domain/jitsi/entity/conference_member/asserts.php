<?php

namespace Compass\Jitsi;

/** Класс содержит логику различных проверок связанных с участником конференции */
class Domain_Jitsi_Entity_ConferenceMember_Asserts {

	protected function __construct(
		/** @var Struct_Db_JitsiData_Conference конференция */
		protected Struct_Db_JitsiData_ConferenceMember $_conference_member,
	) {
	}

	/** инициализируем объект */
	public static function init(Struct_Db_JitsiData_ConferenceMember $conference_member):self {

		return new self($conference_member);
	}

	/**
	 * проверяем, что пользователь активный участник (находится в конференции)
	 *
	 * @throws Domain_jitsi_Exception_ConferenceMember_UnexpectedStatus
	 */
	public function assertActiveMember():self {

		if ($this->_conference_member->status !== Domain_Jitsi_Entity_ConferenceMember_Status::SPEAKING) {
			throw new Domain_jitsi_Exception_ConferenceMember_UnexpectedStatus();
		}

		return $this;
	}

	/**
	 * проверяем, что статус пользователя = активный участник или вступает в конференцию
	 *
	 * @throws Domain_jitsi_Exception_ConferenceMember_UnexpectedStatus
	 */
	public function assertJoinOrSpeakStatus():self {

		if (!in_array($this->_conference_member->status, [Domain_Jitsi_Entity_ConferenceMember_Status::SPEAKING, Domain_Jitsi_Entity_ConferenceMember_Status::JOINING])) {
			throw new Domain_jitsi_Exception_ConferenceMember_UnexpectedStatus();
		}

		return $this;
	}

	/**
	 * проверяем, что у участника есть права модератора
	 *
	 * @return self
	 * @throws Domain_Jitsi_Exception_ConferenceMember_NoModeratorRights
	 */
	public function assertModeratorRights():self {

		if ($this->_conference_member->is_moderator !== 1) {
			throw new Domain_Jitsi_Exception_ConferenceMember_NoModeratorRights();
		}

		return $this;
	}
}