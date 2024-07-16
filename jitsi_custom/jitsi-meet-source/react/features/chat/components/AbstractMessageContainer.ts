import {Component} from 'react';

import {IMessage} from '../types';

export interface IProps {

    /**
     * The messages array to render.
     */
    messages: IMessage[];
}

/**
 * Abstract component to display a list of chat messages, grouped by sender.
 *
 * @augments PureComponent
 */
export default class AbstractMessageContainer<P extends IProps, S> extends Component<P, S> {
    static defaultProps = {
        messages: [] as IMessage[]
    };

    /**
     * Iterates over all the messages and creates nested arrays which hold
     * consecutive messages sent by the same participant.
     *
     * @private
     * @returns {Array<Array<Object>>}
     */
    _getMessagesGroupedBySender() {
        const messagesCount = this.props.messages.length;
        const groups: IMessage[][] = [];
        let currentGrouping: IMessage[] = [];
        let currentGroupParticipantId;
        let currentPrivateMessage;

        for (let i = 0; i < messagesCount; i++) {
            const message = this.props.messages[i];

            if (message.id === currentGroupParticipantId && message.privateMessage === currentPrivateMessage) {
                currentGrouping.push(message);
            } else {
                currentGrouping.length && groups.push(currentGrouping);

                currentGrouping = [message];
                currentGroupParticipantId = message.id;
                currentPrivateMessage = message.privateMessage;
            }
        }

        currentGrouping.length && groups.push(currentGrouping);

        return groups;
    }
}
