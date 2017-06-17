<?php
function erreur($err=''){
	$mess=($err!='')? $err:'Une erreur inconnue s\'est produite';
	echo('<p>'.$mess.'</p>
	<p>Cliquez <a href="./index.php">ici</a> pour revenir à la page d\'accueil</p></div>
	<div>');
	die;
	}
function verif_auth($auth_necessaire){
	//echo "niveau ".$_SESSION['level'];die;

	$level=(isset($_SESSION['level']))?$_SESSION['level']:1;
//	echo $level;die;
	return ($auth_necessaire <= intval($level));}

function move_avatar($avatar){
$name = $avatar['name'];
$p = strpos($name,'.');//premier point seulement!!!!MAP
$name0 = substr($name,0,$p);
$name1 = time();
$nomavatar = str_replace($name0,$name1,$name);
echo "<br>".$nomavatar;
echo "<br>";

if (move_uploaded_file($avatar['tmp_name'],BASE_FORUM.'/images/avatars/'.$nomavatar)) echo 'transfert';
 else echo "Veuillez s&eacute;lectionner un fichier.";

    return $nomavatar;
} // fin de fonction move...
?>

