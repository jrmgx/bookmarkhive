/**
 * Type definitions and interfaces for the extension
 */

// Tags
export interface Tag {
    '@id': string;
    '@type': string;
    id: string;
    name: string;
    slug: string;
    owner: string;
    isPublic: boolean;
}

export interface TagsResponse {
    '@context': string;
    '@id': string;
    '@type': string;
    totalItems: number;
    member: Tag[];
}

// ============================================================================
// Message Interfaces (for runtime communication)
// ============================================================================

export interface InlineCSSMessage {
    action: 'inlineCSS';
}

export interface CompressHTMLMessage {
    action: 'compressHTML';
    html: string;
}

export interface ArchivePageMessage {
    action: 'archivePage';
}

export type MessageRequest = CompressHTMLMessage | InlineCSSMessage | ArchivePageMessage;

export interface ArchivePageResponse extends MessageResponse {
    fileObjectId?: string;
}

export interface MessageResponse {
    success: boolean;
    error?: string;
}

// ============================================================================
// Page Data Interfaces
// ============================================================================

export interface PageData {
    title: string;
    url: string;
    description: string | null;
    image: string | null;
    favicon: string | null;
}

// ============================================================================
// API Request/Response Interfaces
// ============================================================================

export interface AuthRequest {
    email: string;
    password: string;
}

export interface AuthResponse {
    token: string;
}

export interface BookmarkPayload {
    title: string;
    url: string;
    mainImage: string;
    isPublic: boolean;
    tags?: string[]; // Array of tag IRIs
    archive?: string; // File object IRI for archived page
}

export interface FileObjectResponse {
    '@id': string;
    [key: string]: unknown;
}

// ============================================================================
// Internal Processing Interfaces
// ============================================================================

export interface ImportMatch {
    fullMatch: string;
    url: string;
}

export interface BackgroundImageMatch {
    url: string;
    fullMatch: string;
}

