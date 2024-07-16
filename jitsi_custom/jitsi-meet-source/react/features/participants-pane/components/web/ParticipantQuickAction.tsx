import React, {useCallback} from 'react';
import {useTranslation} from 'react-i18next';
import {useDispatch} from 'react-redux';
import {makeStyles} from 'tss-react/mui';

import {approveParticipantAudio, approveParticipantVideo} from '../../../av-moderation/actions';
import Button from '../../../base/ui/components/web/Button';
import {QUICK_ACTION_BUTTON} from '../../constants';

interface IProps {

    /**
     * The translated ask unmute aria label.
     */
    ariaLabel?: boolean;

    /**
     * The translated "ask unmute" text.
     */
    askUnmuteText?: string;

    /**
     * The type of button to be displayed.
     */
    buttonType: string;

    /**
     * Callback used to open a confirmation dialog for audio muting.
     */
    muteAudio: Function;

    /**
     * Label for mute participant button.
     */
    muteParticipantButtonText?: string;

    /**
     * The ID of the participant.
     */
    participantID: string;

    /**
     * The name of the participant.
     */
    participantName: string;

    /**
     * Callback used to stop a participant's video.
     */
    stopVideo: Function;

}

const useStyles = makeStyles()(theme => {
    return {
        button: {
            backgroundColor: 'rgba(0, 107, 224, 1)',
            padding: '6px 16px',
            fontSize: '15px',
            lineHeight: '23px',
            color: 'rgba(255, 255, 255, 1)',
            marginRight: '8px'
        }
    };
});

const ParticipantQuickAction = ({
                                    buttonType,
                                    muteAudio,
                                    participantID,
                                    participantName,
                                    stopVideo
                                }: IProps) => {
    const {classes: styles} = useStyles();
    const dispatch = useDispatch();
    const {t} = useTranslation();

    const askToUnmute = useCallback(() => {
        dispatch(approveParticipantAudio(participantID));
    }, [dispatch, participantID]);

    const allowVideo = useCallback(() => {
        dispatch(approveParticipantVideo(participantID));
    }, [dispatch, participantID]);

    switch (buttonType) {
        case QUICK_ACTION_BUTTON.MUTE: {
            return (
                <Button
                    accessibilityLabel={`${t('participantsPane.actions.mute')} ${participantName}`}
                    className={styles.button}
                    label={t('participantsPane.actions.mute')}
                    onClick={muteAudio(participantID)}
                    size='medium'
                    testId={`mute-audio-${participantID}`}/>
            );
        }
        case QUICK_ACTION_BUTTON.ASK_TO_UNMUTE: {
            return (
                <Button
                    accessibilityLabel={`${t('participantsPane.actions.askUnmuteParticipantPane')} ${participantName}`}
                    className={styles.button}
                    label={t('participantsPane.actions.askUnmuteParticipantPane')}
                    onClick={askToUnmute}
                    size='medium'
                    testId={`unmute-audio-${participantID}`}/>
            );
        }
        case QUICK_ACTION_BUTTON.ALLOW_VIDEO: {
            return (
                <Button
                    accessibilityLabel={`${t('participantsPane.actions.askUnmuteParticipantPane')} ${participantName}`}
                    className={styles.button}
                    label={t('participantsPane.actions.allowVideo')}
                    onClick={allowVideo}
                    size='medium'
                    testId={`unmute-video-${participantID}`}/>
            );
        }
        case QUICK_ACTION_BUTTON.STOP_VIDEO: {
            return (
                <Button
                    accessibilityLabel={`${t('participantsPane.actions.mute')} ${participantName}`}
                    className={styles.button}
                    label={t('participantsPane.actions.stopVideo')}
                    onClick={stopVideo(participantID)}
                    size='medium'
                    testId={`mute-video-${participantID}`}/>
            );
        }
        default: {
            return null;
        }
    }
};

export default ParticipantQuickAction;
