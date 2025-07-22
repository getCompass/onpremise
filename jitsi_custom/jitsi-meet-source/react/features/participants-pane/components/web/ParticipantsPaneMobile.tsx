import React, { useCallback, useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useDispatch, useSelector } from 'react-redux';
import { makeStyles } from 'tss-react/mui';

import { IReduxState } from '../../../app/types';
import { IconCloseLarge } from '../../../base/icons/svg';
import { getParticipantById, isScreenShareParticipant } from '../../../base/participants/functions';
import ClickableIcon from '../../../base/ui/components/web/ClickableIcon';
import { findAncestorByClass } from '../../../base/ui/functions.web';
import { close } from '../../actions.web';
import { getParticipantsPaneOpen, getSortedParticipantIds } from '../../functions';
import LobbyParticipants from './LobbyParticipants';
import MeetingParticipants from './MeetingParticipants';
import VisitorsListMobile from "./VisitorsListMobile";
import { getVisitorsCount } from "../../../visitors/functions";
import { openDialog } from "../../../base/dialog/actions";
import VisitorsTooltipDialog from "../../../visitors/components/web/VisitorsTooltipDialog";

const useStyles = makeStyles()(theme => {
    return {
        backdrop: {
            position: 'absolute',
            width: '100%',
            height: '100%',
            top: 0,
            left: 0,
            backgroundColor: 'rgba(0, 0, 0, 1)',
            opacity: 0.9,
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
            padding: 0,

            '&::-webkit-scrollbar': {
                display: 'none'
            },

            '& > div': {

                '& > .list-item-container': {

                    '&:last-child': {
                        '& > .list-item-details-container': {
                            borderBottom: '0.5px solid transparent',
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
            padding: '16px 16px 4px 16px',
            justifyContent: 'space-between',
        },

        headerTitle: {
            fontFamily: 'Lato Bold',
            fontWeight: 'normal' as const,
            fontSize: '20px',
            lineHeight: '28px',
            letterSpacing: '-0.3px',
        },

        headerVisitorsContainer: {
            padding: "0px 16px 6px 16px",
            width: "fit-content",
        },

        headerVisitors: {
            fontFamily: 'Lato Regular',
            fontWeight: 'normal' as const,
            fontSize: '13px',
            lineHeight: '16px',
            paddingBottom: "3px",
            color: 'rgba(255, 255, 255, 0.75)',
            borderBottom: "1px dashed rgba(255, 255, 255, 0.4)",
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
                marginRight: '12px',
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
    const dispatch = useDispatch();
    const state = useSelector((state: IReduxState) => state);
    const { t } = useTranslation();
    let sortedParticipantIds: any = getSortedParticipantIds(state);
    sortedParticipantIds = sortedParticipantIds.filter((id: any) => {
        const participant = getParticipantById(state, id);

        return !isScreenShareParticipant(participant);
    });
    const { is_in_picture_in_picture_mode } = useSelector((state: IReduxState) => state['features/picture-in-picture']);

    const visitorsCount = useSelector(getVisitorsCount);
    const participantsCount = sortedParticipantIds.length;

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

    const onVisitorsTitleClick = useCallback(() => {
        dispatch(openDialog(VisitorsTooltipDialog));
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
            <div className = {cx(classes.backdrop)} />
            <div className = {cx('participants_pane', classes.participantsPane)}>
                <div className = {cx(classes.header)}>
                    <div className = {cx(classes.headerTitle)}>
                        {t('participantsPane.headings.participantsList', { count: participantsCount })}
                    </div>
                    <ClickableIcon
                        accessibilityLabel = {t('participantsPane.close', 'Close')}
                        icon = {IconCloseLarge}
                        onClick = {onClosePane} />
                </div>
                {visitorsCount > 0 && (
                    <div className = {classes.headerVisitorsContainer}>
                        <div className = {classes.headerVisitors} onClick={() => onVisitorsTitleClick()}>
                            {t("participantsPane.headings.visitors", { count: visitorsCount })}
                        </div>
                    </div>
                )}
                <div className = {cx(classes.container)}>
                    <VisitorsListMobile />
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
