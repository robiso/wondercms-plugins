<?php
defined('INC_ROOT') OR die('Direct access is now allowed');

wCMS::addListener('js', 'loadTrumbowygJS');
wCMS::addListener('css', 'loadTrumbowygCSS');

function loadTrumbowygJS($args) {
	$arg = [];
	array_push($arg, '<script src="'.wCMS::url('plugins/trumbowyg/trumbowyg/trumbowyg.min.js').'"></script>', '<script>$(".editable").trumbowyg({}).on("tbwblur",function(){$.post("",{fieldname:$(this).attr("id"),content:$(this).trumbowyg("html")},function(a){})});</script>');
	$script = <<<'EOT'
<script>function nl2br(a){return(a+"").replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g,"$1<br>$2")}function fieldSave(a,b){$("#save").show(),$.post("",{fieldname:a,content:b},function(a){window.location.reload()})}var changing=!1;$(document).ready(function(){$('[data-toggle="tooltip"]').tooltip(),$("span.editText").click(function(){changing||(a=$(this),title=a.attr("title")?title='"'+a.attr("title")+'" ':"",a.hasClass("editable")?null:a.html("<textarea "+title+' id="'+a.attr("id")+'_field" onblur="fieldSave(a.attr(\'id\'),nl2br(this.value));">'+a.html().replace(/<br>/gi,"\n")+"</textarea>"),a.children(":first").focus(),autosize($("textarea")),changing=!0)})});</script>
EOT;
	array_push($arg, $script);
	return $arg;
}

function loadTrumbowygCSS($args) {
	array_push($args[0], '<link rel="stylesheet" href="'.wCMS::url('plugins/trumbowyg/trumbowyg/ui/trumbowyg.min.css').'">');
	return $args[0];
}