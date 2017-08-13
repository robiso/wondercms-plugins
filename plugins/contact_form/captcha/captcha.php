<?php
############################################
#  Filename   : captcha.php                #
#------------------------------------------#
#  Written By : Thijs Ferket               #
#  Website    : www.ferket.net             #
#------------------------------------------#
############################################

session_start();

// Captcha keygenerator
function keygen($aantal)
{
	$tekens = array_merge(range('A', 'F'), array('H', 'J', 'K', 'M', 'N'), range('P', 'Z'));
	$randomstring = "";
	for($i=1; $i <= $aantal; $i++)
	{
		shuffle($tekens);
		$randomstring .= $tekens[0];
	}
	return $randomstring;
}
	
$random_code = keygen(4);
$_SESSION['captcha_code'] = $random_code;

$breedte = 128;
$hoogte = 45;

header("content-type: image/png");

$afbeelding = imagecreate($breedte, $hoogte);
$achtergrond = imagecolorallocate($afbeelding, 255, 255, 255);
$font = "fonts/arial.ttf";

$kleur = imagecolorallocate($afbeelding, 186, 197, 214);
imagerectangle($afbeelding, 0, 0, $breedte-1, $hoogte-1, $kleur);

$aantal_punten = rand(250, 500);

for ($i = 0; $i < $aantal_punten; $i++)
{
	imagesetpixel($afbeelding, rand(1, $breedte-1), rand(1, $hoogte-1), $kleur);
}

$aantal_lijnen = rand(5, 8);
$aantal_cirkels = rand(5, 8);
$aantal_lijnen2 = rand(2, 6);
$spread = 100;

for($i = 0; $i < $aantal_lijnen; $i++)
{
	$y_begin = rand(-$spread, $hoogte + $spread);
	$y_eind = rand(-$spread, $hoogte + $spread);
	$kleur = imagecolorallocate($afbeelding, rand(170, 255), rand(170, 255), rand(170, 255));

	imageline($afbeelding, 0, $y_begin, $breedte, $y_eind, $kleur);
}

for($i = 0; $i < $aantal_cirkels; $i++)
{
	$y_center = rand(1, $breedte-1);
	$x_center = rand(1, $hoogte-1);
	$kleur = imagecolorallocate($afbeelding, rand(170, 255), rand(170, 255), rand(170, 255));

	imageellipse($afbeelding, $y_center, $y_center, rand(50, 100), rand(50, 100), $kleur);
}

$tekst = str_split($random_code);

for ($i = 0; $i < count($tekst); $i++)
{
	$xas = rand(7, 20);
	$yas = rand(27, 37);
	$graden = rand(-25, 25);
	$grootte = rand(18,21);
	$kleur = imagecolorallocate($afbeelding, rand(0, 100), rand(0, 100), rand(0, 100));
	imagettftext($afbeelding, $grootte, $graden, $i * 25 + $xas, $yas, $kleur, $font, $tekst[$i]);
}

for($i = 0; $i < $aantal_lijnen2; $i++)
{
	$y_begin = rand(-$spread, $hoogte + $spread);
	$y_eind = rand(-$spread, $hoogte + $spread);
	$kleur = imagecolorallocate($afbeelding, rand(170, 255), rand(170, 255), rand(170, 255));

	imageline($afbeelding, 0, $y_begin, $breedte, $y_eind, $kleur);
}

imagepng($afbeelding);
imagedestroy($afbeelding);
?>
