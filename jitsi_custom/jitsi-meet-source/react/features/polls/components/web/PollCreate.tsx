import React, {useCallback, useEffect, useRef, useState} from 'react';
import {makeStyles} from 'tss-react/mui';

import {withPixelLineHeight} from '../../../base/styles/functions.web';
import Button from '../../../base/ui/components/web/Button';
import Input from '../../../base/ui/components/web/Input';
import {BUTTON_TYPES} from '../../../base/ui/constants.web';
import {ANSWERS_LIMIT, CHAR_LIMIT} from '../../constants';
import AbstractPollCreate, {AbstractProps} from '../AbstractPollCreate';
import Icon from "../../../base/icons/components/Icon";
import {IconPlus} from "../../../base/icons/svg";
import {isMobileBrowser} from "../../../base/environment/utils";

const useStyles = makeStyles()(theme => {
    return {
        container: {
            height: '100%',
            position: 'relative'
        },
        createContainer: {
            padding: '0 16px',
            height: 'calc(100% - 67px)',
            overflowY: 'auto',

            '&.is-mobile': {
                height: 'calc(100% - 77px)',
            }
        },
        questionContainer: {
            paddingTop: '24px',
            paddingBottom: '24px',
        },
        questionLabelContainer: {
            marginBottom: '12px',
            fontFamily: 'Lato Regular',
            fontWeight: 'normal' as const,
            fontSize: '14px',
            lineHeight: '17px',
            display: 'flex',
            justifyContent: 'space-between',

            '&.is-mobile': {
                fontSize: '15px',
                lineHeight: '20px',
                letterSpacing: '-0.16px',
            }
        },
        questionLabel: {
            color: 'rgba(180, 180, 180, 1)',

            '&.is-mobile': {
                color: 'rgba(255, 255, 255, 0.7)'
            }
        },
        pollLabel: {
            marginBottom: '12px',
            fontFamily: 'Lato Regular',
            fontWeight: 'normal' as const,
            fontSize: '14px',
            lineHeight: '17px',
            color: 'rgba(180, 180, 180, 1)',

            '&.is-mobile': {
                fontSize: '15px',
                lineHeight: '20px',
                letterSpacing: '-0.16px',
                color: 'rgba(255, 255, 255, 0.7)'
            }
        },
        removeLabelButton: {
            fontFamily: 'Lato Regular',
            fontWeight: 'normal' as const,
            fontSize: '14px',
            lineHeight: '17px',
            color: 'rgba(255, 79, 71, 1)',
            backgroundColor: 'transparent',
            padding: 0,

            '&:not(:disabled)': {
                '&:hover': {
                    color: 'rgba(255, 39, 31, 1)',
                    backgroundColor: 'transparent'
                },

                '&:active': {
                    backgroundColor: 'transparent'
                },
            },

            '&.is-mobile': {
                backgroundColor: 'transparent',
                padding: '0 !important'
            }
        },
        questionInput: {
            padding: '12px 16px',
            borderRadius: '8px',
            backgroundColor: 'rgba(255, 255, 255, 0.03)',
            border: 'none',
            fontFamily: 'Lato Regular',
            fontSize: '14px',
            lineHeight: '20px',
            letterSpacing: '-0.15px',

            '&.is-mobile': {
                padding: '12px 16px',
                fontSize: '15px',
                lineHeight: '20px',
                letterSpacing: '-0.16px',
                backgroundColor: 'rgba(33, 33, 33, 1)',
            }
        },
        answerListContainer: {},
        answerList: {
            listStyleType: 'none',
            margin: 0,
            padding: 0
        },
        answer: {
            '&:first-child': {
                '&>div': {
                    '&>div': {
                        '&>textarea': {
                            borderRadius: '8px 8px 0px 0px !important',
                        }
                    },
                },
            },

            '&.is-mobile': {
                '&:first-child': {
                    '&>div': {
                        '&>div': {
                            '&>textarea': {
                                borderRadius: '12px 12px 0px 0px !important',
                            }
                        },
                    },
                },
            }
        },
        answerInput: {
            padding: '12px 16px',
            borderRadius: 0,
            backgroundColor: 'rgba(255, 255, 255, 0.03)',
            border: 'none',
            fontSize: '14px',
            lineHeight: '20px',
            letterSpacing: '-0.15px',
            marginBottom: '1px',

            '&.is-mobile': {
                padding: '12px 16px',
                fontSize: '15px',
                lineHeight: '20px',
                letterSpacing: '-0.16px',
                backgroundColor: 'rgba(33, 33, 33, 1)',
            }
        },
        removeOption: {
            ...withPixelLineHeight(theme.typography.bodyShortRegular),
            color: theme.palette.link01,
            marginTop: '8px',
            border: 0,
            background: 'transparent'
        },
        addButtonContainer: {
            display: 'flex',
            color: 'rgba(0, 122, 255, 1)',
            backgroundColor: 'rgba(255, 255, 255, 0.03)',
            borderRadius: '0px 0px 8px 8px',
            justifyContent: 'start',
            gap: '6px',
            alignItems: 'center',
            padding: '12px 16px',
            userSelect: 'none',
            '-webkit-tap-highlight-color': 'transparent',

            '&:hover': {
                cursor: 'pointer',
                '& .poll-create-add-button-plus': {
                    '& svg': {
                        fill: 'rgba(0, 88, 184, 1)'
                    }
                },
                '& .poll-create-add-button-button': {
                    cursor: 'pointer',
                    color: 'rgba(0, 88, 184, 1)',
                },
            },

            '&:active': {
                backgroundColor: 'transparent'
            },

            '&.is-mobile': {
                gap: '8px',
                borderRadius: '0px 0px 12px 12px',
            }
        },
        addButton: {
            color: 'rgba(0, 122, 255, 1)',
            backgroundColor: 'transparent',
            padding: 0,
            fontFamily: 'Lato Regular',
            fontSize: '14px',
            lineHeight: '20px',
            letterSpacing: '-0.15px',

            '&:not(:disabled)': {
                '&:hover': {
                    color: 'rgba(0, 88, 184, 1)',
                    backgroundColor: 'transparent'
                },

                '&:active': {
                    backgroundColor: 'transparent'
                },
            },

            '&.is-mobile': {
                fontSize: '15px !important',
                lineHeight: '20px !important',
                letterSpacing: '-0.16px',
                padding: '0 !important',
            }
        },

        createButton: {
            '&.is-mobile': {
                borderTop: '0.5px solid rgba(255, 255, 255, 0.08)',
                padding: '9px 16px',
                fontSize: '17px',
                lineHeight: '26px',
            }
        },

        footer: {
            position: 'absolute',
            bottom: 0,
            display: 'flex',
            justifyContent: 'flex-end',
            padding: '16px',
            width: '100%',
            boxSizing: 'border-box'
        },
    };
});

const PollCreate = ({
                        addAnswer,
                        answers,
                        isSubmitDisabled,
                        onSubmit,
                        question,
                        removeAnswer,
                        setAnswer,
                        setCreateMode,
                        setQuestion,
                        t
                    }: AbstractProps) => {
    const {classes, cx} = useStyles();
    const isMobile = isMobileBrowser();

    /*
     * This ref stores the Array of answer input fields, allowing us to focus on them.
     * This array is maintained by registerfieldRef and the useEffect below.
     */
    const answerInputs = useRef<Array<HTMLInputElement>>([]);
    const registerFieldRef = useCallback((i, r) => {
        if (r === null) {
            return;
        }
        answerInputs.current[i] = r;
    }, [answerInputs]);

    useEffect(() => {
        answerInputs.current = answerInputs.current.slice(0, answers.length);
    }, [answers]);

    /*
     * This state allows us to requestFocus asynchronously, without having to worry
     * about whether a newly created input field has been rendered yet or not.
     */
    const [lastFocus, requestFocus] = useState<number | null>(null);

    useEffect(() => {
        if (lastFocus === null) {
            return;
        }
        const input = answerInputs.current[lastFocus];

        if (input === undefined) {
            return;
        }
        input.focus();
    }, [lastFocus]);

    const checkModifiers = useCallback(ev => {
        // Composition events used to add accents to characters
        // despite their absence from standard US keyboards,
        // to build up logograms of many Asian languages
        // from their base components or categories and so on.
        if (ev.isComposing || ev.keyCode === 229) {
            // keyCode 229 means that user pressed some button,
            // but input method is still processing that.
            // This is a standard behavior for some input methods
            // like entering japanese or сhinese hieroglyphs.
            return true;
        }

        // Because this isn't done automatically on MacOS
        if (ev.key === 'Enter' && ev.metaKey) {
            ev.preventDefault();
            onSubmit();

            return;
        }
        if (ev.ctrlKey || ev.metaKey || ev.altKey || ev.shiftKey) {
            return;
        }
    }, []);

    const onQuestionKeyDown = useCallback(ev => {
        if (checkModifiers(ev)) {
            return;
        }

        if (ev.key === 'Enter') {
            requestFocus(0);
            ev.preventDefault();
        }
    }, []);

    // Called on keypress in answer fields
    const onAnswerKeyDown = useCallback((i, ev) => {
        if (checkModifiers(ev)) {
            return;
        }

        if (ev.key === 'Enter') {
            // We add a new option input
            // only if we are on the last option input
            if (i === answers.length - 1) {
                addAnswer(i + 1);
            }
            requestFocus(i + 1);
            ev.preventDefault();
        } else if (ev.key === 'Backspace' && ev.target.value === '' && answers.length > 1) {
            removeAnswer(i);
            requestFocus(i > 0 ? i - 1 : 0);
            ev.preventDefault();
        } else if (ev.key === 'ArrowDown') {
            if (i === answers.length - 1) {
                addAnswer();
            }
            requestFocus(i + 1);
            ev.preventDefault();
        } else if (ev.key === 'ArrowUp') {
            if (i === 0) {
                addAnswer(0);
                requestFocus(0);
            } else {
                requestFocus(i - 1);
            }
            ev.preventDefault();
        }
    }, [answers, addAnswer, removeAnswer, requestFocus]);

    /* eslint-disable react/jsx-no-bind */
    return (<form
        className={classes.container}
        onSubmit={onSubmit}>
        <div className={cx(classes.createContainer, isMobile && 'is-mobile')}>
            <div className={classes.questionContainer}>
                <div className={cx(classes.questionLabelContainer, isMobile && 'is-mobile')}>
                    <div className={classes.questionLabel}>{t('polls.create.pollQuestion')}</div>
                    <Button
                        accessibilityLabel={t('polls.create.cancel')}
                        labelKey={'polls.create.cancel'}
                        className={cx(classes.removeLabelButton, isMobile && 'is-mobile')}
                        onClick={() => setCreateMode(false)}
                        type={BUTTON_TYPES.DESTRUCTIVE}/>
                </div>
                <Input
                    inputClassName={classes.questionInput}
                    autoFocus={true}
                    id='polls-create-input'
                    maxLength={CHAR_LIMIT}
                    onChange={setQuestion}
                    onKeyPress={onQuestionKeyDown}
                    placeholder={t('polls.create.questionPlaceholder')}
                    textarea={true}
                    value={question}/>
            </div>
            <div className={classes.answerListContainer}>
                <div className={cx(classes.pollLabel, isMobile && 'is-mobile')}>{t('polls.create.pollOptions')}</div>
                <ol className={classes.answerList}>
                    {answers.map((answer: any, i: number) => {

                            // не проверяем дубликаты ответов
                            const isIdenticalAnswer = false;

                            return (<li
                                className={cx(classes.answer, isMobile && 'is-mobile')}
                                key={i}>
                                <Input
                                    inputClassName={classes.answerInput}
                                    bottomLabel={(isIdenticalAnswer ? t('polls.errors.notUniqueOption',
                                        {index: i + 1}) : '')}
                                    error={isIdenticalAnswer}
                                    id={`polls-answer-input-${i}`}
                                    maxLength={CHAR_LIMIT}
                                    onChange={val => setAnswer(i, val)}
                                    onKeyPress={ev => onAnswerKeyDown(i, ev)}
                                    placeholder={i === 0 ? t('polls.create.answerPlaceholder', {index: i + 1}) : t('polls.create.anotherAnswerPlaceholder', {index: i + 1})}
                                    ref={r => registerFieldRef(i, r)}
                                    textarea={true}
                                    value={answer}/>
                            </li>);
                        }
                    )}
                </ol>
            </div>
            <div className={cx(classes.addButtonContainer, isMobile && 'is-mobile')} onClick={() => {
                addAnswer();
                requestFocus(answers.length);
            }}>
                <Icon
                    size={16}
                    src={IconPlus}
                    className={'poll-create-add-button-plus'}
                    color={'rgba(0, 122, 255, 1)'}/>
                <Button
                    className={cx(classes.addButton, 'poll-create-add-button-button', isMobile && 'is-mobile')}
                    accessibilityLabel={t('polls.create.addOption')}
                    disabled={answers.length >= ANSWERS_LIMIT}
                    labelKey={'polls.create.addOption'}
                    onClick={() => {
                        addAnswer();
                        requestFocus(answers.length);
                    }}
                    type={BUTTON_TYPES.SECONDARY}/>
            </div>
        </div>
        <div className={classes.footer}>
            <Button
                accessibilityLabel={t('polls.create.send')}
                className={classes.createButton}
                disabled={isSubmitDisabled}
                fullWidth={true}
                isSubmit={true}
                labelKey={'polls.create.send'}
                type={BUTTON_TYPES.PRIMARY}/>
        </div>
    </form>);
};

/*
 * We apply AbstractPollCreate to fill in the AbstractProps common
 * to both the web and native implementations.
 */
// eslint-disable-next-line new-cap
export default AbstractPollCreate(PollCreate);
