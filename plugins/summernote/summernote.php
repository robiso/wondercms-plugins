<?php
/**
 * SummerNote plugin for WonderCMS.
 *
 * It transforms all the editable areas into SummerNote inline editor.
 *
 * @author Prakai Nadee <prakai@rmuti.acth>
 * @edited by Robert Isoski @robertisoski
 * @version 2.4 for WonderCMS
 */

if(defined('VERSION'))
	define('version', VERSION);
	defined('version') OR die('Direct access is not allowed.');

	$default_contents_path = 'files';

	wCMS::addListener('js', 'loadSummerNoteJS');
	wCMS::addListener('css', 'loadSummerNoteCSS');
	wCMS::addListener('editable', 'initialSummerNoteVariables');

function initialSummerNoteVariables($contents) {
	$content = $contents[0];
	$subside = $contents[1];

	global $default_contents_path;

	$contents_path = wCMS::getConfig('contents_path');
	if ( ! $contents_path) {
		wCMS::setConfig('contents_path', $default_contents_path);
		$contents_path = $default_contents_path;
	}
	$contents_path_n = trim($contents_path, "/");
	if ($contents_path != $contents_path_n) {
		$contents_path = $contents_path_n;
		wCMS::setConfig('contents_path', $contents_path);
	}
	$_SESSION['contents_path'] = $contents_path;

	return array($content, $subside);
}

function loadSummerNoteJS($args) {
	$script = <<<'EOT'

<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.8/dist/summernote.min.js"></script>
<script src="plugins/summernote/js/files.js"></script>
<script>
$(function() {
	var editElements = {};
	$('.editable').summernote({
		airMode: false,
		toolbar: [
			// [groupName, [list of button]]
			['style', ['style']],
			['font', ['bold', 'italic', 'underline', 'clear']],
			['font', ['fontsize', 'color']],
			['para', ['paragraph']],
			['insert', ['link','image', 'doc', 'video']], // image and doc are customized buttons
			['table', ['table']],
			['misc', ['codeview']],
		],
		placeholder: 'Click here to write.',
		callbacks: {
			onChange: function(contents, $editable) {
				editElements[$(this).attr('id')] = contents;
			},
			onBlur: function() {
				if (editElements[$(this).attr('id')]!=undefined) {
					var id = $(this).attr('id');
					var content = editElements[$(this).attr('id')];
					var target = ($(this).attr('data-target')!=undefined) ? $(this).attr('data-target'):'pages';
					editElements[$(this).attr('id')] = undefined;
					$.post("",{
						fieldname: id,
						content: content,
						target: target,
						token: token,
					});
				}
			}
		},
	});
});
</script>
EOT;

	$args[0].=$script;
	return $args;
}
 
function loadSummerNoteCSS($args) {
	$script = <<<'EOT'

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.8/dist/summernote.css" type="text/css" media="screen" charset="utf-8">
<link rel="stylesheet" href="plugins/summernote/summernote/summernote.css" type="text/css" media="screen" charset="utf-8">
<link rel="stylesheet" href="plugins/summernote/css/font-awesome.min.css" type="text/css" media="screen" charset="utf-8">
<link rel="stylesheet" href="plugins/summernote/css/style.css" type="text/css" media="screen" charset="utf-8">
EOT;

	$args[0].=$script;
	return $args;
}

function displaySummerNoteSettings ($args) {
	if ( ! wCMS::$loggedIn) return $args;
	$settings = '

<label for="contents_path" data-toggle="tooltip" data-placement="right" title="Path of uploaded files, reference to root path of CMS, eg: files">SummerNote Contents path</label>
<span id="contents_path" class="change editText">'.wCMS::getConfig('contents_path').'</span>';

	$args[0].=$script;
	return $args;
}
