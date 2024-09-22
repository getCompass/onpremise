import React, {useCallback, useEffect, useRef, useState} from 'react';
import {useSelector} from 'react-redux';

import {getConferenceTimestamp, getDemoConferenceTimestamp} from '../../base/conference/functions';
import {getLocalizedDurationFormatter} from '../../base/i18n/dateUtil';

import {ConferenceTimerDisplay} from './index';
import {isDemoNode} from "../../app/functions.web";

/**
 * The type of the React {@code Component} props of {@link ConferenceTimer}.
 */
interface IProps {

    /**
     * Style to be applied to the rendered text.
     */
    textStyle?: Object;
}

export interface IDisplayProps {

    /**
     * Style to be applied to text (native only).
     */
    textStyle?: Object;

    /**
     * String to display as time.
     */
    timerValue: string;
}

const ConferenceTimer = ({textStyle}: IProps) => {
    const startTimestamp = isDemoNode() ? useSelector(getDemoConferenceTimestamp) : useSelector(getConferenceTimestamp);
    const [timerValue, setTimerValue] = useState(getLocalizedDurationFormatter(0));
    const interval = useRef<number>();

    /**
     * Sets the current state values that will be used to render the timer.
     *
     * @param {number} refValueUTC - The initial UTC timestamp value.
     * @param {number} currentValueUTC - The current UTC timestamp value.
     *
     * @returns {void}
     */
    const setStateFromUTC = useCallback((refValueUTC, currentValueUTC) => {
        if (!refValueUTC || !currentValueUTC) {
            return;
        }

        if (currentValueUTC < refValueUTC) {
            return;
        }

        const timerMsValue = currentValueUTC - refValueUTC;

        const localizedTime = getLocalizedDurationFormatter(timerMsValue);

        setTimerValue(localizedTime);
    }, []);

    /**
     * Sets the current state values that will be used to render the timer.
     *
     * @param {number} refValueUTC - The initial UTC timestamp value.
     * @param {number} currentValueUTC - The current UTC timestamp value.
     *
     * @returns {void}
     */
    const setCountdownStateFromUTC = useCallback((refValueUTC, currentValueUTC) => {
        if (!refValueUTC || !currentValueUTC) {
            return;
        }

        // calculate the remaining time
        const timerMsValue = refValueUTC - currentValueUTC;

        // If the remaining time is negative, set to zero
        const remainingTime = timerMsValue > 0 ? timerMsValue : 0;

        const localizedTime = getLocalizedDurationFormatter(remainingTime);

        setTimerValue(localizedTime);
    }, []);

    /**
     * Start conference timer.
     *
     * @returns {void}
     */
    const startTimer = useCallback(() => {

        if (!interval.current && startTimestamp) {
            isDemoNode() ? setCountdownStateFromUTC(startTimestamp, new Date().getTime()) : setStateFromUTC(startTimestamp, new Date().getTime());

            interval.current = window.setInterval(() => {
                isDemoNode() ? setCountdownStateFromUTC(startTimestamp, new Date().getTime()) : setStateFromUTC(startTimestamp, new Date().getTime());
            }, 1000);
        }
    }, [startTimestamp, interval]);

    /**
     * Stop conference timer.
     *
     * @returns {void}
     */
    const stopTimer = useCallback(() => {
        if (interval.current) {
            clearInterval(interval.current);
            interval.current = undefined;
        }

        setTimerValue(getLocalizedDurationFormatter(0));
    }, [interval]);

    useEffect(() => {
        startTimer();

        return () => stopTimer();
    }, [startTimestamp]);


    if (!startTimestamp) {
        return null;
    }

    return (<ConferenceTimerDisplay
        textStyle={textStyle}
        timerValue={timerValue}/>);
};

export default ConferenceTimer;
