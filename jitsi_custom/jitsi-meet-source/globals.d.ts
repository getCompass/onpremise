import { IStore } from "./react/features/app/types";
import { IConfig } from "./react/features/base/config/configType";

export {};

declare global {
    const __RELEASE__: string
    const __IS_PRODUCTION__: boolean
    const __SENTRY_DSN__: string;
    const __SENTRY_ENVIRONMENT__: string

    const APP: {
        store: IStore;
        UI: any;
        API: any;
        conference: any;
        debugLogs: any;
    };
    const interfaceConfig: any;

    interface Window {
        config: IConfig;
        JITSI_MEET_LITE_SDK?: boolean;
        interfaceConfig?: any;
        JitsiMeetJS?: any;
        JitsiMeetElectron?: any;
        PressureObserver?: any;
        PressureRecord?: any;
        ReactNativeWebView?: any;
        // selenium tests handler
        _sharedVideoPlayer: any;
        alwaysOnTop: { api: any };
    }

    interface Document {
        mozCancelFullScreen?: Function;
        webkitExitFullscreen?: Function;
    }

    const config: IConfig;

    const JitsiMeetJS: any;

    interface HTMLMediaElement {
        setSinkId: (id: string) => Promise<undefined>;
        stop: () => void;
    }
}
