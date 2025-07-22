import React from 'react';
import { useTranslation } from 'react-i18next';
import { makeStyles } from 'tss-react/mui';
import Dialog from '../../../base/ui/components/web/Dialog';
import Icon from "../../../base/icons/components/Icon";
import { IconVisitorInfo, IconVisitorInfoMobile } from "../../../base/icons/svg";
import { BUTTON_TYPES } from "../../../base/ui/constants.any";
import Button from "../../../base/ui/components/web/Button";
import { hideDialog } from "../../../base/dialog/actions";
import { useDispatch } from "react-redux";
import { isMobileBrowser } from "../../../base/environment/utils";

const useStyles = makeStyles()(theme => {
    return {
        modalPadding: {
            padding: 0,
        },
        contentPadding: {
            padding: '32px 24px 24px 24px',
        },
        container: {
            display: "flex",
            flexDirection: "column",
            justifyContent: "center",
            alignItems: "center",
            gap: "24px",
        },
        textContainer: {
            display: "flex",
            flexDirection: "column",
            gap: "16px",
            textAlign: "center",
            color: 'rgba(255, 255, 255, 0.7)',
            fontWeight: 'normal' as const,
            fontSize: '15px',
            lineHeight: '22px',
            userSelect: 'none',
            '-webkit-tap-highlight-color': 'transparent',

            '&.is-mobile': {
                gap: "12px",
                fontSize: '16px',
            },
        },
        title: {
            fontFamily: 'Lato Black',

            '&.is-mobile': {
                marginTop: "4px",
            },
        },
        desc: {
            fontFamily: 'Lato Regular',
        },
        button: {
            width: "100%",
        },
    };
});

/**
 * Component that renders the join meeting dialog for visitors.
 *
 * @returns {JSX.Element}
 */
export default function JoinMeetingDialog() {
    const { t } = useTranslation();
    const { classes, cx } = useStyles();
    const dispatch = useDispatch();
    const isMobile = isMobileBrowser();

    return (
        <Dialog
            className = {classes.modalPadding}
            classNameContent = {classes.contentPadding}
            cancel = {{ hidden: true }}
            ok = {{ hidden: true }}
            hideCloseButton = {true}
            position = "center"
            size = {isMobile ? "mediumMobile" : "medium"}
        >
            <div className = {cx('visitor-join-meeting-dialog', classes.container)}>
                {!isMobile && (
                    <Icon
                        size = {100}
                        src = {IconVisitorInfo}
                    />
                )}
                <div className = {cx(classes.textContainer, isMobile && 'is-mobile')}>
                    {isMobile && (
                        <Icon
                            size = {120}
                            src = {IconVisitorInfoMobile}
                        />
                    )}
                    <div className = {cx(classes.title, isMobile && 'is-mobile')}>
                        {t('visitors.joinMeeting.title')}
                    </div>
                    <div className = {classes.desc}>
                        {t(`visitors.joinMeeting.description${isMobile ? "Mobile" : ""}`)}
                    </div>
                </div>
                <Button
                    className = {classes.button}
                    accessibilityLabel = 'visitors.joinMeeting.button'
                    labelKey = 'visitors.joinMeeting.button'
                    onClick = {() => dispatch(hideDialog())}
                    type = {BUTTON_TYPES.PRIMARY} />
            </div>
        </Dialog>
    );
}
