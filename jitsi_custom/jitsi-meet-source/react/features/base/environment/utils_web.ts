import { browser } from "../lib-jitsi-meet";
import { isMobileBrowser } from "./utils";

/**
 * Returns whether or not the current environment is a electron.
 *
 * @returns {boolean}
 */
export function isNeedShowElectronOnlyElements() {
    return browser.isElectron() && !isMobileBrowser()
}
