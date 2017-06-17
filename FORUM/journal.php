<?php
include('includes/constants.php');
include("includes/identifiants.php");
include('includes/start_session.php');
$titre = "Journal des connections";
include("includes/debut_forum.php");
include("includes/menu_forum.php");

$action = (isset($_GET['action']))?htmlspecialchars($_GET['action']):'';

if (!verif_auth(ADMIN)) erreur(ERR_AUTH_ADMIN);
echo '<p><i>Vous êtes ici</i> : <a href="./index.php">Accueil du forum</a> --> <a href="./admin.php">Administration du forum</a><br> --> Journal des connections</p>';
?>


<h4>Journal des connections</h4>
<p>Ordre d&eacute;croissant des dates.&nbsp;<i><a href="journal.php?action=purger">Purger</a></i></p>
<?php
switch ($action){
	case "consulter" :
	 $query=$db->prepare('SELECT journal_pseudo, journal_ip, journal_entree, journal_sortie, journal_connect
	 FROM forum_journal
	 ORDER BY journal_entree DESC');
	 $query->execute();
	 echo '<p style="text-align:left">';
	 $vide=true;
	 while ($data = $query->fetch()){
	 $vide=false;
	 $duree=$data['journal_sortie']-$data['journal_entree']." sec.";
	 if ($duree <0) $duree='NC';
	 if ($data['journal_connect']>0) $deconn='non'; else $deconn='oui';
	 echo '>'.$data['journal_pseudo'].', conn. du '.date(FORMAT_DATE,$data['journal_entree']).',<br />dur&eacute;e&nbsp;: '.$duree.', adresse <a onclick="">'.long2ip($data['journal_ip']).'</a>, d&eacute;connect&eacute;&nbsp;? '.$deconn.'<br />';}
	 if ($vide) echo "Le journal est vide.</p>"; else echo '</p>';
	 $query->closeCursor();
	break;

	case "purger" :// remettre le compteur à 1 MAP
	 $query=$db->prepare('DELETE FROM forum_journal');
	 $query->execute();
	 $query->closeCursor();
	break;
}
echo '</div>';
include("../inclus/fin.php");
?>
