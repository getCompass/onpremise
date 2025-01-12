import React from "react";
import { connect } from "react-redux";

import { translate } from "../../../base/i18n/functions";

import AbstractPageReloadOverlay, { IProps, abstractMapStateToProps } from "./AbstractPageReloadOverlay";
import NoConnectionPopup from "./NoConnectionPopup/NoConnectionPopup";
import ReloadPopup from "./ReloadPopup";

class PageReloadOverlay extends AbstractPageReloadOverlay<IProps> {
    render() {
        const { isNetworkFailure } = this.props;

        if (!isNetworkFailure) {
            return <ReloadPopup />;
        }

        return <NoConnectionPopup getLocalization = {this.props.t} />;
    }
}

export default translate(connect(abstractMapStateToProps)(PageReloadOverlay));
