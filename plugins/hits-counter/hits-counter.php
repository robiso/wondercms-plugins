<?php
/**
 * Hits counter.
 *
 * Simple hits/visits counter. Hits are displayed in the footer once admin login,
 * hits will not be incremented if admin is logged in.
 *
 * @author  Yassine Addi <yassineaddi.dev@gmail.com>
 * @version 1.0.0
 */

defined('INC_ROOT') || die('Direct access is not allowed.');

wCMS::addListener('after', 'incrementHits');
wCMS::addListener('footer', 'displayHits');

function incrementHits () {
	if (wCMS::$loggedIn) return;
	$hits = file_exists(__DIR__ . '/hits.txt') ? (int) file_get_contents(__DIR__ . '/hits.txt') : 0;
	$hits++;
	file_put_contents(__DIR__ . '/hits.txt', $hits);
}

function displayHits ($args) {
	if ( ! wCMS::$loggedIn) return $args;
	$hits = file_exists(__DIR__ . '/hits.txt') ? (int) file_get_contents(__DIR__ . '/hits.txt') : 0;
	$args[0] .= ' &bull; Hits: ' . $hits;
	return $args;
}
