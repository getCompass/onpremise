import clsx from 'clsx';
import React, { useCallback } from 'react';
import { makeStyles } from 'tss-react/mui';

import Avatar from '../../../base/avatar/components/Avatar';
import { IMessage } from '../../types';

import ChatMessage from './ChatMessage';
import { getPrivateNoticeMessage } from "../../functions";
import { MESSAGE_TYPE_LOCAL } from "../../constants";
import PrivateMessageButton from "./PrivateMessageButton";
import { isMobileBrowser } from "../../../base/environment/utils";
import { handleLobbyChatInitialized, openChat } from "../../actions.web";
import { useDispatch, useSelector } from "react-redux";
import { IReduxState } from "../../../app/types";
import { getLocalParticipant, getParticipantById } from "../../../base/participants/functions";
import { close as closeParticipantsPane } from "../../../participants-pane/actions.any";

interface IProps {

    /**
     * Additional CSS classes to apply to the root element.
     */
    className: string;

    /**
     * The messages to display as a group.
     */
    messages: Array<IMessage>;
}

const useStyles = makeStyles()(theme => {
    return {
        messageGroup: {
            display: 'flex',
            flexDirection: 'column',
            maxWidth: '100%',

            '&.remote': {
                maxWidth: 'calc(100% - 44px)' // 100% - avatar and margin
            },

            '&.local': {
                maxWidth: 'calc(100% - 44px)' // 100% - avatar and margin
            }
        },

        container: {
            display: 'flex',
            flexDirection: 'column',

            '&.privatemessage': {
                backgroundColor: 'rgba(0, 122, 255, 0.17)'
            },
        },

        groupContainer: {
            display: 'flex',
            padding: '0 16px',

            '&.is-mobile': {
                padding: '0 8px',
            }
        },

        privateMessageContainer: {
            display: 'flex',
            justifyContent: 'space-between',
            padding: '4px 12px 8px 16px',

            '&.is-mobile': {
                padding: '7px 8px 8px 8px',
            },
        },

        privateMessageNotice: {
            color: 'rgba(0, 107, 224, 1)',
            fontFamily: 'Lato Regular',
            fontWeight: 'normal' as const,
            fontSize: '13px',
            lineHeight: '16px',

            '&.is-mobile': {
                fontSize: '14px',
                lineHeight: '17px',
            },
        },

        replyButtonContainer: {
            color: 'rgba(0, 107, 224, 1)',
            fontFamily: 'Lato SemiBold',
            fontWeight: 'normal' as const,
            fontSize: '13px',
            lineHeight: '16px',
            display: 'flex',
            alignItems: 'center',
            gap: '4px',

            '& svg': {
                fill: 'rgba(0, 107, 224, 1) !important'
            },
        },

        avatarContainer: {
            height: '52px', // avatar height 36px + 8px margin-top + 8px margin-bottom
            '-webkit-tap-highlight-color': 'transparent',
        },

        avatar: {
            margin: '8px 8px 8px 0',
            position: 'sticky',
            flexShrink: 0,
            top: 0,

            '&.clickable': {
                cursor: 'pointer',
            }
        }
    };
});

const ChatMessageGroup = ({ className = '', messages }: IProps) => {
    const { classes, cx } = useStyles();
    const messagesLength = messages.length;
    const dispatch = useDispatch();
    const participant = useSelector((state: IReduxState) => getParticipantById(state, messages[0].participantId));
    const localParticipant = useSelector((state: IReduxState) => getLocalParticipant(state));
    const isParticipantPaneOpen = useSelector((state: IReduxState) => state['features/participants-pane'].isOpen);
    const isMobile = isMobileBrowser();

    const handleClick = useCallback(() => {

        // на себя клик не должен срабатывать
        if (localParticipant === undefined || localParticipant.id === messages[0].participantId) {
            return;
        }

        if (messages[0].lobbyChat) {
            dispatch(handleLobbyChatInitialized(messages[0].participantId));
        } else {

            if (isParticipantPaneOpen) {
                dispatch(closeParticipantsPane());
            }
            dispatch(openChat(participant));
        }
    }, []);

    if (!messagesLength) {
        return null;
    }

    return (
        <div className = {clsx(classes.container, messages[0].privateMessage && 'privatemessage')}>
            <div className = {clsx(classes.groupContainer, className, isMobile && 'is-mobile')}>
                <div className = {classes.avatarContainer} onClick = {() => handleClick()}>
                    <Avatar
                        className = {clsx(classes.avatar, 'avatar', (localParticipant !== undefined && localParticipant.id !== messages[0].participantId) && 'clickable')}
                        participantId = {messages[0].participantId}
                        size = {36}
                    />
                </div>
                <div className = {`${classes.messageGroup} chat-message-group ${className}`}>
                    {messages.map((message, i) => (
                        <ChatMessage
                            key = { i }
                            message = { message }
                            shouldDisplayChatMessageMenu = { false }
                            showDisplayName = { i === 0 }
                            showTimestamp = { i === messages.length - 1 }
                            type = { className } />
                    ))}
                </div>
            </div>
            {messages[0].privateMessage && (
                <div className = {cx(classes.privateMessageContainer, isMobile && 'is-mobile')}>
                    <div className = {cx(classes.privateMessageNotice, isMobile && 'is-mobile')}>
                        {getPrivateNoticeMessage(messages[0])}
                    </div>
                    {messages[0].messageType !== MESSAGE_TYPE_LOCAL && (
                        <div
                            className = {classes.replyButtonContainer}>
                            <PrivateMessageButton
                                isLobbyMessage = {messages[0].lobbyChat}
                                participantID = {messages[0].participantId} />
                        </div>
                    )}
                </div>)}
        </div>
    );
};

export default ChatMessageGroup;
