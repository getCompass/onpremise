import { Box, Center, HStack, styled, VStack } from "../../styled-system/jsx";
import IconLogo from "../components/IconLogo.tsx";
import { Text } from "../components/text.tsx";
import { Button } from "../components/button.tsx";
import { useLangString } from "../lib/getLangString.ts";
import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import { Portal } from "@ark-ui/react";
import {
	Menu,
	MenuArrow,
	MenuArrowTip,
	MenuContent,
	MenuItem,
	MenuItemGroup,
	MenuPositioner,
	MenuTrigger,
} from "../components/menu.tsx";
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
import { css } from "../../styled-system/css";
import { useApiAuthGenerateToken, useApiAuthLogout } from "../api/auth.ts";
import { NetworkError, ServerError } from "../api/_index.ts";
import {
	Dialog,
	DialogBackdrop,
	DialogCloseTrigger,
	DialogContainer,
	DialogContent,
	DialogTrigger,
	generateDialogId,
} from "../components/dialog.tsx";
import { copyToClipboard } from "../lib/copyToClipboard.ts";
import { useApiJoinLinkAccept } from "../api/joinlink.ts";
import useIsJoinLink from "../lib/useIsJoinLink.ts";
import Toast, { useShowToast } from "../lib/Toast.tsx";
import Preloader16 from "../components/Preloader16.tsx";
import { useAtom } from "jotai/index";

export const MacOsIcon = () => {
	return (
		<Box w="18px" h="18px">
			<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path
					d="M12.6722 9.08267C12.6722 8.07344 13.1359 7.33698 14.036 6.76417C13.5178 6.02771 12.754 5.64584 11.7448 5.56401C10.7629 5.48218 9.69908 6.10954 9.31721 6.10954C8.90807 6.10954 7.98067 5.59129 7.24421 5.59129C5.71673 5.61857 4.10742 6.79145 4.10742 9.21905C4.10742 9.92824 4.21653 10.6647 4.48929 11.4284C4.84388 12.4377 6.0986 14.8925 7.40786 14.838C8.08977 14.838 8.58075 14.347 9.48087 14.347C10.3537 14.347 10.7901 14.838 11.5539 14.838C12.8904 14.838 14.036 12.6013 14.3633 11.5921C12.5904 10.7465 12.6722 9.13722 12.6722 9.08267ZM11.1447 4.60934C11.8812 3.7365 11.7994 2.9182 11.7994 2.61816C11.1447 2.67272 10.381 3.08186 9.94457 3.57284C9.45359 4.11837 9.18083 4.80027 9.23538 5.53674C9.94457 5.59129 10.5992 5.2367 11.1447 4.60934Z"
					fill="#DCDCDC"
				/>
			</svg>
		</Box>
	);
};

export const WindowsIcon = () => {
	return (
		<Box w="18px" h="18px">
			<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path
					d="M3.28613 4.97825V9.12426H8.27771V4.29634L3.28613 4.97825ZM3.28613 13.8431L8.27771 14.525V9.75161H3.28613V13.8431ZM8.82324 14.6068L15.506 15.5069V9.75161H8.82324V14.6068ZM8.82324 4.21451V9.12426H15.506V3.28711L8.82324 4.21451Z"
					fill="#DCDCDC"
				/>
			</svg>
		</Box>
	);
};

export const LinuxDebIcon = () => {
	return (
		<Box w="18px" h="18px">
			<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path
					d="M9.22939 2.46399C5.49252 2.46399 2.46484 5.49167 2.46484 9.22853C2.46484 12.9654 5.49252 15.9931 9.22939 15.9931C12.9663 15.9931 15.9939 12.9654 15.9939 9.22853C15.9939 5.49167 12.9663 2.46399 9.22939 2.46399ZM10.6478 5.00069C10.8932 4.59155 11.4388 4.45517 11.8479 4.70065C12.2571 4.94614 12.3934 5.46439 12.148 5.87354C11.9297 6.30996 11.3842 6.44634 10.9751 6.20085C10.5659 5.95537 10.4023 5.43711 10.6478 5.00069ZM4.83789 10.1014C4.34691 10.1014 3.96504 9.71951 3.96504 9.22853C3.96504 8.76483 4.34691 8.38296 4.83789 8.38296C5.32886 8.38296 5.71073 8.76483 5.71073 9.22853C5.71073 9.71951 5.32886 10.1014 4.83789 10.1014ZM5.60163 10.1832C6.20171 9.71951 6.20171 8.79211 5.60163 8.30114C5.84712 7.40101 6.39264 6.63728 7.15638 6.11902L7.78374 7.21008C6.39264 8.19203 6.39264 10.2923 7.78374 11.2743L7.15638 12.338C6.39264 11.8471 5.84712 11.0833 5.60163 10.1832ZM11.8479 13.7837C11.4115 14.0292 10.8932 13.8928 10.6478 13.4564C10.4023 13.0472 10.5659 12.529 10.9751 12.2835C11.3842 12.038 11.9297 12.1744 12.148 12.6108C12.3934 13.0199 12.2571 13.5382 11.8479 13.7837ZM11.8479 11.9016C11.1115 11.6016 10.3204 12.0653 10.2113 12.8563C10.0477 12.8836 8.87479 13.2382 7.56553 12.5835L8.16561 11.4925C9.72036 12.2017 11.5479 11.1652 11.6843 9.47402H12.939C12.8844 10.4287 12.4753 11.2743 11.8479 11.9016ZM11.6843 9.01032C11.5479 7.31919 9.74764 6.25541 8.16561 6.99187L7.56553 5.90081C8.87479 5.24618 10.0477 5.60077 10.1841 5.62805C10.3204 6.41906 11.1115 6.88276 11.8479 6.58272C12.4753 7.21008 12.8844 8.05565 12.939 9.01032H11.6843Z"
					fill="#DCDCDC"
				/>
			</svg>
		</Box>
	);
};

export const LinuxTarIcon = () => {
	return (
		<Box w="18px" h="18px">
			<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path
					d="M8.22085 4.72657C8.27418 4.70657 8.34751 4.69991 8.39418 4.72657C8.40751 4.73324 8.42084 4.74657 8.41418 4.75991V4.77324H8.42084C8.40751 4.81324 8.34085 4.80657 8.30751 4.8199C8.27418 4.83324 8.24751 4.86657 8.21418 4.86657C8.18085 4.86657 8.12752 4.85324 8.12085 4.8199C8.11418 4.77324 8.18085 4.72657 8.22085 4.72657ZM8.90084 4.8199C8.8675 4.80657 8.80084 4.81324 8.79417 4.77324V4.75991C8.7875 4.74657 8.80084 4.73324 8.8075 4.72657C8.86523 4.70322 8.92977 4.70322 8.9875 4.72657C9.0275 4.72657 9.09417 4.77324 9.0875 4.8199C9.08083 4.85324 9.0275 4.8599 8.99417 4.8599C8.96083 4.8599 8.93417 4.83324 8.90084 4.8199ZM7.72752 5.78655C7.92752 5.91988 8.18751 6.09988 8.51418 6.09988C8.84084 6.09988 9.2275 5.91988 9.46082 5.78655C9.58749 5.69989 9.76082 5.56656 9.89415 5.47323C10.0008 5.38656 9.99415 5.2999 10.0875 5.2999C10.1741 5.30656 10.1075 5.38656 9.98748 5.51989C9.86082 5.60656 9.67415 5.73989 9.52082 5.83322C9.2275 5.96655 8.88084 6.18655 8.51418 6.18655C8.15418 6.18655 7.86085 6.00655 7.65419 5.87322C7.54753 5.78655 7.46753 5.69989 7.40086 5.65322C7.2942 5.56656 7.30753 5.43323 7.3542 5.43323C7.42753 5.43989 7.44086 5.51989 7.48753 5.56656C7.55419 5.60656 7.63419 5.69989 7.72752 5.78655Z"
					fill="white"
				/>
				<path
					d="M9.08714 4.81995C9.08047 4.85328 9.02714 4.85995 8.99381 4.85995C8.96047 4.85995 8.93381 4.83328 8.90047 4.81995C8.86714 4.80662 8.80048 4.81329 8.79381 4.77329V4.75995C8.78714 4.74662 8.80048 4.73329 8.80714 4.72662C8.86487 4.70327 8.92941 4.70327 8.98714 4.72662C9.02714 4.72662 9.0938 4.77329 9.08714 4.81995Z"
					fill="#B4B4B4"
				/>
				<path
					d="M12.087 8.31987V8.93319H11.7204C12.0533 9.54012 12.2916 10.1943 12.427 10.8732C12.527 10.8665 12.647 10.8865 12.7737 10.9132C12.827 10.7732 12.8603 10.6332 12.8803 10.4932C13.007 9.56651 12.4203 8.65986 12.087 8.31987ZM12.087 8.31987V8.93319H11.7204C12.0533 9.54012 12.2916 10.1943 12.427 10.8732C12.527 10.8665 12.647 10.8865 12.7737 10.9132C12.827 10.7732 12.8603 10.6332 12.8803 10.4932C13.007 9.56651 12.4203 8.65986 12.087 8.31987ZM15.187 13.6331V13.5731L15.1803 13.5664C15.067 13.4331 15.0136 13.2131 14.9536 12.9531C14.9003 12.6865 14.8336 12.4265 14.6203 12.2531C14.5803 12.2198 14.5403 12.2131 14.4936 12.1665C14.4556 12.1412 14.4123 12.1252 14.367 12.1198C14.5536 11.5731 14.5736 11.0198 14.4803 10.4932C14.4336 10.2065 14.3536 9.92651 14.247 9.65984C13.887 8.71986 13.2603 7.89988 12.7803 7.33989C12.5337 7.02656 12.2804 6.71323 12.087 6.3799C11.867 5.99991 11.7204 5.59325 11.727 5.09326C11.7404 4.16661 11.8004 2.71997 11.147 1.81999C10.8004 1.33333 10.2337 1 9.3404 1C9.23374 1 9.12707 1.00667 9.01374 1.01333C8.16709 1.08 7.64043 1.38666 7.31377 1.81999C6.54712 2.8333 6.90711 4.51327 6.88045 5.21326C6.82712 5.93991 6.68045 6.51323 6.17379 7.22655C5.96713 7.46655 5.72713 7.75988 5.49381 8.09321C5.04715 8.71986 4.60049 9.46651 4.34049 10.2398C4.17383 10.7398 4.0805 11.2531 4.12716 11.7465L4.14716 11.8998C4.1205 11.9198 4.09383 11.9465 4.07383 11.9865C3.89383 12.1665 3.76717 12.3865 3.62717 12.5465C3.48717 12.6798 3.30051 12.7265 3.08718 12.8131C2.87385 12.9065 2.64052 12.9931 2.50719 13.2664C2.44053 13.3931 2.41386 13.5331 2.41386 13.6664C2.41386 13.7998 2.43386 13.9398 2.45386 14.0264C2.49386 14.2931 2.53386 14.5131 2.48053 14.6731C2.31386 15.1264 2.29386 15.4397 2.40719 15.6664C2.52719 15.8864 2.76719 15.9731 3.04052 16.0664C3.58717 16.1997 4.32716 16.1531 4.91382 16.4664C5.53381 16.773 6.17379 16.913 6.67379 16.7797C7.03378 16.6997 7.33377 16.4664 7.49377 16.1464C7.5271 16.1464 7.55377 16.1464 7.5871 16.1397C7.96043 16.1197 8.37375 15.9664 9.01374 15.9264C9.19374 15.9131 9.38707 15.9331 9.6004 15.9597C9.94039 16.0131 10.3271 16.0864 10.7537 16.0597C10.767 16.1464 10.7937 16.1864 10.827 16.2797H10.8337C11.0937 16.7997 11.5804 17.033 12.1004 16.993C12.6203 16.953 13.1737 16.6397 13.627 16.1264C14.047 15.6131 14.7603 15.3997 15.227 15.1197C15.4603 14.9931 15.6536 14.8131 15.667 14.5531C15.6803 14.2864 15.5336 14.0131 15.187 13.6331ZM10.0004 4.09328C9.97373 4.00661 9.93373 3.95995 9.88039 3.87328C9.82039 3.82662 9.76706 3.77995 9.7004 3.77995H9.68706C9.62707 3.77995 9.56707 3.79995 9.51373 3.87328C9.44707 3.93328 9.4004 4.00661 9.37374 4.09328C9.3404 4.17994 9.3204 4.26661 9.31374 4.35994V4.37327C9.31374 4.43327 9.3204 4.49327 9.32707 4.55327C9.19374 4.5066 9.03374 4.45994 8.92041 4.41327C8.91374 4.37327 8.90708 4.32661 8.90708 4.28661V4.27327C8.90041 4.09328 8.93374 3.91995 9.00708 3.75995C9.06041 3.61329 9.16041 3.48662 9.29374 3.39996C9.41374 3.31329 9.55373 3.26663 9.69373 3.26663H9.70706C9.84706 3.26663 9.97373 3.31329 10.1004 3.39996C10.2271 3.49329 10.3204 3.61995 10.3937 3.75995C10.4671 3.92661 10.5004 4.05994 10.507 4.23994C10.507 4.22661 10.5137 4.21328 10.5137 4.19994V4.26661C10.5071 4.26661 10.507 4.25994 10.507 4.25327C10.507 4.40661 10.4671 4.5666 10.4071 4.71327C10.3737 4.79327 10.3271 4.87326 10.2604 4.93326C10.2404 4.91993 10.2204 4.91326 10.2004 4.9066C10.1337 4.87326 10.0671 4.85993 10.0071 4.81993C9.96066 4.79744 9.91126 4.78172 9.86039 4.77327C9.89373 4.73327 9.96039 4.6866 9.98706 4.63993C10.0204 4.55327 10.0404 4.4666 10.0471 4.37327V4.35994C10.0471 4.27327 10.0337 4.17994 10.0004 4.09328ZM7.22711 3.79995C7.26044 3.66662 7.32711 3.54662 7.42044 3.43996C7.5071 3.35329 7.59377 3.30663 7.70043 3.30663H7.72043C7.81912 3.30805 7.91481 3.34072 7.99376 3.39996C8.09376 3.48662 8.17376 3.59329 8.22709 3.70662C8.28709 3.83995 8.32042 3.97328 8.32709 4.15328C8.33376 4.19994 8.33376 4.23328 8.33376 4.26661C8.33376 4.29327 8.33376 4.31327 8.32709 4.33327V4.38661C8.30998 4.39341 8.29205 4.39789 8.27376 4.39994C8.16709 4.43994 8.08709 4.49327 8.00709 4.53327C8.01376 4.47994 8.01376 4.41994 8.00709 4.35994V4.34661C8.00043 4.25994 7.98043 4.21328 7.95376 4.12661C7.93153 4.05813 7.89258 3.99626 7.84043 3.94661C7.82306 3.93182 7.80291 3.92066 7.78115 3.91379C7.75939 3.90691 7.73648 3.90448 7.71377 3.90661H7.70043C7.65377 3.90661 7.61377 3.93328 7.58044 3.99328C7.53377 4.04661 7.5071 4.10661 7.49377 4.17328C7.48044 4.24661 7.47377 4.31994 7.48044 4.39327V4.40661C7.4871 4.49327 7.5071 4.53994 7.53377 4.6266C7.5671 4.71327 7.60044 4.75993 7.6471 4.8066C7.65377 4.81326 7.66043 4.81993 7.6671 4.81993C7.62043 4.85993 7.59377 4.8666 7.55377 4.91326C7.52634 4.9353 7.49452 4.95121 7.46044 4.95993C7.39377 4.87326 7.33377 4.7866 7.28044 4.6866C7.21378 4.5466 7.18044 4.39994 7.17378 4.24661C7.16044 4.09328 7.18044 3.93995 7.22711 3.79995ZM7.30044 5.20659C7.4471 5.11326 7.55377 5.01993 7.6271 4.97993C7.69377 4.93326 7.72043 4.91326 7.74043 4.89326H7.7471C7.86043 4.75327 8.04043 4.57994 8.30709 4.4866C8.40042 4.4666 8.50709 4.4466 8.62708 4.4466C8.84708 4.4466 9.12041 4.4866 9.4404 4.71327C9.6404 4.8466 9.79373 4.89326 10.1537 5.02659C10.3271 5.11326 10.4271 5.19992 10.4804 5.29326V5.19992C10.5271 5.29992 10.527 5.41325 10.4871 5.51325C10.4071 5.72658 10.1404 5.94658 9.77373 6.07991C9.58707 6.16657 9.43374 6.2999 9.24707 6.38657C9.06041 6.4799 8.85375 6.58657 8.56708 6.56657C8.4648 6.57504 8.36197 6.55904 8.26709 6.5199C8.18709 6.4799 8.11376 6.4399 8.04709 6.39324C7.91376 6.2999 7.80043 6.16657 7.63377 6.07991C7.36044 5.91324 7.21378 5.73325 7.16711 5.60658C7.12044 5.42659 7.16711 5.29326 7.30044 5.20659ZM7.4071 15.6464C7.36711 16.1397 7.08044 16.4064 6.64712 16.5064C6.20713 16.5931 5.62047 16.5064 5.02715 16.1997C4.38049 15.8397 3.60051 15.8864 3.10718 15.7931C2.85385 15.7531 2.69386 15.6597 2.61386 15.5264C2.54053 15.3931 2.54052 15.1264 2.70052 14.7064C2.78052 14.4798 2.72052 14.1998 2.68052 13.9598C2.64719 13.6931 2.62719 13.4864 2.71386 13.3331C2.82052 13.1064 2.98052 13.0664 3.17385 12.9731C3.37384 12.8865 3.60717 12.8398 3.79384 12.6665V12.6598C3.96717 12.4798 4.09383 12.2598 4.24716 12.0998C4.37383 11.9665 4.50049 11.8798 4.69382 11.8798H4.70049C4.73382 11.8798 4.76715 11.8798 4.80715 11.8865C5.06048 11.9265 5.28048 12.1131 5.49381 12.3865L6.10713 13.4998H6.1138C6.27379 13.8531 6.62045 14.2131 6.91378 14.5931C7.20711 14.9931 7.43377 15.3464 7.4071 15.6397V15.6464ZM9.6004 15.2197C8.94041 15.3731 8.24042 15.3264 7.5871 14.9664C7.5071 14.9264 7.4271 14.8797 7.35377 14.8264C7.28044 14.6998 7.18711 14.5798 7.08044 14.4731C7.03138 14.3895 6.96823 14.3151 6.89378 14.2531C7.02045 14.2531 7.12711 14.2331 7.21378 14.2064C7.30711 14.1598 7.38711 14.0798 7.42044 13.9864C7.49377 13.8064 7.42044 13.5198 7.18711 13.2064C6.95378 12.8998 6.56045 12.5465 5.98713 12.1931C5.76713 12.0531 5.60047 11.9065 5.47381 11.7465C5.34714 11.5931 5.26048 11.4331 5.20714 11.2665C5.10048 10.9065 5.11381 10.5398 5.20048 10.1665C5.30048 9.72651 5.50047 9.29985 5.6938 8.93319C5.82713 8.69986 5.9538 8.4932 6.06046 8.32653C6.12713 8.27987 6.08046 8.40653 5.80047 8.93319L5.78047 8.97319C5.51381 9.47318 5.01381 10.6398 5.70047 11.5465C5.72714 10.8865 5.8738 10.2332 6.13379 9.62651C6.22046 9.42651 6.33379 9.19319 6.45379 8.93319C6.84712 8.09987 7.32711 7.01989 7.37377 6.11324C7.40711 6.13991 7.52044 6.20657 7.5671 6.24657C7.71377 6.3399 7.8271 6.47323 7.96709 6.5599C8.10709 6.69323 8.28709 6.7799 8.56042 6.7799C8.58708 6.78656 8.60708 6.78656 8.63375 6.78656C8.90708 6.78656 9.12707 6.6999 9.30707 6.60657C9.5004 6.5199 9.65373 6.38657 9.8004 6.3399H9.80706C10.1204 6.25324 10.3671 6.07324 10.5137 5.87325C10.7537 6.81989 11.3204 8.1932 11.6804 8.85986C12.0343 9.48719 12.2863 10.1668 12.427 10.8732C12.527 10.8665 12.647 10.8865 12.7737 10.9132C12.827 10.7732 12.8603 10.6332 12.8803 10.4932C13.007 9.56651 12.4203 8.65986 12.087 8.31987C12.067 8.30653 12.047 8.28654 12.0337 8.2732C11.887 8.13987 11.8804 8.04654 11.9537 8.04654C12.0004 8.08654 12.0404 8.12654 12.087 8.1732C12.4603 8.55986 12.8937 9.17985 13.0603 9.88651C13.1137 10.0798 13.1403 10.2865 13.1403 10.4932C13.1403 10.6598 13.1203 10.8332 13.0803 10.9998C13.1203 11.0198 13.167 11.0398 13.2137 11.0465C13.9137 11.3998 14.167 11.6665 14.047 12.0665V12.0398H13.9137C14.0137 11.7265 13.7937 11.4865 13.1937 11.2265C12.5803 10.9598 12.087 10.9998 12.0004 11.5331C11.9937 11.5598 11.9937 11.5798 11.987 11.6265C11.9404 11.6398 11.8937 11.6598 11.847 11.6665C11.5604 11.8465 11.4004 12.1131 11.3137 12.4598C11.227 12.8131 11.2004 13.2264 11.1737 13.7064C11.1604 13.9264 11.0604 14.2664 10.9604 14.6064C10.5537 14.8931 10.0937 15.1131 9.6004 15.2197ZM15.1336 14.8797C14.7136 15.1464 13.9603 15.3531 13.4803 15.9264C13.0603 16.4197 12.5537 16.6864 12.107 16.7197C11.6604 16.753 11.2737 16.5864 11.047 16.1197H11.0404C10.9004 15.8531 10.9604 15.4331 11.0804 14.9931C11.2004 14.5464 11.367 14.0931 11.3937 13.7264C11.4137 13.2531 11.4404 12.8398 11.5204 12.5198C11.607 12.2065 11.7337 11.9865 11.9537 11.8598L11.987 11.8465C12.007 12.2465 12.2137 12.6731 12.5803 12.7665C12.9737 12.8531 13.547 12.5398 13.787 12.2531L13.927 12.2465C14.1403 12.2398 14.3203 12.2531 14.5003 12.4265C14.6403 12.5598 14.707 12.7798 14.767 13.0131C14.827 13.2798 14.8736 13.5331 15.0403 13.7198C15.3736 14.0731 15.4803 14.3264 15.4736 14.4798C15.4603 14.6598 15.347 14.7464 15.1336 14.8797ZM12.087 8.31987V8.93319H11.7204C12.0533 9.54012 12.2916 10.1943 12.427 10.8732C12.527 10.8665 12.647 10.8865 12.7737 10.9132C12.827 10.7732 12.8603 10.6332 12.8803 10.4932C13.007 9.56651 12.4203 8.65986 12.087 8.31987Z"
					fill="#B4B4B4"
				/>
				<path
					d="M10.4802 5.19985V5.29318C10.4269 5.19985 10.3269 5.11319 10.1535 5.02652C9.79353 4.89319 9.6402 4.84653 9.44021 4.7132C9.12021 4.48653 8.84688 4.44653 8.62689 4.44653C8.50689 4.44653 8.40023 4.46653 8.30689 4.48653C8.04023 4.57986 7.86024 4.75319 7.7469 4.89319H7.74024C7.72024 4.91319 7.69357 4.93319 7.62691 4.97986C7.55357 5.01986 7.44691 5.11319 7.30025 5.20652C7.16691 5.29318 7.12025 5.42652 7.16691 5.60651C7.21358 5.73318 7.36024 5.91317 7.63357 6.07984C7.80024 6.1665 7.91357 6.29983 8.0469 6.39317C8.11356 6.43983 8.1869 6.47983 8.26689 6.51983C8.36023 6.55983 8.46023 6.57316 8.56689 6.5665C8.85355 6.5865 9.06021 6.47983 9.24688 6.3865C9.43354 6.29983 9.58687 6.1665 9.77353 6.07984C10.1402 5.94651 10.4069 5.72651 10.4869 5.51318C10.5269 5.41318 10.5269 5.29985 10.4802 5.19985ZM8.79355 4.75986C8.78689 4.74653 8.80022 4.73319 8.80689 4.72653C8.86461 4.70318 8.92915 4.70318 8.98688 4.72653C9.02688 4.72653 9.09355 4.77319 9.08688 4.81986C9.08021 4.85319 9.02688 4.85986 8.99355 4.85986C8.96022 4.85986 8.93355 4.83319 8.90022 4.81986C8.86688 4.80653 8.80022 4.81319 8.79355 4.77319V4.75986ZM8.22023 4.72653C8.27356 4.70653 8.34689 4.69986 8.39356 4.72653C8.40689 4.73319 8.42023 4.74653 8.41356 4.75986V4.77319H8.42023C8.40689 4.81319 8.34023 4.80653 8.30689 4.81986C8.27356 4.83319 8.2469 4.86653 8.21356 4.86653C8.18023 4.86653 8.1269 4.85319 8.12023 4.81986C8.11356 4.77319 8.18023 4.72653 8.22023 4.72653ZM9.98686 5.51985C9.8602 5.60651 9.67354 5.73984 9.52021 5.83318C9.22688 5.96651 8.88022 6.1865 8.51356 6.1865C8.15356 6.1865 7.86024 6.00651 7.65357 5.87317C7.54691 5.78651 7.46691 5.69984 7.40024 5.65318C7.29358 5.56651 7.30691 5.43318 7.35358 5.43318C7.42691 5.43985 7.44024 5.51985 7.48691 5.56651C7.55357 5.60651 7.63357 5.69984 7.7269 5.78651C7.9269 5.91984 8.1869 6.09984 8.51356 6.09984C8.84022 6.09984 9.22688 5.91984 9.46021 5.78651C9.58687 5.69984 9.7602 5.56651 9.89353 5.47318C10.0002 5.38652 9.99353 5.29985 10.0869 5.29985C10.1735 5.30652 10.1069 5.38652 9.98686 5.51985ZM15.4734 14.4797C15.4601 14.6597 15.3468 14.7463 15.1334 14.8797C14.7134 15.1463 13.9601 15.353 13.4801 15.9263C13.0601 16.4197 12.5535 16.6863 12.1068 16.7196C11.6602 16.753 11.2735 16.5863 11.0468 16.1197H11.0402C10.9002 15.853 10.9602 15.433 11.0802 14.993C11.2002 14.5464 11.3668 14.093 11.3935 13.7264C11.4135 13.253 11.4402 12.8397 11.5202 12.5197C11.6068 12.2064 11.7335 11.9864 11.9535 11.8597L11.9868 11.8464C12.0068 12.2464 12.2135 12.6731 12.5802 12.7664C12.9735 12.853 13.5468 12.5397 13.7868 12.2531L13.9268 12.2464C14.1401 12.2397 14.3201 12.2531 14.5001 12.4264C14.6401 12.5597 14.7068 12.7797 14.7668 13.013C14.8268 13.2797 14.8734 13.533 15.0401 13.7197C15.3734 14.073 15.4801 14.3264 15.4734 14.4797ZM7.40691 15.6397V15.6463C7.36691 16.1397 7.08025 16.4063 6.64692 16.5063C6.20693 16.593 5.62028 16.5063 5.02695 16.1997C4.3803 15.8397 3.60031 15.8863 3.10699 15.793C2.85366 15.753 2.69366 15.6597 2.61366 15.5263C2.54033 15.393 2.54033 15.1263 2.70033 14.7063C2.78033 14.4797 2.72033 14.1997 2.68033 13.9597C2.647 13.693 2.627 13.4864 2.71366 13.333C2.82033 13.1064 2.98032 13.0664 3.17365 12.973C3.37365 12.8864 3.60698 12.8397 3.79364 12.6664V12.6597C3.96697 12.4797 4.09364 12.2597 4.24697 12.0997C4.37363 11.9664 4.5003 11.8797 4.69363 11.8797H4.70029C4.73363 11.8797 4.76696 11.8797 4.80696 11.8864C5.06029 11.9264 5.28028 12.1131 5.49361 12.3864L6.10693 13.4997H6.1136C6.2736 13.853 6.62026 14.213 6.91359 14.593C7.20691 14.993 7.43358 15.3463 7.40691 15.6397Z"
					fill="white"
				/>
				<path
					d="M8.42084 4.77324C8.40751 4.81324 8.34085 4.80657 8.30751 4.8199C8.27418 4.83324 8.24751 4.86657 8.21418 4.86657C8.18085 4.86657 8.12752 4.85324 8.12085 4.8199C8.11418 4.77324 8.18085 4.72657 8.22085 4.72657C8.27418 4.70657 8.34751 4.69991 8.39418 4.72657C8.40751 4.73324 8.42084 4.74657 8.41418 4.75991V4.77324H8.42084ZM9.98748 5.51989C9.86082 5.60656 9.67415 5.73989 9.52082 5.83322C9.2275 5.96655 8.88084 6.18655 8.51418 6.18655C8.15418 6.18655 7.86085 6.00655 7.65419 5.87322C7.54753 5.78655 7.46753 5.69989 7.40086 5.65322C7.2942 5.56656 7.30753 5.43323 7.3542 5.43323C7.42753 5.43989 7.44086 5.51989 7.48753 5.56656C7.55419 5.60656 7.63419 5.69989 7.72752 5.78655C7.92752 5.91988 8.18751 6.09988 8.51418 6.09988C8.84084 6.09988 9.2275 5.91988 9.46082 5.78655C9.58749 5.69989 9.76082 5.56656 9.89415 5.47323C10.0008 5.38656 9.99415 5.2999 10.0875 5.2999C10.1741 5.30656 10.1075 5.38656 9.98748 5.51989Z"
					fill="#B4B4B4"
				/>
			</svg>
		</Box>
	);
};

type StepTwoContentProps = {
	childButtonWidth: number;
};

const StepTwoContent = ({ childButtonWidth }: StepTwoContentProps) => {
	const langStringPageTokenStep2DescPt1Desktop = useLangString("page_token.step_2.desc_pt1_desktop");
	const langStringPageTokenStep2DescPt2Desktop = useLangString("page_token.step_2.desc_pt2_desktop");
	const langStringPageTokenStep2ButtonDesktop = useLangString("page_token.step_2.button_desktop");
	const langStringPageTokenDesktopBuildsMacosDownload = useLangString("page_token.desktop_builds.macos_download");
	const langStringPageTokenDesktopBuildsIntelVersion = useLangString("page_token.desktop_builds.intel_version");
	const langStringPageTokenDesktopBuildsM1M2Version = useLangString("page_token.desktop_builds.m1m2_version");
	const langStringPageTokenDesktopBuildsWindowsDownload = useLangString("page_token.desktop_builds.windows_download");
	const langStringPageTokenDesktopBuildsLinuxDownload = useLangString("page_token.desktop_builds.linux_download");
	const langStringPageTokenDesktopBuildsDebVersion = useLangString("page_token.desktop_builds.deb_version");
	const langStringPageTokenDesktopBuildsTarVersion = useLangString("page_token.desktop_builds.tar_version");

	const [isStoreMenuOpen, setStoreMenuOpen] = useState(false);

	const onSelectHandler = useCallback((value: string) => {
		switch (value) {
			case "macos_intel":
				window.location.href = "https://update-onpremise.getcompass.ru/apps/compass-on-premise-mac.dmg";
				break;

			case "macos_m1m2":
				window.location.href = "https://update-onpremise.getcompass.ru/apps/compass-on-premise-mac-arm64.dmg";
				break;

			case "windows":
				window.location.href = "https://update-onpremise.getcompass.ru/apps/compass-on-premise-win.exe";
				break;

			case "linux_deb":
				window.location.href = "https://update-onpremise.getcompass.ru/apps/compass-on-premise-linux.deb";
				break;

			case "linux_tar":
				window.location.href = "https://update-onpremise.getcompass.ru/apps/compass-on-premise-linux.tar";
				break;

			default:
				break;
		}
	}, []);

	return (
		<HStack w="100%" gap="16px" justify="space-between">
			<HStack gap="12px" w="100%">
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
				<Text color="white" fs="14" lh="18" font="regular" ls="-015">
					<styled.span fontFamily="lato_bold" fontWeight="700">
						{langStringPageTokenStep2DescPt1Desktop}
					</styled.span>
					<br />
					{langStringPageTokenStep2DescPt2Desktop}
				</Text>
			</HStack>
			<Menu
				isOpen={isStoreMenuOpen}
				onSelect={({ value }) => onSelectHandler(value)}
				onClose={() => setStoreMenuOpen(false)}
				onFocusOutside={() => setStoreMenuOpen(false)}
				onInteractOutside={() => setStoreMenuOpen(false)}
				onPointerDownOutside={() => setStoreMenuOpen(false)}
				positioning={{ placement: "bottom", offset: { mainAxis: 7 } }}
				type="medium_desktop"
			>
				<VStack gap="0px">
					<MenuTrigger asChild>
						<Button
							size="px21py6"
							textSize="xl_desktop"
							color="05c46b"
							style={{
								minWidth: `${childButtonWidth}px`,
							}}
						>
							{langStringPageTokenStep2ButtonDesktop}
						</Button>
					</MenuTrigger>
				</VStack>
				<Portal>
					<MenuPositioner w="290px">
						<MenuContent>
							<MenuArrow
								className={css({
									"--arrow-size": "9px",
								})}
							>
								<MenuArrowTip
									className={css({
										"--arrow-background": "white",
									})}
								/>
							</MenuArrow>
							<MenuItemGroup id="macos_builds">
								<MenuItem id="macos_intel">
									<HStack w="100%" justifyContent="space-between">
										<HStack gap="8px">
											<MacOsIcon />
											<Text fs="15" lh="22" color="333e49" font="regular">
												{langStringPageTokenDesktopBuildsMacosDownload}
											</Text>
										</HStack>
										<Text
											px="8px"
											py="4px"
											bgColor="103115128.01"
											rounded="8px"
											color="677380"
											fs="15"
											lh="18"
											font="regular"
										>
											{langStringPageTokenDesktopBuildsIntelVersion}
										</Text>
									</HStack>
								</MenuItem>
								<MenuItem id="macos_m1m2">
									<HStack w="100%" justifyContent="space-between">
										<HStack gap="8px">
											<MacOsIcon />
											<Text fs="15" lh="22" color="333e49" font="regular">
												{langStringPageTokenDesktopBuildsMacosDownload}
											</Text>
										</HStack>
										<Text
											px="8px"
											py="4px"
											bgColor="103115128.01"
											rounded="8px"
											color="677380"
											fs="15"
											lh="18"
											font="regular"
										>
											{langStringPageTokenDesktopBuildsM1M2Version}
										</Text>
									</HStack>
								</MenuItem>
							</MenuItemGroup>
							<Box w="100%" px="24px" py="7px">
								<Box bgColor="f5f5f5" h="1px" w="100%" />
							</Box>
							<MenuItemGroup id="other_builds">
								<MenuItem id="windows">
									<HStack gap="8px">
										<WindowsIcon />
										<Text fs="15" lh="22" color="333e49" font="regular">
											{langStringPageTokenDesktopBuildsWindowsDownload}
										</Text>
									</HStack>
								</MenuItem>
								<MenuItem id="linux_deb">
									<HStack w="100%" justifyContent="space-between">
										<HStack gap="8px">
											<LinuxDebIcon />
											<Text fs="15" lh="22" color="333e49" font="regular">
												{langStringPageTokenDesktopBuildsLinuxDownload}
											</Text>
										</HStack>
										<Text
											px="8px"
											py="4px"
											bgColor="103115128.01"
											rounded="8px"
											color="677380"
											fs="15"
											lh="18"
											font="regular"
										>
											{langStringPageTokenDesktopBuildsDebVersion}
										</Text>
									</HStack>
								</MenuItem>
								<MenuItem id="linux_tar">
									<HStack w="100%" justifyContent="space-between">
										<HStack gap="8px">
											<LinuxTarIcon />
											<Text fs="15" lh="22" color="333e49" font="regular">
												{langStringPageTokenDesktopBuildsLinuxDownload}
											</Text>
										</HStack>
										<Text
											px="8px"
											py="4px"
											bgColor="103115128.01"
											rounded="8px"
											color="677380"
											fs="15"
											lh="18"
											font="regular"
										>
											{langStringPageTokenDesktopBuildsTarVersion}
										</Text>
									</HStack>
								</MenuItem>
							</MenuItemGroup>
						</MenuContent>
					</MenuPositioner>
				</Portal>
			</Menu>
		</HStack>
	);
};

type StepOneContentProps = {
	scrollableParentBlockRef: any;
	parentButtonRef: any;
};

const StepOneContent = ({ scrollableParentBlockRef, parentButtonRef }: StepOneContentProps) => {
	const langStringPageTokenLifeTimeDesktop = useLangString("token_life_time_desktop");
	const langStringPageTokenStep1RegisterDescPt1 = useLangString("page_token.step_1.register_desc_pt1");
	const langStringPageTokenStep1RegisterDescPt2 = useLangString("page_token.step_1.register_desc_pt2");
	const langStringPageTokenStep1RegisterButton = useLangString("page_token.step_1.register_button");
	const langStringPageTokenStep1LoginDescPt1 = useLangString("page_token.step_1.login_desc_pt1");
	const langStringPageTokenStep1LoginDescPt2Desktop = useLangString("page_token.step_1.login_desc_pt2_desktop");
	const langStringPageTokenStep1LoginButton = useLangString("page_token.step_1.login_button");

	const tokenBoxRef = useRef<HTMLDivElement>(null);
	const joinLink = useAtomValue(joinLinkState);
	const isRegistration = useAtomValue(isRegistrationState);

	const apiAuthGenerateToken = useApiAuthGenerateToken(joinLink === null ? undefined : joinLink.join_link_uniq);

	const onClickHandler = () => {
		if (isRegistration) {
			if (!apiAuthGenerateToken.data || !tokenBoxRef.current) {
				return;
			}
			copyToClipboard(
				apiAuthGenerateToken.data.authentication_token,
				scrollableParentBlockRef.current,
				tokenBoxRef.current
			);
			return;
		} else {
			window.location.replace(`getcompassonpremise://`);
		}
	};

	return (
		<VStack gap="0px">
			<HStack w="100%" gap="16px" justify="space-between">
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
					<Text color="white" fs="14" lh="18" font="regular" ls="-015">
						<styled.span fontFamily="lato_bold" fontWeight="700">
							{isRegistration
								? langStringPageTokenStep1RegisterDescPt1
								: langStringPageTokenStep1LoginDescPt1}
						</styled.span>
						<br />
						{isRegistration
							? langStringPageTokenStep1RegisterDescPt2
							: langStringPageTokenStep1LoginDescPt2Desktop}
					</Text>
				</HStack>
				<Button size="px21py6" textSize="xl_desktop" ref={parentButtonRef} onClick={() => onClickHandler()}>
					{isRegistration ? langStringPageTokenStep1RegisterButton : langStringPageTokenStep1LoginButton}
				</Button>
			</HStack>
			{apiAuthGenerateToken.isLoading || !apiAuthGenerateToken.data ? (
				<VStack
					w="100%"
					bgColor="000000.01"
					rounded="8px"
					px="10px"
					py="8px"
					gap="4px"
					alignItems="start"
					mt="16px"
				>
					<Box w="100%" h="16px" bgColor="434455" rounded="3px" />
					<Box w="48%" h="16px" bgColor="434455" rounded="3px" />
				</VStack>
			) : (
				<Box
					ref={tokenBoxRef}
					w="100%"
					bgColor="000000.01"
					rounded="8px"
					px="12px"
					py="8px"
					cursor="pointer"
					mt="16px"
					onClick={() => {
						if (tokenBoxRef.current) {
							copyToClipboard(
								apiAuthGenerateToken.data.authentication_token,
								scrollableParentBlockRef.current,
								tokenBoxRef.current
							);
						}
					}}
				>
					<Text overflow="breakWord" color="f8f8f8" opacity="50%" fs="13" lh="20" font="regular">
						{apiAuthGenerateToken.data.authentication_token.substring(0, 120)}
					</Text>
				</Box>
			)}
			<Text style="lato_14_20_400" letterSpacing="-0.15px" color="f8f8f8" opacity="50%" mt="12px">
				{langStringPageTokenLifeTimeDesktop}
			</Text>
		</VStack>
	);
};

const PageTokenDesktop = () => {
	const langStringLogoutDialogTitle = useLangString("logout_dialog.title");
	const langStringLogoutDialogDesc = useLangString("logout_dialog.desc");
	const langStringLogoutDialogCancelButton = useLangString("logout_dialog.cancel_button");
	const langStringLogoutDialogConfirmButton = useLangString("logout_dialog.confirm_button");
	const langStringPageTokenTitle = useLangString("page_token.title");
	const langStringPageTokenDesc = useLangString("page_token.desc");
	const langStringErrorsNetworkError = useLangString("errors.network_error");
	const langStringErrorsServerError = useLangString("errors.server_error");
	const langStringCreateNewPasswordDialogSuccessTooltipMessage = useLangString(
		"create_new_password_dialog.success_tooltip_message"
	);

	const isJoinLink = useIsJoinLink();
	const joinLink = useAtomValue(joinLinkState);
	const prepareJoinLinkError = useAtomValue(prepareJoinLinkErrorState);
	const setAuthInput = useSetAtom(authInputState);
	const setPasswordInput = useSetAtom(passwordInputState);
	const setConfirmPassword = useSetAtom(confirmPasswordState);
	const setNameInput = useSetAtom(nameInputState);
	const setAuth = useSetAtom(authState);
	const [isPasswordChanged, setIsPasswordChanged] = useAtom(isPasswordChangedState);
	const apiAuthLogout = useApiAuthLogout();
	const dialogId = useMemo(() => generateDialogId(), []);
	const toastConfig = useToastConfig(dialogId);
	const pageToastConfig = useToastConfig("page_token");
	const showToast = useShowToast(dialogId);
	const pageShowToast = useShowToast("page_token");

	const apiJoinLinkAccept = useApiJoinLinkAccept();
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

		// если уже совершаем запрос
		if (apiJoinLinkAccept.isLoading) {
			return;
		}

		apiJoinLinkAccept.mutateAsync({ join_link_uniq: joinLink.join_link_uniq });
	}, []);

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

	const scrollableParentBlockRef = useRef(null);
	const parentButtonRef = useRef(null);
	const [childButtonWidth, setChildButtonWidth] = useState(0);

	useEffect(() => {
		if (parentButtonRef.current) {
			const { offsetWidth } = parentButtonRef.current;
			setChildButtonWidth(offsetWidth);
		}
	}, [parentButtonRef.current]);

	const onLogoutClickHandler = useCallback(async () => {
		if (apiAuthLogout.isLoading) {
			return;
		}

		try {
			await apiAuthLogout.mutateAsync();
		} catch (error) {
			if (error instanceof NetworkError) {
				showToast(langStringErrorsNetworkError, "warning");
				return;
			}

			if (error instanceof ServerError) {
				showToast(langStringErrorsServerError, "warning");
				return;
			}
		}
	}, [apiAuthLogout]);

	return (
		<VStack
			ref={scrollableParentBlockRef}
			gap="0px"
			maxWidth="100vw"
			width="100%"
			maxHeight="100vh"
			h="100%"
			className="invisible-scrollbar"
			position="relative"
		>
			<HStack w="100%" justify="end" position="absolute" top="0px" pt="32px" px="40px" gap="12px">
				{/*<LangMenuSelectorDesktop/>*/}
				<Dialog style="desktop" size="small" backdrop="opacity50">
					<DialogTrigger asChild>
						<Box
							bgColor="000000.005"
							py="7px"
							pl="8px"
							pr="6px"
							rounded="100%"
							cursor="pointer"
							_hover={{
								bgColor: "000000.005.hover",
								opacity: "100%",
							}}
						>
							<svg
								width="18"
								height="18"
								viewBox="0 0 18 18"
								fill="none"
								xmlns="http://www.w3.org/2000/svg"
							>
								<g opacity="0.3">
									<path
										d="M5.0625 1.5C3.92341 1.5 3 2.42341 3 3.5625V14.4375C3 15.5766 3.92341 16.5 5.0625 16.5H10.3125C11.4516 16.5 12.375 15.5766 12.375 14.4375V12.4688C12.375 12.1581 12.1232 11.9062 11.8125 11.9062C11.5018 11.9062 11.25 12.1581 11.25 12.4688V14.4375C11.25 14.9553 10.8303 15.375 10.3125 15.375H5.0625C4.54473 15.375 4.125 14.9553 4.125 14.4375V3.5625C4.125 3.04473 4.54473 2.625 5.0625 2.625H10.3125C10.8303 2.625 11.25 3.04473 11.25 3.5625V5.53125C11.25 5.84191 11.5018 6.09375 11.8125 6.09375C12.1232 6.09375 12.375 5.84191 12.375 5.53125V3.5625C12.375 2.42341 11.4516 1.5 10.3125 1.5H5.0625Z"
										fill="#A4A4A5"
									/>
									<path
										d="M13.8523 6.72725C14.0719 6.50758 14.4281 6.50758 14.6477 6.72725L16.5227 8.60226C16.6282 8.70775 16.6875 8.85082 16.6875 9.00001C16.6875 9.14919 16.6282 9.29227 16.5227 9.39776L14.6477 11.2727C14.4281 11.4924 14.0719 11.4924 13.8523 11.2727C13.6326 11.0531 13.6326 10.6969 13.8523 10.4773L14.767 9.56251H9.1875C8.87684 9.56251 8.625 9.31067 8.625 9.00001C8.625 8.68935 8.87684 8.43751 9.1875 8.43751H14.767L13.8523 7.52275C13.6326 7.30308 13.6326 6.94692 13.8523 6.72725Z"
										fill="#A4A4A5"
									/>
								</g>
							</svg>
						</Box>
					</DialogTrigger>
					<Portal>
						<DialogBackdrop />
						<DialogContainer>
							<DialogContent overflow="hidden" lazyMount unmountOnExit>
								<Toast toastConfig={toastConfig} />
								<VStack mt="16px" gap="24px">
									<VStack gap="16px" px="4px">
										<Box w="80px" h="80px">
											<svg
												width="80"
												height="80"
												viewBox="0 0 80 80"
												fill="none"
												xmlns="http://www.w3.org/2000/svg"
											>
												<path
													d="M22.4997 6.66669C17.4371 6.66669 13.333 10.7707 13.333 15.8334V64.1667C13.333 69.2293 17.4371 73.3334 22.4997 73.3334H45.833C50.8956 73.3334 54.9997 69.2293 54.9997 64.1667V55.4167C54.9997 54.036 53.8804 52.9167 52.4997 52.9167C51.119 52.9167 49.9997 54.036 49.9997 55.4167V64.1667C49.9997 66.4679 48.1342 68.3334 45.833 68.3334H22.4997C20.1985 68.3334 18.333 66.4679 18.333 64.1667V15.8334C18.333 13.5322 20.1985 11.6667 22.4997 11.6667H45.833C48.1342 11.6667 49.9997 13.5322 49.9997 15.8334V24.5834C49.9997 25.9641 51.119 27.0834 52.4997 27.0834C53.8804 27.0834 54.9997 25.9641 54.9997 24.5834V15.8334C54.9997 10.7707 50.8956 6.66669 45.833 6.66669H22.4997Z"
													fill="#B4B4B4"
												/>
												<path
													d="M61.5652 29.8989C62.5416 28.9226 64.1245 28.9226 65.1008 29.8989L73.4341 38.2323C73.9029 38.7011 74.1663 39.337 74.1663 40.0001C74.1663 40.6631 73.9029 41.299 73.4341 41.7678L65.1008 50.1011C64.1245 51.0774 62.5415 51.0774 61.5652 50.1011C60.5889 49.1248 60.5889 47.5419 61.5652 46.5656L65.6308 42.5H40.833C39.4523 42.5 38.333 41.3808 38.333 40C38.333 38.6193 39.4523 37.5001 40.833 37.5001H65.6308L61.5652 33.4344C60.5889 32.4581 60.5889 30.8752 61.5652 29.8989Z"
													fill="#B4B4B4"
												/>
											</svg>
										</Box>
										<VStack gap="6px">
											<Text font="bold900" ls="-02" fs="18" lh="24" color="333e49">
												{langStringLogoutDialogTitle}
											</Text>
											<Text fs="14" lh="20" color="333e49" textAlign="center" font="regular">
												{langStringLogoutDialogDesc}
											</Text>
										</VStack>
									</VStack>
									<HStack w="100%" justify="space-between">
										<DialogCloseTrigger asChild>
											<Button color="f5f5f5" size="px16py6" textSize="xl_desktop">
												{langStringLogoutDialogCancelButton}
											</Button>
										</DialogCloseTrigger>
										<Button
											color="ff6a64"
											size="px16py6"
											textSize="xl_desktop"
											minW="102px"
											onClick={() => onLogoutClickHandler()}
										>
											{apiAuthLogout.isLoading ? (
												<Box py="3.5px">
													<Preloader16 />
												</Box>
											) : (
												<HStack gap="4px">
													<Box>{langStringLogoutDialogConfirmButton}</Box>
													<Box w="20px" h="21px">
														<svg
															width="20"
															height="21"
															viewBox="0 0 20 21"
															fill="none"
															xmlns="http://www.w3.org/2000/svg"
														>
															<path
																d="M5.62467 2.66663C4.35902 2.66663 3.33301 3.69264 3.33301 4.95829V17.0416C3.33301 18.3073 4.35902 19.3333 5.62467 19.3333H11.458C12.7237 19.3333 13.7497 18.3073 13.7497 17.0416V14.8541C13.7497 14.5089 13.4699 14.2291 13.1247 14.2291C12.7795 14.2291 12.4997 14.5089 12.4997 14.8541V17.0416C12.4997 17.6169 12.0333 18.0833 11.458 18.0833H5.62467C5.04938 18.0833 4.58301 17.6169 4.58301 17.0416V4.95829C4.58301 4.383 5.04938 3.91663 5.62467 3.91663H11.458C12.0333 3.91663 12.4997 4.383 12.4997 4.95829V7.14579C12.4997 7.49097 12.7795 7.77079 13.1247 7.77079C13.4699 7.77079 13.7497 7.49097 13.7497 7.14579V4.95829C13.7497 3.69264 12.7237 2.66663 11.458 2.66663H5.62467Z"
																fill="white"
															/>
															<path
																d="M15.3911 8.47468C15.6351 8.23061 16.0309 8.23061 16.2749 8.47469L18.3583 10.558C18.4755 10.6752 18.5413 10.8342 18.5413 11C18.5413 11.1657 18.4755 11.3247 18.3583 11.4419L16.2749 13.5252C16.0309 13.7693 15.6351 13.7693 15.3911 13.5252C15.147 13.2812 15.147 12.8854 15.3911 12.6413L16.4075 11.625H10.208C9.86283 11.625 9.58301 11.3451 9.58301 11C9.58301 10.6548 9.86283 10.375 10.208 10.375H16.4075L15.3911 9.35857C15.147 9.11449 15.147 8.71876 15.3911 8.47468Z"
																fill="white"
															/>
														</svg>
													</Box>
												</HStack>
											)}
										</Button>
									</HStack>
								</VStack>
							</DialogContent>
						</DialogContainer>
					</Portal>
				</Dialog>
			</HStack>
			<Toast toastConfig={pageToastConfig} />
			<Center gap="8px" maxWidth="560px" h="100vh" className="invisible-scrollbar">
				<VStack w="100%" gap="24px" userSelect="none">
					<VStack w="100%" gap="16px">
						<IconLogo />
						<VStack w="100%" alignItems="center" gap="6px">
							<Text w="100%" textAlign="center" fs="18" lh="24" color="white" font="bold900" ls="-02">
								{langStringPageTokenTitle}
							</Text>
							<Text w="100%" textAlign="center" fs="14" lh="20" color="white" font="regular">
								{langStringPageTokenDesc}
							</Text>
						</VStack>
					</VStack>
					<VStack w="100%" gap="16px">
						<Box w="100%" bgColor="434455" p="16px" rounded="12px">
							<StepOneContent
								scrollableParentBlockRef={scrollableParentBlockRef}
								parentButtonRef={parentButtonRef}
							/>
						</Box>
						<Box w="100%" bgColor="434455" p="16px" rounded="12px">
							<StepTwoContent childButtonWidth={childButtonWidth} />
						</Box>
					</VStack>
				</VStack>
			</Center>
		</VStack>
	);
};

export default PageTokenDesktop;
