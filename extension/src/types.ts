/**
 * Type definitions and interfaces for the extension
 */

// Tags
export interface ApiTag {
    '@iri': string;
    name: string;
    slug: string;
    isPublic?: boolean;
    meta?: Record<string, string>;
}

export interface Tag {
    '@iri': string;
    name: string;
    slug: string;
    isPublic: boolean;
    pinned: boolean;
    layout: string;
    icon: string | null;
}

export interface TagsResponse {
    collection: ApiTag[];
    prevPage: boolean;
    nextPage: boolean;
    total: number;
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
    mainImage?: string | null; // IRI reference to FileObject
    isPublic?: boolean;
    tags?: string[]; // Array of tag IRIs (e.g., "/api/users/me/tags/my-tag")
    archive?: string | null; // IRI reference to FileObject
}

export interface FileObjectResponse {
    '@iri': string;
    id: string;
    contentUrl?: string | null;
    size: number;
    mime: string;
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

