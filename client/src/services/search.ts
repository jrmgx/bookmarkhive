/**
 * Search service using Fuse.js for fuzzy search
 */

import Fuse from 'fuse.js';
import type { Bookmark } from '../types';
import type { IFuseOptions } from 'fuse.js';

/**
 * Search configuration for Fuse.js
 */
const fuseOptions: IFuseOptions<Bookmark> = {
  keys: [
    { name: 'title', weight: 0.4 },
    { name: 'url', weight: 0.3 },
    { name: 'domain', weight: 0.2 },
    { name: 'tags.name', weight: 0.1 },
  ],
  threshold: 0.4, // Moderate fuzziness (0.0 = exact match, 1.0 = match anything)
  includeScore: true,
  minMatchCharLength: 2,
};

/**
 * Search through bookmarks using Fuse.js
 * @param query Search query string
 * @param bookmarks Array of bookmarks to search through
 * @returns Array of matching bookmarks sorted by relevance
 */
export function searchBookmarks(query: string, bookmarks: Bookmark[]): Bookmark[] {
  if (!query.trim() || bookmarks.length === 0) {
    return [];
  }

  const fuse = new Fuse(bookmarks, fuseOptions);
  const results = fuse.search(query);

  // Return bookmarks sorted by relevance (lower score = better match)
  return results.map((result) => result.item);
}

