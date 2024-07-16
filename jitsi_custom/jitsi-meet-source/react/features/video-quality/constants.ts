/**
 * Default last-n value used to be used for "HD" video quality setting when no channelLastN value is specified.
 *
 * @type {number}
 */
export const DEFAULT_LAST_N = 20;

/**
 * The supported remote video resolutions. The values are currently based on
 * available simulcast layers.
 *
 * @type {object}
 */
export const VIDEO_QUALITY_LEVELS = {
    ULTRA: 2160,
    HIGH: 720,
    STANDARD: 480,
    LOW: 480,
    NONE: 0
};

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
    'low': VIDEO_QUALITY_LEVELS.LOW,
    'standard': VIDEO_QUALITY_LEVELS.STANDARD,
    'high': VIDEO_QUALITY_LEVELS.HIGH,
    'ultra': VIDEO_QUALITY_LEVELS.ULTRA
};
