import React, {ReactNode} from 'react';
import {makeStyles} from 'tss-react/mui';

import ContextMenuItem, {IProps as ItemProps} from './ContextMenuItem';


interface IProps {

    className?: string;

    /**
     * List of actions in this group.
     */
    actions?: Array<ItemProps>;

    /**
     * The children of the component.
     */
    children?: ReactNode;
}

const useStyles = makeStyles()(theme => {
    return {
        contextMenuItemGroup: {
            '&:not(:empty)': {
                padding: 0
            },

            '&:first-of-type': {
                paddingTop: 0
            },

            '&:last-of-type': {
                paddingBottom: 0
            }
        }
    };
});

const ContextMenuItemGroup = ({
                                  className,
                                  actions,
                                  children
                              }: IProps) => {
    const {classes: styles, cx} = useStyles();

    return (
        <div className={cx(styles.contextMenuItemGroup, 'context-menu-item-group', className ?? '')}>
            {children}
            {actions?.map(actionProps => (
                <ContextMenuItem
                    key={actionProps.text}
                    {...actionProps} />
            ))}
        </div>
    );
};

export default ContextMenuItemGroup;
