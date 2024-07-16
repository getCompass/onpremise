import React from 'react';
import {connect} from 'react-redux';

import {IReduxState} from '../../../app/types';
import {translate} from '../../../base/i18n/functions';

import AbstractUserMediaPermissionsOverlay, {abstractMapStateToProps}
    from './AbstractUserMediaPermissionsOverlay';
import CustomOverlayFrame from './CustomOverlayFrame';
import BackgroundDesktop from './BackgroundDesktop';
import BackgroundMobile from './BackgroundMobile';
import {isMobileBrowser} from '../../../base/environment/utils';


/**
 * Implements a React Component for overlay with guidance how to proceed with
 * gUM prompt.
 */
class UserMediaPermissionsOverlay extends AbstractUserMediaPermissionsOverlay {
    /**
     * Implements React's {@link Component#render()}.
     *
     * @inheritdoc
     * @returns {ReactElement}
     */
    render() {
        const {t} = this.props;
        const langStringDesktopRequestPermissionsPageDesc = t('startupoverlay.requestPermissions.desktop');
        const langStringMobileRequestPermissionsPageDesc = t('startupoverlay.requestPermissions.mobile');
        const isMobile = isMobileBrowser();

        const mobile: JSX.Element = (
            <CustomOverlayFrame style={{backgroundColor: 'f3f3f7'}}> <BackgroundMobile/>
                <div className={'background-mobile'}>
                    <div className={'user-media-permissions-mobile-overlay-body'}/>
                    <div className={'user-media-permissions-mobile-overlay-containerY'}>
                        <div className={'user-media-permissions-mobile-overlay-containerX'}>
                            <div className={'user-media-permissions-mobile-overlay-containerX-wrap'}>
                                <div className={'user-media-permissions-desktop-overlay-compassLogoIcon'}/>
                                <div className={'user-media-permissions-mobile-overlay-compassLogoText'}>Compass</div>
                            </div>
                        </div>
                        <div className={'user-media-permissions-mobile-overlay-requestMediaPermissionsBlock'}>
                            <div className={'user-media-permissions-mobile-overlay-requestMediaPermissionsText'}>
                                {langStringMobileRequestPermissionsPageDesc.split("\n").map((line, index) => (
                                    <div key={index}>{line}</div>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </CustomOverlayFrame>
        );

        const desktop = (
            <CustomOverlayFrame style={{backgroundColor: 'f3f3f7'}}> <BackgroundDesktop/>
                <div className={'user-media-permissions-desktop-overlay-body'} style={{}}/>
                <div className={'user-media-permissions-desktop-overlay-containerY'}>
                    <div className={'user-media-permissions-desktop-overlay-containerX'}>
                        <div style={{flexDirection: 'row', alignItems: 'center', display: 'flex', gap: '12px'}}>
                            <div className={'user-media-permissions-desktop-overlay-compassLogoIcon'}/>
                            <div className={'user-media-permissions-desktop-overlay-compassLogoText'}>Compass</div>
                        </div>
                    </div>
                    <div className={'user-media-permissions-desktop-overlay-requestMediaPermissionsBlock'}>
                        <div className={'user-media-permissions-desktop-overlay-requestMediaPermissionsText'}>
                            {langStringDesktopRequestPermissionsPageDesc.split("\n").map((line, index) => (
                                <div key={index}>{line}</div>
                            ))}
                        </div>
                    </div>
                </div>
            </CustomOverlayFrame>
        )

        return isMobile ? mobile : desktop;
    }
}

/**
 * Maps (parts of) the redux state to the React {@code Component} props.
 *
 * @param {Object} state - The redux state.
 * @param {Object} ownProps - The props passed to the component.
 * @returns {Object}
 */
function mapStateToProps(state: IReduxState) {
    const {premeetingBackground} = state['features/dynamic-branding'];

    return {
        ...abstractMapStateToProps(state),
        _premeetingBackground: premeetingBackground
    };
}

export default translate(connect(mapStateToProps)(UserMediaPermissionsOverlay));
