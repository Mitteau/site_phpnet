<?php
include('includes/constants.php');
include("includes/identifiants.php");
include('includes/start_session.php');
$titre = "Liste des membres";
include("includes/debut_forum.php");
include("includes/menu_forum.php");

$action = (isset($_GET['action']))?htmlspecialchars($_GET['action']):'';

if (!verif_auth(INSCRIT)) erreur(ERR_IS_NOT_CO);
echo '<p><i>Vous êtes ici</i> : <a href="./index.php">Index du forum</a> --> <a href="./admin.php">Administration du forum</a> --> Liste des membres<br />';

switch ($action){
	case "consulter" :

//A partir d'ici, on va compter le nombre de members
//pour n'afficher que les 25 premiers
$query=$db->query('SELECT COUNT(*) AS nbr FROM forum_membres');
$data = $query->fetch();
$total = $data['nbr'];
//echo $total;
$query->closeCursor();


//recueil des paramètres

	$query = $db->query('SELECT config_valeur FROM forum_config WHERE config_nom="lign_par_page"');
	$data=$query->fetch();
	$query->closeCursor();
	
$MembreParPage=(int)$data['config_valeur'];


$NombreDePages = intval(ceil($total / $MembreParPage));
//echo '  '.$NombreDePages;
//if (($NombreDePages * $MembreParPage-.001<=$total)||($total<=$NombreDePages * $MembreParPage+.001)) $NombreDePages--;



//Nombre de pages

$page = (isset($_GET['page']))?intval($_GET['page']):1;//MAP:OK

//On affiche les pages 1-2-3, etc. Problème si 1 page MAP

echo 'Pages : ';
for ($i = 1 ; $i <= $NombreDePages ; $i++)
{
    if ($i == $page) //On ne met pas de lien sur la page actuelle
    {
        echo "&nbsp;".$i;
    }
    else
    {
        echo '&nbsp;<a href="memberlist.php?action=consulter&amp;page='.$i.'">'.$i.'</a>';
    }
}


$premier = ($page - 1) * $MembreParPage;

//Le titre de la page
echo '<h1>Liste des membres</h1>';

//Tri FONCTION à vérifier MAP
$convert_order = array('membre_pseudo', 'membre_inscrit', 'membre_post', 'membre_derniere_visite', 'membre_rang'); 
$convert_tri = array('ASC', 'DESC');
$texte=array(
'membre_pseudo' => 'pseudo',
'membre_inscrit' => 'date d\'inscription',
'membre_post' => 'nombre de messages',
'membre_derniere_visite' => 'derni&egrave;re visite',
'membre_rang' => 'fonction',
'ASC' => 'croissant',
'DESC' => 'd&eacute;croissant'
);

//On récupère la valeur de s
if (isset ($_POST['s'])) $sort = $convert_order[$_POST['s']];
else $sort = $convert_order[0];
//On récupère la valeur de t
if (isset ($_POST['t'])) $tri = $convert_tri[$_POST['t']];
else $tri = $convert_tri[0];
?>
<p>Votre tri&nbsp;: 
<form action="memberlist.php?action=consulter" method="post">
<p><label for="s">Trier par : </label>

<select name="s" id="s">
<option value="0" name="0">Pseudo</option>
<option value="1" name="1">Inscription</option>
<option value="2" name="2">Messages</option>
<option value="3" name="3">Dernière visite</option>
<option value="4" name="4">Fonction</option>
</select>

<select name="t" id="t">
<option value="0" name="0">Croissant</option>
<option value="1" name="1">Décroissant</option>
</select>
<input type="submit" value="Trier" /></p>
</form>
<?php
//Requête//supprimé online_id? MAP
$query = $db->prepare('SELECT membre_id, membre_pseudo, membre_inscrit, membre_post, membre_derniere_visite, membre_rang, online_id
FROM forum_membres
LEFT JOIN forum_whosonline ON online_id = membre_id
ORDER BY '.$sort.', membre_id '.$tri.'
LIMIT :premier, :membreparpage');
$query->bindValue(':premier',$premier,PDO::PARAM_INT);
$query->bindValue(':membreparpage',$MembreParPage, PDO::PARAM_INT);
if (!$query->execute()) echo "<br>pas d'acc&egrave;s";

if ($query->rowCount() > 0)
{
echo '<p style="text-align:left">Votre tri&nbsp;: selon '.$texte[$sort].', ordre '.$texte[$tri].'.';
?>
       <table>
       <tr>
       <th class="pseudo"><strong>Pseudo</strong></th>             
       <th class="posts"><strong>Messages</strong></th>
       <th class="inscrit"><strong>Inscrit depuis le</strong></th>
       <th class="derniere_visite"><strong>Dernière visite</strong></th>                       
       <th class="fonction"><strong>Fonction</strong></th>                       
       <th><strong>Connecté</strong></th>

       </tr>
       <?php
       //On lance la boucle
       
       while ($data = $query->fetch())
       {
           //echo $data['membre_rang']."-----".$data['membre_pseudo'];
           switch ($data['membre_rang']){
            case 4 : $fonction="Administrateur";break;
            case 3 : $fonction="Mod&eacute;rateur";break;
            default: $fonction="Membre";
           }
           if (isset($data['online_id'])) $el="oui"; else $el="non";
           echo '<tr><td>
           <a href="./voirprofil.php?m='.$data['membre_id'].'&amp;action=consulter">
           '.stripslashes(htmlspecialchars($data['membre_pseudo'])).'</td>
           <td>'.$data['membre_post'].'</td>
           <td>'.date(FORMAT_DATE,$data['membre_inscrit']).'</td>
           <td>'.date(FORMAT_DATE,$data['membre_derniere_visite']).'</td>
           <td>'.$fonction.'</td>
           <td>'.$el.'</td>
           </tr>';
       }
       $query->closeCursor();
       ?>
       </table>
       <?php
}
else //S'il n'y a pas de membre
{
    echo'<p>Ce forum ne contient actuellement aucun membre.</p>';
}
$query->closeCursor();
	break;
	
	case "admin" :
if (!verif_auth(ADMIN)) erreur(ERR_AUTH_ADMIN);


	 $query=$db->prepare('SELECT membre_pseudo, online_time, online_ip
	 FROM forum_whosonline
	 LEFT JOIN forum_membres ON online_id = membre_id
	 ORDER BY online_time 
	 DESC');
	 $query->execute();
	 echo '<h3>Connections en cours</h3>';
	 echo '<div style="text-align:left;">';
	 while ($data = $query->fetch()){
	 if (!isset($data['membre_pseudo'])) $pseudo = "Visite";
	 else {$pseudo = $data['membre_pseudo'];}
	 echo '>'.$pseudo.', connection du '.date(FORMAT_DATE,$data['online_time']).' adresse '.long2ip($data['online_ip']).'<br />';}
	 $query->closeCursor();
	 echo '</div>';
	break;

}
echo '</div>';
include("../inclus/fin.php");
?>
