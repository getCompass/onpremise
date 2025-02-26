const getLeftAlignedStyle = (bounds: DOMRect) => {
    return {
        position: 'fixed',
        right: `${window.innerWidth - bounds.x}px`
    };
};

const getRightAlignedStyle = (bounds: DOMRect) => {
    return {
        position: 'fixed',
        left: `${bounds.x + bounds.width}px`
    };
};

const getTopAlignedStyle = (bounds: DOMRect) => {
    return {
        position: 'fixed',
        bottom: `${window.innerHeight - bounds.y}px`
    };
};

const getBottomAlignedStyle = (bounds: DOMRect) => {
    return {
        position: 'fixed',
        top: `${bounds.y + bounds.height}px`
    };
};

const getLeftRightStartAlign = (bounds: DOMRect, size: DOMRectReadOnly) => {
    return {
        top: `${Math.min(bounds.y, window.innerHeight - size.height - 20)}px`
    };
};

const getLeftRightMidAlign = (bounds: DOMRect, size: DOMRectReadOnly) => {
    return {
        bottom: `${window.innerHeight - bounds.y - (bounds.height / 2) - (size.height / 2)}px`
    };
};

const getLeftRightEndAlign = (bounds: DOMRect, size: DOMRectReadOnly) => {
    return {
        bottom: `${Math.min(window.innerHeight - bounds.y - bounds.height, window.innerHeight - size.height)}px`
    };
};

const getTopBotStartAlign = (bounds: DOMRect) => {
    return {
        right: `${window.innerWidth - bounds.x - 6}px`
    };
};

const getTopBotMidAlign = (bounds: DOMRect, size: DOMRectReadOnly) => {
    return {
        right: `${window.innerWidth - bounds.x - (bounds.width / 2) - (size.width / 2)}px`
    };
};

const getTopBotStartBlockAlign = (bounds: DOMRect, size: DOMRectReadOnly) => {
    return {
        right: `${window.innerWidth - bounds.x - size.width}px`
    };
};

const getTopBotEndAlign = (bounds: DOMRect) => {
    return {
        left: `${bounds.x + bounds.width - 6}px`
    };
};

/**
 * Gets the trigger element's and the context menu's bounds/size info and
 * computes the style to apply to the context menu to positioning it correctly
 * in regards to the given position info.
 *
 * @param {DOMRect} triggerBounds -The bounds info of the trigger html element.
 * @param {DOMRectReadOnly} dialogSize - The size info of the context menu.
 * @param {string} position - The position of the context menu in regards to the trigger element.
 *
 * @returns {Object} = The style to apply to context menu for positioning it correctly.
 */
export const getContextMenuStyle = (triggerBounds: DOMRect,
    dialogSize: DOMRectReadOnly,
    position: string) => {
    const parsed = position.split('-');
    const OFFSET = 8;

    const keepInBounds = (style: any) => {
        const adjustedStyle = { ...style };

        // Проверка и корректировка по горизонтали
        if (adjustedStyle.left !== undefined) {
            const left = parseFloat(adjustedStyle.left);
            if (left < OFFSET) adjustedStyle.left = `${OFFSET}px`;
            if (left + dialogSize.width > window.innerWidth - OFFSET) {
                adjustedStyle.left = `${window.innerWidth - dialogSize.width - OFFSET}px`;
            }
        }
        if (adjustedStyle.right !== undefined) {
            const right = parseFloat(adjustedStyle.right);
            if (right < OFFSET) adjustedStyle.right = `${OFFSET}px`;
            if (right + dialogSize.width > window.innerWidth - OFFSET) {
                adjustedStyle.right = `${window.innerWidth - dialogSize.width - OFFSET}px`;
            }
        }

        // Проверка и корректировка по вертикали
        if (adjustedStyle.top !== undefined) {
            const top = parseFloat(adjustedStyle.top);
            if (top < OFFSET) adjustedStyle.top = `${OFFSET}px`;
            if (top + dialogSize.height > window.innerHeight - OFFSET) {
                adjustedStyle.top = `${window.innerHeight - dialogSize.height - OFFSET}px`;
            }
        }
        if (adjustedStyle.bottom !== undefined) {
            const bottom = parseFloat(adjustedStyle.bottom);
            if (bottom < OFFSET) adjustedStyle.bottom = `${OFFSET}px`;
            if (bottom + dialogSize.height > window.innerHeight - OFFSET) {
                adjustedStyle.bottom = `${window.innerHeight - dialogSize.height - OFFSET}px`;
            }
        }

        return adjustedStyle;
    };

    let alignmentStyle = {};
    switch (parsed[0]) {
    case 'top': {
        if (parsed[1]) {
            alignmentStyle = parsed[1] === 'start'
                ? getTopBotStartAlign(triggerBounds)
                : parsed[1] === 'mid'
                    ? getTopBotMidAlign(triggerBounds, dialogSize)
                    : parsed[1] === 'start_block'
                        ? getTopBotStartBlockAlign(triggerBounds, dialogSize)
                        : getTopBotEndAlign(triggerBounds);
        } else {
            alignmentStyle = getTopBotMidAlign(triggerBounds, dialogSize);
        }
        return keepInBounds({
            ...getTopAlignedStyle(triggerBounds),
            ...alignmentStyle
        });
    }
    case 'bottom': {
        if (parsed[1]) {
            alignmentStyle = parsed[1] === 'start'
                ? getTopBotStartAlign(triggerBounds)
                : parsed[1] === 'mid'
                    ? getTopBotMidAlign(triggerBounds, dialogSize)
                    : getTopBotEndAlign(triggerBounds);
        } else {
            alignmentStyle = getTopBotMidAlign(triggerBounds, dialogSize);
        }
        return keepInBounds({
            ...getBottomAlignedStyle(triggerBounds),
            ...alignmentStyle
        });
    }
    case 'left': {
        if (parsed[1]) {
            alignmentStyle = parsed[1] === 'start'
                ? getLeftRightStartAlign(triggerBounds, dialogSize)
                : parsed[1] === 'mid'
                    ? getLeftRightMidAlign(triggerBounds, dialogSize)
                    : getLeftRightEndAlign(triggerBounds, dialogSize);
        } else {
            alignmentStyle = getLeftRightMidAlign(triggerBounds, dialogSize);
        }
        return keepInBounds({
            ...getLeftAlignedStyle(triggerBounds),
            ...alignmentStyle
        });
    }
    case 'right': {
        if (parsed[1]) {
            alignmentStyle = parsed[1] === 'start'
                ? getLeftRightStartAlign(triggerBounds, dialogSize)
                : parsed[1] === 'mid'
                    ? getLeftRightMidAlign(triggerBounds, dialogSize)
                    : getLeftRightEndAlign(triggerBounds, dialogSize);
        } else {
            alignmentStyle = getLeftRightMidAlign(triggerBounds, dialogSize);
        }
        return keepInBounds({
            ...getRightAlignedStyle(triggerBounds),
            ...alignmentStyle
        });
    }
    default: {
        return keepInBounds({
            ...getLeftAlignedStyle(triggerBounds),
            ...getLeftRightEndAlign(triggerBounds, dialogSize)
        });
    }
    }
};

