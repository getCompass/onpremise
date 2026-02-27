/* eslint-disable react/jsx-no-bind */
import React from 'react';
import { connect } from 'react-redux';
import { makeStyles } from 'tss-react/mui';


import {
    PREMEETING_BUTTONS_ELECTRON,
} from "../../base/config/constants";
import Toolbox from "../../toolbox/components/web/Toolbox";

interface IProps {
    buttons: Array<string>;
}

const useStyles = makeStyles()(theme => {
    return {
        container: {
            gridArea: 'settings',

            ' .toolbox-content': {
                marginBottom: 0,
            },

            ' .toolbox-content-items': {
                paddingLeft: 0,
                paddingRight: 0,
                background: "transparent",
            },

            ' .toolbox-content-items>div': {
                marginRight: '12px',
            },

            ' .toolbox-button .jitsi-icon > svg': {
                width: '24px',
                height: '24px',
            },

            ' .hangup-button .jitsi-icon > svg': {
                width: '32px',
                height: '32px',
            }
        }
    };
});

const PrejoinSettings = ({ buttons }: IProps) => {
    const { classes } = useStyles();
    return (<div className={classes.container}>
        <Toolbox toolbarButtons = {buttons} isLobby />
    </div>);
}


function mapStateToProps() {
    return {
        buttons: PREMEETING_BUTTONS_ELECTRON,
    };
}

const mapDispatchToProps = {};

export default connect(mapStateToProps, mapDispatchToProps)(PrejoinSettings);
