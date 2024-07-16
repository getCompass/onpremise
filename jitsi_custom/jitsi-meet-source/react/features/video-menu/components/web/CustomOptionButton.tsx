import React, {useCallback} from 'react';

import ContextMenuItem from '../../../base/ui/components/web/ContextMenuItem';
import Icon from "../../../base/icons/components/Icon";
import {isMobileBrowser} from "../../../base/environment/utils";

const CustomOptionButton = (
    {icon: iconSrc, onClick, text, className}:
        {
            icon: string;
            onClick: (e?: React.MouseEvent<Element, MouseEvent> | undefined) => void;
            text: string;
            className?: string;
        }
) => {

    const isMobile = isMobileBrowser();

    const icon = useCallback(props => (<img
        src={iconSrc}
        {...props} />), [iconSrc]);

    return (
        <ContextMenuItem
            accessibilityLabel={text}
            className={className}
            customIcon={<Icon
                className={isMobile ? 'is-mobile' : ''}
                size={isMobile ? 22 : 18}
                src={icon}
                color={'rgba(255, 255, 255, 0.3)'}/>}
            onClick={onClick}
            text={text}/>
    );
};

export default CustomOptionButton;
