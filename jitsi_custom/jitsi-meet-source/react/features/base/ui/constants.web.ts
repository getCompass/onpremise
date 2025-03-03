import { Theme } from '@mui/material';

export * from './constants.any';

/**
 * Returns an object containing the declaration of the common, reusable CSS classes.
 *
 * @param {Object} theme -The theme.
 *
 * @returns {Object} - The common styles.
 */
export const commonStyles = (theme: Theme) => {
    return {
        '.empty-list': {
            listStyleType: 'none',
            margin: 0,
            padding: 0
        },

        '.mute-dialog': {
            '& .separator-line': {
                margin: `${theme.spacing(4)} 0 ${theme.spacing(4)} -20px`,
                padding: '0 20px',
                width: '100%',
                height: '1px',
                background: '#5E6D7A'
            },

            '& .control-row': {
                display: 'flex',
                justifyContent: 'space-between',
                marginTop: theme.spacing(3),

                '& label': {
                    fontSize: '14px'
                }
            }
        },

        '.overflow-menu-item': {
            alignItems: 'center',
            color: theme.palette.text01,
            cursor: 'pointer',
            display: 'flex',
            fontSize: 14,
            fontWeight: 400,
            height: 40,
            lineHeight: '24px',
            padding: '8px 16px',
            boxSizing: 'border-box' as const,
            '& > div': {
                display: 'flex',
                alignItems: 'center'
            },

            '&.unclickable': {
                cursor: 'default'
            },

            '&.disabled': {
                cursor: 'initial',
                color: theme.palette.text03,

                '&:hover': {
                    background: 'none'
                },

                '& svg': {
                    fill: theme.palette.text03
                }
            },

            '@media (hover: hover) and (pointer: fine)': {
                '&:hover': {
                    background: theme.palette.action02Hover
                },
                '&.unclickable:hover': {
                    background: 'inherit'
                }
            }
        },

        '.overflow-menu-item-icon': {
            marginRight: '16px',

            '& i': {
                display: 'inline',
                fontSize: 24
            },

            '@media (hover: hover) and (pointer: fine)': {
                '&i:hover': {
                    backgroundColor: 'initial'
                }
            },

            '& img': {
                maxWidth: 24,
                maxHeight: 24
            },

            '& svg': {
                fill: theme.palette.text01,
                height: 20,
                width: 20
            }
        },

        '.prejoin-dialog': {
            backgroundColor: theme.palette.uiBackground,
            boxShadow: '0px 2px 20px rgba(0, 0, 0, 0.5)',
            borderRadius: theme.shape.borderRadius,
            color: '#fff',
            height: '400px',
            width: '375px',

            '.prejoin-dialog--small': {
                height: 300,
                width: 400
            },

            '.prejoin-dialog-label': {
                fontSize: '15px',
                lineHeight: '24px'
            },

            '.prejoin-dialog-label-num': {
                background: '#2b3b4b',
                border: '1px solid #A4B8D1',
                borderRadius: '50%',
                color: '#fff',
                display: 'inline-block',
                height: '24px',
                marginRight: theme.spacing(2),
                width: '24px'
            },

            '.prejoin-dialog-container': {
                alignItems: 'center',
                background: 'rgba(0,0,0,0.6)',
                display: 'flex',
                height: '100dvh',
                justifyContent: 'center',
                left: 0,
                position: 'absolute' as const,
                top: 0,
                width: '100vw',
                zIndex: 3
            },

            '.prejoin-dialog-flag': {
                display: 'inline-block',
                marginRight: theme.spacing(2),
                transform: 'scale(1.2)'
            },

            '.prejoin-dialog-title': {
                display: 'inline-block',
                fontSize: '24px',
                lineHeight: '32px'
            },

            '.prejoin-dialog-icon': {
                cursor: 'pointer'
            },

            '.prejoin-dialog-btn': {
                marginBottom: '8px'
            },

            '.prejoin-dialog-dialin-container': {
                textAlign: 'center' as const
            },

            '.prejoin-dialog-delimiter': {
                background: theme.palette.ui03,
                border: '0',
                height: '1px',
                margin: '0',
                padding: '0',
                width: '100%'
            },

            '.prejoin-dialog-delimiter-container': {
                margin: `${theme.spacing(4)} 0`,
                position: 'relative' as const
            },

            '.prejoin-dialog-delimiter-txt-container': {
                position: 'absolute' as const,
                textAlign: 'center' as const,
                top: '-8px',
                width: '100%'
            },

            '.prejoin-dialog-delimiter-txt': {
                background: theme.palette.uiBackground,
                color: theme.palette.text01,
                fontSize: '11px',
                textTransform: 'uppercase' as const,
                padding: `0 ${theme.spacing(2)}`
            }
        },

        '.prejoin-dialog-btn': {
            '&.primary, &.prejoin-dialog-btn.text': {
                width: '310px'
            }
        },

        '.toolbox-icon': {
            display: 'flex',
            borderRadius: '8px',
            flexDirection: 'column' as const,
            fontSize: 24,
            height: 48,
            justifyContent: 'center',
            width: 48,
            opacity: '60%',

            '&.is-mobile': {
                borderRadius: '100% !important',
                height: 64,
                width: 64,
                opacity: '100%',
                backgroundColor: 'rgba(4, 164, 90, 0.5)',

                '&.mobile-header-button': {
                    backgroundColor: 'rgba(0, 0, 0, 0.2)',

                    '& .jitsi-icon': {
                        '& svg': {
                            fill: 'rgba(255, 255, 255, 0.85) !important'
                        }
                    }
                },

                '&:active': {
                    backgroundColor: 'rgba(4, 164, 90, 0.65)',

                    '&.mobile-header-button': {
                        backgroundColor: 'rgba(255, 255, 255, 0.1)',
                    },
                },

                '&.hangup-button': {
                    backgroundColor: 'rgba(255, 59, 48, 0.5)',
                },

                '&.hangup-button-mobile': {
                    backgroundColor: 'rgba(255, 59, 48, 0.5)',

                    '&:active': {
                        backgroundColor: 'rgba(255, 59, 48, 0.65)',
                    },
                },

                '&.toggled': {
                    backgroundColor: 'rgba(255, 255, 255, 0.1)',

                    '&:active': {
                        backgroundColor: 'rgba(255, 255, 255, 0.25)',
                    },

                    '&.hangup-button': {
                        backgroundColor: 'rgba(255, 59, 48, 0.5)',
                    },

                    '&.hangup-button-mobile': {
                        backgroundColor: 'rgba(255, 59, 48, 0.5)',

                        '&:active': {
                            backgroundColor: 'rgba(255, 59, 48, 0.65)',
                        },
                    },
                },

                '&.microphone-button': {},
            },

            '@media (hover: hover) and (pointer: fine)': {
                '&:hover': {
                    opacity: '100%',
                },

                '&:active': {
                    opacity: '100%',
                }
            },
            [theme.breakpoints.down(320)]: {
                height: 36,
                width: 36
            },

            '&.mobile-header-button': {
                opacity: 1,
                width: '36px',
                height: '36px',
                marginTop: 0,
                '-webkit-tap-highlight-color': 'transparent',
            },

            '&.toggled': {
                backgroundColor: 'transparent',
                opacity: '60%',

                '@media (hover: hover) and (pointer: fine)': {
                    '&:hover': {
                        opacity: '100%',
                    },
                },

                '&.mobile-header-button': {
                    backgroundColor: 'transparent',

                    '@media (hover: hover) and (pointer: fine)': {
                        '&:hover': {
                            backgroundColor: 'transparent',
                        },
                    },
                },

                '&.is-mobile': {
                    opacity: '100%',

                    '&.mobile-header-button': {
                        backgroundColor: 'rgba(0, 0, 0, 0.2)',

                        '& .jitsi-icon': {
                            '& svg': {
                                fill: 'rgba(255, 255, 255, 0.85) !important'
                            }
                        },

                        '@media (hover: hover) and (pointer: fine)': {
                            '&:active': {
                                backgroundColor: 'rgba(255, 255, 255, 0.1)',
                            },
                        },
                    },

                    '@media (hover: hover) and (pointer: fine)': {
                        '&:hover': {
                            backgroundColor: 'transparent',
                        },
                    },
                    '&.microphone-button': {
                        backgroundColor: 'rgba(255, 255, 255, 0.8) !important',

                        '& svg': {
                            fill: 'rgba(255, 79, 71, 1) !important'
                        }
                    },
                },
            },

            '&.disabled': {
                cursor: 'initial !important',
                backgroundColor: 'transparent !important',
                opacity: '30%',

                '@media (hover: hover) and (pointer: fine)': {
                    '&:hover': {
                        opacity: '30%',
                    },
                }
            }
        },

        '.toolbox-button': {
            color: theme.palette.text01,
            cursor: 'pointer',
            display: 'inline-block',
            lineHeight: '48px',
            textAlign: 'center' as const,
            '-webkit-tap-highlight-color': 'transparent' as const,
        },

        '.toolbox-content-items': {
            background: 'rgba(33, 33, 33, 0.9)',
            borderRadius: 6,
            margin: '0 auto',
            padding: 6,
            textAlign: 'center' as const,
            pointerEvents: 'all' as const,
            display: 'flex',

            '& > div': {
                marginRight: theme.spacing(2),

                '&:last-of-type': {
                    marginRight: 0
                }
            }
        },

        '.compass-toolbox-content-items': {
            borderRadius: 6,
            padding: 8,
            textAlign: 'center' as const,
            pointerEvents: 'all' as const,
            display: 'flex',

            '& > div': {
                marginRight: theme.spacing(2),

                '&:last-of-type': {
                    marginRight: 0
                }
            },

            '&.compass-toolbox-content-items-mobile': {
                padding: 0,
                justifyContent: 'center',

                '& > div': {
                    marginRight: '32px',

                    '&:last-of-type': {
                        marginRight: 0
                    }
                },
            },
        }
    };
};
