/* eslint-disable react/jsx-no-bind */
import React from 'react';
import { WithTranslation} from 'react-i18next';
import { connect } from 'react-redux';
import { makeStyles } from 'tss-react/mui';

import { updateSettings } from '../../base/settings/actions';

import { IReduxState } from '../../app/types';
import {getLocalJitsiVideoTrack} from "../../base/tracks/functions.any";
import Checkbox from "../../base/ui/components/web/Checkbox";
import {isLobbyEnabledOnJoin} from "../../base/settings/functions.any";
import {translate} from "../../base/i18n/functions";
import PrejoinAlwaysShowCheckboxTooltip from "./PrejoinAlwaysShowCheckboxTooltip";

interface IProps extends WithTranslation {
    updateSettings: Function;
    isLobbyEnabledOnJoin: boolean;
}

const useStyles = makeStyles()(theme => {
    return {
        container: {
            gridArea: 'checkbox',
            alignSelf: 'center',
            justifySelf: 'flex-start',
            paddingLeft: '24px',
        },
        wrapper: {
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            maxWidth: '334px',
            padding: '12px 0',

            ' label': {
                padding: 0,
                alignItems: 'flex-start',
            }
        },
    };
});

const PrejoinAlwaysShowCheckbox = (props: IProps) => {
    const { classes } = useStyles();
    const { isLobbyEnabledOnJoin } = props;

    const onChange = () => {
        const newValue = !isLobbyEnabledOnJoin;
        props.updateSettings({
            isLobbyEnabledOnJoin: newValue,
        });
    }

    return(<div className = {classes.container}>
        <div className = {classes.wrapper}>
            <Checkbox label={<PrejoinAlwaysShowCheckboxTooltip />} onChange={onChange} checked={isLobbyEnabledOnJoin} />
        </div>
    </div>);
};


function mapStateToProps(state: IReduxState) {
    return {
        videoTrack: getLocalJitsiVideoTrack(state),
        isLobbyEnabledOnJoin: isLobbyEnabledOnJoin(state) ?? true,
    };
}

const mapDispatchToProps = {
    updateSettings,
};

export default translate(connect(mapStateToProps, mapDispatchToProps)(PrejoinAlwaysShowCheckbox));


