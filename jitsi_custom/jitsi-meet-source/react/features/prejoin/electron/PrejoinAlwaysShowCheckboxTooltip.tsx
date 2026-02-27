/* eslint-disable react/jsx-no-bind */
import React, {useState} from 'react';
import {WithTranslation} from 'react-i18next';
import { connect } from 'react-redux';
import { makeStyles } from 'tss-react/mui';


import Popover from "../../base/popover/components/Popover.web";
import {IconQuestionCircle} from "../../base/icons/svg";
import Icon from "../../base/icons/components/Icon";
import {translate} from "../../base/i18n/functions";

interface IProps extends WithTranslation {}

const useStyles = makeStyles()(theme => {
    return {
        container: {
            '& > div': {
                display: 'inline',
            }

        },
        text: {
          lineHeight: '19px',
        },
        iconWrapper: {
            marginLeft: '3px',
            marginBottom: '2px',
            display: 'inline',
            verticalAlign: 'bottom',
        },
        icon: {
            display: 'inline',
            opacity: '30%'
        },
        popoverContent: {
            position: 'relative',
            padding: '5px 8px',
            fontSize: '13px',
            lineHeight: '18px',
            backgroundColor: '#272727',
            maxWidth: '255px',
            borderRadius: '5px',
            marginBottom: '9px',
            cursor: 'pointer',
            textAlign: 'center',

            '&:after': {
                content: '""',
                display: 'block',
                position: 'absolute',
                bottom: '-5px',
                left: '49%',
                width: 0,
                height: 0,
                borderRight: '5px solid transparent',
                borderLeft: '5px solid transparent',
                borderTop: '5px solid #272727'
            }
        },
    };
});

const unsupportedText = (props: WithTranslation) => {
    const {classes: styles, cx, theme} = useStyles();
    const { t } = props;
    return (<div className={cx(styles.popoverContent)}>
        <div dangerouslySetInnerHTML={{__html: t('lobby.previewWindow.tooltip')}}/>
    </div>)
}

const PrejoinAlwaysShowCheckboxTooltip = (props: IProps) => {
    const { classes, theme } = useStyles();
    const [isOpen, changeOpenState] = useState(false);

    return(<div className = {classes.container}>
        <span className = {classes.text}>
            {props.t('lobby.previewWindow.check_option')}
        </span>
        <Popover
            content = {unsupportedText(props)}
            onPopoverClose = {() => changeOpenState(false)}
            position = 'top'
            trigger = 'click'
            visible = {isOpen}>
            <div
                className = {classes.iconWrapper}
                onMouseLeave = {() => changeOpenState(false)}
                onClick = {() => changeOpenState(true)}
                onMouseEnter = {() => changeOpenState(true)}>
                <Icon
                    className = {classes.icon}
                    color = {'#FFF'}
                    size = {20}
                    src = {IconQuestionCircle} />
            </div>
        </Popover>
    </div>);
};


function mapStateToProps() {
    return {};
}

const mapDispatchToProps = {};

export default translate(connect(mapStateToProps, mapDispatchToProps)(PrejoinAlwaysShowCheckboxTooltip));


