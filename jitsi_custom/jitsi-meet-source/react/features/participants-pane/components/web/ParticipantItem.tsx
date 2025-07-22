import React, { ReactNode, useCallback, useState } from 'react';
import { WithTranslation } from 'react-i18next';
import { makeStyles } from 'tss-react/mui';

import Avatar from '../../../base/avatar/components/Avatar';
import { translate } from '../../../base/i18n/functions';
import ListItem from '../../../base/ui/components/web/ListItem';
import {
    ACTION_TRIGGER,
    type ActionTrigger,
    AudioThinStateIcons,
    MEDIA_STATE,
    MediaState,
    VideoThinStateIcons
} from '../../constants';

import { RaisedHandIndicator } from './RaisedHandIndicator';
import { isMobileBrowser } from "../../../base/environment/utils";
import VideoMenuTriggerButton from "../../../filmstrip/components/web/VideoMenuTriggerButton";
import { THUMBNAIL_TYPE } from "../../../filmstrip/constants";

interface IProps extends WithTranslation {

    /**
     * Type of trigger for the participant actions.
     */
    actionsTrigger?: ActionTrigger;

    /**
     * Media state for audio.
     */
    audioMediaState?: MediaState;

    /**
     * React children.
     */
    children?: ReactNode;

    /**
     * Whether or not to disable the moderator indicator.
     */
    disableModeratorIndicator?: boolean;

    /**
     * The name of the participant. Used for showing lobby names.
     */
    displayName?: string;

    /**
     * Is this item highlighted/raised.
     */
    isHighlighted?: boolean;

    /**
     * Whether or not the participant is a moderator.
     */
    isModerator?: boolean;

    /**
     * True if the participant is local.
     */
    local?: boolean;

    /**
     * Callback for when the mouse leaves this component.
     */
    onLeave?: (e?: React.MouseEvent) => void;

    /**
     * Opens a drawer with participant actions.
     */
    openDrawerForParticipant?: Function;

    /**
     * If an overflow drawer can be opened.
     */
    overflowDrawer?: boolean;

    /**
     * The ID of the participant.
     */
    participantID: string;

    /**
     * True if the participant have raised hand.
     */
    raisedHand?: boolean;

    /**
     * True if the lobby participant request item.
     */
    isLobbyParticipantRequest: boolean;

    /**
     * True if the visitor request item.
     */
    isVisitor: boolean;

    /**
     * Media state for video.
     */
    videoMediaState?: MediaState;

    /**
     * The translated "you" text.
     */
    youText?: string;
}

const useStyles = makeStyles()(theme => {
    return {
        nameContainer: {
            display: 'flex',
            flex: 1,
            overflow: 'hidden'
        },

        name: {
            overflow: 'hidden',
            textOverflow: 'ellipsis',
            whiteSpace: 'nowrap'
        },

        moderatorLabel: {
            fontFamily: 'Lato Regular',
            fontWeight: 'normal' as const,
            fontSize: '15px',
            lineHeight: '22px',
            color: 'rgba(255, 255, 255, 0.3)',
            marginTop: '-2px',

            '&.is-mobile': {
                marginTop: 0,
                fontSize: '14px',
                lineHeight: '20px'
            }
        },

        avatar: {},

        avatarContainer: {
            marginRight: '8px',
            '&.is-mobile': {
                paddingLeft: '16px',
            }
        }
    };
});

/**
 * A component representing a participant entry in ParticipantPane and Lobby.
 *
 * @param {IProps} props - The props of the component.
 * @returns {ReactNode}
 */
function ParticipantItem({
    actionsTrigger = ACTION_TRIGGER.HOVER,
    audioMediaState = MEDIA_STATE.NONE,
    children,
    disableModeratorIndicator,
    displayName,
    isHighlighted,
    isModerator,
    local,
    onLeave,
    openDrawerForParticipant,
    overflowDrawer,
    participantID,
    raisedHand,
    isLobbyParticipantRequest,
    isVisitor,
    t,
    videoMediaState = MEDIA_STATE.NONE,
    youText
}: IProps) {
    const onClick = useCallback(
        () => openDrawerForParticipant?.({
            participantID,
            displayName
        }), []);

    const { classes, cx } = useStyles();
    const isMobile = isMobileBrowser();

    const [ popoverVisible, setPopoverVisible ] = useState(false);
    const showPopover = useCallback(() => setPopoverVisible(true), []);
    const hidePopover = useCallback(() => setPopoverVisible(false), []);

    const icon = (
        <Avatar
            className = {classes.avatar}
            displayName = {displayName}
            participantId = {participantID}
            size = {isMobile ? 44 : 40} />
    );

    const text = (
        <>
            <div className = {classes.nameContainer}>
                <div className = {classes.name}>
                    {displayName}
                </div>
                {local ? <span>&nbsp;({youText})</span> : null}
            </div>
            {isModerator && <div className = {cx(classes.moderatorLabel, isMobile && 'is-mobile')}>
                {t('videothumbnail.moderator')}
            </div>}
            {(!isModerator && !isLobbyParticipantRequest && !isVisitor) && <div className = {classes.moderatorLabel}>
                {t('videothumbnail.member')}
            </div>}
            {isVisitor && <div className = {classes.moderatorLabel}>
                {t('participantsPane.visitor')}
            </div>}
            {isLobbyParticipantRequest && <div className = {classes.moderatorLabel}>
                {t('participantsPane.lobbyRequest')}
            </div>}
        </>
    );

    const indicators = (
        <>
            {raisedHand && <RaisedHandIndicator />}
            {VideoThinStateIcons[videoMediaState]}
            {AudioThinStateIcons[audioMediaState]}
        </>
    );

    return (
        <>
            <ListItem
                actions = {children}
                hideActions = {local}
                icon = {icon}
                iconClassName = {cx(classes.avatarContainer, isMobile && 'is-mobile')}
                id = {`participant-item-${participantID}`}
                indicators = {indicators}
                isHighlighted = {isHighlighted}
                onClick = {local && isMobile ? (popoverVisible ? hidePopover : showPopover) : !local && overflowDrawer ? onClick : undefined}
                onMouseLeave = {onLeave}
                textChildren = {text}
                trigger = {actionsTrigger} />
            {local && isMobile && (
                <VideoMenuTriggerButton
                    hidePopover = {hidePopover}
                    local = {local}
                    participantId = {participantID}
                    popoverVisible = {popoverVisible}
                    showPopover = {showPopover}
                    thumbnailType = {THUMBNAIL_TYPE.TILE}
                    visible = {false} />
            )}
        </>
    );
}

export default translate(ParticipantItem);
