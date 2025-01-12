export type ElectronWindowType = {
    JitsiMeetElectron?: {
        obtainDesktopStreams: Function;
        stopObtainDesktopStreams: Function;
        isAudioScreenSharingSupported: Function;
    } ;
} & typeof window;
