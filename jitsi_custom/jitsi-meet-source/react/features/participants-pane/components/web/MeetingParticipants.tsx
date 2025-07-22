import React, { useCallback } from 'react';
import { useTranslation } from 'react-i18next';
import { connect, useDispatch, useSelector } from 'react-redux';
import { makeStyles } from 'tss-react/mui';

import { IReduxState } from '../../../app/types';
import { rejectParticipantAudio, rejectParticipantVideo } from '../../../av-moderation/actions';
import { MEDIA_TYPE } from '../../../base/media/constants';
import { getParticipantById, isScreenShareParticipant } from '../../../base/participants/functions';
import useContextMenu from '../../../base/ui/hooks/useContextMenu.web';
import { normalizeAccents } from '../../../base/util/strings.web';
import { getBreakoutRooms, getCurrentRoomId, isInBreakoutRoom } from '../../../breakout-rooms/functions';
import { isButtonEnabled, showOverflowDrawer } from '../../../toolbox/functions.web';
import { muteRemote } from '../../../video-menu/actions.web';
import { getSortedParticipantIds, shouldRenderInviteButton } from '../../functions';
import { useParticipantDrawer } from '../../hooks';
import MeetingParticipantContextMenu from './MeetingParticipantContextMenu';
import MeetingParticipantItems from './MeetingParticipantItems';
import { isMobileBrowser } from "../../../base/environment/utils";
import { getKnockingParticipants, getLobbyEnabled } from "../../../lobby/functions";
import { getPromotionRequests, getVisitorsCount } from "../../../visitors/functions";
import { isNeedShowElectronOnlyElements } from "../../../base/environment/utils_web";

const useStyles = makeStyles()(theme => {
    return {
        headingContainer: {
            alignItems: 'center',
            display: 'flex',
            justifyContent: 'space-between',
            marginBottom: '10px',

            '&.is-mobile': {
                padding: '10px 16px 10px 16px',
                marginBottom: 0,
            }
        },
        heading: {
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

        separateLine: {
            marginBottom: '16px',
        },

        marginTop: {
            marginTop: '16px',
        },

        search: {
            margin: `${theme.spacing(3)} 0`,

            '& input': {
                textAlign: 'center',
                paddingRight: '16px'
            }
        }
    };
});

interface IProps {
    currentRoom?: {
        jid: string;
        name: string;
    };
    overflowDrawer?: boolean;
    participantsCount?: number;
    searchString: string;
    setSearchString: (newValue: string) => void;
    showInviteButton?: boolean;
    sortedParticipantIds?: Array<string>;
}

/**
 * Renders the MeetingParticipantList component.
 * NOTE: This component is not using useSelector on purpose. The child components MeetingParticipantItem
 * and MeetingParticipantContextMenu are using connect. Having those mixed leads to problems.
 * When this one was using useSelector and the other two were not -the other two were re-rendered before this one was
 * re-rendered, so when participant is leaving, we first re-render the item and menu components,
 * throwing errors (closing the page) before removing those components for the participant that left.
 *
 * @returns {ReactNode} - The component.
 */
function MeetingParticipants({
    currentRoom,
    overflowDrawer,
    participantsCount,
    searchString,
    setSearchString,
    showInviteButton,
    sortedParticipantIds = []
}: IProps) {
    const dispatch = useDispatch();
    const { t } = useTranslation();

    const [ lowerMenu, , toggleMenu, menuEnter, menuLeave, raiseContext ] = useContextMenu<string>();
    const muteAudio = useCallback(id => () => {
        dispatch(muteRemote(id, MEDIA_TYPE.AUDIO));
        dispatch(rejectParticipantAudio(id));
    }, [ dispatch ]);
    const stopVideo = useCallback(id => () => {
        dispatch(muteRemote(id, MEDIA_TYPE.VIDEO));
        dispatch(rejectParticipantVideo(id));
    }, [ dispatch ]);
    const [ drawerParticipant, closeDrawer, openDrawerForParticipant ] = useParticipantDrawer();
    const isMobile = isMobileBrowser();

    // FIXME:
    // It seems that useTranslation is not very scalable. Unmount 500 components that have the useTranslation hook is
    // taking more than 10s. To workaround the issue we need to pass the texts as props. This is temporary and dirty
    // solution!!!
    // One potential proper fix would be to use react-window component in order to lower the number of components
    // mounted.
    const participantActionEllipsisLabel = t('participantsPane.actions.moreParticipantOptions');
    const youText = t('chat.you');
    const isBreakoutRoom = useSelector(isInBreakoutRoom);

    const lobbyEnabled = useSelector(getLobbyEnabled);
    const lobbyParticipants = useSelector(getKnockingParticipants);
    const isLobbyParticipantsVisible = lobbyEnabled && lobbyParticipants.length > 0;

    const visitorsCount = useSelector(getVisitorsCount);
    const visitorsPromotionRequests = useSelector(getPromotionRequests);
    const showVisitorsPromotions = visitorsPromotionRequests.length > 0;
    const isVisitorListVisible = visitorsCount > 0 && showVisitorsPromotions;

    const { classes, cx } = useStyles();

    return (
        <>
            {isMobile ? (
                (isVisitorListVisible || isLobbyParticipantsVisible) && (
                    <div className = {cx(classes.headingContainer, isMobile && 'is-mobile')}>
                        <div className = {cx(classes.heading, isMobile && 'is-mobile')}>
                            {t('participantsPane.headings.connected')}
                        </div>
                    </div>
                )
            ) : (
                (isNeedShowElectronOnlyElements() || isVisitorListVisible || isLobbyParticipantsVisible) ? (
                    <div className = {cx('dotted-separate-line', classes.separateLine)} />
                ) : (
                    <div className = {classes.marginTop} />
                )
            )}
            <div>
                <MeetingParticipantItems
                    isInBreakoutRoom = {isBreakoutRoom}
                    lowerMenu = {lowerMenu}
                    muteAudio = {muteAudio}
                    openDrawerForParticipant = {openDrawerForParticipant}
                    overflowDrawer = {overflowDrawer}
                    participantActionEllipsisLabel = {participantActionEllipsisLabel}
                    participantIds = {sortedParticipantIds}
                    raiseContextId = {raiseContext.entity}
                    searchString = {normalizeAccents(searchString)}
                    stopVideo = {stopVideo}
                    toggleMenu = {toggleMenu}
                    youText = {youText} />
            </div>
            <MeetingParticipantContextMenu
                closeDrawer = {closeDrawer}
                drawerParticipant = {drawerParticipant}
                muteAudio = {muteAudio}
                offsetTarget = {raiseContext?.offsetTarget}
                onEnter = {menuEnter}
                onLeave = {menuLeave}
                onSelect = {lowerMenu}
                overflowDrawer = {overflowDrawer}
                participantID = {raiseContext?.entity} />
        </>
    );
}

/**
 * Maps (parts of) the redux state to the associated props for this component.
 *
 * @param {Object} state - The Redux state.
 * @param {Object} ownProps - The own props of the component.
 * @private
 * @returns {IProps}
 */
function _mapStateToProps(state: IReduxState) {
    let sortedParticipantIds: any = getSortedParticipantIds(state);

    // Filter out the virtual screenshare participants since we do not want them to be displayed as separate
    // participants in the participants pane.
    sortedParticipantIds = sortedParticipantIds.filter((id: any) => {
        const participant = getParticipantById(state, id);

        return !isScreenShareParticipant(participant);
    });

    const participantsCount = sortedParticipantIds.length;
    const showInviteButton = shouldRenderInviteButton(state) && isButtonEnabled('invite', state);
    const overflowDrawer = showOverflowDrawer(state);
    const currentRoomId = getCurrentRoomId(state);
    const currentRoom = getBreakoutRooms(state)[currentRoomId];

    return {
        currentRoom,
        overflowDrawer,
        participantsCount,
        showInviteButton,
        sortedParticipantIds
    };
}

export default connect(_mapStateToProps)(MeetingParticipants);
