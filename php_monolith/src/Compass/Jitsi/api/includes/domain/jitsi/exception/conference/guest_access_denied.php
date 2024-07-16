<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\DomainException;

/**
 * Пользователь является гостем и не может создать конференцию.
 */
class Domain_Jitsi_Exception_Conference_GuestAccessDenied extends DomainException {

}