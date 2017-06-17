<?php
session_start();
include('entre.php');

// On indique où l'on se trouve
echo'<p><i>Vous êtes ici</i> : <a href="./index.php">Index du forum</a> -->  <a href="./admin.php">Administration du forum</a><br>';

if (!verif_auth(ADMIN)) erreur(ERR_AUTH_ADMIN);
echo '
<form method="post" enctype="multipart/form-data">
<label>Fichier à joindre au forum&nbsp;: </label>
<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
<input type="file" name="fichier" title="fichier à charger" accept=".jpg, .gif, .png, .doc, .pdf, .docx, .ods, .odp, .odt"><br />
<input type="submit" value="Charger"></form>';

if ($_FILES){
$name = $_FILES['fichier']['name'];
$p = strpos($name,'.');//premier point !!!!MAP
$name0 = substr($name,0,$p);
$name1 = time();
$nompjointe = str_replace($name0,$name1,$name);
//echo "<br>".$nompjointe;
//echo "<br>";



if (move_uploaded_file($_FILES['fichier']['tmp_name'],'/var/www/FORUM/jointes/'.$nompjointe)) echo 'transfert en cours.<br />';  else {

//gestion erreurs
$errmsg=array(
0 => "Aucune erreur, le téléchargement est correct.",
1 => "La taille du fichier téléchargé excède la valeur de upload_max_filesize, configurée dans le php.ini.",
2 => "La taille du fichier téléchargé excède la valeur de MAX_FILE_SIZE, qui a été spécifiée dans le formulaire HTML.",
3 => "Le fichier n'a été que partiellement téléchargé.",
4 => "Aucun fichier n'a été téléchargé.",
6 => "Un dossier temporaire est manquant. Introduit en PHP 5.0.3.",
7 => "Échec de l\'écriture du fichier sur le disque. Introduit en PHP 5.1.0.",
8 => "Une extension PHP a arrêté l'envoi de fichier. PHP ne propose aucun moyen de déterminer quelle extension est en cause. L'examen du phpinfo() peut aider Introduit en PHP 5.2.0.",
);
echo "<br />Code erreur : ".$errmsg[$_FILES['fichier']['error']]."<br />";}
if ($_FILES['fichier']['error']>0) erreur('Abandon de l\'opération'); else echo 'transfert réussi.';

}
include($BASE_FORUM."/includes/bas.php");


?>