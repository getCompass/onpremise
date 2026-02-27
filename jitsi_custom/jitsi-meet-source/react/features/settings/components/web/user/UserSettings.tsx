/* eslint-disable react/no-multi-comp */
import React from 'react';
import { connect } from 'react-redux';
import { makeStyles } from 'tss-react/mui';

import ContextMenu from "../../../../base/ui/components/web/ContextMenu";
import OnJoinSettings from "../onJoin/OnJoinSettings";

const useStyles = makeStyles()(() => {
    return {
        contextMenu: {
            padding: '20px 0px 12px 0px',
            position: 'relative',
            right: 'auto',
            margin: 0,
            marginBottom: '16px',
            maxHeight: 'calc(100dvh - 100px)',
            overflow: 'auto',
            boxSizing: 'border-box',
            minWidth: '369px',
        },
    };
});

const UserSettings = () => {
    const { classes } = useStyles();

    return (
        <ContextMenu
            activateFocusTrap = {true}
            aria-labelledby = 'user-settings-button'
            className = {classes.contextMenu}
            hidden = {false}
            id = 'user-settings-dialog'
            tabIndex = {-1}>
            <OnJoinSettings />
        </ContextMenu>
    );
};

const mapStateToProps = () => {
    return {};
};

const mapDispatchToProps = () => {
    return {};
};

export default connect(mapStateToProps, mapDispatchToProps)(UserSettings);
