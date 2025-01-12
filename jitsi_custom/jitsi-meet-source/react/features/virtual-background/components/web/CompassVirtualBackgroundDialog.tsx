import React, { useCallback, useEffect, useState } from 'react';
import { connect } from 'react-redux';
import { makeStyles } from 'tss-react/mui';

import { IReduxState, IStore } from '../../../app/types';
import { hideDialog } from "../../../base/dialog/actions";
import { checkBlurSupport, checkVirtualBackgroundEnabled } from "../../functions";
import VirtualBackgrounds from "../VirtualBackgrounds";
import { getCompassVirtualBackgroundProps } from "../../../settings/functions.web";
import { IVirtualBackground } from "../../reducer";
import { submitVirtualBackgroundTab } from "../../../settings/actions.web";
import Dialog from "../../../base/ui/components/web/Dialog";

/**
 * The type of the React {@code Component} props of
 * {@link CompassVirtualBackgroundDialog}.
 */
interface IProps {
    /**
     * Virtual background options.
     */
    options: IVirtualBackground;

    /**
     * The id of the selected video device.
     */
    selectedVideoInputId: string;

    /**
     * Dispatch.
     */
    dispatch: IStore['dispatch'];
}

const useStyles = makeStyles()(() => {
    return {
        modal: {
            paddingBottom: '24px',
        },
        dialogHeader: {
            padding: '24px',
        },
        dialogHeaderTitle: {
            fontFamily: 'Lato Bold',
            fontWeight: 'normal' as const,
            fontSize: '20px',
            lineHeight: '24px',
            color: 'rgba(255, 255, 255, 0.75)',
        },
        dialogHeaderContent: {
            padding: '0 24px',
            overflowY: 'hidden',
            minHeight: '452px',
        },
        container: {
            width: '100%',
            display: 'flex',
            flexDirection: 'column' as const
        }
    };
});

const CompassVirtualBackgroundDialog = ({ dispatch, options, selectedVideoInputId }: IProps) => {
    const { classes } = useStyles();

    // Manage options in local state
    const [ optionsState, setOptionsState ] = useState(options);

    // Update local state when props change
    useEffect(() => {
        setOptionsState(options);
    }, [ options ]);

    /**
     * Callback invoked when virtual background options are changed.
     *
     * @param {Object} newOptions - The new background options.
     *
     * @returns {void}
     */
    const onOptionsChanged = useCallback((newOptions: IVirtualBackground) => {
        setOptionsState(newOptions);
    }, []);

    const onCloseDialog = useCallback((newOptions: IVirtualBackground) => {
        dispatch(submitVirtualBackgroundTab({ options: newOptions }));
    }, [])

    return (
        <Dialog
            className = {classes.modal}
            classNameHeader = {classes.dialogHeader}
            classNameHeaderTitle = {classes.dialogHeaderTitle}
            classNameContent = {classes.dialogHeaderContent}
            onCancel = {() => onCloseDialog(optionsState)}
            size = 'xl'
            titleKey = 'virtualBackground.title'
            ok = {{ hidden: true }}
            cancel = {{ hidden: true }}>
            <div
                className = {classes.container}
                id = 'virtual-background-dialog'
                key = 'virtual-background'>
                <VirtualBackgrounds
                    onOptionsChange = {onOptionsChanged}
                    options = {optionsState}
                    selectedThumbnail = {optionsState.selectedThumbnail ?? ''}
                    selectedVideoInputId = {selectedVideoInputId} />
            </div>
        </Dialog>
    );
};

/**
 * Maps (parts of) the Redux state to the associated props for the
 * {@code CompassVirtualBackgroundDialog} component.
 *
 * @param {Object} state - The Redux state.
 * @param {Object} ownProps - The props passed to the component.
 * @private
 * @returns {{
 *     options: IVirtualBackground,
 *     selectedVideoInputId: string
 * }}
 */
function _mapStateToProps(state: IReduxState, ownProps: any) {
    const { options, selectedVideoInputId } = getCompassVirtualBackgroundProps(state);
    return {
        options,
        selectedVideoInputId: typeof selectedVideoInputId === 'string' ? selectedVideoInputId : '',
    };
}

export default connect(_mapStateToProps)(CompassVirtualBackgroundDialog);
