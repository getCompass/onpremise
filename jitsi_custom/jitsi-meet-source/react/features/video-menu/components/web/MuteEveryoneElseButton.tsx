import React, {useCallback} from 'react';
import {useTranslation} from 'react-i18next';
import {useDispatch} from 'react-redux';

import {createToolbarEvent} from '../../../analytics/AnalyticsEvents';
import {sendAnalytics} from '../../../analytics/functions';
import {IconMicSlash} from '../../../base/icons/svg';
import ContextMenuItem from '../../../base/ui/components/web/ContextMenuItem';
import {NOTIFY_CLICK_MODE} from '../../../toolbox/types';
import {IButtonProps} from '../../types';
import Icon from "../../../base/icons/components/Icon";
import {isMobileBrowser} from "../../../base/environment/utils";
import {MEDIA_TYPE} from "../../../base/media/constants";
import {requestDisableAudioModeration, requestEnableAudioModeration} from "../../../av-moderation/actions";
import { muteAllParticipants } from '../../actions';

interface IProps extends IButtonProps {
    className?: string;
    isEnabledAudioModerationFromState: boolean;
}

/**
 * Implements a React {@link Component} which displays a button for audio muting
 * every participant in the conference except the one with the given
 * participantID.
 *
 * @returns {JSX.Element}
 */
const MuteEveryoneElseButton = ({
                                    notifyClick,
                                    notifyMode,
                                    participantID,
                                    className,
                                    isEnabledAudioModerationFromState
                                }: IProps): JSX.Element => {
    const {t} = useTranslation();
    const isMobile = isMobileBrowser();
    const dispatch = useDispatch();

    const handleClick = useCallback(() => {
        notifyClick?.();
        if (notifyMode === NOTIFY_CLICK_MODE.PREVENT_AND_NOTIFY) {
            return;
        }
        sendAnalytics(createToolbarEvent('mute.everyoneelse.pressed'));
        dispatch(muteAllParticipants([participantID], MEDIA_TYPE.AUDIO));
        if (isEnabledAudioModerationFromState) {
            dispatch(requestEnableAudioModeration());
        } else if (isEnabledAudioModerationFromState !== undefined) {
            dispatch(requestDisableAudioModeration());
        }

    }, [dispatch, notifyMode, notifyClick, participantID, sendAnalytics]);

    return (
        <ContextMenuItem
            accessibilityLabel={t('toolbar.accessibilityLabel.muteEveryoneElse')}
            customIcon={<Icon
                className={isMobile ? 'is-mobile' : ''}
                size={isMobile ? 22 : 18}
                src={IconMicSlash}
                color={'rgba(255, 255, 255, 0.3)'}/>}
            className={className}
            onClick={handleClick}
            text={t('videothumbnail.domuteOthers')}/>
    );
};

export default MuteEveryoneElseButton;
