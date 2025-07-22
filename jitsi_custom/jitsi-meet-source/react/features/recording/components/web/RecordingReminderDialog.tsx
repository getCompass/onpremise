import React from 'react';
import { useTranslation } from 'react-i18next';
import { useDispatch } from "react-redux";
import { makeStyles } from 'tss-react/mui';
import { hideDialog } from "../../../base/dialog/actions";
import Icon from "../../../base/icons/components/Icon";
import { IconCameraRecord } from '../../../base/icons/svg';
import Button from "../../../base/ui/components/web/Button";
import Dialog from '../../../base/ui/components/web/Dialog';
import { BUTTON_TYPES } from "../../../base/ui/constants.any";

const useStyles = makeStyles()(theme => {
    return {
        modalPadding: {
            padding: 0,
        },
        header: {
            display: 'none',
        },
        contentPadding: {
            padding: '32px 24px 24px 24px',
        },
        container: {
            display: "flex",
            flexDirection: "column",
            justifyContent: "center",
            alignItems: "center",
            gap: "26px",
        },
        content: {
            display: "flex",
            flexDirection: "column",
            justifyContent: "center",
            alignItems: "center",
            gap: "22px",
        },
        textContainer: {
            display: "flex",
            flexDirection: "column",
            gap: "16px",
            textAlign: "center",
            color: 'rgba(255, 255, 255, 0.7)',
            fontWeight: 'normal',
            fontSize: '15px',
            lineHeight: '22px',
            userSelect: 'none',
            '-webkit-tap-highlight-color': 'transparent',
        },
        title: {
            fontFamily: 'Lato Black',
        },
        description: {
            fontFamily: 'Lato Regular',
        },
        button: {
            width: "100%",
        },
    };
});

export default function RecordingReminderDialog() {
    const { t } = useTranslation();
    const { classes, cx } = useStyles();
    const dispatch = useDispatch();

    return (
        <Dialog
            className={classes.modalPadding}
            classNameContent={classes.contentPadding}
            cancel={{ hidden: true }}
            ok={{ hidden: true }}
            hideCloseButton={true}
            classNameHeader={classes.header}
            position="center"
        >
            <div className={classes.container}>
                <Icon
                    size={100}
                    src={IconCameraRecord}
                />
                <div className={classes.content}>
                    <div className={classes.textContainer}>
                        <div className={classes.title}>{t('recordReminder.title')}</div>
                        <div className={classes.description}>{t('recordReminder.description')}</div>
                    </div>
                    <Button
                        className={classes.button}
                        accessibilityLabel='recordReminder.button'
                        labelKey='recordReminder.button'
                        onClick={() => dispatch(hideDialog())}
                        type={BUTTON_TYPES.PRIMARY}
                    />
                </div>
            </div>
        </Dialog>
    );
}
