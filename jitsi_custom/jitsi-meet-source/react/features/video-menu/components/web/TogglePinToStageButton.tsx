import React, {useCallback} from 'react';
import {useTranslation} from 'react-i18next';
import {useDispatch, useSelector} from 'react-redux';

import {IconPin, IconPinned} from '../../../base/icons/svg';
import ContextMenuItem from '../../../base/ui/components/web/ContextMenuItem';
import {togglePinStageParticipant} from '../../../filmstrip/actions.web';
import {getPinnedActiveParticipants} from '../../../filmstrip/functions.web';
import {NOTIFY_CLICK_MODE} from '../../../toolbox/types';
import {IButtonProps} from '../../types';
import Icon from "../../../base/icons/components/Icon";
import {isMobileBrowser} from "../../../base/environment/utils";

interface IProps extends IButtonProps {

    /**
     * Context menu class name.
     */
    className?: string;

    /**
     * Button text class name.
     */
    textClassName?: string;

    /**
     * Whether the icon should be hidden or not.
     */
    noIcon?: boolean;

    /**
     * Click handler executed aside from the main action.
     */
    onClick?: Function;
}

const TogglePinToStageButton = ({
                                    className,
                                    textClassName,
                                    noIcon = false,
                                    notifyClick,
                                    notifyMode,
                                    onClick,
                                    participantID
                                }: IProps): JSX.Element => {
    const dispatch = useDispatch();
    const {t} = useTranslation();
    const isActive = Boolean(useSelector(getPinnedActiveParticipants)
        .find(p => p.participantId === participantID));
    const _onClick = useCallback(() => {
        notifyClick?.();
        if (notifyMode === NOTIFY_CLICK_MODE.PREVENT_AND_NOTIFY) {
            return;
        }
        dispatch(togglePinStageParticipant(participantID));
        onClick?.();
    }, [dispatch, isActive, notifyClick, onClick, participantID]);
    const isMobile = isMobileBrowser();

    const text = isActive
        ? t('videothumbnail.unpinFromStage')
        : t('videothumbnail.pinToStage');

    return (
        <ContextMenuItem
            accessibilityLabel={text}
            icon={undefined}
            customIcon={noIcon ? undefined : <Icon
                className={isMobile ? 'is-mobile' : ''}
                size={isMobile ? 22 : 18}
                src={IconPin}
                color={'rgba(255, 255, 255, 0.3)'}/>}
            onClick={_onClick}
            text={text}
            className={className}
            textClassName={textClassName}/>
    );
};

export default TogglePinToStageButton;
