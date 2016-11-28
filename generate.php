<?php
/**
 * @copyright Copyright (c) 2016 Joas Schilling <coding@schilljs.com>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

if (php_sapi_name() !== 'cli') {
	echo 'Script can only run from command line' . "\n";
	exit(1);
}

date_default_timezone_set('Europe/Berlin');

$content = file_get_contents(__DIR__ . '/feed.txt');
$feeds = explode("\n", $content);
$items = '';

$maxDate = new \DateTime();
$maxDate->setTimestamp(0);

foreach ($feeds as $feed) {
	$feed = trim($feed);
	if ($feed === '' || $feed[0] === '#') {
		continue;
	}
	list ($id, $date, $url, $title) = explode(' - ', $feed, 4);

	$dateTime = new \DateTime();
	call_user_func_array([$dateTime, 'setDate'], explode('-', $date));
	$dateTime->setTime(0, 0, 0);

	if ($dateTime->getTimestamp() > $maxDate->getTimestamp()) {
		$maxDate = $dateTime;
	}

	$items .= '		<item>
			<guid isPermaLink="false">' . $id . '</guid>
			<title>' . $title . '</title>
			<link>' . $url . '</link>
			<pubDate>' . $dateTime->format('r') . '</pubDate>
		</item>
';
}

$beginning = '<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<title>Nextcloud organisation</title>
		<language>en</language>
		<link>https://pushfeed.nextcloud.com/feed.rss</link>
		<description>Feed for announcements from Nextcloud</description>
		<pubDate>' . $maxDate->format('r') . '</pubDate>
		<lastBuildDate>' . $maxDate->format('r') . '</lastBuildDate>
		<atom:link href="https://pushfeed.nextcloud.com/feed.rss" rel="self" type="application/rss+xml" />
';

$content = $beginning . $items . '	</channel>
</rss>
';

file_put_contents(__DIR__ . '/feed.rss', $content);
echo 'Successfully generated feed.rss' . "\n";

$key = file_get_contents(__DIR__ . '/nextcloud_announcements.key');
$signature = '';
if (!openssl_sign($content, $signature, $key, OPENSSL_ALGO_SHA512)) {
	echo 'Error signing the feed' . "\n";
	exit(1);
}

$signature = base64_encode($signature);
$signature = implode("\n", str_split($signature, 64));

file_put_contents(__DIR__ . '/feed.signature', $signature);
echo 'Successfully generated feed.signature' . "\n";
