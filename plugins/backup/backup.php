<?php

/*
 * Backup plugin.
 *
 * It adds a button in the settings area that will create a backup includes everything
 * in the directory WonderCMS is installed in, to help you recover your files in
 * catastrophic situations.
 *
 * @author  Yassine Addi <yassineaddi.dev@gmail.com>
 * @version 1.0.0
 */

defined('INC_ROOT') || die('Direct access is not allowed.');

wCMS::addListener('settings', 'addHtmlBackUpButton');
wCMS::addListener('before', 'backUp');

function addHtmlBackUpButton ($args) {
	$output = $args[0];
	$remove = '<div class="padding20 toggle text-center" data-toggle="collapse" data-target="#settings">Close settings</div></div></div></div></div>';
	$output = substr($output, 0, -(strlen($remove)));
	$output .= '<div class="marginTop20"><form action="' . wCMS::url(wCMS::$currentPage) . '" method="post"><button type="submit" class="btn btn-block btn-warning" name="backup">Backup WonderCMS</button></form></div>' . $remove;
	$args[0] = $output;
	return $args;
}

function backUp ($args) {
	if ( ! wCMS::$loggedIn) return;
	$backups = glob(INC_ROOT . '/backup-*.zip');
	if ( ! empty($backups)) {
		$backups = implode(' and ', array_map('basename', $backups));
		wCMS::alert('danger', '<b>Protect your website:</b> download remove your backups: ' . $backups);
	}
	$backup = 'backup-' . date('Y-m-d-') . substr(md5(microtime()), rand(0, 26), 5) . '.zip';
	if ( ! isset($_POST['backup'])) return;
	if (zipData(INC_ROOT, INC_ROOT . '/' . $backup) !== false) {
		wCMS::alert('success', '<b>Backup completed successfully:</b> <a href=' . wCMS::url($backup) . '>' . wCMS::url($backup) . '</a>');
		wCMS::redirect(wCMS::$currentPage);
	}
}

function zipData($source, $destination) {
	if (extension_loaded('zip')) {
		if (file_exists($source)) {
			$zip = new ZipArchive();
			if ($zip->open($destination, ZIPARCHIVE::CREATE)) {
				$source = realpath($source);
				if (is_dir($source)) {
					$iterator = new RecursiveDirectoryIterator($source);
					$iterator->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);
					$files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
					foreach ($files as $file) {
						$file = realpath($file);
						if (is_dir($file)) {
							$zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
						} else if (is_file($file)) {
							$zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
						}
					}
				} else if (is_file($source)) {
					$zip->addFromString(basename($source), file_get_contents($source));
				}
			}
			return $zip->close();
		}
	}
	return false;
}
