import React, {useCallback, useEffect, useState} from 'react';
import {useTranslation} from 'react-i18next';
import {useDispatch, useSelector} from 'react-redux';
import {makeStyles} from 'tss-react/mui';

import {IReduxState} from '../../../app/types';
import {openDialog} from '../../../base/dialog/actions';
import {IconCloseLarge, IconDotsHorizontal} from '../../../base/icons/svg';
import {
    getParticipantById,
    isLocalParticipantModerator,
    isScreenShareParticipant
} from '../../../base/participants/functions';
import Button from '../../../base/ui/components/web/Button';
import ClickableIcon from '../../../base/ui/components/web/ClickableIcon';
import {BUTTON_TYPES} from '../../../base/ui/constants.web';
import {findAncestorByClass} from '../../../base/ui/functions.web';
import MuteEveryoneDialog from '../../../video-menu/components/web/MuteEveryoneDialog';
import {close} from '../../actions.web';
import {
    getParticipantsPaneOpen,
    getSortedParticipantIds,
    isMoreActionsVisible,
    isMuteAllVisible,
    shouldRenderInviteButton
} from '../../functions';

import {FooterContextMenu} from './FooterContextMenu';
import LobbyParticipants from './LobbyParticipants';
import MeetingParticipants from './MeetingParticipants';
import VisitorsList from './VisitorsList';
import {InviteButton} from "./InviteButton";
import {isButtonEnabled} from "../../../toolbox/functions.web";
import {isMobileBrowser} from "../../../base/environment/utils";
import {isDemoNode} from "../../../app/functions.web";
import DownloadMenu from "../../../base/compass/components/web/DownloadMenu";

const useStyles = makeStyles()(theme => {
    return {
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

        participantsPane: {
            backgroundColor: 'rgba(33, 33, 33, 1)',
            flexShrink: 0,
            overflow: 'hidden',
            position: 'relative',
            transition: 'width .16s ease-in-out',
            width: '316px',
            zIndex: 0,
            display: 'flex',
            flexDirection: 'column',
            fontWeight: 600,
            height: '100%',

            [['& > *:first-child', '& > *:last-child'] as any]: {
                flexShrink: 0
            },

            '@media (max-width: 580px)': {
                height: '95dvh',
                position: 'fixed',
                left: 0,
                right: 0,
                bottom: 0,
                width: '100%',
                borderRadius: '15px 15px 0 0',
                backgroundColor: 'rgba(28, 28, 28, 1)',
            }
        },

        participantsPaneDemo: {
            borderRadius: '4px 0 0 0',
        },

        demoConferenceContainer: {
            padding: '0 16px',
            marginTop: '2px',
            marginBottom: '8px',
        },

        demoConferenceContainerNoModerator: {
            marginBottom: '75px',
        },

        demoConferenceContent: {
            display: 'flex',
            flexDirection: 'column',
            alignItems: 'center',
            padding: '16px 20px 20px 20px',
            border: '1px solid rgba(151, 70, 255, 1)',
            borderRadius: '12px',
            background: 'linear-gradient(135.28deg, rgba(151, 70, 255, 0.08) 1.75%, rgba(151, 70, 255, 0.04) 48.16%, rgba(151, 70, 255, 0.11) 94.57%)',
        },

        demoConferenceIcon: {
            width: '89px',
            height: '73px',
        },

        demoConferenceTitle: {
            marginTop: '8px',
            fontFamily: 'Lato Bold',
            fontWeight: 'normal' as const,
            fontSize: '18px',
            lineHeight: '22px',
            color: 'rgba(255, 255, 255, 1)',
            textAlign: 'center',
        },

        demoConferenceDesc: {
            marginTop: '8px',
            marginBottom: '20px',
            fontFamily: 'Lato Regular',
            fontWeight: 'normal' as const,
            fontSize: '15px',
            lineHeight: '21px',
            color: 'rgba(255, 255, 255, 1)',
            textAlign: 'center',
        },

        demoConferenceButton: {
            width: '210px',
            textAlign: 'center',
            cursor: 'pointer',
            padding: '6px 16px',
            fontFamily: 'Lato SemiBold',
            fontWeight: 'normal' as const,
            fontSize: '15px',
            lineHeight: '23px',
            color: 'rgba(255, 255, 255, 1)',
            backgroundColor: 'rgba(151, 70, 255, 1)',
            borderRadius: '6px',
        },

        container: {
            boxSizing: 'border-box',
            flex: 1,
            overflowY: 'auto',
            position: 'relative',
            padding: '0 16px',

            '&::-webkit-scrollbar': {
                display: 'none'
            },

            '&.is-mobile': {
                padding: 0,
            }
        },

        closeButton: {
            alignItems: 'center',
            cursor: 'pointer',
            display: 'flex',
            justifyContent: 'center'
        },

        header: {
            alignItems: 'center',
            boxSizing: 'border-box',
            display: 'flex',
            paddingTop: '20px',
            paddingLeft: '20px',
            paddingRight: '16px',
            justifyContent: 'space-between',

            '&.is-mobile': {
                padding: '16px 16px 14px 16px',
            }
        },

        headerTitle: {
            fontSize: '16px',
            lineHeight: '24px',
            fontFamily: 'Lato Bold',
            letterSpacing: '-0.2px',

            '&.is-mobile': {
                fontSize: '20px',
                lineHeight: '28px',
                letterSpacing: '-0.3px',
            }
        },

        headerInviteButtonContainer: {
            marginTop: '14px',
            marginBottom: '19px',
            padding: '0px 16px'
        },

        antiCollapse: {
            fontSize: 0,

            '&:first-child': {
                display: 'none'
            },

            '&:first-child + *': {
                marginTop: 0
            }
        },

        footer: {
            display: 'flex',
            justifyContent: 'flex-end',
            padding: '16px',

            '& > *:not(:last-child)': {
                marginRight: '8px',

                '&.is-mobile': {
                    marginRight: '12px',
                }
            }
        },

        footerMoreContainer: {
            position: 'relative'
        }
    };
});

const ParticipantsPane = () => {
    const {classes, cx} = useStyles();
    const paneOpen = useSelector(getParticipantsPaneOpen);
    const showFooter = useSelector(isLocalParticipantModerator);
    const showMuteAllButton = useSelector(isMuteAllVisible);
    const showMoreActionsButton = useSelector(isMoreActionsVisible);
    const dispatch = useDispatch();
    const state = useSelector((state: IReduxState) => state);
    const {t} = useTranslation();
    let sortedParticipantIds: any = getSortedParticipantIds(state);
    sortedParticipantIds = sortedParticipantIds.filter((id: any) => {
        const participant = getParticipantById(state, id);

        return !isScreenShareParticipant(participant);
    });
    const isMobile = isMobileBrowser();
    const {is_in_picture_in_picture_mode} = useSelector((state: IReduxState) => state['features/picture-in-picture']);

    const participantsCount = sortedParticipantIds.length;
    const showInviteButton = shouldRenderInviteButton(state) && isButtonEnabled('invite', state);

    const [contextOpen, setContextOpen] = useState(false);
    const [searchString, setSearchString] = useState('');

    const onWindowClickListener = useCallback((e: any) => {
        if (contextOpen && !findAncestorByClass(e.target, classes.footerMoreContainer)) {
            setContextOpen(false);
        }
    }, [contextOpen]);

    useEffect(() => {
        window.addEventListener('click', onWindowClickListener);

        return () => {
            window.removeEventListener('click', onWindowClickListener);
        };
    }, []);

    const onClosePane = useCallback(() => {
        dispatch(close());
    }, []);

    const onDrawerClose = useCallback(() => {
        setContextOpen(false);
    }, []);

    const onMuteAll = useCallback(() => {
        dispatch(openDialog(MuteEveryoneDialog));
    }, []);

    const onToggleContext = useCallback(() => {
        setContextOpen(open => !open);
    }, []);

    if (!paneOpen || is_in_picture_in_picture_mode) {
        return null;
    }

    return (
        <>
            {isMobile && (<div className={cx(classes.backdrop, isMobile && 'is-mobile')}/>)}
            <div
                className={cx('participants_pane', classes.participantsPane, isDemoNode() && classes.participantsPaneDemo)}>
                <div className={cx(classes.header, isMobile && 'is-mobile')}>
                    <div className={cx(classes.headerTitle, isMobile && 'is-mobile')}>
                        {t('participantsPane.headings.participantsList', {count: participantsCount})}
                    </div>
                    <ClickableIcon
                        accessibilityLabel={t('participantsPane.close', 'Close')}
                        icon={IconCloseLarge}
                        onClick={onClosePane}/>
                </div>
                {!isMobile && (
                    <div className={classes.headerInviteButtonContainer}>
                        {showInviteButton && <InviteButton/>}
                    </div>
                )}
                <div className={cx(classes.container, isMobile && 'is-mobile')}>
                    <VisitorsList/>
                    <LobbyParticipants/>
                    <MeetingParticipants
                        searchString={searchString}
                        setSearchString={setSearchString}/>
                </div>
                {(isDemoNode() && !isMobile) && (
                    <div
                        className={cx(classes.demoConferenceContainer, !showFooter && classes.demoConferenceContainerNoModerator)}>
                        <div className={classes.demoConferenceContent}>
                            <div className={classes.demoConferenceIcon}>
                                <svg width="89" height="73" viewBox="0 0 89 73" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <g filter="url(#filter0_d_3839_27283)">
                                        <path
                                            d="M45.8149 42.9214C45.242 42.9214 44.9108 42.626 44.8213 42.0352C44.5348 40.0658 44.2126 38.4455 43.8545 37.1743C43.4964 35.9032 42.9683 34.9006 42.27 34.1665C41.5718 33.4146 40.596 32.8506 39.3428 32.4746C38.1074 32.0807 36.4603 31.7764 34.4014 31.5615C33.8105 31.472 33.5151 31.1587 33.5151 30.6216C33.5151 30.0487 33.8105 29.7174 34.4014 29.6279C36.0485 29.3952 37.4181 29.1445 38.5103 28.876C39.6024 28.5895 40.4976 28.2135 41.1958 27.748C41.894 27.2826 42.4491 26.6917 42.8608 25.9756C43.2905 25.2415 43.6486 24.3195 43.9351 23.2095C44.2215 22.0994 44.5169 20.7567 44.8213 19.1812C44.9466 18.6082 45.2778 18.3218 45.8149 18.3218C46.37 18.3218 46.6922 18.6172 46.7817 19.208C47.0145 20.7656 47.2651 22.0905 47.5337 23.1826C47.8022 24.2568 48.1514 25.161 48.5811 25.895C49.0286 26.6291 49.6105 27.2378 50.3267 27.7212C51.0428 28.1867 51.9559 28.5627 53.0659 28.8491C54.1938 29.1356 55.5724 29.3952 57.2017 29.6279C57.7925 29.7174 58.0879 30.0487 58.0879 30.6216C58.0879 31.1945 57.7925 31.5078 57.2017 31.5615C55.5545 31.7943 54.1849 32.0539 53.0928 32.3403C52.0007 32.6268 51.1055 33.0028 50.4072 33.4683C49.709 33.9338 49.145 34.5335 48.7153 35.2676C48.3035 36.0016 47.9544 36.9237 47.668 38.0337C47.3815 39.1437 47.0861 40.4865 46.7817 42.062C46.6564 42.6349 46.3341 42.9214 45.8149 42.9214ZM35.2339 47.2988C34.8579 47.2988 34.6162 47.0661 34.5088 46.6006C34.2402 45.3294 34.0164 44.4074 33.8374 43.8345C33.6763 43.2437 33.3182 42.8408 32.7632 42.626C32.2082 42.3932 31.2324 42.1694 29.8359 41.9546C29.3883 41.9009 29.1646 41.6502 29.1646 41.2026C29.1646 40.7729 29.3704 40.5312 29.7822 40.4775C31.1966 40.2448 32.1813 40.012 32.7363 39.7793C33.3092 39.5465 33.6763 39.1437 33.8374 38.5708C34.0164 37.98 34.2402 37.0579 34.5088 35.8047C34.6162 35.3392 34.8579 35.1064 35.2339 35.1064C35.6457 35.1064 35.8963 35.3392 35.9858 35.8047C36.2365 37.0579 36.4513 37.98 36.6304 38.5708C36.8094 39.1437 37.1764 39.5465 37.7314 39.7793C38.3044 40.012 39.307 40.2448 40.7393 40.4775C41.151 40.5312 41.3569 40.7729 41.3569 41.2026C41.3569 41.6323 41.151 41.883 40.7393 41.9546C39.307 42.1694 38.3044 42.3932 37.7314 42.626C37.1764 42.8408 36.8094 43.2437 36.6304 43.8345C36.4513 44.4253 36.2365 45.3742 35.9858 46.6812C35.8963 47.0929 35.6457 47.2988 35.2339 47.2988ZM17.8315 54.3618C15.6831 54.3618 13.9912 53.7531 12.7559 52.5356C11.5384 51.3003 10.9297 49.6084 10.9297 47.46V16.6567C10.9297 14.5083 11.5384 12.8254 12.7559 11.6079C13.9912 10.3726 15.6831 9.75488 17.8315 9.75488H71.1665C73.3149 9.75488 74.9979 10.3726 76.2153 11.6079C77.4507 12.8254 78.0684 14.5083 78.0684 16.6567V47.46C78.0684 49.6084 77.4507 51.3003 76.2153 52.5356C74.9979 53.7531 73.3149 54.3618 71.1665 54.3618H17.8315ZM17.9121 50.0381H71.0859C71.9274 50.0381 72.5809 49.8053 73.0464 49.3398C73.5119 48.8743 73.7446 48.2298 73.7446 47.4062V16.7104C73.7446 15.8869 73.5119 15.2424 73.0464 14.7769C72.5809 14.3114 71.9274 14.0786 71.0859 14.0786H17.9121C17.0706 14.0786 16.4172 14.3114 15.9517 14.7769C15.4862 15.2424 15.2534 15.8869 15.2534 16.7104V47.4062C15.2534 48.2298 15.4862 48.8743 15.9517 49.3398C16.4172 49.8053 17.0706 50.0381 17.9121 50.0381ZM30.9907 63.4658C30.3999 63.4658 29.8896 63.251 29.46 62.8213C29.0303 62.3916 28.8154 61.8813 28.8154 61.2905C28.8154 60.6818 29.0303 60.1626 29.46 59.7329C29.8896 59.3211 30.3999 59.1152 30.9907 59.1152H58.0073C58.5981 59.1152 59.1084 59.3211 59.5381 59.7329C59.9678 60.1626 60.1826 60.6818 60.1826 61.2905C60.1826 61.8813 59.9678 62.3916 59.5381 62.8213C59.1084 63.251 58.5981 63.4658 58.0073 63.4658H30.9907Z"
                                            fill="#9746FF"/>
                                    </g>
                                    <defs>
                                        <filter id="filter0_d_3839_27283" x="2.92969" y="1.75488" width="83.1387"
                                                height="69.7109" filterUnits="userSpaceOnUse"
                                                colorInterpolationFilters="sRGB">
                                            <feFlood floodOpacity="0" result="BackgroundImageFix"/>
                                            <feColorMatrix in="SourceAlpha" type="matrix"
                                                           values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"
                                                           result="hardAlpha"/>
                                            <feOffset/>
                                            <feGaussianBlur stdDeviation="4"/>
                                            <feComposite in2="hardAlpha" operator="out"/>
                                            <feColorMatrix type="matrix"
                                                           values="0 0 0 0 0.592157 0 0 0 0 0.27451 0 0 0 0 1 0 0 0 0.4 0"/>
                                            <feBlend mode="normal" in2="BackgroundImageFix"
                                                     result="effect1_dropShadow_3839_27283"/>
                                            <feBlend mode="normal" in="SourceGraphic"
                                                     in2="effect1_dropShadow_3839_27283" result="shape"/>
                                        </filter>
                                    </defs>
                                </svg>
                            </div>
                            <div className={classes.demoConferenceTitle}>Вы используете демо-конференцию Compass</div>
                            <div className={classes.demoConferenceDesc}>Скачайте приложение Compass для общения без
                                ограничений.
                            </div>
                            <DownloadMenu
                                triggerEl={
                                    <div className={classes.demoConferenceButton}>Скачать Compass</div>
                                }
                                position="top-mid"
                            />
                        </div>
                    </div>
                )}
                {showFooter && (
                    <div className={cx(classes.footer, isMobile && 'is-mobile')}>
                        {isMobile ? (
                            showInviteButton && <InviteButton/>
                        ) : (
                            showMuteAllButton && (
                                <Button
                                    accessibilityLabel={t('participantsPane.actions.muteAll')}
                                    labelKey={'participantsPane.actions.muteAll'}
                                    onClick={onMuteAll}
                                    type={BUTTON_TYPES.SECONDARY}
                                    fullWidth={true}/>
                            )
                        )}
                        {showMoreActionsButton && (
                            <div className={classes.footerMoreContainer}>
                                <Button
                                    accessibilityLabel={t('participantsPane.actions.moreModerationActions')}
                                    icon={IconDotsHorizontal}
                                    id='participants-pane-context-menu'
                                    onClick={onToggleContext}
                                    type={BUTTON_TYPES.SECONDARY}/>
                                <FooterContextMenu
                                    isOpen={contextOpen}
                                    onDrawerClose={onDrawerClose}
                                    onMouseLeave={onToggleContext}/>
                            </div>
                        )}
                    </div>
                )}
            </div>
        </>
    );
};


export default ParticipantsPane;
