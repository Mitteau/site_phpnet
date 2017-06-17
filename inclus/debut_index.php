<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" >
<head>

<?php
//Si le titre est indiqué, on l'affiche entre les balises <title>
echo (!empty($titre))?'<title>'.$titre.'</title>':'<title>Forum</title>';
?>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta content="Jean-Claude Mitteau" name="Author">
<meta content="Teste le nouveau site" name=" Description">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<link rel="icon" type=image/gif" href="/SITE/commun/clavier.gif" />
<link rel="stylesheet" type="text/css" title="Design" href="/css/design2.css" />
<!--[if IE]>
<link rel="stylesheet" type="text/css" title="Design" href="/css/IEdesign.css" />
<![endif]-->
<meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
<script LANGUAGE="JavaScript">

function affichage_popup(nom_de_la_page, nom_interne_de_la_fenetre){
window.open (nom_de_la_page, nom_interne_de_la_fenetre, config='height=300, width=600, toolbar=no, menubar=no, scrollbars=yes, resizable=no, location=no, directories=no, status=no')}

function bbcode(bbdebut, bbfin){
var input = window.document.formulaire.message;
input.focus();
if(typeof document.selection != 'undefined')
{
var range = document.selection.createRange();
var insText = range.text;
range.text = bbdebut + insText + bbfin;
range = document.selection.createRange();
if (insText.length == 0)
{
range.move('character', -bbfin.length);
}
else
{
range.moveStart('character', bbdebut.length + insText.length + bbfin.length);
}
range.select();
}
else if(typeof input.selectionStart != 'undefined')
{
var start = input.selectionStart;
var end = input.selectionEnd;
var insText = input.value.substring(start, end);
input.value = input.value.substr(0, start) + bbdebut + insText + bbfin + input.value.substr(end);
var pos;
if (insText.length == 0)
{
pos = start + bbdebut.length;
}
else
{
pos = start + bbdebut.length + insText.length + bbfin.length;
}
input.selectionStart = pos;
input.selectionEnd = pos;
}
 
else
{
var pos;
var re = new RegExp('^[0-9]{0,3}$');
while(!re.test(pos))
{
pos = prompt("insertion (0.." + input.value.length + "):", "0");
}
if(pos > input.value.length)
{
pos = input.value.length;
}
var insText = prompt("Veuillez taper le texte");
input.value = input.value.substr(0, pos) + bbdebut + insText + bbfin + input.value.substr(pos);
}
}


function smilies(img){
window.document.formulaire.message.value += '' + img + '';
if (window.attachEvent) window.attachEvent("onload", sfHover);}

$(document).ready( function () {
  // On cache les sous-menus :
  $(".navigation ul.subMenu").hide();    

    // On sélectionne tous les items de liste portant la classe "toggleSubMenu"
    // et on remplace l'élément span qu'ils contiennent par un lien :
    $(".navigation li.toggleSubMenu span").each( function () {
        $(this).replaceWith('<a href="" title="Afficher le sous-menu">' + $(this).text() + '<\/a>') ;
    } ) ;    


    // On modifie l'évènement "click" sur les liens dans les items de liste
    // qui portent la classe "toggleSubMenu" :
    $(".navigation li.toggleSubMenu > a").click( function () {
        // Si le sous-menu était déjà ouvert, on le referme :
        if ($(this).next("ul.subMenu:visible").length != 0) {
            $(this).next("ul.subMenu").slideUp("normal");
        }
        // Si le sous-menu est caché, on ferme les autres et on l'affiche :
        else {
            $(".navigation ul.subMenu").slideUp("normal");
            $(this).next("ul.subMenu").slideDown("normal");
        }
        // On empêche le navigateur de suivre le lien :
        return false;
    });    
} ) ;
</script> 
<!--
<script type="text/css">
body {border:0}
</script>-->
</head>
<?php
function code($texte)
{
//Smileys
$texte = str_replace(':D ', '<img src="./FORUM/images/smileys/heureux.png" title="heureux" alt="heureux" />', $texte);
$texte = str_replace(':lol: ', '<img src="./FORUM/images/smileys/lol.gif" title="lol" alt="lol" />', $texte);
$texte = str_replace(':triste:', '<img src="./FORUM/images/smileys/triste.png" title="triste" alt="triste" />', $texte);
$texte = str_replace(':frime:', '<img src="./FORUM/images/smileys/cool.gif" title="cool" alt="cool" />', $texte);
$texte = str_replace(':rire:', '<img src="./FORUM/images/smileys/rire.gif" title="rire" alt="rire" />', $texte);
$texte = str_replace(':s', '<img src="./FORUM/images/smileys/confus.gif" title="confus" alt="confus" />', $texte);
$texte = str_replace(':O', '<img src="./FORUM/images/smileys/choc.gif" title="choc" alt="choc" />', $texte);
$texte = str_replace(':question:', '<img src="./FORUM/images/smileys/question.gif" title="?" alt="?" />', $texte);
$texte = str_replace(':exclamation:', '<img src="./FORUM/images/smileys/exclamation.gif" title="!" alt="!" />', $texte);

//Mise en forme du texte
//gras
$texte = preg_replace('`\[g\](.+)\[/g\]`isU', '<strong>$1</strong>', $texte); 
//italique
$texte = preg_replace('`\[i\](.+)\[/i\]`isU', '<em>$1</em>', $texte);
//souligné
$texte = preg_replace('`\[s\](.+)\[/s\]`isU', '<u>$1</u>', $texte);
//lien
$texte = preg_replace('#http://[a-z0-9._/-]+#i', '<a href="$0">$0</a>', $texte);
//etc., etc.

//On retourne la variable texte
return $texte;
}
//Attribution des variables de session
$lvl=(isset($_SESSION['level']))?(int) $_SESSION['level']:1;
$id=(isset($_SESSION['id']))?(int) $_SESSION['id']:0;
$pseudo=(isset($_SESSION['pseudo']))?$_SESSION['pseudo']:'';

echo "<script language=\"JavaScript\">
<!-- 
var w = window.innerWidth
|| document.documentElement.clientWidth
|| document.body.clientWidth;

var h = window.innerHeight
|| document.documentElement.clientHeight
|| document.body.clientHeight; 
document.location=\"$PHP_SELF?r_window=1&Largeur=\"+w\"&Hauteur=\"+h;
//-->
</script>";
     if(isset($_GET['Largeur']) && isset($_GET['Hauteur'])) {
               // Résolution détectée
          $largeur=$_GET['Largeur'];
          $largeur=$largeur-20;
          $largeur2=$largeur-220;
          $largeur_e= $largeur."px";
          $largeur2_e= $largeur2."px";
          $hauteur= $_GET['Hauteur'];
          $hauteur_conteneur=$hauteur-130;
          $hauteur_contenu= $hauteur-160;
          $hauteur_cr_e= $hauteur_conteneur."px";
          $hauteur_cu_e= $hauteur_contenu."px";
          $ok_size=true;
     }
     else {
               // Résolution non détectée ... 1024x768
          $largeur_e="994px";
          $largeur2_e= "774px";
          $hauteur=768;
          $hauteur_cr_e="565px";
          $hauteur_cu_e="538px";
          $ok_size=false;
     }
//echo "là  ".$hauteur;
?>
