import React from "react";
import { makeStyles } from "tss-react/mui";

import OverlayFrame from "../OverlayFrame";
import EarthIcon from "./EarthIcon";

const useStyles = makeStyles()(() => {
    return {
        popup: {
            boxSizing: "border-box",
            position: "relative",
            marginTop: "50px",
            borderRadius: "8px",
            padding: "54px 32px 32px 32px",
            maxWidth: "300px",
            backgroundColor: "#1c1c1c",
        },
        iconWrapper: {
            position: "absolute",
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            borderRadius: "50%",
            width: "100px",
            height: "100px",
            top: "-50px",
            left: "50%",
            transform: "translateX(-50%)",
            backgroundColor: "#1c1c1c",
        },
        content: {
            display: "flex",
            flexDirection: "column",
            gap: "8px",
            textAlign: "center",
            color: "rgba(255, 255, 255, 0.75)",
        },
        title: {
            fontFamily: "Lato Regular",
            fontWeight: 600,
            fontSize: "15px",
        },
        description: {
            fontFamily: "Lato Regular",
            fontSize: "14px",
            lineHeight: "20px",
            letterSpacing: "-0.15px",
        },
    };
});

type NoConnectionPopupProps = {
    getLocalization: (key: string) => string;
};

const NoConnectionPopup = (props: NoConnectionPopupProps) => {
    const { getLocalization: t } = props;
    const { classes } = useStyles();

    return (
        <OverlayFrame isContentCentered={true} isLightOverlay={true}>
            <div className={classes.popup}>
                <div className={classes.iconWrapper}>
                    <EarthIcon />
                </div>
                <div className={classes.content}>
                    <div className={classes.title}>{t("dialog.conferenceNoConnection.title")}</div>
                    <div className={classes.description}>{t("dialog.conferenceNoConnection.description")}</div>
                </div>
            </div>
        </OverlayFrame>
    );
};

export default NoConnectionPopup;
