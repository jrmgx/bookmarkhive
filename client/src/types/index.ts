export interface FileObject {
  contentUrl: string;
  size: number;
  mime: string;
}

export interface User {
  username: string;
  isPublic: boolean;
}

export interface Tag {
  slug: string;
  name: string;
  isPublic: boolean;
  pinned: boolean;
  layout: string;
  icon: string | null;
}

export interface Bookmark {
  id: string;
  createdAt: string; // ISO date string
  title: string;
  url: string;
  tags: Tag[];
  owner: User;
  domain: string;
  mainImage: FileObject | null;
  pdf: FileObject | null;
  archive: FileObject | null;
  isPublic: boolean;
}

export const LAYOUT_DEFAULT = 'default';
export const LAYOUT_EMBEDDED = 'embedded';
export const LAYOUT_IMAGE = 'image';

