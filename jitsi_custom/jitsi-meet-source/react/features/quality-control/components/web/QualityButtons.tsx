import React, { useEffect, useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { isLocalParticipantModerator } from "../../../base/participants/functions";
import { COMMAND_QUALITY_LEVEL } from "../../constants";
import { setReducerQuality } from "../../actions";
import { IReduxState } from "../../../app/types";
import { makeStyles } from "tss-react/mui";
import { useTranslation } from "react-i18next";

const useStyles = makeStyles()(theme => {
    return {
        qualityButtonsContainer: {
            display: "flex",
            justifyContent: "center",
            gap: "9px",
        },
        qualityButtonItem: {
            fontFamily: 'Lato Regular',
            fontWeight: 'normal' as const,
            fontSize: '15px',
            lineHeight: '22px',
            color: 'rgba(255, 255, 255, 0.75)',
            padding: "3px 21px",
            backgroundColor: "rgba(255, 255, 255, 0.05)",
            border: "1px solid transparent",
            borderRadius: "5px",
            cursor: "pointer",
            flexGrow: 1,

            '&:hover': {
                color: 'rgba(255, 255, 255, 1)',
            }
        },

        qualityButtonItemActive: {
            cursor: "default",
            border: "1px solid rgba(255, 255, 255, 0.75)",
            backgroundColor: "transparent",

            '&:hover': {
                color: 'rgba(255, 255, 255, 0.75)',
            }
        }
    };
});

type QualityButtonProps = {
    quality: string,
    isActive: boolean,
    onClick: (e: React.MouseEvent) => void,
}

export function QualityButton({ quality, isActive, onClick }: QualityButtonProps) {
    const { cx, classes } = useStyles();

    return (
        <button
            className = {cx(classes.qualityButtonItem, isActive && classes.qualityButtonItemActive)}
            onClick = {onClick}
        >
            {quality}
        </button>
    );
}

interface IProps {
    className?: string;
}

export default function QualityButtons({ className }: IProps) {
    const { cx, classes } = useStyles();
    const { t } = useTranslation();
    const dispatch = useDispatch();
    const state = APP.store.getState();
    const qualityLevel = useSelector((state: IReduxState) => state['features/quality-control'].qualityLevel);
    const { conference } = state['features/base/conference'];
    const isModerator = useSelector(isLocalParticipantModerator);
    const [ activeQuality, setActiveQuality ] = useState(qualityLevel);

    useEffect(() => {
        setActiveQuality(qualityLevel)
    }, [ qualityLevel ]);

    if (!isModerator) {
        return null;
    }

    const handleQualityChange = (newQuality: string) => {
        if (newQuality !== activeQuality && conference !== undefined) {

            setActiveQuality(newQuality);

            // обновляем качество в prosody
            const messagePayload = {
                type: COMMAND_QUALITY_LEVEL,
                qualityLevel: newQuality
            };
            conference.sendMessage(JSON.stringify(messagePayload));

            // уведомляем всех, что сменилось качество
            try {
                conference.sendEndpointMessage('', {
                    type: COMMAND_QUALITY_LEVEL,
                    value: newQuality
                });
            } catch (Error) {
                // ошибка падает когда bridge не инициализирован, т.е в конференции кроме локального пользователя никого нет
                // в таком случае не падаем, т.к уведомлять некого, а идем дальше
                // когда новый пользователь подключится - ему с prosody придет актуальное качество
            }

            // обновляем локально
            dispatch(setReducerQuality(newQuality));
        }
    };

    return (
        <div className = {cx(classes.qualityButtonsContainer, className ?? '')}>
            <QualityButton
                quality = {t('moderatorSettings.highQuality')}
                isActive = {activeQuality === 'high'}
                onClick = {(e: React.MouseEvent) => {
                    e.stopPropagation();
                    handleQualityChange('high')
                }}
            />
            <QualityButton
                quality = {t('moderatorSettings.mediumQuality')}
                isActive = {activeQuality === 'medium'}
                onClick = {(e: React.MouseEvent) => {
                    e.stopPropagation();
                    handleQualityChange('medium')
                }}
            />
            <QualityButton
                quality = {t('moderatorSettings.lowQuality')}
                isActive = {activeQuality === 'low'}
                onClick = {(e: React.MouseEvent) => {
                    e.stopPropagation();
                    handleQualityChange('low')
                }}
            />
        </div>
    );
}
