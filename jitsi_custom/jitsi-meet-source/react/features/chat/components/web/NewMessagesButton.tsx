import React from 'react';
import { WithTranslation } from 'react-i18next';
import { makeStyles } from 'tss-react/mui';

import { translate } from '../../../base/i18n/functions';
import Icon from '../../../base/icons/components/Icon';
import { IconArrowDown } from '../../../base/icons/svg';
import { withPixelLineHeight } from '../../../base/styles/functions.web';
import BaseTheme from '../../../base/ui/components/BaseTheme.web';

export interface INewMessagesButtonProps extends WithTranslation {

    /**
     * Function to notify messageContainer when click on goToFirstUnreadMessage button.
     */
    onGoToFirstUnreadMessage: () => void;
}

const useStyles = makeStyles()(theme => {
    return {
        container: {
            position: 'absolute',
            left: 'calc(50% - 72px)',
            bottom: '8px'
        },

        newMessagesButton: {
            display: 'flex',
            alignItems: 'center',
            padding: '9px 15px',
            border: '1px solid rgba(255, 255, 255, 0.05)',
            borderRadius: '40px',
            backgroundColor: 'rgba(55, 55, 55, 1)',
            boxShadow: '0px 2px 4px 0px rgba(0, 0, 0, 0.05)',

            '&:hover': {
                backgroundColor: 'rgba(55, 55, 55, 1)',
            },

            '&:active': {
                backgroundColor: 'rgba(55, 55, 55, 1)',
            }
        },

        textContainer: {
            fontFamily: 'Lato Regular !important',
            fontWeight: 'normal' as const,
            fontSize: '15px',
            lineHeight: '20px',
            color: 'rgba(255, 255, 255, 1)'
        }
    };
});

/** NewMessagesButton.
 *
 * @param {Function} onGoToFirstUnreadMessage - Function for lifting up onClick event.
 * @returns {JSX.Element}
 */
function NewMessagesButton({ onGoToFirstUnreadMessage, t }: INewMessagesButtonProps): JSX.Element {
    const { classes: styles } = useStyles();

    return (
        <div
            className = { styles.container }>
            <button
                aria-label = { t('chat.newMessages') }
                className = { styles.newMessagesButton }
                onClick = { onGoToFirstUnreadMessage }
                type = 'button'>
                <div className = { styles.textContainer }> { t('chat.newMessages') }</div>
            </button>
        </div>
    );
}

export default translate(NewMessagesButton);
