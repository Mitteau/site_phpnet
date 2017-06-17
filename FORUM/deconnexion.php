<?php
include('includes/constants.php');
include("includes/identifiants.php");
include('includes/start_session.php');
$titre = "D&eacute;connection";
include("includes/debut_forum.php");
include("includes/menu_forum.php");

if (!$db) erreur("pas de base");
$id=$_SESSION['id'];
if ($ok=isset($_GET['ind'])) $str = $_GET['ind'];
else $str = $_SESSION['online_index']; //modif
if ($id==0 && !$ok) erreur(ERR_IS_NOT_CO);//problème avec la fonction erreur MAP
else {
echo'<p><i>Vous êtes ici</i> : <a href="./index.php">Accueil du forum</a> -->  D&eacute;connection<br>';
//$dec=false;
echo '
<h1>D&eacute;connection</h1>

<p>Bonjour '.$_SESSION['pseudo'].', vous allez être &agrave; pr&eacute;sent d&eacute;connect&eacute;(e)</p>';

?>
<script type="text/javascript">
   function retour(){
   location.assign("./index.php")};
</script>
<?php


$lignes= explode(',',$str);
//print_r($lignes);
reset($lignes);
while ($data=each($lignes)){
$query=$db->prepare('SELECT membre_pseudo, online_ip, online_time, membre_rang, online_host, online_session
FROM forum_whosonline
LEFT JOIN forum_membres ON online_id = membre_id
WHERE online_index=:index');
$query->bindValue(':index',$data['1'],PDO::PARAM_INT);
$query->execute();
$data1=$query->fetch();
$query->closeCursor();
$pseudo=$data1['membre_pseudo'];
if ($pseudo<>""){
$query=$db->prepare('INSERT INTO forum_journal SET
journal_pseudo=:pseudo,
journal_ip=:ip,
journal_entree=:entree,
journal_sortie=:sortie,
journal_connect=:connect,
journal_rang=:rang,
journal_host=:host
');
$query->bindValue(':pseudo',$data1['membre_pseudo'],PDO::PARAM_STR);
$query->bindValue(':ip',$data1['online_ip'],PDO::PARAM_INT);
$query->bindValue(':entree',$data1['online_time'],PDO::PARAM_INT);
$query->bindValue(':sortie',time(),PDO::PARAM_INT);
$query->bindValue(':connect','0',PDO::PARAM_STR);
$query->bindValue(':rang',$data1['membre_rang'],PDO::PARAM_INT);
$query->bindValue(':host',$data1['online_host'],PDO::PARAM_STR);
$query->execute();
$query->closeCursor();
//echo '<pre>';
//print_r($_SESSION);
//echo '</pre>';
//mise à jour des sessions correspondantes par effacement du fichier de session
unlink(SESS_SAVE_PATH.'/sess_'.$data1['online_session']);


}
$query=$db->prepare('UPDATE forum_whosonline 
SET online_id=0
WHERE online_index=:index');
$query->bindValue(':index',$data['1'],PDO::PARAM_INT);
$query->execute();
$query->closeCursor();
}
$_SESSION['id']=0;
$_SESSION['pseudo']='';
$_SESSION['level']=1;
$_SESSION['rang']=1;


echo '	<p><button onClick="retour()">Retour &agrave; l\'accueil.</button>
	</p>';
/*?>
<script type="text/javascript">
echo '	<p><button onClick="retour()">Retour &agrave; l\'accueil.</button>
	</p>';
location.assign("./index.php");
</script>
<?php
*/
}
echo '</div>';
include ("../inclus/fin.php");?>
