/**
 * Default last-n value used to be used for "HD" video quality setting when no channelLastN value is specified.
 *
 * @type {number}
 */
export const DEFAULT_LAST_N = 16;

/**
 * The supported video codecs.
 *
 * @type {enum}
 */
export enum VIDEO_CODEC {
    AV1 = 'av1',
    H264 = 'h264',
    VP8 = 'vp8',
    VP9 = 'vp9'
}

/**
 * The supported remote video resolutions. The values are currently based on
 * available simulcast layers.
 *
 * @type {object}
 */
export const VIDEO_QUALITY_LEVELS = {
    ULTRA: 2160,
    FULL_HD: 1080,
    HIGH: 720,
    MEDIUM_HD: 640,
    MEDIUM: 480,
    STANDARD: 360,
    STANDARD_SD: 240,
    LOW_HD: 180,
    LOW: 144,
    NONE: 0
};

export const VIDEO_QUALITY_GROUPS = {
    VIDEO_GROUP_QUALITY_HIGH: {
        VIDEO_QUALITY_SCREEN_SHARE: VIDEO_QUALITY_LEVELS.ULTRA, // screen_share 4k
        VIDEO_QUALITY_T1: VIDEO_QUALITY_LEVELS.FULL_HD, // t1 1080
        VIDEO_QUALITY_T2: VIDEO_QUALITY_LEVELS.HIGH, // t2 720
        VIDEO_QUALITY_T3: VIDEO_QUALITY_LEVELS.MEDIUM_HD, // t3 640
        VIDEO_QUALITY_T3_MIN: VIDEO_QUALITY_LEVELS.STANDARD, // t3_min 360
        VIDEO_QUALITY_T4: VIDEO_QUALITY_LEVELS.STANDARD_SD, //  t4 240
    },
    VIDEO_GROUP_QUALITY_MEDIUM: {
        VIDEO_QUALITY_SCREEN_SHARE: VIDEO_QUALITY_LEVELS.ULTRA, // screen_share 4k
        VIDEO_QUALITY_T1: VIDEO_QUALITY_LEVELS.HIGH, // t1 720
        VIDEO_QUALITY_T2: VIDEO_QUALITY_LEVELS.MEDIUM_HD, // t2 640
        VIDEO_QUALITY_T3: VIDEO_QUALITY_LEVELS.MEDIUM, // t3 480
        VIDEO_QUALITY_T3_MIN: VIDEO_QUALITY_LEVELS.STANDARD, // t3_min 360
        VIDEO_QUALITY_T4: VIDEO_QUALITY_LEVELS.LOW_HD, //  t4 180
    },
    VIDEO_GROUP_QUALITY_LOW: {
        VIDEO_QUALITY_SCREEN_SHARE: VIDEO_QUALITY_LEVELS.ULTRA, // screen_share 4k
        VIDEO_QUALITY_T1: VIDEO_QUALITY_LEVELS.MEDIUM_HD, // t1 640
        VIDEO_QUALITY_T2: VIDEO_QUALITY_LEVELS.MEDIUM, // t2 480
        VIDEO_QUALITY_T3: VIDEO_QUALITY_LEVELS.STANDARD, // t3 360
        VIDEO_QUALITY_T3_MIN: VIDEO_QUALITY_LEVELS.STANDARD_SD, // t3_min 240
        VIDEO_QUALITY_T4: VIDEO_QUALITY_LEVELS.LOW, //  t4 144
    },
}

/**
 * Indicates unlimited video quality.
 */
export const VIDEO_QUALITY_UNLIMITED = -1;

/**
 * The maximum video quality from the VIDEO_QUALITY_LEVELS map.
 */
export const MAX_VIDEO_QUALITY = Math.max(...Object.values(VIDEO_QUALITY_LEVELS));

/**
 * Maps quality level names used in the config.videoQuality.minHeightForQualityLvl to the quality level constants used
 * by the application.
 *
 * @type {Object}
 */
export const CFG_LVL_TO_APP_QUALITY_LVL = {
    'low': VIDEO_QUALITY_LEVELS.STANDARD_SD,
    'standard': VIDEO_QUALITY_LEVELS.MEDIUM_HD,
    'high': VIDEO_QUALITY_LEVELS.HIGH,
    'ultra': VIDEO_QUALITY_LEVELS.ULTRA
};
