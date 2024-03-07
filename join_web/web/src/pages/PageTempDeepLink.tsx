import {Center, HStack, VStack} from "../../styled-system/jsx";
import useIsMobile from "../lib/useIsMobile.ts";
import {Input} from "../components/input.tsx";
import {Button} from "../components/button.tsx";
import {Portal} from "@ark-ui/react";
import {
	Select,
	SelectContent, SelectControl,
	SelectItem,
	SelectItemIndicator,
	SelectItemText,
	SelectPositioner, SelectTrigger, SelectValue
} from "../components/select.tsx";
import {useMemo, useRef, useState} from "react";
import {css} from "../../styled-system/css";
import {Text} from "../components/text.tsx";

type SelectorProps = {
	items: {
		value: string
	}[],
	setSelectedValue: (value: any) => void,
}

const SelectorDesktop = ({items, setSelectedValue}: SelectorProps) => {

	return (
		<Select
			positioning={{placement: "bottom", sameWidth: true}}
			width="100%"
			items={items}
			multiple={false}
			defaultValue={[`${items[0].value}`]}
			onChange={(items) => {
				setSelectedValue(items.value[0])
			}}
		>
			<SelectControl
				className={css({
					bgColor: "#f8f8f8",
					rounded: "8px",
					_hover: {
						bgColor: "#f5f5f5"
					}
				})}
			>
				<SelectTrigger
					style={{
						cursor: "pointer",
						paddingLeft: "16px",
						paddingRight: "16px",
						paddingTop: "6px",
						paddingBottom: "6px",
						alignItems: "center",
						display: "inline-flex",
						justifyContent: "space-between",
						outline: 0,
						appearance: "none",
						transitionDuration: "normal",
						transitionProperty: "background, box-shadow, border-color",
						transitionTimingFunction: "default",
						width: "100%",
					}}
				>
					<SelectValue
						placeholder={`${items[0].value}`}
						style={{
							color: "#333e49",
						}}
					/>
				</SelectTrigger>
			</SelectControl>
			<Portal>
				<SelectPositioner>
					<SelectContent
						lazyMount
						unmountOnExit
						className={css({
							overflow: "hidden",
							userSelect: "none",
							outline: "none",
							WebkitTapHighlightColor: "transparent",
							gap: "0px",
							bgColor: "white",
							border: "1px solid #f5f5f5",
							roundedBottom: "8px",
							roundedTop: "8px",
							display: "flex",
							flexDirection: "column",
							_open: {
								animation: "fadeIn 0.25s ease-out",
							},
						})}
					>
						{items.map((item) => (
							<SelectItem
								key={item.value}
								item={item}
								className={css({
									gap: "13px",
									width: "100%",
									alignItems: "center",
									cursor: "pointer",
									display: "flex",
									justifyContent: "flex-startstart",
									outline: "none",
									_hover: {
										bgColor: "c.f8f8f8",
									},
								})}
							>
								<HStack
									px="16px"
									py="8px"
									w="100%"
									gap="16px"
									alignItems="center"
									justifyContent="space-between"
									borderBottom="1px solid #f0f0f0"
								>
									<SelectItemText>{item.value}</SelectItemText>
									<SelectItemIndicator>
										<svg width="20" height="20" viewBox="0 0 20 20" fill="none"
											 xmlns="http://www.w3.org/2000/svg">
											<path fillRule="evenodd" clipRule="evenodd"
												  d="M16.9109 4.82611C17.2363 4.50067 17.764 4.50067 18.0894 4.82611C18.4149 5.15155 18.4149 5.67918 18.0894 6.00462L17.5002 5.41536L16.9109 4.82611ZM8.3335 13.4035L16.9109 4.82611C16.911 4.82606 16.9109 4.82611 17.5002 5.41536C18.0894 6.00462 18.0895 6.00458 18.0894 6.00462L18.0888 6.00522L8.92275 15.1713C8.59731 15.4967 8.06968 15.4967 7.74424 15.1713L3.57757 11.0046C3.25214 10.6792 3.25214 10.1515 3.57757 9.82611C3.90301 9.50067 4.43065 9.50067 4.75609 9.82611L8.3335 13.4035Z"
												  fill="#009FE6"/>
										</svg>
									</SelectItemIndicator>
								</HStack>
							</SelectItem>
						))}
					</SelectContent>
				</SelectPositioner>
			</Portal>
		</Select>
	);
}

const SelectorMobile = ({items, setSelectedValue}: SelectorProps) => {

	return (
		<Select
			positioning={{placement: "bottom", sameWidth: true}}
			width="100%"
			items={items}
			multiple={false}
			defaultValue={[`${items[0].value}`]}
			onChange={(items) => {
				setSelectedValue(items.value[0])
			}}
		>
			<SelectControl
				className={css({
					bgColor: "#f8f8f8",
					rounded: "8px",
					_hover: {
						bgColor: "#f5f5f5"
					}
				})}
			>
				<SelectTrigger
					style={{
						cursor: "pointer",
						paddingLeft: "16px",
						paddingRight: "16px",
						paddingTop: "12px",
						paddingBottom: "12px",
						alignItems: "center",
						display: "inline-flex",
						justifyContent: "space-between",
						outline: 0,
						appearance: "none",
						transitionDuration: "normal",
						transitionProperty: "background, box-shadow, border-color",
						transitionTimingFunction: "default",
						width: "100%",
					}}
				>
					<SelectValue
						placeholder={`${items[0].value}`}
						style={{
							color: "#333e49",
						}}
					/>
				</SelectTrigger>
			</SelectControl>
			<Portal>
				<SelectPositioner>
					<SelectContent
						lazyMount
						unmountOnExit
						className={css({
							overflow: "hidden",
							userSelect: "none",
							outline: "none",
							WebkitTapHighlightColor: "transparent",
							gap: "0px",
							bgColor: "white",
							border: "1px solid #f5f5f5",
							roundedBottom: "8px",
							roundedTop: "8px",
							display: "flex",
							flexDirection: "column",
							_open: {
								animation: "fadeIn 0.25s ease-out",
							},
						})}
					>
						{items.map((item) => (
							<SelectItem
								key={item.value}
								item={item}
								className={css({
									gap: "13px",
									width: "100%",
									alignItems: "center",
									cursor: "pointer",
									display: "flex",
									justifyContent: "flex-start",
									outline: "none",
									_hover: {
										bgColor: "c.f8f8f8",
									},
								})}
							>
								<HStack
									px="16px"
									py="16px"
									w="100%"
									gap="16px"
									alignItems="center"
									justifyContent="space-between"
									borderBottom="1px solid #f0f0f0"
								>
									<SelectItemText>{item.value}</SelectItemText>
									<SelectItemIndicator>
										<svg width="20" height="20" viewBox="0 0 20 20" fill="none"
											 xmlns="http://www.w3.org/2000/svg">
											<path fillRule="evenodd" clipRule="evenodd"
												  d="M16.9109 4.82611C17.2363 4.50067 17.764 4.50067 18.0894 4.82611C18.4149 5.15155 18.4149 5.67918 18.0894 6.00462L17.5002 5.41536L16.9109 4.82611ZM8.3335 13.4035L16.9109 4.82611C16.911 4.82606 16.9109 4.82611 17.5002 5.41536C18.0894 6.00462 18.0895 6.00458 18.0894 6.00462L18.0888 6.00522L8.92275 15.1713C8.59731 15.4967 8.06968 15.4967 7.74424 15.1713L3.57757 11.0046C3.25214 10.6792 3.25214 10.1515 3.57757 9.82611C3.90301 9.50067 4.43065 9.50067 4.75609 9.82611L8.3335 13.4035Z"
												  fill="#009FE6"/>
										</svg>
									</SelectItemIndicator>
								</HStack>
							</SelectItem>
						))}
					</SelectContent>
				</SelectPositioner>
			</Portal>
		</Select>
	);
}

const PageTempDeepLinkDesktop = () => {

	const envItems = useMemo(() => [
		{value: "getCompassOnPremise://"},
		{value: "stageCompassOnPremise://"},
		{value: "devCompassOnPremise://"},
	], []);

	const isPostModerationItems = useMemo(() => [
		{value: "false"},
		{value: "true"},
	], []);

	const roleItems = useMemo(() => [
		{value: "member"},
		{value: "guest"},
	], []);

	const isPreviousSpaceMemberItems = useMemo(() => [
		{value: "false"},
		{value: "true"},
	], []);

	const [selectedEnv, setSelectedEnv] = useState(envItems[0].value);
	const [spaceId, setSpaceId] = useState("");
	const [inviterUserId, setInviterUserId] = useState("");
	const [isPostModeration, setIsPostModeration] = useState("false");
	const [role, setRole] = useState("member");
	const [isPreviousSpaceMember, setIsPreviousSpaceMember] = useState("false");

	return (
		<Center
			gap="8px"
			py="8px"
			px="16px"
			maxWidth="50vw"
			w="100%"
			h="100dvh"
			className="invisible-scrollbar"
		>
			<VStack
				w="100%"
				bgColor="434455"
				px="16px"
				pt="20px"
				pb="16px"
				rounded="12px"
			>
				<VStack gap="4px" w="100%">
					<Text
						w="100%"
						color="white"
						textAlign="start"
						font="regular"
					>spaceId</Text>
					<Input
						size="default_desktop"
						value={spaceId}
						onChange={(changeEvent) => {
							setSpaceId(changeEvent.target.value ?? "")
						}}
						placeholder={"spaceId"}
					/>
				</VStack>
				<VStack gap="4px" w="100%">
					<Text
						w="100%"
						color="white"
						textAlign="start"
						font="regular"
					>inviterUserId</Text>
					<Input
						size="default_desktop"
						value={inviterUserId}
						onChange={(changeEvent) => {
							setInviterUserId(changeEvent.target.value ?? "")
						}}
						placeholder={"inviterUserId"}
					/>
				</VStack>
				<VStack gap="4px" w="100%">
					<Text
						w="100%"
						color="white"
						textAlign="start"
						font="regular"
					>isPostModeration</Text>
					<SelectorDesktop items={isPostModerationItems} setSelectedValue={setIsPostModeration}/>
				</VStack>
				<VStack gap="4px" w="100%">
					<Text
						w="100%"
						color="white"
						textAlign="start"
						font="regular"
					>role</Text>
					<SelectorDesktop items={roleItems} setSelectedValue={setRole}/>
				</VStack>
				<VStack gap="4px" w="100%">
					<Text
						w="100%"
						color="white"
						textAlign="start"
						font="regular"
					>isPreviousSpaceMember</Text>
					<SelectorDesktop items={isPreviousSpaceMemberItems} setSelectedValue={setIsPreviousSpaceMember}/>
				</VStack>
				<VStack gap="4px" w="100%">
					<Text
						w="100%"
						color="white"
						textAlign="start"
						font="regular"
					>env</Text>
					<SelectorDesktop items={envItems} setSelectedValue={setSelectedEnv}/>
				</VStack>
				<Button
					mt="22px"
					textSize="xl_desktop"
					onClick={() => {

						if (spaceId.length < 1) {

							alert("Не заполнено поле spaceId");
							return;
						}

						if (inviterUserId.length < 1) {

							alert("Не заполнено поле inviterUserId");
							return;
						}

						window.location.replace(`${selectedEnv}spaceJoin?spaceId=${spaceId}&inviterUserId=${inviterUserId}&isPostModeration=${isPostModeration}&role=${role}&isPreviousSpaceMember=${isPreviousSpaceMember}`)
					}}
				>Открыть Compass</Button>
			</VStack>
		</Center>
	);
}

const PageTempDeepLinkMobile = () => {

	const envItems = useMemo(() => [
		{value: "getCompassOnPremise://"},
		{value: "stageCompassOnPremise://"},
		{value: "devCompassOnPremise://"},
	], []);

	const isPostModerationItems = useMemo(() => [
		{value: "false"},
		{value: "true"},
	], []);

	const roleItems = useMemo(() => [
		{value: "member"},
		{value: "guest"},
	], []);

	const isPreviousSpaceMemberItems = useMemo(() => [
		{value: "false"},
		{value: "true"},
	], []);

	const [selectedEnv, setSelectedEnv] = useState(envItems[0].value);
	const [isPostModeration, setIsPostModeration] = useState("false");
	const [role, setRole] = useState("member");
	const [isPreviousSpaceMember, setIsPreviousSpaceMember] = useState("false");
	const spaceIdInputRef = useRef<HTMLInputElement>(null);
	const inviterUserIdInputRef = useRef<HTMLInputElement>(null);

	return (
		<Center
			gap="8px"
			py="8px"
			px="16px"
			maxWidth="100vw"
			w="100%"
			h="100dvh"
			className="invisible-scrollbar"
		>
			<VStack
				w="100%"
				bgColor="434455"
				px="16px"
				pt="20px"
				pb="16px"
				rounded="12px"
			>
				<VStack gap="4px" w="100%">
					<Text
						w="100%"
						color="white"
						textAlign="start"
						font="regular"
					>spaceId</Text>
					<Input
						ref={spaceIdInputRef}
						placeholder={"spaceId"}
					/>
				</VStack>
				<VStack gap="4px" w="100%">
					<Text
						w="100%"
						color="white"
						textAlign="start"
						font="regular"
					>inviterUserId</Text>
					<Input
						ref={inviterUserIdInputRef}
						placeholder={"inviterUserId"}
					/>
				</VStack>
				<VStack gap="4px" w="100%">
					<Text
						w="100%"
						color="white"
						textAlign="start"
						font="regular"
					>isPostModeration</Text>
					<SelectorMobile items={isPostModerationItems} setSelectedValue={setIsPostModeration}/>
				</VStack>
				<VStack gap="4px" w="100%">
					<Text
						w="100%"
						color="white"
						textAlign="start"
						font="regular"
					>role</Text>
					<SelectorMobile items={roleItems} setSelectedValue={setRole}/>
				</VStack>
				<VStack gap="4px" w="100%">
					<Text
						w="100%"
						color="white"
						textAlign="start"
						font="regular"
					>isPreviousSpaceMember</Text>
					<SelectorMobile items={isPreviousSpaceMemberItems} setSelectedValue={setIsPreviousSpaceMember}/>
				</VStack>
				<VStack gap="4px" w="100%">
					<Text
						w="100%"
						color="white"
						textAlign="start"
						font="regular"
					>env</Text>
					<SelectorMobile items={envItems} setSelectedValue={setSelectedEnv}/>
				</VStack>
				<Button
					mt="22px"
					textSize="xl"
					onClick={() => {

						const spaceId = spaceIdInputRef.current?.value ?? "";
						const inviterUserId = inviterUserIdInputRef.current?.value ?? "";
						if (spaceId.length < 1) {

							alert("Не заполнено поле spaceId");
							return;
						}

						if (inviterUserId.length < 1) {

							alert("Не заполнено поле inviterUserId");
							return;
						}

						window.location.href = `${selectedEnv}spaceJoin?spaceId=${spaceId}&inviterUserId=${inviterUserId}&isPostModeration=${isPostModeration}&role=${role}&isPreviousSpaceMember=${isPreviousSpaceMember}`
					}}
				>Открыть Compass</Button>
			</VStack>
		</Center>
	);
}

const PageTempDeepLink = () => {

	const isMobile = useIsMobile();

	if (isMobile) {
		return <PageTempDeepLinkMobile/>
	}

	return <PageTempDeepLinkDesktop/>
}

export default PageTempDeepLink;