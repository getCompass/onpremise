import React, {useCallback} from 'react';
import {useTranslation} from 'react-i18next';
import {useDispatch} from 'react-redux';

import {IconInfoCircle} from '../../../base/icons/svg';
import ContextMenuItem from '../../../base/ui/components/web/ContextMenuItem';
import {NOTIFY_CLICK_MODE} from '../../../toolbox/types';
import {renderConnectionStatus} from '../../actions.web';
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
 * Implements a React {@link Component} which displays a button that shows
 * the connection status for the given participant.
 *
 * @returns {JSX.Element}
 */
const ConnectionStatusButton = ({
                                    notifyClick,
                                    notifyMode,
                                    className
                                }: IProps): JSX.Element => {
    const {t} = useTranslation();
    const dispatch = useDispatch();
    const isMobile = isMobileBrowser();

    const handleClick = useCallback(e => {
        e.stopPropagation();
        notifyClick?.();
        if (notifyMode === NOTIFY_CLICK_MODE.PREVENT_AND_NOTIFY) {
            return;
        }
        dispatch(renderConnectionStatus(true));
    }, [dispatch, notifyClick, notifyMode]);

    return (
        <ContextMenuItem
            accessibilityLabel={t('videothumbnail.connectionInfo')}
            className={className}
            customIcon={<Icon
                className={isMobile ? 'is-mobile' : ''}
                size={isMobile ? 22 : 18}
                src={IconInfoCircle}
                color={'rgba(255, 255, 255, 0.3)'}/>}
            onClick={handleClick}
            text={t('videothumbnail.connectionInfo')}/>
    );
};

export default ConnectionStatusButton;
