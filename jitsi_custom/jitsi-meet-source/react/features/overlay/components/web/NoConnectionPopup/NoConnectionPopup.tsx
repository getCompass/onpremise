import React from "react";
import { makeStyles } from "tss-react/mui";

import OverlayFrame from "../OverlayFrame";
import EarthIcon from "./EarthIcon";
import { isMobileBrowser } from "../../../../base/environment/utils";
import OverlayFrameMobile from "../OverlayFrameMobile";
import EarthIconMobile from "./EarthIconMobile";

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
        popupMobile: {
            position: "relative",
            borderRadius: "8px",
            border: "1px solid rgba(255, 255, 255, 0.05)",
            background: "linear-gradient(122.89deg, rgba(28, 28, 28, 0.8) -31.07%, rgba(28, 28, 28, 0.8) 6.08%, rgba(28, 28, 28, 0.8) 42.1%, rgba(28, 28, 28, 0.8) 89.18%, rgba(28, 28, 28, 0.8) 122.33%)",
            padding: "12px",
            display: "flex",
            flexDirection: "row",
            gap: "8px",
            marginBottom: "112px",
        },
        contentMobile: {
            display: "flex",
            flexDirection: "column",
            gap: 0,
        },
        titleMobile: {
            fontFamily: "Lato Bold",
            fontWeight: "normal" as const,
            fontSize: "14px",
            lineHeight: "20px",
            letterSpacing: "-0.16px",
            color: "rgba(255, 255, 255, 0.8)",
        },
        descriptionMobile: {
            fontFamily: "Lato SemiBold",
            fontWeight: "normal" as const,
            fontSize: "14px",
            lineHeight: "20px",
            letterSpacing: "-0.16px",
            color: "rgba(255, 255, 255, 0.7)",
        },
    };
});

type NoConnectionPopupProps = {
    getLocalization: (key: string) => string;
};

const NoConnectionPopup = (props: NoConnectionPopupProps) => {
    const { getLocalization: t } = props;
    const { classes } = useStyles();

    if (isMobileBrowser()) {
        return (
            <OverlayFrameMobile contentPosition = "bottom" isLightOverlay = {true}>
                <div className = {classes.popupMobile}>
                    <div style={{
                        flexShrink: 0,
                    }}>
                        <EarthIconMobile />
                    </div>
                    <div className = {classes.contentMobile}>
                        <div
                            className = {classes.titleMobile}>{t("dialog.conferenceNoConnection.title")}</div>
                        <div
                            className = {classes.descriptionMobile}>{t("dialog.conferenceNoConnection.description")}</div>
                    </div>
                </div>
            </OverlayFrameMobile>
        );
    }

    return (
        <OverlayFrame isContentCentered = {true} isLightOverlay = {true}>
            <div className = {classes.popup}>
                <div className = {classes.iconWrapper}>
                    <EarthIcon />
                </div>
                <div className = {classes.content}>
                    <div
                        className = {classes.title}>{t("dialog.conferenceNoConnection.title")}</div>
                    <div
                        className = {classes.description}>{t("dialog.conferenceNoConnection.description")}</div>
                </div>
            </div>
        </OverlayFrame>
    );
};

export default NoConnectionPopup;
