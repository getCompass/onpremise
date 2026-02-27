/* eslint-disable react/jsx-no-bind */
import React, {useEffect} from 'react';
import { connect } from 'react-redux';
import { makeStyles } from 'tss-react/mui';

import PrejoinVideo from "./PrejoinVideo";
import PrejoinAlwaysShowCheckbox from "./PrejoinAlwaysShowCheckbox";
import PrejoinSettings from "./PrejoinSettings";
import PrejoinJoinButton from "./PrejoinJoinButton";

const useStyles = makeStyles()(theme => {
    return {
        container: {
            height: '100%',
            position: 'absolute',
            inset: '0 0 0 0',
            display: 'grid',
            backgroundColor: 'rgb(23, 23, 23)',
            zIndex: 252,
            gridTemplateAreas: `
                "video video video"
                "video video video"
                "checkbox settings button"
            `,
            gridTemplateColumns: '1fr 1fr 1fr',
            gridTemplateRows: '1fr 1fr 64px',
        },

        video: {},

        checkbox: {},

        settings: {},

        button: {},
    };
});

const Prejoin = () => {
    useEffect(() => {
        // оповестим electron что открыли страницу pre join
        window.parent.postMessage({ type: 'video_conference_pre_join_page', data: {
                isJoined: true,
            } }, '*');

        return () => {
            // оповестим electron что закрыли страницу pre join
            window.parent.postMessage({ type: 'video_conference_pre_join_page', data: {
                    isJoined: false,
                } }, '*');
        }
    }, []);

    const { classes } = useStyles();
    return (<div className={classes.container}>
        <PrejoinVideo />
        <PrejoinAlwaysShowCheckbox />
        <PrejoinSettings />
        <PrejoinJoinButton />
    </div>);
};


function mapStateToProps() {
    return {};
}

const mapDispatchToProps = {};

export default connect(mapStateToProps, mapDispatchToProps)(Prejoin);
