import React from 'react';
import { makeStyles } from 'tss-react/mui';

import Button from '../../../base/ui/components/web/Button';
import AbstractPollsPane, { AbstractProps } from '../AbstractPollsPane';

import PollCreate from './PollCreate';
import PollsList from './PollsList';
import { BUTTON_TYPES } from "../../../base/ui/constants.any";
import { useSelector } from "react-redux";
import { IReduxState } from "../../../app/types";
import { isMobileBrowser } from "../../../base/environment/utils";
/* eslint-enable lines-around-comment */

const useStyles = makeStyles()(() => {
    return {
        container: {
            height: '100%',
            position: 'relative'
        },
        emptyListContainer: {
            height: 'calc(100% - 88px)',
            overflowY: 'auto',

            '&.is-mobile': {
                height: 'calc(100% - 77px)',
            }
        },
        listContainer: {
            height: 'calc(100% - 88px)',
            overflowY: 'auto',

            '&>div': {
                '&:first-child': {
                    '&>div': {
                        marginTop: '24px',
                    }
                }
            },
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
            padding: '16px',
            width: '100%',
            boxSizing: 'border-box',

            '&.is-mobile': {
                borderTop: '0.5px solid rgba(255, 255, 255, 0.08)',
            }
        }
    };
});

const PollsPane = ({ createMode, onCreate, setCreateMode, t }: AbstractProps) => {
    const { classes, cx } = useStyles();
    const isMobile = isMobileBrowser();

    const polls = useSelector((state: IReduxState) => state['features/polls'].polls);
    const listPolls = Object.keys(polls);

    return createMode
        ? <PollCreate setCreateMode = {setCreateMode} />
        : <div className = {classes.container}>
            <div
                className = {cx(listPolls.length === 0 ? classes.emptyListContainer : classes.listContainer, isMobile && 'is-mobile')}>
                <PollsList setCreateMode = { setCreateMode } />
            </div>
            <div className = {cx(classes.footer, isMobile && 'is-mobile')}>
                <Button
                    accessibilityLabel = {t('polls.create.create')}
                    className = {classes.createButton}
                    fullWidth = {true}
                    labelKey = {'polls.create.create'}
                    onClick = {onCreate}
                    type = {BUTTON_TYPES.PRIMARY} />
            </div>
        </div>;
};

/*
 * We apply AbstractPollsPane to fill in the AbstractProps common
 * to both the web and native implementations.
 */
// eslint-disable-next-line new-cap
export default AbstractPollsPane(PollsPane);
