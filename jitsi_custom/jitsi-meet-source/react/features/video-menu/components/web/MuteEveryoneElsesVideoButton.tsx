import React, { useCallback } from 'react';
import { useTranslation } from 'react-i18next';
import { useDispatch } from 'react-redux';

import { createToolbarEvent } from '../../../analytics/AnalyticsEvents';
import { sendAnalytics } from '../../../analytics/functions';
import { IconVideoOff } from '../../../base/icons/svg';
import ContextMenuItem from '../../../base/ui/components/web/ContextMenuItem';
import { NOTIFY_CLICK_MODE } from '../../../toolbox/types';
import { IButtonProps } from '../../types';
import Icon from "../../../base/icons/components/Icon";
import { isMobileBrowser } from "../../../base/environment/utils";
import { MEDIA_TYPE } from "../../../base/media/constants";
import { requestDisableVideoModeration, requestEnableVideoModeration } from "../../../av-moderation/actions";
import { muteAllParticipants } from '../../actions';

interface IProps extends IButtonProps {
    className?: string;
    isEnabledVideoFromState: boolean;
}

/**
 * Implements a React {@link Component} which displays a button for audio muting
 * every participant in the conference except the one with the given
 * participantID.
 *
 * @returns {JSX.Element}
 */
const MuteEveryoneElsesVideoButton = ({
    notifyClick,
    notifyMode,
    participantID,
    className,
    isEnabledVideoFromState
}: IProps): JSX.Element => {
    const { t } = useTranslation();
    const isMobile = isMobileBrowser();
    const dispatch = useDispatch();

    const handleClick = useCallback(() => {
        notifyClick?.();
        if (notifyMode === NOTIFY_CLICK_MODE.PREVENT_AND_NOTIFY) {
            return;
        }
        sendAnalytics(createToolbarEvent('mute.everyoneelsesvideo.pressed'));

        dispatch(muteAllParticipants([ participantID ], MEDIA_TYPE.VIDEO));
        if (isEnabledVideoFromState) {
            dispatch(requestEnableVideoModeration());
        } else if (isEnabledVideoFromState !== undefined) {
            dispatch(requestDisableVideoModeration());
        }
    }, [ notifyClick, notifyMode, participantID ]);

    return (
        <ContextMenuItem
            accessibilityLabel = {t('toolbar.accessibilityLabel.muteEveryoneElsesVideoStream')}
            customIcon = {<Icon
                className = {isMobile ? 'is-mobile' : ''}
                size = {isMobile ? 22 : 18}
                src = {IconVideoOff}
                color = {'rgba(255, 255, 255, 0.3)'} />}
            className = {className}
            onClick = {handleClick}
            text = {t('videothumbnail.domuteVideoOfOthers')} />
    );
};

export default MuteEveryoneElsesVideoButton;
