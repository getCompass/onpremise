import React, { useCallback } from 'react';
import { connect } from 'react-redux';
import { makeStyles } from 'tss-react/mui';

import { IReduxState, IStore } from '../../../app/types';
import { hideNotification } from '../../actions';
import { areThereNotifications } from '../../functions';
import { INotificationProps } from '../../types';
import NotificationsTransition from '../NotificationsTransition';

import Notification from './Notification';

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
        container: {
            position: 'absolute',
            bottom: 0,
            width: '100%',
        },
        backgroundContainer: {
            minHeight: '276px',
            opacity: '0.9',
            background: 'linear-gradient(180deg, rgba(23, 23, 23, 0) 0%, rgba(23, 23, 23, 0.5) 21.22%, rgba(23, 23, 23, 0.8) 41.72%, rgba(23, 23, 23, 0.9) 59.5%, #171717 98.72%)',
        },
        notificationsContainer: {
            position: 'absolute',
            top: '0px',
            right: '0px',
            minWidth: '100%',
            maxWidth: '100%',
            zIndex: 600,
            maxHeight: '172px',
            height: '100%',
            justifyContent: 'end',
            alignItems: 'start',
            display: 'flex',
            flexDirection: 'column',
        },
        notificationsScrollContainer: {
            width: '100%',
            overflow: 'scroll',
        },
        notificationsContainerPortal: {
            width: '100%',
            maxWidth: '100%',
        }
    };
});

const NotificationsContainerMobile = ({
    _iAmSipGateway,
    _notifications,
    dispatch,
    portal
}: IProps) => {
    const { classes, cx } = useStyles();

    const _onDismissed = useCallback((uid: string) => {
        dispatch(hideNotification(uid));
    }, []);

    if (_iAmSipGateway || _notifications.length < 1) {
        return null;
    }

    return (
        <div className = {classes.container}>
            <div className = {classes.backgroundContainer} />
            <div
                className = {cx(classes.notificationsContainer, {
                    [classes.notificationsContainerPortal]: portal
                })}
                id = 'notifications-container'>
                <div className = {classes.notificationsScrollContainer}>
                    <NotificationsTransition>
                        {_notifications.map(({ props, uid }) => (
                            <Notification
                                {...props}
                                key = {uid}
                                onDismissed = {_onDismissed}
                                uid = {uid} />
                        )) || null}
                    </NotificationsTransition>
                </div>
            </div>
        </div>
    );
}

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
        _notifications: _visible ? notifications.reverse() : []
    };
}

export default connect(_mapStateToProps)(NotificationsContainerMobile);
