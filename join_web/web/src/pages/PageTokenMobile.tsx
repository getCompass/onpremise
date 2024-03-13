import { Box, HStack, styled, VStack } from "../../styled-system/jsx";
import IconLogo from "../components/IconLogo.tsx";
import { Text } from "../components/text.tsx";
import { Button } from "../components/button.tsx";
import Preloader20 from "../components/Preloader20.tsx";
import { useLangString } from "../lib/getLangString.ts";
import useDeviceDetect from "../lib/useDeviceDetect.ts";
import { useCallback, useEffect, useRef, useState } from "react";
import { Portal } from "@ark-ui/react";
import { Menu, MenuContent, MenuItem, MenuItemGroup, MenuPositioner, MenuTrigger } from "../components/menu.tsx";
import { useAtomValue, useSetAtom } from "jotai";
import {
	authInputState,
	authState,
	confirmPasswordState,
	isPasswordChangedState,
	isRegistrationState,
	joinLinkState,
	nameInputState,
	passwordInputState,
	prepareJoinLinkErrorState,
	useToastConfig,
} from "../api/_stores.ts";
import { useApiAuthGenerateToken } from "../api/auth.ts";
import { copyToClipboard } from "../lib/copyToClipboard.ts";
import { useApiJoinLinkAccept } from "../api/joinlink.ts";
import useIsJoinLink from "../lib/useIsJoinLink.ts";
import LogoutButtonMobile from "../components/LogoutButtonMobile.tsx";
import { useAtom } from "jotai/index";
import Toast, { useShowToast } from "../lib/Toast.tsx";

const AppGalleryIcon = () => {
	return (
		<Box w="32px" h="32px">
			<svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
				<g opacity="0.3">
					<path
						d="M3.63672 26.7656V6.68166C3.65491 6.64761 3.6694 6.61172 3.67993 6.57459C3.81345 5.8348 4.1811 5.23046 4.81513 4.83638C5.15956 4.62225 5.57236 4.51841 5.95419 4.36426H26.0388C26.0588 4.37651 26.0775 4.39715 26.0987 4.40038C27.2339 4.54227 28.3678 5.58971 28.3633 7.18732C28.3459 13.5429 28.3569 19.8985 28.353 26.2541C28.3579 26.5109 28.333 26.7673 28.2788 27.0184C27.9802 28.2819 26.9243 29.0823 25.5757 29.0823C19.9926 29.0823 14.4092 29.0823 8.82572 29.0823C7.95626 29.0823 7.08423 29.1068 6.21929 29.0733C5.17955 29.0346 4.40169 28.5283 3.92697 27.5995C3.79345 27.3402 3.73347 27.0448 3.63672 26.7656ZM15.4814 17.554L15.5711 17.5198C15.6079 17.0825 15.6511 16.6459 15.6801 16.2079C15.802 14.3517 15.8343 12.498 15.5105 10.656C15.3963 10.0065 15.2357 9.36991 14.9397 8.77331C14.8713 8.63529 14.7855 8.58562 14.6359 8.62368C14.3869 8.68817 14.1321 8.72881 13.887 8.80427C13.4562 8.94319 13.0785 9.21118 12.805 9.57194C12.5316 9.93269 12.3757 10.3688 12.3584 10.8211C12.3571 10.8614 12.3565 10.9016 12.3566 10.9417C12.358 11.5428 12.5125 12.1171 12.7351 12.6722C13.4213 14.3827 14.4037 15.9261 15.424 17.4489C15.446 17.4824 15.4627 17.5198 15.4814 17.554ZM16.4257 17.5237L16.4941 17.5301C16.5402 17.476 16.5833 17.4194 16.6231 17.3605C17.2481 16.3408 17.8931 15.3327 18.4884 14.2956C18.9515 13.4881 19.3437 12.6399 19.5456 11.7208C19.7507 10.785 19.6339 9.9304 18.8838 9.24608C18.4794 8.87844 17.9808 8.75525 17.4648 8.64109C17.287 8.60197 17.179 8.62671 17.097 8.71531C17.0573 8.75818 17.0238 8.81599 16.9914 8.88876C16.7011 9.54083 16.5399 10.2264 16.4335 10.9262C16.129 12.9102 16.2 14.8999 16.3619 16.8897C16.3633 16.9124 16.3649 16.9352 16.3667 16.9579C16.3671 16.9631 16.3676 16.9684 16.3681 16.9737C16.3829 17.1581 16.4067 17.3412 16.4257 17.5243V17.5237ZM14.9371 17.9436C14.7008 17.4337 14.4528 16.9297 14.1935 16.4319C13.1426 14.4146 11.9044 12.499 10.4947 10.7107C10.4924 10.7078 10.49 10.7049 10.4877 10.702C10.4874 10.7015 10.487 10.701 10.4866 10.7005C9.99446 11.201 9.60488 11.7163 9.36881 12.3362C9.02309 13.243 9.18692 14.0557 9.83837 14.769C9.85251 14.7842 9.86678 14.7992 9.88116 14.8142C10.0116 14.9498 10.1516 15.0759 10.3002 15.1915C10.3548 15.2351 10.4096 15.2783 10.4648 15.3211C11.5684 16.1776 12.7751 16.8613 14.0043 17.5076C14.2556 17.6397 14.5078 17.7702 14.7603 17.9004C14.8179 17.9198 14.8771 17.9345 14.9371 17.9442V17.9436ZM21.5038 10.6921C19.7397 12.9586 18.2524 15.3308 17.1056 17.9158L17.1058 17.9158C17.1996 17.9063 17.2903 17.8771 17.3719 17.8301C17.5594 17.7285 17.7475 17.6277 17.9356 17.527C18.4198 17.2676 18.9041 17.0081 19.3798 16.7336C20.3054 16.1996 21.2161 15.6371 22.0062 14.909C22.4068 14.5387 22.6912 14.0963 22.7609 13.5468C22.7705 13.4704 22.7768 13.3954 22.78 13.3219C22.7801 13.3198 22.7801 13.3177 22.7802 13.3157C22.8231 12.251 22.2042 11.4732 21.5038 10.6921ZM14.4927 18.5053L14.5159 18.4376C12.4803 17.1012 10.3505 15.9222 8.20781 14.7548C7.59378 16.522 8.93989 18.4763 10.7704 18.5131C11.6021 18.5295 12.4337 18.5279 13.2654 18.5262C13.607 18.5255 13.9486 18.5248 14.2901 18.5253C14.3579 18.5253 14.4249 18.5118 14.4927 18.5053ZM23.7806 14.7542C23.7799 14.7546 23.7792 14.755 23.7785 14.7554C21.6294 15.9346 19.5043 17.1056 17.49 18.4712C17.5469 18.5046 17.6112 18.5231 17.677 18.5252C17.6781 18.5252 17.6792 18.5252 17.6803 18.5252C17.6846 18.5253 17.6889 18.5254 17.6931 18.5253C17.7319 18.5253 17.7707 18.5254 17.8094 18.5254C18.0444 18.5255 18.2795 18.5259 18.5145 18.5263C18.984 18.527 19.4536 18.5278 19.9232 18.5263C19.9446 18.5262 19.966 18.5262 19.9874 18.5261C20.196 18.5253 20.4045 18.5241 20.613 18.5221C20.9652 18.5221 21.3225 18.5221 21.6683 18.4525C23.312 18.1217 24.3426 16.3346 23.7785 14.7554C23.7783 14.755 23.7782 14.7546 23.778 14.7542H23.7806ZM9.50619 19.0626C9.78354 19.5605 10.1093 19.9585 10.5556 20.2468C10.9101 20.4754 11.2779 20.5917 11.65 20.5528C11.655 20.5522 11.66 20.5517 11.665 20.5511C11.8634 20.5281 12.0629 20.4609 12.2623 20.3429C12.7896 20.0294 13.3094 19.7002 13.8288 19.3713C14.0083 19.2576 14.1878 19.144 14.3675 19.031C14.3953 19.0129 14.4088 18.9723 14.4643 18.8904L9.50619 19.0626ZM17.5042 18.8898C17.5329 18.9253 17.5528 18.9519 17.5677 18.9719C17.5909 19.003 17.6024 19.0184 17.6177 19.0278C17.7893 19.1343 17.9609 19.2411 18.1324 19.3479C18.6952 19.6982 19.2582 20.0487 19.8261 20.3913C20.0861 20.5437 20.393 20.5953 20.6885 20.5364C21.5464 20.3842 22.043 19.7895 22.4945 19.0626L17.5042 18.8898ZM16.3548 22.1191C16.363 22.1494 16.3698 22.1766 16.3761 22.2018C16.3877 22.2481 16.3975 22.2875 16.4109 22.3255C16.4933 22.5629 16.5763 22.8001 16.6594 23.0374C16.8417 23.5581 17.024 24.0788 17.1991 24.6017C17.2273 24.6864 17.2633 24.7352 17.3146 24.7613C17.3569 24.7828 17.4097 24.7888 17.4771 24.7868C17.6261 24.7823 17.7286 24.7784 17.7828 24.6029C17.9176 24.1657 18.0756 23.7355 18.2253 23.3033C18.2491 23.235 18.2807 23.1698 18.3291 23.0531C18.5226 23.6077 18.6981 24.0986 18.8567 24.5952C18.9103 24.7623 19.0051 24.7855 19.1573 24.7887C19.2013 24.7896 19.2399 24.7869 19.2739 24.7786C19.3103 24.7696 19.3413 24.7543 19.3679 24.7301C19.3701 24.7281 19.3722 24.726 19.3743 24.7239C19.4055 24.693 19.4305 24.6492 19.4508 24.5881C19.7049 23.8264 19.9758 23.0705 20.2389 22.312C20.2571 22.2483 20.2719 22.1837 20.2835 22.1185C20.2828 22.1186 20.2821 22.1186 20.2815 22.1187C20.239 22.1222 20.1964 22.1233 20.1539 22.122C20.1531 22.1219 20.1523 22.1219 20.1515 22.1219C20.1325 22.1212 20.1135 22.1201 20.0945 22.1185C20.0657 22.113 20.0387 22.1094 20.0132 22.1076C19.7972 22.0926 19.6935 22.2109 19.6301 22.4474C19.5397 22.7873 19.4281 23.1218 19.3097 23.4767C19.2591 23.6282 19.2073 23.7835 19.1554 23.9444C19.155 23.9434 19.1547 23.9424 19.1543 23.9414C18.967 23.3757 18.7939 22.869 18.6335 22.3584C18.5845 22.2014 18.5301 22.1136 18.3478 22.1073C18.3407 22.107 18.3334 22.1069 18.3259 22.1069C18.1259 22.1069 18.0679 22.2011 18.0221 22.3649C17.9668 22.555 17.9027 22.7424 17.8386 22.93C17.8092 23.0161 17.7798 23.1021 17.7512 23.1885C17.7005 23.3403 17.6503 23.4926 17.6002 23.6449C17.5751 23.7211 17.55 23.7973 17.5248 23.8735C17.5244 23.8726 17.5241 23.8717 17.5237 23.8708C17.3158 23.361 17.1338 22.8409 16.9785 22.3126C16.9449 22.2001 16.897 22.142 16.8157 22.1236C16.7847 22.1166 16.7488 22.1154 16.7069 22.1191C16.6333 22.1242 16.56 22.1226 16.4815 22.1209C16.4812 22.1209 16.4808 22.1209 16.4805 22.1209C16.4396 22.12 16.3973 22.1191 16.3528 22.1191H16.3548ZM8.09881 22.1288V24.7661H8.62899H8.63029V23.7128V23.7116H9.85578V24.7629H10.3769V22.1333H9.84611V23.1788H8.62061V23.1775V22.1288H8.09881ZM22.6951 22.1185H20.7601V22.1204V24.7629H22.7441H22.7473V24.2934H21.2896V23.6245V23.6226H22.2507H22.2539V23.1382H21.2922V22.5977H22.6925L22.6925 22.5935L22.6951 22.1185ZM11.1625 22.1295C11.1625 22.2938 11.1602 22.4562 11.1576 22.6169C11.1573 22.6399 11.1569 22.6629 11.1565 22.6858C11.1498 23.0945 11.1433 23.4933 11.1709 23.8896C11.2109 24.4636 11.6224 24.7971 12.2345 24.8229C12.2564 24.8238 12.278 24.8243 12.2995 24.8244C12.3149 24.8244 12.3301 24.8242 12.3453 24.8238C12.903 24.8099 13.3237 24.5053 13.3897 23.967C13.4542 23.4201 13.4278 22.8615 13.4394 22.3081C13.4372 22.2478 13.4314 22.1878 13.422 22.1282H12.9021C12.9021 22.3958 12.9021 22.6513 12.9021 22.906C12.901 22.98 12.901 23.0541 12.901 23.1282C12.901 23.1348 12.901 23.1415 12.901 23.1481C12.9012 23.3679 12.9014 23.5877 12.8762 23.8043C12.8756 23.8095 12.875 23.8147 12.8744 23.8199C12.8342 24.1469 12.6201 24.3047 12.2925 24.3035C12.2865 24.3035 12.2805 24.3034 12.2744 24.3033C12.2712 24.3033 12.2681 24.3031 12.2648 24.303C11.9359 24.2927 11.7185 24.1031 11.7011 23.7658C11.6798 23.3652 11.6889 22.9628 11.6856 22.561C11.6856 22.4197 11.6856 22.2785 11.6856 22.1295H11.1625ZM16.5205 24.7739C16.4973 24.7094 16.4831 24.6558 16.4612 24.6062C16.1239 23.8322 15.7775 23.0666 15.4518 22.2901C15.4003 22.167 15.3299 22.1331 15.2437 22.1211C15.2147 22.117 15.1839 22.1154 15.1514 22.1138C15.1377 22.1131 15.1236 22.1123 15.1093 22.1114C14.95 22.1017 14.8648 22.1533 14.7997 22.3049C14.5716 22.8362 14.3379 23.3654 14.1042 23.8945C14.0009 24.1284 13.8976 24.3624 13.7948 24.5965C13.7732 24.6535 13.7547 24.7117 13.7396 24.7708C13.7395 24.7712 13.7394 24.7716 13.7393 24.7719C14.2418 24.8055 14.236 24.8022 14.4527 24.3759C14.4769 24.3364 14.5095 24.3026 14.5482 24.277C14.5868 24.2513 14.6306 24.2345 14.6765 24.2276C14.9487 24.2089 15.2234 24.2321 15.4956 24.216C15.5673 24.2117 15.6272 24.2183 15.6759 24.2385C15.7521 24.2702 15.801 24.3352 15.8246 24.4443C15.8483 24.5547 15.8887 24.6393 15.9463 24.6981C15.9469 24.6988 15.9476 24.6995 15.9483 24.7001C16.0378 24.7901 16.1682 24.8184 16.3406 24.7848C16.3994 24.7773 16.4587 24.7734 16.518 24.7732L16.5205 24.7739ZM23.422 22.1295V24.7642H23.918V22.1295H23.422Z"
						fill="#333E49"
					/>
					<path
						d="M15.5182 23.7264H14.7185C14.8097 23.5079 14.8986 23.2997 14.994 23.0766C15.0335 22.9842 15.0741 22.8892 15.1164 22.7899C15.2603 23.1252 15.3847 23.4142 15.5182 23.7264Z"
						fill="#333E49"
					/>
					<path
						fillRule="evenodd"
						clipRule="evenodd"
						d="M12.7351 12.6722C12.5125 12.117 12.358 11.5447 12.3566 10.9417C12.358 11.5428 12.5125 12.1171 12.7351 12.6722Z"
						fill="#333E49"
					/>
					<path
						fillRule="evenodd"
						clipRule="evenodd"
						d="M16.4257 17.5243L16.4935 17.5308C16.4937 17.5306 16.4939 17.5304 16.4941 17.5301L16.4257 17.5237V17.5243ZM16.3612 16.8904C16.3629 16.9129 16.3648 16.9354 16.3667 16.9579C16.3649 16.9352 16.3633 16.9124 16.3619 16.8897C16.2 14.8999 16.129 12.9102 16.4335 10.9262C16.5399 10.2264 16.7011 9.54083 16.9914 8.88876C17.0238 8.81599 17.0573 8.75818 17.097 8.71531C17.0571 8.75825 17.0233 8.81629 16.9907 8.88945C16.7005 9.54152 16.5399 10.2271 16.4328 10.9269C16.1284 12.9109 16.1993 14.9006 16.3612 16.8904Z"
						fill="#333E49"
					/>
					<path
						fillRule="evenodd"
						clipRule="evenodd"
						d="M17.1058 17.9158L17.1055 17.9164C17.1994 17.907 17.2902 17.8777 17.3719 17.8307C17.5676 17.7244 17.7639 17.6192 17.9602 17.514C18.4362 17.2588 18.9122 17.0036 19.3798 16.7342C20.3054 16.1995 21.2161 15.6403 22.0062 14.9096C22.4068 14.5394 22.6912 14.0969 22.7609 13.5474C22.7705 13.4708 22.7768 13.3956 22.78 13.3219C22.7768 13.3954 22.7705 13.4704 22.7609 13.5468C22.6912 14.0963 22.4068 14.5387 22.0062 14.909C21.2161 15.6371 20.3054 16.1996 19.3798 16.7336C18.9041 17.0081 18.4198 17.2676 17.9356 17.527C17.7475 17.6277 17.5594 17.7285 17.3719 17.8301C17.2903 17.8771 17.1996 17.9063 17.1058 17.9158Z"
						fill="#333E49"
					/>
					<path
						fillRule="evenodd"
						clipRule="evenodd"
						d="M23.7785 14.7554C23.7783 14.755 23.7782 14.7546 23.778 14.7542C21.6283 15.9338 19.5023 17.1025 17.4874 18.4711C17.545 18.5049 17.6103 18.5235 17.677 18.5252C17.6112 18.5231 17.5469 18.5046 17.49 18.4712C19.5043 17.1056 21.6294 15.9346 23.7785 14.7554ZM19.9874 18.5261C19.966 18.5262 19.9446 18.5262 19.9232 18.5263C19.4536 18.5278 18.984 18.527 18.5145 18.5263C18.2795 18.5259 18.0444 18.5255 17.8094 18.5254C18.0047 18.5256 18.2 18.5259 18.3952 18.5263C18.926 18.5272 19.4567 18.5282 19.9874 18.5261Z"
						fill="#333E49"
					/>
					<path
						fillRule="evenodd"
						clipRule="evenodd"
						d="M17.5042 18.8898L17.5022 18.8898C17.5315 18.926 17.5515 18.9528 17.5665 18.973C17.5892 19.0034 17.6006 19.0187 17.6157 19.0284C17.7873 19.1349 17.9588 19.2417 18.1304 19.3485C18.6932 19.6988 19.2562 20.0493 19.8242 20.3919C20.0848 20.5446 20.3924 20.596 20.6885 20.5364C20.393 20.5953 20.0861 20.5437 19.8261 20.3913C19.2582 20.0487 18.6952 19.6982 18.1324 19.3479C17.9609 19.2411 17.7893 19.1343 17.6177 19.0278C17.6024 19.0184 17.5909 19.003 17.5677 18.9719C17.5528 18.9519 17.5329 18.9253 17.5042 18.8898Z"
						fill="#333E49"
					/>
					<path
						fillRule="evenodd"
						clipRule="evenodd"
						d="M13.8181 19.3783C14.0011 19.2623 14.1841 19.1463 14.3675 19.031C14.1878 19.144 14.0083 19.2576 13.8288 19.3713C13.3094 19.7002 12.7896 20.0294 12.2623 20.3429C12.7873 20.0316 13.3028 19.7049 13.8181 19.3783ZM11.665 20.5511C11.66 20.5517 11.655 20.5522 11.65 20.5528C11.2779 20.5917 10.9101 20.4754 10.5556 20.2468C10.9137 20.4792 11.2868 20.5954 11.665 20.5511Z"
						fill="#333E49"
					/>
					<path
						fillRule="evenodd"
						clipRule="evenodd"
						d="M13.4353 18.5266C13.7203 18.526 14.0052 18.5253 14.2901 18.5253C13.9486 18.5248 13.607 18.5255 13.2654 18.5262C12.4337 18.5279 11.6021 18.5295 10.7704 18.5131C11.6587 18.5307 12.547 18.5287 13.4353 18.5266Z"
						fill="#333E49"
					/>
					<path
						fillRule="evenodd"
						clipRule="evenodd"
						d="M8.61931 22.1288H8.09622V24.7674H8.62899V24.7661H8.09881V22.1288L8.61931 22.1288ZM8.62061 23.1775V23.1788H9.84611V22.1333H10.3769L9.84481 22.1333V23.1775H8.62061ZM10.3769 24.7629H9.85578V23.7116H8.63029V23.7128H9.85448V24.7641H10.3769L10.3769 24.7629Z"
						fill="#333E49"
					/>
					<path
						fillRule="evenodd"
						clipRule="evenodd"
						d="M15.2437 22.1211C15.2121 22.1161 15.1785 22.1142 15.1431 22.1122C15.131 22.1116 15.1186 22.1109 15.1061 22.1101C14.9468 22.101 14.861 22.1526 14.7965 22.3036C14.5684 22.8349 14.3347 23.364 14.101 23.8931C13.9977 24.1271 13.8944 24.361 13.7916 24.5951C13.7698 24.6525 13.7513 24.7111 13.7361 24.7706C13.7373 24.7707 13.7384 24.7707 13.7396 24.7708C13.7547 24.7117 13.7732 24.6535 13.7948 24.5965C13.8976 24.3624 14.0009 24.1284 14.1042 23.8945C14.3379 23.3654 14.5716 22.8362 14.7997 22.3049C14.8648 22.1533 14.95 22.1017 15.1093 22.1114C15.1236 22.1123 15.1377 22.1131 15.1514 22.1138C15.1839 22.1154 15.2147 22.117 15.2437 22.1211ZM15.8214 24.4429C15.8468 24.5542 15.8881 24.6393 15.9463 24.6981C15.8887 24.6393 15.8483 24.5547 15.8246 24.4443C15.801 24.3352 15.7521 24.2702 15.6759 24.2385C15.7503 24.2707 15.7981 24.3354 15.8214 24.4429ZM14.7185 23.7264C14.8097 23.5079 14.8986 23.2997 14.994 23.0766C15.0335 22.9842 15.0741 22.8892 15.1164 22.7899C14.9719 23.1291 14.8494 23.4174 14.7185 23.7264Z"
						fill="#333E49"
					/>
					<path
						fillRule="evenodd"
						clipRule="evenodd"
						d="M22.6925 22.5996V22.5935L22.6925 22.5977H21.2922V23.1382L21.2923 22.5996H22.6925ZM22.2507 23.6226H21.2896V23.6245H22.2507V23.6226ZM22.7441 24.7629V24.7648H20.7569V22.1204H20.7601V24.7629H22.7441Z"
						fill="#333E49"
					/>
					<path
						fillRule="evenodd"
						clipRule="evenodd"
						d="M23.422 22.1295L23.4207 22.1295V24.7642H23.9167L23.422 24.7642V22.1295Z"
						fill="#333E49"
					/>
					<path
						fillRule="evenodd"
						clipRule="evenodd"
						d="M14.9371 17.9442C14.8778 17.935 14.8193 17.921 14.7622 17.9023C14.5092 17.7716 14.2563 17.6404 14.0043 17.5076C14.2556 17.6397 14.5078 17.7702 14.7603 17.9004C14.8179 17.9198 14.8771 17.9345 14.9371 17.9442ZM10.4648 15.3211C10.4096 15.2783 10.3548 15.2351 10.3002 15.1915C10.1516 15.0759 10.0116 14.9498 9.88116 14.8142C10.0121 14.9506 10.1528 15.0773 10.3021 15.1934C10.356 15.2364 10.4103 15.279 10.4648 15.3211ZM10.4877 10.702C10.49 10.7049 10.4924 10.7078 10.4947 10.7107L10.4885 10.7012L10.4877 10.702Z"
						fill="#333E49"
					/>
					<path
						fillRule="evenodd"
						clipRule="evenodd"
						d="M11.1576 22.6169C11.1602 22.4562 11.1625 22.2938 11.1625 22.1295L11.6824 22.1295H11.16C11.16 22.3191 11.1568 22.5054 11.1536 22.6896C11.1466 23.0948 11.1398 23.4901 11.1677 23.8896C11.2084 24.4707 11.6141 24.7971 12.2313 24.8229C12.2543 24.8238 12.277 24.8243 12.2995 24.8244C12.278 24.8243 12.2564 24.8238 12.2345 24.8229C11.6224 24.7971 11.2109 24.4636 11.1709 23.8896C11.1433 23.4933 11.1498 23.0945 11.1565 22.6858C11.1569 22.6629 11.1573 22.6399 11.1576 22.6169ZM12.2744 24.3033C12.2805 24.3034 12.2865 24.3035 12.2925 24.3035C12.6201 24.3047 12.8342 24.1469 12.8744 23.8199C12.875 23.8147 12.8756 23.8095 12.8762 23.8043L12.8712 23.8199C12.8982 23.5983 12.898 23.3732 12.8979 23.1481C12.8979 23.1435 12.8978 23.1389 12.8978 23.1343L12.901 23.1282C12.901 23.0541 12.901 22.98 12.9021 22.906V22.1282L13.4188 22.1282H12.8989C12.8989 22.2183 12.8994 22.3068 12.8998 22.3942C12.9006 22.5664 12.9015 22.7349 12.8989 22.906C12.8978 22.982 12.8978 23.0582 12.8978 23.1343L12.3058 24.278L12.2744 24.3033ZM12.3058 24.278L12.2925 24.3035C12.6183 24.3036 12.8312 24.1458 12.8712 23.8199L12.3058 24.278Z"
						fill="#333E49"
					/>
				</g>
			</svg>
		</Box>
	);
};

const GooglePlayIcon = () => {
	return (
		<Box w="32px" h="32px">
			<svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
				<g opacity="0.3">
					<path
						d="M17.1171 16.0437L5.04092 28.1543C4.6409 27.7233 4.42861 27.1967 4.37129 26.6003C4.36135 26.4942 4.36486 26.3863 4.36486 26.279C4.36486 19.4297 4.36486 12.5803 4.36486 5.73096C4.36486 5.0185 4.5637 4.39049 4.99764 3.8862L17.1171 16.0437Z"
						fill="#333E49"
					/>
					<path
						d="M18.1382 16.9966L22.3712 21.2437L19.3915 22.844C15.7429 24.8025 12.0944 26.7614 8.4458 28.7207C7.77266 29.0831 7.07613 29.198 6.33515 28.9816C6.30717 28.9747 6.27979 28.9655 6.25327 28.9541C6.23631 28.9459 6.22462 28.9283 6.2328 28.9365L18.1382 16.9966Z"
						fill="#333E49"
					/>
					<path
						d="M22.3794 10.7651L18.1528 15.0046L6.23163 3.05001C6.97554 2.80491 7.70833 2.88114 8.42065 3.26346C11.1343 4.72238 13.8488 6.17935 16.5644 7.63436C18.3797 8.60893 20.1952 9.58291 22.0109 10.5563C22.1378 10.6249 22.2618 10.6988 22.3794 10.7651Z"
						fill="#333E49"
					/>
					<path
						d="M19.0915 16.0249C20.612 14.5003 22.122 12.9851 23.6572 11.4453L25.3076 12.3295C26.0615 12.733 26.8135 13.1387 27.568 13.541C28.5563 14.0687 29.0991 14.8897 29.0914 16.0132C29.0838 17.1367 28.5329 17.9453 27.5387 18.4684C26.2925 19.1245 25.055 19.7977 23.8169 20.4679C23.6841 20.5401 23.5952 20.5682 23.466 20.4386C22.0285 18.9856 20.5859 17.5366 19.1382 16.0918C19.1208 16.0708 19.1052 16.0485 19.0915 16.0249Z"
						fill="#333E49"
					/>
				</g>
			</svg>
		</Box>
	);
};

const AppStoreIcon = () => {
	return (
		<Box w="32px" h="32px">
			<svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
				<g opacity="0.3">
					<path
						d="M6.18638 3.88477H25.8292C27.308 3.88477 28.5078 5.08454 28.5078 6.56334V26.2062C28.5078 27.685 27.308 28.8848 25.8292 28.8848H6.18638C4.70759 28.8848 3.50781 27.685 3.50781 26.2062V6.56334C3.50781 5.08454 4.70759 3.88477 6.18638 3.88477ZM21.4208 23.5555C21.7277 24.0912 22.4141 24.2698 22.9442 23.9629C23.4799 23.656 23.6585 22.9696 23.3516 22.4395L22.5536 21.0611C21.6551 20.7877 20.9185 20.9997 20.3438 21.6973L21.4208 23.5555ZM13.6696 20.5477H23.8203C24.4342 20.5477 24.9364 20.0455 24.9364 19.4316C24.9364 18.8178 24.4342 18.3156 23.8203 18.3156H20.9743L17.3248 11.9986L18.4688 10.0232C18.7757 9.48744 18.5915 8.80664 18.0614 8.49972C17.5257 8.1928 16.8449 8.37695 16.5379 8.90709L16.0413 9.76646L15.5446 8.90709C15.2377 8.37137 14.5513 8.1928 14.0212 8.49972C13.4855 8.80664 13.3069 9.49302 13.6138 10.0232L18.4018 18.3156H14.9364C13.8092 18.3156 13.1786 19.6381 13.6696 20.5477ZM8.19531 20.5477H9.81362L8.71987 22.4395C8.41295 22.9752 8.5971 23.656 9.12723 23.9629C9.66295 24.2698 10.3438 24.0857 10.6507 23.5555C12.4866 20.3803 13.8594 17.9919 14.7801 16.4071C15.7121 14.7888 15.048 13.1705 14.3839 12.6236C13.6529 13.8903 12.5592 15.7877 11.0971 18.3156H8.19531C7.58147 18.3156 7.07924 18.8178 7.07924 19.4316C7.07924 20.0511 7.58147 20.5477 8.19531 20.5477Z"
						fill="#333E49"
					/>
				</g>
			</svg>
		</Box>
	);
};

type StepTwoContentProps = {
	childBlockWidth: number;
	isStoreMenuOpen: boolean;
	setStoreMenuOpen: (value: boolean) => void;
};

const StepTwoContent = ({ childBlockWidth, isStoreMenuOpen, setStoreMenuOpen }: StepTwoContentProps) => {
	const langStringPageTokenStep2DescPt1Mobile = useLangString("page_token.step_2.desc_pt1_mobile");
	const langStringPageTokenStep2DescPt2Mobile = useLangString("page_token.step_2.desc_pt2_mobile");
	const langStringPageTokenStep2ButtonMobile = useLangString("page_token.step_2.button_mobile");
	const langStringPageTokenMobileStoresAppstore = useLangString("page_token.mobile_stores.appstore");
	const langStringPageTokenMobileStoresGooglePlay = useLangString("page_token.mobile_stores.google_play");
	const langStringPageTokenMobileStoresAppGallery = useLangString("page_token.mobile_stores.app_gallery");

	const onClickHandler = useCallback(() => {
		setStoreMenuOpen(true);
		return;
		if (useDeviceDetect().isMobileAndroid) {
			window.location.href = "https://play.google.com/store/apps/details?id=com.getcompass.android.enterprise";
			return;
		}

		if (useDeviceDetect().isMobileApple) {
			window.location.href = "https://apps.apple.com/ru/app/compass-on-premise/id6469516890";
			return;
		}

		if (useDeviceDetect().isMobileHuawei) {
			window.location.href = "https://appgallery.huawei.com/app/C109414583";
			return;
		}

		setStoreMenuOpen(true);
	}, [window.navigator.userAgent]);

	const onSelectHandler = useCallback((value: string) => {
		switch (value) {
			case "app_store":
				window.location.href = "https://apps.apple.com/ru/app/compass-on-premise/id6469516890";
				break;

			case "google_play":
				window.location.href =
					"https://play.google.com/store/apps/details?id=com.getcompass.android.enterprise";
				break;

			case "app_gallery":
				window.location.href = "https://appgallery.huawei.com/app/C109414583";
				break;

			default:
				break;
		}

		setTimeout(() => setStoreMenuOpen(false), 500);
	}, []);

	return (
		<VStack gap="16px">
			<HStack gap="12px">
				<Text
					w="35px"
					h="35px"
					bgColor="05c46b"
					color="white"
					fs="20"
					lh="24"
					rounded="100%"
					flexShrink="0"
					textAlign="Center"
					pt="5px"
					font="regular"
				>
					2
				</Text>
				<Text color="white" fs="16" lh="22" font="regular">
					<styled.span fontFamily="lato_semibold" fontWeight="700">
						{langStringPageTokenStep2DescPt1Mobile}
					</styled.span>
					{langStringPageTokenStep2DescPt2Mobile}
				</Text>
			</HStack>
			<Menu
				isOpen={isStoreMenuOpen}
				onSelect={({ value }) => onSelectHandler(value)}
				positioning={{ placement: "top", offset: { mainAxis: 10 } }}
			>
				<VStack w="100%" gap="0px">
					<MenuTrigger
						style={{
							height: "0px",
							opacity: "0%",
						}}
					/>
					<Button
						size="full"
						color="05c46b"
						onClick={(event) => {
							event.stopPropagation();
							onClickHandler();
						}}
					>
						{langStringPageTokenStep2ButtonMobile}
					</Button>
				</VStack>
				<Portal>
					<MenuPositioner
						style={{
							// @ts-ignore
							"--positioner-width": `${childBlockWidth}px`,
							zIndex: "999999",
						}}
					>
						<MenuContent>
							<MenuItemGroup id="stores">
								<MenuItem id="app_store">
									<HStack gap="12px">
										<AppStoreIcon />
										<Text fs="18" lh="27" color="333e49" opacity="80%" font="regular">
											{langStringPageTokenMobileStoresAppstore}
										</Text>
									</HStack>
								</MenuItem>
								<MenuItem id="google_play">
									<HStack gap="12px">
										<GooglePlayIcon />
										<Text fs="18" lh="27" color="333e49" opacity="80%" font="regular">
											{langStringPageTokenMobileStoresGooglePlay}
										</Text>
									</HStack>
								</MenuItem>
								<MenuItem id="app_gallery">
									<HStack gap="12px">
										<AppGalleryIcon />
										<Text fs="18" lh="27" color="333e49" opacity="80%" font="regular">
											{langStringPageTokenMobileStoresAppGallery}
										</Text>
									</HStack>
								</MenuItem>
							</MenuItemGroup>
						</MenuContent>
					</MenuPositioner>
				</Portal>
			</Menu>
		</VStack>
	);
};

type StepOneContentProps = {
	scrollableParentBlockRef: any;
	parentBlockRef: any;
	setStoreMenuOpen: (value: boolean) => void;
};

const StepOneContent = ({ scrollableParentBlockRef, parentBlockRef, setStoreMenuOpen }: StepOneContentProps) => {
	const langStringPageTokenStep1RegisterDescPt1 = useLangString("page_token.step_1.register_desc_pt1");
	const langStringPageTokenStep1RegisterDescPt2 = useLangString("page_token.step_1.register_desc_pt2");
	const langStringPageTokenStep1RegisterButton = useLangString("page_token.step_1.register_button");
	const langStringPageTokenStep1LoginDescPt1 = useLangString("page_token.step_1.login_desc_pt1");
	const langStringPageTokenStep1LoginDescPt2 = useLangString("page_token.step_1.login_desc_pt2");
	const langStringPageTokenStep1LoginButton = useLangString("page_token.step_1.login_button");

	const copyButtonRef = useRef<HTMLButtonElement>(null);

	const joinLink = useAtomValue(joinLinkState);
	const isRegistration = useAtomValue(isRegistrationState);

	// приходится извращаться с этой кнопкой на мобилках, иначе не на всех мобилках работает ховер на этой кнопке
	useEffect(() => {
		if (copyButtonRef.current) {
			copyButtonRef.current.addEventListener(
				"touchstart",
				function () {
					this.style.backgroundColor = "#0066d6";
				},
				false
			);

			copyButtonRef.current.addEventListener(
				"touchend",
				function () {
					this.style.backgroundColor = "#007aff";
				},
				false
			);
		}
	}, [copyButtonRef]);

	const apiAuthGenerateToken = useApiAuthGenerateToken(joinLink === null ? undefined : joinLink.join_link_uniq);

	return (
		<VStack gap="16px">
			<HStack gap="12px">
				<Text
					w="35px"
					h="35px"
					bgColor="007aff"
					color="white"
					fs="20"
					lh="24"
					rounded="100%"
					flexShrink="0"
					textAlign="Center"
					pt="5px"
					font="regular"
				>
					1
				</Text>
				<Text color="white" fs="16" lh="22" font="regular">
					<styled.span fontFamily="lato_semibold" fontWeight="700">
						{isRegistration
							? langStringPageTokenStep1RegisterDescPt1
							: langStringPageTokenStep1LoginDescPt1}
					</styled.span>
					{isRegistration ? langStringPageTokenStep1RegisterDescPt2 : langStringPageTokenStep1LoginDescPt2}
				</Text>
			</HStack>
			{apiAuthGenerateToken.isLoading || !apiAuthGenerateToken.data ? (
				<VStack w="100%" bgColor="000000.01" rounded="8px" px="12px" py="12px" gap="4px" ref={parentBlockRef}>
					<Box w="100%" h="19px" bgColor="434455" rounded="3px" />
					<Box w="100%" h="19px" bgColor="434455" rounded="3px" />
					<Box w="100%" h="19px" bgColor="434455" rounded="3px" />
				</VStack>
			) : (
				<Box ref={parentBlockRef} w="100%" bgColor="000000.01" rounded="8px" px="8px" py="12px">
					<Text
						overflow="breakWord"
						color="f8f8f8"
						opacity="50%"
						fs="16"
						lh="22"
						font="regular"
						onClick={() => {
							if (parentBlockRef.current) {
								copyToClipboard(
									apiAuthGenerateToken.data.authentication_token,
									scrollableParentBlockRef.current,
									parentBlockRef.current
								);
							}
						}}
					>
						{apiAuthGenerateToken.data.authentication_token.substring(0, 80)}
					</Text>
				</Box>
			)}
			<Button
				ref={copyButtonRef}
				size="full"
				onClick={(event) => {
					if (isRegistration) {
						if (!apiAuthGenerateToken.data || !parentBlockRef.current) {
							return;
						}

						copyToClipboard(
							apiAuthGenerateToken.data.authentication_token,
							scrollableParentBlockRef.current,
							parentBlockRef.current
						);
						return;
					} else {
						event.stopPropagation();
						window.location.replace(`getcompassonpremise://`);
						setStoreMenuOpen(true);
					}
				}}
			>
				{apiAuthGenerateToken.isLoading || !apiAuthGenerateToken.data ? (
					<Preloader20 />
				) : isRegistration ? (
					langStringPageTokenStep1RegisterButton
				) : (
					langStringPageTokenStep1LoginButton
				)}
			</Button>
		</VStack>
	);
};

const PageTokenMobile = () => {
	const langStringPageTokenTitle = useLangString("page_token.title");
	const langStringPageTokenDesc = useLangString("page_token.desc");
	const langStringCreateNewPasswordDialogSuccessTooltipMessage = useLangString(
		"create_new_password_dialog.success_tooltip_message"
	);

	const scrollableParentBlockRef = useRef(null);
	const parentBlockRef = useRef(null);
	const [childBlockWidth, setChildBlockWidth] = useState(0);
	const setAuthInput = useSetAtom(authInputState);
	const setPasswordInput = useSetAtom(passwordInputState);
	const setConfirmPassword = useSetAtom(confirmPasswordState);
	const setNameInput = useSetAtom(nameInputState);
	const setAuth = useSetAtom(authState);
	const [isPasswordChanged, setIsPasswordChanged] = useAtom(isPasswordChangedState);
	const [isStoreMenuOpen, setStoreMenuOpen] = useState(false);

	const isJoinLink = useIsJoinLink();
	const joinLink = useAtomValue(joinLinkState);
	const prepareJoinLinkError = useAtomValue(prepareJoinLinkErrorState);
	const apiJoinLinkAccept = useApiJoinLinkAccept();
	const pageToastConfig = useToastConfig("page_token");
	const pageShowToast = useShowToast("page_token");

	// очищаем из local storage
	useEffect(() => {
		setAuthInput("");
		setPasswordInput("");
		setConfirmPassword("");
		setNameInput("");
		setAuth(null);
	}, []);

	useEffect(() => {
		if (!isPasswordChanged) {
			return;
		}

		pageShowToast(langStringCreateNewPasswordDialogSuccessTooltipMessage, "success");
		setIsPasswordChanged(false);
	}, [isPasswordChanged]);

	useEffect(() => {
		if (parentBlockRef.current) {
			const { offsetWidth } = parentBlockRef.current;
			setChildBlockWidth(offsetWidth);
		}
	}, [parentBlockRef.current]);

	useEffect(() => {
		// если это не join ссылка
		if (!isJoinLink) {
			return;
		}

		// если нет инфы по ссылке
		if (joinLink === null || prepareJoinLinkError !== null) {
			return;
		}

		// если уже отправили заявку на постмодерацию - повторно не кидаем
		if (joinLink.is_waiting_for_postmoderation === 1) {
			return;
		}

		apiJoinLinkAccept.mutateAsync({ join_link_uniq: joinLink.join_link_uniq });
	}, []);

	return (
		<>
			<Toast toastConfig={pageToastConfig} />
			<VStack
				ref={scrollableParentBlockRef}
				gap="8px"
				py="16px"
				px="16px"
				maxWidth="100vw"
				width="100%"
				h="100%"
				className="invisible-scrollbar"
				position="relative"
				onClick={() => setStoreMenuOpen(false)}
			>
				<HStack w="100%" justify="end">
					<LogoutButtonMobile />
				</HStack>
				<VStack w="100%" gap="32px" userSelect="none" px="8px">
					<VStack w="100%">
						<VStack w="100%" gap="24px">
							<IconLogo />
							<VStack w="100%" alignItems="center" gap="4px">
								<Text w="100%" textAlign="center" fs="20" lh="28" color="white" font="bold" ls="-03">
									{langStringPageTokenTitle}
								</Text>
								<Text w="100%" textAlign="center" fs="16" lh="22" color="white" font="regular">
									{langStringPageTokenDesc}
								</Text>
							</VStack>
						</VStack>
					</VStack>
					<VStack w="100%" gap="16px">
						<Box w="100%" bgColor="434455" p="16px" rounded="12px">
							<StepOneContent
								scrollableParentBlockRef={scrollableParentBlockRef}
								parentBlockRef={parentBlockRef}
								setStoreMenuOpen={setStoreMenuOpen}
							/>
						</Box>
						<Box w="100%" bgColor="434455" p="16px" rounded="12px">
							<StepTwoContent
								childBlockWidth={childBlockWidth}
								isStoreMenuOpen={isStoreMenuOpen}
								setStoreMenuOpen={setStoreMenuOpen}
							/>
						</Box>
					</VStack>
				</VStack>
			</VStack>
		</>
	);
};

export default PageTokenMobile;
