/* eslint-disable react/jsx-no-bind */
import React from 'react';
import { useTranslation } from 'react-i18next';
import {connect, useDispatch} from 'react-redux';
import { makeStyles } from 'tss-react/mui';

import { IReduxState } from '../../app/types';
import Button from "../../base/ui/components/web/Button";
import {BUTTON_TYPES} from "../../base/ui/constants.any";
import {joinConference, setPrejoinPageVisibility} from "../actions.web";

interface IProps {
    joiningInProgress: boolean;
}

const useStyles = makeStyles()(theme => {
    return {
        container: {
            gridArea: 'button',
            alignSelf: 'center',
            justifySelf: 'flex-end',
            paddingRight: '16px',
        },
        button: {

        },
    };
});

const PrejoinJoinButton = ({ joiningInProgress }: IProps) => {
    const { classes } = useStyles();
    const { t } = useTranslation();
    const dispatch = useDispatch();

    const onClick = () => {
        dispatch(joinConference());
    };
    return (<div className={classes.container}>
        <Button
            className = {classes.button}
            accessibilityLabel = {t('lobby.previewWindow.button')}
            labelKey = {t('lobby.previewWindow.button')}
            disabled = {joiningInProgress}
            onClick = {onClick}
            role = 'button'
            tabIndex = {0}
            testId = 'lobby.previewWindow.button'
            type = {BUTTON_TYPES.PRIMARY} />
    </div>);
}


function mapStateToProps(state: IReduxState) {
    const { joiningInProgress } = state['features/prejoin'];
    return {
        joiningInProgress: joiningInProgress ?? false,
    };
}

const mapDispatchToProps = {};

export default connect(mapStateToProps, mapDispatchToProps)(PrejoinJoinButton);
