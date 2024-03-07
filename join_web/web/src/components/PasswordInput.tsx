import { Input } from "./input.tsx";
import { type PropertyValue } from "../../styled-system/types/prop-type";
import { Box, HStack, VStack } from "../../styled-system/jsx";
import { RefObject } from "react";
import useIsMobile from "../lib/useIsMobile.ts";
import { Tooltip, TooltipArrow, TooltipContent, TooltipProvider, TooltipTrigger } from "./tooltip.tsx";
import { Portal } from "@ark-ui/react";
import { useLangString } from "../lib/getLangString.ts";

const ShowPasswordIconDesktop = () => {
	return (
		<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path
				d="M15.5652 16.4777C15.8172 16.7297 16.2257 16.7297 16.4776 16.4777C16.7296 16.2258 16.7296 15.8173 16.4776 15.5653L14.615 13.7027C14.6148 13.7028 14.6152 13.7026 14.615 13.7027L13.6633 12.7502C13.6632 12.7503 13.6635 12.7501 13.6633 12.7502L12.8695 11.9572C12.8694 11.9574 12.8697 11.9571 12.8695 11.9572L10.2304 9.31723C10.2302 9.31737 10.2305 9.31709 10.2304 9.31723L8.10166 7.18937C8.10151 7.18951 8.1018 7.18924 8.10166 7.18937L6.61948 5.70635C6.61928 5.70643 6.61968 5.70627 6.61948 5.70635L4.43462 3.52234C4.18267 3.27039 3.77418 3.27039 3.52223 3.52234C3.27028 3.77429 3.27028 4.18278 3.52223 4.43473L15.5652 16.4777Z"
				fill="#B4B4B4"
			/>
			<path
				d="M10.8376 12.961L6.39383 8.51723C6.36106 8.71992 6.34403 8.92787 6.34403 9.13981C6.34403 11.2777 8.07712 13.0108 10.215 13.0108C10.4269 13.0108 10.6349 12.9937 10.8376 12.961Z"
				fill="#B4B4B4"
			/>
			<path
				d="M3.06695 10.0008C3.82576 9.03992 4.67223 8.2721 5.5696 7.693L4.63908 6.76249C3.58035 7.48611 2.60058 8.43642 1.74584 9.6074C1.71558 9.64684 1.69034 9.68926 1.67025 9.73369C1.63279 9.81613 1.61396 9.90392 1.61286 9.99135C1.61149 10.0879 1.63172 10.1854 1.67477 10.2761C1.69378 10.3164 1.71707 10.3549 1.74454 10.3909C3.93406 13.403 6.94444 14.9493 10.0006 14.9462C10.8345 14.9454 11.665 14.8292 12.4763 14.5997L11.4028 13.5262C10.9363 13.6124 10.467 13.6555 9.99929 13.6559C7.52492 13.6584 5.0089 12.4692 3.06695 10.0008Z"
				fill="#B4B4B4"
			/>
			<path
				d="M15.3646 13.235C16.4219 12.5117 17.4003 11.5622 18.254 10.3927C18.2844 10.3531 18.3098 10.3104 18.3299 10.2658C18.3672 10.1834 18.386 10.0957 18.387 10.0084C18.3884 9.91214 18.3682 9.81501 18.3254 9.72461C18.3064 9.6841 18.2829 9.64534 18.2553 9.60908C16.0658 6.59704 13.0554 5.0507 9.99928 5.0538C9.16708 5.05464 8.3383 5.17037 7.52859 5.39893L8.91982 6.79016C8.9916 6.77966 9.06503 6.77422 9.13973 6.77422C9.97112 6.77422 10.6451 7.4482 10.6451 8.27959C10.6451 8.35429 10.6397 8.42772 10.6292 8.4995L13.4284 11.2988C13.8436 10.682 14.086 9.93921 14.086 9.13981C14.086 8.42959 13.8947 7.76404 13.5609 7.19187C14.781 7.79651 15.9331 8.72833 16.9329 9.99925C16.1752 10.9587 15.3301 11.7257 14.4342 12.3045L15.3646 13.235Z"
				fill="#B4B4B4"
			/>
		</svg>
	);
};

const HidePasswordIconDesktop = () => {
	return (
		<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path
				fillRule="evenodd"
				clipRule="evenodd"
				d="M10.0005 15C13.089 14.9969 16.1308 13.4284 18.3436 10.3969C18.3741 10.3572 18.3995 10.3146 18.4197 10.2699C18.4578 10.1863 18.477 10.0972 18.4781 10.0085C18.4794 9.91116 18.459 9.81297 18.4158 9.72159C18.3965 9.68065 18.3728 9.64147 18.3449 9.60482C16.1316 6.56003 13.0885 4.99687 9.99914 5C6.91061 5.00313 3.86876 6.57157 1.65594 9.60315C1.6256 9.64271 1.60024 9.68524 1.58002 9.72978C1.54181 9.8135 1.52262 9.9027 1.52155 9.99153C1.52021 10.0888 1.54056 10.187 1.58382 10.2784C1.60309 10.3193 1.62676 10.3585 1.65469 10.3952C3.86801 13.44 6.9111 15.0031 10.0005 15ZM9.99914 13.6956C7.49788 13.6982 4.95451 12.496 2.99144 10.0008C4.16582 8.51368 5.54789 7.48403 7.00435 6.89607C6.56295 7.52957 6.30412 8.29978 6.30412 9.13043C6.30412 11.2915 8.05605 13.0435 10.2172 13.0435C12.3783 13.0435 14.1302 11.2915 14.1302 9.13043C14.1302 8.41249 13.9369 7.7397 13.5994 7.1613C14.8328 7.77252 15.9974 8.71446 17.0082 9.99922C15.0447 12.4855 12.5007 13.6931 9.99914 13.6956ZM10.6519 8.26087C10.6519 9.10131 9.97062 9.78261 9.13019 9.78261C8.28975 9.78261 7.60845 9.10131 7.60845 8.26087C7.60845 7.42044 8.28975 6.73913 9.13019 6.73913C9.97062 6.73913 10.6519 7.42044 10.6519 8.26087Z"
				fill="#B4B4B4"
			/>
		</svg>
	);
};

const ShowPasswordIconMobile = () => {
	return (
		<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
			<g clipPath="url(#clip0_3253_15138)">
				<path
					d="M14.0087 14.8299C14.2355 15.0567 14.6031 15.0567 14.8299 14.8299C15.0567 14.6032 15.0567 14.2355 14.8299 14.0088L13.1535 12.3324C13.1533 12.3325 13.1537 12.3323 13.1535 12.3324L12.297 11.4752C12.2969 11.4752 12.2972 11.4751 12.297 11.4752L11.5826 10.7615C11.5825 10.7616 11.5827 10.7614 11.5826 10.7615L9.20735 8.38547C9.20722 8.3856 9.20747 8.38534 9.20735 8.38547L7.29152 6.4704C7.29139 6.47052 7.29165 6.47028 7.29152 6.4704L5.95755 5.13568C5.95737 5.13575 5.95773 5.13561 5.95755 5.13568L3.99119 3.17007C3.76443 2.94331 3.39679 2.94331 3.17003 3.17007C2.94327 3.39682 2.94327 3.76447 3.17003 3.99122L14.0087 14.8299Z"
					fill="#B4B4B4"
				/>
				<path
					d="M9.75384 11.6648L5.75447 7.66547C5.72498 7.84789 5.70965 8.03505 5.70965 8.22579C5.70965 10.1499 7.26943 11.7097 9.19352 11.7097C9.38426 11.7097 9.57142 11.6943 9.75384 11.6648Z"
					fill="#B4B4B4"
				/>
				<path
					d="M2.76028 9.00067C3.44321 8.13589 4.20503 7.44486 5.01266 6.92367L4.1752 6.0862C3.22234 6.73746 2.34054 7.59274 1.57128 8.64663C1.54405 8.68212 1.52133 8.7203 1.50325 8.76028C1.46954 8.83448 1.45259 8.91349 1.4516 8.99218C1.45036 9.07911 1.46857 9.16684 1.50732 9.24846C1.52443 9.28468 1.54539 9.31934 1.57011 9.35179C3.54068 12.0626 6.25002 13.4544 9.00056 13.4516C9.75109 13.4508 10.4985 13.3462 11.2287 13.1397L10.2626 12.1736C9.84266 12.2511 9.42035 12.2899 8.99939 12.2903C6.77245 12.2926 4.50804 11.2223 2.76028 9.00067Z"
					fill="#B4B4B4"
				/>
				<path
					d="M13.8282 11.9114C14.7797 11.2605 15.6603 10.406 16.4286 9.35338C16.456 9.31772 16.4788 9.27934 16.4969 9.23915C16.5305 9.16502 16.5474 9.08611 16.5484 9.00752C16.5495 8.92089 16.5314 8.83348 16.4929 8.75211C16.4757 8.71566 16.4547 8.68077 16.4298 8.64813C14.4592 5.9373 11.7499 4.54559 8.99938 4.54838C8.2504 4.54914 7.50449 4.65329 6.77575 4.859L8.02786 6.11111C8.09246 6.10165 8.15855 6.09676 8.22578 6.09676C8.97404 6.09676 9.58062 6.70334 9.58062 7.4516C9.58062 7.51883 9.57572 7.58491 9.56627 7.64952L12.0856 10.1689C12.4593 9.61379 12.6774 8.94526 12.6774 8.22579C12.6774 7.58659 12.5053 6.9876 12.2048 6.47265C13.303 7.01683 14.3398 7.85546 15.2397 8.99929C14.5577 9.86283 13.7971 10.5531 12.9908 11.074L13.8282 11.9114Z"
					fill="#B4B4B4"
				/>
			</g>
			<defs>
				<clipPath id="clip0_3253_15138">
					<rect width="18" height="18" fill="white" />
				</clipPath>
			</defs>
		</svg>
	);
};

const HidePasswordIconMobile = () => {
	return (
		<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
			<g clipPath="url(#clip0_3290_20680)">
				<path
					fillRule="evenodd"
					clipRule="evenodd"
					d="M9.00071 13.5C11.7804 13.4972 14.518 12.0856 16.5096 9.35717C16.537 9.32148 16.5598 9.2831 16.578 9.24292C16.6123 9.16765 16.6296 9.08746 16.6305 9.00761C16.6317 8.92004 16.6134 8.83168 16.5745 8.74943C16.5571 8.71258 16.5359 8.67732 16.5107 8.64434C14.5187 5.90403 11.7799 4.49719 8.99951 4.5C6.21984 4.50282 3.48218 5.91442 1.49064 8.64284C1.46333 8.67844 1.44051 8.71672 1.42231 8.7568C1.38792 8.83215 1.37065 8.91243 1.36968 8.99238C1.36848 9.07994 1.3868 9.16831 1.42573 9.25056C1.44308 9.28741 1.46438 9.32267 1.48951 9.35566C3.4815 12.096 6.22028 13.5028 9.00071 13.5ZM8.99952 12.3261C6.74838 12.3284 4.45935 11.2464 2.69259 9.00069C3.74953 7.66231 4.99339 6.73562 6.3042 6.20646C5.90695 6.77662 5.674 7.4698 5.674 8.21739C5.674 10.1624 7.25074 11.7391 9.19574 11.7391C11.1407 11.7391 12.7175 10.1624 12.7175 8.21739C12.7175 7.57124 12.5435 6.96573 12.2397 6.44517C13.3499 6.99526 14.398 7.84302 15.3076 8.9993C13.5405 11.2369 11.2509 12.3238 8.99952 12.3261ZM9.58703 7.43479C9.58703 8.19118 8.97385 8.80435 8.21746 8.80435C7.46107 8.80435 6.8479 8.19118 6.8479 7.43479C6.8479 6.6784 7.46107 6.06522 8.21746 6.06522C8.97385 6.06522 9.58703 6.6784 9.58703 7.43479Z"
					fill="#B4B4B4"
				/>
			</g>
			<defs>
				<clipPath id="clip0_3290_20680">
					<rect width="18" height="18" fill="white" />
				</clipPath>
			</defs>
		</svg>
	);
};

type PasswordInputProps = {
	isDisabled: boolean;
	password: string;
	setPassword: (value: string) => void;
	inputPlaceholder: string;
	isToolTipVisible: boolean;
	setIsToolTipVisible: (value: boolean) => void;
	isNeedShowTooltip: boolean;
	setIsNeedShowTooltip: (value: boolean) => void;
	isError: boolean;
	setIsError: (value: boolean) => void;
	onEnterClick: () => void;
	inputRef: RefObject<HTMLInputElement>;
	isPasswordVisible: boolean;
	setIsPasswordVisible: (value: boolean) => void;
	onInputFocus?: () => void;
	inputTabIndex?: number;
	mt?: PropertyValue<"mt">;
	autoFocus?: boolean;
};

function PasswordInputDesktop({
	isDisabled,
	password,
	setPassword,
	inputPlaceholder,
	isToolTipVisible,
	setIsToolTipVisible,
	isNeedShowTooltip,
	setIsNeedShowTooltip,
	isError,
	setIsError,
	onEnterClick,
	inputRef,
	isPasswordVisible,
	setIsPasswordVisible,
	onInputFocus,
	inputTabIndex,
	mt,
	autoFocus,
}: PasswordInputProps) {
	const langStringEmailPhoneNumberDialogProhibitedSymbolsTooltip = useLangString(
		"email_phone_number_dialog.prohibited_symbols_tooltip"
	);

	return (
		<TooltipProvider>
			<Tooltip open={isToolTipVisible} onOpenChange={() => null} style="desktop" type="warning_desktop">
				<VStack w="100%" gap="0px" mt={mt}>
					<TooltipTrigger
						style={{
							width: "100%",
							height: "0px",
							opacity: "0%",
						}}
					/>
					<HStack
						w="100%"
						bgColor="f8f8f8"
						rounded="8px"
						borderColor={isError ? "red" : "transparent"}
						borderWidth="1px"
						gap="0px"
					>
						<Input
							rounded="8px"
							py="8px"
							pl="12px"
							pr="10px"
							autoFocus={autoFocus}
							ref={inputRef}
							input="hstack_input"
							size="hstack_default_desktop"
							placeholder={inputPlaceholder}
							value={password}
							type={isPasswordVisible ? "text" : "password"}
							maxLength={40}
							disabled={isDisabled}
							tabIndex={inputTabIndex}
							autoCapitalize="none"
							onFocus={() => (onInputFocus ? onInputFocus() : undefined)}
							onChange={(changeEvent) => {
								const value = changeEvent.target.value ?? "";
								if (isNeedShowTooltip) {
									// it's okay
									const isTooltipNotSavedSymbolsVisible =
										/[\s\p{Extended_Pictographic}\u{1F3FB}-\u{1F3FF}\u{1F9B0}-\u{1F9B3}]/gu.test(
											value
										);
									if (isTooltipNotSavedSymbolsVisible) {
										setIsToolTipVisible(isTooltipNotSavedSymbolsVisible);
										setIsError(true);
										if (isTooltipNotSavedSymbolsVisible) {
											setIsNeedShowTooltip(false);
										}
										setPassword(value);
										return;
									}
								}

								setPassword(value);
								setIsError(false);
							}}
							onKeyDown={(event: React.KeyboardEvent) => {
								if (event.key === "Enter") {
									onEnterClick();
								}
							}}
						/>

						<Box
							w="20px"
							h="20px"
							mr="8px"
							cursor="pointer"
							WebkitTapHighlightColor="transparent"
							tabIndex={-1}
							onClick={(event) => {
								event.stopPropagation();
								isPasswordVisible ? setIsPasswordVisible(false) : setIsPasswordVisible(true);
							}}
						>
							{isPasswordVisible ? <HidePasswordIconDesktop /> : <ShowPasswordIconDesktop />}
						</Box>
					</HStack>
				</VStack>
				<Portal>
					<TooltipContent
						onClick={() => setIsToolTipVisible(false)}
						onEscapeKeyDown={() => setIsToolTipVisible(false)}
						onPointerDownOutside={() => setIsToolTipVisible(false)}
						sideOffset={4}
						avoidCollisions={false}
						style={{
							maxWidth: "256px",
							width: "var(--radix-tooltip-trigger-width)",
						}}
					>
						<TooltipArrow width="8px" height="5px" asChild>
							<svg width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M0 0L4 5L8 0H0Z" fill="#FF8A00" />
							</svg>
						</TooltipArrow>
						{langStringEmailPhoneNumberDialogProhibitedSymbolsTooltip}
					</TooltipContent>
				</Portal>
			</Tooltip>
		</TooltipProvider>
	);
}

function PasswordInputMobile({
	isDisabled,
	password,
	setPassword,
	inputPlaceholder,
	isToolTipVisible,
	setIsToolTipVisible,
	isNeedShowTooltip,
	setIsNeedShowTooltip,
	isError,
	setIsError,
	onEnterClick,
	inputRef,
	isPasswordVisible,
	setIsPasswordVisible,
	onInputFocus,
	inputTabIndex,
	mt,
	autoFocus,
}: PasswordInputProps) {
	const langStringEmailPhoneNumberDialogProhibitedSymbolsTooltip = useLangString(
		"email_phone_number_dialog.prohibited_symbols_tooltip"
	);

	return (
		<TooltipProvider>
			<Tooltip open={isToolTipVisible} onOpenChange={() => null} style="mobile" type="warning_mobile">
				<VStack w="100%" gap="0px" mt={mt}>
					<TooltipTrigger
						style={{
							width: "100%",
							height: "0px",
							opacity: "0%",
						}}
					/>
					<HStack
						w="100%"
						bgColor="f8f8f8"
						rounded="8px"
						borderColor={isError ? "red" : "transparent"}
						borderWidth="1px"
						gap="0px"
					>
						<Input
							rounded="8px"
							py="10px"
							pr="10px"
							pl="14px"
							autoFocus={autoFocus}
							ref={inputRef}
							input="hstack_input"
							size="hstack_default_mobile"
							placeholder={inputPlaceholder}
							value={password}
							type={isPasswordVisible ? "text" : "password"}
							maxLength={40}
							disabled={isDisabled}
							tabIndex={inputTabIndex}
							autoCapitalize="none"
							onFocus={() => (onInputFocus ? onInputFocus() : undefined)}
							onChange={(changeEvent) => {
								const value = changeEvent.target.value ?? "";
								if (isNeedShowTooltip) {
									// it's okay
									const isTooltipNotSavedSymbolsVisible =
										/[\s\p{Extended_Pictographic}\u{1F3FB}-\u{1F3FF}\u{1F9B0}-\u{1F9B3}]/gu.test(
											value
										);
									if (isTooltipNotSavedSymbolsVisible) {
										setIsToolTipVisible(isTooltipNotSavedSymbolsVisible);
										setIsError(true);
										if (isTooltipNotSavedSymbolsVisible) {
											setIsNeedShowTooltip(false);
										}
										setPassword(value);
										return;
									}
								}

								setPassword(value);
								setIsError(false);
							}}
							onKeyDown={(event: React.KeyboardEvent) => {
								if (event.key === "Enter") {
									onEnterClick();
								}
							}}
						/>

						<Box
							w="20px"
							h="20px"
							mr="12px"
							cursor="pointer"
							WebkitTapHighlightColor="transparent"
							tabIndex={-1}
							onClick={(event) => {
								event.stopPropagation();
								isPasswordVisible ? setIsPasswordVisible(false) : setIsPasswordVisible(true);
							}}
						>
							{isPasswordVisible ? <HidePasswordIconMobile /> : <ShowPasswordIconMobile />}
						</Box>
					</HStack>
				</VStack>
				<Portal>
					<TooltipContent
						onClick={() => setIsToolTipVisible(false)}
						onEscapeKeyDown={() => setIsToolTipVisible(false)}
						onPointerDownOutside={() => setIsToolTipVisible(false)}
						sideOffset={4}
						avoidCollisions={false}
						style={{
							maxWidth: "256px",
							width: "var(--radix-tooltip-trigger-width)",
						}}
					>
						<TooltipArrow width="8px" height="5px" asChild>
							<svg width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M0 0L4 5L8 0H0Z" fill="#FF8A00" />
							</svg>
						</TooltipArrow>
						{langStringEmailPhoneNumberDialogProhibitedSymbolsTooltip}
					</TooltipContent>
				</Portal>
			</Tooltip>
		</TooltipProvider>
	);
}

export default function PasswordInput({
	isDisabled,
	password,
	setPassword,
	inputPlaceholder,
	isToolTipVisible,
	setIsToolTipVisible,
	isNeedShowTooltip,
	setIsNeedShowTooltip,
	isError,
	setIsError,
	onEnterClick,
	inputRef,
	isPasswordVisible,
	setIsPasswordVisible,
	onInputFocus,
	inputTabIndex,
	mt,
	autoFocus = false,
}: PasswordInputProps) {
	const isMobile = useIsMobile();

	if (isMobile) {
		return (
			<PasswordInputMobile
				isDisabled={isDisabled}
				password={password}
				setPassword={setPassword}
				inputPlaceholder={inputPlaceholder}
				isToolTipVisible={isToolTipVisible}
				setIsToolTipVisible={setIsToolTipVisible}
				isNeedShowTooltip={isNeedShowTooltip}
				setIsNeedShowTooltip={setIsNeedShowTooltip}
				isError={isError}
				setIsError={setIsError}
				onEnterClick={onEnterClick}
				inputRef={inputRef}
				isPasswordVisible={isPasswordVisible}
				setIsPasswordVisible={setIsPasswordVisible}
				onInputFocus={onInputFocus}
				inputTabIndex={inputTabIndex}
				mt={mt}
				autoFocus={autoFocus}
			/>
		);
	}

	return (
		<PasswordInputDesktop
			isDisabled={isDisabled}
			password={password}
			setPassword={setPassword}
			inputPlaceholder={inputPlaceholder}
			isToolTipVisible={isToolTipVisible}
			setIsToolTipVisible={setIsToolTipVisible}
			isNeedShowTooltip={isNeedShowTooltip}
			setIsNeedShowTooltip={setIsNeedShowTooltip}
			isError={isError}
			setIsError={setIsError}
			onEnterClick={onEnterClick}
			inputRef={inputRef}
			isPasswordVisible={isPasswordVisible}
			setIsPasswordVisible={setIsPasswordVisible}
			onInputFocus={onInputFocus}
			inputTabIndex={inputTabIndex}
			mt={mt}
			autoFocus={autoFocus}
		/>
	);
}
