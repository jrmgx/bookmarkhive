/**
 * Page metadata extraction functions
 */

import { PageData } from '../types';

/**
 * Extracts metadata from the current page
 * @returns Page metadata object containing title, URL, description, image, and favicon
 */
export function extractPageMetadata(): PageData {
    const pageData: PageData = {
        title: document.title || '',
        url: window.location.href,
        description: null,
        image: null,
        favicon: null
    };

    // Get meta description
    const metaDescription = document.querySelector('meta[name="description"]') as HTMLMetaElement | null;
    if (metaDescription) {
        pageData.description = metaDescription.content || null;
    } else {
        // Try og:description as fallback
        const ogDescription = document.querySelector('meta[property="og:description"]') as HTMLMetaElement | null;
        if (ogDescription) {
            pageData.description = ogDescription.content || null;
        }
    }

    // Get og:image
    const ogImage = document.querySelector('meta[property="og:image"]') as HTMLMetaElement | null;
    if (ogImage) {
        pageData.image = ogImage.content || null;
    }

    // Get favicon
    const faviconLink = document.querySelector('link[rel="icon"]') as HTMLLinkElement | null ||
        document.querySelector('link[rel="shortcut icon"]') as HTMLLinkElement | null ||
        document.querySelector('link[rel="apple-touch-icon"]') as HTMLLinkElement | null;

    if (faviconLink && faviconLink.href) {
        // Resolve relative URLs to absolute
        pageData.favicon = new URL(faviconLink.href, window.location.href).href;
    } else {
        // Fallback to default favicon location
        pageData.favicon = new URL('/favicon.ico', window.location.origin).href;
    }

    return pageData;
}

