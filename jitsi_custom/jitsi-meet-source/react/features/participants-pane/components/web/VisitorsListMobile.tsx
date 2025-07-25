import React, { useCallback } from 'react';
import { useTranslation } from 'react-i18next';
import { useDispatch, useSelector } from 'react-redux';
import { makeStyles } from 'tss-react/mui';

import { withPixelLineHeight } from '../../../base/styles/functions.web';
import { admitMultiple, goLive } from '../../../visitors/actions';
import {
    getPromotionRequests,
    getVisitorsCount,
    getVisitorsInQueueCount,
    isVisitorsLive
} from '../../../visitors/functions';

import { VisitorsItem } from './VisitorsItem';
import { isNeedShowElectronOnlyElements } from "../../../base/environment/utils_web";
import Tooltip from "../../../base/tooltip/components/Tooltip";

const useStyles = makeStyles()(theme => {
    return {
        container: {
            marginBottom: '16px'
        },
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
            padding: '10px 16px 10px 16px',
            alignItems: 'center',
            display: 'flex',
            justifyContent: 'space-between',
            marginBottom: 0,
        },
        heading: {
            fontFamily: 'Lato Regular',
            fontWeight: 'normal' as const,
            textTransform: 'uppercase',
            fontSize: '13px',
            lineHeight: '18px',
            letterSpacing: '-0.1px',
            color: 'rgba(255, 255, 255, 0.3)'
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
 * Component used to display a list of visitors waiting for approval to join the main meeting.
 *
 * @returns {ReactNode}
 */
export default function VisitorsListMobile() {
    const requests = useSelector(getPromotionRequests);
    const visitorsCount = useSelector(getVisitorsCount);
    const visitorsInQueueCount = useSelector(getVisitorsInQueueCount);
    const isLive = useSelector(isVisitorsLive);
    const showVisitorsInQueue = visitorsInQueueCount > 0 && isLive === false;

    const { t } = useTranslation();
    const { classes, cx } = useStyles();
    const dispatch = useDispatch();

    const admitAll = useCallback(() => {
        dispatch(admitMultiple(requests));
    }, [ dispatch, requests ]);

    const goLiveCb = useCallback(() => {
        dispatch(goLive());
    }, [ dispatch ]);

    if (visitorsCount <= 0 && !showVisitorsInQueue) {
        return null;
    }

    return (
        <>
            {requests.length > 0 && (
                <>
                    {isNeedShowElectronOnlyElements() && (
                        <div className = {classes.separateLineContainer}>
                            <div className = {cx('dotted-separate-line')} />
                        </div>
                    )}
                    <div className = {cx(classes.headingContainer)}>
                        <div className = {cx(classes.heading)}>
                            {requests.length > 0
                                && t('participantsPane.headings.visitorRequests', { count: requests.length })}
                            {showVisitorsInQueue
                                && t('participantsPane.headings.visitorInQueue', { count: visitorsInQueueCount })}
                        </div>
                        {
                            requests.length > 1 && !showVisitorsInQueue // Go live button is with higher priority
                            && <div
                                className = {classes.link}
                                onClick = {admitAll}>{t('participantsPane.actions.admitAll')}</div>
                        }
                        {
                            showVisitorsInQueue
                            && <div
                                className = {classes.link}
                                onClick = {goLiveCb}>{t('participantsPane.actions.goLive')}</div>
                        }
                    </div>
                    <div
                        className = {classes.container}
                        id = 'visitor-list'>
                        {
                            requests.map(r => (
                                <VisitorsItem
                                    key = {r.from}
                                    request = {r} />)
                            )
                        }
                    </div>
                </>
            )}
        </>
    );
}
