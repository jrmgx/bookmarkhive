/**
 * API interaction functions for communicating with the backend
 * Includes authentication and storage management
 */

import { getBrowserStorage } from './lib/browser';
import { Tag, ApiTag, TagsResponse, AuthRequest, AuthResponse, BookmarkPayload, FileObjectResponse } from './types';

// ============================================================================
// Tag Transformation Constants
// ============================================================================

const META_PREFIX = 'client-o-';
const LAYOUT_DEFAULT = 'default';

/**
 * Transforms an API tag to an internal Tag with extracted metadata
 * @param apiTag The tag from the API
 * @returns Transformed tag with icon, pinned, and layout extracted from meta
 */
function transformTagFromApi(apiTag: ApiTag): Tag {
    const meta = apiTag.meta || {};
    const iconValue = meta[`${META_PREFIX}icon`];
    const icon = iconValue != null && iconValue !== '' && String(iconValue).trim() !== ''
        ? String(iconValue)
        : null;

    return {
        '@iri': apiTag['@iri'],
        name: apiTag.name,
        slug: apiTag.slug,
        isPublic: apiTag.isPublic ?? false,
        pinned: Boolean(meta[`${META_PREFIX}pinned`] ?? false),
        layout: String(meta[`${META_PREFIX}layout`] ?? LAYOUT_DEFAULT),
        icon,
    };
}

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
    headers.set('accept', 'application/json');
    // Use application/json as per OpenAPI spec
    if (!headers.has('Content-Type')) {
        headers.set('Content-Type', 'application/json');
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
 * @returns Promise that resolves to the file object response containing @iri
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

    const response = await fetch(`${apiHost}/api/users/me/files`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'accept': 'application/json'
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
            'accept': 'application/json',
            'Content-Type': 'application/json'
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
            'accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ name: tagName })
    });

    if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`Failed to create tag: ${response.status} ${errorText}`);
    }

    const apiTag: ApiTag = await response.json();
    return transformTagFromApi(apiTag);
}

/**
 * Fetches all user tags from the API
 * @returns Array of all user tags
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
            'accept': 'application/json'
        }
    });

    if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`Failed to fetch tags: ${response.status} ${errorText}`);
    }

    const tagsData: TagsResponse = await response.json();
    // Return all tags from the collection (API should return all tags in one response)
    return (tagsData.collection || []).map(transformTagFromApi);
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
            // Use existing tag IRI from @iri property or construct it
            const tagIRI = existingTag['@iri'] || `${apiHost}/api/users/me/tags/${existingTag.slug}`;
            tagIRIs.push(tagIRI);
        } else {
            // Create new tag
            try {
                const newTag = await createTag(tagName, apiHost);
                // Add to existingTags for future reference
                existingTags.push(newTag);
                // Use @iri property or construct it
                const tagIRI = newTag['@iri'] || `${apiHost}/api/users/me/tags/${newTag.slug}`;
                tagIRIs.push(tagIRI);
                console.log(`Created new tag: ${tagName} (slug: ${newTag.slug})`);
            } catch (error) {
                console.error(`Error creating tag "${tagName}":`, error);
                throw error;
            }
        }
    }

    return tagIRIs;
}

