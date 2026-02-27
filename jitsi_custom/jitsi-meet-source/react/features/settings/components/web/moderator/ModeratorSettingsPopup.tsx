import React, { ReactNode } from 'react';
import { connect } from 'react-redux';
import { makeStyles } from 'tss-react/mui';

import { IReduxState } from '../../../../app/types';
import Popover from '../../../../base/popover/components/Popover.web';
import { SMALL_MOBILE_WIDTH } from '../../../../base/responsive-ui/constants';
import { getModeratorSettingsVisibility } from '../../../functions.web';

import ModeratorSettingsContent from './ModeratorSettingsContent';
import { toggleModeratorSettings } from "../../../actions.web";
import {getLocalParticipant, isParticipantModerator} from "../../../../base/participants/functions";
import UserSettings from "../user/UserSettings";
import {getWindowQueryData} from "../../../../desktop-picker/functions";


interface IProps {

    /**
    * Component's children (the moderator button).
    */
    children: ReactNode;

    /**
    * Flag controlling the visibility of the popup.
    */
    isOpen: boolean;

    /**
    * Callback executed when the popup closes.
    */
    onClose: Function;

    /**
     * The popup placement enum value.
     */
    popupPlacement: string;

    isModerator: boolean;
}

const useStyles = makeStyles()(() => {
    return {
        container: {
            display: 'inline-block'
        }
    };
});

/**
 * Popup with moderator settings.
 *
 * @returns {ReactElement}
 */
function ModeratorSettingsPopup({
    children,
    isOpen,
    onClose,
    popupPlacement,
    isModerator,
}: IProps) {
    const { classes, cx } = useStyles();
    const { isSupportPreJoinPage } = getWindowQueryData();

    const content = () => {
        if (isSupportPreJoinPage) {
            return isModerator ? <ModeratorSettingsContent /> : <UserSettings/>;
        }
        return <ModeratorSettingsContent />;
    }

    return (
        <div className = { classes.container }>
            <Popover
                allowClick = { true }
                content = { content() }
                headingId = 'moderator-settings-button'
                onPopoverClose = { onClose }
                position = { popupPlacement }
                trigger = 'click'
                visible = { isOpen }>
                {children}
            </Popover>
        </div>
    );
}

/**
 * Function that maps parts of Redux state tree into component props.
 *
 * @param {Object} state - Redux state.
 * @returns {Object}
 */
function mapStateToProps(state: IReduxState) {
    const { clientWidth } = state['features/base/responsive-ui'];
    const localParticipant = getLocalParticipant(state);
    const isModerator = isParticipantModerator(localParticipant);

    return {
        popupPlacement: clientWidth <= Number(SMALL_MOBILE_WIDTH) ? 'auto' : 'top-mid',
        isOpen: Boolean(getModeratorSettingsVisibility(state)),
        isModerator,
    };
}

const mapDispatchToProps = {
    onClose: toggleModeratorSettings,
};

export default connect(mapStateToProps, mapDispatchToProps)(ModeratorSettingsPopup);
