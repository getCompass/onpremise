import useIsMobile from "../lib/useIsMobile.ts";
import {Box, Center, VStack} from "../../styled-system/jsx";
import {PropsWithChildren} from "react";
import {loadingState, prepareJoinLinkErrorState} from "../api/_stores.ts";
import {useAtomValue} from "jotai";
import {useNavigateDialog, useNavigatePage} from "../components/hooks.ts";
import LoadingLogoMobile from "../components/LoadingLogoMobile.tsx";
import LoadingLogoDesktop from "../components/LoadingLogoDesktop.tsx";
import {ALREADY_MEMBER_ERROR_CODE} from "../api/_types.ts";

type PageLayoutProps = PropsWithChildren<{
	isLoading: boolean,
}>

const PageLayoutDesktop = ({isLoading, children}: PageLayoutProps) => {

	return (
		<Center
			userSelect="none"
			fontFamily="lato_regular"
			minHeight="100vh"
			maxWidth="100vw"
			className={"main-bg"}
		>
			<Box
				bgColor="393a4d"
				display="flex"
				justifyContent="center"
				alignItems="center"
				width="100%"
				height="100%"
				overflow="hidden"
				position="absolute"
			>
				<Box className={"animated-bg-desktop"}/>
			</Box>
			{isLoading ? (
				<LoadingLogoDesktop/>
			) : (
				<>{children}</>
			)}
		</Center>
	);
}

const PageLayoutMobile = ({isLoading, children}: PageLayoutProps) => {

	const {activePage} = useNavigatePage();
	const {activeDialog} = useNavigateDialog();
	const prepareJoinLinkError = useAtomValue(prepareJoinLinkErrorState);

	if (activePage === "token" && !isLoading) {

		if (prepareJoinLinkError === null || prepareJoinLinkError.error_code === ALREADY_MEMBER_ERROR_CODE) {

			return (
				<VStack
					userSelect="none"
					bgColor="393a4d"
					fontFamily="lato_regular"
					className={"main-bg h100dvh"}
				>
					<Box
						bgColor="393a4d"
						display="flex"
						justifyContent="center"
						alignItems="center"
						width="100%"
						height="100%"
						overflow="hidden"
						position="absolute"
					>
						<Box className={"animated-bg-mobile"}/>
					</Box>
					<>{children}</>
				</VStack>
			);
		}
	}

	return (
		<Center
			userSelect="none"
			bgColor="393a4d"
			fontFamily="lato_regular"
			className={`main-bg ${!isLoading && activePage === "auth" && (activeDialog === "auth_phone_number" || activeDialog === "auth_confirm_code" || activeDialog === "auth_create_profile") ? "h100vh" : "h100dvh"}`}
		>
			<Box
				bgColor="393a4d"
				display="flex"
				justifyContent="center"
				alignItems="center"
				width="100%"
				height="100%"
				overflow="hidden"
				position="absolute"
			>
				<Box className={"animated-bg-mobile"}/>
			</Box>
			{isLoading ? (
				<LoadingLogoMobile/>
			) : (
				<>{children}</>
			)}
		</Center>
	);
}

const PageLayout = ({children}: PropsWithChildren) => {

	const isMobile = useIsMobile();
	const isLoading = useAtomValue(loadingState);

	if (isMobile) {

		return <PageLayoutMobile
			isLoading={isLoading}
			children={children}
		/>
	}

	return <PageLayoutDesktop
		isLoading={isLoading}
		children={children}
	/>
}

export default PageLayout;
