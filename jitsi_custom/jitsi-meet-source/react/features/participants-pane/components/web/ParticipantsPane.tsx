import React, { useCallback, useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useDispatch, useSelector } from 'react-redux';
import { makeStyles } from 'tss-react/mui';

import { IReduxState } from '../../../app/types';
import { openDialog } from '../../../base/dialog/actions';
import { IconCloseLarge, IconDotsHorizontal } from '../../../base/icons/svg';
import {
    getParticipantById,
    isLocalParticipantModerator,
    isScreenShareParticipant
} from '../../../base/participants/functions';
import Button from '../../../base/ui/components/web/Button';
import ClickableIcon from '../../../base/ui/components/web/ClickableIcon';
import { BUTTON_TYPES } from '../../../base/ui/constants.web';
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

import { FooterContextMenu } from './FooterContextMenu';
import LobbyParticipants from './LobbyParticipants';
import MeetingParticipants from './MeetingParticipants';
import VisitorsList from './VisitorsList';
import { InviteButton } from "./InviteButton";
import { isButtonEnabled } from "../../../toolbox/functions.web";
import { isMobileBrowser } from "../../../base/environment/utils";
import { isNeedShowElectronOnlyElements } from "../../../base/environment/utils_web";

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
                height: '95dvh',
                position: 'fixed',
                left: 0,
                right: 0,
                bottom: 0,
                width: '100%',
                borderRadius: '15px 15px 0 0',
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
            }
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
            paddingTop: '20px',
            paddingLeft: '20px',
            paddingRight: '16px',
            justifyContent: 'space-between',

            '&.is-mobile': {
                padding: '16px 16px 14px 16px',
            }
        },

        headerTitle: {
            fontSize: '16px',
            lineHeight: '24px',
            fontFamily: 'Lato Bold',
            fontWeight: 'normal' as const,
            letterSpacing: '-0.2px',

            '&.is-mobile': {
                fontSize: '20px',
                lineHeight: '28px',
                letterSpacing: '-0.3px',
            }
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

const ParticipantsPane = () => {
    const { classes, cx } = useStyles();
    const paneOpen = useSelector(getParticipantsPaneOpen);
    const showFooter = useSelector(isLocalParticipantModerator) && isNeedShowElectronOnlyElements();
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
    const isMobile = isMobileBrowser();
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

    if (!paneOpen || is_in_picture_in_picture_mode) {
        return null;
    }

    return (
        <>
            {isMobile && (<div className = {cx(classes.backdrop, isMobile && 'is-mobile')} />)}
            <div className = {cx('participants_pane', classes.participantsPane)}>
                <div className = {cx(classes.header, isMobile && 'is-mobile')}>
                    <div className = {cx(classes.headerTitle, isMobile && 'is-mobile')}>
                        {t('participantsPane.headings.participantsList', { count: participantsCount })}
                    </div>
                    <ClickableIcon
                        accessibilityLabel = {t('participantsPane.close', 'Close')}
                        icon = {IconCloseLarge}
                        onClick = {onClosePane} />
                </div>
                {isNeedShowElectronOnlyElements() && (
                    <div className = {classes.headerInviteButtonContainer}>
                        {showInviteButton && <InviteButton />}
                    </div>
                )}
                <div className = {cx(classes.container, isMobile && 'is-mobile')}>
                    <VisitorsList />
                    <LobbyParticipants />
                    <MeetingParticipants
                        searchString = {searchString}
                        setSearchString = {setSearchString} />
                </div>
                {showFooter && (
                    <div className = {cx(classes.footer, isMobile && 'is-mobile')}>
                        {isMobile ? (
                            showInviteButton && <InviteButton />
                        ) : (
                            showMuteAllButton && (
                                <Button
                                    accessibilityLabel = {t('participantsPane.actions.muteAll')}
                                    labelKey = {'participantsPane.actions.muteAll'}
                                    onClick = {onMuteAll}
                                    type = {BUTTON_TYPES.SECONDARY}
                                    fullWidth = {true} />
                            )
                        )}
                        {showMoreActionsButton && (
                            <div className = {classes.footerMoreContainer}>
                                <Button
                                    accessibilityLabel = {t('participantsPane.actions.moreModerationActions')}
                                    icon = {IconDotsHorizontal}
                                    id = 'participants-pane-context-menu'
                                    onClick = {onToggleContext}
                                    type = {BUTTON_TYPES.SECONDARY} />
                                <FooterContextMenu
                                    isOpen = {contextOpen}
                                    onDrawerClose = {onDrawerClose}
                                    onMouseLeave = {onToggleContext} />
                            </div>
                        )}
                    </div>
                )}
            </div>
        </>
    );
};


export default ParticipantsPane;
