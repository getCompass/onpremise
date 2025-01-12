import React from 'react';
import { WithTranslation } from 'react-i18next';
import { connect } from 'react-redux';
import { makeStyles } from 'tss-react/mui';

import { IReduxState } from '../../../../app/types';
import { translate } from '../../../../base/i18n/functions';
import { getDeviceStatusText, getDeviceStatusType } from '../../../functions';
import { IconCheck, IconWarningCircle } from "../../../../base/icons/svg";
import Icon from "../../../../base/icons/components/Icon";

export interface IProps extends WithTranslation {

    /**
     * The text to be displayed in relation to the status of the audio/video devices.
     */
    deviceStatusText?: string;

    /**
     * The type of status for current devices, controlling the background color of the text.
     * Can be `ok` or `warning`.
     */
    deviceStatusType?: string;
}

const useStyles = makeStyles()(theme => {
    return {
        deviceStatus: {
            display: 'flex',
            alignItems: 'start',
            justifyContent: 'start',
            gap: '8px',
            color: 'rgba(255, 255, 255, 0.7)',
            width: 'calc(100% - 58px)',
            position: 'absolute',
            bottom: '268px',
            zIndex: 1,
            fontWeight: 'normal' as const,
            fontSize: '14px',
            lineHeight: '20px',
            letterSpacing: '-0.16px',

            '&.device-status-error-mobile': {
                background: 'linear-gradient(122.89deg, rgba(28, 28, 28, 0.8) -31.07%, rgba(28, 28, 28, 0.8) 6.08%, rgba(28, 28, 28, 0.8) 42.1%, rgba(28, 28, 28, 0.8) 89.18%, rgba(28, 28, 28, 0.8) 122.33%)',
                border: '1px solid rgba(255, 255, 255, 0.05)',
                borderRadius: '8px',
                padding: '12px',
                textAlign: 'start'
            },
        },

        warningCircle: {
            opacity: '0.65',
        },

        title: {
            fontFamily: 'Lato Bold',
            color: 'rgba(255, 255, 255, 0.8)',
        },
        desc: {
            fontFamily: 'Lato SemiBold',
            color: 'rgba(255, 255, 255, 0.7)',
        },
    };
});

/**
 * Strip showing the current status of the devices.
 * User is informed if there are missing or malfunctioning devices.
 *
 * @returns {ReactElement}
 */
function DeviceStatusMobile({ deviceStatusType, deviceStatusText, t }: IProps) {
    const { classes, cx } = useStyles();
    const hasError = deviceStatusType === 'warning';
    const containerClassName = cx(classes.deviceStatus, { 'device-status-error-mobile': hasError });

    if (!hasError) {
        return <></>;
    }

    return (
        <div style = {{ padding: '0 16px' }}>
            <div
                className = {containerClassName}
                role = 'alert'
                tabIndex = {-1}>
                <Icon
                    aria-hidden = {true}
                    className = {classes.warningCircle}
                    color = 'rgba(255, 255, 255, 0.3)'
                    size = {60}
                    src = {IconWarningCircle} />
                <div>
                    <div className = {classes.title}>{t('prejoin.errorNoPermissionsTitle')}</div>
                    <div className = {classes.desc}>{t('prejoin.errorNoPermissions')}</div>
                </div>
            </div>
        </div>
    );
}

/**
 * Maps (parts of) the redux state to the React {@code Component} props.
 *
 * @param {Object} state - The redux state.
 * @returns {{ deviceStatusText: string, deviceStatusText: string }}
 */
function mapStateToProps(state: IReduxState) {
    return {
        deviceStatusText: getDeviceStatusText(state),
        deviceStatusType: getDeviceStatusType(state)
    };
}

export default translate(connect(mapStateToProps)(DeviceStatusMobile));
