<?php
session_start();
$title = "Suppressions";
include('entre.php');

if (!verif_auth(ADMIN)) erreur(ERR_AUTH_ADMIN);
// On indique où l'on se trouve
$f = (isset($_GET['f']))?(int)($_GET['f']):'0';
$cat = (isset($_GET['cat']))?htmlspecialchars($_GET['cat']):'';
//echo $cat."   ".$f;
switch ($cat){
case "forum":
	echo'<p><i>Vous êtes ici</i> : <a href="./index.php">Index du forum</a> -->  <a href="./admin.php">Administration du forum</a> --> Suppressions</p>';

	echo '<p>Vous supprimez le form n° '.$f.'</p>
	<p>Votre dernière chance d\'éviter cela&nbsp;: 
	<div>
	<button onClick="annuler()" autofocus>Annuler</button>
	<button onClick="poursuivre(';
	echo $f.')">Supprimer</button>
		</div>';
?>
<script type="text/javascript">
function annuler(){
   location.assign("./admin.php");}
function poursuivre(i){
   location.assign("./suppression.php?cat=forumc&f="+i);}
</script>
<?php
	break;
case "forumc": // actions de suppression d'un forum
//suppression messages
	$query=$db->prepare('DELETE FROM forum_post WHERE post_forum_id =:forum');
	$query->bindValue(':forum', $f, PDO::PARAM_INT);    
try{$query->execute();}
catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
	$query->closeCursor;

//suppression sujets
	$query=$db->prepare('DELETE FROM forum_topic WHERE forum_id =:forum');
	$query->bindValue(':forum', $f, PDO::PARAM_INT);    
try{$query->execute();}
catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
	$query->closeCursor;

//suppression forum
	$query=$db->prepare('DELETE FROM forum_forum WHERE forum_id =:forum');
	$query->bindValue(':forum', $f, PDO::PARAM_INT);    
try{$query->execute();}
catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
	$query->closeCursor;

	echo 'Le forum est supprimé';
	break;






}// fin du switch


include($BASE_FORUM."/includes/bas.php");
?>


