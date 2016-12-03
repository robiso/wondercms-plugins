<?php
/**
 * Upload plugin.
 *
 * Simple file uploader that adds an upload form in the settings area.
 *
 * @author  Yassine Addi <yassineaddi.dev@gmail.com>
 * @version 1.0.0
 */

defined('INC_ROOT') || die('Direct access is not allowed.');

wCMS::addListener('settings', 'addHtmlUploadForm');
wCMS::addListener('before', 'uploadFile');

function addHtmlUploadForm ($args) {
	$output = $args[0];
	$remove = '<div class="padding20 toggle text-center" data-toggle="collapse" data-target="#settings">Close settings</div></div></div></div></div>';
	$output = substr($output, 0, -(strlen($remove)));
	$output .= '<div class=" marginTop20 change"><b style="font-size: 22px;" class="glyphicon glyphicon-info-sign" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="Upload files from your device to your web host."></b><form action="' . wCMS::url(wCMS::$currentPage) . '" method="post" enctype="multipart/form-data"><div class="form-group"><input type="file" name="upfile" class="form-control"></div><button type="submit" class="btn btn-info">Upload</button></form></div>' . $remove;
	$args[0] = $output;
	return $args;
}

function uploadFile ($args) {
	if ( ! isset($_FILES['upfile'])) return;

	$allowed = [
		'jpg' => 'image/jpeg',
		'png' => 'image/png',
		'gif' => 'image/gif',
	];

	try {
		if (
			! isset($_FILES['upfile']['error']) ||
			is_array($_FILES['upfile']['error'])
		) {
			wCMS::alert('danger', '<strong>Upload</strong>: invalid parameters.');
			wCMS::redirect(wCMS::$currentPage);
		}

		switch ($_FILES['upfile']['error']) {
			case UPLOAD_ERR_OK:
				break;
			case UPLOAD_ERR_NO_FILE:
				wCMS::alert('danger', '<strong>Upload</strong>: no file sent.');
				wCMS::redirect(wCMS::$currentPage);
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				wCMS::alert('danger', '<strong>Upload</strong>: exceeded filesize limit.');
				wCMS::redirect(wCMS::$currentPage);
			default:
				wCMS::alert('danger', '<strong>Upload</strong>: unknown error.');
				wCMS::redirect(wCMS::$currentPage);
		}

		$mimeType = '';
		if (class_exists('finfo')) {
			$finfo = new finfo(FILEINFO_MIME_TYPE);
			$mimeType = $finfo->file($_FILES['upfile']['tmp_name']);
		} else if (function_exists('mime_content_type')) {
			$mimeType = mime_content_type($_FILES['upfile']['tmp_name']);
		} else {
			$ext = strtolower(array_pop(explode('.', $_FILES['upfile']['name'])));
			if (array_key_exists($ext, $allowed)) {
				$mimeType = $allowed[$ext];
			}
		}

		if (false === $ext = array_search(
			$mimeType,
			$allowed,
			true
		)) {
			wCMS::alert('danger', '<strong>Upload</strong>: invalid file format.');
			wCMS::redirect(wCMS::$currentPage);
		}

		if ( ! is_dir(INC_ROOT . '/uploads')) {
			mkdir(INC_ROOT . '/uploads');
		}

		if ( ! move_uploaded_file(
			$_FILES['upfile']['tmp_name'],
			sprintf(INC_ROOT . '/uploads/%s',
				$_FILES['upfile']['name']
			)
		)) {
			wCMS::alert('danger', '<strong>Upload</strong>: failed to move uploaded file.');
			wCMS::redirect(wCMS::$currentPage);
		}

		wCMS::alert('success', '<strong>Upload</strong>: file uploaded successfully.');
		wCMS::redirect(wCMS::$currentPage);
	} catch (RuntimeException $e) {
		wCMS::alert('danger', '<strong>Upload</strong>: ' . $e->getMessage());
		wCMS::redirect(wCMS::$currentPage);
	}
}
