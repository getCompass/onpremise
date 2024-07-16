let audioCtx;
let audioDest;
let recorder;
let app_title;

if (navigator.mediaDevices.getDisplayMedia) {
    window.addEventListener("message", baseHandler);
    window.parent.postMessage({ type: "recorder_ready" }, "*");
}

function baseHandler(event) {
    if (event && event.data) {
        switch (event.data.type) {
            case "set_app_title": {
                app_title = event.data.app_title;
                break;
            }
            case "recorder_start":
                if (window.JitsiMeetElectron && JitsiMeetScreenObtainer && JitsiMeetScreenObtainer.openDesktopPicker) {
                    closeDesktopPicker();
                    let observer = new MutationObserver(() => {
                        let el = document.querySelector("label:not([style]) > input[name=share-system-audio]");
                        if (el) {
                            el.closest("label").style.display = "none";
                        }
                    });
                    let bodyEl = document.querySelector("body.desktop-browser");
                    if (bodyEl) {
                        observer.observe(bodyEl, {
                            childList: true,
                            subtree: true,
                        });
                    }
                    try {
                        obtainDesktopSources({
                            types: ["window"],
                            thumbnailSize: {
                                height: 300,
                                width: 300,
                            },
                        }).then((sources) => {
                            const streamId = sources.filter((source) => source.name === app_title)[0]?.id;

                            if (!streamId) {
                                startDefaultRecording(sources, event.data.data.external_save)
                                return;
                            }

                            startRecording(
                                navigator.mediaDevices.getUserMedia({
                                    audio: false,
                                    video: {
                                        mandatory: {
                                            chromeMediaSource: "desktop",
                                            chromeMediaSourceId: streamId,
                                        },
                                    },
                                }),
                                event.data.data && event.data.data.external_save
                            );
                        });
                    } catch (error) {
                        startDefaultRecording(sources, event.data.data.external_save)
                    }
                } else {
                    startRecording(
                        navigator.mediaDevices.getDisplayMedia({
                            audio: false,
                            video: true,
                        }),
                        event.data.data && event.data.data.external_save
                    );
                }
                break;
            case "recorder_stop":
                JitsiMeetScreenObtainer.openConfrimFinished(() => {
                    stopRecording();
                });

                break;
        }
    }
}

function clrCtx() {
    recorder = null;
    audioCtx = null;
    audioDest = null;
    if (APP.conference._room) {
        APP.conference._room.off(JitsiMeetJS.events.conference.TRACK_ADDED, trackAddedHandler);
    }
}

async function obtainDesktopSources(options) {
    const { JitsiMeetElectron } = window;
    if (JitsiMeetElectron?.obtainDesktopStreams) {
        return new Promise((resolve, reject) => {
            JitsiMeetElectron.obtainDesktopStreams(
                (sources) => resolve(sources),
                (error) => {
                    reject(error);
                },
                options
            );
        });
    }
}

function errorHandler(e) {
    console.error(e);
    window.parent.postMessage({ type: "recorder_error" }, "*");
}

function trackAddedHandler(track) {
    if (audioCtx && audioDest && track.getType() === "audio") {
        audioCtx.createMediaStreamSource(track.stream).connect(audioDest);
    }
}

async function startDefaultRecording(sources, external_save) {
    const filteredSourceNames = ["StatusIndicator"];
    sources = sources.filter((source) => !filteredSourceNames.includes(source.name));

    startRecording(
        navigator.mediaDevices.getUserMedia({
            audio: false,
            video: {
                mandatory: {
                    chromeMediaSource: "desktop",
                    chromeMediaSourceId: sources[0].id,
                },
            },
        }),
        external_save
    );
}

async function startRecording(videoStreamPromise, isExternalSave) {
    try {
        const recordingData = [];
        audioCtx = new AudioContext();
        audioDest = audioCtx.createMediaStreamDestination();

        const videoTrack = (await videoStreamPromise).getVideoTracks()[0];
        videoTrack.addEventListener("ended", () => {
            window.parent.postMessage({ type: "recorder_stop" }, "*");
            stopRecording();
        });
        audioDest.stream.addTrack(videoTrack);

        APP.conference._room.on(JitsiMeetJS.events.conference.TRACK_ADDED, trackAddedHandler);
        audioCtx.createMediaElementSource(new Audio(createSilentAudio(1))).connect(audioDest);
        let localAudioTrack = APP.conference._room.getLocalAudioTrack();
        if (localAudioTrack && localAudioTrack.stream) {
            audioCtx.createMediaStreamSource(localAudioTrack.stream).connect(audioDest);
        }
        for (let participant of APP.conference._room.getParticipants()) {
            for (let track of participant.getTracksByMediaType("audio")) {
                audioCtx.createMediaStreamSource(track.stream).connect(audioDest);
            }
        }

        recorder = new MediaRecorder(audioDest.stream);
        recorder.onerror = (e) => {
            throw e;
        };
        if (isExternalSave) {
            recorder.ondataavailable = (e) => {
                if (e.data && e.data.size > 0) {
                    window.parent.postMessage({ type: "recorder_data", data: e.data }, "*");
                }
            };
        } else {
            recorder.ondataavailable = (e) => {
                if (e.data && e.data.size > 0) {
                    recordingData.push(e.data);
                }
            };
        }
        recorder.onstop = () => {
            videoTrack.stop();
            if (!isExternalSave && recordingData.length) {
                const a = document.createElement("a");
                a.href = window.URL.createObjectURL(new Blob(recordingData, { type: recordingData[0].type }));
                a.download = APP.conference._room.getMeetingUniqueId();
                a.click();
            }
        };
        recorder.start(1000);
        window.parent.postMessage({ type: "recorder_start" }, "*");

        APP.store.dispatch({
            type: "SET_SCREENSHOT_CAPTURE",
            payload: true,
        });

        APP.store.dispatch({
            type: "START_ELECTRON_RECORDING",
        });
    } catch (e) {
        errorHandler(e);
        clrCtx();
    }
}

function stopRecording() {
    try {
        if (recorder) {
            recorder.stop();
        } else {
            closeDesktopPicker();
        }
    } catch (e) {
        errorHandler(e);
    }
    clrCtx();
    APP.store.dispatch({
        type: "SET_SCREENSHOT_CAPTURE",
        payload: false,
    });

    APP.store.dispatch({
        type: "STOP_ELECTRON_RECORDING",
    });
    window.parent.postMessage({ type: "recorder_finished" }, "*");
}

function closeDesktopPicker() {
    if (window.JitsiMeetElectron) {
        let desktopPickerCancelBtn = document.getElementById("modal-dialog-cancel-button");
        if (desktopPickerCancelBtn) {
            desktopPickerCancelBtn.click();
        }
    }
}

function createSilentAudio(time, freq = 44100) {
    const audioFile = new AudioContext().createBuffer(1, time * freq, freq);
    let numOfChan = audioFile.numberOfChannels,
        len = time * freq * numOfChan * 2 + 44,
        buffer = new ArrayBuffer(len),
        view = new DataView(buffer),
        channels = [],
        i,
        sample,
        offset = 0,
        pos = 0;

    setUint32(0x46464952);
    setUint32(len - 8);
    setUint32(0x45564157);

    setUint32(0x20746d66);
    setUint32(16);
    setUint16(1);
    setUint16(numOfChan);
    setUint32(audioFile.sampleRate);
    setUint32(audioFile.sampleRate * 2 * numOfChan);
    setUint16(numOfChan * 2);
    setUint16(16);

    setUint32(0x61746164);
    setUint32(len - pos - 4);

    for (i = 0; i < audioFile.numberOfChannels; i++) {
        channels.push(audioFile.getChannelData(i));
    }

    while (pos < len) {
        for (i = 0; i < numOfChan; i++) {
            sample = Math.max(-1, Math.min(1, channels[i][offset]));
            sample = (0.5 + sample < 0 ? sample * 32768 : sample * 32767) | 0;
            view.setInt16(pos, sample, true);
            pos += 2;
        }
        offset++;
    }
    return URL.createObjectURL(new Blob([buffer], { type: "audio/wav" }));

    function setUint16(data) {
        view.setUint16(pos, data, true);
        pos += 2;
    }

    function setUint32(data) {
        view.setUint32(pos, data, true);
        pos += 4;
    }
}
