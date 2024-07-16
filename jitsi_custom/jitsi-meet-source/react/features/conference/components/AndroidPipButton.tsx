import React from 'react';
import {useSelector} from 'react-redux';
import {IReduxState} from "../../app/types";

const AndroidPipButton = () => {
    const {is_in_picture_in_picture_mode} = useSelector((state: IReduxState) => state['features/picture-in-picture']);

    return (<>
        {/* @ts-ignore */}
        {(typeof AndroidJitsiWebInterface !== 'undefined' && typeof AndroidJitsiWebInterface.showPictureInPictureMode === 'function' && !is_in_picture_in_picture_mode) && (
            <div className='conference-android-webview-pip-button' onClick={() => {
                // @ts-ignore
                AndroidJitsiWebInterface.showPictureInPictureMode()
                // не обновляем здесь состояние pip, android при переходе в pip режим сами обновят
            }}>
                <svg width="26" height="26" viewBox="0 0 26 26" fill="none"
                     xmlns="http://www.w3.org/2000/svg">
                    <g opacity="0.8">
                        <path fillRule="evenodd" clipRule="evenodd"
                              d="M5.41669 8.68825C5.09939 9.00556 5.09939 9.52 5.41669 9.8373L12.4253 16.8462C12.7426 17.1635 13.2571 17.1635 13.5744 16.8462L20.5833 9.83722C20.9006 9.51992 20.9006 9.00547 20.5833 8.68817C20.266 8.37087 19.7516 8.37087 19.4343 8.68817L12.9999 15.1227L6.56574 8.68825C6.24843 8.37095 5.73399 8.37095 5.41669 8.68825Z"
                              fill="white"/>
                    </g>
                </svg>
            </div>
        )}
    </>);
};

export default AndroidPipButton;
