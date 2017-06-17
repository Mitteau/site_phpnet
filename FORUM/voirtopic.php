<?php
include('includes/constants.php');
include("includes/identifiants.php");
include('includes/start_session.php');
$titre = "Acc&egrave;s &agrave; un sujet";
include("includes/debut_forum.php");
include("includes/menu_forum.php");
 
//On récupère la valeur de t
$topic = (int) $_GET['t'];
//A partir d'ici, on va compter le nombre de messages pour n'afficher que les 15 premiers
$query=$db->prepare('SELECT topic_titre, topic_post, topic_genre, forum_topic.forum_id, topic_last_post, topic_createur
topic_locked, topic_locked_time,
forum_name, auth_view, auth_topic, auth_post 
FROM forum_topic 
LEFT JOIN forum_forum ON forum_topic.forum_id = forum_forum.forum_id 
WHERE topic_id = :topic');
$query->bindValue(':topic',$topic,PDO::PARAM_INT);
//$query->bindValue(':lvl',$lvl,PDO::PARAM_INT);//MAP
try{$query->execute();}
catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
$data=$query->fetch();
$query->closeCursor();

$forum=$data['forum_id']; 
echo '<p><i>Vous êtes ici</i> : <a href="./index.php">Accueil du forum</a> --> 
<a href="./voirforum.php?f='.$forum.'">'.stripslashes(htmlspecialchars($data['forum_name'])).'</a>
 --> ';
echo stripslashes(htmlspecialchars($data['topic_titre'])).'<br />';
echo $forum_name;

if ($data['topic_locked']=='1' && !verif_auth(MODO)) erreur ("Sujet clos depuis le ".date(FORMAT_DATE,$data['topic_locked_time']));

if (!verif_auth($data['auth_view'])){erreur(ERR_AUTH_VIEW);}

$totalDesMessages = $data['topic_post'] + 1;
$nombreDeMessagesParPage = 15;
$nombreDePages = ceil($totalDesMessages / $nombreDeMessagesParPage);

$last_post = $data['topic_last_post'];

//Nombre de pages
$page = (isset($_GET['page']))?intval($_GET['page']):1;

//On affiche les pages 1-2-3 etc...
echo 'Page : ';
for ($i = 1 ; $i <= $nombreDePages ; $i++)
{
    if ($i == $page) //On affiche pas la page actuelle en lien
    {
    echo $i;
    }
    else
    {
    echo '<a href="voirtopic.php?t='.$topic.'&page='.$i.'">
    ' . $i . '</a> ';
    }
}
echo'</p>';
echo '<h1>Sujet&nbsp;: '.stripslashes(htmlspecialchars($data['topic_titre'])).'</h1>';
 
$premierMessageAafficher = ($page - 1) * $nombreDeMessagesParPage;

 
//On affiche l'image répondre
if (verif_auth($data['auth_post']) && $data['topic_genre']<>"Annonce")
{echo'<a href="./poster.php?action=repondre&amp;t='.$topic.'">
<img src="./images/repondre.gif" alt="Répondre" title="Répondre à ce sujet" /></a>&nbsp;';}
if ($data['topic_genre']=="Annonce") echo "Il n'est pas pr&eacute;vu de r&eacute;pondre &agrave; une annonce.";


echo "&nbsp;";
//On affiche l'image nouveau message
if (verif_auth($data['auth_topic']))
{echo'<a href="./poster.php?action=nouveaumessage&amp;t='.$topic.'">
<img src="./images/nouveau.gif" alt="Nouveau message" title="Poster un nouveau message" /></a>';}
$query->closeCursor(); 


echo "&nbsp;";
//On affiche l'image nouveau message
if ($id)
{echo'&nbsp;<a href="./favoris.php?action=ranger&amp;m='.$id.'&amp;t='.$topic.'&amp;f='.$forum.'">Ranger dans vos liens</a>&nbsp;
	<a href="./messagesprives.php?action=alauteur&amp;a='.$data['topic_last_post'].'">Message personnel &agrave; l\'auteur</a>';}
//on envoie un message au dernier intervenant. Sinon topic_createur...



//Enfin on commence la boucle !
$query=$db->prepare('SELECT post_id , post_createur , post_texte , post_time ,
membre_id, membre_pseudo, membre_inscrit, membre_avatar, membre_localisation, membre_post, membre_signature
FROM forum_post
LEFT JOIN forum_membres ON forum_membres.membre_id = forum_post.post_createur
WHERE topic_id =:topic
ORDER BY post_id
LIMIT :premier, :nombre');
$query->bindValue(':topic',$topic,PDO::PARAM_INT);
$query->bindValue(':premier',(int) $premierMessageAafficher,PDO::PARAM_INT);
$query->bindValue(':nombre',(int) $nombreDeMessagesParPage,PDO::PARAM_INT);
try{$query->execute();}
catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
 
//On vérifie que la requête a bien retourné des messages
if ($query->rowCount()<1)
{
        erreur('Il n\'y a aucun post sur ce sujet');
}
else
{
        //Si tout roule on affiche notre tableau puis on remplit avec une boucle
?>
        <table>
        <tr>
        <th class="vt_auteur"><strong>Auteurs</strong></th>             
        <th class="vt_mess"><strong>Messages</strong></th>       
        </tr>
        <?php
        while ($data = $query->fetch())
        {

//On commence à afficher le pseudo du créateur du message :
         //On vérifie les droits du membre
         //(partie du code commentée plus tard)
if ($data['post_createur'] < 1) $affiche='Visiteur non inscrit';else $affiche='Membre inscrit le '.date(FORMAT_DATE,$data['membre_inscrit']).'
         Messages : '.$data['membre_post'].'
         Localisation : '.stripslashes(htmlspecialchars($data['membre_localisation']));
         echo'<tr><td style="background-color:Moccasin;"  title="'.$affiche.'"><strong>
         <a href="./voirprofil.php?m='.$data['membre_id'].'&amp;action=consulter">
         '.stripslashes(htmlspecialchars($data['membre_pseudo'])).'</a></strong></td>';
           
//         Si on est l'auteur du message, on affiche des liens pour
//         Modérer celui-ci.
//         Les modérateurs pourront aussi le faire
    
         if ($id == $data['post_createur'] || verif_auth(MODO))
         {
         echo'<td id=p_'.$data['post_id'].'>Posté le '.date(FORMAT_DATE,$data['post_time']).'<br/>
         <a href="./poster.php?p='.$data['post_id'].'&amp;action=delete">
         <img src="./images/supprimer.gif" alt="Supprimer"
         title="Supprimer ce message" /></a>   
         <a href="./poster.php?p='.$data['post_id'].'&amp;action=edit">
         <img src="./images/editer.gif" alt="&Eacute;diter"
         title="&Eacute;diter ce message" /></a></td></tr>';
         }
         else
         {
         echo'<td>
         Posté le '.date(FORMAT_DATE,$data['post_time']).'
         </td></tr>';
         }
       
         //Détails sur le membre qui a posté
         echo'';
               
         //Message
         echo '<tr><td style="text-align:left;" colspan=2>'.code(nl2br(stripslashes(htmlspecialchars($data['post_texte'])))).'<hr />'.code(nl2br(stripslashes(htmlspecialchars($data['membre_signature'])))).'</td></tr>';

         } //Fin de la boucle ! \o/
echo "</table>";
         $query->closeCursor();
/*
        echo '<p>Page : ';  
        for ($i = 1 ; $i <= $nombreDePages ; $i++)
        {
                if ($i == $page) //On affiche pas la page actuelle en lien
                {
                echo $i;
                }
                else
                {
                echo '<a href="voirtopic.php?t='.$topic.'&amp;page='.$i.'">
                ' . $i . '</a> ';
                }
        }
        echo'</p>';*/
      
        //On ajoute 1 au nombre de visites de ce topic
        $query=$db->prepare('UPDATE forum_topic
        SET topic_vu = topic_vu + 1 WHERE topic_id = :topic');
        $query->bindValue(':topic',$topic,PDO::PARAM_INT);
        $query->execute();
        $query->closeCursor();
//	}//fin de boucle

} //Fin du if qui vérifiait si le topic contenait au moins un message

//Modération

if (verif_auth(MODO)){
$query = $db->prepare('SELECT topic_locked, topic_last_post FROM forum_topic WHERE topic_id = :topic');
$query->bindValue(':topic',$topic,PDO::PARAM_INT);
$query->execute();
$data=$query->fetch();

$query1=$db->prepare('SELECT forum_id, forum_name FROM forum_forum WHERE forum_id <> :forum');
$query1->bindValue(':forum',$forum,PDO::PARAM_INT);
$query1->execute();

//$forum a été définie tout en haut de la page !
echo'<p>
<form method="post" action=postok.php?action=deplacer&amp;t='.$topic.'>
<label>Déplacer le sujet vers&nbsp;: </label>
<select name="dest"></p>';               
while($data0=$query1->fetch())
{
     echo'<option value='.$data0['forum_id'].' id='.$data0['forum_id'].'>'.$data0['forum_name'].'</option>';
}
$query1->closeCursor();
echo'
</select>
<input type="hidden" name="from" value='.$forum.'>
<input type="submit" name="submit" value="Envoyer" />
</form>';

if ($data['topic_locked'] == '1')  // Topic verrouillé !
{
    echo'<a href="./postok.php?action=unlock&t='.$topic.'">
    <img src="./images/unlock.gif" alt="R&eacute;activer" title="R&eacute;activer ce sujet" /></a>';
}
else //Sinon le topic est déverrouillé !
{
    echo'<a href="./postok.php?action=lock&amp;t='.$topic.'">
    <img src="./images/lock.gif" alt="Clore ce sujet" title="Clore ce sujet" /></a>';
}
$query->closeCursor();
}

//gestion lu/non lu
if ($id>0){
//echo $id."   ".$topic."   ".$forum."   >";
//echo $last_post."<  ";die;
//Topic déjà consulté ?
$query=$db->prepare('SELECT COUNT(*) FROM forum_topic_view WHERE tv_topic_id = :topic AND tv_id = :id');
$query->bindValue(':topic',$topic,PDO::PARAM_INT);
$query->bindValue(':id',$id,PDO::PARAM_INT);
$query->execute();
$nbr_vu=$query->fetchColumn();
$query->closeCursor();
if ($nbr_vu == 0){ //Si c'est la première fois on insère une ligne entière
    $query=$db->prepare('INSERT INTO forum_topic_view 
    (tv_id, tv_topic_id, tv_forum_id, tv_post_id)
    VALUES (:id, :topic, :forum, :last_post)');
    $query->bindValue(':id',$id,PDO::PARAM_INT);
    $query->bindValue(':topic',$topic,PDO::PARAM_INT);
    $query->bindValue(':forum',$forum,PDO::PARAM_INT);
    $query->bindValue(':last_post',$last_post,PDO::PARAM_INT);
    $query->execute();
    $query->closeCursor();}
else {//Sinon, on met simplement à jour
    $query=$db->prepare('UPDATE forum_topic_view SET tv_post_id = :last_post 
    WHERE tv_topic_id = :topic 
    AND tv_id = :id');
    $query->bindValue(':last_post',$last_post,PDO::PARAM_INT);
    $query->bindValue(':topic',$topic,PDO::PARAM_INT);
    $query->bindValue(':id',$id,PDO::PARAM_INT);
    $query->execute();
    $query->closeCursor();}
}
echo '</div>';
include("../inclus/fin.php");
?>           

