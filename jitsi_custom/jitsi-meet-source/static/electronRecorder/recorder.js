// eslint-disable-next-line max-len
/* eslint-disable require-jsdoc,camelcase,no-undef,no-param-reassign,prefer-const,sort-vars,no-bitwise,no-mixed-operators,no-throw-literal */
let audioCtx;
let audioDest;
let recorder;
let app_title;
let notificationUid;
let localTrack;
let previousLocalTrackId;
let replaceLocalTrackTimeoutId;
let notificationPermissionUid;
let drawId;
let intervalId;

// окно ВКС свернуто?
let isWindowMinimized = false;
const canvas = document.createElement('canvas');
const ctx = canvas.getContext('2d');
const convertedImages = new Map();
const canvasSize = {
    width: 1920,
    height: 1080
};
const FPS = 30;

// соотношение сторон каждого блока в режиме спикера
// чтобы каждый раз не брать из стилей
// так как операция дорогая будет на каждый рендер
const itemAspectRatio = 618 / 347.63;

setCanvasSize(canvasSize.width, canvasSize.height);

if (navigator.mediaDevices.getDisplayMedia) {
    window.addEventListener('message', baseHandler);
    window.parent.postMessage({ type: 'recorder_ready' }, '*');
}

function baseHandler(event) {
    if (event && event.data) {
        switch (event.data.type) {
        case 'set_app_title': {
            app_title = event.data.app_title;
            break;
        }
        case 'recorder_start':
            startRecording();
            break;
        case 'recorder_stop':
            stopRecording();
            break;
        case 'show_notification_minimize_when_recording':
            showWarningNotificationWhenMinimizeApp();
            break;
        case 'converted_src_url_to_data_url':
            saveConvertedImages(event.data);
            break;
        case 'get_window_state':
            isWindowMinimized = event.data.isMinimized;
            break;
        }
    }
}

// Получаем локальный наш трек если он есть и добавляем в запись
function addLocalTrackToRecording() {
    // Предыдущий уберем из записи чтобы не дублировался
    localTrack?.disconnect();

    // Получаем наш локальный трек
    const localAudioTrack = APP.conference._room.getLocalAudioTrack();

    if (localAudioTrack && localAudioTrack.stream) {
        // запишем его ID чтобы потом можно было заменить при старте шаринга
        previousLocalTrackId = localAudioTrack.stream.id;

        // Сохраняем его чтобы потом могли его удалить
        localTrack = audioCtx.createMediaStreamSource(localAudioTrack.stream);

        // Добавляем аудиодорожку нашу локальную в поток записи
        localTrack.connect(audioDest);
    }

}

// Проверяет совпадает ли текущий локальный аудио трек - с тем что мы ранее добавляли в запись
// После начала/окончания стрима он меняется не сразу, поэтому делаем через таймаут
function replaceLocalAudioTrack(retries = 0) {
    if (retries > 5) {
        return;
    }
    window.clearTimeout(replaceLocalTrackTimeoutId);

    const retry = () => {
        retries++;
        replaceLocalTrackTimeoutId = setTimeout(() => {
            replaceLocalAudioTrack(retries);
        }, 2000);
    };

    // Получим нашу локальную аудиодорожку
    const newLocalTrack = APP.conference._room.getLocalAudioTrack();

    if (!newLocalTrack || !newLocalTrack.stream) {
        retry();

        return;
    }

    // Получим ID стрима - поможет сравнить со старым
    const newLocalTrackId = newLocalTrack.stream.id;

    // Если айдишники не совпадают - значит у нас сейчас в записи конфы - неправильный звук
    if (newLocalTrackId !== previousLocalTrackId) {
        addLocalTrackToRecording();

        return;
    }

    // Если айдишники совпадают - значит еще не сменился источник, попробуем чуть позже
    retry();
}

function clrCtx() {
    stopDrawFrame();
    recorder = null;
    audioCtx = null;
    audioDest = null;
    localTrack = null;
    previousLocalTrackId = null;
    window.clearTimeout(replaceLocalTrackTimeoutId);
    replaceLocalTrackTimeoutId = null;
    if (APP.conference._room) {
        APP.conference._room.off(JitsiMeetJS.events.conference.TRACK_ADDED, trackAddedHandler);
        APP.conference._room.off(JitsiMeetJS.events.conference.TRACK_MUTE_CHANGED, trackMutedChanged);
    }
}

function errorHandler(e) {
    console.error(e);
    window.parent.postMessage({ type: 'recorder_error' }, '*');
}

function trackAddedHandler(track) {
    if (!audioCtx || !audioDest) {
        return;
    }

    // Если начался стрим экрана - он может быть со звуком, заново подгрузим локальны звук
    if (track.videoType === 'desktop') {
        replaceLocalAudioTrack();

        return;
    }
    if (track.getType() === 'audio') {
        const audioSource = audioCtx.createMediaStreamSource(track.stream);

        // Если это локальный трек - то запишем его на будущее, вдруг придется удалить
        const localAudioTrack = APP.conference._room.getLocalAudioTrack();

        if (localAudioTrack?.stream.id === track.stream.id) {
            localTrack = audioSource;
        }

        audioSource.connect(audioDest);
    }
}

function trackMutedChanged(track) {
    // Если закончили стрим экрана - он может быть со звуком, заново подгрузим локальный звук
    if (track.videoType === 'desktop') {
        replaceLocalAudioTrack();
    }
}

async function startRecording(isExternalSave = true) {
    try {
        const recordingData = [];

        audioCtx = new AudioContext();
        audioDest = audioCtx.createMediaStreamDestination();

        drawOnCanvas();

        const stream = canvas.captureStream(FPS);
        const tracks = stream.getVideoTracks();
        const track = tracks[0];

        audioDest.stream.addTrack(track);


        // Слушатель что когда трек появляется с аудио  - его аудио добавляется
        // Например другой человек вступил в конфу или мы микро подключили
        APP.conference._room.on(JitsiMeetJS.events.conference.TRACK_ADDED, trackAddedHandler);

        // Когда свой скриншаринг закрываем - трек просто мьютится
        APP.conference._room.on(JitsiMeetJS.events.conference.TRACK_MUTE_CHANGED, trackMutedChanged);


        // добавляем пустую аудиодорожку (не знаю зачем, видать надо для медиарекордера)
        audioCtx.createMediaElementSource(new Audio(createSilentAudio(1))).connect(audioDest);

        // Добавляем нашу аудиодорожку если есть
        addLocalTrackToRecording();

        // Берем аудиодорожки всех участников и добавляем
        for (const participant of APP.conference._room.getParticipants()) {
            for (const trackParticipant of participant.getTracksByMediaType('audio')) {
                audioCtx.createMediaStreamSource(trackParticipant.stream).connect(audioDest);
            }
        }

        recorder = new MediaRecorder(audioDest.stream);
        recorder.onerror = e => {
            throw e;
        };
        if (isExternalSave) {
            recorder.ondataavailable = e => {
                if (e.data && e.data.size > 0) {
                    window.parent.postMessage({ type: 'recorder_data',
                        data: e.data }, '*');
                }
            };
        } else {
            recorder.ondataavailable = e => {
                if (e.data && e.data.size > 0) {
                    recordingData.push(e.data);
                }
            };
        }
        recorder.onstop = () => {
            stopDrawFrame();
            if (!isExternalSave && recordingData.length) {
                const a = document.createElement('a');

                a.href = window.URL.createObjectURL(new Blob(recordingData, { type: recordingData[0].type }));
                a.download = APP.conference._room.getMeetingUniqueId();
                a.click();
            }
        };
        recorder.start(1000);
        window.parent.postMessage({ type: 'recorder_start',
            data: {
                // eslint-disable-next-line camelcase
                app_title,
                // eslint-disable-next-line camelcase
                is_disable_minimize_window: true
            } }, '*');

        APP.store.dispatch({
            type: 'SET_SCREENSHOT_CAPTURE',
            payload: true
        });

        APP.store.dispatch({
            type: 'START_ELECTRON_RECORDING'
        });
    } catch (e) {
        showPermissionNotificationWhenMinimizeApp();
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
        type: 'SET_SCREENSHOT_CAPTURE',
        payload: false
    });

    APP.store.dispatch({
        type: 'STOP_ELECTRON_RECORDING'
    });
    window.parent.postMessage({ type: 'recorder_finished' }, '*');
}

function closeDesktopPicker() {
    if (window.JitsiMeetElectron) {
        const desktopPickerCancelBtn = document.getElementById('modal-dialog-cancel-button');

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

    return URL.createObjectURL(new Blob([ buffer ], { type: 'audio/wav' }));

    function setUint16(data) {
        view.setUint16(pos, data, true);
        pos += 2;
    }

    function setUint32(data) {
        view.setUint32(pos, data, true);
        pos += 4;
    }
}

function showWarningNotificationWhenMinimizeApp() {
    if (notificationUid) {
        const store = APP.store.getState()['features/notifications'];
        const isShown = store?.notifications?.some(item => item.uid === notificationUid);

        if (isShown) {
            APP.store.dispatch({
                type: 'RESET_NOTIFICATION',
                uid: notificationUid,
                timeout: 10000
            });

            return;
        }
    }
    notificationUid = Date.now().toString();
    APP.store.dispatch({
        type: 'SHOW_NOTIFICATION',
        uid: notificationUid,
        timeout: 10000,
        props: {
            descriptionKey: 'minimizeWindowAlert.text',
            titleKey: 'minimizeWindowAlert.title',
            icon: 'recording',
            customActionType: [ 'info', 'destructive' ],
            customActionNameKey: [ 'dialog.Cancel', 'minimizeWindowAlert.minimizeButton' ],
            customActionHandler: [ () => {
                hideNotifications();
            }, () => {
                window.parent.postMessage({ type: 'force_minimize_window' }, '*');
                hideNotifications();
            } ]
        }
    });
}

function showPermissionNotificationWhenMinimizeApp() {
    if (notificationPermissionUid) {
        const store = APP.store.getState()['features/notifications'];
        const isShown = store?.notifications?.some(item => item.uid === notificationPermissionUid);

        if (isShown) {
            APP.store.dispatch({
                type: 'RESET_NOTIFICATION',
                uid: notificationPermissionUid,
                timeout: 5000
            });

            return;
        }
    }
    notificationPermissionUid = Date.now().toString();
    APP.store.dispatch({
        type: 'SHOW_NOTIFICATION',
        uid: notificationPermissionUid,
        timeout: 5000,
        props: {
            descriptionKey: 'dialog.screenRecordingPermissionErrorDescription',
            titleKey: 'dialog.screenRecordingPermissionErrorTitle',
            icon: 'warning'
        }
    });
}

function hideNotifications() {
    notificationUid && APP.store.dispatch({
        type: 'HIDE_NOTIFICATION',
        uid: notificationUid
    });
}

/**
 * Начать отрисовку на канвасе
 * исходя из того что сейчас окно свернуто или нет
 */
function drawOnCanvas() {
    stopDrawFrame();

    // если окно свернуто
    // requestAnimationFrame не вызывается
    // поэтому рендерим через interval
    if (isWindowMinimized) {
        drawWithInterval();
    } else {
        drawWithAnimationFrame();
    }
}

/**
 * Отрисовка через setInterval
 */
function drawWithInterval() {
    drawFrame(false);

    intervalId = window.setInterval(() => drawFrame(false), 1000 / FPS);
}

/**
 * Отрисовка через requestAnimationFrame
 */
function drawWithAnimationFrame() {
    drawFrame(true);
    drawId = requestAnimationFrame(drawWithAnimationFrame);
}

/**
 * Отрисовка одного кадра
 * @param isAnimationFrame - сейчас идет отрисовка через requestAnimationFrame ?
 */
function drawFrame(isAnimationFrame) {
    try {
        const canvasW = canvasSize.width;
        const canvasH = canvasSize.height;
        const isInScreenShare = isScreenShare();
        const isInSpeakerMode = isSpeakerMode();
        const isOneParticipant = getParticipantCount() <= 1 && !isInScreenShare;

        ctx.fillStyle = '#171717';
        ctx.fillRect(0, 0, canvasW, canvasH);

        // Режим спикера
        // рисуем одного участника на весь канвас
        if (isInSpeakerMode) {
            drawSpeakerMode();

            // если только 1 участник
            // его тоже отрисуем на весь канвас
        } else if (isOneParticipant) {
            drawOnlyOneParticipant();
        } else {

            // рисуем в режиме плитки
            drawParticipants();
        }
    } catch (e) {
        console.error('[ERROR DRAW FRAME] ', e);
    }

    const isMinimizedAndUsingAnimationRender = isAnimationFrame && isWindowMinimized;
    const isRestoredAndUsingIntervalRender = !isAnimationFrame && !isWindowMinimized;
    const shouldChangeRenderType = isMinimizedAndUsingAnimationRender || isRestoredAndUsingIntervalRender;

    // если у нас окно свернуто то нужно перейти на рендер по интервалу
    // так как если окно свернуто
    // requestAnimationFrame не вызывается
    // если же окно развернуто то уже нужно перейти на requestAnimationFrame так как
    // requestAnimationFrame работает намного оптимальнее
    // за счет того что вызывается именно тогка когда нужнен рендер
    if (shouldChangeRenderType) {
        drawOnCanvas();
    }
}

/**
 * Отрисуем в режиме спикера
 * В режиме спикера нужно отрисовать одного участника
 * на ВЕСЬ канвас
 */
function drawSpeakerMode() {
    const canvasW = canvasSize.width;
    const canvasH = canvasSize.height;
    const videoTag = getLargeVideo();
    const isVideo = videoTag && document.getElementById('largeVideoCompassContainer')
        ?.style.visibility === 'visible';
    const imageTag = getLargeVideoImage();
    const avatarTag = getLargeVideoAvatar();

    const rect = {
        x: 0,
        y: 0,
        width: canvasW,
        height: canvasH
    };

    if (isVideo && videoTag) {
        if (videoTag.readyState >= 1) {
            drawFullScreenVideo(videoTag);
        } else {
            videoTag.addEventListener('loadedmetadata', () => {
                drawFullScreenVideo(videoTag);
            }, { once: true });
        }

        return;
    }

    if (imageTag) {
        return drawOneParticipant({
            rectSize: rect,
            imageRef: imageTag,
            isVideo: false
        });
    }

    drawOneParticipant({
        rectSize: rect,
        userAvatarRef: avatarTag,
        isVideo: false
    });
}

/**
 * Отрисуем ОДНОГО участника
 * кейса когда включен режим плитки
 * но в конференции всего один участник
 */
function drawOnlyOneParticipant() {
    const videoRef = getSmallVideos()?.[0];

    drawOneParticipant({
        item: videoRef,
        rectSize: {
            x: 0,
            y: 0,
            width: canvasSize.width,
            height: canvasSize.height
        },
        isOneParticipant: true
    });
}

/**
 * Отрисуем всех участников когда включен режим плитки
 */
function drawParticipants() {
    const videos = getSmallVideos() ?? [];

    const positions = layoutParticipantsOnCanvas({
        itemsList: videos
    });

    videos.forEach((item, index) => {
        drawOneParticipant({
            item,
            rectSize: positions[index]
        });
    });
}

/**
 * Распределить участников на канвасе так,
 * чтобы не осталось свободного места на канвасе,
 * но и чтобы блок не был растянуть
 * @param itemsList - кол-во участников
 * @return {DOMRect[]}
 */
function layoutParticipantsOnCanvas({ itemsList }) {
    const itemCount = itemsList.length;
    const spaceBetween = 2;

    /**
     * Лучший конфиг для распределения блоков,
     * то есть как нужно отрисовать блоки чтобы они разместились на весь канвас
     * @type {{
     * cols: number,
     * rows: number,
     * itemWidth: number,
     * itemHeight: number,
     * totalGridWidth: number,
     * totalGridHeight: number,
     * unusedArea: number,
     * }}
     */
    let bestConfig;

    // пройдемся по списку и найдем лучший вариант для распределения
    // чтобы на канвасе не осталось свободного места
    for (let cols = 1; cols <= itemCount; cols++) {
        // кол-во строк
        const rows = Math.ceil(itemCount / cols);

        // текущее доступное пространство
        const availableWidth = canvasSize.width;
        const availableHeight = canvasSize.height;

        // какая ширина/высота блока должна быть чтобы
        // было распределено по канвасу
        let itemWidth = availableWidth / cols;
        let itemHeight = itemWidth / itemAspectRatio;

        // если у каждого блока будет такая высота мы выйдем за рамки доступной высоты канваса ?
        if (itemHeight * rows > availableHeight) {
            // если выходим за рамки доступной высоты канваса,
            // то считаем по доступной высоте
            itemHeight = availableHeight / rows;
            itemWidth = itemHeight * itemAspectRatio;
        }

        // предполагая что у каждого блока будет ширина/высота itemWidth/itemHeight
        // считаем сколько всего ширины/высоты возьмем из канваса
        const totalGridWidth = itemWidth * cols;
        const totalGridHeight = itemHeight * rows;

        // считаем сколько НЕ распределенного участка осталось
        const unusedArea = (availableWidth * availableHeight) - (totalGridWidth * totalGridHeight);

        // если первая инициализация или же
        // меньше НЕ использованного участка из канваса осталось чем за предыдущий расчет
        // значит этот расчет ЛУЧШЕ заменим его
        if (!bestConfig || unusedArea < bestConfig.unusedArea) {
            bestConfig = {
                cols,
                rows,
                itemWidth,
                itemHeight,
                totalGridWidth,
                totalGridHeight,
                unusedArea
            };
        }
    }

    // смешения чтобы было блоки были по центру
    const offsetX = (canvasSize.width - bestConfig.totalGridWidth) / 2;
    const offsetY = (canvasSize.height - bestConfig.totalGridHeight) / 2;

    /**
     * сохраним список позиций
     * позиция по оси X
     * позиция по оси Y
     * Ширина
     * Высота
     * @type {DOMRect[]}
     */
    const positions = [];

    for (let i = 0; i < itemCount; i++) {
        // расположения по столбцу/строке
        const col = i % bestConfig.cols;
        const row = Math.floor(i / bestConfig.cols);

        // позиция по оси X/Y с учетом отступа между блоками spaceBetween
        const x = offsetX + col * (bestConfig.itemWidth + spaceBetween);
        const y = offsetY + row * (bestConfig.itemHeight + spaceBetween);

        // сохраним в список позицию каждого блока
        positions.push({
            x,
            y,
            width: bestConfig.itemWidth - spaceBetween,
            height: bestConfig.itemHeight - spaceBetween
        });
    }

    return positions;
}

/**
 * Отрисовать одного участника конференции
 * @param {Object} params
 * @param {HTMLElement | undefined} params.item
 * @param {DOMRect | undefined} params.rectSize
 * @param {HTMLElement | undefined} params.userAvatarRef
 * @param {HTMLImageElement | undefined} params.imageRef
 * @param {Boolean | undefined} params.isVideo
 * @param {Boolean | undefined} params.isOneParticipant
 */
function drawOneParticipant({
    item, rectSize,
    videoRef,
    userAvatarRef,
    imageRef,
    isVideo,
    isOneParticipant
}) {
    const borderRadius = 4;

    const avatarRef = item?.querySelector('.avatar-container');

    videoRef = videoRef ?? item?.querySelector('video');
    imageRef = imageRef ?? avatarRef?.querySelector('img');
    userAvatarRef = userAvatarRef ?? avatarRef?.querySelector('.userAvatar');
    isVideo = isVideo ?? !item?.classList.contains('display-avatar-only');

    let rect = rectSize ?? item?.getBoundingClientRect();

    const { centerX, centerY } = getCenter({ rect });

    drawRectangle({
        rect,
        borderRadius
    });

    if (isVideo && videoRef) {
        if (isOneParticipant) {
            return drawFullScreenVideo(videoRef);
        }

        return drawOneParticipantVideo(videoRef, rect);
    }

    const radius = rect.width / 3.5;
    const fontSize = rect.width / 7;
    const fillStyle = imageRef?.dataset?.backgound ? imageRef?.dataset?.backgound
        : userAvatarRef?.style?.background ?? 'rgba(255, 255, 255, 0.02)';

    ctx.save();
    drawCircle({
        centerX,
        centerY,
        radius,
        fillStyle
    });

    // у участника есть аватарка
    if (imageRef) {

        // в кэше уже есть dataUrl изображения ?
        if (convertedImages.has(imageRef.src)) {
            const img = convertedImages.get(imageRef.src);

            ctx.drawImage(img, centerX - radius / 2, centerY - radius / 2, radius, radius);
        } else {

            // мы не можем отрисовать изображения на канвасе
            // так как изображения получаются из другого домена будет падать CORS ошибка
            // конвертируем изображения в dataUrl на Electron
            // и пока она конвертируется отрисуем инициалы
            window.parent.postMessage({
                type: 'convert_src_url_to_data_url',
                data: {
                    url: imageRef.src
                } }, '*');

            const initials = imageRef.dataset.initials;

            drawLetter({
                centerX,
                centerY,
                text: initials,
                fontSize
            });
        }

        // есть инициалы
    } else if (userAvatarRef) {
        drawLetter({
            centerX,
            centerY,
            text: userAvatarRef.innerText,
            fontSize
        });
    }

    ctx.restore();
}

/**
 * Получит центр блока по его относительным размерам
 * @param {Object} params
 * @param {DOMRect} params.rect
 * @return {{centerY: number, centerX: number}}
 */
function getCenter({ rect }) {
    const centerX = rect.x + rect.width / 2;
    const centerY = rect.y + rect.height / 2;

    return {
        centerY,
        centerX
    };
}

/**
 * Блок в которым будут отрисован участник
 * @param {Object} params
 * @param {DOMRect} params.rect
 * @param {number} params.borderRadius
 */
function drawRectangle({ rect, borderRadius }) {
    ctx.fillStyle = '#1C1C1C';
    ctx.beginPath();
    ctx.roundRect(rect.x, rect.y, rect.width, rect.height, [ borderRadius ]);
    ctx.fill();
}

/**
 * Отрисовать круг (заглушка когда у участника нет видео)
 * @param {Object} params
 * @param {number} params.centerX
 * @param {number} params.centerY
 * @param {number} params.radius
 * @param {string} params.fillStyle
 */
function drawCircle({ centerX, centerY, radius, fillStyle }) {
    ctx.beginPath();
    ctx.arc(centerX, centerY, radius / 2, 0, 2 * Math.PI);
    ctx.fillStyle = fillStyle;
    ctx.fill();
    ctx.closePath();
    ctx.clip();
    ctx.stroke();
}

/**
 * Отрисовать видео на весь canvas
 * @param {HTMLVideoElement}videoTag
 */
function drawFullScreenVideo(videoTag) {
    const isReverse = isVideoReversed(videoTag);
    const videoW = videoTag.videoWidth;
    const videoH = videoTag.videoHeight;
    const canvasW = canvasSize.width;
    const canvasH = canvasSize.height;

    const scale = Math.min(canvasW / videoW, canvasH / videoH);

    const drawW = videoW * scale;
    const drawH = videoH * scale;

    const dx = (canvasW - drawW) / 2;
    const dy = (canvasH - drawH) / 2;

    if (isReverse) {
        ctx.save();
        ctx.translate(canvasW, 0);
        ctx.scale(-1, 1);
    }

    let x = isReverse ? canvasW - dx - drawW : dx;

    ctx.drawImage(videoTag, x, dy, drawW, drawH);

    if (isReverse) {
        ctx.restore();
    }
}

/**
 * Отрисовать одного участника конференции
 * @param {HTMLVideoElement} videoRef
 * @param {DOMRect} rect
 */
function drawOneParticipantVideo(videoRef, rect) {
    const borderRadius = 4;
    const drawW = rect.width;
    const drawH = rect.height;

    const videoAspect = videoRef.videoWidth / videoRef.videoHeight;
    const rectAspect = drawW / drawH;

    let drawWidth = drawW;
    let drawHeight = drawH;

    if (videoAspect > rectAspect) {
        drawHeight = drawW / videoAspect;
    } else {
        drawWidth = drawH * videoAspect;
    }

    const drawX = rect.x;
    const drawY = rect.y;

    const isReverse = isVideoReversed(videoRef);

    const offsetX = drawX + (drawW - drawWidth) / 2;
    const offsetY = drawY + (drawH - drawHeight) / 2;


    ctx.save();
    ctx.beginPath();

    // это чтобы у видео было закругление
    ctx.roundRect(offsetX, offsetY, drawWidth, drawHeight, borderRadius);
    ctx.clip();

    if (isReverse) {
        ctx.translate(offsetX + drawWidth, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(videoRef, 0, offsetY, drawWidth, drawHeight);
    } else {
        ctx.drawImage(videoRef, offsetX, offsetY, drawWidth, drawHeight);
    }

    ctx.restore();
}

/**
 * Видео отзеркалено ?
 * @param {HTMLElement}videoRef
 */
function isVideoReversed(videoRef) {
    const style = videoRef.style.transform;

    return style === 'scaleX(-1)' || videoRef?.classList?.contains('flipVideoX');
}

/**
 * Отрисовать инициалы
 * @param {Object} params
 * @param {number} params.centerX
 * @param {number} params.centerY
 * @param {string} params.text
 * @param {number} params.fontSize
 */
function drawLetter({ centerX, centerY, text, fontSize }) {
    ctx.font = `${fontSize}px "Lato Semibold"`;
    ctx.fillStyle = 'white';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(text, centerX, centerY);
}

/**
 * Завершаем отрисовку на канвас
 */
function stopDrawFrame() {
    drawId && cancelAnimationFrame(drawId);
    intervalId && window.clearInterval(intervalId);
}

/**
 * Получить больше видео когда режим спикера
 * @returns {HTMLVideoElement | null}
 */
function getLargeVideo() {
    return document.querySelector('#largeVideoWrapper video');
}

/**
 * Получить больше изображение в режиме спикера когда нет видео
 * @returns {HTMLVideoElement | null}
 */
function getLargeVideoImage() {
    return document.querySelector('#dominantSpeakerAvatarContainer img');
}

/**
 * Получить аватарку в режиме спикера когда нет изображения
 * @returns {HTMLVideoElement | null}
 */
function getLargeVideoAvatar() {
    return document.querySelector('#dominantSpeakerAvatarContainer .avatar');
}

/**
 * Получить мелкие видео когда обычный режим плитки
 * @returns NodeListOf<{HTMLVideoElement | null}>
 */
function getSmallVideos() {
    return document.querySelectorAll('#videospace .filmstrip__videos .videocontainer');
}

/**
 * Включен режим спикера
 * @return {boolean}
 */
function isSpeakerMode() {
    return !APP.store.getState()['features/video-layout'].tileViewEnabled;
}

/**
 * Кол-во участнивок в конференции
 * @return {*}
 */
function getParticipantCount() {
    const {
        local,
        remote,
        fakeParticipants,
        sortedRemoteVirtualScreenshareParticipants
    } = APP.store.getState()['features/base/participants'];

    return remote.size - fakeParticipants.size - sortedRemoteVirtualScreenshareParticipants.size + (local ? 1 : 0);
}

/**
 * Сейчас идет демонстрация экрана?
 * @return {*|boolean}
 */
function isScreenShare() {
    const tracks = APP.store.getState()['features/base/tracks'];
    const localScreenshare = tracks?.filter(t => t.local && (t.jitsiTrack || false))
        ?.find(t => t.mediaType === 'screenshare' || t.videoType === 'video');

    return localScreenshare?.jitsiTrack && !localScreenshare.jitsiTrack.isMuted();
}

/**
 * Зададим размеры канвасу
 * @param width
 * @param height
 */
function setCanvasSize(width, height) {
    canvas.width = width;
    canvas.height = height;
    canvas.style.width = `${width}px`;
    canvas.style.height = `${height}px`;
    canvas.style.imageRendering = 'pixelated';
}

/**
 * Сохраним полученные изображения в виде dataUrl
 * так как напрямую на canvas изображения из url мы отрисовать не сможем
 * если отрисуем напрямую получим CORS ошибку
 * @param url
 * @param dataUrl
 */
function saveConvertedImages({ url, dataUrl }) {
    const img = new Image();

    img.onload = () => {
        convertedImages.set(url, img);
    };
    img.src = dataUrl;
}
