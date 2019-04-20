<?php
/**
 * Addition Contents plugin.
 *
 * It allows add add manage addition contents on page.
 *
 * @author  Prakai Nadee <prakai@rmuti.acth> pre-fork: version 1.1.0
 * @edited by Robert Isoski @robertisoski
 * @version 2.4 for WonderCMS
 */

global $Wcms;

if(defined('VERSION')) {
    define('version', VERSION);
    defined('version') OR die('Direct access is not allowed.');

     $Wcms->addListener('js', 'loadAdditionContentsJS');
     $Wcms->addListener('css', 'loadAdditionContentsCSS');
     $Wcms->addListener('page', 'loadAdditionContentsEditableV2');
}

function loadAdditionContentsJS($args) {
    global $Wcms;

    $script = <<<'EOT'

<script src="plugins/addition_contents/js/script.js" type="text/javascript"></script>
EOT;
    if(version<'2.0.0')
        array_push($args[0], $script);
    else
        $args[0].=$script;
    return $args;
}

function loadAdditionContentsCSS($args) {
    global $Wcms;
    $script = <<<'EOT'
<link rel="stylesheet" href="plugins/addition_contents/css/style.css" type="text/css" media="screen" charset="utf-8">
EOT;
    if(version<'2.0.0')
        array_push($args[0], $script);
    else
        $args[0].=$script;
    return $args;
}

function loadAdditionContentsEditableV2($contents) {
    global $Wcms;
    // $db = $Wcms->getDb();
    // print_r($db);

    if ($contents[1]!='content')
        return $contents;

    $content = $contents[0];

    $target = 'pages';
    $page = $Wcms->currentPage;

    if ($Wcms->loggedIn) {
        if (isset($_POST['delac'])) {
            $key = $_POST['delac'];
            if (getContentV2($key)!=false) {
                list($_, $k) = explode('content_', $key);
                $key = 'addition_content_'.$k;
                $Wcms->unset($target, $page, $key);
                $key = 'addition_content_show_'.$k;
                $Wcms->unset($target, $page, $key);
                for ($i=$k+1 ;$i!=0; $i++) {
                    $addition_content = getContentV2('addition_content_'.$i);
                    $addition_content_show = (getContentV2('addition_content_show_'.$i)=='hide') ? 'hide':'show';
                    if (empty($addition_content)) {
                        break;
                    }
                    $key = 'addition_content_'.$i;
                    $Wcms->unset($target, $page, $key);
                    $key = 'addition_content_'.$k;
                    $db->$target->$page->$key = $addition_content;
                    $key = 'addition_content_show_'.$i;
                    $Wcms->unset($target, $page, $key);
                    $key = 'addition_content_show_'.$k;
                    $db->$target->$page->$key = $addition_content_show;
                    $k++;
                }
                $Wcms->save($db);
            }
            die;
        }
        if (isset($_POST['addac'])) {
            $key = $_POST['addac'];
            $content = $_POST['content'];
            list($_, $k) = explode('content_', $key);
            $bf_addition_content = getContentV2('addition_content_'.$k);
            $bf_addition_content_show = (getContentV2('addition_content_show_'.$k)=='hide') ? 'hide':'show';
            if (!empty($bf_addition_content)) {
                for ($i=$k+1 ;$i!=0; $i++) {
                    $addition_content = getContentV2('addition_content_'.$i);
                    $addition_content_show = (getContentV2('addition_content_show_'.$i)=='hide') ? 'hide':'show';
                    $key = 'addition_content_'.$i;
                    $Wcms->set('pages', $Wcms->currentPage, $key, $bf_addition_content);
                    $key = 'addition_content_show_'.$i;
                    $Wcms->set('pages', $Wcms->currentPage, $key, $bf_addition_content_show);
                    if (empty($addition_content)) {
                        break;
                    }
                    $bf_addition_content = $addition_content;
                    $bf_addition_content_show = $addition_content_show;
                    $k++;
                }
            }
            $key = 'addition_content_1';
            $content = $_POST['content'];
            $Wcms->set('pages', $Wcms->currentPage, $key, $content);
            $key = 'addition_content_show_1';
            $content = 'hide';
            $Wcms->set('pages', $Wcms->currentPage, $key, $content);
            die;
        }
        $content = '<div id="contents"  class="addition_contents">'.$content;
        $content.='
        <ul class="nav navbar-left"><li><i value="1" class="btn glyphicon glyphicon-plus-sign content_plus" data-toggle="tooltip" title="Add a content"></i></li></ul><br style="font-size: 1.1em;"/>';
        for ($i=1; $i!=0; $i++) {
            $addition_content = getContentV2('addition_content_'.$i);
            if (empty($addition_content)) {
                break;
            }
            $content.='<p></p>';
            $addition_content_show = getContentV2('addition_content_show_'.$i);
            $addition_content_show = ($addition_content_show) ? $addition_content_show:'show';
            $content.='
            <ul class="nav navbar-left addition_content">
            <li>';
            if ($addition_content_show=='show') {
                $content.='
                <i value="'.$i.'" class="btn glyphicon glyphicon-eye-open toolbar content_hide" data-toggle="tooltip" title="Hide content"></i>';
            } else {
                $content.='
                <i value="'.$i.'" class="btn glyphicon glyphicon-eye-close toolbar content_show" data-toggle="tooltip" title="Show content"></i>';
            }
            $content.='
            <i value="'.$i.'" class="btn glyphicon glyphicon-minus-sign toolbar content_delete" data-toggle="tooltip" title="Remove content"></i>
            </li>
            </ul>';
            $content.= '
            <hr />';
            $content.= $addition_content = $Wcms->editable('addition_content_'.$i, $addition_content, 'pages');
        }
        $content.= '</div>';
    } else {
        $content = '<div id="content">'.$content.'</div>';
        for ($i=1; $i!=0; $i++) {
            $addition_content = getContentV2('addition_content_'.$i);
            if (empty($addition_content)) {
                break;
            }
            $addition_content_show = getContentV2('addition_content_show_'.$i);
            $addition_content_show = ($addition_content_show) ? $addition_content_show:'show';
            if ($addition_content_show=='show')
                $content.='<hr /><div id="addition_content_'.$i.'">'.$addition_content.'</div>';
        }
    }
    $contents[0] = $content;
    return $contents;
}

function getContentV2($key, $page = false) {
    global $Wcms;
    $segments = $Wcms->currentPageExists ? $Wcms->get('pages',$Wcms->currentPage) : ($Wcms->get('config','login') == $Wcms->currentPage ? (object) $Wcms->loginView() : (object) $Wcms->notFoundView());
    return isset($segments->$key) ? $segments->$key : false;
}
