/**
 * Bookmark indexing service
 * Fetches all bookmarks and stores them in localStorage for client-side search
 */

import { getBookmarks, getCursorFromUrl } from './api';
import type { Bookmark } from '../types';

const STORAGE_KEY = 'bookmarkIndex';

export type ProgressCallback = (progress: number, fetched: number, total: number) => void;

/**
 * Index all bookmarks by fetching all pages
 * @param onProgress Optional callback to track indexing progress
 * @returns Promise that resolves with all indexed bookmarks
 */
export async function indexAllBookmarks(onProgress?: ProgressCallback): Promise<Bookmark[]> {
  const allBookmarks: Bookmark[] = [];
  let cursor: string | undefined;
  let total: number | null = null;
  let fetched = 0;

  try {
    // Fetch first page to get total count
    const firstResponse = await getBookmarks();
    allBookmarks.push(...firstResponse.collection);
    fetched += firstResponse.collection.length;
    total = firstResponse.total;
    cursor = getCursorFromUrl(firstResponse.nextPage);

    // Report initial progress
    if (onProgress && total !== null) {
      const progress = Math.round((fetched / total) * 100);
      onProgress(progress, fetched, total);
    }

    // Continue fetching remaining pages
    while (cursor) {
      const response = await getBookmarks(undefined, cursor);
      allBookmarks.push(...response.collection);
      fetched += response.collection.length;
      cursor = getCursorFromUrl(response.nextPage);

      // Report progress
      if (onProgress && total !== null) {
        const progress = Math.round((fetched / total) * 100);
        onProgress(progress, fetched, total);
      }
    }

    // Store in localStorage
    localStorage.setItem(STORAGE_KEY, JSON.stringify(allBookmarks));

    // Report completion
    if (onProgress && total !== null) {
      onProgress(100, fetched, total);
    }

    return allBookmarks;
  } catch (error) {
    // Clear partial index on error
    clearIndex();
    throw error;
  }
}

/**
 * Get indexed bookmarks from localStorage
 * @returns Array of bookmarks or null if not indexed
 */
export function getIndexedBookmarks(): Bookmark[] | null {
  try {
    const stored = localStorage.getItem(STORAGE_KEY);
    if (!stored) {
      return null;
    }
    return JSON.parse(stored) as Bookmark[];
  } catch {
    return null;
  }
}

/**
 * Clear the bookmark index from localStorage
 */
export function clearIndex(): void {
  localStorage.removeItem(STORAGE_KEY);
}

/**
 * Check if bookmarks are indexed
 * @returns true if index exists and is valid
 */
export function isIndexed(): boolean {
  const bookmarks = getIndexedBookmarks();
  return bookmarks !== null && Array.isArray(bookmarks) && bookmarks.length > 0;
}

