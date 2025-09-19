import { OTPInput, SlotProps } from "input-otp";
import { styled } from "../../styled-system/jsx";
import { Text } from "./text.tsx";
import { css } from "../../styled-system/css";
import { ClipboardEventHandler, useCallback, useEffect, useMemo, useRef } from "react";

interface CustomSlotProps extends SlotProps {
	isSuccess: boolean;
	isError: boolean;
	isHavePreviousChar: boolean;
	style: styleProps;
	index: number;
	char: string | null;
	isActive: boolean;
	hasFakeCaret: boolean;
}

type FakeDashProps = {
	style: styleProps;
};

function FakeDash({ style }: FakeDashProps) {
	if (style === "Mobile") {
		return (
			<styled.div
				key = "pin_input_delimiter"
				mx = "4px"
				w = "24px"
				display = "flex"
				justifyContent = "center"
				alignItems = "center"
			>
				<svg width = "17" height = "4" viewBox = "0 0 17 4" fill = "none" xmlns = "http://www.w3.org/2000/svg">
					<path d = "M0.748438 0.899999H16.7484V3.5H0.748438V0.899999Z" fill = "#333E49" />
				</svg>
			</styled.div>
		);
	}
	if (style === "MobileSmall") {
		return (
			<styled.div
				key = "pin_input_delimiter"
				mx = "4px"
				w = "18px"
				display = "flex"
				justifyContent = "center"
				alignItems = "center"
			>
				<svg width = "14" height = "3" viewBox = "0 0 14 3" fill = "none" xmlns = "http://www.w3.org/2000/svg">
					<path d = "M0.798789 0.3475H13.1988V2.3625H0.798789V0.3475Z" fill = "#333E49" />
				</svg>
			</styled.div>
		);
	}

	return (
		<styled.div key = "pin_input_delimiter" mx = "8px" w = "18px" h = "3px">
			<svg width = "18" height = "3" viewBox = "0 0 18 3" fill = "none" xmlns = "http://www.w3.org/2000/svg">
				<rect width = "18" height = "3" rx = "1" fill = "#B4B4B4" />
			</svg>
		</styled.div>
	);
}

type FakeCaretProps = {
	style: styleProps;
	char: string | null;
};

function FakeCaret({ char, style }: FakeCaretProps) {
	return (
		<styled.div
			position = "absolute"
			pointerEvents = "none"
			inset = "0"
			display = "flex"
			justifyContent = "center"
			alignItems = "center"
			animation = "caretBlink"
			ml = {char !== null ? "10px" : "0px"}
		>
			<styled.div
				w = "1px"
				h = {style === "Mobile" ? "48px" : style === "MobileSmall" ? "37px" : "41px"}
				bgColor = "black"
			/>
		</styled.div>
	);
}

function Slot(props: CustomSlotProps) {
	return (
		<styled.div
			className = {css({
				position: "relative",
				width: props.style === "MobileSmall" ? "30px" : "36px",
				height: props.style === "Mobile" ? "56px" : props.style === "MobileSmall" ? "46px" : "51px",
				display: "flex",
				justifyContent: "center",
				alignItems: "center",
				appearance: "none",
				backgroundColor: "#f8f8f8",
				borderWidth: "1px",
				borderColor: props.isError ? "ff6a64" : props.isSuccess ? "05c46b" : "transparent",
				borderRadius: "8px",
				outline: 0,
			})}
		>
			{props.char !== null && (
				<Text
					style = {
						props.style === "Mobile"
							? "lato_40_40_400"
							: props.style === "MobileSmall"
								? "lato_31_31_400"
								: "lato_42_50_400"
					}
				>
					{props.char}
				</Text>
			)}
			{(props.hasFakeCaret || props.isActive) && props.char === null && (
				<FakeCaret char = {props.char} style = {props.style} />
			)}
		</styled.div>
	);
}

const formatAuthCode = (value: string, maxLength: number) => {
	// удаление всех символов, кроме цифр
	const cleanedValue = value.replace(/\D+/g, "");

	// ограничение длины
	return cleanedValue.slice(0, maxLength);
};

type styleProps = "Desktop" | "Mobile" | "MobileSmall";

type PinInputProps = {
	style: styleProps;
	confirmCode: string;
	onChange: (value: string) => void;
	onComplete: (value: boolean) => void;
	isSuccess: boolean;
	isError: boolean;
	isCompleted: boolean;
};

export default function PinInput({
	style,
	confirmCode,
	onChange,
	onComplete,
	isSuccess,
	isError,
	isCompleted,
}: PinInputProps) {
	const maxLength = useMemo(() => 6, []);
	const onPasteHandler = useCallback<ClipboardEventHandler<HTMLInputElement>>(
		(e) => {
			if (onChange) {
				e.preventDefault();
				onChange(formatAuthCode(e.clipboardData.getData("Text"), maxLength));
			}
		},
		[ onChange ]
	);

	const inputRef = useRef<HTMLInputElement>(null);

	useEffect(() => inputRef.current?.focus(), []);
	useEffect(() => {
		if (!isCompleted) {
			inputRef.current?.focus();
		}
	}, [ inputRef.current, isCompleted ]);

	return (
		<OTPInput
			key = "otp_input"
			ref = {inputRef}
			maxLength = {maxLength}
			containerClassName = "pin-input-container"
			inputMode = "numeric"
			textAlign = "left"
			onPaste = {onPasteHandler}
			onChange = {onChange}
			onComplete = {onComplete}
			value = {confirmCode}
			disabled = {isCompleted}
			autoFocus
			render = {({ slots }) => (
				<>
					<styled.div display = "flex" gap = "4px">
						{slots.slice(0, 3).map((slot, idx) => (
							<Slot
								key = {idx}
								index = {idx}
								isHavePreviousChar = {slots[idx - 1] !== undefined && slots[idx - 1].char !== null}
								isSuccess = {isSuccess}
								isError = {isError}
								style = {style}
								{...slot}
							/>
						))}
					</styled.div>

					<FakeDash style = {style} />

					<styled.div display = "flex" gap = "4px">
						{slots.slice(3).map((slot, idx) => (
							<Slot
								key = {idx}
								index = {idx}
								isHavePreviousChar = {slots[idx - 1] !== undefined && slots[idx - 1].char !== null}
								isSuccess = {isSuccess}
								isError = {isError}
								style = {style}
								{...slot}
							/>
						))}
					</styled.div>
				</>
			)}
		/>
	);
}
