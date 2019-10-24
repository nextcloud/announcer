# Announcer

This script generates the `feed.rss` and `feed.signature` which are published on https://pushfeed.nextcloud.com/.
The feed is then pulled by the [nextcloud_announcements](https://github.com/nextcloud/nextcloud_announcements) app.

## How To


1. Ask @blizzz for the `nextcloud_announcements.key`, or generate a temporary one yourself.
2. Add the new announcement to `feed.txt`

  _Note: Lines starting with `#` are ignored_

3. Run `php generate.php`
4. Upload `feed.rss` and `feed.signature` to the server

