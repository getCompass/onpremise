import {useEffect, useState} from "react";

const useIsMobile = (): boolean => {

	const [isMobile, setIsMobile] = useState<boolean>(checkMobile());

	function checkMobile(): boolean {

		// проверка по ширине экрана
		const widthCheck = window.innerWidth <= 768;

		// проверка по user-agent
		const ua = window.navigator.userAgent;
		const mobileCheck = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(ua);

		return widthCheck || mobileCheck;
	}

	// на случай изменения разрешения (например повернули телефон боком или уменьшили размер браузера)
	useEffect(() => {

		const handleResize = () => {
			setIsMobile(checkMobile());
		};

		window.addEventListener("resize", handleResize);
		return () => window.removeEventListener("resize", handleResize);
	}, []);

	return isMobile;
};

export default useIsMobile;
