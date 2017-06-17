<?php
include('includes/constants.php');
include("includes/identifiants.php");
include('includes/start_session.php');
$titre = "Un forum de discussions";
include("includes/debut_forum.php");
include("includes/menu_forum.php");

//On récupère la valeur de f
$forum = (int) $_GET['f'];
//echo $forum;die;
//A partir d'ici, on va compter le nombre de messages
//pour n'afficher que les 25 premiers
$query=$db->prepare('SELECT forum_name, forum_topic, auth_view, auth_topic FROM forum_forum WHERE forum_id = :forum');
$query->bindValue(':forum',$forum,PDO::PARAM_INT);
$query->execute();
$data=$query->fetch();

if (!verif_auth($data['auth_view'])){
erreur(ERR_AUTH_VIEW);}

$totalDesMessages = $data['forum_topic'] + 1;
$nombreDeMessagesParPage = 25;
$nombreDePages = ceil($totalDesMessages / $nombreDeMessagesParPage);
echo '<p><i>Vous êtes ici</i> : <a href="./index.php">Accueil du forum</a> --> 
<a href="./voirforum.php?f='.$forum.'">'.stripslashes(htmlspecialchars($data['forum_name'])).'</a><br />';
//Nombre de pages

$page = (isset($_GET['page']))?intval($_GET['page']):1;
//On affiche les pages 1-2-3, etc.
echo 'Page : ';
for ($i = 1 ; $i <= $nombreDePages ; $i++)
{
    if ($i == $page) //On ne met pas de lien sur la page actuelle
    {
    echo $i;
    }
    else
    {
    echo '
    <a href="voirforum.php?f='.$forum.'&amp;page='.$i.'">'.$i.'</a>';
    }
}
echo '</p>';

$premierMessageAafficher = ($page - 1) * $nombreDeMessagesParPage;
//Le titre du forum
echo '<h1>Forum&nbsp;: '.stripslashes(htmlspecialchars($data['forum_name'])).'</h1>';

//Et le bouton pour poster
if (verif_auth($data['auth_topic'])){
echo'<a href="./poster.php?action=nouveautopic&amp;f='.$forum.'">
<img src="./images/nouveau.gif" alt="Nouveau sujet" title="Poster un nouveau sujet" /></a>';}
$query->closeCursor();


$add1='';
$add2='';
if ($id!=0) {//on est connecté 
//Premièrement, sélection des champs
$add1 = 'tv_id, tv_post_id, tv_poste, '; 
//Deuxièmement, jointure
$add2 = 'LEFT JOIN forum_topic_view 
ON forum_topic.topic_id = forum_topic_view.tv_topic_id AND forum_topic_view.tv_id = :id';}

//On prend tout ce qu'on a sur les Annonces du forum
$query=$db->prepare('SELECT forum_topic.topic_id, topic_titre, topic_createur, topic_vu, topic_post, topic_time, topic_last_post, topic_locked, topic_locked_time,
Mb.membre_pseudo AS membre_pseudo_createur, post_createur, post_time, Ma.membre_pseudo AS membre_pseudo_last_posteur, 
'.$add1.'post_id FROM forum_topic 
LEFT JOIN forum_membres Mb ON Mb.membre_id = forum_topic.topic_createur
LEFT JOIN forum_post ON forum_topic.topic_last_post = forum_post.post_id
LEFT JOIN forum_membres Ma ON Ma.membre_id = forum_post.post_createur
'.$add2.'
WHERE topic_genre = "Annonce" AND forum_topic.forum_id = :forum
ORDER BY topic_last_post DESC');
$query->bindValue(':forum',$forum,PDO::PARAM_INT);
if ($id!=0) $query->bindValue(':id',$id,PDO::PARAM_INT);
try{$query->execute();}
catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
//On lance notre tableau seulement s'il y a des requêtes !
if ($query->rowCount()>0)
{
        ?>
        <table>   
        <tr>
        <th><img src="./images/annonce.gif" alt="Annonce" /></th>
        <th class="titre"><strong>Titre</strong></th>             
        <th class="nombrevu"><strong>Vus</strong></th>
        <th class="auteur"><strong>Auteur</strong></th>                       
        <th class="derniermessage"><strong>Dernier message</strong></th>
        </tr>   
       
        <?php

        //On commence la boucle
        while ($data=$query->fetch())
        {
                //Pour chaque topic :
                //Si le topic est une annonce on l'affiche en haut
                //mega echo de bourrain pour tout remplir
               
                $rep=$data['topic_post']--;
                echo'<tr><td><img src="./images/annonce.gif" alt="Annonce" style="background:orange;"/></td>
                <td id="titre"><strong>Annonce : </strong>
                <strong><a href="./voirtopic.php?t='.$data['topic_id'].'"                 
                title="Sujet commencé le
                '.date(FORMAT_DATE,$data['topic_time']).'">
                '.stripslashes(htmlspecialchars($data['topic_titre'])).'</a></strong></td>
                <td class="nombremessages">'.$rep.'</td>
                <td><a href="./voirprofil.php?m='.$data['topic_createur'].'
                &amp;action=consulter">
                '.stripslashes(htmlspecialchars($data['membre_pseudo_createur'])).'</a></td>';
               	//Selection dernier message
		$nombreDeMessagesParPage = 15;
		$nbr_post = $data['topic_post'] +1;
		$page = ceil($nbr_post / $nombreDeMessagesParPage);
                echo '<td class="derniermessage">Par
                <a href="./voirprofil.php?m='.$data['post_createur'].'
                &amp;action=consulter">
                '.stripslashes(htmlspecialchars($data['membre_pseudo_last_posteur'])).'</a><br />
                le <a href="./voirtopic.php?t='.$data['topic_id'].'&amp;page='.$page.'#p_'.$data['post_id'].'">'.date(FORMAT_DATE,$data['post_time']).'</a></td></tr>';
        }
        ?>
        </table>
        <?php
}
$query->closeCursor();

//On prend tout ce qu'on a sur les topics normaux du forum
$query=$db->prepare('SELECT forum_topic.topic_id, topic_titre, topic_createur, topic_vu, topic_post, topic_time, topic_last_post, topic_locked, topic_locked_time,
Mb.membre_pseudo AS membre_pseudo_createur, post_createur, post_time, Ma.membre_pseudo AS membre_pseudo_last_posteur, 
'.$add1.'post_id FROM forum_topic
LEFT JOIN forum_membres Mb ON Mb.membre_id = forum_topic.topic_createur
LEFT JOIN forum_post ON forum_topic.topic_last_post = forum_post.post_id
LEFT JOIN forum_membres Ma ON Ma.membre_id = forum_post.post_createur
'.$add2.'
WHERE topic_genre <> "Annonce" AND forum_topic.forum_id = :forum
ORDER BY topic_last_post DESC
LIMIT :premier ,:nombre');
if ($id!=0) $query->bindValue(':id',$id,PDO::PARAM_INT);
$query->bindValue(':forum',$forum,PDO::PARAM_INT);
$query->bindValue(':premier',(int) $premierMessageAafficher,PDO::PARAM_INT);
$query->bindValue(':nombre',(int) $nombreDeMessagesParPage,PDO::PARAM_INT);
try{$query->execute();}
catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
if ($query->rowCount()>0)
{
?>
        <table>
        <tr>
        <th>Lu/non lu</th>
        <th class="titre"><strong>Sujets</strong></th>             
        <th class="nombremessages"><strong>R&eacute;ponses</strong></th>
        <th class="nombrevu"><strong>Vus</strong></th>
        <th class="auteur"><strong>Auteur</strong></th>                       
        <th class="derniermessage"><strong>Dernier message  </strong></th>
        </tr>
        <?php
        //On lance la boucle
       
        while ($data = $query->fetch())
        {

//if ($data['topic_locked']) echo "bloqué";else echo "libre";
                //Ah bah tiens... re vla l'echo de fou
                $rep=$data['topic_post']-1;

if (!empty($id)) {// Si le membre est connecté
    if ($data['tv_id'] == $id) {//S'il a seulement lu le topic
        if ($data['tv_poste'] == '0') {// S'il n'a pas posté
            if ($data['tv_post_id'] == $data['topic_last_post']) {//S'il n'y a pas de nouveau message
                $ico_mess = 'message.gif';$alt="L&agrave;Rep";$color='red';$bgcolor='green';}
            else {
                $ico_mess = 'messagec_non_lus.gif';$alt="LNouv";$color='red';$bgcolor='#ffc0cb';} //S'il y a un nouveau message
        } else {// S'il a  posté
            if ($data['tv_post_id'] == $data['topic_last_post']) {//S'il n'y a pas de nouveau message
               $ico_mess = 'messagep_lu.gif';$alt="LR";$color='black';$bgcolor='green';}
            else {//S'il y a un nouveau message
                $ico_mess = 'messagep_non_lu.gif';$alt="LRNouv";$color='black';$bgcolor='#ffc0cb';}
        }
    } else {//S'il n'a pas lu le topic
        $ico_mess = 'message_non_lu.gif';$alt="NonL";$color='white';$bgcolor='red';}
} //S'il n'est pas connecté
else {
    $ico_mess = 'message.gif';$alt="";$bgcolor='grey';}
$titre=stripslashes(htmlspecialchars($data['topic_titre']));
if ($data['topic_locked']>0) $titre=$titre.'&nbsp;<img src="/commun/cadenas.png" title="Sujet clos" width="16px"/>';

                echo'<tr><td style="text-align:center;font-weight:bold;color:'.$color.';background:'.$bgcolor.';">';
                //Gestion de l'image à afficher
                echo '
                <img src="/images/'.$ico_mess.'" alt="'.$alt.'" />
                </td>
                <td class="titre">
                <strong><a href="./voirtopic.php?t='.$data['topic_id'].'"                 
                title="Sujet commencé le
                '.date(FORMAT_DATE,$data['topic_time']).'">
                '.$titre.'</a></strong></td>
                <td class="nombremessages">'.$rep.'</td>

                <td class="nombrevu">'.$data['topic_vu'].'</td>

                <td><a href="./voirprofil.php?m='.$data['topic_createur'].'
                &amp;action=consulter">
                '.stripslashes(htmlspecialchars($data['membre_pseudo_createur'])).'</a></td>';
               	//Selection dernier message
		$nombreDeMessagesParPage = 15;
		$nbr_post = $data['topic_post'] +1;
		$page = ceil($nbr_post / $nombreDeMessagesParPage);
                if ($data['topic_locked'])
                echo '<td class="derniermessage">
                Sujet clos<br />
                le '.date(FORMAT_DATE,$data['post_time']).'</td></tr>';
                else
                echo '<td class="derniermessage">Par
                <a href="./voirprofil.php?m='.$data['post_createur'].'
                &amp;action=consulter">
                '.stripslashes(htmlspecialchars($data['membre_pseudo_last_posteur'])).'</a><br />
                le <a href="./voirtopic.php?t='.$data['topic_id'].'&amp;page='.$page.'#p_'.$data['post_id'].'">'.date(FORMAT_DATE,$data['post_time']).'</a></td></tr>';
        }
        ?>
        </table>
      <?php
}
else //S'il n'y a pas de message
{
        echo'<p>Ce forum ne contient aucun sujet actuellement</p>';
}
$query->closeCursor();
echo '</div>';
include("../inclus/fin.php");
?>

