import {useLangString} from "../lib/getLangString.ts";
import {useCallback, useEffect, useMemo, useState} from "react";
import dayjs from "dayjs";
import {Button} from "./button.tsx";
import {Text} from "./text.tsx";
import {HStack} from "../../styled-system/jsx";
import {ApiAuthGenerateToken, ApiAuthGenerateTokenAcceptArgs} from "../api/auth.ts";
import {useAtom, useAtomValue} from "jotai/index";
import {authenticationTokenTimeLeft, joinLinkState} from "../api/_stores.ts";
import {UseMutationResult} from "@tanstack/react-query";
import {plural} from "../lib/plural.ts";
import useIsMobile from "../lib/useIsMobile.ts";
import useIsTabActive from "../lib/useIsTabActive.ts";

type DynamicTimerAuthenticationTokenProps = {
	apiAuthGenerateToken: UseMutationResult<ApiAuthGenerateToken, unknown, ApiAuthGenerateTokenAcceptArgs, unknown>;
};

export const DynamicTimerAuthenticationToken = ({apiAuthGenerateToken}: DynamicTimerAuthenticationTokenProps) => {
	const isMobile = useIsMobile();
	const isTabActive = useIsTabActive();

	const langStringTokenLifeTimeExpired = useLangString("token_life_time_expired");
	const langStringTokenLifeTime = useLangString(isMobile ? "token_life_time_mobile" : "token_life_time_desktop");

	const langStringOneMinute = useLangString(isMobile ? "one_minute" : "two_minutes");
	const langStringTwoMinutes = useLangString(isMobile ? "two_minutes" : "five_minutes");
	const langStringFiveMinutes = useLangString("five_minutes");
	const langStringRefreshToken = useLangString("page_token.step_1.update_token");

	const [expiresAt, setExpiresAt] = useState(
		apiAuthGenerateToken.data !== undefined ? apiAuthGenerateToken.data.expires_at : 0
	);
	const [timeLeft, setTimeLeft] = useAtom(authenticationTokenTimeLeft);
	const minutes = useMemo(() => Math.ceil(timeLeft / 60), [timeLeft]);
	const joinLink = useAtomValue(joinLinkState);

	// обновляем таймер, когда пользователь вернулся на страницу
	// нужно для того, чтобы правильно обновить таймер при выходе из бэкграунда мобильных устройств
	useEffect(() => {
		setTimeLeft(expiresAt - dayjs().unix());
	}, [isTabActive]);

	useEffect(() => {

		const timer = setInterval(() => {
			setTimeLeft((prevTime) => {
				return prevTime - 1;
			});
		}, 1000);
		return () => clearInterval(timer);
	}, []);

	const onRefreshClickHandler = useCallback(async () => {
		const response = await apiAuthGenerateToken.mutateAsync({
			join_link_uniq: joinLink === null ? undefined : joinLink.join_link_uniq,
		});
		setExpiresAt(response.expires_at);
	}, [joinLink]);

	// если время истекло, отображаем кнопку повторить
	if (timeLeft <= 0) {
		return (
			<HStack
				gap="3px"
				mt="12px"
				w="100%"
				alignItems="center"
				justifyContent="center"
				alignContent="start"
				flexWrap="wrap">
				<Text
					color="ff6a64"
					letterSpacing="-0.15px"
					style="lato_14_20_400"
					opacity="80%"> {langStringTokenLifeTimeExpired}
				</Text>
				<Button
					p="0px"
					minHeight="20px"
					size="px0py0"
					textSize="lato_14_20_400"
					color="f8f8f8_80"
					textDecoration="underline"
					textDecorationSkipInk="none"
					textDecorationThickness="1px"
					textUnderlineOffset="4px"
					letterSpacing="-0.15px"
					onClick={() => onRefreshClickHandler()}
				>
					{langStringRefreshToken}
				</Button>
			</HStack>
		);
	}

	return (
		<HStack
			gap="3px"
			mt="12px"
			w="100%"
			alignItems="center"
			justifyContent="center"
			alignContent="start"
			flexWrap="wrap">
			<Text color="248248248.05" style="lato_14_20_400" ls="-015">
				{langStringTokenLifeTime}
				{minutes}
				{plural(minutes, langStringOneMinute, langStringTwoMinutes, langStringFiveMinutes)}.
			</Text>
		</HStack>
	);
};
