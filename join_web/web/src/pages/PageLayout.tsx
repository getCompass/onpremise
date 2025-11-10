import useIsMobile from "../lib/useIsMobile.ts";
import { Box, Center, VStack } from "../../styled-system/jsx";
import { PropsWithChildren, useEffect, useRef, useState } from "react";
import { loadingState, prepareJoinLinkErrorState } from "../api/_stores.ts";
import { useAtomValue } from "jotai";
import { useNavigatePage } from "../components/hooks.ts";
import LoadingLogoMobile from "../components/LoadingLogoMobile.tsx";
import LoadingLogoDesktop from "../components/LoadingLogoDesktop.tsx";
import { ALREADY_MEMBER_ERROR_CODE } from "../api/_types.ts";
import { Property } from "../../styled-system/types/csstype";

type PageLayoutProps = PropsWithChildren<{
	isLoading: boolean;
}>;

const PageLayoutDesktop = ({ isLoading, children }: PageLayoutProps) => {
	const blockRef = useRef<HTMLDivElement>(null);
	const [bgHeight, setBgHeight] = useState<Property.Height | null>(null);

	useEffect(() => {
		const handleResize = () => {
			if (!blockRef.current) {
				return;
			}

			setBgHeight(`${blockRef.current?.clientHeight}px`);
		};

		window.addEventListener("resize", handleResize);
		return () => window.removeEventListener("resize", handleResize);
	}, []);

	return (
		<Center
			ref={blockRef}
			bgColor="393a4d"
			userSelect="none"
			fontFamily="lato_regular"
			minHeight="100vh"
			maxWidth="100vw"
			className={"main-bg"}
		>
			<Box
				bgColor="393a4d"
				minHeight="100vh"
				display="flex"
				justifyContent="center"
				alignItems="center"
				width="100%"
				overflow="hidden"
				position="absolute"
				style={{
					height: bgHeight === null ? "100%" : bgHeight,
				}}
			>
				<Box className={"static-bg-desktop"} />
			</Box>
			{isLoading ? <LoadingLogoDesktop /> : <>{children}</>}
		</Center>
	);
};

const PageLayoutMobile = ({ isLoading, children }: PageLayoutProps) => {
	const { activePage } = useNavigatePage();
	const prepareJoinLinkError = useAtomValue(prepareJoinLinkErrorState);

	if (activePage === "token" && !isLoading) {
		if (prepareJoinLinkError === null || prepareJoinLinkError.error_code === ALREADY_MEMBER_ERROR_CODE) {
			return (
				<>
					<Box
						bgColor="393a4d"
						display="flex"
						justifyContent="center"
						alignItems="center"
						width="100%"
						minHeight="100vh"
						height="100%"
						overflow="hidden"
						position="absolute"
						className={"static-bg-mobile"}
					/>
					<VStack
						userSelect="none"
						bgColor="393a4d"
						fontFamily="lato_regular"
						w="100%"
						minHeight="100vh"
						className={"main-bg"}
					>
						{children}
					</VStack>
				</>
			);
		}
	}

	return (
		<>
			<Box
				bgColor="393a4d"
				display="flex"
				justifyContent="center"
				alignItems="center"
				width="100%"
				minHeight="100vh"
				height="100%"
				overflow="hidden"
				position="absolute"
				className={"static-bg-mobile"}
			/>
			<Center
				userSelect="none"
				bgColor="393a4d"
				w="100%"
				minHeight="100vh"
				fontFamily="lato_regular"
				className="main-bg h100vh"
			>
				{isLoading ? <LoadingLogoMobile /> : <>{children}</>}
			</Center>
		</>
	);
};

const PageLayout = ({ children }: PropsWithChildren) => {
	const isMobile = useIsMobile();
	const isLoading = useAtomValue(loadingState);

	if (isMobile) {
		return <PageLayoutMobile isLoading={isLoading} children={children} />;
	}

	return <PageLayoutDesktop isLoading={isLoading} children={children} />;
};

export default PageLayout;
