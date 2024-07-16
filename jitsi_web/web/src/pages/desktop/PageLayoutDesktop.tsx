import { VStack } from "../../../styled-system/jsx";
import { PageLayoutProps } from "../PageLayout.tsx";
import Background from "../../components/desktop/Background.tsx";
import { useToastConfig } from "../../api/_stores.ts";
import Toast from "../../components/Toast.tsx";

const PageLayoutDesktop = ({ children }: PageLayoutProps) => {
	const toastConfig = useToastConfig("page_main");

	return (
		<>
			<Toast toastConfig={toastConfig} />
			<Background />
			<VStack minHeight="100vh" gap="0" px="32px">
				{children}
			</VStack>
		</>
	);
};

export default PageLayoutDesktop;
