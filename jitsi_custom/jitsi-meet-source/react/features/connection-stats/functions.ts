interface IResolutionItem {
    width?: number;
    height?: number;
}

interface ICodecItem {
    audio?: string;
    video?: string;
}

interface IStats {
    packetLoss?: {
        total?: number;
    };
    connectionQuality?: number;
    bandwidth?: {
        download?: number;
        upload?: number;
    };
    bitrate?: {
        download?: number;
        upload?: number;
    };
    jvbRTT?: number;
    resolution?: Record<string, IResolutionItem>;
    codec?: Record<string, ICodecItem>;
    framerate?: Record<string, number>;
}

type TSsrcMediaTypeMap = Record<string | number, string>;

/**
 * Function on stats updated
 *
 * @param {string} conferenceId
 * @param {string} memberId
 * @param {IStats} stats - stats.
 * @param {TSsrcMediaTypeMap} ssrcMediaTypeMap
 * @returns {Object} Пакет для отправки.
 */
export function preparePayloadForCollector(
    conferenceId: string,
    memberId: string,
    stats: IStats = {},
    ssrcMediaTypeMap: TSsrcMediaTypeMap = {}
) {
    const data = _prepareData(conferenceId, memberId, stats, ssrcMediaTypeMap);

    return {
        type: "TYPE_DEBUG_MESSAGE",
        namespace: "jitsi",
        company_id: 0,
        key: "jitsi_conference_metric",
        data: data,
        event_time: Math.floor(Date.now() / 1000)
    };
}

/**
 * Собирает всю статистику в один объект с массивом stream_list.
 *
 * @param {string} conferenceId
 * @param {string} memberId
 * @param {IStats} stats
 * @param {TSsrcMediaTypeMap} ssrcMediaTypeMap
 * @returns {Object} Объединённый объект данных.
 */
function _prepareData(
    conferenceId: string,
    memberId: string,
    stats: IStats = {},
    ssrcMediaTypeMap: TSsrcMediaTypeMap = {}
) {
    const {
        packetLoss = {},
        connectionQuality = 0,
        bandwidth = {},
        bitrate = {},
        jvbRTT = 0,
        resolution = {},
        codec = {},
        framerate = {}
    } = stats;

    const totalPacketLost = packetLoss.total ?? 0;
    const downloadBandwidth = bandwidth.download;
    const uploadBandwidth = bandwidth.upload;
    const downloadBitrate = bitrate.download;
    const uploadBitrate = bitrate.upload;

    const resolutionSources = Object.keys(resolution);
    const codecSources = Object.keys(codec);
    const allSources = new Set([ ...resolutionSources, ...codecSources ]);

    const streamList = [];

    for (const sourceId of allSources) {
        const sourceType: string = ssrcMediaTypeMap[sourceId] ?? "unknown";

        const resObj = resolution[sourceId];
        const fpsValue = framerate[sourceId];
        const codecObj = codec[sourceId] || {};

        if (codecObj.video) {
            let videoResolution = "";

            if (resObj?.width && resObj?.height) {
                // формируем строку вида "1920x1080 @ 30fps"
                if (fpsValue) {
                    videoResolution = `${resObj.width}x${resObj.height} @ ${fpsValue}fps`;
                } else {
                    videoResolution = `${resObj.width}x${resObj.height}`;
                }
            }

            // это значит что источник видео отключили, в стате он останется, но такие не шлем
            if (videoResolution.length < 1) {
                continue;
            }

            streamList.push({
                type: "video",
                source: sourceType,
                resolution: videoResolution,
                codec: codecObj.video
            });
        }

        if (codecObj.audio) {
            streamList.push({
                type: "audio",
                source: sourceType,
                codec: codecObj.audio
            });
        }
    }

    return {
        conference_id: conferenceId,
        member_id: memberId,
        datetime: Math.floor(Date.now() / 1000),
        packet_loss_rate: replaceUndefinedNumberValue(totalPacketLost),
        connection_quality: replaceUndefinedNumberValue(connectionQuality),
        download_bandwidth_kbits: replaceUndefinedNumberValue(downloadBandwidth),
        upload_bandwidth_kbits: replaceUndefinedNumberValue(uploadBandwidth),
        download_bitrate_kbits: replaceUndefinedNumberValue(downloadBitrate),
        upload_bitrate_kbits: replaceUndefinedNumberValue(uploadBitrate),
        videobridge_rtt: replaceUndefinedNumberValue(jvbRTT),
        stream_list: streamList
    };
}

/**
 * Подменяет undefined, null или NaN на 0.
 */
function replaceUndefinedNumberValue(value: any): number {
    if (value === undefined || value === null || Number.isNaN(value)) {
        return 0;
    }
    return Math.floor(value);
}
