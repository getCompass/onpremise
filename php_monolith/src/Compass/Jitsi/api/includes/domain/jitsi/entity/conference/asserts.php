<?php

namespace Compass\Jitsi;

/** Класс содержит логику различных проверок связанных с конференцией */
class Domain_Jitsi_Entity_Conference_Asserts {

	protected function __construct(
		/** @var Struct_Db_JitsiData_Conference конференция */
		protected Struct_Db_JitsiData_Conference $_conference,
	) {
	}

	/** инициализируем объект */
	public static function init(Struct_Db_JitsiData_Conference $conference):self {

		return new self($conference);
	}

	/**
	 * проверяем, что конференция не окончена
	 *
	 * @throws Domain_Jitsi_Exception_Conference_IsFinished
	 */
	public function assertNotFinished():self {

		if ($this->_conference->status === Domain_Jitsi_Entity_Conference::STATUS_FINISHED) {
			throw new Domain_Jitsi_Exception_Conference_IsFinished();
		}

		return $this;
	}

	/**
	 * проверяем, что конференция активна
	 *
	 * @throws Domain_Jitsi_Exception_Conference_UnexpectedStatus
	 */
	public function assertActive():self {

		if ($this->_conference->status !== Domain_Jitsi_Entity_Conference::STATUS_ACTIVE) {
			throw new Domain_Jitsi_Exception_Conference_UnexpectedStatus();
		}

		return $this;
	}

	/**
	 * проверяем, что конференция является сингл звонком
	 *
	 * @throws Domain_Jitsi_Exception_Conference_UnexpectedStatus
	 */
	public function assertSingle():self {

		if (Domain_Jitsi_Entity_Conference_Data::getConferenceType($this->_conference->data) !== Domain_Jitsi_Entity_Conference_Data::CONFERENCE_TYPE_SINGLE) {
			throw new Domain_Jitsi_Exception_Conference_UnexpectedStatus();
		}

		return $this;
	}
}