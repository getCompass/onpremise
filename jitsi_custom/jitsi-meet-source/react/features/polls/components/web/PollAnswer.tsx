/* eslint-disable react/jsx-no-bind */

import React from 'react';
import { useDispatch } from 'react-redux';
import { makeStyles } from 'tss-react/mui';

import Icon from '../../../base/icons/components/Icon';
import { IconCloseLarge } from '../../../base/icons/svg';
import { withPixelLineHeight } from '../../../base/styles/functions.web';
import Button from '../../../base/ui/components/web/Button';
import Checkbox from '../../../base/ui/components/web/Checkbox';
import { BUTTON_TYPES } from '../../../base/ui/constants.web';
import { editPoll, removePoll } from '../../actions';
import { isSubmitAnswerDisabled } from '../../functions';
import AbstractPollAnswer, { AbstractProps } from '../AbstractPollAnswer';
import { isMobileBrowser } from "../../../base/environment/utils";

const useStyles = makeStyles()(theme => {
    return {
        container: {
            margin: '8px 16px',
            padding: '16px',
            backgroundColor: 'rgba(255, 255, 255, 0.03)',
            borderRadius: '8px',
            wordBreak: 'break-word'
        },
        closeBtn: {
            cursor: 'pointer',
            float: 'right'
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
        answerList: {
            listStyleType: 'none',
            margin: 0,
            padding: 0,
            marginBottom: '24px',

            '&.is-mobile': {
                marginBottom: '32px',
            }
        },
        answer: {
            display: 'flex',
            marginBottom: '16px',
            '&:last-child': {
                marginBottom: 0,
            },
        },
        checkbox: {
            padding: 0
        },
        footer: {
            display: 'flex',
            justifyContent: 'flex-end',
            gap: '12px',

            '&.is-mobile': {
                justifyContent: 'space-between',
            }
        },
        buttonCancel: {
            color: 'rgba(255, 255, 255, 1)',

            '&.is-mobile': {
                color: 'rgba(180, 180, 180, 1)',
                padding: '9px 16px',
                fontSize: '17px',
                lineHeight: '26px',
            }
        },
        buttonCreate: {
            '&.is-mobile': {
                padding: '9px 16px',
                fontSize: '17px',
                lineHeight: '26px',
            }
        },
    };
});

const PollAnswer = ({
    creatorName,
    checkBoxStates,
    poll,
    pollId,
    setCheckbox,
    setCreateMode,
    skipAnswer,
    skipChangeVote,
    sendPoll,
    submitAnswer,
    t
}: AbstractProps) => {
    const { changingVote, saved: pollSaved } = poll;
    const dispatch = useDispatch();

    const { classes, cx } = useStyles();

    const isMobile = isMobileBrowser();

    return (
        <div className = {classes.container}>
            {
                pollSaved && <Icon
                    ariaLabel = { t('polls.closeButton') }
                    className = { classes.closeBtn }
                    onClick = { () => dispatch(removePoll(pollId, poll)) }
                    role = 'button'
                    src = { IconCloseLarge }
                    tabIndex = { 0 } />
            }
            <div className = {cx(classes.header, isMobile && 'is-mobile')}>
                <div className = {cx(classes.question, isMobile && 'is-mobile')}>
                    {poll.question}
                </div>
                <div className = {cx(classes.creator, isMobile && 'is-mobile')}>
                    {creatorName}
                </div>
            </div>
            <ul className = {cx(classes.answerList, isMobile && 'is-mobile')}>
                {
                    poll.answers.map((answer, index: number) => (
                        <li
                            className = {classes.answer}
                            key = {index}>
                            <Checkbox
                                className = {classes.checkbox}
                                checked = {checkBoxStates[index]}
                                disabled = { poll.saved }
                                key = {index}
                                label = {answer.name}
                                onChange = {ev => setCheckbox(index, ev.target.checked)} />
                        </li>
                    ))
                }
            </ul>
            <div className = {cx(classes.footer, isMobile && 'is-mobile')}>
                {
                    pollSaved ? <>
                        <Button
                            accessibilityLabel = { t('polls.answer.edit') }
                            className = { classes.buttonCancel }
                            labelKey = { 'polls.answer.edit' }
                            onClick = { () => {
                                setCreateMode(true);
                                dispatch(editPoll(pollId, true));
                            } }
                            type = { BUTTON_TYPES.SECONDARY } />
                        <Button
                            accessibilityLabel = { t('polls.answer.send') }
                            labelKey = { 'polls.answer.send' }
                            onClick = { sendPoll } />
                    </> : <>
                        <Button
                            accessibilityLabel = {t('polls.answer.skip')}
                            className = {classes.buttonCancel}
                            labelKey = {'polls.answer.skip'}
                            onClick = {changingVote ? skipChangeVote : skipAnswer}
                            type = {BUTTON_TYPES.SECONDARY} />
                        <Button
                            accessibilityLabel = {t('polls.answer.submit')}
                            className = {classes.buttonCreate}
                            disabled = {isSubmitAnswerDisabled(checkBoxStates)}
                            labelKey = {'polls.answer.submit'}
                            onClick = {submitAnswer} />
                    </>
                }
            </div>
        </div>
    );
};

/*
 * We apply AbstractPollAnswer to fill in the AbstractProps common
 * to both the web and native implementations.
 */
// eslint-disable-next-line new-cap
export default AbstractPollAnswer(PollAnswer);
