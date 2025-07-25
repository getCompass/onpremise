import React, { ReactNode, useCallback, useContext, useEffect } from 'react';
import { FocusOn } from 'react-focus-on';
import { useTranslation } from 'react-i18next';
import { keyframes } from 'tss-react';
import { makeStyles } from 'tss-react/mui';
import { isElementInTheViewport } from '../../functions.web';

import { DialogTransitionContext } from './DialogTransition';
import { useDispatch, useSelector } from "react-redux";
import { IReduxState } from "../../../../app/types";
import { hideDialog } from "../../../dialog/actions";
import { isMobileBrowser } from "../../../environment/utils";
import { isAnyDialogOpen } from "../../../dialog/functions";

const useStyles = makeStyles()(theme => {
    return {
        container: {
            width: '100%',
            height: '100%',
            position: 'fixed',
            color: 'rgba(255, 255, 255, 0.75)',
            fontFamily: 'Lato Regular',
            fontWeight: 'normal' as const,
            letterSpacing: '-0.15px',
            fontSize: '14px',
            lineHeight: '20px',
            top: 0,
            left: 0,
            display: 'flex',
            justifyContent: 'center',
            alignItems: 'flex-start',
            zIndex: 301,
            animation: `${keyframes`
                0% {
                    opacity: 0.4;
                }
                100% {
                    opacity: 1;
                }
            `} 0.2s forwards ease-out`,

            '&.unmount': {
                animation: `${keyframes`
                    0% {
                        opacity: 1;
                    }
                    100% {
                        opacity: 0.5;
                    }
                `} 0.15s forwards ease-in`,

                '&.is-mobile': {
                    animation: 'none',
                },
            },

            '&.is-mobile': {
                animation: 'none',
            },

            '&.position-center': {
                alignItems: 'center',
            },
        },

        backdrop: {
            position: 'absolute',
            width: '100%',
            height: '100%',
            top: 0,
            left: 0,
            backgroundColor: 'rgba(0, 0, 0, 1)',
            opacity: 0.8,

            '&.is-mobile': {
                opacity: 0.9,
            },
        },

        mobileContainer: {
            position: "absolute",
            left: 0,
            bottom: 0,
            width: "100%",
            height: "100%",
            display: "flex",
            justifyContent: "center",
            alignItems: "center",
        },

        modal: {
            backgroundColor: 'rgba(28, 28, 28, 1)',
            border: 'none',
            boxShadow: '0px 4px 25px 4px rgba(20, 20, 20, 0.6)',
            borderRadius: '8px',
            display: 'flex',
            flexDirection: 'column',
            height: 'auto',
            maxHeight: '80vh',
            marginTop: '42px',
            paddingBottom: '16px',
            animation: `${keyframes`
                0% {
                    margin-top: 85px
                }
                100% {
                    margin-top: 64px
                }
            `} 0.2s forwards ease-out`,

            '&.mediumMobile': {
                width: '350px !important',
                position: 'relative',
                borderRadius: '12px',
            },

            '&.medium': {
                width: '360px'
            },

            '&.xl': {
                width: '445px'
            },

            '&.large': {
                width: '665px'
            },

            '&.xxl': {
                width: '770px'
            },

            '&.unmount': {
                animation: `${keyframes`
                    0% {
                        margin-top: 64px
                    }
                    100% {
                        margin-top: 40px
                    }
                `} 0.15s forwards ease-in`
            },

            '&.position-center': {
                marginTop: '0px',
                animation: 'none',
            },

            '@media (max-width: 448px)': {
                borderRadius: '15px 15px 0 0',
                width: '100% !important',
                maxHeight: 'initial',
                margin: 0,
                position: 'absolute',
                left: 0,
                bottom: 0,
                animation: `${keyframes`
                    0% {
                        margin-top: 15px
                    }
                    100% {
                        margin-top: 0
                    }
                `} 0.2s forwards ease-out`,

                '&.unmount': {
                    animation: `${keyframes`
                        0% {
                            margin-top: 0
                        }
                        100% {
                            margin-top: 15px
                        }
                    `} 0.15s forwards ease-in`
                }
            }
        },

        focusLock: {
            zIndex: 1
        }
    };
});

export interface IProps {
    children?: ReactNode;
    className?: string;
    classNameHeader?: string;
    classNameHeaderTitle?: string;
    classNameContent?: string;
    classNameFooter?: string;
    okButtonClassName?: string;
    description?: string;
    disableBackdropClose?: boolean;
    disableEnter?: boolean;
    disableEscape?: boolean;
    onClose?: () => void;
    size?: 'xxl' | 'large' | 'xl' | 'medium' | 'mediumMobile';
    submit?: () => void;
    testId?: string;
    title?: string;
    titleKey?: string;
    position?: '' | 'center';
}

const BaseDialog = ({
    children,
    className,
    description,
    disableBackdropClose,
    disableEnter,
    disableEscape,
    onClose,
    size = 'medium',
    submit,
    testId,
    title,
    titleKey,
    position = ''
}: IProps) => {
    const { classes, cx } = useStyles();
    const dispatch = useDispatch();
    const { isUnmounting } = useContext(DialogTransitionContext);
    const { t } = useTranslation();
    const { is_in_picture_in_picture_mode } = useSelector((state: IReduxState) => state['features/picture-in-picture']);
    const is_open_any_dialog = useSelector((state: IReduxState) => isAnyDialogOpen(state));
    const isMobile = isMobileBrowser();

    const onBackdropClick = useCallback(() => {
        !disableBackdropClose && onClose?.();
    }, [ disableBackdropClose, onClose ]);

    const handleKeyDown = useCallback((e: KeyboardEvent) => {
        if (e.key === 'Escape' && !disableEscape) {
            onClose?.();
        }
        if (e.key === 'Enter' && !disableEnter) {
            submit?.();
        }
    }, [ disableEnter, onClose, submit ]);

    useEffect(() => {
        window.addEventListener('keydown', handleKeyDown);

        return () => window.removeEventListener('keydown', handleKeyDown);
    }, [ handleKeyDown ]);

    useEffect(() => {

        if (is_in_picture_in_picture_mode) {
            dispatch(hideDialog());
        }
    }, [ is_in_picture_in_picture_mode ]);

    // функция для android доступная из dom, чтобы вызывать ее с kotlin
    // @ts-ignore
    window.dispatchNavigationBack = () => {

        // если нажали назад, когда нет открытых попапов - сворачиваемся в pip
        if (!is_open_any_dialog) {

            // @ts-ignore
            if (typeof AndroidJitsiWebInterface !== 'undefined' && typeof AndroidJitsiWebInterface.showPictureInPictureMode === 'function') {

                // @ts-ignore
                AndroidJitsiWebInterface.showPictureInPictureMode()
            }
            return;
        }

        // иначе просто скрываем диалог
        dispatch(hideDialog());
    };

    if (position === "center" && isMobile) {

        return (
            <div
                className = {cx(classes.container, isUnmounting && 'unmount', isMobile && 'is-mobile', position && `position-${position}`)}
                data-testid = {testId}>
                <div className = {cx(classes.backdrop, isMobile && 'is-mobile')} />
                <div className = {classes.mobileContainer}>
                    <FocusOn
                        className = {classes.focusLock}
                        onClickOutside = {onBackdropClick}
                        returnFocus = {

                            // If we return the focus to an element outside the viewport the page will scroll to
                            // this element which in our case is undesirable and the element is outside of the
                            // viewport on purpose (to be hidden). For example if we return the focus to the toolbox
                            // when it is hidden the whole page will move up in order to show the toolbox. This is
                            // usually followed up with displaying the toolbox (because now it is on focus) but
                            // because of the animation the whole scenario looks like jumping large video.
                            isElementInTheViewport
                        }>
                        <div
                            aria-description = {description}
                            aria-label = {title ?? t(titleKey ?? '')}
                            aria-modal = {true}
                            className = {cx(classes.modal, isUnmounting && 'unmount', size, className, position && `position-${position}`)}
                            data-autofocus = {true}
                            role = 'dialog'
                            tabIndex = {-1}>
                            {children}
                        </div>
                    </FocusOn>
                </div>
            </div>
        );
    }

    return (
        <div
            className = {cx(classes.container, isUnmounting && 'unmount', isMobile && 'is-mobile', position !== '' && `position-${position}`)}
            data-testid = {testId}>
            <div className = {cx(classes.backdrop, isMobile && 'is-mobile')} />
            <FocusOn
                className = {classes.focusLock}
                onClickOutside = {onBackdropClick}
                returnFocus = {

                    // If we return the focus to an element outside the viewport the page will scroll to
                    // this element which in our case is undesirable and the element is outside of the
                    // viewport on purpose (to be hidden). For example if we return the focus to the toolbox
                    // when it is hidden the whole page will move up in order to show the toolbox. This is
                    // usually followed up with displaying the toolbox (because now it is on focus) but
                    // because of the animation the whole scenario looks like jumping large video.
                    isElementInTheViewport
                }>
                <div
                    aria-description = {description}
                    aria-label = {title ?? t(titleKey ?? '')}
                    aria-modal = {true}
                    className = {cx(classes.modal, isUnmounting && 'unmount', size, className, position !== '' && `position-${position}`)}
                    data-autofocus = {true}
                    role = 'dialog'
                    tabIndex = {-1}>
                    {children}
                </div>
            </FocusOn>
        </div>
    );
};

export default BaseDialog;
