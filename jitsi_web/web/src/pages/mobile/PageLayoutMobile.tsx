import Background from "../../components/mobile/Background.tsx";
import { VStack } from "../../../styled-system/jsx";
import { PageLayoutProps } from "../PageLayout.tsx";
import Toast from "../../components/Toast.tsx";
import { useToastConfig } from "../../api/_stores.ts";

const PageLayoutMobile = ({ children }: PageLayoutProps) => {
	const toastConfig = useToastConfig("page_main");

	return (
		<>
			<Toast toastConfig={toastConfig} />
			<Background />
			<VStack className="page-layout-mobile" gap="0" px="24px">
				{children}
			</VStack>
		</>
	);
};

export default PageLayoutMobile;
