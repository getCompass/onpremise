import React, { useCallback } from 'react';
import { useTranslation } from 'react-i18next';
import { useDispatch, useSelector } from 'react-redux';
import { makeStyles } from 'tss-react/mui';

import { IReduxState } from '../../../app/types';
import { CHAT_ENABLED } from '../../../base/flags/constants';
import { getFeatureFlag } from '../../../base/flags/functions';
import { IconReply } from '../../../base/icons/svg';
import { getParticipantById } from '../../../base/participants/functions';
import Button from '../../../base/ui/components/web/Button';
import { BUTTON_TYPES } from '../../../base/ui/constants.any';
import { handleLobbyChatInitialized, openChat } from '../../actions.web';
import Icon from "../../../base/icons/components/Icon";
import { isMobileBrowser } from "../../../base/environment/utils";
import { close as closeParticipantsPane } from "../../../participants-pane/actions.any";

export interface IProps {

    /**
     * True if the message is a lobby chat message.
     */
    isLobbyMessage: boolean;

    /**
     * The ID of the participant that the message is to be sent.
     */
    participantID: string;

    /**
     * Whether the button should be visible or not.
     */
    visible?: boolean;
}

const useStyles = makeStyles()(theme => {
    return {
        replyButton: {
            padding: 0,
            color: 'rgba(0, 107, 224, 1)',
            backgroundColor: "transparent",
            fontFamily: 'Lato SemiBold',
            fontWeight: 'normal' as const,
            fontSize: '13px',
            lineHeight: '16px',

            '&:hover': {
                backgroundColor: "transparent"
            },

            '&:not(:disabled)': {
                '&:hover': {
                    backgroundColor: 'transparent'
                },

                '&:active': {
                    backgroundColor: 'transparent'
                }
            },


            '&.is-mobile': {
                padding: 0,
                fontSize: '14px',
                lineHeight: '17px',
                height: 'initial',
            }
        }
    };
});

const PrivateMessageButton = ({ participantID, isLobbyMessage, visible }: IProps) => {
    const { classes } = useStyles();
    const dispatch = useDispatch();
    const participant = useSelector((state: IReduxState) => getParticipantById(state, participantID));
    const isVisible = useSelector((state: IReduxState) => getFeatureFlag(state, CHAT_ENABLED, true)) ?? visible;
    const isParticipantPaneOpen = useSelector((state: IReduxState) => state['features/participants-pane'].isOpen);
    const { t } = useTranslation();

    const handleClick = useCallback(() => {
        if (isLobbyMessage) {
            dispatch(handleLobbyChatInitialized(participantID));
        } else {

            if (isParticipantPaneOpen) {
                dispatch(closeParticipantsPane());
            }
            dispatch(openChat(participant));
        }
    }, []);

    if (!isVisible) {
        return null;
    }

    return (
        <>
            <Button
                accessibilityLabel = {t('toolbar.accessibilityLabel.privateMessage')}
                className = {classes.replyButton}
                label = {t('chat.privateMessageReply')}
                onClick = {handleClick}
                type = {BUTTON_TYPES.TERTIARY} />
            <Icon
                size = {isMobileBrowser() ? 17 : 14}
                src = {IconReply} />
        </>
    );
};

export default PrivateMessageButton;
