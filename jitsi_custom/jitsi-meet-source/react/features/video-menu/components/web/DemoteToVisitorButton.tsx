import React, { useCallback } from 'react';
import { useTranslation } from 'react-i18next';
import { useDispatch } from 'react-redux';

import { openDialog } from '../../../base/dialog/actions';
import { IconUsers, IconVisitors } from '../../../base/icons/svg';
import ContextMenuItem from '../../../base/ui/components/web/ContextMenuItem';
import { NOTIFY_CLICK_MODE } from '../../../toolbox/types';
import { IButtonProps } from '../../types';

import DemoteToVisitorDialog from './DemoteToVisitorDialog';
import { makeStyles } from "tss-react/mui";
import Icon from "../../../base/icons/components/Icon";
import { isMobileBrowser } from "../../../base/environment/utils";
import { demoteRequest } from "../../../visitors/actions";

const useStyles = makeStyles()(theme => {
    return {};
});

interface IProps extends IButtonProps {

    /**
     * Button text class name.
     */
    className?: string;

    /**
     * Whether the icon should be hidden or not.
     */
    noIcon?: boolean;

    /**
     * Click handler executed aside from the main action.
     */
    onClick?: Function;
}

/**
 * Implements a React {@link Component} which displays a button for demoting a participant to visitor.
 *
 * @returns {JSX.Element}
 */
export default function DemoteToVisitorButton({
    className,
    noIcon = false,
    notifyClick,
    notifyMode,
    participantID
}: IProps): JSX.Element {
    const { t } = useTranslation();
    const isMobile = isMobileBrowser();
    const { cx } = useStyles();
    const dispatch = useDispatch();

    const handleClick = useCallback(() => {
        notifyClick?.();
        if (notifyMode === NOTIFY_CLICK_MODE.PREVENT_AND_NOTIFY) {
            return;
        }
        dispatch(demoteRequest(participantID));
    }, [ dispatch, notifyClick, notifyMode, participantID ]);

    return (
        <ContextMenuItem
            accessibilityLabel = {t('videothumbnail.demote')}
            className = {cx('demotelink', className === undefined ? '' : className)}
            icon = {undefined}
            customIcon = {noIcon ? undefined : <Icon
                className = {isMobile ? 'is-mobile' : ''}
                size = {isMobile ? 22 : 18}
                src = {IconVisitors}
                color = {'rgba(255, 255, 255, 0.3)'} />}
            id = {`demotelink_${participantID}`}
            onClick = {handleClick}
            text = {t('videothumbnail.demote')} />
    );
}
