import React from 'react';
import {makeStyles} from 'tss-react/mui';
import {MOBILE_BREAKPOINT} from '../../constants';
import abstractSpeakerStatsList from '../AbstractSpeakerStatsList';

import SpeakerStatsItem from './SpeakerStatsItem';

const useStyles = makeStyles()(theme => {
    return {
        list: {
            '& .item': {
                padding: '6px 0',
                [theme.breakpoints.down(MOBILE_BREAKPOINT)]: {
                    padding: '8px 16px'
                },
                '& .has-left': {
                    color: theme.palette.text03
                },
                '& .avatar': {
                    '& .avatar': {
                        marginRight: '8px'
                    },
                },
                '& .time': {
                    padding: '5px 8px',
                    borderRadius: '6px',
                    color: 'rgba(255, 255, 255, 0.8)',
                    fontFamily: 'Lato Regular',
                    fontWeight: 'normal' as const,
                    fontSize: '13px',
                    lineHeight: '16px',
                    backgroundColor: 'rgba(255, 255, 255, 0.05)'
                },
                '& .display-name': {
                    color: 'rgba(255, 255, 255, 0.75)',
                    fontFamily: 'Lato Bold',
                    fontWeight: 'normal' as const,
                    fontSize: '15px',
                    lineHeight: '20px',

                    [theme.breakpoints.down(MOBILE_BREAKPOINT)]: {
                        fontSize: '16px',
                        lineHeight: '20px',
                        color: 'rgba(255, 255, 255, 0.85)',
                    }
                },
                '& .display-role': {
                    marginTop: '-2px',
                    color: 'rgba(255, 255, 255, 0.3)',
                    fontFamily: 'Lato Regular',
                    fontWeight: 'normal' as const,
                    fontSize: '15px',
                    lineHeight: '22px',
                    [theme.breakpoints.down(MOBILE_BREAKPOINT)]: {
                        fontSize: '14px',
                        lineHeight: '20px',
                    }
                },
                '& .local': {
                    color: 'rgba(255, 255, 255, 1)',
                    backgroundColor: 'rgba(4, 164, 90, 1)'
                }
            },

            '& .dividerContainer': {
                paddingLeft: '50px',

                '& .divider': {
                    borderBottom: '0.5px solid rgba(255, 255, 255, 0.08)'
                }
            }
        }
    };
});

/**
 * Component that renders the list of speaker stats.
 *
 * @returns {React$Element<any>}
 */
const SpeakerStatsList = () => {
    const {classes} = useStyles();
    const items = abstractSpeakerStatsList(SpeakerStatsItem);

    return (
        <div className={classes.list}>
            {items}
        </div>
    );
};

export default SpeakerStatsList;
