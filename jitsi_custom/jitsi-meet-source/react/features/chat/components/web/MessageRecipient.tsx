import React, {useCallback} from 'react';
import {useTranslation} from 'react-i18next';
import {connect} from 'react-redux';
import {makeStyles} from 'tss-react/mui';

import {IconCloseLargeNoColor} from '../../../base/icons/svg';
import Button from '../../../base/ui/components/web/Button';
import {BUTTON_TYPES} from '../../../base/ui/constants.any';
import {_mapDispatchToProps, _mapStateToProps, IProps} from '../AbstractMessageRecipient';
import Icon from "../../../base/icons/components/Icon";
import {isMobileBrowser} from "../../../base/environment/utils";

const useStyles = makeStyles()(theme => {
    return {
        container: {
            marginBottom: '16px',
            padding: '6px 19px 6px 16px',
            display: 'flex',
            justifyContent: 'space-between',
            alignItems: 'center',
            backgroundColor: 'rgba(0, 122, 255, 0.17)',
            fontFamily: 'Lato Regular',
            fontWeight: 'normal' as const,
            fontSize: '13px',
            lineHeight: '19px',
            color: 'rgba(0, 107, 224, 1)',

            '&.is-mobile': {
                marginBottom: 0,
                padding: '4px 8px',
                fontSize: '14px',
                lineHeight: '17px',
            }
        },

        text: {
            maxWidth: 'calc(100% - 30px)',
            overflow: 'hidden',
            whiteSpace: 'break-spaces',
            wordBreak: 'break-all'
        },

        iconButton: {
            padding: 0,
            backgroundColor: 'transparent',

            '&.is-mobile': {
                padding: 0
            },

            '&:hover': {
                backgroundColor: 'transparent',
            },

            '&:not(:disabled)': {
                '&:hover': {
                    backgroundColor: 'transparent !important'
                },

                '&:active': {
                    backgroundColor: 'transparent !important'
                }
            },

            '& svg': {
                fill: 'rgba(0, 107, 224, 1) !important'
            }
        }
    };
});

const MessageRecipient = ({
    _privateMessageRecipient,
    _isLobbyChatActive,
    _lobbyMessageRecipient,
    _onRemovePrivateMessageRecipient,
    _onHideLobbyChatRecipient,
    _visible
}: IProps) => {
    const { classes, cx } = useStyles();
    const { t } = useTranslation();
    const isMobile = isMobileBrowser();

    const _onKeyPress = useCallback((e: React.KeyboardEvent) => {
        if (
            (_onRemovePrivateMessageRecipient || _onHideLobbyChatRecipient)
            && (e.key === ' ' || e.key === 'Enter')
        ) {
            e.preventDefault();
            if (_isLobbyChatActive && _onHideLobbyChatRecipient) {
                _onHideLobbyChatRecipient();
            } else if (_onRemovePrivateMessageRecipient) {
                _onRemovePrivateMessageRecipient();
            }
        }
    }, [ _onRemovePrivateMessageRecipient, _onHideLobbyChatRecipient, _isLobbyChatActive ]);

    if ((!_privateMessageRecipient && !_isLobbyChatActive) || !_visible) {
        return null;
    }

    return (
        <div
            className = { cx(classes.container, isMobile && 'is-mobile') }
            id = 'chat-recipient'
            role = 'alert'>
            <span className = { classes.text }>
                {t(_isLobbyChatActive ? 'chat.lobbyChatMessageTo' : 'chat.messageTo', {
                    recipient: _isLobbyChatActive ? _lobbyMessageRecipient : _privateMessageRecipient
                })}
            </span>
            <Button
                accessibilityLabel = { t('dialog.close') }
                className = { classes.iconButton }
                customIcon={<Icon
                    size={24}
                    src={IconCloseLargeNoColor}
                    color={'rgba(0, 107, 224, 1)'}/>}
                onClick = { _isLobbyChatActive
                    ? _onHideLobbyChatRecipient : _onRemovePrivateMessageRecipient }
                onKeyPress = { _onKeyPress }
                type = { BUTTON_TYPES.TERTIARY } />
        </div>
    );
};

export default connect(_mapStateToProps, _mapDispatchToProps)(MessageRecipient);
