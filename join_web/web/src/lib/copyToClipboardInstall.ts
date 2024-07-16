// функция для копирования текста в буфер обмена
export function copyToClipboardInstall(text: string, textOnSuccess: string): void {
	// создание нового текстового поля (невидимого)
	const tempElement = document.createElement("textarea");

	// устанавливаем значение, которое нужно скопировать
	tempElement.value = text;

	// стилизуем, чтобы он был "невидимым" и не влиял на layout страницы
	tempElement.style.position = "fixed";
	tempElement.style.top = "0";
	tempElement.style.left = "-9999px";
	tempElement.setAttribute("readonly", ""); // предотвращение отображения клавиатуры на мобильных устройствах

	// добавление элемента в документ
	document.body.appendChild(tempElement);

	// выбор текста внутри элемента
	tempElement.select();
	tempElement.setSelectionRange(0, 99999); // Для мобильных устройств

	// копирование текста в буфер обмена
	document.execCommand("copy");

	// удаление временного элемента из документа
	document.body.removeChild(tempElement);

	// показываем анимашку
	showNotification(textOnSuccess);
}

function showNotification(textOnSuccess: string): void {
	const rootBlock = document.getElementById("root");

	if (rootBlock === null) {
		return;
	}

	// создаем элемент уведомления
	const notificationContainer = document.createElement("div");
	notificationContainer.style.width = "100%";
	notificationContainer.style.position = "fixed";
	notificationContainer.style.zIndex = "99999";
	notificationContainer.style.top = "0px";
	notificationContainer.style.left = "0px";
	notificationContainer.style.padding = "24px";
	notificationContainer.style.pointerEvents = "none"; // делаем элемент "прозрачным" для событий нажатий и прочего

	const notification = document.createElement("div");
	notification.style.width = "100%";
	notification.style.borderRadius = "12px";
	notification.style.padding = "16px";
	notification.style.background = "rgba(5, 196, 107, 1)";
	notification.style.opacity = "0";
	notification.style.transition = "opacity 200ms ease-out";
	notification.style.pointerEvents = "none"; // делаем элемент "прозрачным" для событий нажатий и прочего
	notification.style.textAlign = "center";
	notification.style.color = "rgba(255, 255, 255, 1)";
	notification.style.fontFamily = "Inter Medium";
	notification.style.fontWeight = "normal";
	notification.style.fontSize = "18px";
	notification.style.lineHeight = "25px";
	notification.textContent = textOnSuccess;

	// добавляем на страницу
	notificationContainer.appendChild(notification);
	rootBlock.appendChild(notificationContainer);

	// анимация появления
	setTimeout(() => {
		notification.style.opacity = "1";
	}, 0);

	// анимация исчезновения и удаление элемента
	setTimeout(() => {
		notification.style.opacity = "0";
		setTimeout(() => {
			rootBlock.removeChild(notificationContainer);
		}, 200); // должно соответствовать длительности анимации
	}, 1500); // время отображения
}
