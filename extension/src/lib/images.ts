/**
 * Image embedding and conversion functions
 */

import { resolveURL } from './utils';
import { BackgroundImageMatch } from '../types';

/**
 * Converts an image URL to a data URI
 * @param url The image URL to convert
 * @returns The data URI string or null if conversion fails
 */
export async function imageToDataURI(url: string): Promise<string | null> {
    try {
        const response = await fetch(url, {
            method: 'GET',
            mode: 'cors',
            credentials: 'omit'
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const blob = await response.blob();
        return new Promise<string | null>((resolve, reject) => {
            const reader = new FileReader();
            reader.onloadend = () => resolve(reader.result as string | null);
            reader.onerror = reject;
            reader.readAsDataURL(blob);
        });
    } catch (error) {
        console.warn(`Archive: Failed to convert image to data URI: ${url}`, error);
        return null;
    }
}

/**
 * Embeds all images in the page as data URIs, including:
 * - <img> tags
 * - <source> tags (in <picture> elements)
 * - CSS background images (in <style> tags and inline styles)
 */
export async function embedAllImages(): Promise<void> {
    const imgTags = Array.from(document.querySelectorAll<HTMLImageElement>('img[src]'));
    console.log(`Archive: Found ${imgTags.length} image tag(s) to embed`);

    const imagePromises = imgTags.map(async (img) => {
        const src = img.getAttribute('src');
        if (!src || src.startsWith('data:')) {
            return; // Skip if already data URI or no src
        }

        const absoluteURL = resolveURL(src, window.location.href);
        const dataURI = await imageToDataURI(absoluteURL);
        if (dataURI) {
            img.setAttribute('src', dataURI);
            img.setAttribute('data-archive-embedded', 'true');
        }
    });

    await Promise.all(imagePromises);

    // Process all <source> tags (especially in <picture> elements)
    const sourceTags = Array.from(document.querySelectorAll<HTMLSourceElement>('source[src], source[srcset]'));
    console.log(`Archive: Found ${sourceTags.length} source tag(s) to embed`);

    const sourcePromises = sourceTags.map(async (source) => {
        // Process src attribute if present
        const src = source.getAttribute('src');
        if (src && !src.startsWith('data:')) {
            const absoluteURL = resolveURL(src, window.location.href);
            const dataURI = await imageToDataURI(absoluteURL);
            if (dataURI) {
                source.setAttribute('src', dataURI);
                source.setAttribute('data-archive-embedded', 'true');
            }
        }

        // Process srcset attribute if present
        // TODO double check if needed or if it makes sense
        const srcset = source.getAttribute('srcset');
        if (srcset && !srcset.startsWith('data:')) {
            // Parse srcset: "image.jpg 1x, image-2x.jpg 2x, image-3x.jpg 3x"
            // or "image-small.jpg 300w, image-large.jpg 600w"
            const srcsetEntries = srcset.split(',').map(entry => entry.trim());
            const processedEntries: string[] = [];

            for (const entry of srcsetEntries) {
                // Split URL and descriptor (e.g., "image.jpg 1x" -> ["image.jpg", "1x"])
                const parts = entry.trim().split(/\s+/);
                const url = parts[0];
                const descriptor = parts.slice(1).join(' '); // In case descriptor has spaces

                if (url && !url.startsWith('data:')) {
                    const absoluteURL = resolveURL(url, window.location.href);
                    console.log(`Archive: Embedding source srcset image: ${absoluteURL}`);

                    const dataURI = await imageToDataURI(absoluteURL);
                    if (dataURI) {
                        // Reconstruct entry with data URI
                        const newEntry = descriptor ? `${dataURI} ${descriptor}` : dataURI;
                        processedEntries.push(newEntry);
                        console.log(`Archive: Successfully embedded source srcset image: ${absoluteURL}`);
                    } else {
                        // Keep original entry if conversion failed
                        processedEntries.push(entry);
                    }
                } else {
                    // Keep entry if already data URI or no URL
                    processedEntries.push(entry);
                }
            }

            // Update srcset with processed entries
            if (processedEntries.length > 0) {
                source.setAttribute('srcset', processedEntries.join(', '));
                source.setAttribute('data-archive-embedded', 'true');
            }
        }
    });

    await Promise.all(sourcePromises);

    // Process CSS background images
    const allStyleTags = Array.from(document.querySelectorAll<HTMLStyleElement>('style'));

    for (const styleTag of allStyleTags) {
        let cssText = styleTag.textContent || '';
        const originalCSS = cssText;

        // Find all background-image URLs
        // Match: background-image: url(...) or background: ... url(...)
        const bgImageRegex = /background(?:-image)?\s*:\s*[^;]*url\s*\(\s*['"]?([^'")]+)['"]?\s*\)/gi;
        const matches: BackgroundImageMatch[] = [];
        let match: RegExpExecArray | null;

        while ((match = bgImageRegex.exec(cssText)) !== null) {
            matches.push({
                url: match[1],
                fullMatch: match[0]
            });
        }

        // Process each background image URL
        for (const bgMatch of matches) {
            const imageURL = bgMatch.url;
            if (imageURL.startsWith('data:')) {
                continue; // Skip if already data URI
            }

            const absoluteURL = resolveURL(imageURL, window.location.href);
            console.log(`Archive: Embedding CSS background image: ${absoluteURL}`);

            const dataURI = await imageToDataURI(absoluteURL);
            if (dataURI) {
                // Replace the URL in the CSS
                const escapedURL = imageURL.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                cssText = cssText.replace(
                    new RegExp(`url\\(\\s*['"]?${escapedURL}['"]?\\s*\\)`, 'gi'),
                    `url(${dataURI})`
                );
                console.log(`Archive: Successfully embedded CSS background image: ${absoluteURL}`);
            }
        }

        // Update the style tag if CSS was modified
        if (cssText !== originalCSS) {
            styleTag.textContent = cssText;
            styleTag.setAttribute('data-archive-images-embedded', 'true');
        }
    }

    // Process inline styles on elements
    const elementsWithInlineStyles = Array.from(document.querySelectorAll<HTMLElement>('[style]'));
    console.log(`Archive: Found ${elementsWithInlineStyles.length} element(s) with inline styles`);

    for (const element of elementsWithInlineStyles) {
        let inlineStyle = element.getAttribute('style') || '';
        const originalStyle = inlineStyle;

        // Find background-image URLs in inline styles
        const bgImageRegex = /background(?:-image)?\s*:\s*[^;]*url\s*\(\s*['"]?([^'")]+)['"]?\s*\)/gi;
        const matches: BackgroundImageMatch[] = [];
        let match: RegExpExecArray | null;

        while ((match = bgImageRegex.exec(inlineStyle)) !== null) {
            matches.push({
                url: match[1],
                fullMatch: match[0]
            });
        }

        // Process each background image URL
        for (const bgMatch of matches) {
            const imageURL = bgMatch.url;
            if (imageURL.startsWith('data:')) {
                continue; // Skip if already data URI
            }

            const absoluteURL = resolveURL(imageURL, window.location.href);
            console.log(`Archive: Embedding inline style background image: ${absoluteURL}`);

            const dataURI = await imageToDataURI(absoluteURL);
            if (dataURI) {
                // Replace the URL in the inline style
                const escapedURL = imageURL.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                inlineStyle = inlineStyle.replace(
                    new RegExp(`url\\(\\s*['"]?${escapedURL}['"]?\\s*\\)`, 'gi'),
                    `url(${dataURI})`
                );
                console.log(`Archive: Successfully embedded inline style background image: ${absoluteURL}`);
            }
        }

        // Update the inline style if it was modified
        if (inlineStyle !== originalStyle) {
            element.setAttribute('style', inlineStyle);
            element.setAttribute('data-archive-images-embedded', 'true');
        }
    }

    console.log("Archive: Image embedding complete!");
}

