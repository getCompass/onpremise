/* eslint-disable react/no-multi-comp */
import React from 'react';
import { useTranslation } from 'react-i18next';
import { connect } from 'react-redux';
import { makeStyles } from 'tss-react/mui';

import { IReduxState } from '../../app/types';
import ContextMenu from '../../base/ui/components/web/ContextMenu';
import ContextMenuItemGroup from '../../base/ui/components/web/ContextMenuItemGroup';
import CheckboxCircle from "../../base/ui/components/web/CheckboxCircle";

export interface IProps {
    screenShareHint: 'detail' | 'motion';

    onClick: Function;
}

const useStyles = makeStyles()(theme => {
    return {
        contextMenu: {
            maxWidth: '315px',
            padding: "19px",
            position: 'relative',
            right: 'auto',
            margin: 0,
            marginBottom: '4px',
            maxHeight: 'calc(100dvh - 100px)',
            overflow: 'auto',
        },

        contextMenuItemGroup: {
            padding: '0px !important',
        },

        separateLineContainer: {
            padding: '6px 4px 7px 4px',
            margin: '12px 0px',
        },

        separateLine: {
            backgroundColor: 'rgba(255, 255, 255, 0.05)',
            height: '1px'
        },
    };
});

const DesktopPickerQualityContent = (props: IProps) => {
    const { classes } = useStyles();
    const { t } = useTranslation();

    return (
        <ContextMenu
            activateFocusTrap = {true}
            aria-labelledby = 'desktop-share-quality-settings-button'
            className = {classes.contextMenu}
            hidden = {false}
            id = 'desktop-share-quality-settings-dialog'
            tabIndex = {-1}>
            <ContextMenuItemGroup className = {classes.contextMenuItemGroup}>
                <div onClick = {() => props.onClick("detail")}>
                    <CheckboxCircle
                        checked = {props.screenShareHint === "detail"}
                        title = {t('screenshare.high_readability_text.title')}
                        description = {t('screenshare.high_readability_text.description')}
                        name = 'high-readability-text-checkbox'
                        disabled = {false} />
                </div>
            </ContextMenuItemGroup>
            <div className = {classes.separateLineContainer}>
                <div className = {classes.separateLine} />
            </div>
            <ContextMenuItemGroup className = {classes.contextMenuItemGroup}>
                <div onClick = {() => props.onClick("motion")}>
                    <CheckboxCircle
                        checked = {props.screenShareHint === "motion"}
                        title = {t('screenshare.smooth_video.title')}
                        description = {t('screenshare.smooth_video.description')}
                        name = 'smooth-video-checkbox'
                        disabled = {false} />
                </div>
            </ContextMenuItemGroup>
        </ContextMenu>
    );
};

const mapStateToProps = (state: IReduxState) => {
    return {};
};

export default connect(mapStateToProps)(DesktopPickerQualityContent);
