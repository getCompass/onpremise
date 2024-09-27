import {Theme} from '@mui/material';
import React, {useCallback} from 'react';
import {connect, useDispatch, useSelector} from 'react-redux';
import {makeStyles} from 'tss-react/mui';

import {IReduxState} from '../../../app/types';
import {translate} from '../../../base/i18n/functions';
import Message from '../../../base/react/components/web/Message';
import {getCanReplyToMessage, getFormattedTimestamp, getMessageText} from '../../functions';
import {IChatMessageProps} from '../../types';
import {handleLobbyChatInitialized} from "../../actions.any";
import {openChat} from "../../actions.web";
import {getLocalParticipant, getParticipantById} from "../../../base/participants/functions";

interface IProps extends IChatMessageProps {

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

        displayNameTimestampContainer: {
            display: 'flex',
            gap: '4px',
            '-webkit-tap-highlight-color': 'transparent',
        },

        displayName: {
            fontFamily: 'Lato Bold',
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
            fontSize: '15px',
            lineHeight: '22px',
            color: 'rgba(255, 255, 255, 0.75)',
            whiteSpace: 'pre-wrap',
            wordBreak: 'break-word'
        },

        timestamp: {
            fontFamily: 'Lato Regular',
            fontSize: '12px',
            lineHeight: '15px',
            color: 'rgba(255, 255, 255, 0.2)',
            marginTop: '5px',
        }
    };
});

/**
 * Renders a single chat message.
 *
 * @param {IProps} props - Component's props.
 * @returns {JSX}
 */
const ChatMessage = ({
                         canReply,
                         knocking,
                         message,
                         showDisplayName,
                         showTimestamp,
                         type,
                         t
                     }: IProps) => {
    const {classes, cx} = useStyles();

    /**
     * Renders the display name of the sender.
     *
     * @returns {React$Element<*>}
     */
    function _renderDisplayName() {
        const dispatch = useDispatch();
        const participant = useSelector((state: IReduxState) => getParticipantById(state, message.id));
        const localParticipant = useSelector((state: IReduxState) => getLocalParticipant(state));
        const handleClick = useCallback(() => {

            // на себя клик не должен срабатывать
            if (localParticipant === undefined || localParticipant.id === message.id) {
                return;
            }

            if (message.lobbyChat) {
                dispatch(handleLobbyChatInitialized(message.id));
            } else {
                dispatch(openChat(participant));
            }
        }, []);

        return (
            <div
                aria-hidden={true}
                className={cx('display-name', classes.displayName, (localParticipant !== undefined && localParticipant.id !== message.id) && 'clickable')}
                onClick={() => handleClick()}>
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
            <div className={cx('timestamp', classes.timestamp)}>
                {getFormattedTimestamp(message)}
            </div>
        );
    }

    return (
        <div
            className={cx(classes.chatMessageWrapper, type)}
            id={message.messageId}
            tabIndex={-1}>
            <div
                className={cx('chatmessage', classes.chatMessage, type,
                    message.privateMessage && 'privatemessage',
                    message.lobbyChat && !knocking && 'lobbymessage')}>
                <div className={classes.replyWrapper}>
                    <div className={cx('messagecontent', classes.messageContent)}>
                        {showDisplayName && (
                            <div className={classes.displayNameTimestampContainer}>
                                {_renderDisplayName()}
                                {_renderTimestamp()}
                            </div>
                        )}
                        <div className={cx('usermessage', classes.userMessage)}>
                            <Message text={getMessageText(message)}/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

/**
 * Maps part of the Redux store to the props of this component.
 *
 * @param {Object} state - The Redux state.
 * @returns {IProps}
 */
function _mapStateToProps(state: IReduxState, {message}: IProps) {
    const {knocking} = state['features/lobby'];

    return {
        canReply: getCanReplyToMessage(state, message),
        knocking
    };
}

export default translate(connect(_mapStateToProps)(ChatMessage));
