import React, {useCallback, useMemo} from 'react';
import {useTranslation} from 'react-i18next';
import {useDispatch, useSelector} from 'react-redux';

import {IReduxState} from '../../../app/types';
import {IconModerator} from '../../../base/icons/svg';
import {PARTICIPANT_ROLE} from '../../../base/participants/constants';
import {getLocalParticipant, getParticipantById, isParticipantModerator} from '../../../base/participants/functions';
import ContextMenuItem from '../../../base/ui/components/web/ContextMenuItem';
import {NOTIFY_CLICK_MODE} from '../../../toolbox/types';
import {IButtonProps} from '../../types';
import {makeStyles} from "tss-react/mui";
import Icon from "../../../base/icons/components/Icon";
import {isMobileBrowser} from "../../../base/environment/utils";
import {sendAnalytics} from "../../../analytics/functions";
import {createRemoteVideoMenuButtonEvent} from "../../../analytics/AnalyticsEvents";
import {grantModerator} from "../../../base/participants/actions";

const useStyles = makeStyles()(theme => {
    return {};
});

interface IProps extends IButtonProps {
    className?: string;
}

/**
 * Implements a React {@link Component} which displays a button for granting
 * moderator to a participant.
 *
 * @returns {JSX.Element|null}
 */
const GrantModeratorButton = ({
                                  notifyClick,
                                  notifyMode,
                                  participantID,
                                  className
                              }: IProps): JSX.Element | null => {
    const {t} = useTranslation();
    const isMobile = isMobileBrowser();
    const {cx} = useStyles();
    const dispatch = useDispatch();
    const localParticipant = useSelector(getLocalParticipant);
    const targetParticipant = useSelector((state: IReduxState) => getParticipantById(state, participantID));
    const visible = useMemo(() => Boolean(localParticipant?.role === PARTICIPANT_ROLE.MODERATOR)
        && !isParticipantModerator(targetParticipant), [isParticipantModerator, localParticipant, targetParticipant]);

    const handleClick = useCallback(() => {
        notifyClick?.();
        if (notifyMode === NOTIFY_CLICK_MODE.PREVENT_AND_NOTIFY) {
            return;
        }
        sendAnalytics(createRemoteVideoMenuButtonEvent(
            'grant.moderator.button',
            {
                'participant_id': participantID
            }));

        dispatch(grantModerator(participantID));
    }, [dispatch, notifyClick, notifyMode, participantID]);

    if (!visible) {
        return null;
    }

    return (
        <ContextMenuItem
            accessibilityLabel={t('toolbar.accessibilityLabel.grantModerator')}
            className={cx('grantmoderatorlink', className === undefined ? '' : className)}
            customIcon={<Icon
                className={isMobile ? 'is-mobile' : ''}
                size={isMobile ? 22 : 18}
                src={IconModerator}
                color={'rgba(255, 255, 255, 0.3)'}/>}
            onClick={handleClick}
            text={t('videothumbnail.grantModerator')}/>
    );
};

export default GrantModeratorButton;
