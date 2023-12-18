<?php

namespace Compass\Thread;

use BaseFrame\Exception\DomainException;

/**
 * Время напоминания меньше текущего
 */
class Domain_Remind_Exception_RemindAtBeforeCurrentTime extends DomainException {

}