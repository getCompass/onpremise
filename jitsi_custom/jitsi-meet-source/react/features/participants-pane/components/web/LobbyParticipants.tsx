import React, {useCallback} from 'react';
import {useTranslation} from 'react-i18next';
import {useDispatch, useSelector} from 'react-redux';
import {makeStyles} from 'tss-react/mui';

import Avatar from '../../../base/avatar/components/Avatar';
import Icon from '../../../base/icons/components/Icon';
import {IconCheck, IconCloseLarge} from '../../../base/icons/svg';
import {withPixelLineHeight} from '../../../base/styles/functions.web';
import {admitMultiple} from '../../../lobby/actions.web';
import {getKnockingParticipants, getLobbyEnabled} from '../../../lobby/functions';
import Drawer from '../../../toolbox/components/web/Drawer';
import JitsiPortal from '../../../toolbox/components/web/JitsiPortal';
import {showOverflowDrawer} from '../../../toolbox/functions.web';
import {useLobbyActions, useParticipantDrawer} from '../../hooks';

import LobbyParticipantItems from './LobbyParticipantItems';
import {isMobileBrowser} from "../../../base/environment/utils";

const useStyles = makeStyles()(theme => {
    return {
        separateLineContainer: {
            padding: '0 2px',
            marginBottom: '20px',
        },
        drawerActions: {
            listStyleType: 'none',
            margin: 0,
            padding: 0
        },
        drawerItem: {
            alignItems: 'center',
            color: theme.palette.text01,
            display: 'flex',
            padding: '12px 16px',
            ...withPixelLineHeight(theme.typography.bodyShortRegularLarge),

            '&:first-child': {
                marginTop: '15px'
            },

            '&:hover': {
                cursor: 'pointer',
                background: theme.palette.action02
            }
        },
        icon: {
            marginRight: 16
        },
        headingContainer: {
            padding: '0 0 0 4px',
            alignItems: 'center',
            display: 'flex',
            justifyContent: 'space-between',
            marginBottom: '10px',

            '&.is-mobile': {
                padding: '8px 16px 10px 16px',
                marginBottom: 0,
            }
        },
        heading: {
            fontFamily: 'Lato Regular',
            fontWeight: 'normal' as const,
            textTransform: 'uppercase',
            fontSize: '12px',
            lineHeight: '15px',
            color: 'rgba(255, 255, 255, 0.4)',

            '&.is-mobile': {
                fontFamily: 'Lato Regular',
                fontWeight: 'normal' as const,
                textTransform: 'uppercase',
                fontSize: '13px',
                lineHeight: '18px',
                letterSpacing: '-0.1px',
                color: 'rgba(255, 255, 255, 0.3)'
            }
        },
        link: {
            fontFamily: 'Lato Regular',
            fontWeight: 'normal' as const,
            fontSize: '12px',
            lineHeight: '15px',
            color: 'rgba(0, 107, 224, 1)',
            cursor: 'pointer',

            '&:hover': {
                color: 'rgba(0, 88, 184, 1)',
            }
        }
    };
});

/**
 * Component used to display a list of participants waiting in the lobby.
 *
 * @returns {ReactNode}
 */
export default function LobbyParticipants() {
    const lobbyEnabled = useSelector(getLobbyEnabled);
    const participants = useSelector(getKnockingParticipants);
    const {t} = useTranslation();
    const isMobile = isMobileBrowser();
    const {classes, cx} = useStyles();
    const dispatch = useDispatch();
    const admitAll = useCallback(() => {
        dispatch(admitMultiple(participants));
    }, [dispatch, participants]);
    const overflowDrawer = useSelector(showOverflowDrawer);

    if (!lobbyEnabled || !participants.length) {
        return null;
    }

    return (
        <>
            {!isMobileBrowser() && <div className={classes.separateLineContainer}>
                <div className={cx('dotted-separate-line')}/>
            </div>}
            <div className={cx(classes.headingContainer, isMobile && 'is-mobile')}>
                <div className={cx(classes.heading, isMobile && 'is-mobile')}>
                    {isMobile ? t('participantsPane.headings.lobbyMobile') : t('participantsPane.headings.lobby', {count: participants.length})}
                </div>
                {
                    participants.length > 1
                    && <div
                        className={classes.link}
                        onClick={admitAll}>{t('participantsPane.actions.admitAll')}</div>
                }
            </div>
            <LobbyParticipantItems
                openDrawerForParticipant={() => null}
                overflowDrawer={overflowDrawer}
                participants={participants}/>
        </>
    );
}
