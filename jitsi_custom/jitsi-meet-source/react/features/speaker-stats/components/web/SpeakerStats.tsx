import React, {useCallback, useEffect} from 'react';
import {useTranslation} from 'react-i18next';
import {useDispatch, useSelector} from 'react-redux';
import {makeStyles} from 'tss-react/mui';

import {IReduxState} from '../../../app/types';
import {
    IconEmotionsAngry,
    IconEmotionsDisgusted,
    IconEmotionsFearful,
    IconEmotionsHappy,
    IconEmotionsNeutral,
    IconEmotionsSad,
    IconEmotionsSurprised
} from '../../../base/icons/svg';
import Dialog from '../../../base/ui/components/web/Dialog';
import {escapeRegexp} from '../../../base/util/helpers';
import {initSearch, resetSearchCriteria, toggleFaceExpressions} from '../../actions.any';
import {DISPLAY_SWITCH_BREAKPOINT, MOBILE_BREAKPOINT} from '../../constants';
import SpeakerStatsList from './SpeakerStatsList';
import {isMobileBrowser} from "../../../base/environment/utils";

const useStyles = makeStyles()(theme => {
    return {
        dialogClassNameContainer: {
            paddingBottom: '10px',

            '&.is-mobile': {
                paddingBottom: '8px',
            },
        },
        dialogClassNameHeader: {
            padding: '20px 16px 12px 20px',

            '& #dialog-title': {
                fontFamily: 'Lato Black',
                fontWeight: 'normal' as const,
                fontSize: '18px',
                lineHeight: '24px',
                letterSpacing: '-0.2px',

                '&.is-mobile': {
                    fontFamily: 'Lato Bold',
                    fontSize: '20px',
                    lineHeight: '28px',
                    letterSpacing: '-0.3px',
                },
            },

            '&.is-mobile': {
                padding: '16px 16px 14px 16px',
                maxHeight: '90vh',
                borderRadius: '15px 15px 0 0',
            },
        },
        dialogClassNameContent: {
            '&.is-mobile': {
                padding: 0
            }
        },
        speakerStats: {
            '& .row': {
                display: 'flex',
                alignItems: 'center',
                '& .name-time': {
                    width: 'calc(100% - 48px)',
                    display: 'flex',
                    justifyContent: 'space-between',
                    alignItems: 'center',
                    '&.expressions-on': {
                        width: 'calc(47% - 48px)',
                        marginRight: theme.spacing(4)
                    }
                },
                '& .timeline-container': {
                    height: '100%',
                    width: `calc(53% - ${theme.spacing(4)})`,
                    display: 'flex',
                    alignItems: 'center',
                    borderLeftWidth: 1,
                    borderLeftColor: theme.palette.ui02,
                    borderLeftStyle: 'solid',
                    '& .timeline': {
                        height: theme.spacing(2),
                        display: 'flex',
                        width: '100%',
                        '&>div': {
                            marginRight: theme.spacing(1),
                            borderRadius: 5
                        },
                        '&>div:first-child': {
                            borderRadius: '0 5px 5px 0'
                        },
                        '&>div:last-child': {
                            marginRight: 0,
                            borderRadius: '5px 0 0 5px'
                        }
                    }
                },
                '& .axis-container': {
                    height: '100%',
                    width: `calc(53% - ${theme.spacing(6)})`,
                    display: 'flex',
                    alignItems: 'center',
                    marginLeft: theme.spacing(3),
                    '& div': {
                        borderRadius: 5
                    },
                    '& .axis': {
                        height: theme.spacing(1),
                        display: 'flex',
                        width: '100%',
                        backgroundColor: theme.palette.ui03,
                        position: 'relative',
                        '& .left-bound': {
                            position: 'absolute',
                            bottom: 10,
                            left: 0
                        },
                        '& .right-bound': {
                            position: 'absolute',
                            bottom: 10,
                            right: 0
                        },
                        '& .handler': {
                            position: 'absolute',
                            backgroundColor: theme.palette.ui09,
                            height: 12,
                            marginTop: -4,
                            display: 'flex',
                            justifyContent: 'space-between',
                            '& .resize': {
                                height: '100%',
                                width: 5,
                                cursor: 'col-resize'
                            }
                        }
                    }
                }
            },
        }
    };
});

const EMOTIONS_LEGEND = [
    {
        translationKey: 'speakerStats.neutral',
        icon: IconEmotionsNeutral
    },
    {
        translationKey: 'speakerStats.happy',
        icon: IconEmotionsHappy
    },
    {
        translationKey: 'speakerStats.surprised',
        icon: IconEmotionsSurprised
    },
    {
        translationKey: 'speakerStats.sad',
        icon: IconEmotionsSad
    },
    {
        translationKey: 'speakerStats.fearful',
        icon: IconEmotionsFearful
    },
    {
        translationKey: 'speakerStats.angry',
        icon: IconEmotionsAngry
    },
    {
        translationKey: 'speakerStats.disgusted',
        icon: IconEmotionsDisgusted
    }
];

const SpeakerStats = () => {
    const {faceLandmarks} = useSelector((state: IReduxState) => state['features/base/config']);
    const {showFaceExpressions} = useSelector((state: IReduxState) => state['features/speaker-stats']);
    const {clientWidth} = useSelector((state: IReduxState) => state['features/base/responsive-ui']);
    const displaySwitch = faceLandmarks?.enableDisplayFaceExpressions && clientWidth > DISPLAY_SWITCH_BREAKPOINT;
    const displayLabels = clientWidth > MOBILE_BREAKPOINT;
    const dispatch = useDispatch();
    const {classes, cx} = useStyles();
    const {t} = useTranslation();
    const isMobile = isMobileBrowser();

    const onToggleFaceExpressions = useCallback(() =>
            dispatch(toggleFaceExpressions())
        , [dispatch]);

    const onSearch = useCallback((criteria = '') => {
            dispatch(initSearch(escapeRegexp(criteria)));
        }
        , [dispatch]);

    useEffect(() => {
        showFaceExpressions && !displaySwitch && dispatch(toggleFaceExpressions());
    }, [clientWidth]);

    useEffect(() => () => {
        dispatch(resetSearchCriteria());
    }, []);

    return (
        <Dialog
            cancel={{hidden: true}}
            ok={{hidden: true}}
            size={showFaceExpressions ? 'large' : 'medium'}
            className={cx(classes.dialogClassNameContainer, isMobile && 'is-mobile')}
            classNameHeader={cx(classes.dialogClassNameHeader, isMobile && 'is-mobile')}
            classNameContent={cx(classes.dialogClassNameContent, isMobile && 'is-mobile')}
            titleKey='speakerStats.speakerStats'
            hideCloseButton={isMobile}
        >
            <div className={classes.speakerStats}>
                <SpeakerStatsList/>
            </div>
        </Dialog>

    );
};

export default SpeakerStats;
