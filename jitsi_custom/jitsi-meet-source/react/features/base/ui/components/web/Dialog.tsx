import React, { useCallback, useEffect } from 'react';
import { useTranslation } from 'react-i18next';
import { useDispatch, useSelector } from 'react-redux';
import { makeStyles } from 'tss-react/mui';

import { hideDialog } from '../../../dialog/actions';
import { IconCloseLarge } from '../../../icons/svg';
import { operatesWithEnterKey } from '../../functions.web';

import BaseDialog, { IProps as IBaseDialogProps } from './BaseDialog';
import Button from './Button';
import ClickableIcon from './ClickableIcon';
import { IReduxState } from "../../../../app/types";
import { isMobileBrowser } from "../../../environment/utils";


const useStyles = makeStyles()(theme => {
    return {
        header: {
            width: '100%',
            padding: '20px 16px 16px 20px',
            boxSizing: 'border-box',
            display: 'flex',
            alignItems: 'flex-start',
            justifyContent: 'space-between',

            '&.is-mobile': {
                padding: '16px 16px 12px 16px',
            }
        },

        title: {
            fontFamily: 'Lato Regular',
            fontWeight: 'normal' as const,
            fontSize: '14px',
            lineHeight: '20px',
            color: 'rgba(255, 255, 255, 0.75)',
            margin: 0,
            padding: 0,

            '&.is-mobile': {
                fontSize: '16px',
                lineHeight: '22px',
                color: 'rgba(255, 255, 255, 0.7)',
            }
        },

        content: {
            height: 'auto',
            overflowY: 'auto',
            width: '100%',
            boxSizing: 'border-box',
            padding: '0 16px',
            overflowX: 'hidden',

            '@media (max-width: 448px)': {
                height: '100%'
            }
        },

        footer: {
            width: '100%',
            boxSizing: 'border-box',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'flex-end',
            padding: '24px',

            '& button:last-child': {
                marginLeft: '16px'
            }
        }
    };
});

interface IDialogProps extends IBaseDialogProps {
    back?: {
        hidden?: boolean;
        onClick?: () => void;
        translationKey?: string;
    };
    cancel?: {
        hidden?: boolean;
        translationKey?: string;
    };
    children?: React.ReactNode;
    customButton?: React.ReactNode;
    disableAutoHideOnSubmit?: boolean;
    hideCloseButton?: boolean;
    ok?: {
        disabled?: boolean;
        hidden?: boolean;
        translationKey?: string;
    };
    onCancel?: () => void;
    onSubmit?: () => void;
}

const Dialog = ({
    back = { hidden: true },
    cancel = { translationKey: 'dialog.Cancel' },
    customButton,
    children,
    className,
    classNameHeader,
    classNameHeaderTitle,
    classNameContent,
    classNameFooter,
    description,
    disableAutoHideOnSubmit = false,
    disableBackdropClose,
    hideCloseButton,
    disableEnter,
    disableEscape,
    ok = { translationKey: 'dialog.Ok' },
    onCancel,
    onSubmit,
    size,
    testId,
    title,
    titleKey
}: IDialogProps) => {
    const { classes, cx } = useStyles();
    const { t } = useTranslation();
    const dispatch = useDispatch();
    const { is_in_picture_in_picture_mode } = useSelector((state: IReduxState) => state['features/picture-in-picture']);
    const isMobile = isMobileBrowser();

    const onClose = useCallback(() => {
        dispatch(hideDialog());
        onCancel?.();
    }, [ onCancel ]);

    const submit = useCallback(() => {
        if ((document.activeElement && !operatesWithEnterKey(document.activeElement)) || !document.activeElement) {
            !disableAutoHideOnSubmit && dispatch(hideDialog());
            onSubmit?.();
        }
    }, [ onSubmit ]);

    useEffect(() => {
        if (is_in_picture_in_picture_mode) {
            dispatch(hideDialog());
        }
    }, [ is_in_picture_in_picture_mode ]);

    return (
        <BaseDialog
            className = {className}
            description = {description}
            disableBackdropClose = {disableBackdropClose}
            disableEnter = {disableEnter}
            disableEscape = {disableEscape}
            onClose = {onClose}
            size = {size}
            submit = {submit}
            testId = {testId}
            title = {title}
            titleKey = {titleKey}>
            <div className = {cx(classes.header, classNameHeader, isMobile && 'is-mobile')}>
                <h1
                    className = {cx(classes.title, classNameHeaderTitle, isMobile && 'is-mobile')}
                    id = 'dialog-title'>
                    {title ?? t(titleKey ?? '')}
                </h1>
                {!hideCloseButton && (
                    <ClickableIcon
                        accessibilityLabel = {t('dialog.accessibilityLabel.close')}
                        icon = {IconCloseLarge}
                        id = 'modal-header-close-button'
                        onClick = {onClose} />
                )}
            </div>
            <div
                className = {cx(classes.content, classNameContent, isMobile && 'is-mobile')}
                data-autofocus-inside = 'true'>
                {children}
            </div>
            {(!back.hidden || !cancel.hidden || !ok.hidden || customButton) && (
                <div
                    className = {cx(classes.footer, classNameFooter)}
                    data-autofocus-inside = 'true'>
                    {customButton && customButton}
                    {!back.hidden && <Button
                        accessibilityLabel = {t(back.translationKey ?? '')}
                        labelKey = {back.translationKey}
                        // eslint-disable-next-line react/jsx-handler-names
                        onClick = {back.onClick}
                        type = 'secondary' />}
                    {!cancel.hidden && <Button
                        accessibilityLabel = {t(cancel.translationKey ?? '')}
                        labelKey = {cancel.translationKey}
                        onClick = {onClose}
                        type = 'tertiary' />}
                    {!ok.hidden && <Button
                        accessibilityLabel = {t(ok.translationKey ?? '')}
                        disabled = {ok.disabled}
                        id = 'modal-dialog-ok-button'
                        isSubmit = {true}
                        labelKey = {ok.translationKey}
                        {...(!ok.disabled && { onClick: submit })} />}
                </div>
            )}
        </BaseDialog>
    );
};

export default Dialog;
