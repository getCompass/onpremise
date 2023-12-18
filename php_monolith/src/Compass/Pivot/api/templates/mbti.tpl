<!doctype html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<style>
        @font-face
        {
        	font-family: LatoRegular;
        	src: url(Lato-Regular.ttf);
        	font-weight: normal;
        	font-display: swap;
        }
        @font-face {
        	font-family: LatoBold;
        	src: url(Lato-Bold.ttf);
        	font-display: swap;
        }
        body {
        	font-family: LatoRegular, Arial, sans-serif;
        }
        b {
        	font-family: LatoBold, Arial, sans-serif;
        	font-weight: normal;
        }
        </style>
</head>
<body style="margin: 0"><div id="path_container" style="margin: 12px 16px 16px 16px; color: #333E49; font-size: 16px; line-height: 19.2px; white-space: pre-wrap;">{TEXT}</div>
<script>
	// закрасить список диапазонов в контейнере
	function highlightColorSelectionList(text_container, color_selection_list) {

		// создаем копию текущего контейнера с текстом
		let cloned_text_container = text_container.cloneNode(true);

		for (let color_selection of color_selection_list) {

			// начало и конец диапазона
			let start = color_selection.position;
			let end   = color_selection.position + color_selection.length;

			// получим ноды и смещение по которым нужно сделать выделение
			let selection_nodes = getSelectionNodes(cloned_text_container, start, end);

			// создаем спан для обертки
			let span = document.createElement('span');

			// задаем цвет фона для обертки
			if (color_selection.color_id == 1) {
				span.style.backgroundColor = '#D7FFC3';
			}
			if (color_selection.color_id == 2) {
				span.style.backgroundColor = '#F6C7FF';
			}

			// создаем новый диапазон
			let range = new Range();

			// задаем стартовую и конечную позицию
			range.setStart(selection_nodes.start_node, selection_nodes.start_offset);
			range.setEnd(selection_nodes.end_node, selection_nodes.end_offset);

			// оборачиваем выбранный диапазон в span и удаляем его из дом дерева
			span.appendChild(range.extractContents());

			// ваставляем обратно обернутый диапазон
			range.insertNode(span);
		}

		// заменяем текст на выделеный
		text_container.replaceWith(cloned_text_container);
	}

	// получить начальную и конечную ноды на которые попадает выделение
	function getSelectionNodes(text_container, start, end) {

		// получим массив текстовых нод
		let text_node_list = getTextNodesIn(text_container);

		// нашли начало
		let is_found_start = false;

		// пройденное кол-во символов
		let offset_count = 0;

		// конечное кол-во символов
		let total_count;

		// данные о нодах в диапазоне
		let result = {
			start_node  : null,
			start_offset: 0,
			end_node    : null,
			end_offset  : 0
		};

		for (let text_node of text_node_list) {

			// конечное кол-во символов
			total_count = offset_count + text_node.nodeValue.length;

			// если нода включает в себя стартовую позицию
			if (!is_found_start && start >= offset_count && start <= total_count) {

				// сохраним данные для стартовой ноды
				result.start_node   = text_node;
				result.start_offset = start - offset_count;

				// поментим что нали старт
				is_found_start = true;
			}

			// если уже нашли старт и коненое кол-во меньше конечной позиции
			if (is_found_start && end >= offset_count && end <= total_count) {

				// сохраним данные для конечной ноды
				result.end_node   = text_node;
				result.end_offset = end - offset_count;

				// выйдем из цикла
				break;
			}

			// сохраним общее кол-во символов
			offset_count = total_count;
		}

		return result;
	}

	function getTextNodesIn(node) {

		// тип текстовой ноды
		const TEXT_NODE = 3;

		// сюда запишем все текстовые ноды
		let text_nodes = [];

		// если нода текстовая и не пустая
		if (node.nodeType === TEXT_NODE && node.nodeValue !== '') {

			// запишем в массив
			text_nodes.push(node);
		} else {

			// список дочерни нод
			let children = node.childNodes;

			// пройдем по вме дочерним нода
			for (let i = 0; i < children.length; i++) {

				// добавим все текстовые дочерние ноды
				text_nodes.push(...getTextNodesIn(children[i]));
			}
		}

		return text_nodes;
	}

	document.addEventListener('DOMContentLoaded', () => {

		let container = document.getElementById('path_container');

		let color_selection_list = {COLOR_SELECTION_LIST};

		highlightColorSelectionList(container, color_selection_list);
	});

</script>
</body>
</html>