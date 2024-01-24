// функция для копирования текста в буфер обмена
export function copyToClipboard(text: string, parentScrollableBlock: HTMLDivElement, referenceElement: HTMLDivElement): void {

    // создание нового текстового поля (невидимого)
    const tempElement = document.createElement("textarea");

    // устанавливаем значение, которое нужно скопировать
    tempElement.value = text;

    // стилизуем, чтобы он был "невидимым" и не влиял на layout страницы
    tempElement.style.position = "fixed";
    tempElement.style.top = "0";
    tempElement.style.left = "-9999px";
    tempElement.setAttribute("readonly", "");  // предотвращение отображения клавиатуры на мобильных устройствах

    // добавление элемента в документ
    document.body.appendChild(tempElement);

    // выбор текста внутри элемента
    tempElement.select();
    tempElement.setSelectionRange(0, 99999);  // Для мобильных устройств

    // копирование текста в буфер обмена
    document.execCommand("copy");

    // удаление временного элемента из документа
    document.body.removeChild(tempElement);

    // показываем анимашку
    showNotification(parentScrollableBlock, referenceElement);
}


function showNotification(parentScrollableBlock: HTMLDivElement, referenceElement: HTMLDivElement): void {

    // если элемента не существует
    if (!referenceElement) return;

    const computedStyles = window.getComputedStyle(referenceElement);
    const svgElement = makeSvgIcon();

    // создаем элемент уведомления
    const notification = document.createElement("div");

    // получаем координаты referenceElement относительно окна просмотра
    const referenceRect = referenceElement.getBoundingClientRect();
    const parentRect = parentScrollableBlock.getBoundingClientRect();

    const relativeTop = referenceRect.top - parentRect.top + parentScrollableBlock.scrollTop;
    const relativeLeft = referenceRect.left - parentRect.left + parentScrollableBlock.scrollLeft;

    notification.style.position = "absolute";
    notification.style.top = `${relativeTop}px`;
    notification.style.left = `${relativeLeft}px`;
    notification.style.width = `${referenceRect.width}px`;
    notification.style.height = `${referenceRect.height}px`;
    notification.style.borderRadius = computedStyles.borderRadius;
    notification.style.padding = "10px";
    notification.style.border = "1px solid rgba(5, 196, 107, 1)";
    notification.style.background = "rgba(5, 196, 107, 0.05)";
    notification.style.opacity = "0";
    notification.style.transition = "opacity 200ms ease-out";
    notification.style.pointerEvents = "none"; // делаем элемент "прозрачным" для событий нажатий и прочего

    // добавляем на страницу
    parentScrollableBlock.appendChild(notification);

    // добавляем иконку в уведомление
    notification.appendChild(svgElement);

    // анимация появления
    setTimeout(() => {
        notification.style.opacity = "1";
    }, 0);

    // анимация исчезновения и удаление элемента
    setTimeout(() => {

        notification.style.opacity = "0";
        setTimeout(() => {
            parentScrollableBlock.removeChild(notification);
        }, 200); // должно соответствовать длительности анимации
    }, 1500); // время отображения
}

function makeSvgIcon() {

    // создаем SVG элемент c галочкой
    const svgElement = document.createElementNS("http://www.w3.org/2000/svg", "svg");
    svgElement.setAttribute("width", "20");
    svgElement.setAttribute("height", "20");
    svgElement.setAttribute("viewBox", "0 0 20 20");
    svgElement.setAttribute("fill", "none");

    const rect = document.createElementNS("http://www.w3.org/2000/svg", "rect");
    rect.setAttribute("width", "20");
    rect.setAttribute("height", "20");
    rect.setAttribute("rx", "10");
    rect.setAttribute("fill", "#434455");
    svgElement.appendChild(rect);

    const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
    path.setAttribute("fill-rule", "evenodd");
    path.setAttribute("clip-rule", "evenodd");
    path.setAttribute("d", "M9.99935 18.3333C14.6017 18.3333 18.3327 14.6023 18.3327 9.99996C18.3327 5.39759 14.6017 1.66663 9.99935 1.66663C5.39698 1.66663 1.66602 5.39759 1.66602 9.99996C1.66602 14.6023 5.39698 18.3333 9.99935 18.3333ZM13.7746 8.77523C14.0187 8.53116 14.0187 8.13543 13.7746 7.89135C13.5305 7.64727 13.1348 7.64727 12.8907 7.89135L9.16602 11.6161L7.10796 9.55802C6.86388 9.31394 6.46815 9.31394 6.22407 9.55802C5.98 9.8021 5.98 10.1978 6.22407 10.4419L8.72407 12.9419C8.84128 13.0591 9.00026 13.125 9.16602 13.125C9.33178 13.125 9.49075 13.0591 9.60796 12.9419L13.7746 8.77523Z");
    path.setAttribute("fill", "#05C46B");
    svgElement.appendChild(path);

    // меняем расположение на сверху справа
    svgElement.style.position = "absolute";
    svgElement.style.top = "-8px";
    svgElement.style.right = "-8px";

    return svgElement;
}