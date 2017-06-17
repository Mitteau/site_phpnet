<?php
include('includes/constants.php');
include("includes/identifiants.php");
include('includes/start_session.php');
$titre = "Poster";
include("includes/debut_forum.php");
include("includes/menu_forum.php");

$id=$_SESSION['id'];
//On récupère la valeur de la variable action
$action = (isset($_GET['action']))?htmlspecialchars($_GET['action']):'';

// Si le membre n'est pas connecté, il est arrivé ici par erreur




switch($action)
{
//XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
    //Premier cas : nouveau topic
    case "nouveautopic":
if ($id==0) erreur(ERR_IS_NOT_CO);
    //On passe le message dans une série de fonction
    $message = $_POST['message'];
    $mess = $_POST['mess'];

    //Pareil pour le titre
    $titre = $_POST['titre'];
    //ici seulement, maintenant qu'on est sur qu'elle existe, on récupère la valeur de la variable f
    $forum = (int) $_GET['f'];
    $temps = time();

    if (empty($message) || empty($titre))
    {
        echo'<p>Votre message ou votre titre est vide, 
        cliquez <a href="./poster.php?action=nouveautopic&amp;f='.$forum.'">ici</a> pour recommencer</p>';
    }
    else //Si jamais le message n'est pas vide
    {
    
$t0=time();
$t1=$t0-$config0['temps_flood'];
$query = $db->prepare('SELECT COUNT(*) FROM forum_post WHERE post_createur = :id AND post_time > :time');
$query->bindValue(':id',$id,PDO::PARAM_INT);
//$query->bindValue(':time',$config0['temps_flood'],PDO::PARAM_INT);
$query->bindValue(':time',$t1,PDO::PARAM_INT);
$query->execute();

$nombre_mess=$query->fetch();
$query->closeCursor();
//echo ">".$nombre_mess[0]."<";
if ($nombre_mess[0] !=0){
    erreur(ERR_FLOOD);}

  
        //On entre le topic dans la base de donnée en laissant
        //le champ topic_last_post &agrave; 0
        $query=$db->prepare('INSERT INTO forum_topic
        (forum_id, topic_titre, topic_createur, topic_vu, topic_time, topic_genre,topic_last_post,topic_first_post,topic_post, topic_locked, topic_locked_time)
        VALUES(:forum, :titre, :id, 0, :temps, :mess,1,1,1,\'0\',0)');
        $query->bindValue(':forum', $forum, PDO::PARAM_INT);
        $query->bindValue(':titre', $titre, PDO::PARAM_STR);
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->bindValue(':temps', $temps, PDO::PARAM_INT);
        $query->bindValue(':mess', $mess, PDO::PARAM_STR);
try{$query->execute();}
catch (Exception $e){die ('Erreur : ' . $e->getMessage());}

        $nouveautopic = $db->lastInsertId(); //Notre fameuse fonction !

        $query->closeCursor(); 
        //Puis on entre le message
        $query=$db->prepare('INSERT INTO forum_post
        (post_createur, post_texte, post_time, topic_id, post_forum_id)
        VALUES (:id, :mess, :temps, :nouveautopic, :forum)');
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->bindValue(':mess', $message, PDO::PARAM_STR);
        $query->bindValue(':temps', $temps,PDO::PARAM_INT);
        $query->bindValue(':nouveautopic', (int) $nouveautopic, PDO::PARAM_INT);
        $query->bindValue(':forum', $forum, PDO::PARAM_INT);
try{$query->execute();}
catch (Exception $e){die ('Erreur : ' . $e->getMessage());}


        $nouveaupost = $db->lastInsertId(); //Encore notre fameuse fonction !
        $query->closeCursor(); 

        //Ici on update comme prévu la valeur de topic_last_post et de topic_first_post
        $query=$db->prepare('UPDATE forum_topic
        SET topic_last_post = :nouveaupost,
        topic_first_post = :nouveaupost
        WHERE topic_id = :nouveautopic');
        $query->bindValue(':nouveaupost', (int) $nouveaupost, PDO::PARAM_INT);    
        $query->bindValue(':nouveautopic', (int) $nouveautopic, PDO::PARAM_INT);
try{$query->execute();}
catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
        $query->closeCursor();

        //Enfin on met &agrave; jour les tables forum_forum et forum_membres
        $query=$db->prepare('UPDATE forum_forum SET forum_post = forum_post + 1 ,forum_topic = forum_topic + 1, 
        forum_last_post_id = :nouveaupost
        WHERE forum_id = :forum');
        $query->bindValue(':nouveaupost', (int) $nouveaupost, PDO::PARAM_INT);    
        $query->bindValue(':forum', (int) $forum, PDO::PARAM_INT);
try{$query->execute();}
catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
        $query->closeCursor();
    
        $query=$db->prepare('UPDATE forum_membres SET membre_post = membre_post + 1 WHERE membre_id = :id');
        $query->bindValue(':id', $id, PDO::PARAM_INT);    
try{$query->execute();}
catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
        $query->closeCursor();

//On ajoute une ligne dans la table forum_topic_view
$query=$db->prepare('INSERT INTO forum_topic_view 
(tv_id, tv_topic_id, tv_forum_id, tv_post_id, tv_poste) 
VALUES(:id, :topic, :forum, :post, :poste)');
$query->bindValue(':id',$id,PDO::PARAM_INT);
$query->bindValue(':topic',$nouveautopic,PDO::PARAM_INT);
$query->bindValue(':forum',$forum ,PDO::PARAM_INT);
$query->bindValue(':post',$nouveaupost,PDO::PARAM_INT);
$query->bindValue(':poste','0',PDO::PARAM_STR);
$query->execute();
$query->closeCursor();        //Et un petit message
        echo'<p>Votre envoi a bien été ajouté!<br /><br />Cliquez <a href="./index.php">ici</a> pour revenir &agrave; l\'accueil du forum<br />
        Cliquez <a href="./voirtopic.php?t='.$nouveautopic.'">ici</a> pour le voir</p>';
    }
    break; //Hourra !


//XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

    //Deuxième cas : répondre
    case "repondre":
    $message = $_POST['message'];

    $query=$db->prepare('SELECT auth_post FROM forum_forum WHERE forum_id = :forum');
    $query->bindValue(':forum',$_SESSION['forum'],PDO::PARAM_INT);
    $query->execute(); 
    $data=$query->fetch();
    $query->closeCursor(); 

if ($id==0 && !verif_auth($data['auth_post'])) erreur(ERR_AUTH_EDIT);

    //ici seulement, maintenant qu'on est sur qu'elle existe, on récupère la valeur de la variable t
    $topic = (int) $_GET['t'];

    $query=$db->prepare('SELECT topic_locked FROM forum_topic WHERE topic_id = :topic');
    $query->bindValue(':topic',$topic,PDO::PARAM_INT);
    $query->execute(); 
    $data=$query->fetch();
    if ($data['topic_locked'] != 0)
    {
        erreur(ERR_TOPIC_VERR); //A vous d'afficher un message du genre : le topic est verrouillé qu'est ce que tu fous l&agrave; !?
    }
    $query->closeCursor();


    $temps = time();

    if (empty($message))
    {
        echo'<p>Votre message est vide, cliquez <a href="./poster.php?action=repondre&amp;t='.$topic.'">ici</a> pour recommencer</p>';
    }
    else //Sinon, si le message n'est pas vide
    {


//Contrôle anti flood

$t0=time();
$t1=$t0-$config0['temps_flood'];
$query = $db->prepare('SELECT COUNT(*) FROM forum_post WHERE post_createur = :id AND post_time > :time');
$query->bindValue(':id',$id,PDO::PARAM_INT);
//$query->bindValue(':time',$config0['temps_flood'],PDO::PARAM_INT);
$query->bindValue(':time',$t1,PDO::PARAM_INT);
$query->execute();

$nombre_mess=$query->fetch();
$query->closeCursor();
//echo ">".$nombre_mess[0]."<";
if ($nombre_mess[0] !=0){
    erreur(ERR_FLOOD);}





        //On récupère l'id du forum
        $query=$db->prepare('SELECT forum_id, topic_post FROM forum_topic WHERE topic_id = :topic');
        $query->bindValue(':topic', $topic, PDO::PARAM_INT);    
try{$query->execute();}
catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
        $data=$query->fetch();
        $forum = $data['forum_id'];
//echo "id ".$data['forum_id'];die;
        //Puis on entre le message
        $query=$db->prepare('INSERT INTO forum_post
        (post_createur, post_texte, post_time, topic_id, post_forum_id)
        VALUES(:id,:mess,:temps,:topic,:forum)');
        $query->bindValue(':id', $id, PDO::PARAM_INT);   
        $query->bindValue(':mess', $message, PDO::PARAM_STR);  
        $query->bindValue(':temps', $temps, PDO::PARAM_INT);  
        $query->bindValue(':topic', $topic, PDO::PARAM_INT);   
        $query->bindValue(':forum', $forum, PDO::PARAM_INT); 
        $query->execute();

        $nouveaupost = $db->lastInsertId();
        $query->closeCursor(); 

        //On change un peu la table forum_topic
        $query=$db->prepare('UPDATE forum_topic SET topic_post = topic_post + 1, topic_last_post = :nouveaupost WHERE topic_id =:topic');
        $query->bindValue(':nouveaupost', (int) $nouveaupost, PDO::PARAM_INT);   
        $query->bindValue(':topic', (int) $topic, PDO::PARAM_INT); 
        $query->execute();
        $query->closeCursor(); 

        //Puis même combat sur les 2 autres tables
        $query=$db->prepare('UPDATE forum_forum SET forum_post = forum_post + 1 , forum_last_post_id = :nouveaupost WHERE forum_id = :forum');
        $query->bindValue(':nouveaupost', (int) $nouveaupost, PDO::PARAM_INT);   
        $query->bindValue(':forum', (int) $forum, PDO::PARAM_INT); 
        $query->execute();
        $query->closeCursor(); 

        $query=$db->prepare('UPDATE forum_membres SET membre_post = membre_post + 1 WHERE membre_id = :id');
        $query->bindValue(':id', $id, PDO::PARAM_INT); 
try{$query->execute();}
catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
        $query->closeCursor(); 
//On update la table forum_topic_view
$query=$db->prepare('UPDATE forum_topic_view 
SET tv_post_id = :post, tv_poste = :poste
WHERE tv_id = :id AND tv_topic_id = :topic');
$query->bindValue(':post',$nouveaupost,PDO::PARAM_INT);
$query->bindValue(':poste','1',PDO::PARAM_STR);
$query->bindValue(':id',$id,PDO::PARAM_INT);
$query->bindValue(':topic',$topic,PDO::PARAM_INT);
$query->execute();
$query->closeCursor();        //Et un petit message
        $nombreDeMessagesParPage = 15;
        $nbr_post = $data['topic_post']+1;
        $page = ceil($nbr_post / $nombreDeMessagesParPage);
        echo'<p>Votre message a bien été ajouté!<br /><br />
        Cliquez <a href="./index.php">ici</a> pour revenir &agrave; l\'accueil du forum<br />
        Cliquez <a href="./voirtopic.php?t='.$topic.'&amp;page='.$page.'#p_'.$nouveaupost.'">ici</a> pour le voir</p>';
    }//Fin du else
    break;

//XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

case "repondremp": //Si on veut répondre
if ($id==0) erreur(ERR_IS_NOT_CO);
    //On récupère le titre et le message
    $message = $_POST['message'];
    $titre = $_POST['titre'];
    $temps = time();
    //On récupère la valeur de l'id du destinataire
    $dest = (int) $_GET['dest'];
    $mess_id = (int) $_GET['m'];
    //Enfin on peut envoyer le message

$t0=time();
$t1=$t0-$config0['temps_flood'];
$query = $db->prepare('SELECT COUNT(*) FROM forum_post WHERE post_createur = :id AND post_time > :time');
$query->bindValue(':id',$id,PDO::PARAM_INT);
//$query->bindValue(':time',$config0['temps_flood'],PDO::PARAM_INT);
$query->bindValue(':time',$t1,PDO::PARAM_INT);
$query->execute();

$nombre_mess=$query->fetch();
$query->closeCursor();
//echo ">".$nombre_mess[0]."<";
if ($nombre_mess[0] !=0){
    erreur(ERR_FLOOD);}





    $query=$db->prepare('INSERT INTO forum_mp
    (mp_expediteur, mp_receveur, mp_rep, mp_titre, mp_text, mp_time, mp_lu)
    VALUES(:id, :dest, :rep, :titre, :txt, :tps, "0")'); 
    $query->bindValue(':id',$id,PDO::PARAM_INT);   
    $query->bindValue(':dest',$dest,PDO::PARAM_INT);   
    $query->bindValue(':rep',$mess_id,PDO::PARAM_INT);   
    $query->bindValue(':titre',$titre,PDO::PARAM_STR);   
    $query->bindValue(':txt',$message,PDO::PARAM_STR);   
    $query->bindValue(':tps',$temps,PDO::PARAM_INT);   
try{$query->execute();}
catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
	catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
    $query->closeCursor(); 

    echo'<p>Votre message a bien été envoyé!</p><p>
    Cliquez <a href="./messagesprives.php">ici</a> pour retourner
    &agrave; la messagerie</p><p>Cliquez <a href="./index.php">ici</a> pour revenir &agrave; l\'accueil du   
    forum</p>';


    break;


    case "nouveaumessage": //On envoie un nouveau mess

    //On récupère le titre et le message, pas le titre puiqu'on reste dans le même sujet MAP
    $message = $_POST['message'];
	$forum = $_POST['forum'];
	
//	echo $forum;die;
	
    $tps = time();
    $topic = (int) $_GET['t'];
        if ($query)$query->closeCursor();

$t0=time();
$t1=$t0-$config0['temps_flood'];
$query = $db->prepare('SELECT COUNT(*) FROM forum_post WHERE post_createur = :id AND post_time > :time');
$query->bindValue(':id',$id,PDO::PARAM_INT);
//$query->bindValue(':time',$config0['temps_flood'],PDO::PARAM_INT);
$query->bindValue(':time',$t1,PDO::PARAM_INT);
$query->execute();

$nombre_mess=$query->fetch();
$query->closeCursor();
//echo ">".$nombre_mess[0]."<";
if ($nombre_mess[0] !=0){
    erreur(ERR_FLOOD);}




        $query=$db->prepare('INSERT INTO forum_post
        (post_createur, post_texte, post_time, topic_id, post_forum_id)
        VALUES(:id, :txt, :tps, :topic, :forum)'); 
        $query->bindValue(':id',$id,PDO::PARAM_INT);   
        $query->bindValue(':txt',$message,PDO::PARAM_STR);   
        $query->bindValue(':tps',$tps,PDO::PARAM_INT);   
        $query->bindValue(':topic',$topic,PDO::PARAM_INT);   
        $query->bindValue(':forum',$forum,PDO::PARAM_INT);   //MAP
	try {$query->execute();}
	catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
        $query->closeCursor(); 
    echo'<p>Votre message a bien été envoyé!</p>
    <p>Cliquez <a href="./voirtopic.php?action=consulter&t='.$topic.'">ici</a> pour retourner
	au sujet</p><p>Cliquez <a href="./index.php">ici</a> pour revenir &agrave; l\'accueil du   
    forum</p>';

    break;

//XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
    case "nouveaump": //On envoie un nouveau mp
if ($id==0) erreur(ERR_IS_NOT_CO);
    //On récupère le titre et le message
    $message = $_POST['message'];
    $titre = $_POST['titre'];
    $tps = time();
    $dest = $_POST['to'];
//echo $dest;die;
    //On récupère la valeur de l'id du destinataire
    //Il faut déja vérifier le nom
    $query=$db->prepare('SELECT membre_id FROM forum_membres
    WHERE LOWER(membre_pseudo) = :dest');
//    $query->bindValue(':dest',strotolower($dest),PDO::PARAM_STR);MAP
    $query->bindValue(':dest',$dest,PDO::PARAM_STR);
	try {$query->execute();}
	catch (Exception $e){die ('Erreur : ' . $e->getMessage());}

$t0=time();
$t1=$t0-$config0['temps_flood'];
$query2 = $db->prepare('SELECT COUNT(*) FROM forum_post WHERE post_createur = :id AND post_time > :time');
$query2->bindValue(':id',$id,PDO::PARAM_INT);
//$query->bindValue(':time',$config0['temps_flood'],PDO::PARAM_INT);
$query2->bindValue(':time',$t1,PDO::PARAM_INT);
$query2->execute();

$nombre_mess=$query2->fetch();
$query2->closeCursor();
//echo ">".$nombre_mess[0]."<";
if ($nombre_mess[0] !=0){
    erreur(ERR_FLOOD);}

    if($data = $query->fetch())
    {
        $query1=$db->prepare('INSERT INTO forum_mp
        (mp_expediteur, mp_receveur, mp_rep, mp_titre, mp_text, mp_time, mp_lu)
        VALUES(:id, :dest, 0, :titre, :txt, :tps, :lu)'); 
        $query1->bindValue(':id',$id,PDO::PARAM_INT);   
        $query1->bindValue(':dest',(int) $data['membre_id'],PDO::PARAM_INT);   
        $query1->bindValue(':titre',$titre,PDO::PARAM_STR);   
        $query1->bindValue(':txt',$message,PDO::PARAM_STR);   
        $query1->bindValue(':tps',$tps,PDO::PARAM_INT);   
        $query1->bindValue(':lu','0',PDO::PARAM_STR);   
	try {$query1->execute();}
	catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
        $query1->closeCursor(); 

    echo'<p>Votre message a bien été envoyé!</p>
    <p>Cliquez <a href="./messagesprives.php?action=consulter">ici</a> pour retourner
    &agrave; la messagerie</p><p>Cliquez <a href="./index.php">ici</a> pour revenir &agrave; l\'accueil du   
    forum</p>';
    }
    //Sinon l'utilisateur n'existe pas !
    else
    {
        echo'<p>Désolé, ce membre n\'existe pas, veuillez vérifier et
        réessayer de nouveau.</p>';
    }
    $query->closeCursor(); 
    break;

//XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

case "editer":{
	$post = (int) $_GET['p'];

	$query=$db->prepare('SELECT post_createur, forum_topic.topic_genre, forum_topic.topic_id FROM forum_post
	LEFT JOIN forum_topic ON forum_topic.topic_id = forum_post.topic_id
	WHERE post_id = :post');
	$query->bindValue(':post', $post, PDO::PARAM_INT);    
try{$query->execute();}
catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
	$data=$query->fetch();
	$query->closeCursor();
	$autor=$data['post_createur'];

//vérifier que nous sommes l'auteur ou modérateur
	if (verif_auth(MODO) || ($id == $autor)){


$t0=time();
$t1=$t0-$config0['temps_flood'];
$query = $db->prepare('SELECT COUNT(*) FROM forum_post WHERE post_createur = :id AND post_time > :time');
$query->bindValue(':id',$id,PDO::PARAM_INT);
//$query->bindValue(':time',$config0['temps_flood'],PDO::PARAM_INT);
$query->bindValue(':time',$t1,PDO::PARAM_INT);
$query->execute();

$nombre_mess=$query->fetch();
$query->closeCursor();
//echo ">".$nombre_mess[0]."<";
if ($nombre_mess[0] !=0){
    erreur(ERR_FLOOD);}





	$query=$db->prepare('UPDATE forum_post SET post_texte = :message WHERE post_id =:post');
	$query->bindValue(':message', $_POST['message'], PDO::PARAM_INT);    
	$query->bindValue(':post', $post, PDO::PARAM_INT);    
try{$query->execute();}
catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
	$query->closeCursor();
//MAP
    echo'<p>Votre message a bien été envoyé!</p>
    <p>Cliquez <a href="./voirtopic.php?t='.$data['topic_id'].'>ici</a> pour revenir au sujet</p>
     <p>Cliquez <a href="./index.php">ici</a> pour revenir &agrave; l\'accueil du   
    forum</p>';
	}else echo "Vous n'êtes ni l'auteur ni un modérateur.";


	}// fin d'editer
    break;


case "delete": //Si on veut supprimer le post
    //On récupère la valeur de p
    $post = (int) $_GET['p'];
    $query=$db->prepare('SELECT post_createur, post_texte, forum_id, topic_id, auth_modo
    FROM forum_post
    LEFT JOIN forum_forum ON forum_post.post_forum_id = forum_forum.forum_id
    WHERE post_id=:post');
    $query->bindValue(':post',$post,PDO::PARAM_INT);
    $query->execute();
    $data = $query->fetch();
    $topic = $data['topic_id'];
    $forum = $data['forum_id'];
	$poster = $data['post_createur'];

   
    //Ensuite on vérifie que le membre a le droit d'être ici 
    //(soit le créateur soit un modo/admin)
    if (!verif_auth($data['auth_modo']) && $poster != $id){
        // Si cette condition n'est pas remplie ça va barder :o
        erreur(ERR_AUTH_DELETE); }
    else //Sinon ça roule et on continue
    {
        //Ici on vérifie plusieurs choses :
        //est-ce un premier post ? Dernier post ou post classique ?
 
        $query = $db->prepare('SELECT topic_first_post, topic_last_post FROM forum_topic
        WHERE topic_id = :topic');
        $query->bindValue(':topic',$topic,PDO::PARAM_INT);
        $query->execute();
        $data_post=$query->fetch();

        //On distingue maintenant les cas
        if ($data_post['topic_first_post']==$post) //Si le message est le premier
        {//Les autorisations ont changé !
            //Normal, seul un modo peut décider de supprimer tout un topic
            if (!verif_auth($data['auth_modo']))
            {
                erreur('ERR_AUTH_DELETE_TOPIC');
            }

            //Il faut s'assurer que ce n'est pas une erreur
 
            echo'<p>Vous avez choisi de supprimer un message.
            Cependant ce message est le premier du sujet. Voulez vous supprimer le sujet ? <br />
            <a href="./postok.php?action=delete_topic&amp;t='.$topic.'">oui</a> - <a href="./voirtopic.php?t='.$topic.'">non</a>
            </p>';
            $query->closeCursor();                     
        }
        elseif ($data_post['topic_last_post']==$post)  //Si le message est le dernier
        {
            //On supprime le post
            $query=$db->prepare('DELETE FROM forum_post WHERE post_id = :post');
            $query->bindValue(':post',$post,PDO::PARAM_INT);
            $query->execute();
            $query->closeCursor();
           
            //On modifie la valeur de topic_last_post pour cela on
            //récupère l'id du plus récent message de ce topic
            $query=$db->prepare('SELECT post_id FROM forum_post WHERE topic_id = :topic 
            ORDER BY post_id DESC LIMIT 0,1');
            $query->bindValue(':topic',$topic,PDO::PARAM_INT);
            $query->execute();
            $data=$query->fetch();             
            $last_post_topic=$data['post_id'];
            $query->closeCursor();

            //On fait de même pour forum_last_post_id
            $query=$db->prepare('SELECT post_id FROM forum_post WHERE post_forum_id = :forum
            ORDER BY post_id DESC LIMIT 0,1');
            $query->bindValue(':forum',$forum,PDO::PARAM_INT);
            $query->execute();
            $data=$query->fetch();             
            $last_post_forum=$data['post_id'];
            $query->closeCursor();   
                   
            //On met &agrave; jour la valeur de topic_last_post
			
            $query=$db->prepare('UPDATE forum_topic SET topic_last_post = :last
            WHERE topic_last_post = :post');
            $query->bindValue(':last',$last_post_topic,PDO::PARAM_INT);
            $query->bindValue(':post',$post,PDO::PARAM_INT);
            $query->execute();
            $query->closeCursor();
 
            //On enlève 1 au nombre de messages du forum et on met &agrave;       
            //jour forum_last_post
            $query=$db->prepare('UPDATE forum_forum SET forum_post = forum_post - 1, forum_last_post_id = :last
            WHERE forum_id = :forum');
            $query->bindValue(':last',$last_post_forum,PDO::PARAM_INT);
            $query->bindValue(':forum',$forum,PDO::PARAM_INT);
            $query->execute();
            $query->closeCursor(); 
                        
            //On enlève 1 au nombre de messages du topic
            $query=$db->prepare('UPDATE forum_topic SET  topic_post = topic_post - 1
            WHERE topic_id = :topic');
            $query->bindValue(':topic',$topic,PDO::PARAM_INT);
            $query->execute();
            $query->closeCursor(); 
                       
            //On enlève 1 au nombre de messages du membre
            $query=$db->prepare('UPDATE forum_membres SET  membre_post = membre_post - 1
            WHERE membre_id = :id');
            $query->bindValue(':id',$poster,PDO::PARAM_INT);
            $query->execute();
            $query->closeCursor();  
                        
            //Enfin le message
            echo'<p>Le message a bien été supprimé !<br />
            Cliquez <a href="./voirtopic.php?t='.$topic.'">ici</a> pour retourner au sujet<br />
            Cliquez <a href="./index.php">ici</a> pour revenir &agrave; l\'accueil du forum</p>';
 
        }
        else // Si c'est un post classique
        {
 
            //On supprime le post
            $query=$db->prepare('DELETE FROM forum_post WHERE post_id = :post');
            $query->bindValue(':post',$post,PDO::PARAM_INT);
            $query->execute();
            $query->closeCursor();
                       
            //On enlève 1 au nombre de messages du forum
            $query=$db->prepare('UPDATE forum_forum SET forum_post = forum_post - 1  WHERE forum_id = :forum');
            $query->bindValue(':forum',$forum,PDO::PARAM_INT);
            $query->execute();
            $query->closeCursor(); 
                        
            //On enlève 1 au nombre de messages du topic
            $query=$db->prepare('UPDATE forum_topic SET  topic_post = topic_post - 1
            WHERE topic_id = :topic');
            $query->bindValue(':topic',$topic,PDO::PARAM_INT);
            $query->execute();
            $query->closeCursor(); 
                       
            //On enlève 1 au nombre de messages du membre
            $query=$db->prepare('UPDATE forum_membres SET  membre_post = membre_post - 1
            WHERE membre_id = :id');
            $query->bindValue(':id',$data['post_createur'],PDO::PARAM_INT);
            $query->execute();
            $query->closeCursor();  
                        
            //Enfin le message
            echo'<p>Le message a bien été supprimé !<br />
            Cliquez <a href="./voirtopic.php?t='.$topic.'">ici</a> pour retourner au sujet<br />
            Cliquez <a href="./index.php">ici</a> pour revenir &agrave; l\'accueil du forum</p>';
        }
               
            //On enlève les lignes "vues" du topic
 
            $query=$db->prepare('DELETE FROM forum_topic_view WHERE tv_post_id = :post AND tv_id = :id');
            $query->bindValue(':id',$poster,PDO::PARAM_INT);
            $query->bindValue(':post',$post,PDO::PARAM_INT);
            $query->execute();
            $query->closeCursor();  
                        
       //On enlève le nombre vues du topic : 1 pour y être venu et 1 pour suppression du message
        $query=$db->prepare('UPDATE forum_topic SET  topic_vu = topic_vu - 2
            WHERE topic_id = :topic'); // à vérifier MAP
        $query->bindValue(':topic',$topic,PDO::PARAM_INT);
        $query->execute();

    } //Fin du else
break;

case "delete_topic":
    $topic = (int) $_GET['t'];
    $query=$db->prepare('SELECT forum_topic.forum_id, auth_modo
    FROM forum_topic
    LEFT JOIN forum_forum ON forum_topic.forum_id = forum_forum.forum_id
    WHERE topic_id=:topic');
    $query->bindValue(':topic',$topic,PDO::PARAM_INT);
    $query->execute();
    $data = $query->fetch();
    $query->closeCursor();
    $forum = $data['forum_id'];
 
    //Ensuite on vérifie que le membre a le droit d'être ici 
    //c'est-&agrave;-dire si c'est un modo / admin
 
    if (!verif_auth($data['auth_modo']))
    {
        erreur('ERR_AUTH_DELETE_TOPIC');
    }
    else //Sinon ça roule et on continue
    {

        //On compte le nombre de post du topic
        $query=$db->prepare('SELECT topic_post FROM forum_topic WHERE topic_id = :topic');
        $query->bindValue(':topic',$topic,PDO::PARAM_INT);
        $query->execute();
        $data = $query->fetch();
        $nombrepost = $data['topic_post'];// erreur ??? + 1;
        $query->closeCursor();

        //On supprime le topic
        $query=$db->prepare('DELETE FROM forum_topic
        WHERE topic_id = :topic');
        $query->bindValue(':topic',$topic,PDO::PARAM_INT);
        $query->execute();
        $query->closeCursor();
       
        //On enlève le nombre de post posté par chaque membre dans le topic
        $query=$db->prepare('SELECT post_createur, COUNT(*) AS nombre_mess FROM forum_post
        WHERE topic_id = :topic GROUP BY post_createur');
        $query->bindValue(':topic',$topic,PDO::PARAM_INT);
        $query->execute();

         while($data = $query->fetch())
        {
            $query1=$db->prepare('UPDATE forum_membres
            SET membre_post = membre_post - :mess
            WHERE membre_id = :id');
            $query1->bindValue(':mess',$data['nombre_mess'],PDO::PARAM_INT);
            $query1->bindValue(':id',$data['post_createur'],PDO::PARAM_INT);
            $query1->execute();
            $query1->closeCursor();
        }

        $query->closeCursor();       
        //Et on supprime les posts !
        $query=$db->prepare('DELETE FROM forum_post WHERE topic_id = :topic');
        $query->bindValue(':topic',$topic,PDO::PARAM_INT);
        $query->execute();
        $query->closeCursor(); 

        //Dernière chose, on récupère le dernier post du forum
        $query=$db->prepare('SELECT post_id FROM forum_post
        WHERE post_forum_id = :forum ORDER BY post_id DESC LIMIT 0,1');
        $query->bindValue(':forum',$forum,PDO::PARAM_INT);
        $query->execute();
        $data = $query->fetch();
 
        //Ensuite on modifie certaines valeurs :
        $query=$db->prepare('UPDATE forum_forum
        SET forum_topic = forum_topic - 1, forum_post = forum_post - :nbr, forum_last_post_id = :id
        WHERE forum_id = :forum');
        $query->bindValue(':nbr',$nombrepost,PDO::PARAM_INT);
        $query->bindValue(':id',$data['post_id'],PDO::PARAM_INT);
        $query->bindValue(':forum',$forum,PDO::PARAM_INT);
        $query->execute(); 
        $query->closeCursor();

        //Enfin le message
        echo'<p>Le sujet a bien été supprimé !<br />
        Cliquez <a href="./index.php">ici</a> pour revenir &agrave; l\'accueil du forum</p>';

    } //Fin du else
break;

case "lock": //Si on veut verrouiller le topic
    //On récupère la valeur de t
    $topic = (int) $_GET['t'];
    $query = $db->prepare('SELECT forum_topic.forum_id, auth_modo FROM forum_topic
    LEFT JOIN forum_forum ON forum_forum.forum_id = forum_topic.forum_id
    WHERE topic_id = :topic');
    $query->bindValue(':topic',$topic,PDO::PARAM_INT);
    $query->execute();
    $data = $query->fetch();

    //Ensuite on vérifie que le membre a le droit d'être ici
    if (!verif_auth($data['auth_modo']))
    {
        // Si cette condition n'est pas remplie ça va barder :o
        erreur(ERR_AUTH_VERR);
    }  
    else //Sinon ça roule et on continue
    {
        //On met &agrave; jour la valeur de topic_locked
        $time=time();
        $query->closeCursor();
        $query=$db->prepare('UPDATE forum_topic SET topic_locked = :lock, topic_locked_time = :ltime WHERE topic_id = :topic');
        $query->bindValue(':lock',1,PDO::PARAM_STR);
        $query->bindValue(':ltime',$time,PDO::PARAM_STR);
        $query->bindValue(':topic',$topic,PDO::PARAM_INT);
        $query->execute(); 
        $query->closeCursor();

        echo'<p>Le sujet a bien été verrouillé ! <br />
        Cliquez <a href="./voirtopic.php?t='.$topic.'">ici</a> pour retourner au sujet<br />
        Cliquez <a href="./index.php">ici</a> pour revenir &agrave; l\'accueil du forum</p>';
    }
break;
 
case "unlock": //Si on veut déverrouiller le topic
    //On récupère la valeur de t
        $topic = (int) $_GET['t'];
    $query = $db->prepare('SELECT forum_topic.forum_id, auth_modo FROM forum_topic
    LEFT JOIN forum_forum ON forum_forum.forum_id = forum_topic.forum_id
    WHERE topic_id = :topic');
    $query->bindValue(':topic',$topic,PDO::PARAM_INT);
    $query->execute();
    $data = $query->fetch();
 
 //Ensuite on vérifie que le membre a le droit d'être ici
    if (!verif_auth($data['auth_modo']))
    {
        // Si cette condition n'est pas remplie ça va barder :o
        erreur(ERR_AUTH_VERR);
    }  
    else //Sinon ça roule et on continue
    {
        //On met &agrave; jour la valeur de topic_locked
        $query->closeCursor();
        $query=$db->prepare('UPDATE forum_topic SET topic_locked = :lock WHERE topic_id = :topic');
        $query->bindValue(':lock',0,PDO::PARAM_STR);
        $query->bindValue(':topic',$topic,PDO::PARAM_INT);
        $query->execute(); 
        $query->closeCursor();
 
        echo'<p>Le sujet a bien été déverrouillé !<br />
        Cliquez <a href="./voirtopic.php?t='.$topic.'">ici</a> pour retourner au sujet<br />
        Cliquez <a href="./index.php">ici</a> pour revenir &agrave; l\'accueil du forum</p>';
    }
break;

case "deplacer"://déplacement d'un sujet


    $topic = (int) $_GET['t'];
    $query= $db->prepare('SELECT forum_topic.forum_id, auth_modo
    FROM forum_topic
    LEFT JOIN forum_forum 
    ON forum_forum.forum_id = forum_topic.forum_id
    WHERE topic_id =:topic');
    $query->bindValue(':topic',$topic,PDO::PARAM_INT);
    $query->execute();
    $data=$query->fetch();
    
    if (!verif_auth($data['auth_modo']))
    {
        // Si cette condition n'est pas remplie ça va barder :o
        erreur(ERR_AUTH_MOVE);
    }
    else //Sinon ça roule et on continue
    {
        $query->closeCursor();
        $destination = (int) $_POST['dest'];
        $origine = (int) $_POST['from'];
               
        //On déplace le topic
        $query=$db->prepare('UPDATE forum_topic SET forum_id = :dest WHERE topic_id = :topic');
        $query->bindValue(':dest',$destination,PDO::PARAM_INT);
        $query->bindValue(':topic',$topic,PDO::PARAM_INT);
        $query->execute();
        $query->closeCursor(); 
 
        //On déplace les posts
        $query=$db->prepare('UPDATE forum_post SET post_forum_id = :dest
        WHERE topic_id = :topic');
        $query->bindValue(':dest',$destination,PDO::PARAM_INT);
        $query->bindValue(':topic',$topic,PDO::PARAM_INT);
        $query->execute(); 
        $query->closeCursor();     
        //On s'occupe d'ajouter / enlever les nombres de post / topic aux
        //forum d'origine et de destination
        //Pour cela on compte le nombre de post déplacé
               
        
        $query=$db->prepare('SELECT COUNT(*) AS nombre_post
        FROM forum_post WHERE topic_id = :topic');
        $query->bindValue(':topic',$topic,PDO::PARAM_INT);
        $query->execute();    
        $data = $query->fetch();
        $nombrepost = $data['nombre_post'];
        $query->closeCursor();       
                
        //Il faut également vérifier qu'on a pas déplacé un post qui été
        //l'ancien premier post du forum (champ forum_last_post_id)
 
        $query=$db->prepare('SELECT post_id FROM forum_post WHERE post_forum_id = :ori
        ORDER BY post_id DESC LIMIT 0,1');
        $query->bindValue(':ori',$origine,PDO::PARAM_INT);
        $query->execute();
        $data=$query->fetch();       
        $last_post=$data['post_id'];
        $query->closeCursor();
        
        //Puis on met &agrave; jour le forum d'origine
        $query=$db->prepare('UPDATE forum_forum SET forum_post = forum_post - :nbr, forum_topic = forum_topic - 1,
        forum_last_post_id = :id
        WHERE forum_id = :ori');
        $query->bindValue(':nbr',$nombrepost,PDO::PARAM_INT);
        $query->bindValue(':ori',$origine,PDO::PARAM_INT);
        $query->bindValue(':id',$last_post,PDO::PARAM_INT);
        $query->execute();
        $query->closeCursor();

        //Avant de mettre &agrave; jour le forum de destination il faut
        //vérifier la valeur de forum_last_post_id
        $query=$db->prepare('SELECT post_id FROM forum_post WHERE post_forum_id = :dest
        ORDER BY post_id DESC LIMIT 0,1');
        $query->bindValue(':dest',$destination,PDO::PARAM_INT);
        $query->execute();
        $data=$query->fetch();
        $last_post=$data['post_id'];
        $query->closeCursor();

        //Et on met &agrave; jour enfin !
        $query=$db->prepare('UPDATE forum_forum SET forum_post = forum_post + :nbr,
        forum_topic = forum_topic + 1,
        forum_last_post_id = :last
        WHERE forum_id = :forum');
        $query->bindValue(':nbr',$nombrepost,PDO::PARAM_INT);
        $query->bindValue(':last',$last_post,PDO::PARAM_INT);
        $query->bindValue(':forum',$destination,PDO::PARAM_INT);
        $query->execute();
        $query->closeCursor();

        //C'est gagné ! On affiche le message
        echo'<p>Le sujet a bien été déplacé <br />
        Cliquez <a href="./voirtopic.php?t='.$topic.'">ici</a> pour revenir au sujet<br />
        Cliquez <a href="./index.php">ici</a> pour revenir &agrave; l\'accueil du forum</p>';
    }
break;





    default;

    echo'<p>Cette action est impossible</p>';

}//Fin du Switch
echo '</div>';
include("../inclus/fin.php");
?>

