import useIsMobile from "../lib/useIsMobile.ts";
import PageInviteMobile from "./PageInviteMobile.tsx";
import PageInviteDesktop from "./PageInviteDesktop.tsx";
import {VStack} from "../../styled-system/jsx";
import {Text} from "../components/text.tsx";
import {useLangString} from "../lib/getLangString.ts";
import HeaderWaitingForPostModerationLogoMobile from "../components/HeaderWaitingForPostModerationLogoMobile.tsx";
import HeaderWaitingForPostModerationLogoDesktop from "../components/HeaderWaitingForPostModerationLogoDesktop.tsx";

const PageInviteWaitingForPostModeration = () => {

    const langStringPageInviteWaitingForPostModerationTitle = useLangString("page_invite.waiting_for_postmoderation.title");
    const langStringPageInviteWaitingForPostModerationDesc = useLangString("page_invite.waiting_for_postmoderation.desc");

    const isMobile = useIsMobile();

    if (isMobile) {

        return <PageInviteMobile
            headerContent={
                <VStack
                    gap="14px"
                    width="100%"
                >
                    <HeaderWaitingForPostModerationLogoMobile/>
                    <VStack w="100%" gap="4px" px="8px">
                        <Text
                            w="100%"
                            color="white"
                            font="bold"
                            ls="-03"
                            fs="20"
                            lh="28"
                            textAlign="center"
                        >
                            {langStringPageInviteWaitingForPostModerationTitle}
                        </Text>
                        <Text
                            w="100%"
                            color="white"
                            fs="16"
                            lh="22"
                            textAlign="center"
							font="regular"
                        >
                            {langStringPageInviteWaitingForPostModerationDesc}
                        </Text>
                    </VStack>
                </VStack>
            }
        />
    }

    return <PageInviteDesktop
        headerContent={
            <VStack
                w="100%"
                gap="16px"
            >
                <HeaderWaitingForPostModerationLogoDesktop/>
                <VStack w="100%" gap="6px">
                    <Text
                        w="100%"
                        color="white"
                        font="bold"
                        ls="-02"
                        fs="18"
                        lh="24"
                        textAlign="center"
                    >
                        {langStringPageInviteWaitingForPostModerationTitle}
                    </Text>
                    <Text
                        w="100%"
                        color="white"
                        fs="14"
                        lh="20"
                        textAlign="center"
						font="regular"
                    >
                        {langStringPageInviteWaitingForPostModerationDesc}
                    </Text>
                </VStack>
            </VStack>
        }
    />
}

export default PageInviteWaitingForPostModeration;
