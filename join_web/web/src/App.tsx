import {createHashRouter, RouterProvider} from "react-router-dom";
import {QueryClient, QueryClientProvider} from "@tanstack/react-query";
import {Provider} from "jotai";
import GlobalStartProvider from "./providers/GlobalStartProvider.tsx";
import PageLayout from "./pages/PageLayout.tsx";
import Auth from "./pages/auth/Auth.tsx";
import {useAtomValue} from "jotai";
import {firstAuthState, joinLinkState, prepareJoinLinkErrorState} from "./api/_stores.ts";
import PageToken from "./pages/PageToken.tsx";
import InactiveLink from "./pages/InactiveLink.tsx";
import InvalidLink from "./pages/InvalidLink.tsx";
import AcceptLimitLink from "./pages/AcceptLimitLink.tsx";
import {
	ALREADY_MEMBER_ERROR_CODE,
	INACTIVE_LINK_ERROR_CODE,
	LIMIT_ERROR_CODE,
	NEED_FINISH_SPACE_LEAVING_BEFORE_JOIN,
	PrepareJoinLinkErrorAlreadyMemberData,
} from "./api/_types.ts";
import PageInviteAlreadyMember from "./pages/PageInviteAlreadyMember.tsx";
import PageWelcomeJoinLink from "./pages/PageWelcomeJoinLink.tsx";
import {useNavigatePage} from "./components/hooks.ts";
import PageInvite from "./pages/PageInvite.tsx";
import useIsJoinLink from "./lib/useIsJoinLink.ts";
import PageInviteWaitingForPostModeration from "./pages/PageInviteWaitingForPostModeration.tsx";
import PageInviteAsGuest from "./pages/PageInviteAsGuest.tsx";
import ErrorPage from "./error-page.tsx";
import NeedFinishSpaceLeavingBeforeJoin from "./pages/NeedFinishSpaceLeavingBeforeJoin.tsx";

const Page = () => {

	const firstAuth = useAtomValue(firstAuthState);
	const prepareJoinLinkError = useAtomValue(prepareJoinLinkErrorState);
	const joinLink = useAtomValue(joinLinkState);
	const {activePage} = useNavigatePage();
	const isJoinLink = useIsJoinLink();

	switch (activePage) {

		case "welcome":

			if (prepareJoinLinkError !== null) {

				if (prepareJoinLinkError.error_code === NEED_FINISH_SPACE_LEAVING_BEFORE_JOIN) {
					return <NeedFinishSpaceLeavingBeforeJoin/>;
				}

				if (prepareJoinLinkError.error_code === INACTIVE_LINK_ERROR_CODE) {
					return <InactiveLink/>;
				}

				if (prepareJoinLinkError.error_code === LIMIT_ERROR_CODE) {
					return <AcceptLimitLink/>;
				}

				if (prepareJoinLinkError.error_code !== ALREADY_MEMBER_ERROR_CODE) {
					return <InvalidLink/>;
				}
			}
			return <PageWelcomeJoinLink/>;

		case "auth":

			if (prepareJoinLinkError !== null) {

				if (prepareJoinLinkError.error_code === NEED_FINISH_SPACE_LEAVING_BEFORE_JOIN) {
					return <NeedFinishSpaceLeavingBeforeJoin/>;
				}

				if (prepareJoinLinkError.error_code === INACTIVE_LINK_ERROR_CODE) {
					return <InactiveLink/>;
				}

				if (prepareJoinLinkError.error_code === LIMIT_ERROR_CODE) {
					return <AcceptLimitLink/>;
				}

				if (prepareJoinLinkError.error_code !== ALREADY_MEMBER_ERROR_CODE) {
					return <InvalidLink/>;
				}
			}
			return <Auth/>;

		case "token":

			if (prepareJoinLinkError !== null) {

				if (prepareJoinLinkError.error_code === NEED_FINISH_SPACE_LEAVING_BEFORE_JOIN) {
					return <NeedFinishSpaceLeavingBeforeJoin/>;
				}

				if (prepareJoinLinkError.error_code === INACTIVE_LINK_ERROR_CODE) {
					return <InactiveLink/>;
				}

				if (prepareJoinLinkError.error_code === ALREADY_MEMBER_ERROR_CODE) {

					if ((prepareJoinLinkError.data as PrepareJoinLinkErrorAlreadyMemberData).is_waiting_for_postmoderation == 1) {
						return <PageInviteWaitingForPostModeration/>;
					}

					return <PageInviteAlreadyMember/>;
				}

				if (prepareJoinLinkError.error_code === LIMIT_ERROR_CODE) {
					return <AcceptLimitLink/>;
				}

				return <InvalidLink/>;
			}

			// если нужно отрисовать какую-то другую доп страницу
			if (joinLink !== null && !firstAuth) {

				if (joinLink.is_postmoderation === 1 || joinLink.is_waiting_for_postmoderation === 1) {
					return <PageInviteWaitingForPostModeration/>;
				}

				if (joinLink.role === "guest") {
					return <PageInviteAsGuest/>;
				}
			}

			if (isJoinLink && !firstAuth) {
				return <PageInvite/>;
			}

			return <PageToken/>;

		default:
			return <Auth/>;
	}
}

const router = createHashRouter([
	{
		index: true,
		element: (
			<PageLayout>
				<Page/>
			</PageLayout>
		),
		errorElement: <ErrorPage/>,
	},
]);

const queryClient = new QueryClient();

export default function App() {

	return (
		<Provider>
			<QueryClientProvider client={queryClient}>
				<GlobalStartProvider>
					<RouterProvider router={router}/>
				</GlobalStartProvider>
			</QueryClientProvider>
		</Provider>
	)
}
