import React, { Component } from 'react';
import { connect } from 'react-redux';

import { IReduxState } from '../../../app/types';
import { getUnreadPollCount } from '../../../polls/functions';
import { getUnreadCount } from '../../functions';
import { isMobileBrowser } from "../../../base/environment/utils";

/**
 * The type of the React {@code Component} props of {@link ChatCounterMobile}.
 */
interface IProps {

    /**
     * The value of to display as a count.
     */
    _count: number;

    /**
     * True if the chat window should be rendered.
     */
    _isOpen: boolean;

    customClass?: string;
}

/**
 * Implements a React {@link Component} which displays a count of the number of
 * unread chat messages.
 *
 * @augments Component
 */
class ChatCounterMobile extends Component<IProps> {

    /**
     * Implements React's {@link Component#render()}.
     *
     * @inheritdoc
     * @returns {ReactElement}
     */
    render() {
        const { _isOpen, _count, customClass } = this.props;

        if (_count === undefined || _count < 1) {
            return <></>;
        }

        return (
            <span
                className = {`badge-round-mobile${!_isOpen && _count > 0 ? ' not-empty' : ''} ${customClass ?? ''}${_count > 9 ? ' more-than-9' : ''}`}>

                {!_isOpen && (
                    <span>{_count > 9 ? '9+' : _count}</span>
                )}
            </span>
        );
    }
}

/**
 * Maps (parts of) the Redux state to the associated {@code ChatCounterMobile}'s
 * props.
 *
 * @param {Object} state - The Redux state.
 * @private
 * @returns {{
 *     _count: number
 * }}
 */
function _mapStateToProps(state: IReduxState) {
    const { isOpen } = state['features/chat'];

    return {
        _count: getUnreadCount(state) + getUnreadPollCount(state),
        _isOpen: isOpen
    };
}

export default connect(_mapStateToProps)(ChatCounterMobile);
