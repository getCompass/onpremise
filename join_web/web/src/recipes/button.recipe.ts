import {defineRecipe} from "@pandacss/dev"

export const button = defineRecipe({
    className: "button",
    base: {
        rounded: "8px",
        fontSize: "17px",
        lineHeight: "26px",
        outline: "none",
        userSelect: "none",
        fontFamily: "lato_semibold",
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        WebkitTapHighlightColor: "transparent",
        _enabled: {
            cursor: "pointer",
        },
        _disabled: {
            cursor: "default",
        },
    },
    defaultVariants: {
        size: "full",
        textSize: "xl",
        color: "007aff",
    },
    variants: {
        textSize: {
            "md": {fontSize: "16px", lineHeight: "22px", fontWeight: "400"},
            "xl": {fontSize: "17px", lineHeight: "26px", fontWeight: "500"},
            "md_desktop": {fontSize: "13px", lineHeight: "18px", fontWeight: "400"},
            "xl_desktop": {fontSize: "15px", lineHeight: "23px", fontWeight: "500"},
        },
        color: {
            "007aff": {
                bgColor: "007aff",
                color: "white",
                _enabled: {
                    opacity: "100%",
                    _active: {
                        bgColor: "007aff.hover",
                    },
                    ["@media (hover: hover) and (pointer: fine)"]: {
                        "&:hover": {
                            bgColor: "007aff.hover"
                        }
                    },
                },
                _disabled: {
                    opacity: "30%",
                },
            },
            "05c46b": {
                bgColor: "05c46b",
                color: "white",
                _enabled: {
                    _active: {
                        bgColor: "05c46b.hover",
                        color: "white",
                    },
                    ["@media (hover: hover) and (pointer: fine)"]: {
                        "&:hover": {
                            bgColor: "05c46b.hover",
                            color: "white",
                        }
                    },
                },
            },
            "2574a9": {
                color: "2574a9",
                _active: {
                    color: "2574a9.hover",
                },
                _enabled: {
                    ["@media (hover: hover) and (pointer: fine)"]: {
                        "&:hover": {
                            color: "2574a9.hover"
                        }
                    },
                },
                _disabled: {
                    opacity: "30%",
                },
            },
            "f5f5f5": {
                bgColor: "f5f5f5",
                color: "b4b4b4",
                _enabled: {
                    _active: {
                        bgColor: "f5f5f5.hover",
                        color: "b4b4b4.hover",
                    },
                    ["@media (hover: hover) and (pointer: fine)"]: {
                        "&:hover": {
                            bgColor: "f5f5f5.hover",
                            color: "b4b4b4.hover",
                        }
                    },
                },
            },
            "ff6a64": {
                bgColor: "ff6a64",
                color: "white",
                _enabled: {
                    _active: {
                        bgColor: "ff6a64.hover",
                    },
                    ["@media (hover: hover) and (pointer: fine)"]: {
                        "&:hover": {
                            bgColor: "ff6a64.hover"
                        }
                    },
                },
            },
        },
        size: {
            full: {w: "100%", px: "16px", py: "9px", minHeight: "44px"},
            full_desktop: {w: "100%", px: "16px", py: "6px", minHeight: "35px"},
            fullPy12: {w: "100%", px: "16px", py: "12px"},
            px16py6: {px: "16px", py: "6px"},
            px21py6: {px: "21px", py: "6px"},
            px0py8: {px: "0px", py: "8px"},
            px8py8: {px: "8px", py: "8px"},
            px16py9: {px: "16px", py: "9px"},
        },
    },
})
