import React, { useCallback } from 'react';
import { connect } from 'react-redux';
import { makeStyles } from 'tss-react/mui';

import { IReduxState, IStore } from '../../../app/types';
import { hideNotification } from '../../actions';
import { areThereNotifications } from '../../functions';
import { INotificationProps } from '../../types';
import NotificationsTransition from '../NotificationsTransition';

import Notification from './Notification';
import { isMobileBrowser } from "../../../base/environment/utils";

interface IProps {

    /**
     * Whether we are a SIP gateway or not.
     */
    _iAmSipGateway: boolean;

    /**
     * Whether or not the chat is open.
     */
    _isChatOpen: boolean;

    /**
     * The notifications to be displayed, with the first index being the
     * notification at the top and the rest shown below it in order.
     */
    _notifications: Array<{
        props: INotificationProps;
        uid: string;
    }>;

    /**
     * Invoked to update the redux store in order to remove notifications.
     */
    dispatch: IStore['dispatch'];

    /**
     * Whether or not the notifications are displayed in a portal.
     */
    portal?: boolean;
}

const useStyles = makeStyles()(() => {
    return {
        backgroundContainer: {
            '&.is-mobile': {
                position: 'absolute',
                bottom: 0,
                width: '100%',
            }
        },
        background1: {
            '&.is-mobile': {
                background: 'linear-gradient(180deg, rgba(33, 33, 33, 0) 0%, rgba(33, 33, 33, 0.5) 100%)',
                height: '48px'
            }
        },
        background2: {
            '&.is-mobile': {
                background: 'linear-gradient(180deg, rgba(33, 33, 33, 0.5) 0%, rgba(33, 33, 33, 0.8) 100%)',
                height: '73px'
            }
        },
        background3: {
            '&.is-mobile': {
                background: 'linear-gradient(180deg, rgba(33, 33, 33, 0.8) 0%, rgba(33, 33, 33, 0.9) 100%)',
                height: '52px'
            }
        },
        background4: {
            '&.is-mobile': {
                background: 'rgba(33, 33, 33, 0.9)',
                height: '94px'
            }
        },
        container: {
            position: 'absolute',
            top: '12px',
            right: '12px',
            width: '316px',
            maxWidth: '100%',
            zIndex: 600,

            '&.is-mobile': {
                position: 'initial',
                top: '0px',
                right: '0px',
            },
        },

        containerPortal: {
            width: '100%',
            maxWidth: 'calc(100% - 32px)',

            '&.is-mobile': {
                maxWidth: '100%',
            },
        }
    };
});

const NotificationsContainer = ({
    _iAmSipGateway,
    _notifications,
    dispatch,
    portal
}: IProps) => {
    const { classes, cx } = useStyles();
    const isMobile = isMobileBrowser();

    const _onDismissed = useCallback((uid: string) => {
        dispatch(hideNotification(uid));
    }, []);

    if (_iAmSipGateway) {
        return null;
    }

    return (
        <>
            {(isMobile && _notifications.length > 0) && (
                <div className = {classes.backgroundContainer}>
                    <div className = {classes.background1} />
                    <div className = {classes.background2} />
                    <div className = {classes.background3} />
                    <div className = {classes.background4} />
                </div>
            )}
            <div
                className = {cx(classes.container, {
                    [classes.containerPortal]: portal
                })}
                id = 'notifications-container'>
                <NotificationsTransition>
                    {
                        _notifications.map(({ props, uid }, index) => {
                            // для последних 5 показываем обычное уведомление
                            if (index < 5) {
                                return (
                                    <Notification
                                        key = {uid}
                                        {...props}
                                        onDismissed = {_onDismissed}
                                        uid = {uid}
                                    />
                                );
                            }

                            // для всех остальных сразу вызываем _onDismissed и тоже «рисуем»,
                            // но они тут же пропадут
                            _onDismissed(uid);
                            return (
                                <Notification
                                    key = {uid}
                                    {...props}
                                    onDismissed = {_onDismissed}
                                    uid = {uid}
                                />
                            );
                        }) || null
                    }
                </NotificationsTransition>
            </div>
        </>
    );
};

/**
 * Maps (parts of) the Redux state to the associated props for this component.
 *
 * @param {Object} state - The Redux state.
 * @private
 * @returns {IProps}
 */
function _mapStateToProps(state: IReduxState) {
    const { notifications } = state['features/notifications'];
    const { iAmSipGateway } = state['features/base/config'];
    const { isOpen: isChatOpen } = state['features/chat'];
    const _visible = areThereNotifications(state);

    return {
        _iAmSipGateway: Boolean(iAmSipGateway),
        _isChatOpen: isChatOpen,
        _notifications: _visible ? notifications : []
    };
}

export default connect(_mapStateToProps)(NotificationsContainer);
