import React from 'react';
import {makeStyles} from 'tss-react/mui';
import AbstractPollResults, {AbstractProps} from '../AbstractPollResults';
import {isMobileBrowser} from "../../../base/environment/utils";

const useStyles = makeStyles()(theme => {
    return {
        container: {
            margin: '24px 16px',
            padding: '16px',
            backgroundColor: 'rgba(255, 255, 255, 0.03)',
            borderRadius: '8px',
            wordBreak: 'break-word'
        },
        header: {
            marginBottom: '16px',

            '&.is-mobile': {
                marginBottom: '24px',
            }
        },
        question: {
            fontFamily: 'Lato Bold',
            fontWeight: 'normal' as const,
            fontSize: '15px',
            lineHeight: '22px',
            color: 'rgba(255, 255, 255, 0.75)',
            marginBottom: '4px',

            '&.is-mobile': {
                letterSpacing: '-0.25px',
                fontSize: '18px',
                lineHeight: '26px',
                color: 'rgba(255, 255, 255, 0.7)',
            }
        },
        creator: {
            fontFamily: 'Lato Regular',
            fontWeight: 'normal' as const,
            fontSize: '13px',
            lineHeight: '22px',
            color: 'rgba(255, 255, 255, 0.5)',

            '&.is-mobile': {
                letterSpacing: '-0.16px',
                fontSize: '15px',
                lineHeight: '20px',
                color: 'rgba(255, 255, 255, 0.3)',
            }
        },
        resultList: {
            listStyleType: 'none',
            margin: 0,
            padding: 0,

            '& li': {
                marginBottom: '16px',

                '&.is-mobile': {
                    marginBottom: '32px',
                }
            }
        },
        answerNameVoteContainer: {
            display: 'flex',
            fontFamily: 'Lato SemiBold',
            fontWeight: 'normal' as const,
            fontSize: '15px',
            lineHeight: '18px',
            color: 'rgba(255, 255, 255, 0.75)',
            marginBottom: '15px',

            '&:last-child': {
                marginBottom: 0,
            },

            '&.is-mobile': {
                display: 'flex',
            }
        },
        answerName: {
            display: 'flex',
            flexShrink: 1,
            overflowWrap: 'anywhere',

            '&.is-mobile': {
                fontFamily: 'Lato Bold',
                fontWeight: 'normal' as const,
                fontSize: '16px',
                lineHeight: '22px',
                color: 'rgba(255, 255, 255, 0.7)',
            }
        },
        voteCount: {
            flex: 1,
            textAlign: 'right',
            fontFamily: 'Lato Regular',
            fontWeight: 'normal' as const,

            '&.is-mobile': {
                fontSize: '16px',
                lineHeight: '22px',
                color: 'rgba(255, 255, 255, 0.7)',
            }
        },
        answerResultContainer: {
            display: 'flex',
            justifyContent: 'space-between',
            alignItems: 'center',
        },
        barContainer: {
            backgroundColor: 'rgba(255, 255, 255, 0.05)',
            borderRadius: '4px',
            height: '4px',
            maxWidth: '250px',
            width: '250px',
            flexGrow: 1,

            '&.is-mobile': {
                maxWidth: '100%',
            }
        },
        bar: {
            height: '4px',
            borderRadius: '4px',
            backgroundColor: 'rgba(0, 107, 224, 1)'
        },
        votersContainer: {
            margin: 0,
            marginTop: '12px',
            listStyleType: 'none',
            display: 'flex',
            flexDirection: 'column',
            gap: '4px',
            padding: 0,

            '& li': {
                width: 'max-content',
                backgroundColor: ' rgba(255, 255, 255, 0.1)',
                borderRadius: '4px',
                fontFamily: 'Lato SemiBold',
                fontWeight: 'normal' as const,
                fontSize: '12px',
                lineHeight: '15px',
                padding: '5px 8px 4px 8px',
                color: 'rgba(255, 255, 255, 1)',
                margin: 0,
                marginBottom: '4px',

                '&:last-of-type': {
                    marginBottom: 0
                }
            }
        },
        buttonsContainer: {
            display: 'flex',
            justifyContent: 'space-between',

            '& button': {
                border: 0,
                backgroundColor: 'transparent',
                fontFamily: 'Inter SemiBold',
                fontWeight: 'normal' as const,
                fontSize: '14px',
                lineHeight: '21px',
                color: 'rgba(255, 255, 255, 1)',

                '&:hover': {
                    color: 'rgba(255, 255, 255, 0.7)',
                },
            }
        },

        buttonHideDetailedResults: {
            '&.is-mobile': {
                color: 'rgba(255, 255, 255, 1)',
                padding: 0,
                fontSize: '17px',
                lineHeight: '26px',
            }
        },
        changeVote: {
            '&.is-mobile': {
                color: 'rgba(255, 255, 255, 1)',
                padding: 0,
                fontSize: '17px',
                lineHeight: '26px',
            }
        }
    };
});

/**
 * Component that renders the poll results.
 *
 * @param {Props} props - The passed props.
 * @returns {React.Node}
 */
const PollResults = ({
                         answers,
                         changeVote,
                         creatorName,
                         haveVoted,
                         showDetails,
                         question,
                         t,
                         toggleIsDetailed
                     }: AbstractProps) => {
    const {classes, cx} = useStyles();
    const isMobile = isMobileBrowser();

    return (
        <div className={classes.container}>
            <div className={cx(classes.header, isMobile && 'is-mobile')}>
                <div className={cx(classes.question, isMobile && 'is-mobile')}>
                    {question}
                </div>
                <div className={cx(classes.creator, isMobile && 'is-mobile')}>
                    {creatorName}
                </div>
            </div>
            <ul className={classes.resultList}>
                {answers.map(({name, percentage, voters, voterCount}, index) =>
                    (<li key={index} className={isMobile ? 'is-mobile' : ''}>
                        <div className={cx(classes.answerNameVoteContainer, isMobile && 'is-mobile')}>
                            <div className={cx(classes.answerName, isMobile && 'is-mobile')}>
                                {name}
                            </div>
                            <div className={cx(classes.voteCount, isMobile && 'is-mobile')}>
                                {voterCount} ({percentage}%)
                            </div>
                        </div>
                        <div className={classes.answerResultContainer}>
                            <span className={cx(classes.barContainer, isMobile && 'is-mobile')}>
                                <div
                                    className={classes.bar}
                                    style={{width: `${percentage}%`}}/>
                            </span>
                        </div>
                        {showDetails && voters && voterCount > 0
                            && <ul className={classes.votersContainer}>
                                {voters.map(voter =>
                                    <li key={voter?.id}>{voter?.name}</li>
                                )}
                            </ul>}
                    </li>)
                )}
            </ul>
            <div className={classes.buttonsContainer}>
                <button
                    className={cx(classes.buttonHideDetailedResults, isMobile && 'is-mobile')}
                    onClick={toggleIsDetailed}>
                    {showDetails ? t('polls.results.hideDetailedResults') : t('polls.results.showDetailedResults')}
                </button>
                <button
                    className={cx(classes.changeVote, isMobile && 'is-mobile')}
                    onClick={changeVote}>
                    {haveVoted ? t('polls.results.changeVote') : t('polls.results.vote')}
                </button>
            </div>
        </div>
    );
};

/*
 * We apply AbstractPollResults to fill in the AbstractProps common
 * to both the web and native implementations.
 */
// eslint-disable-next-line new-cap
export default AbstractPollResults(PollResults);
