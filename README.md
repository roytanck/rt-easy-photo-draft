# rt-easy-photo-draft
Automatically create draft posts for photos uploaded to the WordPress media library

## Installation
* Copy the 'rt-easy-photo-draft' folder to the wp-content/plugins folder in your WordPress installation.
* Activate the plugin from the Plugins screen in wp-admin.

## Usage
* Upload a photo (any image file with EXIF data that includes the DateTimeOriginal property).
* On the posts screen, you should now see a new draft post with the photo set as 'featured image'.
* Edit the post and publish.
* The post's creation date is updated to be the photo's capture time.
* Works with multiple uploads too.

