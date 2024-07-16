import React, {useCallback} from 'react';
import {useTranslation} from 'react-i18next';
import {makeStyles} from 'tss-react/mui';

import {withPixelLineHeight} from '../../base/styles/functions.web';
import Select from '../../base/ui/components/web/Select';
import {isMobileBrowser} from "../../base/environment/utils";
import {useSelector} from "react-redux";
import SelectMobile from "../../base/ui/components/web/SelectMobile";

/**
 * The type of the React {@code Component} props of {@link DeviceSelector}.
 */
interface IProps {

    /**
     * MediaDeviceInfos used for display in the select element.
     */
    devices: Array<MediaDeviceInfo> | undefined;

    /**
     * If false, will return a selector with no selection options.
     */
    hasPermission: boolean;

    /**
     * The id of the dropdown element.
     */
    id: string;

    /**
     * If true, will render the selector disabled with a default selection.
     */
    isDisabled: boolean;

    /**
     * The translation key to display as a menu label.
     */
    label: string;

    /**
     * Left icon for action.
     */
    icon?: Function;

    /**
     * The callback to invoke when a selection is made.
     */
    onSelect: Function;

    /**
     * The default device to display as selected.
     */
    selectedDeviceId: string;
}

const useStyles = makeStyles()(theme => {
    return {
        textSelector: {
            width: '100%',
            boxSizing: 'border-box',
            borderRadius: theme.shape.borderRadius,
            backgroundColor: theme.palette.uiBackground,
            padding: '10px 16px',
            textAlign: 'center',
            ...withPixelLineHeight(theme.typography.bodyShortRegular),
            border: `1px solid ${theme.palette.ui03}`
        }
    };
});

const DeviceSelector = ({
                            devices,
                            hasPermission,
                            id,
                            isDisabled,
                            label,
                            icon,
                            onSelect,
                            selectedDeviceId
                        }: IProps) => {
    const {classes} = useStyles();
    const {t} = useTranslation();
    const isMobile = useSelector(() => isMobileBrowser());

    const _onSelect = useCallback((e: React.ChangeEvent<HTMLSelectElement>) => {
        const deviceId = e.target.value;

        if (selectedDeviceId !== deviceId) {
            onSelect(deviceId);
        }
    }, [selectedDeviceId, onSelect]);

    const _createDropdown = (options: {
        defaultSelected?: MediaDeviceInfo; isDisabled: boolean;
        items?: Array<{ label: string; value: string; }>; placeholder: string;
    }, isMobile: boolean) => {
        const triggerText
            = (options.defaultSelected && (options.defaultSelected.label ? cleanLabel(options.defaultSelected.label) : options.defaultSelected.deviceId))
            || options.placeholder;

        if (options.isDisabled || !options.items?.length) {
            return (
                <div className={classes.textSelector}>
                    {triggerText}
                </div>
            );
        }

        if (isMobile) {
            return (
                <SelectMobile
                    id={id}
                    onSelect={onSelect}
                    options={options.items}
                    icon={icon}
                    value={selectedDeviceId}
                    isLanguages={false}/>
            );
        }

        return (
            <Select
                id={id}
                onChange={_onSelect}
                options={options.items}
                icon={icon}
                value={selectedDeviceId}/>
        );
    };

    const _renderNoDevices = () => _createDropdown({
        isDisabled: true,
        placeholder: t('settings.noDevice')
    }, isMobile);

    const _renderNoPermission = () => _createDropdown({
        isDisabled: true,
        placeholder: t('deviceSelection.noPermission')
    }, isMobile);

    if (hasPermission === undefined) {
        return null;
    }

    if (!hasPermission) {
        return _renderNoPermission();
    }

    if (!devices?.length) {
        return _renderNoDevices();
    }

    const cleanLabel = (label: string | undefined): string => {
        if (label === undefined) {
            return "";
        }
        return label.replace(/\([a-zA-Z0-9]{1,10}:[a-zA-Z0-9]{1,10}\)/, '').trim();
    };

    const items = devices.map(device => {
        return {
            value: device.deviceId,
            label: device.label ? cleanLabel(device.label) : device.deviceId
        };
    });
    const defaultSelected = devices.find(item =>
        item.deviceId === selectedDeviceId
    );

    return _createDropdown({
        defaultSelected,
        isDisabled,
        items,
        placeholder: t('deviceSelection.selectADevice')
    }, isMobile);
};

export default DeviceSelector;
