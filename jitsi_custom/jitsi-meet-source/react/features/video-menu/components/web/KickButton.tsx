import React, { useCallback } from 'react';
import { useTranslation } from 'react-i18next';
import { useDispatch } from 'react-redux';

import { openDialog } from '../../../base/dialog/actions';
import { IconUserDeleted } from '../../../base/icons/svg';
import ContextMenuItem from '../../../base/ui/components/web/ContextMenuItem';
import { NOTIFY_CLICK_MODE } from '../../../toolbox/types';
import { IButtonProps } from '../../types';

import KickRemoteParticipantDialog from './KickRemoteParticipantDialog';
import { makeStyles } from "tss-react/mui";
import Icon from "../../../base/icons/components/Icon";
import { isMobileBrowser } from "../../../base/environment/utils";

const useStyles = makeStyles()(theme => {
    return {
        icon: {
            '& svg': {
                fill: 'rgba(255, 79, 71, 1) !important',
            }
        }
    };
});

interface IProps extends IButtonProps {
    className?: string;
}

/**
 * Implements a React {@link Component} which displays a button for kicking out
 * a participant from the conference.
 *
 * @returns {JSX.Element}
 */
const KickButton = ({
    notifyClick,
    notifyMode,
    participantID,
    className
}: IProps): JSX.Element => {
    const { t } = useTranslation();
    const isMobile = isMobileBrowser();
    const { classes, cx } = useStyles();
    const dispatch = useDispatch();

    const handleClick = useCallback(() => {
        notifyClick?.();
        if (notifyMode === NOTIFY_CLICK_MODE.PREVENT_AND_NOTIFY) {
            return;
        }
        dispatch(openDialog(KickRemoteParticipantDialog, { participantID }));
    }, [ dispatch, notifyClick, notifyMode, participantID ]);

    return (
        <ContextMenuItem
            accessibilityLabel = {t('videothumbnail.kick')}
            className = {cx('kicklink', className === undefined ? '' : className)}
            customIcon = {<Icon
                className = {cx(classes.icon, isMobile ? 'is-mobile' : '')}
                size = {isMobile ? 22 : 18}
                src = {IconUserDeleted} />}
            id = {`ejectlink_${participantID}`}
            onClick = {handleClick}
            text = {t('videothumbnail.kick')} />
    );
};

export default KickButton;
