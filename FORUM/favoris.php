<?php
include('includes/constants.php');
include("includes/identifiants.php");
include('includes/start_session.php');
$titre = "Un sujet";
include("includes/debut_forum.php");
include("includes/menu_forum.php");


 
if ($id==0)erreur(ERR_IS_NOT_CO);
$id = (int) $_GET['m'];
$forum = (int) $_GET['f'];
$action = $_GET['action'];

switch ($action){
	case "ranger" :
	 $topic = (int) $_GET['t'];
	 $query=$db->prepare('SELECT lien_id FROM forum_liens WHERE lien_id = :id AND lien_adresse = :topic');
	 $query->bindValue(':id',$id,PDO::PARAM_INT);
	 $query->bindValue(':topic',$topic,PDO::PARAM_INT);
	 $query->execute();
	 $i=0;
	 while ($query->fetch()) $i++;
	 if ($i==0){
	  $topic = (int) $_GET['t'];
	  $query1=$db->prepare('INSERT INTO forum_liens (lien_id, lien_adresse, lien_time, lien_forum) VALUES (:id, :topic, :time, :forum)');
	  $query1->bindValue(':id',$id,PDO::PARAM_INT);
	  $query1->bindValue(':topic',$topic,PDO::PARAM_INT);
	  $query1->bindValue(':time',time(),PDO::PARAM_INT);
	  $query1->bindValue(':forum',$forum,PDO::PARAM_INT);
	  $query1->execute();
	  $query1->closeCursor();}
	 else {
	  $topic = (int) $_GET['t'];
	  $query1=$db->prepare('UPDATE forum_liens SET lien_time = :time, lien_forum= :forum
	  WHERE lien_id = :id AND lien_adresse = :topic');
	  $query1->bindValue(':id',$id,PDO::PARAM_INT);
	  $query1->bindValue(':topic',$topic,PDO::PARAM_INT);
	  $query1->bindValue(':time',time(),PDO::PARAM_INT);
	  $query1->bindValue(':forum',$forum,PDO::PARAM_INT);
	  $query1->execute();
	  $query1->closeCursor();}
	 $query->closeCursor();
?>
<script type="text/javascript">
 history.go(-1);
</script>
<?php
	break;

	case "consulter" :
	 
	 echo '<h1>Vos pages r&eacute;f&eacute;renc&eacute;es</h1>';
	 $query=$db->prepare('SELECT  lien_ind, lien_adresse, lien_id, lien_time, topic_titre, forum_name
	 FROM forum_liens 
	 LEFT JOIN forum_topic ON lien_adresse = topic_id
	 LEFT JOIN forum_forum ON lien_forum = forum_forum.forum_id
	 WHERE lien_id = :id');
	 $query->bindValue(':id',$id,PDO::PARAM_INT);
	 $query->execute();
	 echo '<div style="text-align:left;">';
	 while ($data = $query->fetch())
	  echo 'Forum&nbsp;: '.$data['forum_name'].', sujet&nbsp;: <a href="./voirtopic.php?t='.$data['lien_adresse'].'">'.$data['topic_titre'].'</a>, lien du '.date('j-m-Y',$data['lien_time']).'<a href="favoris.php?action=effacer&amp;lien='.$data['lien_ind'].'">  <img src="images/28107.png" title="Supprimer" alt="Supprimer" height=15px /></a><br />';
	 $query->closeCursor();
	 echo '</div>';
	break;

	case "effacer" :
	$index = $_GET['lien'];
	 echo '<h1>Suppression d\'une r&eacute;f&eacute;rence</h1>';
	$query=$db->prepare('DELETE FROM forum_liens WHERE lien_ind = :index');
	$query->bindValue(':index',$index,PDO::PARAM_INT);
	$query->execute();
	$query->closeCursor();
echo '<p>La r&eacute;f&eacute;rence est supprim&eacute;e.
<p><button onClick="retour()">Retour &agrave; l\'accueil.</button>
	</p>';

	break;

	default :
}?>
<script type="text/javascript">
   function retour(){
   location.assign("./index.php")};
</script>
<?php echo'</div>';
include("../inclus/fin.php");
?>