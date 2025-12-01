/**
 * UI helper functions for popup and options pages
 */

/**
 * Shows a status message in the UI
 * @param statusElement The HTML element to display the status in
 * @param message The message to display
 * @param type The type of status (success or error)
 * @param keepOpen Whether to keep the message open (default: false, auto-hides after 3 seconds)
 */
export function showStatus(
    statusElement: HTMLElement,
    message: string,
    type: 'success' | 'error',
    keepOpen: boolean = false
): void {
    statusElement.textContent = message;
    statusElement.className = `status-message ${type} show`;

    // Log errors to console
    if (type === 'error') {
        console.error('Status error:', message);
    } else {
        console.log('Status success:', message);
    }

    // Only auto-hide if not explicitly told to keep open
    if (!keepOpen) {
        setTimeout(() => {
            statusElement.classList.remove('show');
        }, 3000);
    }
}

/**
 * Shows an API host required error with a link to the options page
 * @param statusElement The HTML element to display the error in
 * @param runtime The browser runtime API (chrome.runtime or browser.runtime)
 */
export function showApiHostRequiredError(
    statusElement: HTMLElement,
    runtime: typeof chrome.runtime
): void {
    statusElement.innerHTML = 'Please <a href="#" id="configLink" style="color: #721c24; text-decoration: underline; font-weight: bold;">configure API host</a> in the options page.';
    statusElement.className = 'status-message error show';

    console.error('API host not configured. Please configure it in the options page.');

    // Handle click on config link
    const configLink = document.getElementById('configLink');
    if (configLink) {
        configLink.addEventListener('click', (e) => {
            e.preventDefault();
            if (runtime && typeof runtime.openOptionsPage === 'function') {
                runtime.openOptionsPage();
            }
        });
    }
}

/**
 * Shows a login required error with a link to the options page
 * @param statusElement The HTML element to display the error in
 * @param runtime The browser runtime API (chrome.runtime or browser.runtime)
 */
export function showLoginRequiredError(
    statusElement: HTMLElement,
    runtime: typeof chrome.runtime
): void {
    statusElement.innerHTML = 'Please <a href="#" id="loginLink" style="color: #721c24; text-decoration: underline; font-weight: bold;">login</a> to save bookmarks.';
    statusElement.className = 'status-message error show';

    console.error('Authentication required. Please login in the options page.');

    // Handle click on login link
    const loginLink = document.getElementById('loginLink');
    if (loginLink) {
        loginLink.addEventListener('click', (e) => {
            e.preventDefault();
            if (runtime && typeof runtime.openOptionsPage === 'function') {
                runtime.openOptionsPage();
            }
        });
    }

    // Don't auto-hide 401 errors - keep them visible
}

/**
 * Starts an animated clock emoji animation on a button
 * @param button The button element to animate
 * @param baseText The base text to display before the clock emoji
 * @returns A function to stop the animation
 */
export function startClockAnimation(
    button: HTMLButtonElement,
    baseText: string
): () => void {
    const clocks = ['🕐','🕑','🕒','🕓','🕔','🕕','🕖','🕗','🕘','🕙','🕚','🕛'];
    let clockIdx = 0;

    const interval = setInterval(() => {
        button.textContent = `${baseText} ${clocks[clockIdx]}`;
        clockIdx = (clockIdx + 1) % clocks.length;
    }, 400);

    // Return function to stop the animation
    return () => {
        clearInterval(interval);
    };
}

