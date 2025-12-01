/**
 * CSS inlining and processing functions
 */

import { resolveURL } from './utils';
import { ImportMatch } from '../types';

/**
 * Fetches CSS content from a URL
 * @param url The URL to fetch CSS from
 * @returns The CSS text content or null if fetch fails
 */
async function fetchCSS(url: string): Promise<string | null> {
    try {
        const response = await fetch(url, {
            method: 'GET',
            mode: 'cors',
            credentials: 'omit'
        });
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        return await response.text();
    } catch (error) {
        console.warn(`Archive: Failed to fetch CSS from ${url}:`, error);
        return null;
    }
}

/**
 * Resolves @import statements in CSS by fetching and inlining imported stylesheets
 * @param cssText The CSS text containing @import statements
 * @param baseURL The base URL for resolving relative @import URLs
 * @returns CSS text with @import statements resolved
 */
async function resolveImports(cssText: string, baseURL: string): Promise<string> {
    const importRegex = /@import\s+(?:url\()?['"]?([^'")]+)['"]?\)?[^;]*;/g;
    let resolvedCSS = cssText;
    const imports: ImportMatch[] = [];
    let match: RegExpExecArray | null;

    // Store both the full match and the URL for exact replacement
    while ((match = importRegex.exec(cssText)) !== null) {
        imports.push({
            // The entire @import statement
            fullMatch: match[0],
            // The extracted URL
            url: match[1]
        });
    }

    for (const importData of imports) {
        const importURL = importData.url;
        const fullImportStatement = importData.fullMatch;
        const absoluteURL = resolveURL(importURL, baseURL);
        const importedCSS = await fetchCSS(absoluteURL);
        if (importedCSS) {
            // Resolve nested imports
            const nestedResolved = await resolveImports(importedCSS, absoluteURL);
            // Replace using the exact full match instead of reconstructing regex
            resolvedCSS = resolvedCSS.replace(fullImportStatement, nestedResolved);
        } else {
            // If CSS fetch fails, remove the @import statement to prevent broken CSS
            console.warn(`Archive: Removing unresolved @import: ${fullImportStatement}`);
            resolvedCSS = resolvedCSS.replace(fullImportStatement, '');
        }
    }

    return resolvedCSS;
}

/**
 * Inlines all external CSS stylesheets into <style> tags and resolves @import statements
 */
export async function inlineAllCSS(): Promise<void> {
    const linkTags = Array.from(document.querySelectorAll<HTMLLinkElement>('link[rel="stylesheet"]'));
    console.log(`Archive: Found ${linkTags.length} stylesheet(s) to inline`);

    const inlinePromises = linkTags.map(async (link) => {
        const href = link.getAttribute('href');
        if (!href) return;

        const absoluteURL = resolveURL(href, window.location.href);
        const cssText = await fetchCSS(absoluteURL);
        if (!cssText) {
            console.warn(`Archive: Skipping ${absoluteURL} - could not fetch`);
            return;
        }

        const resolvedCSS = await resolveImports(cssText, absoluteURL);

        // Create inline style tag
        const styleTag = document.createElement('style');
        styleTag.textContent = resolvedCSS;
        styleTag.setAttribute('data-archive-inlined', 'true');
        styleTag.setAttribute('data-archive-source', absoluteURL);

        if (link.parentNode) {
            link.parentNode.insertBefore(styleTag, link);
        }
        link.remove();

        console.log(`Archive: Successfully inlined CSS from ${absoluteURL}`);
    });

    // Process existing <style> tags to resolve @import statements
    const styleTags = Array.from(document.querySelectorAll<HTMLStyleElement>('style:not([data-archive-inlined])'));
    console.log(`Archive: Found ${styleTags.length} existing <style> tag(s) to process`);

    const stylePromises = styleTags.map(async (styleTag) => {
        const cssText = styleTag.textContent || '';
        const resolvedCSS = await resolveImports(cssText, window.location.href);
        if (resolvedCSS !== cssText) {
            styleTag.textContent = resolvedCSS;
            styleTag.setAttribute('data-archive-processed', 'true');
            console.log('Archive: Resolved @import statements in existing <style> tag');
        }
    });

    // Wait for all CSS to be inlined
    await Promise.all([...inlinePromises, ...stylePromises]);
}

