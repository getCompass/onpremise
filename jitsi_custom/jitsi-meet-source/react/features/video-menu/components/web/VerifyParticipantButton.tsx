import React, {useCallback} from 'react';
import {useTranslation} from 'react-i18next';
import {useDispatch} from 'react-redux';

import {IconCheck, IconUserDeleted} from '../../../base/icons/svg';
import ContextMenuItem from '../../../base/ui/components/web/ContextMenuItem';
import {startVerification} from '../../../e2ee/actions';
import {NOTIFY_CLICK_MODE} from '../../../toolbox/types';
import {IButtonProps} from '../../types';
import {makeStyles} from "tss-react/mui";
import Icon from "../../../base/icons/components/Icon";
import {isMobileBrowser} from "../../../base/environment/utils";

const useStyles = makeStyles()(theme => {
    return {};
});

interface IProps extends IButtonProps {
    className?: string;
}

/**
 * Implements a React {@link Component} which displays a button that
 * verifies the participant.
 *
 * @returns {JSX.Element}
 */
const VerifyParticipantButton = ({
                                     notifyClick,
                                     notifyMode,
                                     participantID,
                                     className
                                 }: IProps): JSX.Element => {
    const {t} = useTranslation();
    const isMobile = isMobileBrowser();
    const {cx} = useStyles();
    const dispatch = useDispatch();

    const _handleClick = useCallback(() => {
        notifyClick?.();
        if (notifyMode === NOTIFY_CLICK_MODE.PREVENT_AND_NOTIFY) {
            return;
        }
        dispatch(startVerification(participantID));
    }, [dispatch, notifyClick, notifyMode, participantID]);

    return (
        <ContextMenuItem
            accessibilityLabel={t('videothumbnail.verify')}
            className={cx('verifylink', className === undefined ? '' : className)}
            customIcon={<Icon
                className={isMobile ? 'is-mobile' : ''}
                size={isMobile ? 22 : 18}
                src={IconCheck}
                color={'rgba(255, 255, 255, 0.3)'}/>}
            id={`verifylink_${participantID}`}
            // eslint-disable-next-line react/jsx-handler-names
            onClick={_handleClick}
            text={t('videothumbnail.verify')}/>
    );
};

export default VerifyParticipantButton;
