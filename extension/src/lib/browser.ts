/**
 * Browser API compatibility helper
 */

export function getBrowserAPI(): typeof chrome {
    // @ts-expect-error - browser may not be defined in Chrome/Edge
    return typeof browser !== 'undefined' ? browser : chrome;
}

export function getBrowserStorage(): typeof chrome.storage {
    return getBrowserAPI().storage;
}

export function getBrowserRuntime(): typeof chrome.runtime {
    return getBrowserAPI().runtime;
}

export function getBrowserTabs(): typeof chrome.tabs {
    return getBrowserAPI().tabs;
}

