<?php
include('includes/constants.php');
include("includes/identifiants.php");
include('includes/start_session.php');
$titre = "Messages personnels";
include("includes/debut_forum.php");
include("includes/menu_forum.php");

if (!$db) erreur("pas de base");
if (!$id) erreur(ERR_IS_NOT_CO);

$query=$db->prepare('SELECT mp_id FROM forum_mp
    WHERE mp_receveur = :id');
$query->bindValue(':id',$id,PDO::PARAM_INT);
$query->execute();
$totalDesMessages=0;
while ($data=$query->fetch())$totalDesMessages++;
if (!$totalDesMessages && $_GET['action']!="nouveau"){echo 'Vous n\'avez aucun message.';
echo '<br><a href="./messagesprives.php?action=nouveau">
	 <img src="./images/nouveau.gif" alt="" 
	  title="Nouveau message" >Envoyer une nouveau message.</a>';die;}
//	  <a href="./messagesprives.php?action=nouveau">
//	 <img src="./images/nouveau.gif" alt="Nouveau message" 
//	  title="Supprimer ce message" /></a>

$action = (isset($_GET['action']))?htmlspecialchars($_GET['action']):'';
$id_mess= (isset($_GET['id']))?htmlspecialchars($_GET['id']):'';
switch($action) //On switch sur $action
{
//XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
	case "consulter": //Si on veut lire un message
 
 
	if (!$id_mess){
 
	 //$totalDesMessages = $data['forum_mp'] + 1;
	 //echo $totalDesMessages;die;
	 $nombreDeMessagesParPage = 12;
	 $nombreDePages = ceil($totalDesMessages / $nombreDeMessagesParPage);
	 //echo $nombreDePages;
	 echo '<p><i>Vous êtes ici</i> : <a href="./index.php">Index du forum</a> --> 
	 <a href="./messagesprives.php?action=consulter&id=".$id>Messages personnels</a>';
	 //Nombre de pages

	 $page = (isset($_GET['page']))?intval($_GET['page']):1;
	 //On affiche les pages 1-2-3, etc.
	 echo '<br>Page : ';
	 for ($i = 1 ; $i <= $nombreDePages ; $i++)
	 {
	  if ($i == $page) //On ne met pas de lien sur la page actuelle
	   echo $i;
	  else
	   echo '<a href="messagesprives.php?page='.$i.'">'.$i.'</a>';
	  echo '<br>';
	 }
	 $premierMessageAafficher = ($page - 1) * $nombreDeMessagesParPage;
	 //Le titre du forum
if ($totalDesMessages)
?>
<h1>Vos messages</h1>
<p><a href="./messagesprives.php?action=nouveau">Nouveau message</a>&nbsp;
<a href="./messagesprives.php">G&eacute;rer</a></p>
<?php
	 
	 $query->closeCursor();

	 $query=$db->prepare('SELECT mp_id, mp_expediteur, mp_receveur, mp_titre,               
		mp_time, mp_text, mp_lu, membre_id, membre_pseudo, membre_avatar,
		membre_localisation, membre_inscrit, membre_post, membre_signature
		FROM forum_mp
		LEFT JOIN forum_membres ON membre_id = mp_expediteur
		WHERE mp_receveur = :id');
	 $query->bindValue(':id',$id,PDO::PARAM_INT);
	 $query->execute();

	 //On lance notre tableau seulement s'il y a des requêtes !
	 //echo "lignes ".$query->rowCount();
	 if ($query->rowCount()>0)
	  {
?>

<!--
membre_avatar(expé
mp_titre
mp_expediteur=membre_pseudo

, mp_receveur, ,               
    mp_time, mp_text, mp_lu, membre_id, , ,
    membre_localisation, membre_inscrit, membre_post, membre_signature-->

	  <table>   
	  <tr>
	   <th><img src="" alt="" /></th>
	   <th class="auteur"><strong>Exp&eacute;diteur</strong></th>                       
	   <th class="titre"><strong>Titre</strong></th>             
	   <th class="date"><strong>Date</strong></th>             
	   <th class="nombrevu"><strong>Vus</strong></th>
<!--        <th class="derniermessage"><strong>Dernier message</strong></th>MAP-->
	  </tr>   
       
<?php

        //On commence la boucle
        while ($data=$query->fetch())
        {
         //Pour chaque message :
          echo'<tr><td><a href="messagesprives.php?action=consulter&amp;id='.$data['mp_id'].'">Voir</a></td>
          <td>'.$data['membre_pseudo'].'</td>
          <td id="titre">'.$data['mp_titre'].'</td>
          <td id="date">'.date('d-m-Y',$data['mp_time']).'</td>
          <td>';
          if ($data['mp_lu'])echo "lu</td></tr>";
        }
        echo '</table>';
}
/*   gestion des pages MAP


		$nombreDeMessagesParPage = 15;
		$nbr_post = $data['topic_post'] +1;
		$page = ceil($nbr_post / $nombreDeMessagesParPage);
                echo '<td class="derniermessage">Par
                <a href="./voirprofil.php?m='.$data['post_createur'].'
                &amp;action=consulter">
                '.stripslashes(htmlspecialchars($data['membre_pseudo_last_posteur'])).'</a><br />
                A <a href="./voirtopic.php?t='.$data['topic_id'].'&amp;page='.$page.'#p_'.$data['post_id'].'">'.date('H\hi \l\e d M y',$data['post_time']).'</a></td></tr>';
        }
        ?>
        <?php
}
*/
        $query->closeCursor();
//XXXXXXXXXXXXXXXXXXX
	} else { //if mp_id Cas où un message a été choisi
     $id_mess = (int) $_GET['id']; //On récupère la valeur de l'id
	 //La requête nous permet d'obtenir les infos sur ce message :
 	 $query = $db->prepare('SELECT  mp_expediteur, mp_receveur, mp_titre,               
		mp_time, mp_text, mp_lu, membre_id, membre_pseudo, membre_avatar,
		membre_localisation, membre_inscrit, membre_post, membre_signature
		FROM forum_mp
		LEFT JOIN forum_membres ON membre_id = mp_expediteur
		WHERE mp_receveur = :id AND mp_id = :mp_id');


	 $query->bindValue(':id',$id,PDO::PARAM_INT);
	 $query->bindValue(':mp_id',$id_mess,PDO::PARAM_INT);
	 try {$query->execute();}
	 catch (Exception $e){die ('Erreur : ' . $e->getMessage());}

	 $data=$query->fetch();
     echo '<p><i>Vous êtes ici</i> : <a href=\"./index.php\">Index du forum</a> --> <a href="./messagesprives.php?action=consulter">Messagerie privée</a> --> Consulter un message personnel de '.$data['membre_pseudo'].'</p>';
     echo '<h1>Consulter un message</h1>';

	 // Attention ! Seul le receveur du mp peut le lire !
	 if ($id != $data['mp_receveur']) erreur(ERR_WRONG_USER);
       
	 //bouton de réponse MAP mettre un vrai bouton 
	 echo'<p><a href="./messagesprives.php?action=repondre&amp;dest='.$data['mp_expediteur'].'&amp;mess='.$id_mess.'">
	 <img src="./images/repondre.gif" alt="Répondre" 
	  title="Répondre à ce message" /></a>&nbsp;

	 <table>     
	  <tr>
	  <th class=\"vt_auteur\"><strong>Auteur</strong></th>             
	  <th class=\"vt_mess\"><strong>Message</strong></th>       
	  </tr>
	  <tr>
	  <td>';
echo'<strong>
    <a href="./voirprofil.php?m='.$data['mp_expediteur'].'&amp;action=consulter">
    '.stripslashes(htmlspecialchars($data['membre_pseudo'])).'</a></strong></td>
    <td>'.$data['mp_titre'].',&nbsp;post&eacute; &agrave; '.date('H\hi \l\e d M Y',$data['mp_time']).'</td>';
    ?>
    </tr>
    <tr>
    <td colspan="2">
<?php    //Ici des infos sur le membre qui a envoyé le mp
//    echo'<p><img src="./images/avatars/'.$data['membre_avatar']"." 'alt='$data['membre_pseudo']" />
//<p><img src="/images/avatars/".$data['membre_avatar'] />
//<!--    <p><img src="/images/avatars/"'.$data[\'membre_avatar\'].'" alt='.$data['membre_pseudo'].' />-->
/*
    echo'
    <br />Membre inscrit le '.date('d/m/Y',$data['membre_inscrit']).'
    <br />Messages : '.$data['membre_post'].'
    <br />Localisation : '.stripslashes(htmlspecialchars($data['membre_localisation'])).'</p>
    </td><td>';
*/        
    echo code(nl2br(stripslashes(htmlspecialchars($data['mp_text'])))).'
    <hr />'.code(nl2br(stripslashes(htmlspecialchars($data['membre_signature'])))).'
    </td></tr></table>';


    if ($data['mp_lu'] == 0) //Si le message n'a jamais été lu
    {
        $query->closeCursor();
        $query=$db->prepare('UPDATE forum_mp 
        SET mp_lu = :lu
        WHERE mp_id= :id');
        $query->bindValue(':id',$id_mess, PDO::PARAM_INT);
        $query->bindValue(':lu','1', PDO::PARAM_STR);
        $query->execute();
        $query->closeCursor();
    }
	}
break; //La fin !

//XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
case "nouveau": //Nouveau mp
       
   echo'<p><i>Vous êtes ici</i> : <a href="./index.php">Index du forum</a> --> <a href="./messagesprives.php">Messagerie priv&eacute;e</a> --> Ecrire un message</p>';
   echo '<h1>Nouveau message privé</h1><br /><br />';
   ?>
   <form method="post" action="postok.php?action=nouveaump" name="formulaire">
   <p>
   <label for="to">Envoyer à : </label>
   <input type="text" size="30" id="to" name="to" />
   <br />
   <label for="titre">Titre : </label>
   <input type="text" size="77" id="titre" name="titre" />
   <br /><br />
<?php include 'includes/editeur.php' ?>
   <br />
   <input type="submit" name="submit" value="Envoyer" />
   <input type="reset" name="Effacer" value="Effacer" /></p>
   </form>
<?php
break;
/*
*/
//XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
case "repondre": //On veut répondre - rajouter les messages auquel on répond
	echo'<p><i>Vous êtes ici</i> : <a href="./index.php">Accueil du forum</a> --> <a href="./messagesprives.php&action=consulter">Messagerie privée</a> --> Ecrire un message</p>';
	echo '<h1>Ré&eacute;ondre à un message priv&eacute;</h1>';
	//La requête nous permet d'obtenir les infos sur ce message :
 
     $id_mess = (int) $_GET['mess']; //On récupère la valeur de l'id
     
	$query = $db->prepare('SELECT  mp_expediteur, mp_receveur, mp_titre,               
		mp_time, mp_text, mp_lu, membre_id, membre_pseudo, membre_avatar,
		membre_localisation, membre_inscrit, membre_post, membre_signature
		FROM forum_mp
		LEFT JOIN forum_membres ON membre_id = mp_expediteur
		WHERE mp_receveur = :id AND mp_id = :mp_id');


	 $query->bindValue(':id',$id,PDO::PARAM_INT);
	 $query->bindValue(':mp_id',$id_mess,PDO::PARAM_INT);
	 try {$query->execute();}
	 catch (Exception $e){die ('Erreur : ' . $e->getMessage());}

	 $data=$query->fetch();
	 $query->closeCursor();
	 echo 'Expéditeur&nbsp;: '.$data['membre_pseudo'];

echo '<script type="text/javascript">document.getElementById("titre_rep").innerHTML='.$data['membre_pseudo'].'</script>';

	$dest = (int) $_GET['dest'];
	$titremess="[Re]&nbsp;: ".$data['mp_titre'];
?>
	<form method="post" action="postok.php?action=repondremp&amp;dest=<?php echo $dest ?>&amp;m=<?php echo $id_mess ?>" name="formulaire">
	<p>
<?php
	echo '<label for="titre" style="text-align:initial">Titre&nbsp;: </label>';
?>
	<input type="text" size="77" name="titre" value="
<?php
	echo $titremess;
?>
	" />



<?php include 'includes/editeur.php' ?>
	<br />
	<input type="submit" name="submit" value="Envoyer" />
	<input type="reset" name="Effacer" value="Effacer"/>
	</p></form>

<?php
//on réaffiche le message en dessous

	 // Attention ! Seul le receveur du mp peut le lire !
	 if ($id != $data['mp_receveur']) erreur(ERR_WRONG_USER);
       
echo "<p style=\"text-align:left\">Message re&ccedil;u&nbsp;:</p>
	 <table>     
	  <tr>
	  <th class=\"vt_auteur\"><strong>Auteur</strong></th>             
	  <th class=\"vt_mess\"><strong>Message</strong></th>       
	  </tr>
	  <tr>
	  <td>";
echo'<strong>
    <a href="./voirprofil.php?m='.$data['mp_expediteur'].'&amp;action=consulter">
    '.stripslashes(htmlspecialchars($data['membre_pseudo'])).'</a></strong></td>
    <td>'.$data['mp_titre'].',&nbsp;post&eacute; &agrave; '.date('H\hi \l\e d M Y',$data['mp_time']).'</td>';
    ?>
    </tr>
    <tr>
    <td colspan=2>
<?php    //Ici des infos sur le membre qui a envoyé le mp
//    echo'<p><img src="./images/avatars/'.$data['membre_avatar']"." 'alt='$data['membre_pseudo']" />
//<p><img src="/images/avatars/".$data['membre_avatar'] />
//<!--    <p><img src="/images/avatars/"'.$data[\'membre_avatar\'].'" alt='.$data['membre_pseudo'].' />-->
    echo code(nl2br(stripslashes(htmlspecialchars($data['mp_text'])))).'
    <hr />'.code(nl2br(stripslashes(htmlspecialchars($data['membre_signature'])))).'
    </td></tr></table>';


break;

//XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
case "alauteur": //On veut écrire en privé à l'auteur
	echo'<p><i>Vous êtes ici</i> : <a href="./index.php">Accueil du forum</a> --> <a href="./messagesprives.php&action=consulter">Messagerie priv&eacute;e</a> --> &Eacute;crire un message</p>';
	
$aut = (int) $_GET['a']; //On récupère la valeur de l'id

$query=$db->prepare('SELECT membre_pseudo
FROM forum_post
LEFT JOIN forum_membres ON membre_id=post_createur
WHERE post_id=:post
');	
        $query->bindValue(':post',$aut,PDO::PARAM_INT);
        $query->execute();
        $data=$query->fetch();
        $query->CloseCursor(); 
//	echo $data['membre_pseudo'];die;
	
	echo '<h1>&Eacute;crire &agrave; l\'auteur</h1>';
	//La requête nous permet d'obtenir les infos sur ce message :
echo '<script type="text/javascript">document.getElementById("titre_rep").innerHTML='.$data['membre_pseudo'].'</script>';
    ?>

   <form method="post" action="postok.php?action=nouveaump" name="formulaire">
   <p>
   <input type="hidden" size="30" id="to" name="to" value="<?php echo $data['membre_pseudo'];?>"/>
   <label for="titre">Titre : </label>
   <input type="text" size="77" id="titre" name="titre" autofocus />
   <br /><br />
<?php include 'includes/editeur.php' ?>
   <br />
   <input type="submit" name="submit" value="Envoyer" />
   <input type="reset" name="Effacer" value="Effacer" /></p>
   </form>

<?php

/*     
	$query = $db->prepare('SELECT  mp_expediteur, mp_receveur, mp_titre,               
		mp_time, mp_text, mp_lu, membre_id, membre_pseudo, membre_avatar,
		membre_localisation, membre_inscrit, membre_post, membre_signature
		FROM forum_mp
		LEFT JOIN forum_membres ON membre_id = mp_expediteur
		WHERE mp_expediteur = :aut AND mp_id = :mp_id');


	 $query->bindValue(':id',$id,PDO::PARAM_INT);
	 $query->bindValue(':mp_id',$id_mess,PDO::PARAM_INT);
	 try {$query->execute();}
	 catch (Exception $e){die ('Erreur : ' . $e->getMessage());}

	 $data=$query->fetch();
	 $query->closeCursor();
	 echo 'Expéditeur&nbsp;: '.$data['membre_pseudo'];
*/


break;

//XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
    case "supprimer":
       
    //On récupère la valeur de l'id
    $id_mess = (int) $_GET['id'];
    
//echo $id_mess."   ";    
    
    //Il faut vérifier que le membre est bien celui qui a reçu le message
    $query=$db->prepare('SELECT mp_receveur
    FROM forum_mp WHERE mp_id = :id');
    $query->bindValue(':id',$id_mess,PDO::PARAM_INT);
    $query->execute();
    $data = $query->fetch();
    //Sinon la sanction est terrible :p
    
//    echo ">>".$id_mess." - ".$id." - ".$data['mp_receveur'];die;
    
    if ($id != $data['mp_receveur']) erreur(ERR_WRONG_USER);
    $query->closeCursor(); 

    //2 cas pour cette partie : on est sûr de supprimer ou alors on ne l'est pas
    $sur = (int) $_GET['sur'];
    //Pas encore certain
    if ($sur == 0)
    {
    echo'<p>Etes-vous certain de vouloir supprimer ce message ?<br />
    <a href="./messagesprives.php?action=supprimer&amp;id='.$id_mess.'&amp;sur=1">
    Oui</a> - <a href="./messagesprives.php?&action=consulter">Non</a></p>';
    }
    //Certain
    else
    {
        $query=$db->prepare('DELETE from forum_mp WHERE mp_id = :id');
        $query->bindValue(':id',$id_mess,PDO::PARAM_INT);
        $query->execute();
        $query->CloseCursor(); 
        echo'<p>Le message a bien été supprimé.<br />
        Cliquez <a href="./messagesprives.php">ici</a> pour revenir à la boite
        de messagerie.</p>';
    }

    break;

//Si rien n'est demandé ou s'il y a une erreur dans l'url 
//On affiche la boite de mp.
default;
    
    echo'<p><i>Vous êtes ici</i> : <a href="./index.php">Index du forum</a> --> <a href="./messagesprives.php">Gestion messagerie priv&eacute;e</a>';
    echo '<h1>Messagerie Privée - gestion</h1>';
    $query=$db->prepare('SELECT mp_lu, mp_id, mp_expediteur, mp_titre, mp_time, membre_id, membre_pseudo
    FROM forum_mp
    LEFT JOIN forum_membres ON forum_mp.mp_expediteur = forum_membres.membre_id
    WHERE mp_receveur = :id ORDER BY mp_id ASC');
    $query->bindValue(':id',$id,PDO::PARAM_INT);
    $query->execute();
    echo'<p><a href="./messagesprives.php?action=nouveau">
    <img src="./images/nouveau.gif" alt="Nouveau" title="Nouveau message" />
    </a></p>';
    if ($query->rowCount()>0)
    {
        ?>
        <table>
        <tr>
        <th></th>
        <th class="mp_titre"><strong>Titre</strong></th>
        <th class="mp_expediteur"><strong>Expéditeur</strong></th>
        <th class="mp_time"><strong>Date</strong></th>
        <th><strong>Action</strong></th>
        </tr>

        <?php
setlocale(LC_ALL,'fr_FR.UTF-8');
        //On boucle et on remplit le tableau
        while ($data = $query->fetch())
        {
            echo'<tr>';
            //Mp jamais lu, on affiche l'icone en question
            if($data['mp_lu'] == 0)
            {
            echo'<td><img src="./images/message_non_lu.gif" alt="Non lu" /></td>';
            }
            else //sinon une autre icone
            {
            echo'<td><img src="./images/message.gif" alt="Déja lu" /></td>';
            }
            echo'<td id="mp_titre">
            <a href="./messagesprives.php?action=consulter&amp;id='.$data['mp_id'].'">
            '.stripslashes(htmlspecialchars($data['mp_titre'])).'</a></td>
            <td id="mp_expediteur">
            <a href="./voirprofil.php?action=consulter&amp;m='.$data['membre_id'].'">
            '.stripslashes(htmlspecialchars($data['membre_pseudo'])).'</a></td>
            <td id="mp_time">'.strftime('%a %e %B %G',$data['mp_time']).'</td>
            <td>
            <a href="./messagesprives.php?action=supprimer&amp;id='.$data['mp_id'].'&amp;sur=0">supprimer</a></td></tr>';
        } //Fin de la boucle
        $query->closeCursor();
        echo '</table>';

    } //Fin du if
    else
    {
        echo'<p>Vous n avez aucun message privé pour l\'instant, cliquez
        <a href="./index.php">ici</a> pour revenir à la page d index</p>';
    }
//    } //fin de mp_id
} //Fin du switch
echo '</div>';
include("../inclus/fin.php");
?>

