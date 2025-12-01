# Client

## Meta on tags

This client defines a few meta on tags for its own usage:

 - pinned: true/false => will show on top in the sidebar
 - layout: "default"/"embedded"/"image" => will load a specific layout when displaying that tag

## Icons

The client use `symfony/ux-icons` so to integrate a new icon:
 - Find your icon on [ux.symfony.com/icons](https://ux.symfony.com/icons)
 - Use the twig syntax `<twig:ux:icon name="ph:share-fat" />`
 - Run the php command to download them for good `php bin/console ux:icons:lock -v`
