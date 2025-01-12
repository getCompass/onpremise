import ReactionButton from "../../../reactions/components/web/ReactionButton";
import i18n from "i18next";
import React, { useCallback } from "react";
import { getLocalParticipant, hasRaisedHand } from "../../../base/participants/functions";
import { useDispatch, useSelector } from "react-redux";
import { sendAnalytics } from "../../../analytics/functions";
import { createToolbarEvent } from "../../../analytics/AnalyticsEvents";
import { toggleReactionsMenuVisibility } from "../../../reactions/actions.web";
import { raiseHand } from "../../../base/participants/actions";

export default function RaiseHandButtonMobile() {

    const localParticipant = useSelector(getLocalParticipant);
    const isRaisedHand = hasRaisedHand(localParticipant);
    const dispatch = useDispatch();

    const _onToolbarToggleRaiseHand = useCallback(() => {
        sendAnalytics(createToolbarEvent(
            'raise.hand',
            { enable: !isRaisedHand }));
        dispatch(raiseHand(!isRaisedHand));
        dispatch(toggleReactionsMenuVisibility());
    }, [ isRaisedHand ]);

    return (
        <>
            <div style = {{
                borderTop: "0.5px solid rgba(255, 255, 255, 0.05)",
            }} />
            <div className = 'raise-hand-row-button-mobile'>
                <ReactionButton
                    accessibilityLabel = {i18n.t('toolbar.accessibilityLabel.raiseHand')}
                    icon = '✋'
                    key = 'raisehand'
                    label = {
                        `${i18n.t(`toolbar.${isRaisedHand ? 'lowerYourHand' : 'raiseYourHand'}`)}`
                    }
                    onClick = {_onToolbarToggleRaiseHand}
                    toggled = {true}
                    className = 'emoji-raise-hand-button-mobile'
                    classNameIcon = 'emoji-raise-hand-button-mobile' />
            </div>
        </>
    );
}