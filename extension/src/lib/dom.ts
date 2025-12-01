/**
 * DOM manipulation functions for archiving pages
 */

/**
 * Removes all script tags and event listeners from the page
 */
export function removeAllScripts(): void {
    const scriptTags = Array.from(document.querySelectorAll<HTMLScriptElement>('script'));
    console.log(`Archive: Found ${scriptTags.length} script tag(s) to remove`);

    // Remove each script tag
    scriptTags.forEach((script) => {
        script.remove();
    });

    // Also remove event listeners by cloning and replacing the body
    // This removes most event listeners attached to elements
    const body = document.body;
    if (body && body.parentNode) {
        const newBody = body.cloneNode(true) as HTMLBodyElement;
        body.parentNode.replaceChild(newBody, body);
    }

    // Remove inline event handlers from all elements
    const allElements = document.querySelectorAll('*');
    allElements.forEach((element) => {
        // Remove common inline event handlers
        const events = ['onclick', 'onload', 'onerror', 'onchange', 'onsubmit', 'onfocus', 'onblur',
            'onmouseover', 'onmouseout', 'onkeydown', 'onkeyup', 'onkeypress'];
        events.forEach((event) => {
            if (element.hasAttribute(event)) {
                element.removeAttribute(event);
            }
        });
    });
}

/**
 * Removes all <noscript> and <iframe> tags from the page
 */
export function removeNoscriptAndIframes(): void {
    const noscriptTags = Array.from(document.querySelectorAll<HTMLElement>('noscript'));
    const iframeTags = Array.from(document.querySelectorAll<HTMLIFrameElement>('iframe'));

    noscriptTags.forEach((noscript) => {
        noscript.remove();
    });

    iframeTags.forEach((iframe) => {
        iframe.remove();
    });
}

/**
 * Disables all links by replacing href with "#" + original href
 */
export function disableAllLinks(): void {
    console.log("Archive: Disabling all links...");

    // Find all anchor tags with href attributes
    const links = Array.from(document.querySelectorAll<HTMLAnchorElement>('a[href]'));
    console.log(`Archive: Found ${links.length} link(s) to disable`);

    links.forEach((link) => {
        const originalHref = link.getAttribute('href');

        // Skip if already disabled or if it's already an anchor link starting with #
        if (originalHref && !originalHref.startsWith('#')) {
            // Replace href with "#" + original href
            link.setAttribute('href', '#' + originalHref);
            link.setAttribute('data-archive-original-href', originalHref);
            console.log(`Archive: Disabled link: ${originalHref} -> #${originalHref}`);
        }
    });

    console.log("Archive: Link disabling complete!");
}

/**
 * Cleans up the <head> tag, keeping only CSS-related elements
 */
export function cleanupHead(): void {
    console.log("Archive: Cleaning up <head> tag...");

    const head = document.head;
    if (!head) {
        console.log("Archive: No <head> tag found");
        return;
    }

    // Get all children of head
    const headChildren = Array.from(head.children);
    console.log(`Archive: Found ${headChildren.length} element(s) in <head>`);

    // Elements to keep (CSS-related)
    const elementsToKeep: Element[] = [];

    headChildren.forEach((element) => {
        const tagName = element.tagName.toLowerCase();

        // Keep style tags (CSS)
        if (tagName === 'style') {
            elementsToKeep.push(element);
            console.log(`Archive: Keeping <style> tag`);
            return;
        }

        // Keep link tags that are stylesheets (though they should already be inlined)
        if (tagName === 'link') {
            const rel = element.getAttribute('rel');
            if (rel && rel.toLowerCase() === 'stylesheet') {
                // These should already be inlined, but keep them just in case
                elementsToKeep.push(element);
                console.log(`Archive: Keeping stylesheet <link> tag`);
                return;
            }
        }

        // Keep meta charset (important for encoding)
        if (tagName === 'meta') {
            const charset = element.getAttribute('charset');
            if (charset) {
                elementsToKeep.push(element);
                console.log(`Archive: Keeping charset <meta> tag`);
                return;
            }
        }

        // Remove everything else
        console.log(`Archive: Removing <${tagName}> tag from <head>`);
        element.remove();
    });

    console.log(`Archive: Kept ${elementsToKeep.length} element(s) in <head>`);
    console.log("Archive: <head> cleanup complete!");
}

