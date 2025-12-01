/**
 * API interaction functions for communicating with the backend
 * Includes authentication and storage management
 */

import { getBrowserStorage } from './lib/browser';
import { Tag, TagsResponse, AuthRequest, AuthResponse, BookmarkPayload, FileObjectResponse } from './types';

// ============================================================================
// Storage Management Functions
// ============================================================================

/**
 * Retrieves the JWT token from secure storage
 * @returns Promise that resolves to the JWT token or null if not found
 */
export async function getJWTToken(): Promise<string | null> {
    const storage = getBrowserStorage();

    if (!storage) {
        console.error('Storage API not available');
        return null;
    }

    return new Promise((resolve) => {
        storage.local.get(['jwtToken'], (result: { jwtToken?: string }) => {
            if (chrome.runtime.lastError) {
                console.error('Error retrieving token:', chrome.runtime.lastError.message);
                resolve(null);
            } else {
                resolve(result.jwtToken || null);
            }
        });
    });
}

/**
 * Clears the JWT token from storage
 */
export async function clearJWTToken(): Promise<void> {
    const storage = getBrowserStorage();

    if (!storage) {
        console.error('Storage API not available');
        return;
    }

    return new Promise((resolve) => {
        storage.local.remove(['jwtToken'], () => {
            if (chrome.runtime.lastError) {
                console.error('Error clearing token:', chrome.runtime.lastError.message);
            }
            resolve();
        });
    });
}

/**
 * Gets the API host from storage
 * @returns Promise that resolves to the API host or null if not configured
 */
export async function getAPIHost(): Promise<string | null> {
    const storage = getBrowserStorage();

    if (!storage) {
        console.error('Storage API not available');
        return null;
    }

    return new Promise((resolve) => {
        storage.local.get(['apiHost'], (result: { apiHost?: string }) => {
            if (chrome.runtime.lastError) {
                console.error('Error retrieving API host:', chrome.runtime.lastError.message);
                resolve(null);
            } else {
                resolve(result.apiHost || null);
            }
        });
    });
}

// ============================================================================
// API Interaction Functions
// ============================================================================

/**
 * Makes an authenticated API request with the JWT token
 * @param url The API endpoint URL
 * @param options Fetch options (method, body, etc.)
 * @returns Promise that resolves to the response
 */
export async function authenticatedFetch(
    url: string,
    options: RequestInit = {}
): Promise<Response> {
    const token = await getJWTToken();

    if (!token) {
        throw new Error('No authentication token found. Please login in the options page.');
    }

    const headers = new Headers(options.headers);
    headers.set('Authorization', `Bearer ${token}`);
    headers.set('accept', 'application/ld+json');
    // Use application/ld+json for API Platform compatibility
    if (!headers.has('Content-Type')) {
        headers.set('Content-Type', 'application/ld+json');
    }

    return fetch(url, {
        ...options,
        headers: headers
    });
}

/**
 * Authenticates the user and returns the JWT token
 * @param email User email
 * @param password User password
 * @returns Promise that resolves to the authentication response with token
 */
export async function authenticate(email: string, password: string): Promise<AuthResponse> {
    const apiHost = await getAPIHost();
    if (!apiHost) {
        throw new Error('API host not configured');
    }

    const authRequest: AuthRequest = {
        email: email,
        password: password
    };

    const response = await fetch(`${apiHost}/api/auth`, {
        method: 'POST',
        headers: {
            'accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(authRequest)
    });

    if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`Authentication failed: ${response.status} ${errorText}`);
    }

    const authResponse: AuthResponse = await response.json();
    if (!authResponse.token) {
        throw new Error('No token received from server');
    }

    return authResponse;
}

/**
 * Uploads a file to the API and returns the file object response
 * @param file The file to upload (File or Blob)
 * @param apiHost The API host URL
 * @returns Promise that resolves to the file object response containing @id
 */
export async function uploadFileObject(
    file: File | Blob,
    apiHost: string
): Promise<FileObjectResponse> {
    const token = await getJWTToken();

    if (!token) {
        throw new Error('No authentication token found. Please login in the options page.');
    }

    // Create FormData with the file
    const formData = new FormData();
    formData.append('file', file);

    const response = await fetch(`${apiHost}/api/file_objects`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'accept': 'application/ld+json'
            // Don't set Content-Type - browser will set it with boundary for multipart/form-data
        },
        body: formData
    });

    if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`Failed to upload file: ${response.status} ${errorText}`);
    }

    const fileObject = await response.json();
    return fileObject;
}

/**
 * Creates a bookmark via API
 * @param payload The bookmark payload
 * @param apiHost The API host URL
 * @returns Promise that resolves to the created bookmark
 */
export async function createBookmark(
    payload: BookmarkPayload,
    apiHost: string
): Promise<unknown> {
    const token = await getJWTToken();
    if (!token) {
        throw new Error('No authentication token found. Please login in the options page.');
    }

    const response = await fetch(`${apiHost}/api/users/me/bookmarks`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'accept': 'application/ld+json',
            'Content-Type': 'application/ld+json'
        },
        body: JSON.stringify(payload)
    });

    if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`Failed to create bookmark: ${response.status} ${errorText}`);
    }

    return await response.json();
}

/**
 * Creates a new tag via API
 * @param tagName The name of the tag to create
 * @param apiHost The API host URL
 * @returns The created tag
 */
export async function createTag(tagName: string, apiHost: string): Promise<Tag> {
    const token = await getJWTToken();
    if (!token) {
        throw new Error('No authentication token found. Please login in the options page.');
    }

    const response = await fetch(`${apiHost}/api/users/me/tags`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'accept': 'application/ld+json',
            'Content-Type': 'application/ld+json'
        },
        body: JSON.stringify({ name: tagName })
    });

    if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`Failed to create tag: ${response.status} ${errorText}`);
    }

    const newTag: Tag = await response.json();
    return newTag;
}

/**
 * Fetches all user tags from the API
 * @returns Array of user tags
 */
export async function fetchUserTags(): Promise<Tag[]> {
    const apiHost = await getAPIHost();
    if (!apiHost) {
        throw new Error('API host not configured');
    }

    const token = await getJWTToken();
    if (!token) {
        throw new Error('No authentication token found. Please login in the options page.');
    }

    const response = await fetch(`${apiHost}/api/users/me/tags`, {
        method: 'GET',
        headers: {
            'Authorization': `Bearer ${token}`,
            'accept': 'application/ld+json'
        }
    });

    if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`Failed to fetch tags: ${response.status} ${errorText}`);
    }

    const tagsData: TagsResponse = await response.json();
    return tagsData.member || [];
}

/**
 * Ensures all selected tags exist and returns their IRIs
 * Creates new tags if they don't exist
 * @param selectedTagNames Array of tag names to ensure exist
 * @param existingTags Array of existing tags to check against
 * @param apiHost The API host URL
 * @returns Array of tag IRIs
 */
export async function ensureTagsExist(
    selectedTagNames: string[],
    existingTags: Tag[],
    apiHost: string
): Promise<string[]> {
    const tagIRIs: string[] = [];

    for (const tagName of selectedTagNames) {
        // Check if tag already exists in existingTags
        const existingTag = existingTags.find(tag => tag.name.toLowerCase() === tagName.toLowerCase());

        if (existingTag) {
            // Use existing tag IRI
            tagIRIs.push(`${apiHost}/api/users/me/tags/${existingTag.slug}`);
        } else {
            // Create new tag
            try {
                const newTag = await createTag(tagName, apiHost);
                // Add to existingTags for future reference
                existingTags.push(newTag);
                tagIRIs.push(`${apiHost}/api/users/me/tags/${newTag.slug}`);
                console.log(`Created new tag: ${tagName} (slug: ${newTag.slug})`);
            } catch (error) {
                console.error(`Error creating tag "${tagName}":`, error);
                throw error;
            }
        }
    }

    return tagIRIs;
}

