import useIsMobile from "../lib/useIsMobile.ts";
import PageInviteMobile from "./PageInviteMobile.tsx";
import PageInviteDesktop from "./PageInviteDesktop.tsx";
import {VStack} from "../../styled-system/jsx";
import {Text} from "../components/text.tsx";
import HeaderLogoMobile from "../components/HeaderLogoMobile.tsx";
import HeaderLogoDesktop from "../components/HeaderLogoDesktop.tsx";
import {useLangString} from "../lib/getLangString.ts";

const PageInviteAlreadyMember = () => {

    const langStringPageInviteAlreadyMemberTitle = useLangString("page_invite.already_member.title");
    const langStringPageInviteAlreadyMemberDesc = useLangString("page_invite.already_member.desc");

    const isMobile = useIsMobile();

    if (isMobile) {

        return <PageInviteMobile
            headerContent={
                <VStack
                    gap="14px"
                    width="100%"
                >
                    <HeaderLogoMobile/>
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
                            {langStringPageInviteAlreadyMemberTitle}
                        </Text>
                        <Text
                            w="100%"
                            color="white"
                            fs="16"
                            lh="22"
                            textAlign="center"
                        >
                            {langStringPageInviteAlreadyMemberDesc}
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
                <HeaderLogoDesktop/>
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
                        {langStringPageInviteAlreadyMemberTitle}
                    </Text>
                    <Text
                        w="100%"
                        color="white"
                        fs="14"
                        lh="20"
                        textAlign="center"
                    >
                        {langStringPageInviteAlreadyMemberDesc}
                    </Text>
                </VStack>
            </VStack>
        }
    />
}

export default PageInviteAlreadyMember;
