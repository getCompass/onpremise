# Local Jitsi Meet Screen Recorder

## Description

Quick hack to record your self hosted [Jitsi Meet](https://github.com/jitsi/jitsi-meet) session locally, just using your browser without Jibri. Uses `getDisplayMedia` just to capture user selected screen and local Jitsi audio streams from each participants.
Captures the audio stream of the current user from the selected microphone in the Jitsi interface. Thus, if the microphone is muted, then the user's sound will be muted.
When new participants are connected to conference, the recorder also captures theirs audio streams.

## Installation

Installation assumes Jitsi Meet's web files are located in `/usr/share/jitsi-meet/index.html`.
Simply need to insert [recorder.js](https://github.com/TALRACE/LocalScreenRecorder/blob/main/recorder.js) in the head section:

```
<head>
    ...
    <script src="libs/app.bundle.min.js?v=5056"></script>
    <script src="libs/test3?v=5056"></script>
    <script src="static/recorder.js"></script>
    ...
<head>
```

## Integration

Last checked version: [docker-jitsi-meet stable-7001](https://github.com/jitsi/docker-jitsi-meet/releases/tag/stable-7001)

The recorder implementation does not provide an additional interface. In any case, you can add the interface yourself. It is recommended to use the [Jitsi Iframe API](https://jitsi.github.io/handbook/docs/dev-guide/dev-guide-iframe).
Communication with the recorder takes place using Cross-window communication `jitsiIframeApi.getIFrame().contentWindow.postMessage(command, '*');`.
Before initializing `Jitsi Iframe API` it is required to subscribe to window event `message` for listening commands from the recorder.

Commands for the recorder:
* `{type: 'recorder_start', data: {external_save: boolean}}` starts recording and prompts the user to select a screen to record; `external_save` allows to save recording data yourself and passes chunks to the parent window every second
* `{type: 'recorder_stop'}` stops recording and prompts the user to save the file or saves the file without notification depending on the browser settings

Commands from the recorder:
* `{type: 'recorder_ready'}` the recorder is initialized and ready to receive commands to record the screen
* `{type: 'recorder_start'}` notification that recording was started. Obviously that after user will choose video stream
* `{type: 'recorder_stop'}` recording stopped unexpectedly and prompted to save the file. This can happen if the user has stopped capture of the screen through the browser interface
* `{type: 'recorder_error'}` errors have occurred on the recorder side. it is recommended to perform the same actions in your interface as when stopping recording. The recorder will display an error in the console
* `{type: 'recorder_data', data: Blob}` chunks of data received if the parameter `external_save` is enabled

It is recommended to start screen recording only after all the conditions are met:
* recorder announced that it is ready to receive commands through the `recorder_ready` command
* `videoConferenceJoined` event occurred in Jitsi Iframe API

## Browser compatibility

Latest versions of all popular desktop browsers

## Electron support

Yes. The solution uses the `JitsiMeetScreenObtainer.openDesktopPicker` for the electron.