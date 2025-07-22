import {CAPTCHA_PROVIDER_ENTERPRISE_GOOGLE, CAPTCHA_PROVIDER_YANDEX} from "./../api/_types.ts";

export function doCaptchaRender(
	node: HTMLDivElement | null,
	captchaPublicKey: string,
	captchaProvider: string,
	setGrecaptchaResponse: (value: string) => void
) {
	let widgetId = "";
	switch (captchaProvider) {
		case CAPTCHA_PROVIDER_ENTERPRISE_GOOGLE:
		default:
			// @ts-ignore
			grecaptcha.enterprise.render(node, {
				sitekey: captchaPublicKey,
				action: "check_captcha",
				callback: function (grecaptchaResponse: string) {
					setGrecaptchaResponse(grecaptchaResponse);
				},
			});
			break;
		case CAPTCHA_PROVIDER_YANDEX:
			// @ts-ignore
			widgetId = window.smartCaptcha.render(node, {
				sitekey: captchaPublicKey,
				invisible: false, // Сделать капчу невидимой
				callback: function (grecaptchaResponse: string) {
					setGrecaptchaResponse(grecaptchaResponse);
				},
			});
			break;
	}

	return widgetId;
}

export function doCaptchaReset(captchaProvider: string, widgetId: string) {
	switch (captchaProvider) {
		case CAPTCHA_PROVIDER_ENTERPRISE_GOOGLE:
		default:
			// @ts-ignore
			if (grecaptcha.enterprise.reset !== undefined) {
				try {
					// @ts-ignore
					grecaptcha.enterprise.reset();
				} catch (error) {
					// в случае если упала ошибка - скорее всего при отрисованной капче на сервере сменили тип капчи
					// с гугла на яндекс
					// в таком случае мы никак не можем убрать гугл капчу со страницы и нужно релоадить
					// в инпуте все сохранится
					window.location.reload();
				}
			}
			break;
		case CAPTCHA_PROVIDER_YANDEX:
			// @ts-ignore
			if (window.smartCaptcha.reset !== undefined) {
				try {
					// @ts-ignore
					window.smartCaptcha.reset(widgetId);
				} catch (error) {
					// в случае если упала ошибка - скорее всего при отрисованной капче на сервере сменили тип капчи
					// с яндекса на гугл
					// в таком случае мы никак не можем убрать гугл капчу со страницы и нужно релоадить
					// в инпуте все сохранится
					window.location.reload();
				}
			}
			break;
	}
}

export function doCaptchaReady(captchaProvider: string, setIsLoginCaptchaRendered: Function) {
	switch (captchaProvider) {
		case CAPTCHA_PROVIDER_ENTERPRISE_GOOGLE:
		default:
			// @ts-ignore
			grecaptcha.enterprise.ready(function () {
				setTimeout(() => {
					setIsLoginCaptchaRendered(true);
				}, 500);
			});
			break;
		case CAPTCHA_PROVIDER_YANDEX:
			// @ts-ignore
			setTimeout(() => {
				setIsLoginCaptchaRendered(true);
			}, 350);
			break;
	}
}

export function arraysEqual(a: string[], b: string[]): boolean {
	if (a.length !== b.length) return false;
	return a.every((value, index) => value === b[index]);
}