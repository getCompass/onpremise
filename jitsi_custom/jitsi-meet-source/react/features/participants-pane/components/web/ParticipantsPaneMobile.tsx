import React, { useCallback, useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useDispatch, useSelector } from 'react-redux';
import { makeStyles } from 'tss-react/mui';

import { IReduxState } from '../../../app/types';
import { openDialog } from '../../../base/dialog/actions';
import { IconCloseLarge } from '../../../base/icons/svg';
import {
    getParticipantById,
    isLocalParticipantModerator,
    isScreenShareParticipant
} from '../../../base/participants/functions';
import ClickableIcon from '../../../base/ui/components/web/ClickableIcon';
import { findAncestorByClass } from '../../../base/ui/functions.web';
import MuteEveryoneDialog from '../../../video-menu/components/web/MuteEveryoneDialog';
import { close } from '../../actions.web';
import {
    getParticipantsPaneOpen,
    getSortedParticipantIds,
    isMoreActionsVisible,
    isMuteAllVisible,
    shouldRenderInviteButton
} from '../../functions';
import LobbyParticipants from './LobbyParticipants';
import MeetingParticipants from './MeetingParticipants';
import VisitorsList from './VisitorsList';
import { isButtonEnabled } from "../../../toolbox/functions.web";

const useStyles = makeStyles()(theme => {
    return {
        backdrop: {
            position: 'absolute',
            width: '100%',
            height: '100%',
            top: 0,
            left: 0,
            backgroundColor: 'rgba(0, 0, 0, 1)',
            opacity: 0.8,

            '&.is-mobile': {
                opacity: 0.9,
            },
        },

        participantsPane: {
            backgroundColor: 'rgba(33, 33, 33, 1)',
            flexShrink: 0,
            position: 'relative',
            transition: 'width .16s ease-in-out',
            width: '316px',
            zIndex: 0,
            display: 'flex',
            flexDirection: 'column',
            fontWeight: 600,
            height: '100%',

            [[ '& > *:first-child', '& > *:last-child' ] as any]: {
                flexShrink: 0
            },

            '@media (max-width: 580px)': {
                height: '100dvh',
                position: 'fixed',
                left: 0,
                right: 0,
                bottom: 0,
                width: '100%',
                backgroundColor: 'rgba(28, 28, 28, 1)',
            }
        },

        container: {
            boxSizing: 'border-box',
            flex: 1,
            overflowY: 'auto',
            position: 'relative',
            padding: '0 16px',

            '&::-webkit-scrollbar': {
                display: 'none'
            },

            '&.is-mobile': {
                padding: 0,
            },

            '& > div': {

                '& > .list-item-container': {

                    '&:last-child': {
                        '& > .list-item-details-container': {
                            '&.is-mobile': {
                                borderBottom: '0.5px solid transparent',
                            },
                        },
                    },
                },
            },
        },

        closeButton: {
            alignItems: 'center',
            cursor: 'pointer',
            display: 'flex',
            justifyContent: 'center'
        },

        header: {
            alignItems: 'center',
            boxSizing: 'border-box',
            display: 'flex',
            padding: '16px 16px 14px 16px',
            justifyContent: 'space-between',
        },

        headerTitle: {
            fontFamily: 'Lato Bold',
            fontWeight: 'normal' as const,
            fontSize: '20px',
            lineHeight: '28px',
            letterSpacing: '-0.3px',
        },

        headerInviteButtonContainer: {
            marginTop: '14px',
            marginBottom: '19px',
            padding: '0px 16px'
        },

        antiCollapse: {
            fontSize: 0,

            '&:first-child': {
                display: 'none'
            },

            '&:first-child + *': {
                marginTop: 0
            }
        },

        footer: {
            display: 'flex',
            justifyContent: 'flex-end',
            padding: '2px 16px 16px 16px',

            '& > *:not(:last-child)': {
                marginRight: '8px',

                '&.is-mobile': {
                    marginRight: '12px',
                }
            }
        },

        footerMoreContainer: {
            position: 'relative'
        }
    };
});

const ParticipantsPaneMobile = () => {
    const { classes, cx } = useStyles();
    const paneOpen = useSelector(getParticipantsPaneOpen);
    const showFooter = useSelector(isLocalParticipantModerator);
    const showMuteAllButton = useSelector(isMuteAllVisible);
    const showMoreActionsButton = useSelector(isMoreActionsVisible);
    const dispatch = useDispatch();
    const state = useSelector((state: IReduxState) => state);
    const { t } = useTranslation();
    let sortedParticipantIds: any = getSortedParticipantIds(state);
    sortedParticipantIds = sortedParticipantIds.filter((id: any) => {
        const participant = getParticipantById(state, id);

        return !isScreenShareParticipant(participant);
    });
    const { is_in_picture_in_picture_mode } = useSelector((state: IReduxState) => state['features/picture-in-picture']);

    const participantsCount = sortedParticipantIds.length;
    const showInviteButton = shouldRenderInviteButton(state) && isButtonEnabled('invite', state);

    const [ contextOpen, setContextOpen ] = useState(false);
    const [ searchString, setSearchString ] = useState('');

    const onWindowClickListener = useCallback((e: any) => {
        if (contextOpen && !findAncestorByClass(e.target, classes.footerMoreContainer)) {
            setContextOpen(false);
        }
    }, [ contextOpen ]);

    useEffect(() => {
        window.addEventListener('click', onWindowClickListener);

        return () => {
            window.removeEventListener('click', onWindowClickListener);
        };
    }, []);

    const onClosePane = useCallback(() => {
        dispatch(close());
    }, []);

    const onDrawerClose = useCallback(() => {
        setContextOpen(false);
    }, []);

    const onMuteAll = useCallback(() => {
        dispatch(openDialog(MuteEveryoneDialog));
    }, []);

    const onToggleContext = useCallback(() => {
        setContextOpen(open => !open);
    }, []);

    // ловим событие "назад" и закрываем участников
    useEffect(() => {
        // обрабатываем событие popstate
        const handlePopState = (event: PopStateEvent) => {

            if (!paneOpen) {
                return;
            }

            // закрываем окно участников
            onClosePane();

            // возвращаем текущее состояние в историю, чтобы предотвратить навигацию назад
            window.history.pushState(null, '', window.location.href);
        };

        // добавляем начальное состояние в историю
        if (paneOpen) {
            window.history.pushState(null, '', window.location.href);
        }

        // добавляем обработчик события popstate
        window.addEventListener('popstate', handlePopState);

        // очистка обработчика при размонтировании компонента
        return () => {
            window.removeEventListener('popstate', handlePopState);
        };
    }, [ paneOpen ]);

    if (!paneOpen || is_in_picture_in_picture_mode) {
        return null;
    }

    return (
        <>
            <div className = {cx(classes.backdrop, 'is-mobile')} />
            <div className = {cx('participants_pane', classes.participantsPane)}>
                <div className = {cx(classes.header, 'is-mobile')}>
                    <div className = {cx(classes.headerTitle, 'is-mobile')}>
                        {t('participantsPane.headings.participantsList', { count: participantsCount })}
                    </div>
                    <ClickableIcon
                        accessibilityLabel = {t('participantsPane.close', 'Close')}
                        icon = {IconCloseLarge}
                        onClick = {onClosePane} />
                </div>
                <div className = {cx(classes.container, 'is-mobile')}>
                    <VisitorsList />
                    <LobbyParticipants />
                    <MeetingParticipants
                        searchString = {searchString}
                        setSearchString = {setSearchString} />
                </div>
            </div>
        </>
    );
};


export default ParticipantsPaneMobile;
