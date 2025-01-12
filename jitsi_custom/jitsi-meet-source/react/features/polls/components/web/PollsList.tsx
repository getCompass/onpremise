import React, { useCallback, useEffect, useRef } from 'react';
import { useTranslation } from 'react-i18next';
import { useSelector } from 'react-redux';
import { makeStyles } from 'tss-react/mui';

import { IReduxState } from '../../../app/types';
import { browser } from '../../../base/lib-jitsi-meet';

import PollItem from './PollItem';
import { isMobileBrowser } from "../../../base/environment/utils";

const useStyles = makeStyles()(theme => {
    return {
        container: {
            height: '100%',
            width: '100%',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            flexDirection: 'column'
        },
        emptyIcon: {
            width: '100px',
            padding: '16px',

            '& svg': {
                width: '100%',
                height: 'auto'
            }
        },
        emptyMessage: {
            fontFamily: 'Lato Regular',
            fontWeight: 'normal' as const,
            fontSize: '13px',
            lineHeight: '18px',
            color: 'rgba(255, 255, 255, 0.2)',
            padding: '0 36px',
            textAlign: 'center',

            '&.is-mobile': {
                fontSize: '16px',
                lineHeight: '22px',
            }
        }
    };
});

interface IPollListProps {
    setCreateMode: (mode: boolean) => void;
}

const PollsList = ({ setCreateMode }: IPollListProps) => {
    const { t } = useTranslation();
    const { classes, cx } = useStyles();
    const { polls } = useSelector((state: IReduxState) => state['features/polls']);

    const pollListEndRef = useRef<HTMLDivElement>(null);

    const scrollToBottom = useCallback(() => {
        if (pollListEndRef.current) {
            // Safari does not support options
            const param = browser.isSafari()
                ? false : {
                    behavior: 'smooth' as const,
                    block: 'end' as const,
                    inline: 'nearest' as const
                };

            pollListEndRef.current.scrollIntoView(param);
        }
    }, [ pollListEndRef.current ]);

    useEffect(() => {
        scrollToBottom();
    }, [ polls ]);

    const listPolls = Object.keys(polls);

    return (
        <>
            {listPolls.length === 0
                ? <div className = {classes.container}>
                    <span
                        className = {cx(classes.emptyMessage, isMobileBrowser() && 'is-mobile')}>{t('polls.results.empty')}</span>
                </div>
                : listPolls.map((id, index) => (
                    <PollItem
                        key = { id }
                        pollId = { id }
                        ref = { listPolls.length - 1 === index ? pollListEndRef : null }
                        setCreateMode = { setCreateMode } />
                ))}
        </>
    );
};

export default PollsList;
