<?php
if (!isset($_SESSION['level'])) $_SESSION['level']=1;
if (!isset($_SESSION['id'])) $_SESSION['id']=0;
if (!isset($_SESSION['pseudo'])) $_SESSION['pseudo']="";
if (!isset($_SESSION['in'])) $_SESSION['in']=0;
if (!isset($_SESSION['online_index'])) $_SESSION['online_index'] = 1;
if (!isset($_SESSION['online_id'])) $_SESSION['online_id'] = 0;
if (!isset($_SESSION['online_time'])) $_SESSION['online_time'] = time();
if (!isset($_SESSION['online_session'])) $_SESSION['online_session'] = session_id();
if (!isset($_SESSION['online_ip'])) $_SESSION['online_ip'] = ip2long($_SERVER['REMOTE_ADDR']);
if (!isset($_SESSION['online_host'])) $_SESSION['online_host'] = $_SERVER['REMOTE_ADDR'];
if (!isset($_SESSION['membre_rang'])) $_SESSION['membre_rang'] = 1;
if (!isset($_SESSION['message'])) $_SESSION['message'] ="";
$lvl=$_SESSION['level'];
$id=$_SESSION['id'];
$pseudo=$_SESSION['pseudo'];
$index=$_SESSION['online_index'];
$time=$_SESSION['online_time'];
$sess=$_SESSION['online_session'];
$ip=$_SESSION['online_ip'];
$host=$_SESSION['online_host'];
$rang=$_SESSION['membre_rang'];
//affichage valeurs
/*echo 
'level '.$lvl.
' id '.$id.
' pseudo -'.$pseudo.
'-host '.$host.
' in '.$_SESSION['in'].
' online_index '.$_SESSION['online_index'].
' online_ip '.$_SESSION['online_ip'].
' temps '.$time.
' rang '.$rang.
' session '.$sess.
'<br>';*/

//Requête
if ($_SESSION['in'] <1){
//onvérifie la présence de la session dans whosonline et on efface les autres lignes
//TABLEAU des sessions dans session_save_path
$sessions=array();

$dir=opendir(SESS_SAVE_PATH);
while ($sess1 = readdir($dir)){
if (strlen($sess1)>2) array_push($sessions,substr($sess1,5,strlen($sess1)-5));
}

//suppression des lignes dans whosonline, mise en journal si non déconnecté
$str=implode('\',\'',$sessions);
$str='(\''.$str.'\')';
//echo $str;
$query=$db->prepare('SELECT
online_id,
online_time,
online_ip,
online_host,
membre_pseudo,
membre_rang
FROM forum_whosonline
LEFT JOIN forum_membres ON forum_whosonline.online_id = forum_membres.membre_id
WHERE online_session NOT IN '.$str);
$query->execute();
while ($data=$query->fetch()) if ($data['membre_pseudo']<>""){
	$query1=$db->prepare('INSERT INTO forum_journal SET
	journal_pseudo=:pseudo,
	journal_ip=:ip,
	journal_entree=:entree,
	journal_sortie=:sortie,
	journal_connect=:connect,
	journal_rang=:rang,
	journal_host=:host
	');
	$query1->bindValue(':pseudo',$data['membre_pseudo'],PDO::PARAM_STR);
	$query1->bindValue(':ip',$data['online_ip'],PDO::PARAM_INT);
	$query1->bindValue(':entree',$data['online_time'],PDO::PARAM_INT);
	$query1->bindValue(':sortie',time(),PDO::PARAM_INT);
	$query1->bindValue(':connect','1',PDO::PARAM_STR);//pas déconnecté
	$query1->bindValue(':rang',$data['membre_rang'],PDO::PARAM_INT);
	$query1->bindValue(':host',$data['online_host'],PDO::PARAM_STR);
	$query1->execute();
	$query1->closeCursor();
}
$query->closeCursor();

$query=$db->prepare('DELETE FROM forum_whosonline
WHERE online_session NOT IN '.$str);
$query->execute();
$query->closeCursor();

//nouvelle ligne whosonline
$query=$db->prepare('INSERT INTO forum_whosonline SET
online_id=:id,
online_time=:time,
online_session=:session,
online_ip=:ip,
online_host=:host
');
$query->bindValue(':id',$id,PDO::PARAM_INT);
$query->bindValue(':time',time(), PDO::PARAM_INT);
$query->bindValue(':session',$sess, PDO::PARAM_STR);
$query->bindValue(':ip',$_SESSION['online_ip'], PDO::PARAM_INT);
$query->bindValue(':host',$host, PDO::PARAM_STR);
if (!$query->execute()) {echo 'raté';die;}
$query->closeCursor();
$_SESSION['online_index']=$db->lastInsertId();

$_SESSION['in']=1;
}

//recueil des paramètres
	$config0 = array(
	"avatar_maxsize" => "",
	"avatar_maxh" => "",
	"avatar_maxl" => "",
	"sign_maxl" => "",
	"auth_bbcode_sign" => "",
	"pseudo_maxsize" => "",
	"pseudo_minsize" => "",
	"temps_flood" => "",
	);
	$query = $db->query('SELECT config_nom, config_valeur FROM forum_config');
	while($data=$query->fetch()){
          $config0[$data['config_nom']]=$data['config_valeur'];}
	$query->closeCursor();



//Initialisation de la variable
$count_online = 0;

//Décompte des visiteurs
$count_visiteurs=$db->query('SELECT COUNT(*) AS nbr_visiteurs FROM forum_whosonline WHERE online_id = "0"')->fetchColumn();
$query->closeCursor();

//Décompte des membres
$texte_a_afficher = "<br />Liste des personnes en ligne : ";
$time_max = time() - (300);// 5 heures
$query=$db->prepare('SELECT membre_id, membre_pseudo, online_ip
FROM forum_whosonline
LEFT JOIN forum_membres ON online_id = membre_id
WHERE online_id <> 0');

//WHERE online_time > :timemax AND online_id <> 0');


//$query->bindValue(':timemax',$time_max, PDO::PARAM_INT);
$query->execute();
$count_membres=0;
$prologue="<br />Membre en ligne&nbsp;:&nbsp;";
while ($data = $query->fetch()){
    $count_membres ++;
    $texte_a_afficher = $prologue.'<a href="./voirprofil.php?m='.$data['membre_id'].'&amp;action=consulter">
    '.stripslashes(htmlspecialchars($data['membre_pseudo'])).', adresse&nbsp;:'.long2ip($data['online_ip']).'</a> ,';}
if ($count_membres==0)$texte_a_afficher=$prologue." personne.";
$texte_a_afficher = substr($texte_a_afficher, 0, -1);
$count_online = $count_visiteurs + $count_membres;
$query->closeCursor();
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" >
<head>

<?php
//Si le titre est indiqué, on l'affiche entre les balises <title>
echo (!empty($titre))?'<title>'.$titre.'</title>':'<title>Forum</title>';
//include("includes/constants.php");
include("includes/functions.php");
include("includes/functions_text.php");?>

<meta name="google-site-verification" content="u3lhdqH6L0g9vv0QvjU78Yk2_RlRA0q9ElVBouhKjrU" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta content="Jean-Claude Mitteau" name="Author">
<meta content="Teste le nouveau site" name=" Description">
<?php echo'
<link rel="icon" type=image/gif" href="'.SITE.'/commun/clavier.gif" />
<link rel="stylesheet" media="screen" type="text/css" title="Design" href="'.SITE.'/css/design.css" />
<!--<link rel="stylesheet" type="text/css" title="Design" href="'.SITE.'/css/design.css" />
<!--[if IE]>
<link rel="stylesheet" type="text/css" title="Design" href="'.SITE.'/css/IEdesign.css" />
<![endif]-->';
?>
<meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
<!--<script type="text/javascript" src="http://code.jquery.com/jquery-3.1.0.min.js"></script>-->
<script type="text/javascript" src="http://jqueryjs.googlecode.com/files/jquery-1.3.2.min.js"></script>

<script LANGUAGE="JavaScript">
<!--
function affichage_popup(nom_de_la_page, nom_interne_de_la_fenetre){
window.open (nom_de_la_page, nom_interne_de_la_fenetre, config='height=300, width=600, toolbar=no, menubar=no, scrollbars=yes, resizable=no, location=no, directories=no, status=no')}

function affichage_popup(nom_de_la_page, nom_interne_de_la_fenetre)
{
window.open (nom_de_la_page, nom_interne_de_la_fenetre, config='height=300, width=600, toolbar=no, menubar=no, scrollbars=yes, resizable=no, location=no, directories=no, status=no')
}

function bbcode(bbdebut, bbfin)// codage de l'éditeur
{
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
}// codage de l'éditeur

function smilies(img)// codage des émoticons
{
window.document.formulaire.message.value += '' + img + '';
}// codage des émoticons

if (window.attachEvent) window.attachEvent("onload", sfHover);

$(document).ready( function () {// menu déroulant
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
}// menu déroulant
 ) ;// ?????????????, MAP
-->

</script> 
</head>
<body id="global">
<!--<?php echo '<br>$id = '.$id."<br>";?>-->

<div id="conteneur">
    <div id="header">
        <!-- Ceci est mon haut de page -->

<table>
<tr>
<!--<td style="width:750px;border:1px solid black">-->
<td style="width:750px;border:0;margin:0">
        <p    style="font-family:serif"><b><font size="6">J</font><font size="5">EAN-</font><font size="6">C</font><font size="5">LAUDE 
            </font><font size="6">M</font><font size="5">ITTEAU</font></b></p>


</td>
<td style="width:200px;text-align: right;border:0;">
<?php

//menu local
if ($id>0){
       $query=$db->prepare('SELECT membre_pseudo, membre_avatar,
       membre_email, membre_msn, membre_signature, membre_siteweb, membre_post,
       membre_inscrit, membre_localisation
       FROM forum_membres WHERE membre_id=:membre') ;
       $query->bindValue(':membre',$id, PDO::PARAM_INT);
       $query->execute();
       $data=$query->fetch();
       $query->closeCursor();
?>
<ul id="menu1">
   <li><img src="./images/avatars/<?php echo $data['membre_avatar'];?>" alt="vous" title="vous" height="30px" />
   <ul>
	 <li>Vous avez &eacute;crit <?php echo $data['membre_post'];?> messages</a></li>
	 <li><a href="favoris.php?action=consulter&m=<?php echo $id;?>">Vos liens</a></li>
	 <li><a href="voirprofil.php?action=consulter&m=<?php echo $id;?>">Votre profil</a></li>
	 <li><a href="deconnexion.php">Se d&eacute;connecter</a></li>
   </ul>
   </li>
</ul>
<?php } ?>
</td></tr>
</table>
    </div>

    <div id="wrap">
        <div id="sidebar" style="opacity:1.; z-index:1;">
            <!-- Ceci est ma colonne latérale -->

