export type ElectronWindowType = {
    JitsiMeetElectron?: {
        obtainDesktopStreams: Function;
        stopObtainDesktopStreams: Function;
        isAudioScreenSharingSupported: Function;
        isScreenSharingSupported: Function;
    } ;
} & typeof window;

export type screenDimensions = { width: string, height: string }
