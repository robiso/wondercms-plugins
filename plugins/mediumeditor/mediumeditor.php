<?php
/**
 * MediumEditor plugin.
 *
 * It transforms all the editable areas into MediumEditor inline editor.
 *
 * @author  Yassine Addi <yassineaddi.dev@gmail.com>
 * @version 1.0.0
 */
defined('INC_ROOT') OR die('Direct access is not allowed.');

wCMS::addListener('js', 'loadMediumEditorJS');
wCMS::addListener('css', 'loadMediumEditorCSS');

function loadMediumEditorJS($args) {
	$args = [];
	array_push($args, '<script src="//cdn.jsdelivr.net/medium-editor/latest/js/medium-editor.min.js"></script>', '<script>var s=$("span.editable").clone();s.each(function(a,b){var c=s[a].id,d=s[a].outerHTML.replace(/span/,"div");$("span.editable#"+c).replaceWith(d)});var editor = new MediumEditor(".editable").subscribe("blur",function(d,e){$.post("",{fieldname:e.id,content:e.innerHTML})});</script>');
	$script = <<<'EOT'
<script>function nl2br(a){return(a+"").replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g,"$1<br>$2")}function fieldSave(a,b){$("#save").show(),$.post("",{fieldname:a,content:b},function(a){window.location.reload()})}var changing=!1;$(document).ready(function(){$('[data-toggle="tooltip"]').tooltip(),$("span.editText").click(function(){changing||(a=$(this),title=a.attr("title")?title='"'+a.attr("title")+'" ':"",a.hasClass("editable")?null:a.html("<textarea "+title+' id="'+a.attr("id")+'_field" onblur="fieldSave(a.attr(\'id\'),nl2br(this.value));">'+a.html().replace(/<br>/gi,"\n")+"</textarea>"),a.children(":first").focus(),autosize($("textarea")),changing=!0)})});</script>
EOT;
	array_push($args, $script);
	return $args;
}

function loadMediumEditorCSS($args) {
	array_push($args[0], '<link rel="stylesheet" href="//cdn.jsdelivr.net/medium-editor/latest/css/medium-editor.min.css" type="text/css" media="screen" charset="utf-8">', '<link rel="stylesheet" href="//cdn.jsdelivr.net/medium-editor/latest/css/themes/beagle.min.css" type="text/css" media="screen" charset="utf-8">', '<style>.medium-editor-toolbar-form a{border:none;}.medium-editor-toolbar-anchor-preview a{border:none;}</style>');
	return $args;
}
