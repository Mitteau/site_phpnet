<?php
include('includes/constants.php');
include("includes/identifiants.php");
include('includes/start_session.php');
$titre = "&Eacute;crire";
include("includes/debut_forum.php");
include("includes/menu_forum.php");

//Qu'est ce qu'on veut faire ? poster, répondre ou éditer ?
//Il faut être connecté pour poster !
$action = (isset($_GET['action']))?htmlspecialchars($_GET['action']):''; 
//Si on veut poster un nouveau topic, la variable f se trouve dans l'url,
//On récupère certaines valeurs
if (isset($_GET['f']))//nouveau sujet
{
    $forum = (int) $_GET['f'];
    $query= $db->prepare('SELECT forum_name, forum_id, auth_view, auth_post, auth_topic, auth_annonce, auth_modo
    FROM forum_forum WHERE forum_id =:forum');
    $query->bindValue(':forum',$forum,PDO::PARAM_INT);
try{$query->execute();}
catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
    $data=$query->fetch();
if ($id==0 && verif_auth($data['auth_post'])) erreur(ERR_IS_NOT_CO);
    echo '<p><i>Vous êtes ici</i> : <a href="./index.php">Index du forum</a> --> 
    <a href="./voirforum.php?f='.$data['forum_id'].'">'.stripslashes(htmlspecialchars($data['forum_name'])).'</a>
    --> Nouveau sujet</p>'; 
}//nouveau sujet 


//Sinon c'est un nouveau message, on a la variable t et
//On récupère f grâce à une requête
elseif (isset($_GET['t']))
{
    $topic = (int) $_GET['t'];
    $query=$db->prepare('SELECT topic_titre, forum_topic.forum_id,
    forum_name, auth_view, auth_post, auth_topic, auth_annonce, auth_modo
    FROM forum_topic
    LEFT JOIN forum_forum ON forum_forum.forum_id = forum_topic.forum_id
    WHERE topic_id =:topic');
    $query->bindValue(':topic',$topic,PDO::PARAM_INT);
try{$query->execute();}
catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
    $data=$query->fetch();
    $forum = $data['forum_id'];   
    echo '<p><i>Vous êtes ici</i> : <a href="./index.php">Index du forum</a> --> 
    <a href="./voirforum.php?f='.$data['forum_id'].'">'.stripslashes(htmlspecialchars($data['forum_name'])).'</a>
    --> <a href="./voirtopic.php?t='.$topic.'">'.stripslashes(htmlspecialchars($data['topic_titre'])).'</a>
    --> Répondre</p>';
}

//Enfin sinon c'est au sujet de la modération(on verra plus tard en détail)
//On ne connait que le post, il faut chercher le reste
elseif (isset ($_GET['p']))
{
    $post = (int) $_GET['p'];
    $query=$db->prepare('SELECT post_createur, forum_post.topic_id, topic_titre, forum_topic.forum_id,
    forum_name, auth_view, auth_post, auth_topic, auth_annonce, auth_modo, post_texte, topic_genre
    FROM forum_post
    LEFT JOIN forum_topic ON forum_topic.topic_id = forum_post.topic_id
    LEFT JOIN forum_forum ON forum_forum.forum_id = forum_topic.forum_id
    WHERE forum_post.post_id =:post');
    $query->bindValue(':post',$post,PDO::PARAM_INT);
try{$query->execute();}
catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
    $data=$query->fetch(); 
    $topic = $data['topic_id'];
    $forum = $data['forum_id'];
    $autor = $data['post_createur'];
    echo '<p><i>Vous êtes ici</i> : <a href="./index.php">Index du forum</a> --> 
    <a href="./voirforum.php?f='.$data['forum_id'].'">'.stripslashes(htmlspecialchars($data['forum_name'])).'</a>
    --> <a href="./voirtopic.php?t='.$topic.'">'.stripslashes(htmlspecialchars($data['topic_titre'])).'</a>
    --> Modérer un message</p>';
}
$query->closeCursor();   


switch($action)
{
case "repondre": {//Premier cas on souhaite répondre
?>
<h1>Poster une réponse</h1>
 
<form method="post" action="postok.php?action=repondre&amp;t=<?php echo $topic ?>" name="formulaire">
<?php include 'includes/editeur.php' ?>
<input type="submit" name="submit" value="Envoyer" />
<input type="reset" name = "Effacer" value = "Effacer"/>
</p></form>

<?php
// affichage des messages du topic
	$query->closeCursor();
	$query = $db->prepare('SELECT  post_id, post_createur, post_texte, post_time, topic_id, membre_id,
		membre_pseudo, membre_avatar,
		membre_localisation, membre_inscrit, membre_post, membre_signature
		FROM forum_post
		LEFT JOIN forum_membres ON membre_id = post_createur
		WHERE topic_id = :topic_id
		ORDER BY post_time DESC
');


	 $query->bindValue(':topic_id',$topic,PDO::PARAM_INT);
	 try {$query->execute();}
	 catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
echo "<p style=\"text-align:left\"><i>Message(s) re&ccedil;u(s)&nbsp;:</i></p>";
	while ($data=$query->fetch()){
//	print_r($data);echo "xxx<br>";
echo "<table>     
	  <tr>
	  <th class=\"vt_auteur\"><strong>Auteur</strong></th>             
	  <th class=\"vt_mess\"><strong>Message</strong></th>       
	  </tr>
	  <tr>
	  <td>";
echo'<strong>
    <a href="./voirprofil.php?m='.$data['membre_id'].'&amp;action=consulter">
    '.stripslashes(htmlspecialchars($data['membre_pseudo'])).'</a></strong></td>
    <td>&nbsp;post&eacute; &agrave; '.date(FORMAT_DATE,$data['post_time']).'</td>';
    ?>
    </tr>
    <tr>
    <td colspan=2>
<?php    //Ici des infos sur le membre qui a envoyé le mp
//    echo'<p><img src="./images/avatars/'.$data['membre_avatar']"." 'alt='$data['membre_pseudo']" />
//<p><img src="/images/avatars/".$data['membre_avatar'] />
//<!--    <p><img src="/images/avatars/"'.$data[\'membre_avatar\'].'" alt='.$data['membre_pseudo'].' />-->
    echo code(nl2br(stripslashes(htmlspecialchars($data['post_texte'])))).'
    <hr />'.code(nl2br(stripslashes(htmlspecialchars($data['membre_signature'])))).'
    </td></tr></table>';
	}	





}
break;

case "nouveautopic":{
?>
 
<h1>Nouveau sujet</h1>

<form method="post" action="postok.php?action=nouveautopic&amp;f=<?php echo $forum ?>" name="formulaire">
<input type="hidden" name="forum" value="9
<!--<?php echo $forum;?>-->
"/> 

<fieldset><legend>Titre du sujet</legend>
<input type="text" size="80" id="titre" name="titre" /></fieldset>
<?php include 'includes/editeur.php' ?>
<label><input type="radio" name="mess" value="Annonce" />Annonce</label>
<label><input type="radio" name="mess" value="Message" checked="checked" />Sujet</label>
</fieldset>
<p>
<input type="submit" name="submit" value="Envoyer" />
<input type="reset" name = "Effacer" value = "Effacer" /></p>
</form>
<?php
}
break;
case "nouveaumessage":{
?>
 
<h1>Nouveau message</h1>

<form method="post" action="postok.php?action=nouveaumessage&amp;t=<?php echo $topic ?>" name="formulaire">
<input type="hidden" name="forum" value="9
<!--<?php echo $forum;?>-->
"/> 
<fieldset><legend>Titre du sujet</legend>
<input type="text" size="80" id="titre" name="titre" /></fieldset>
<?php include 'includes/editeur.php' ?>
<label><input type="radio" name="mess" value="Annonce" />Annonce</label>
<label><input type="radio" name="mess" value="Message" checked="checked" />Sujet</label>

<p>
<input type="submit" name="submit" value="Envoyer" />
<input type="reset" name = "Effacer" value = "Effacer" />
<!--<button type="button" onclick="alert('pièce jointe')">Ajouter une pièce jointe</button></p>-->
</form>
<?php
}
break;
case "edit":{
	if (verif_auth(MODO) || ($id == $autor)){
    $post = (int) $_GET['p'];

    //On lance notre requête
    $query=$db->prepare('SELECT post_createur, forum_post.topic_id, post_texte, auth_modo, forum_topic.topic_genre
    FROM forum_post
    LEFT JOIN forum_forum ON forum_post.post_forum_id = forum_forum.forum_id
    LEFT JOIN forum_topic ON forum_topic.topic_id = forum_post.topic_id
    WHERE post_id=:post');
    $query->bindValue(':post',$post,PDO::PARAM_INT);
    $query->execute();
    $data=$query->fetch();
    $text_edit = $data['post_texte']; //On récupère le message
    $query->closeCursor();
?>
	<h1>Corrections</h1>

<form method="post" action="postok.php?action=editer&amp;p=<?php echo $post ?>" name="formulaire">
 
<fieldset><legend>Titre</legend>
<!--<input type="text" size="80" id="titre" name="titre" /></fieldset>-->
<?php

$_SESSION['message']=$text_edit;
include 'includes/editeur.php';
$_SESSION['message']="";
?>



<?php
if ($data['topic_genre'] == "Message"){?>
<label><input type="radio" name="mess" value="Annonce" disabled />Annonce</label>
<label><input type="radio" name="mess" value="Message" checked="checked" disabled />Sujet</label>
<?php }else if ($data['topic_genre'] == "Annonce"){?>
<label><input type="radio" name="mess" value="Annonce" checked="checked" disabled  />Annonce</label>
<label><input type="radio" name="mess" value="Message"disabled />Sujet</label>
<?php }?>



<p>
<input type="submit" name="submit" value="Envoyer" />
<input type="reset" name = "Effacer" value = "Effacer" /></p>
</form>
<?php

	}//vérfication accès ok
	else erreur(ERR_AUTH_EDIT);
}

break;


case "delete": //Si on veut supprimer le post
    //On récupère la valeur de p
    $post = (int) $_GET['p'];
    //Ensuite on vérifie que le membre a le droit d'être ici
    echo'<h1>Suppression</h1>';
    $query=$db->prepare('SELECT post_createur, auth_modo
    FROM forum_post
    LEFT JOIN forum_forum ON forum_post.post_forum_id = forum_forum.forum_id
    WHERE post_id= :post');
    $query->bindValue(':post',$post,PDO::PARAM_INT);
    $query->execute();
    $data = $query->fetch();
 
    if (!verif_auth($data['auth_modo']) && $data['post_createur'] != $id)
    {
        // Si cette condition n'est pas remplie ça va barder :o
        erreur(ERR_AUTH_DELETE); 
    }
    else //Sinon ça roule et on affiche la suite
    {
        echo'<p>Êtes vous certains de vouloir supprimer ce message ?</p>';
        echo'<p><a href="./postok.php?action=delete&amp;p='.$post.'">Oui</a> ou <a href="./index.php">Non</a></p>';
    }
    $query->CloseCursor();
break;
 //D'autres cas viendront s'ajouter ici par la suite 

default: //Si jamais c'est aucun de ceux là c'est qu'il y a eu un problème : 
echo'<p>Cette action est impossible</p>';
} //Fin du switch 
//}
//echo "</div>";*/
echo '</div>';
include("../inclus/fin.php");
?>

