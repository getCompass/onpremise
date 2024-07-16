import React, {useCallback} from 'react';
import {useTranslation} from 'react-i18next';
import {useDispatch, useSelector} from 'react-redux';
import {makeStyles} from 'tss-react/mui';

import {IReduxState} from '../../../app/types';
import {withPixelLineHeight} from '../../../base/styles/functions.web';
import {admitMultiple} from '../../../visitors/actions';
import {getPromotionRequests} from '../../../visitors/functions';

import {VisitorsItem} from './VisitorsItem';
import {isMobileBrowser} from "../../../base/environment/utils";

const useStyles = makeStyles()(theme => {
    return {
        container: {
            marginBottom: '14px'
        },
        headingW: {
            color: theme.palette.warning02
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
 * Component used to display a list of visitors waiting for approval to join the main meeting.
 *
 * @returns {ReactNode}
 */
export default function VisitorsList() {
    const requests = useSelector(getPromotionRequests);
    const visitorsCount = useSelector((state: IReduxState) => state['features/visitors'].count || 0);

    const {t} = useTranslation();
    const isMobile = isMobileBrowser();
    const {classes, cx} = useStyles();
    const dispatch = useDispatch();

    const admitAll = useCallback(() => {
        dispatch(admitMultiple(requests));
    }, [dispatch, requests]);

    if (visitorsCount <= 0) {
        return null;
    }

    return (
        <>
            {!isMobileBrowser() && <div className={classes.separateLineContainer}>
                <div className={cx('dotted-separate-line')}/>
            </div>}
            <div className={cx(classes.headingContainer, isMobile && 'is-mobile')}>
                <div className={cx(classes.heading, classes.headingW, isMobile && 'is-mobile')}>
                    {isMobile ? t('participantsPane.headings.visitorsMobile') : t('participantsPane.headings.visitors', {count: visitorsCount})}
                    {requests.length > 0
                        && t('participantsPane.headings.visitorRequests', {count: requests.length})}
                </div>
                {
                    requests.length > 1
                    && <div
                        className={classes.link}
                        onClick={admitAll}>{t('participantsPane.actions.admitAll')}</div>
                }
            </div>
            <div
                className={classes.container}
                id='visitor-list'>
                {
                    requests.map(r => (
                        <VisitorsItem
                            key={r.from}
                            request={r}/>)
                    )
                }
            </div>
        </>
    );
}
