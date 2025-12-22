import type { Bookmark } from '../types';

/**
 * Share a bookmark using the Web Share API or fallback to prompt
 */
export const shareBookmark = (bookmark: Bookmark): void => {
  const shareObject = {
    title: bookmark.title,
    url: bookmark.url,
  };

  const navigatorShare = window.navigator;

  if (navigatorShare.share && navigatorShare.canShare && navigatorShare.canShare(shareObject)) {
    navigatorShare
      .share(shareObject)
      .then(() => {
        console.log('Share succeed!');
      })
      .catch((error) => {
        prompt('You can copy/paste this URL to share this bookmark', shareObject.url);
        console.log('Share failed', error);
      });
  } else {
    prompt('You can copy/paste this URL to share this bookmark', shareObject.url);
  }
};
