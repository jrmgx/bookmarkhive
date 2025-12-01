import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
    share({params: {bookmark: bookmark}}) {
        const navigatorShare = window.navigator;
        const shareObject = {
            title: bookmark.title,
            url: bookmark.url,
        };
        if (navigatorShare.share && navigatorShare.canShare && navigatorShare.canShare(shareObject)) {
            navigatorShare.share(shareObject)
                .then(() => { console.log('Share succeed!') })
                .catch((error) => {
                    prompt('You can copy/past this URL to share this bookmark', shareObject.url);
                    console.log('Share failed', error)
                })
        } else {
            prompt('You can copy/past this URL to share this bookmark', shareObject.url);
        }
    }
}
