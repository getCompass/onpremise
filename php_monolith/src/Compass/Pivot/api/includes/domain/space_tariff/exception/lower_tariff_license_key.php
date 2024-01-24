<?php

namespace Compass\Pivot;

/**
 * Ключ лицензии ниже тарифом чем нужно
 */
class Domain_SpaceTariff_Exception_LowerTariffLicenseKey extends \BaseFrame\Exception\DomainException {

	protected int $_member_count;
	protected int $_license_key_member_count;

	public function __construct(string $message, int $member_count, int $license_key_member_count) {

		$this->message                   = $message;
		$this->_member_count             = $member_count;
		$this->_license_key_member_count = $license_key_member_count;
		parent::__construct($message);
	}

	/**
	 * вернуть member_count
	 */
	public function getMemberCount():int {

		return $this->_member_count;
	}

	/**
	 * вернуть license_key_member_count
	 */
	public function getLicenseKeyMemberCount():int {

		return $this->_license_key_member_count;
	}
}