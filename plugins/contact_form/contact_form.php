<?php
if(defined('VERSION'))
	define('version', VERSION);
	defined('version') OR die('Direct access is not allowed.');


wCMS::addListener('css', 'contactfCSS');

function contactfCSS($args) {
	$script = '<link rel="stylesheet" href="'.wCMS::url("plugins/contact_form/css/style.css").'" type="text/css">';

	$args[0].=$script;
	return $args;
}

function contact_form() {
	
					#################################################
					#-----------------------------------------------#
					#  Written By : Thijs Ferket               		#
					#  Website    : www.ferket.net             		#
					#-----------------------------------------------#
					#################################################
					#  Edited and adapted by Herman Adema		#
					#################################################
					#################################################
					#  Edited by Robert Isoski for WonderCMS	#
					#################################################
					
					global $contact_form_email;
					$emailadr = $contact_form_email;
					#preg_match("/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i", $GLOBALS['Infoooter1EditableArea'], $matches);#
					#$emailadr = print_r($matches[0], true);#

					ini_set('display_errors', 1);
					error_reporting(E_ALL);


					// Config
					$cfg['email'] = $emailadr;         // Webmaster email
					$cfg['text'] = TRUE;               // If an error occurs, make text red   ( TRUE is on, FALSE is off )
					$cfg['input'] = TRUE;              // If an error occurs, make border red ( TRUE is on, FALSE is off )
					$cfg['HTML'] = TRUE;               // Een HTML email ( TRUE is on, FALSE is off )
					$cfg['CAPTCHA'] = TRUE;            // CAPTCHA ( TRUE is on, FALSE is off )


					// Don't change anything below here
					// E-mail Checker / Validator
					function checkmail($email)
					{
						if(preg_match("/(^[0-9a-z]([-_.]?[0-9a-z])*@[0-9a-z]([-.]?[0-9a-z])*\\.[a-z]{2,4}$)/i", $email))
						{
							return TRUE;
						}
						return FALSE;
					}

					$formulier = TRUE;
		
						if(isset($_POST['submitForm']) && ($_SERVER['REQUEST_METHOD'] == "POST"))
						{
							$aFout = array();
		
							$name = trim($_POST['name']);
							$email = trim($_POST['email']);
							$subject = trim($_POST['subject']);
							$message = trim($_POST['message']);
		
							if($cfg['CAPTCHA'])
							{
								$code = $_POST['code'];
							}
				
							if(empty($name) || (strlen($name) < 3) || preg_match("/([<>])/i", $name) )
							{
								$aFout[] = "Name field is empty.";
								unset($name);
								$fout['text']['name'] = TRUE;
								$fout['input']['name'] = TRUE;
							}
							if(empty($email))
							{
								$aFout[] = "Email field is empty.";
								unset($email);
								$fout['text']['email'] = TRUE;
								$fout['input']['email'] = TRUE;
							}
							elseif(checkmail($email) == 0)
							{
								$aFout[] = "Entered email address is not valid.";
								unset($email);
								$fout['text']['email'] = TRUE;
								$fout['input']['email'] = TRUE;
							}
							if(empty($subject))
							{
								$aFout[] = "Subject is empty.";
								unset($subject);
								$fout['text']['subject'] = TRUE;
								$fout['input']['subject'] = TRUE;
							}
							if(empty($message))
							{
								$aFout[] = "Message is empty.";
								unset($message);
								$fout['text']['message'] = TRUE;
								$fout['input']['message'] = TRUE;
							}
							if($cfg['CAPTCHA'])
							{
								if(strtoupper($code) != $_SESSION['captcha_code'])
								{
									$aFout[] = "Captcha is incorrect.";
									$fout['text']['code'] = TRUE;
									$fout['input']['code'] = TRUE;
								}
							}
							if(!$cfg['text'])
							{
								unset($fout['text']);
							}
							if(!$cfg['input'])
							{
								unset($fout['input']);
							}
							if(empty( $aFout ))
							{
								$formulier = FALSE;
			
								
								if($cfg['HTML'])
								{
									// Headers
									$headers = "From: ".$cfg['email']."\r\n"; 
									$headers .= "Reply-To: \"".$name."\" <".$email.">\n";
									$headers .= "Return-Path: Mail-Error <".$cfg['email'].">\n";
									$headers .= "MIME-Version: 1.0\n";
									$headers .= "Content-Transfer-Encoding: 8bit\n";
									$headers .= "Content-type: text/html; charset=iso-8859-1\n";
				
				
									$message = '
									<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
									<html>
									<head>
									</head>
								
									<body>
								   <br />
									<b>Name:</b> '.$name.'<br />
									<b>Email:</b> '.$email.'<br />
									<br />
									<b>Message:</b><br />
									'.$message.'
									<br />
									<br />
									<br />
									--------------------------------------------------------------------------<br />
									<b>IP:</b> '.$_SERVER['REMOTE_ADDR'].'<br />
									</body>
									</html>';
								}
								else 
								{
									$message_wrap = wordwrap ($message, 40, "\n", 1);
									// Headers
									$headers = "From: \"Contact Formulier\" <".$cfg['email'].">\n"; 
									$headers .= "MIME-Version: 1.0\n";
									$headers .= "Content-type: text/plain; charset='iso-8859-1'\n"; 
			
									// message
									$message = "Name: ".$name."        \n";
									$message .= "Email: ".$email."     \n";
									$message .= "Message:\n".$message_wrap."     \n ";
									$message .= "               \n ";
									$message .= "------------------------------------------------------- \n ";
									$message .= "IP: ".$_SERVER['REMOTE_ADDR']."                    \n ";
									$message .= "Host: ".gethostbyaddr($_SERVER['REMOTE_ADDR'])."                \n ";
								
								}
		
								if(mail($cfg['email'], "[Contact from your website] ".$subject, $message, $headers)) 
								{
									$headers = "From: ".$cfg['email']."\r\n"; 
									$headers .= "Reply-To: \"".$cfg['email']."\" <".$cfg['email'].">\n";
									$headers .= "Return-Path: Mail-Error <".$email.">\n";
									$headers .= "MIME-Version: 1.0\n";
									$headers .= "Content-Transfer-Encoding: 8bit\n";
									$headers .= "Content-type: text/html; charset=iso-8859-1\n";
					
									mail($email, "[Contactformulier] ".$subject, $message, $headers);
				
				
									unset($name, $email, $subject, $message);
		
									echo "
									<p class='message'>
									Thank you! Your message has been sent successfully. We will respond as quickly as possible.<br />
									</p>
									";    
								}
								else
								{
									echo "<p class='message'>An error occurred while sending the email.</p>";
								}
							}
						}
						if($formulier)
						{
						echo "<div id='containerform'>";
						if(isset($errors)) {
							echo $errors;
						}

							echo "<form method='post' action='contact'>";
							echo "<p>";
							echo "<input type='text' placeholder='Name' id='name' name='name' maxlength='30'";
							if(isset($fout['input']['name'])) { echo "class='fout'"; } echo "value='";
							if (!empty($name)) { echo stripslashes($name); } echo "' /><br />";
		
							echo "<input type='text' placeholder='Email' id='email' name='email' maxlength='255'";
							if(isset($fout['input']['email'])) { echo "class='fout'"; } echo "value='";
							if (!empty($email)) { echo stripslashes($email); } echo "' /><br />";
		
							echo "<input type='text' placeholder='Subject' id='subject' name='subject' maxlength='40'";
							if(isset($fout['input']['subject'])) { echo "class='fout'"; } echo "value='";
							if (!empty($subject)) { echo stripslashes($subject); } echo "' /><br />";
		
							echo "<textarea placeholder='Message' id='message' name='message'";
							if(isset($fout['input']['message'])) { echo "class='fout'"; } echo " cols='31' rows='10'>";
							if (!empty($message)) { echo stripslashes($message); } echo "</textarea><br />";

							if($cfg['CAPTCHA'])
							{
							echo "<img src=\"" . wCMS::url('plugins/contact_form/captcha/captcha.php') . "\" alt='' /><br />";
		
							echo "<input type='text' placeholder='Captcha' id='code' name='code' maxlength='4' size='4'";
								if(isset($fout['input']['code'])) { echo "class='captcha fout'"; } echo " /><br />";
							}
		
							echo "<input type='submit' id='submitForm' name='submitForm' value='Submit' />";
							echo "</p>";
							echo "</form>";
							echo "</div>";
						}
}
