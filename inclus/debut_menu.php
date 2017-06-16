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
<link rel="icon" type=image/gif" href="/commun/clavier.gif" />
<link rel="stylesheet" media="screen" type="text/css" title="Design" href="/css/design.css" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
<script type="text/javascript" src="http://code.jquery.com/jquery-3.1.0.min.js"></script>
<script LANGUAGE="JavaScript">
<!--
function affichage_popup(nom_de_la_page, nom_interne_de_la_fenetre)
{
window.open (nom_de_la_page, nom_interne_de_la_fenetre, config='height=300, width=600, toolbar=no, menubar=no, scrollbars=yes, resizable=no, location=no, directories=no, status=no')
}

if (window.attachEvent) window.attachEvent("onload", sfHover);
-->
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

</head>

