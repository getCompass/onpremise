import { Theme } from '@mui/material';

const BACKGROUND_COLOR = 'rgba(33, 33, 33, 0.8)';

/**
 * Creates the styles for the component.
 *
 * @param {Object} theme - The current theme.
 * @returns {Object}
 */
export const styles = (theme: Theme) => {
    return {
        filmstripPaginationButton: {
            background: "rgba(33, 33, 33, 1)",
            borderRadius: "4px",
            padding: "10px 4px",
            cursor: "pointer",

            "&:hover": {
                background: "rgba(55, 55, 55, 1)",
            }
        },
        tileViewPaginationButtonsMobile: {
            display: "flex",
            width: "100%",
            gap: "4px",
        },
        tileViewPaginationButtonContainerMobile: {
            background: "rgba(33, 33, 33, 0.9)",
            padding: "10px 4px 11px 4px",
            color: "rgba(255, 255, 255, 1)",
            display: "flex",
            gap: "4px",
            flexDirection: "column",
            alignItems: "center",
            justifyContent: "center",
            flexGrow: 1,
            borderRadius: "4px",
            '-webkit-tap-highlight-color': 'transparent',
        },
        tileViewPaginationButtonContainerMobileEnabled: {
            "&:active": {
                background: "rgba(33, 33, 33, 0.3)",
            }
        },
        tileViewPaginationButtonPageMobile: {
            fontFamily: "Lato SemiBold",
            fontWeight: "normal" as const,
            fontSize: "12px",
            lineHeight: "15px",
            color: "rgba(255, 255, 255, 1)",
            minWidth: "36px",
            textAlign: "center"
        },
        tileViewPaginationButtonDisabledMobile: {
            opacity: "30%"
        },
        tileViewPaginationButtonContainer: {
            position: "absolute",
            zIndex: 1,
            background: "rgba(33, 33, 33, 0.9)",
            padding: "8px 4px",
            color: "rgba(255, 255, 255, 1)",
            display: "flex",
            gap: "4px",
            flexDirection: "column",
            alignItems: "center",
            justifyContent: "center",

            "&:hover": {
                background: "rgba(33, 33, 33, 1)",
            }
        },
        tileViewPaginationButtonContainerLeft: {
            borderRadius: "0px 8px 8px 0px",
            left: 0
        },
        tileViewPaginationButtonContainerRight: {
            borderRadius: "8px 0px 0px 8px",
            right: 0
        },
        tileViewPaginationButtonPage: {
            fontFamily: "Lato SemiBold",
            fontWeight: "normal" as const,
            fontSize: "12px",
            lineHeight: "15px",
            color: "rgba(255, 255, 255, 1)",
            minWidth: "36px",
            textAlign: "center"
        },
        tileViewPaginationButtonDisabled: {
            opacity: "20%"
        },

        toggleFilmstripContainer: {
            display: 'flex',
            flexWrap: 'nowrap' as const,
            alignItems: 'center',
            justifyContent: 'center',
            backgroundColor: BACKGROUND_COLOR,
            width: '32px',
            height: '24px',
            position: 'absolute' as const,
            borderRadius: '4px',
            top: 'calc(-24px - 2px)',
            left: 'calc(50% - 16px)',
            opacity: 0,
            transition: 'opacity .3s',
            zIndex: 1,

            '&:hover, &:focus-within': {
                backgroundColor: 'rgba(33, 33, 33, 0.8)'
            }
        },

        toggleFilmstripButton: {
            fontSize: '14px',
            lineHeight: 1.2,
            textAlign: 'center' as const,
            background: 'transparent',
            height: 'auto',
            width: '100%',
            padding: 0,
            margin: 0,
            border: 'none',

            '-webkit-appearance': 'none',

            '& svg': {
                fill: 'rgba(255, 255, 255, 1)'
            }
        },

        toggleVerticalFilmstripContainer: {
            transform: 'rotate(-90deg)',
            left: 'calc(-24px - 4px - 4px)',
            top: 'calc(50% - 12px)'
        },

        toggleTopPanelContainer: {
            transform: 'rotate(180deg)',
            bottom: 'calc(-24px - 6px)',
            top: 'auto'
        },

        toggleTopPanelContainerHidden: {
            visibility: 'hidden' as const
        },

        filmstrip: {
            background: 'rgba(23, 23, 23, 1)',
            right: 0,
            top: 0,

            '.horizontal-filmstrip &.hidden': {
                bottom: '-50px',

                '&:hover': {
                    backgroundColor: 'transparent'
                }
            },

            '&.hidden': {
                '& .toggleFilmstripContainer': {
                    opacity: 1
                }
            }
        },

        filmstripBackground: {
            backgroundColor: theme.palette.uiBackground,

            '&:hover, &:focus-within': {
                backgroundColor: theme.palette.uiBackground
            }
        },

        resizableFilmstripContainer: {
            display: 'flex',
            position: 'relative' as const,
            flexDirection: 'row' as const,
            alignItems: 'center',
            height: '100%',
            width: '100%',
            transition: 'background .2s ease-in-out',

            '& .avatar-container': {
                maxWidth: 'initial',
                maxHeight: 'initial'
            },

            '& .avatar-container-mobile': {
                maxWidth: 'initial',
                maxHeight: 'initial'
            },

            '&.top-panel-filmstrip': {
                flexDirection: 'column'
            }
        },

        dragHandleContainer: {
            height: '100%',
            width: '9px',
            backgroundColor: 'transparent',
            position: 'relative' as const,
            cursor: 'col-resize',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            visibility: 'hidden' as const,

            '&:hover': {
                '& .dragHandle': {
                    backgroundColor: theme.palette.icon01
                }
            },

            '&.visible': {
                visibility: 'visible',

                '& .dragHandle': {
                    backgroundColor: theme.palette.icon01
                }
            },

            '&.top-panel': {
                order: 2,
                width: '100%',
                height: '9px',
                cursor: 'row-resize',

                '& .dragHandle': {
                    height: '3px',
                    width: '100px'
                }
            }
        },

        dragHandle: {
            backgroundColor: theme.palette.icon02,
            height: '100px',
            width: '3px',
            borderRadius: '1px'
        }
    };
};
