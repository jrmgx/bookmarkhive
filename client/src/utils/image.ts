export const getImageUrl = (url: string | null | undefined): string | undefined => {
  if (!url) {
    return undefined;
  }

  if (url.startsWith('http://') || url.startsWith('https://')) {
    return url;
  }

  const baseUrl = import.meta.env.VITE_API_BASE_URL || 'https://bookmarkhive.test';
  const cleanBaseUrl = baseUrl.replace(/\/$/, '');
  const cleanUrl = url.startsWith('/') ? url : `/${url}`;

  return `${cleanBaseUrl}${cleanUrl}`;
};
