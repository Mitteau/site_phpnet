<?php
include('includes/constants.php');
include("includes/identifiants.php");
include('includes/start_session.php');
$titre = "Accueil du forum";
include("includes/debut_forum.php");
include("includes/menu_forum.php");


if (!$db) erreur("pas de base");
//Initialisation de deux variables
$totaldesmessages = 0;
$categorie = NULL;

//Cette requête permet d'obtenir tout sur le forum
$query=$db->prepare('SELECT cat_id, cat_nom, 
forum_forum.forum_id, forum_name, forum_desc, forum_post, forum_topic, auth_view, forum_topic.topic_id, forum_topic.topic_post, topic_locked, topic_locked_time, post_id, post_time, post_createur, membre_pseudo, membre_id 
FROM forum_categorie
LEFT JOIN forum_forum ON forum_categorie.cat_id = forum_forum.forum_cat_id
LEFT JOIN forum_post ON forum_post.post_id = forum_forum.forum_last_post_id
LEFT JOIN forum_topic ON forum_topic.topic_id = forum_post.topic_id
LEFT JOIN forum_membres ON forum_membres.membre_id = forum_post.post_createur
WHERE auth_view <= :lvl 
ORDER BY cat_ordre, forum_ordre DESC');
$query->bindValue(':lvl',$lvl,PDO::PARAM_INT);
try{$query->execute();}
catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
//gestion des messages personnels
$query1=$db->prepare('SELECT mp_id, mp_expediteur, mp_receveur, mp_titre, mp_time, mp_text, mp_lu 
FROM forum_mp
WHERE  mp_lu = "0" AND mp_receveur = :mpr');
$query1->bindValue(':mpr',$id);
try{$query1->execute();}
catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
$data=[];
while ($data[]=$query1->fetch());
$query1->closeCursor();
//if ($id) echo "nombre de nouveaux messages ".(count($data)-1)."<br>";


if ((count($data)-1) && $id){?><script type="text/javascript">document.getElementById("mp_notification").innerHTML="<div style=\"color:red;\">Vous avez un ou plusieurs nouveaux messages personnels</div>";</script>
<?php }else {?>
<script type="text/javascript">document.getElementById("mp_notification").innerHTML="&nbsp;";</script>
<?php ;}
?>

<h1>Les forums</h1>
<table  class="forum">
<?php
//Début de la boucle
while($data = $query->fetch())
{
    //On affiche chaque catégorie
    if( $categorie != $data['cat_id'] )
    {
        //Si c'est une nouvelle catégorie on l'affiche
       
        $categorie = $data['cat_id'];
?>
        <tr>
        <th></th>
        <th class="titre"><strong><?php echo stripslashes(htmlspecialchars($data['cat_nom']));?>
        </strong></th>             
        <th class="nombremessages"><strong>Sujets</strong></th>       
        <th class="nombresujets"><strong>Messages</strong></th>       
        <th class="derniermessage"><strong>Dernier message</strong></th>   
        </tr>
<?php          

    }
    //Ici, on met le contenu de chaque catégorie

    // Ce super echo de la mort affiche tous
    // les forums en détail : description, nombre de réponses etc...
if (verif_auth($data['auth_view']))//MAPMAP
{

    echo'<tr><td>
    <img src="./images/message.gif" alt="forum" /></td>
    <td class="titre"><strong>
    <a href="./voirforum.php?f='.$data['forum_id'].'">
    '.stripslashes(htmlspecialchars($data['forum_name'])).'</a></strong>
    <br />'.nl2br(stripslashes(htmlspecialchars($data['forum_desc']))).'</td>
    <td class="nombresujets">'.$data['forum_topic'].'</td>
    <td class="nombremessages">'.$data['forum_post'].'</td>';

    // Deux cas possibles :
    // Soit il y a un nouveau message, soit le forum est vide
    if (!empty($data['forum_post']))
    {
         //Selection dernier message
     $nombreDeMessagesParPage = 15;
         $nbr_post = $data['topic_post'] +1;
     $page = ceil($nbr_post / $nombreDeMessagesParPage);
         
         echo'<td class="derniermessage">
         '.date(FORMAT_DATE,$data['post_time']).'<br />
         <a href="./voirprofil.php?m='.stripslashes(htmlspecialchars($data['membre_id'])).'&amp;action=consulter">'.$data['membre_pseudo'].'</a>&nbsp;
         <a href="./voirtopic.php?t='.$data['topic_id'].'&amp;page='.$page.'#p_'.$data['post_id'].'">         <img src="./images/go.gif" alt="Voir" /></a></td></tr>';

     }
     else
     {
         echo'<td class="nombremessages">Pas de message.</td></tr>';
     }

     //Cette variable stock le nombre de messages, on la met à jour
     $totaldesmessages += $data['forum_post'];

     //On ferme notre boucle et nos balises
}//if (verif_auth
else
{
echo'<td class="nombremessages">Pas de message</td></tr>';
}
} //fin de la boucle
$query->closeCursor();
echo '</table>';

//Le pied du document ici :
echo'
<h6>
Qui est en ligne ?
</h6>
<div style="font-size:small">
';
//On compte les membres
$TotalDesMembres = $db->query('SELECT COUNT(*) FROM forum_membres')->fetchColumn();
$query->closeCursor();	
$query = $db->query('SELECT membre_pseudo, membre_id FROM forum_membres ORDER BY membre_id DESC LIMIT 0, 1');
$data = $query->fetch();
$derniermembre = stripslashes(htmlspecialchars($data['membre_pseudo']));
echo'<p>Votre total des messages du forum est <strong>'.$totaldesmessages.'</strong>.<br />';
echo'Le site et le forum comptent <strong>'.$TotalDesMembres.'</strong> membres.<br />';
if ($id)
echo'Le dernier membre inscrit est <a href="./voirprofil.php?m='.$data['membre_id'].'&amp;action=consulter">'.$derniermembre.'</a>.</p>';
$query->closeCursor();

echo '<p>'.$count_online.' personnes(s) connect&eacute;e(s) ('.$count_membres.' membre(s) et '.$count_visiteurs.' invit&eacute;(s)).';
if ($id>0)echo $texte_a_afficher;
echo '</p></div></div>';


include("../inclus/fin.php");
?>

