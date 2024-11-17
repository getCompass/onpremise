<?php

namespace Compass\Pivot;

/** возможные действия, совершаемые с аватаром пользователя при актуализации его информации из SSO */
enum Domain_User_Action_Sso_ActualizeProfileData_AvatarAction: int {

	/** ничего не делаем с аватаром */
	case NO_ACTION = 0;

	/** заменяем аватар */
	case CHANGE = 1;

	/** очищаем аватар */
	case CLEAR = 2;
}