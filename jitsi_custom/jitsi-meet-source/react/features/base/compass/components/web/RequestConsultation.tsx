import React, {useCallback, useMemo, useState} from 'react';
import {makeStyles} from 'tss-react/mui';
import BaseDialog from "../../../ui/components/web/BaseDialog";
import {DialogProps} from "../../../dialog/constants";
import {hideDialog} from "../../../dialog/actions";
import {useDispatch} from "react-redux";
import {formatPhoneNumberIntl, isPossiblePhoneNumber, isValidPhoneNumber} from "react-phone-number-input";
import {useApiWwwGetCompassRequestConsultation} from "../../../../../api/www";

interface IProps extends DialogProps {
}

const useStyles = makeStyles()(() => {
    return {
        dialogContainer: {
            padding: 0,
            width: '818px',
            backgroundColor: "#141414",
            overflow: "hidden",
        },

        successDialogContainer: {
            padding: 0,
            width: '418px',
            backgroundColor: "#141414",
            overflow: "hidden",
        },

        dialogCloseButton: {
            width: "32px",
            height: "32px",
            borderRadius: "100px",
            flexShrink: 0,
            cursor: "pointer",
            position: "absolute",
            top: "24px",
            right: "24px",
        },

        fontInter_14_21_400: {
            fontFamily: 'Inter Regular',
            fontWeight: 'normal' as const,
            fontSize: '14px',
            lineHeight: '21px',
        },

        fontInter_18_25_400: {
            fontFamily: 'Inter Regular',
            fontWeight: 'normal' as const,
            fontSize: '18px',
            lineHeight: '25px',
        },

        fontInter_20_24_500: {
            fontFamily: 'Inter Medium',
            fontWeight: 'normal' as const,
            fontSize: '20px',
            lineHeight: '24px',
        },

        fontInter_30_36_700: {
            fontFamily: 'Inter Bold',
            fontWeight: 'normal' as const,
            fontSize: '30px',
            lineHeight: '36px',
        },

        color_255255255_02: {
            color: "rgba(255, 255, 255, 0.2)"
        },

        color_255255255_07: {
            color: "rgba(255, 255, 255, 0.7)"
        },

        color_255255255_085: {
            color: "rgba(255, 255, 255, 0.85)"
        },

        color_white: {
            color: "white"
        },

        leftContainer: {
            width: "336px", // width - paddingRight - paddingLeft
            height: "100%",
            padding: "32px 32px 40px 32px",
            gap: "32px",
            alignItems: "start"
        },

        avatar: {
            width: "79px",
            height: "79px",
            backgroundPosition: "center",
            backgroundSize: "cover",
            backgroundRepeat: "no-repeat",
            rounded: "100px",
            flexShrink: 0,
            cursor: "default",
        },

        rightContainer: {
            alignItems: "start",
            width: "370px", // width - paddingRight - paddingLeft
            height: "100%",
            padding: "32px 24px 24px 24px",
            background: "linear-gradient(147.37deg, rgba(45, 47, 63, 0.3) -6.32%, rgba(20, 20, 20, 0.3) 77.49%, rgba(45, 47, 63, 0.3) 101.82%);",
        },

        input: {
            appearance: "none",
            position: "relative",
            transitionDuration: "normal",
            transitionProperty: "box-shadow, border-color",
            transitionTimingFunction: "default",
            width: "100%",
            padding: "12px 15px",
            backgroundColor: "rgba(255, 255, 255, 0.02)",
            borderRadius: "12px",
            outline: "none",
            fontSize: "18px",
            lineHeight: "27px",
            fontFamily: "inter_regular",
            fontWeight: "normal",
            color: "rgba(255, 255, 255, 0.85)",
            "&::placeholder": {
                color: 'rgba(255, 255, 255, 0.3)',
            },
        },

        button: {
            width: "338px", // width - paddingRight - paddingLeft
            padding: "16px",
            borderRadius: "12px",
            backgroundColor: "#9746ff",
            color: "white",
            opacity: "100%",
            outline: "none",
            userSelect: "none",
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            WebkitTapHighlightColor: "transparent",
            '&:not(:disabled)': {
                cursor: "pointer",
                '&:hover': {
                    transition: "background-color .2s linear",
                    backgroundColor: "#811fff"
                },
                ["@media (hover: hover) and (pointer: fine)"]: {
                    "&:hover": {
                        transition: "background-color .2s linear",
                        backgroundColor: "#811fff",
                    },
                },
            },
        },

        buttonDisabled: {
            cursor: "default",
        },

        hyperLink: {
            color: "#008ac7",
            transition: "color .3s",
            '&:hover': {
                color: "#1d5c86",
                textDecoration: "none",
            },
        },

        hyperLinkHover: {
            color: "#1d5c86",
            textDecoration: "none",
        },

        footerHyperLink: {
            textDecoration: "underline",
            transition: "color .3s",
            '&:hover': {
                color: "rgba(255, 255, 255, 0.5)",
            },
        },
    };
});

export default function RequestConsultationDialog({}: IProps): JSX.Element {

    const {classes, cx} = useStyles();
    const dispatch = useDispatch();

    const [nameValue, setNameValue] = useState("");
    const [phoneNumberValue, setPhoneNumberValue] = useState("");
    const [isNameError, setIsNameError] = useState(false);
    const [isPhoneNumberError, setIsPhoneNumberError] = useState(false);
    const [isTelegramHovered, setIsTelegramHovered] = useState(false);
    const [isWhatsappHovered, setIsWhatsappHovered] = useState(false);
    const [isSuccessSend, setIsSuccessSend] = useState(false);

    const apiWwwGetCompassRequestConsultation = useApiWwwGetCompassRequestConsultation();

    const onClickHandler = useCallback(async () => {
        if (nameValue.length < 1) {
            setIsNameError(true);
            return;
        }

        if (phoneNumberValue.length < 5) {
            setIsPhoneNumberError(true);
            return;
        }

        try {
            await apiWwwGetCompassRequestConsultation.mutateAsync({
                name: nameValue,
                phone_number: phoneNumberValue,
            });
            setIsSuccessSend(true);
        } catch (error) {
        }
    }, [nameValue, phoneNumberValue]);

    const renderedContent = () => {

        if (isSuccessSend) {

            return (
                <div className="vstack" style={{
                    padding: "48px 24px 24px 24px",
                    justifyContent: "center",
                    alignItems: "center",
                    position: "relative",
                }}
                >
                    <div className={classes.dialogCloseButton}
                         onClick={() => dispatch(hideDialog(RequestConsultationDialog))}>
                        <svg
                            className="dialog_close_button"
                            width="32"
                            height="32"
                            viewBox="0 0 32 32"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <g opacity="0.4">
                                <path
                                    fillRule="evenodd"
                                    clipRule="evenodd"
                                    d="M16 32C24.8366 32 32 24.8366 32 16C32 7.16344 24.8366 0 16 0C7.16344 0 0 7.16344 0 16C0 24.8366 7.16344 32 16 32ZM18.0218 15.9994L21.8678 19.8454C22.4124 20.39 22.4124 21.3232 21.8678 21.8678C21.3232 22.4124 20.39 22.4124 19.8454 21.8678L15.9994 18.0219L12.1535 21.8678C11.6089 22.4124 10.6757 22.4124 10.1311 21.8678C9.58651 21.3232 9.58651 20.39 10.1311 19.8454L13.977 15.9994L10.1311 12.1535C9.58651 11.6089 9.58651 10.6757 10.1311 10.1311C10.6757 9.58651 11.6089 9.58651 12.1535 10.1311L15.9994 13.977L19.8454 10.1311C20.39 9.58651 21.3232 9.58651 21.8678 10.1311C22.4124 10.6757 22.4124 11.6089 21.8678 12.1535L18.0218 15.9994ZM17.2137 15.9994L21.4637 11.7494C21.7852 11.428 21.7852 10.8566 21.4637 10.5352C21.1679 10.2393 20.6602 10.2158 20.3307 10.4645C20.6602 10.2167 21.1671 10.2405 21.4626 10.536C21.784 10.8574 21.784 11.4289 21.4626 11.7503L17.2126 16.0003L21.4626 20.2503C21.784 20.5717 21.784 21.1431 21.4626 21.4646C21.437 21.4901 21.4099 21.5137 21.3814 21.5352C21.4103 21.5135 21.4378 21.4896 21.4637 21.4637C21.7852 21.1423 21.7852 20.5709 21.4637 20.2494L17.2137 15.9994ZM15.9983 17.2146L15.9985 17.2147L11.7494 21.4637C11.7235 21.4896 11.696 21.5135 11.6671 21.5352C11.6956 21.5137 11.7228 21.4901 11.7483 21.4646L15.9983 17.2146ZM11.7483 10.536C11.4531 10.2408 10.9471 10.2167 10.6175 10.4637C10.9471 10.2158 11.4539 10.2396 11.7494 10.5352L15.9993 14.785L15.9983 14.786L11.7483 10.536Z"
                                />
                            </g>
                        </svg>
                    </div>
                    <div
                        style={{
                            width: "80px",
                            height: "96px",
                            backgroundPosition: "center",
                            backgroundSize: "contain",
                            backgroundRepeat: "no-repeat",
                            flexShrink: 0,
                            cursor: "default",
                            backgroundImage: `url(images/desktop/RocketIcon.png)`,
                        }}
                    />
                    <div className={cx(classes.fontInter_30_36_700, classes.color_255255255_085)}
                         style={{
                             width: "100%",
                             marginTop: "12px",
                             textAlign: "center"
                         }}
                    >
                        {"Заявка\nотправлена".split("\n").map((line, index) => (
                            <div key={index}>{line}</div>
                        ))}
                    </div>
                    <div className={cx(classes.fontInter_18_25_400, classes.color_255255255_07)}
                         style={{
                             marginTop: "16px",
                             textAlign: "center"
                         }}
                    >
                        {"Свяжемся с вами в будние дни с 10:00 до 19:00 (мск) и проведём консультацию."}
                    </div>
                    <div
                        className={classes.button}
                        onClick={() => dispatch(hideDialog(RequestConsultationDialog))}
                        style={{
                            marginTop: "32px",
                        }}
                    >
                        <div className={cx(classes.fontInter_20_24_500, classes.color_white)}>
                            {"Хорошо"}
                        </div>
                    </div>
                </div>
            );
        }

        return (
            <div className="hstack" style={{
                position: "relative",
            }}
            >
                <div className={classes.dialogCloseButton}
                     onClick={() => dispatch(hideDialog(RequestConsultationDialog))}>
                    <svg
                        className="dialog_close_button"
                        width="32"
                        height="32"
                        viewBox="0 0 32 32"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <g opacity="0.4">
                            <path
                                fillRule="evenodd"
                                clipRule="evenodd"
                                d="M16 32C24.8366 32 32 24.8366 32 16C32 7.16344 24.8366 0 16 0C7.16344 0 0 7.16344 0 16C0 24.8366 7.16344 32 16 32ZM18.0218 15.9994L21.8678 19.8454C22.4124 20.39 22.4124 21.3232 21.8678 21.8678C21.3232 22.4124 20.39 22.4124 19.8454 21.8678L15.9994 18.0219L12.1535 21.8678C11.6089 22.4124 10.6757 22.4124 10.1311 21.8678C9.58651 21.3232 9.58651 20.39 10.1311 19.8454L13.977 15.9994L10.1311 12.1535C9.58651 11.6089 9.58651 10.6757 10.1311 10.1311C10.6757 9.58651 11.6089 9.58651 12.1535 10.1311L15.9994 13.977L19.8454 10.1311C20.39 9.58651 21.3232 9.58651 21.8678 10.1311C22.4124 10.6757 22.4124 11.6089 21.8678 12.1535L18.0218 15.9994ZM17.2137 15.9994L21.4637 11.7494C21.7852 11.428 21.7852 10.8566 21.4637 10.5352C21.1679 10.2393 20.6602 10.2158 20.3307 10.4645C20.6602 10.2167 21.1671 10.2405 21.4626 10.536C21.784 10.8574 21.784 11.4289 21.4626 11.7503L17.2126 16.0003L21.4626 20.2503C21.784 20.5717 21.784 21.1431 21.4626 21.4646C21.437 21.4901 21.4099 21.5137 21.3814 21.5352C21.4103 21.5135 21.4378 21.4896 21.4637 21.4637C21.7852 21.1423 21.7852 20.5709 21.4637 20.2494L17.2137 15.9994ZM15.9983 17.2146L15.9985 17.2147L11.7494 21.4637C11.7235 21.4896 11.696 21.5135 11.6671 21.5352C11.6956 21.5137 11.7228 21.4901 11.7483 21.4646L15.9983 17.2146ZM11.7483 10.536C11.4531 10.2408 10.9471 10.2167 10.6175 10.4637C10.9471 10.2158 11.4539 10.2396 11.7494 10.5352L15.9993 14.785L15.9983 14.786L11.7483 10.536Z"
                            />
                        </g>
                    </svg>
                </div>
                <div className={cx(classes.leftContainer, "vstack")}>
                    <div className="hstack">
                        <div
                            className={classes.avatar}
                            style={{
                                backgroundImage: `url(images/desktop/Avatar1.png)`,
                            }}
                        />
                        <div
                            className={classes.avatar}
                            style={{
                                backgroundImage: `url(images/desktop/Avatar2.png)`,
                                marginLeft: "-27px"
                            }}
                        />
                        <div
                            className={classes.avatar}
                            style={{
                                backgroundImage: `url(images/desktop/Avatar3.png)`,
                                marginLeft: "-27px"
                            }}
                        />
                    </div>
                    <div className="vstack" style={{
                        gap: "16px",
                    }}>
                        <div className="hstack"
                             style={{
                                 gap: "12px",
                                 alignItems: "start",
                             }}>
                            <div
                                style={{
                                    backgroundImage: `url(images/desktop/Arrow.svg)`,
                                    width: "26px",
                                    height: "22px",
                                    backgroundPosition: "center",
                                    backgroundSize: "cover",
                                    backgroundRepeat: "no-repeat",
                                    borderRadius: "100px",
                                    flexShrink: "0",
                                    cursor: "default",
                                }}
                            />
                            <div className={cx(classes.fontInter_18_25_400, classes.color_255255255_07)}>
                                {"Познакомим с функционалом Compass и покажем его в деле"}
                            </div>
                        </div>
                        <div className="hstack"
                             style={{
                                 gap: "12px",
                                 alignItems: "start",
                             }}>
                            <div
                                style={{
                                    backgroundImage: `url(images/desktop/Arrow.svg)`,
                                    width: "26px",
                                    height: "22px",
                                    backgroundPosition: "center",
                                    backgroundSize: "cover",
                                    backgroundRepeat: "no-repeat",
                                    borderRadius: "100px",
                                    flexShrink: "0",
                                    cursor: "default",
                                }}
                            />
                            <div className={cx(classes.fontInter_18_25_400, classes.color_255255255_07)}>
                                {"Расскажем как организовать эффективные процессы в мессенджере Compass"}
                            </div>
                        </div>
                        <div className="hstack"
                             style={{
                                 gap: "12px",
                                 alignItems: "start",
                             }}>
                            <div
                                style={{
                                    backgroundImage: `url(images/desktop/Arrow.svg)`,
                                    width: "26px",
                                    height: "22px",
                                    backgroundPosition: "center",
                                    backgroundSize: "cover",
                                    backgroundRepeat: "no-repeat",
                                    borderRadius: "100px",
                                    flexShrink: "0",
                                    cursor: "default",
                                }}
                            />
                            <div className={cx(classes.fontInter_18_25_400, classes.color_255255255_07)}>
                                {"Поможем настроить Compass на ваших серверах или в облаке"}
                            </div>
                        </div>
                        <div className="hstack"
                             style={{
                                 gap: "12px",
                                 alignItems: "start",
                             }}>
                            <div
                                style={{
                                    backgroundImage: `url(images/desktop/Arrow.svg)`,
                                    width: "26px",
                                    height: "22px",
                                    backgroundPosition: "center",
                                    backgroundSize: "cover",
                                    backgroundRepeat: "no-repeat",
                                    borderRadius: "100px",
                                    flexShrink: "0",
                                    cursor: "default",
                                }}
                            />
                            <div className={cx(classes.fontInter_18_25_400, classes.color_255255255_07)}>
                                {"Обсудим индивидуальные условия сотрудничества и подберём тариф"}
                            </div>
                        </div>
                    </div>
                </div>
                <div className={cx(classes.rightContainer, "vstack")}>
                    <div className={cx(classes.fontInter_30_36_700, classes.color_255255255_085)}>
                        {"Заявка на консультацию с экспертом Compass"}
                    </div>
                    <input
                        autoFocus
                        value={nameValue}
                        className={classes.input}
                        onChange={(event) => {
                            const name = (event.target.value ?? "").replace(
                                /[^\dA-Za-zА-Яа-яЁёẞßÄäÜüÖöÀàÈèÉéÌìÍíÎîÒòÓóÙùÚúÂâÊêÔôœÛûËëÏïŸÿÇçÑñ\-' ]/g,
                                ""
                            );
                            setIsNameError(false);
                            setNameValue(name);
                        }}
                        maxLength={80}
                        placeholder="Имя"
                        style={{
                            marginTop: "32px",
                            border: isNameError ? "1px solid rgba(255, 106, 100, 0.5)" : "1px solid transparent",
                        }}
                    />
                    <input
                        value={phoneNumberValue}
                        className={classes.input}
                        onChange={(event) => {
                            let phoneNumber = (event.target.value ?? "").replace(/\D/g, "");
                            setIsPhoneNumberError(false);
                            if (phoneNumber.length < 1) {
                                setPhoneNumberValue("");
                                return;
                            }
                            phoneNumber = `+${phoneNumber}`;

                            if (isValidPhoneNumber(phoneNumber) || isPossiblePhoneNumber(phoneNumber)) {
                                phoneNumber = formatPhoneNumberIntl(phoneNumber);
                            }
                            setPhoneNumberValue(phoneNumber);
                        }}
                        onKeyDown={(event: React.KeyboardEvent) => {
                            if (event.key === "Enter") {
                                if (phoneNumberValue.length < 5) {
                                    return;
                                }

                                onClickHandler();
                            }
                        }}
                        maxLength={40}
                        placeholder="Телефон"
                        style={{
                            marginTop: "12px",
                            border: isPhoneNumberError ? "1px solid rgba(255, 106, 100, 0.5)" : "1px solid transparent",
                        }}
                    />
                    <div
                        className={cx(classes.button, apiWwwGetCompassRequestConsultation.isLoading && classes.buttonDisabled)}
                        onClick={() => apiWwwGetCompassRequestConsultation.isLoading ? null : onClickHandler()}
                        style={{
                            marginTop: "24px",
                        }}
                    >
                        <div className={cx(classes.fontInter_20_24_500, classes.color_white)}>
                            {apiWwwGetCompassRequestConsultation.isLoading ? "Подождите..." : "Оставить заявку"}
                        </div>
                    </div>
                    <div style={{
                        marginTop: "24px",
                        width: "100%",
                        textAlign: "center"
                    }}>
                        <div className={cx(classes.fontInter_14_21_400, classes.color_255255255_07)}>
                            <div>{"Если вы не любите заполнять формы,"}</div>
                            <div className="hstack" style={{
                                width: "100%",
                                justifyContent: "center"
                            }}>
                                <div>{"напишите нам в"}</div>
                                <div
                                    className="hstack"
                                    onMouseEnter={() => setIsTelegramHovered(true)}
                                    onMouseLeave={() => setIsTelegramHovered(false)}
                                    onClick={() => window.location.assign("https://t.me/getcompass")}
                                    style={{
                                        gap: "2px",
                                        marginLeft: "4px",
                                        cursor: "pointer",
                                    }}
                                >
                                    <div style={{
                                        width: "14px",
                                        height: "14px",
                                    }}>
                                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none"
                                             xmlns="http://www.w3.org/2000/svg">
                                            <g clipPath="url(#clip0_4454_35853)">
                                                <path
                                                    d="M7 14C10.866 14 14 10.866 14 7C14 3.13401 10.866 0 7 0C3.13401 0 0 3.13401 0 7C0 10.866 3.13401 14 7 14Z"
                                                    fill="url(#paint0_linear_4454_35853)"
                                                />
                                                <path
                                                    d="M4.73828 7.51145L5.56877 9.81014C5.56877 9.81014 5.67261 10.0252 5.78379 10.0252C5.89497 10.0252 7.54866 8.30485 7.54866 8.30485L9.38762 4.75293L4.76791 6.91809L4.73828 7.51145Z"
                                                    fill="#C8DAEA"
                                                />
                                                <path
                                                    d="M5.83995 8.10156L5.68053 9.79591C5.68053 9.79591 5.61379 10.3151 6.13284 9.79591C6.65189 9.27675 7.14872 8.8764 7.14872 8.8764"
                                                    fill="#A9C6D8"
                                                />
                                                <path
                                                    d="M4.75337 7.59416L3.04502 7.03754C3.04502 7.03754 2.84086 6.95471 2.9066 6.76688C2.92013 6.72814 2.94743 6.69518 3.0291 6.63854C3.40762 6.3747 10.0353 3.99254 10.0353 3.99254C10.0353 3.99254 10.2224 3.92948 10.3328 3.97143C10.3601 3.97988 10.3847 3.99543 10.404 4.01649C10.4233 4.03756 10.4367 4.06337 10.4427 4.0913C10.4547 4.14063 10.4596 4.19138 10.4576 4.24209C10.457 4.28596 10.4517 4.32662 10.4477 4.39038C10.4073 5.04167 9.19936 9.90247 9.19936 9.90247C9.19936 9.90247 9.12709 10.1869 8.86815 10.1966C8.80451 10.1987 8.74111 10.1879 8.68173 10.1649C8.62235 10.142 8.56821 10.1073 8.52252 10.0629C8.01438 9.62585 6.25808 8.44553 5.86999 8.18595C5.86123 8.17999 5.85386 8.17221 5.84837 8.16316C5.84287 8.1541 5.83939 8.14397 5.83814 8.13345C5.83271 8.10609 5.86246 8.0722 5.86246 8.0722C5.86246 8.0722 8.92065 5.35387 9.00202 5.0685C9.00832 5.04639 8.98452 5.03548 8.95255 5.04517C8.74944 5.11989 5.22832 7.3435 4.83971 7.58891C4.81173 7.59737 4.78217 7.59917 4.75337 7.59416Z"
                                                    fill="white"
                                                />
                                            </g>
                                            <defs>
                                                <linearGradient
                                                    id="paint0_linear_4454_35853"
                                                    x1="7"
                                                    y1="14"
                                                    x2="7"
                                                    y2="0"
                                                    gradientUnits="userSpaceOnUse"
                                                >
                                                    <stop stopColor="#1D93D2"/>
                                                    <stop offset="1" stopColor="#38B0E3"/>
                                                </linearGradient>
                                                <clipPath id="clip0_4454_35853">
                                                    <rect width="14" height="14" fill="white"/>
                                                </clipPath>
                                            </defs>
                                        </svg>
                                    </div>
                                    <div className={cx(classes.hyperLink, isTelegramHovered && classes.hyperLinkHover)}>
                                        {"Telegram"}
                                    </div>
                                </div>
                                <div>,</div>
                                <div
                                    className="hstack"
                                    onMouseEnter={() => setIsWhatsappHovered(true)}
                                    onMouseLeave={() => setIsWhatsappHovered(false)}
                                    onClick={() => window.location.assign("https://wa.me/message/CJINDDW52XJYM1")}
                                    style={{
                                        gap: "2px",
                                        marginLeft: "4px",
                                        cursor: "pointer",
                                    }}
                                >
                                    <div style={{
                                        width: "14px",
                                        height: "14px",
                                    }}>
                                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none"
                                             xmlns="http://www.w3.org/2000/svg">
                                            <g clipPath="url(#clip0_4454_35847)">
                                                <path d="M14 0H0V14H14V0Z" fill="#1BD741"/>
                                                <path
                                                    d="M2.97168 11.0327L3.53596 9.02855C3.17309 8.4121 2.98204 7.71072 2.98204 6.9902C2.98204 4.77169 4.78693 2.9668 7.00544 2.9668C9.22395 2.9668 11.0288 4.77169 11.0288 6.9902C11.0288 9.20871 9.22395 11.0136 7.00544 11.0136C6.31417 11.0136 5.63728 10.8367 5.03847 10.5007L2.97168 11.0327ZM5.14413 9.76869L5.26729 9.84389C5.78938 10.1626 6.39043 10.3311 7.00544 10.3311C8.84762 10.3311 10.3463 8.83237 10.3463 6.9902C10.3463 5.14802 8.84762 3.6493 7.00544 3.6493C5.16327 3.6493 3.66455 5.14802 3.66455 6.9902C3.66455 7.63208 3.84698 8.2554 4.19207 8.79279L4.27501 8.92193L3.95006 10.0761L5.14413 9.76869Z"
                                                    fill="white"
                                                />
                                                <path
                                                    d="M5.87564 5.11822L5.61457 5.10399C5.53257 5.09951 5.45214 5.12692 5.39016 5.18076C5.26359 5.29067 5.06121 5.50315 4.99906 5.78005C4.90636 6.19291 5.04961 6.69847 5.42036 7.20402C5.79111 7.70958 6.48203 8.51847 7.70379 8.86394C8.09749 8.97527 8.40719 8.90022 8.64615 8.74736C8.8354 8.62631 8.96586 8.432 9.01288 8.21233L9.05456 8.01764C9.06781 7.95576 9.03639 7.89297 8.9789 7.86648L8.09658 7.45978C8.0393 7.43339 7.97138 7.45008 7.93286 7.5L7.58647 7.94904C7.56031 7.98296 7.51552 7.99641 7.47507 7.9822C7.23787 7.89883 6.44328 7.56605 6.00729 6.72622C5.98838 6.6898 5.99308 6.64558 6.0199 6.61452L6.35094 6.23156C6.38476 6.19245 6.39331 6.13747 6.37298 6.08995L5.99265 5.20015C5.97241 5.15277 5.92703 5.12103 5.87564 5.11822Z"
                                                    fill="white"
                                                />
                                            </g>
                                            <defs>
                                                <clipPath id="clip0_4454_35847">
                                                    <rect width="14" height="14" rx="7" fill="white"/>
                                                </clipPath>
                                            </defs>
                                        </svg>
                                    </div>
                                    <div className={cx(classes.hyperLink, isWhatsappHovered && classes.hyperLinkHover)}>
                                        {"WhatsApp"}
                                    </div>
                                </div>
                            </div>
                            <div className="hstack" style={{
                                width: "100%",
                                justifyContent: "center",
                                gap: "4px",
                            }}>
                                <div>{"или на почту"}</div>
                                <a
                                    className={cx(classes.fontInter_14_21_400, classes.hyperLink)}
                                    href={"mailto:support@getcompass.ru"}
                                >
                                    {"support@getcompass.ru"}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        );
    };

    return (
        <BaseDialog
            className={isSuccessSend ? classes.successDialogContainer : classes.dialogContainer}
            disableBackdropClose={false}
            disableEnter={true}
            disableEscape={!isSuccessSend}
            onClose={() => {
                dispatch(hideDialog(RequestConsultationDialog))
                setTimeout(() => setIsSuccessSend(false), 1000);
            }}
            size={isSuccessSend ? "medium" : "large"}
            footerContent={
                isSuccessSend
                    ? undefined
                    : <div className={cx(classes.fontInter_14_21_400, classes.color_255255255_02)}
                           style={{
                               textAlign: "center",
                               width: "100%",
                               userSelect: "none",
                           }}
                    >
                        <div className="hstack" style={{
                            gap: "4px",
                            width: "100%",
                            justifyContent: "center",
                        }}>
                            {"Нажимая «Оставить заявку», вы даёте "}
                            <a className={cx(classes.footerHyperLink, classes.fontInter_14_21_400, classes.color_255255255_02)}
                               href="https://getcompass.ru/docs/agreement.pdf"
                               target="_blank"
                            >
                                {"согласие"}
                            </a>
                            {" на обработку персональных"}
                        </div>
                        <div className="hstack" style={{
                            width: "100%",
                            justifyContent: "center",
                        }}>
                            {"данных и принимаете условия "}
                            <a className={cx(classes.footerHyperLink, classes.fontInter_14_21_400, classes.color_255255255_02)}
                               href="https://getcompass.ru/docs/privacy.pdf"
                               target="_blank"
                               style={{
                                   marginLeft: "4px"
                               }}
                            >
                                {"Политики конфиденциальности"}
                            </a>
                            .
                        </div>
                    </div>
            }
        >
            {renderedContent()}
        </BaseDialog>
    );
}
