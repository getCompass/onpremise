import { Theme } from '@mui/material';
import React, { useCallback, useState } from 'react';
import { connect, useDispatch, useSelector } from 'react-redux';
import { makeStyles } from 'tss-react/mui';

import { IReduxState } from '../../../app/types';
import { translate } from '../../../base/i18n/functions';
import { getParticipantDisplayName } from '../../../base/participants/functions';
import Popover from '../../../base/popover/components/Popover.web';
import Message from '../../../base/react/components/web/Message';
import { getFormattedTimestamp, getMessageText } from '../../functions';
import { IChatMessageProps } from '../../types';
import { handleLobbyChatInitialized } from "../../actions.any";
import { openChat } from "../../actions.web";
import { getLocalParticipant, getParticipantById } from "../../../base/participants/functions";

import MessageMenu from './MessageMenu';
import ReactButton from './ReactButton';
import { close as closeParticipantsPane } from "../../../participants-pane/actions.any";

interface IProps extends IChatMessageProps {
    shouldDisplayChatMessageMenu: boolean;
    state?: IReduxState;
    type: string;
}

const useStyles = makeStyles()((theme: Theme) => {
    return {
        chatMessageWrapper: {
            maxWidth: '100%'
        },
        chatMessage: {
            display: 'inline-flex',
            maxWidth: '100%',
            boxSizing: 'border-box' as const,
            '&.error': {
                backgroundColor: theme.palette.actionDanger,
                borderRadius: 0,
                fontWeight: 100
            },
            '&.lobbymessage': {
                backgroundColor: theme.palette.support05
            }
        },
        sideBySideContainer: {
            display: 'flex',
            flexDirection: 'row',
            justifyContent: 'left',
            alignItems: 'center',
            marginLeft: theme.spacing(1)
        },
        reactionBox: {
            display: 'flex',
            alignItems: 'center',
            gap: theme.spacing(1),
            backgroundColor: theme.palette.grey[800],
            borderRadius: theme.shape.borderRadius,
            padding: theme.spacing(0, 1),
            cursor: 'pointer'
        },
        reactionCount: {
            fontSize: '0.8rem',
            color: theme.palette.grey[400]
        },
        replyButton: {
            padding: '2px',

            '&:not(:disabled)': {
                '&:hover': {
                    backgroundColor: 'transparent'
                },

                '&:active': {
                    backgroundColor: 'transparent'
                }
            },
        },
        replyWrapper: {
            display: 'flex',
            flexDirection: 'row' as const,
            alignItems: 'center',
            maxWidth: '100%'
        },
        messageContent: {
            maxWidth: '100%',
            overflow: 'hidden',
            flex: 1
        },
        optionsButtonContainer: {
            display: 'flex',
            flexDirection: 'column',
            alignItems: 'center',
            gap: theme.spacing(1),
            minWidth: '32px',
            minHeight: '32px'
        },

        displayNameTimestampContainer: {
            display: 'flex',
            gap: '4px',
            '-webkit-tap-highlight-color': 'transparent',
        },

        displayName: {
            fontFamily: 'Lato Bold',
            fontWeight: 'normal' as const,
            fontSize: '15px',
            lineHeight: '22px',
            color: 'rgba(255, 255, 255, 0.75)',
            whiteSpace: 'nowrap',
            textOverflow: 'ellipsis',
            overflow: 'hidden',

            '&.clickable': {
                cursor: 'pointer',
            }
        },

        userMessage: {
            fontFamily: 'Lato Regular',
            fontWeight: 'normal' as const,
            fontSize: '15px',
            lineHeight: '22px',
            color: 'rgba(255, 255, 255, 0.75)',
            whiteSpace: 'pre-wrap',
            wordBreak: 'break-word'
        },

        timestamp: {
            fontFamily: 'Lato Regular',
            fontWeight: 'normal' as const,
            fontSize: '12px',
            lineHeight: '15px',
            color: 'rgba(255, 255, 255, 0.2)',
            marginTop: '5px',
        },
        reactionsPopover: {
            padding: theme.spacing(2),
            backgroundColor: theme.palette.ui03,
            borderRadius: theme.shape.borderRadius,
            maxWidth: '150px',
            maxHeight: '400px',
            overflowY: 'auto',
            color: theme.palette.text01
        },
        reactionItem: {
            display: 'flex',
            alignItems: 'center',
            marginBottom: theme.spacing(1),
            gap: theme.spacing(1),
            borderBottom: `1px solid ${theme.palette.common.white}`,
            paddingBottom: theme.spacing(1),
            '&:last-child': {
                borderBottom: 'none',
                paddingBottom: 0
            }
        },
        participantList: {
            marginLeft: theme.spacing(1),
            fontSize: '0.8rem',
            maxWidth: '120px'
        },
        participant: {
            overflow: 'hidden',
            textOverflow: 'ellipsis',
            whiteSpace: 'nowrap'
        }
    };
});

const ChatMessage = ({
    message,
    state,
    showDisplayName,
    type,
    shouldDisplayChatMessageMenu,
    knocking,
    t
}: IProps) => {
    const { classes, cx } = useStyles();
    const [ isHovered, setIsHovered ] = useState(false);
    const [ isReactionsOpen, setIsReactionsOpen ] = useState(false);

    const handleMouseEnter = useCallback(() => {
        setIsHovered(true);
    }, []);

    const handleMouseLeave = useCallback(() => {
        setIsHovered(false);
    }, []);

    const handleReactionsOpen = useCallback(() => {
        setIsReactionsOpen(true);
    }, []);

    const handleReactionsClose = useCallback(() => {
        setIsReactionsOpen(false);
    }, []);

    /**
     * Renders the display name of the sender.
     *
     * @returns {React$Element<*>}
     */
    function _renderDisplayName() {
        const dispatch = useDispatch();
        const participant = useSelector((state: IReduxState) => getParticipantById(state, message.participantId));
        const localParticipant = useSelector((state: IReduxState) => getLocalParticipant(state));
        const isParticipantPaneOpen = useSelector((state:IReduxState) => state['features/participants-pane'].isOpen);
        const handleClick = useCallback(() => {

            // на себя клик не должен срабатывать
            if (localParticipant === undefined || localParticipant.id === message.participantId) {
                return;
            }

            if (message.lobbyChat) {
                dispatch(handleLobbyChatInitialized(message.participantId));
            } else {

                if (isParticipantPaneOpen) {
                    dispatch(closeParticipantsPane());
                }
                dispatch(openChat(participant));
            }
        }, []);

        return (
            <div
                aria-hidden = {true}
                className = {cx('display-name', classes.displayName, (localParticipant !== undefined && localParticipant.id !== message.participantId) && 'clickable')}
                onClick = {() => handleClick()}>
                {message.displayName}
            </div>
        );
    }

    /**
     * Renders the time at which the message was sent.
     *
     * @returns {React$Element<*>}
     */
    function _renderTimestamp() {
        return (
            <div className = {cx('timestamp', classes.timestamp)}>
                {getFormattedTimestamp(message)}
            </div>
        );
    }

    /**
     * Renders the reactions for the message.
     *
     * @returns {React$Element<*>}
     */
    const renderReactions = () => {
        if (!message.reactions || message.reactions.size === 0) {
            return null;
        }

        const reactionsArray = Array.from(message.reactions.entries())
            .map(([ reaction, participants ]) => {
                return {
                    reaction,
                    participants
                };
            })
            .sort((a, b) => b.participants.size - a.participants.size);

        const totalReactions = reactionsArray.reduce((sum, { participants }) => sum + participants.size, 0);
        const numReactionsDisplayed = 3;

        const reactionsContent = (
            <div className = {classes.reactionsPopover}>
                {reactionsArray.map(({ reaction, participants }) => (
                    <div
                        className = {classes.reactionItem}
                        key = {reaction}>
                        <span>{reaction}</span>
                        <span>{participants.size}</span>
                        <div className = {classes.participantList}>
                            {Array.from(participants).map(participantId => (
                                <div
                                    className = {classes.participant}
                                    key = {participantId}>
                                    {state && getParticipantDisplayName(state, participantId)}
                                </div>
                            ))}
                        </div>
                    </div>
                ))}
            </div>
        );

        return (
            <Popover
                content = {reactionsContent}
                onPopoverClose = {handleReactionsClose}
                onPopoverOpen = {handleReactionsOpen}
                position = 'top'
                trigger = 'hover'
                visible = {isReactionsOpen}>
                <div className = {classes.reactionBox}>
                    {reactionsArray.slice(0, numReactionsDisplayed).map(({ reaction }, index) =>
                        <span key = {index}>{reaction}</span>
                    )}
                    {reactionsArray.length > numReactionsDisplayed && (
                        <span className = {classes.reactionCount}>
                            +{totalReactions - numReactionsDisplayed}
                        </span>
                    )}
                </div>
            </Popover>
        );
    };

    return (
        <div
            className = {cx(classes.chatMessageWrapper, type)}
            id = {message.messageId}
            onMouseEnter = {handleMouseEnter}
            onMouseLeave = {handleMouseLeave}
            tabIndex = {-1}>
            {/*<div className = {classes.sideBySideContainer}>*/}
            {/*{!shouldDisplayChatMessageMenu && (*/}
            {/*    <div className = {classes.optionsButtonContainer}>*/}
            {/*        {isHovered && <MessageMenu*/}
            {/*            isLobbyMessage = {message.lobbyChat}*/}
            {/*            message = {message.message}*/}
            {/*            participantId = {message.participantId}*/}
            {/*            shouldDisplayChatMessageMenu = {shouldDisplayChatMessageMenu} />}*/}
            {/*    </div>*/}
            {/*)}*/}
            <div
                className = {cx('chatmessage', classes.chatMessage, type,
                    message.privateMessage && 'privatemessage',
                    message.lobbyChat && !knocking && 'lobbymessage')}>
                <div className = {classes.replyWrapper}>
                    <div className = {cx('messagecontent', classes.messageContent)}>
                        {showDisplayName && (
                            <div className = {classes.displayNameTimestampContainer}>
                                {_renderDisplayName()}
                                {_renderTimestamp()}
                            </div>
                        )}
                        <div className = {cx('usermessage', classes.userMessage)}>
                            <Message text = {getMessageText(message)} />
                        </div>
                    </div>
                </div>
            </div>
            {/*{shouldDisplayChatMessageMenu && (*/}
            {/*    <div className = { classes.sideBySideContainer }>*/}
            {/*        {!message.privateMessage && <div>*/}
            {/*            <div className = { classes.optionsButtonContainer }>*/}
            {/*                {isHovered && <ReactButton*/}
            {/*                    messageId = { message.messageId }*/}
            {/*                    receiverId = { '' } />}*/}
            {/*            </div>*/}
            {/*        </div>}*/}
            {/*        <div>*/}
            {/*            <div className = { classes.optionsButtonContainer }>*/}
            {/*                {isHovered && <MessageMenu*/}
            {/*                    isLobbyMessage = { message.lobbyChat }*/}
            {/*                    message = { message.message }*/}
            {/*                    participantId = { message.participantId }*/}
            {/*                    shouldDisplayChatMessageMenu = { shouldDisplayChatMessageMenu } />}*/}
            {/*            </div>*/}
            {/*        </div>*/}
            {/*    </div>*/}
            {/*)}*/}
            {/*</div>*/}
        </div>
    );
};

/**
 * Maps part of the Redux store to the props of this component.
 *
 * @param {Object} state - The Redux state.
 * @returns {IProps}
 */
function _mapStateToProps(state: IReduxState, { message }: IProps) {
    const { knocking } = state['features/lobby'];
    const localParticipantId = state['features/base/participants'].local?.id;

    return {
        shouldDisplayChatMessageMenu: message.participantId !== localParticipantId,
        knocking,
        state
    };
}

export default translate(connect(_mapStateToProps)(ChatMessage));
