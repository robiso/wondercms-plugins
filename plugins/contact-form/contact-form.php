<?php
/**
 * Contact form plugin.
 *
 * It allows to add and manage additional contents on page.
 *
 * @author Thijs Ferket www.ferket.net
 * @forked and adapted by Herman Adema   
 * @forked by Jeremy Czajkowski
 * @version 3.0.0
 */

global $Wcms;

if (defined('VERSION')) {
    define('version', VERSION);
    defined('version') OR die('Direct access is not allowed.');
}

$configuration = parse_ini_file('config');

define('CONTACT_FORM_PAGE', $configuration ['page']);
define('CONTACT_FORM_EMAIL', $configuration ['emailAddress']);
define('CONTACT_FORM_LANG', $configuration ['language']);

$Wcms->addListener('css', 'contactfCSS');

function contactfCSS($args) {
    global $Wcms;

    $script = '<link rel="stylesheet" href="'.$Wcms->url("plugins/contact-form/css/style.css").'" type="text/css">';
    $args[0].=$script;
    return $args;
}

function contactfCONTENT() {
    global $Wcms;

    $emailadr = CONTACT_FORM_EMAIL;

    // Internationalization
    $i18n =  parse_ini_file('languages/'.CONTACT_FORM_LANG.'.ini');

    // Config
    $cfg['email'] = $emailadr;         // Webmaster email
    $cfg['text'] = TRUE;               // If an error occurs, make text red   ( TRUE is on, FALSE is off )
    $cfg['input'] = TRUE;              // If an error occurs, make border red ( TRUE is on, FALSE is off )
    $cfg['HTML'] = FALSE;              // HTML email ( TRUE is on, FALSE is off )

    // Email validator
    function checkmail($email) {
        if(preg_match("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$^", $email)) {
            return TRUE;
        }
        return FALSE;
    }

    $formulier = TRUE;
    $finall_content = '';

    if(isset($_POST['submitForm']) && ($_SERVER['REQUEST_METHOD'] == "POST")) {
        $aFout = array();

        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $subject = trim($_POST['subject']);
        $message = trim($_POST['message']);

        if(empty($name) || (strlen($name) < 3) || preg_match("/([<>])/i", $name) ) {
            $aFout[] = $i18n['name_empty'];
            unset($name);
            $fout['text']['name'] = TRUE;
            $fout['input']['name'] = TRUE;
        }
        if(empty($email)) {
            $aFout[] = $i18n['email_empty'];
            unset($email);
            $fout['text']['email'] = TRUE;
            $fout['input']['email'] = TRUE;
        } elseif(checkmail($email) == 0) {
            $aFout[] = $i18n['email_invalid'];
            unset($email);
            $fout['text']['email'] = TRUE;
            $fout['input']['email'] = TRUE;
        }
        if(empty($subject)) {
            $aFout[] = $i18n['subject_empty'];
            unset($subject);
            $fout['text']['subject'] = TRUE;
            $fout['input']['subject'] = TRUE;
        }
        if(empty($message)) {
            $aFout[] = $i18n['message_empty'];
            unset($message);
            $fout['text']['message'] = TRUE;
            $fout['input']['message'] = TRUE;
        }
        if(!$cfg['text']) {
            unset($fout['text']);
        }
        if(!$cfg['input']) {
            unset($fout['input']);
        }
        if(empty( $aFout )) {
            $formulier = FALSE;

            if($cfg['HTML']) {
                // Headers
                $headers = "From: ".$cfg['email']."\r\n";
                $headers .= "Reply-To: \"".$name."\" <".$email.">\n";
                $headers .= "Return-Path: Mail-Error <".$cfg['email'].">\n";
                $headers .= "MIME-Version: 1.0\n";
                $headers .= "Content-Transfer-Encoding: 8bit\n";
                $headers .= "Content-type: text/html; charset=utf-8\n";

                $message = '
                <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
                <html>
                    <head>
                    </head>

                    <body>
                       <br />
                        <b>Name:</b> '.$name.'<br /><br />
                        <b>Email:</b> '.$email.'<br /><br />
                        <br />
                        <b>Message:</b><br /><br />
                        '.$message.'
                        <br />
                        <br />
                        <br />
                        --------------------------------------------------------------------------<br />
                        <b>IP:</b> '.$_SERVER['REMOTE_ADDR'].'<br />
                    </body>
                </html>';
            } else {
                $message_wrap = wordwrap ($message, 40, "\n", 1);
                // Headers
                $headers = "From: \"".$Wcms->get('config','siteTitle')."\" <$cfg[email]>\n";
                $headers .= "MIME-Version: 1.0\n";
                $headers .= "Content-type: text/plain; charset='utf-8'\n";

                // message
                $message = "Name: ".$name."        \n\n";
                $message .= "Email: ".$email."     \n\n";
                $message .= "Message:\n".$message_wrap."     \n";
                $message .= "               \n ";
                $message .= "------------------------------------------------------- \n ";
                $message .= "IP: ".$_SERVER['REMOTE_ADDR']."                    \n ";
            }

            if(mail($cfg['email'], $i18n['subject_prefix']." ".$subject, $message, $headers)) {
                $headers = "From: ".$cfg['email']."\r\n";
                $headers .= "Reply-To: \"".$cfg['email']."\" <".$cfg['email'].">\n";
                $headers .= "Return-Path: Mail-Error <".$email.">\n";
                $headers .= "MIME-Version: 1.0\n";
                $headers .= "Content-Transfer-Encoding: 8bit\n";
                $headers .= "Content-type: text/html; charset=utf-8\n";

                $sent = true;

                unset($name, $email, $subject, $message);
            }
            else {
                $sent = false;
            }                

            if ($sent) {
               echo $i18n['result_sent'];
            } else {
               echo $i18n['result_failed'];
            }
        }
    }

    if($formulier) {
        $finall_content .= "<div id='message'><p class='message'>" . $_SESSION['SubmitMessage'] . "</p></div>";
        unset($_SESSION['SubmitMessage']);

        if($aFout) {
            $finall_content .=  '<div id="errors">' . implode('<br>' ,$aFout) . '</div>';
        }
        $finall_content .=  "<div id='containerform'>";

        $finall_content .=  "<form method='post' action=''>";
        $finall_content .=  "<p>";
        $finall_content .=  "<div class='form-group'><input type='text' placeholder='$i18n[name]' id='name' name='name' maxlength='30'";
        if(isset($fout['input']['name'])) { $finall_content .=  "class='fout'"; } $finall_content .=  "value='";
        if (!empty($name)) { $finall_content .=  stripslashes($name); } $finall_content .=  "' /></div>";

        $finall_content .=  "<div class='form-group'><input type='text' placeholder='$i18n[email]' id='email' name='email' maxlength='255'";
        if(isset($fout['input']['email'])) { $finall_content .=  "class='fout'"; } $finall_content .=  "value='";
        if (!empty($email)) { $finall_content .=  stripslashes($email); } $finall_content .=  "' /></div>";

        $finall_content .=  "<div class='form-group'><input type='text' placeholder='$i18n[subject]' id='subject' name='subject' maxlength='40'";
        if(isset($fout['input']['subject'])) { $finall_content .=  "class='fout'"; } $finall_content .=  "value='";
        if (!empty($subject)) { $finall_content .=  stripslashes($subject); } $finall_content .=  "' /></div>";

        $finall_content .=  "<div class='form-group'><textarea placeholder='$i18n[message]' id='message' name='message'";
        if(isset($fout['input']['message'])) { $finall_content .=  "class='fout'"; } $finall_content .=  " cols='31' rows='10'>";
        if (!empty($message)) { $finall_content .=  stripslashes($message); } $finall_content .=  "</textarea></div>";

        $finall_content .=  "<input type='submit' id='submitForm' class='btn btn-primary btn-block' name='submitForm' value='$i18n[submit]' />";
        $finall_content .=  "</p>";
        $finall_content .=  "</form>";
        $finall_content .=  "</div>";
    }

    return $finall_content;
}

function contact_form() {
    global $Wcms;

    $result = '';
    if ($Wcms->currentPage == CONTACT_FORM_PAGE) {
        $result .=  '<div class="container marginTop20"><div class="col-xs-12 col-md-6 col-md-offset-3">';
        $result .=  '<div id="contactform" class="grayFont">';
        $result .= contactfCONTENT();
        $result .=  '</div></div></div>';
    }
    return $result;
}

?>