import React, { useCallback } from 'react';
import { useTranslation } from 'react-i18next';
import { useDispatch, useSelector } from 'react-redux';
import { makeStyles } from 'tss-react/mui';

import { IReduxState } from '../../../app/types';
import { IconRaiseHand } from '../../../base/icons/svg';
import Label from '../../../base/label/components/web/Label';
import Tooltip from '../../../base/tooltip/components/Tooltip';
import { open as openParticipantsPane } from '../../../participants-pane/actions.web';

const useStyles = makeStyles()(theme => {
    return {
        label: {
            backgroundColor: 'rgba(255, 214, 56, 1)',
            color: 'rgba(0, 0, 0, 1)'
        }
    };
});

const RaisedHandsCountLabel = () => {
    const { classes: styles, theme } = useStyles();
    const dispatch = useDispatch();
    const raisedHandsCount = useSelector((state: IReduxState) =>
        (state['features/base/participants'].raisedHandsQueue || []).length);
    const { t } = useTranslation();
    const onClick = useCallback(() => {
        dispatch(openParticipantsPane());
    }, []);

    return raisedHandsCount > 0 ? (<Label
        accessibilityText = { t('raisedHandsLabel') }
        className = { styles.label }
        icon = { IconRaiseHand }
        iconColor = { 'rgba(0, 0, 0, 1)' }
        id = 'raisedHandsCountLabel'
        onClick = { onClick }
        text = { `${raisedHandsCount}` } />) : null;
};

export default RaisedHandsCountLabel;
