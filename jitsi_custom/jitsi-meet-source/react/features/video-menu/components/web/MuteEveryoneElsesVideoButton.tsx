import React, {useCallback} from 'react';
import {useTranslation} from 'react-i18next';
import {useDispatch} from 'react-redux';

import {createToolbarEvent} from '../../../analytics/AnalyticsEvents';
import {sendAnalytics} from '../../../analytics/functions';
import {openDialog} from '../../../base/dialog/actions';
import {IconVideoOff} from '../../../base/icons/svg';
import ContextMenuItem from '../../../base/ui/components/web/ContextMenuItem';
import {NOTIFY_CLICK_MODE} from '../../../toolbox/types';
import {IButtonProps} from '../../types';

import MuteEveryonesVideoDialog from './MuteEveryonesVideoDialog';
import Icon from "../../../base/icons/components/Icon";
import {isMobileBrowser} from "../../../base/environment/utils";

interface IProps extends IButtonProps {
    className?: string;
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
                                          className
                                      }: IProps): JSX.Element => {
    const {t} = useTranslation();
    const isMobile = isMobileBrowser();
    const dispatch = useDispatch();

    const handleClick = useCallback(() => {
        notifyClick?.();
        if (notifyMode === NOTIFY_CLICK_MODE.PREVENT_AND_NOTIFY) {
            return;
        }
        sendAnalytics(createToolbarEvent('mute.everyoneelsesvideo.pressed'));
        dispatch(openDialog(MuteEveryonesVideoDialog, {exclude: [participantID]}));
    }, [notifyClick, notifyMode, participantID]);

    return (
        <ContextMenuItem
            accessibilityLabel={t('toolbar.accessibilityLabel.muteEveryoneElsesVideoStream')}
            customIcon={<Icon
                className={isMobile ? 'is-mobile' : ''}
                size={isMobile ? 22 : 18}
                src={IconVideoOff}
                color={'rgba(255, 255, 255, 0.3)'}/>}
            className={className}
            onClick={handleClick}
            text={t('videothumbnail.domuteVideoOfOthers')}/>
    );
};

export default MuteEveryoneElsesVideoButton;