<?php
include('includes/constants.php');
include("includes/identifiants.php");
include('includes/start_session.php');
$titre = "\"profil\"";
include("includes/debut_forum.php");
include("includes/menu_forum.php");

if ($id==0 || $_SESSION['level'] < 2) erreur(ERR_NO_PROFIL);
else {

//recueil des paramètres

	$config = array(
	"avatar_maxsize" => "",
	"avatar_maxh" => "",
	"avatar_maxl" => "",
	"sign_maxl" => "",
	"auth_bbcode_sign" => "",
	"pseudo_maxsize" => "",
	"pseudo_minsize" => "",
	);
	$query = $db->query('SELECT config_nom, config_valeur FROM forum_config');
	
	while($data=$query->fetch())
	{
          $config[$data['config_nom']]=$data['config_valeur'];
	}
	$query->closeCursor();

    $avmxsz=$config["avatar_maxsize"];
    $avmxszko=floor($avmxsz/1024);

//On récupère la valeur de nos variables passées par URL
$action = isset($_GET['action'])?htmlspecialchars($_GET['action']):'consulter';
$membre = isset($_GET['m'])?(int) $_GET['m']:$id;
switch($action)
{
    //Si c'est "consulter"
    case "consulter":
       //On récupère les infos du membre
       if (!$db){ echo 'base inaccessible';die;}
       $query=$db->prepare('SELECT membre_pseudo, membre_avatar,
       membre_email, membre_msn, membre_signature, membre_siteweb, membre_post,
       membre_inscrit, membre_localisation
       FROM forum_membres WHERE membre_id=:membre') ;
       $query->bindValue(':membre',$membre, PDO::PARAM_INT);
       $query->execute();
       $data=$query->fetch();

//echo $data['membre_avatar'];die;
       //On affiche les infos sur le membre
       echo '<p><i>Vous êtes ici</i> : <a href="./index.php">Accueil du forum</a> --> 
       profil de '.stripslashes(htmlspecialchars($data['membre_pseudo']));
       echo'<h1>Profil de '.stripslashes(htmlspecialchars($data['membre_pseudo'])).'</h1>';
       
       echo'<p><strong>Adresse E-Mail : </strong>
       <a href="mailto:'.stripslashes($data['membre_email']).'">
       '.stripslashes(htmlspecialchars($data['membre_email'])).'</a><br />';
       
       echo'<strong>MSN Messenger : </strong>'.stripslashes(htmlspecialchars($data['membre_msn'])).'<br />';
       
       echo'<strong>Site Web : </strong>
       <a href="'.stripslashes($data['membre_siteweb']).'">'.stripslashes(htmlspecialchars($data['membre_siteweb'])).'</a>
       <br /><br />';
 
       echo'Ce membre est inscrit depuis le
       <strong>'.date('d/m/Y',$data['membre_inscrit']).'</strong>
       et a posté <strong>'.$data['membre_post'].'</strong> messages
       <br /><br />';
       echo'<strong>Localisation : </strong>'.stripslashes(htmlspecialchars($data['membre_localisation'])).'
       </p>';
       //echo $BASE_FORUM.'/images/avatars/'.$data['membre_avatar'];die;
       echo '<p>Avatar actuel&nbsp;: 
<img src="./images/avatars/'.$data['membre_avatar'].'" style="width:60px;" alt="pas d\'avatar"></p>';

$query->closeCursor();         //pas d\'avatar"</p>';
       break;



    //Si on choisit de modifier son profil
    case "modifier":
    if (empty($_POST['sent'])) // Si on la variable est vide, on peut considérer qu'on est sur la page de formulaire
    {
        //On commence par s'assurer que le membre est connecté
        if ($id==0) erreur(ERR_IS_NOT_CO);
        //On prend les infos du membre
        $query=$db->prepare('SELECT membre_pseudo, membre_email,
        membre_siteweb, membre_signature, membre_msn, membre_localisation,
        membre_avatar
        FROM forum_membres WHERE membre_id=:id');
        $query->bindValue(':id',$id,PDO::PARAM_INT);
        $query->execute();
        $data=$query->fetch();
        echo '<p><i>Vous êtes ici</i> : <a href="./index.php">Index du forum</a> --> Modification du profil';
        echo '<h1>Modifier son profil</h1>';
        
        echo '<form method="post" action="voirprofil.php?action=modifier" enctype="multipart/form-data">
       
 
        <fieldset><legend>Identifiants</legend>
        Pseudo : <strong>'.stripslashes(htmlspecialchars($data['membre_pseudo'])).'</strong><br />
        Souhaitez-vous changer de mot de passe&nbsp;? <button type="button" onclick="change_MP()">Changer</button>
<script type="text/javascript">
	function change_MP(){
	location.assign("./changeMP.php");
}
</script>


        </fieldset>
 
        <fieldset><legend>Contacts</legend>
        <label for="email">Votre adresse E_Mail :</label>
        <input type="text" name="email" id="email"
        value="'.stripslashes($data['membre_email']).'" /><br />
 
        <label for="msn">Votre adresse MSN :</label>
        <input type="text" name="msn" id="msn"
        value="'.stripslashes($data['membre_msn']).'" /><br />
 
        <label for="website">Votre site web :</label>
        <input type="text" name="website" id="website"
        value="'.stripslashes($data['membre_siteweb']).'" /><br />
        </fieldset>
 
        <fieldset><legend>Informations suppl&eacute;mentaires</legend>
        <label for="localisation">Localisation :</label>
        <input type="text" name="localisation" id="localisation"
        value="'.stripslashes($data['membre_localisation']).'" /><br />
        </fieldset>
<!--.floor($config["avatar_maxsize"]/1024)-->             
        <fieldset><legend>Profil sur le forum</legend>
<p>Votre avatar actuel&nbsp;: 
<img src="./images/avatars/'.$data['membre_avatar'].'" style="width:60px;" alt="pas d\'avatar"></p>
        <label for="avatar")>Changer votre avatar :</label>
        <input type="file" name="avatar"  accept=".jpeg, .jpg, .gif, .png" title="" value =""><br /><div style="font-size:small">(jpg, png ou gif seulement, taille taille max : '.$avmxszko.' ko)</div><br />
               <label><input type="checkbox" name="delete" value="Delete" />
        Ou bien supprimer l\'avatar actuel&nbsp;: </label><img src="./images/avatars/'.$data['membre_avatar'].'" style="width:20px;"alt="pas d\'avatar" /><br />

    <label for="signature">Signature&nbsp;: </label><textarea cols="40" rows="4" name="signature" id="signature" style="text-align:left">'.$data['membre_signature'].
    '</textarea>
        </fieldset>
        <p>
        <input type="submit" value="Modifier son profil" />
        <input type="hidden" id="sent" name="sent" value="1" />
        </p></form>';
        $query->closeCursor();   
    }   
    else //Sinon on est dans la page de traitement
    {
     //On déclare les variables 

    $mdp_erreur = NULL;
    $email_erreur1 = NULL;
    $email_erreur2 = NULL;
    $msn_erreur = NULL;
    $signature_erreur = NULL;
    $avatar_erreur = NULL;
    $avatar_erreur1 = NULL;
    $avatar_erreur2 = NULL;
    $avatar_erreur3 = NULL;

    //Encore et toujours notre belle variable $i :p
    $i = 0;
    $temps = time(); 
    $signature = $_POST['signature'];
    $email = $_POST['email'];
    $msn = $_POST['msn'];
    $website = $_POST['website'];
    $localisation = $_POST['localisation'];

    
     //Vérification de l'adresse email
    //Il faut que l'adresse email n'ait jamais été utilisée (sauf si elle n'a pas été modifiée)

    //On commence donc par récupérer le mail
    $query=$db->prepare('SELECT membre_email FROM forum_membres WHERE membre_id =:id'); 
    $query->bindValue(':id',$id,PDO::PARAM_INT);
    $query->execute();
    $data=$query->fetch();
    if (strtolower($data['membre_email']) != strtolower($email))
    {
        //Il faut que l'adresse email n'ait jamais été utilisée
        $query=$db->prepare('SELECT COUNT(*) AS nbr FROM forum_membres WHERE membre_email =:mail');
        $query->bindValue(':mail',$email,PDO::PARAM_STR);
        $query->execute();
        $mail_free=($query->fetchColumn()==0)?1:0;
        $query->CloseCursor();
        if(!$mail_free)
        {
            $email_erreur1 = "Votre adresse email est déjà utilisé par un membre";
            $i++;
        }

        //On vérifie la forme maintenant
        if (!preg_match("#^[a-z0-9A-Z._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$#", $email) || empty($email))
        {
            $email_erreur2 = "Votre nouvelle adresse E-Mail n'a pas un format valide";
            $i++;
        }
    }
    //Vérification de l’adresse MSN
    if (!preg_match("#^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$#", $msn) && !empty($msn))
    {
        $msn_erreur = "Votre nouvelle adresse MSN n'a pas un format valide";
        $i++;
    }

    //Vérification de la signature
    if (strlen($signature) > $config['sign_maxl'])//paramétré MAP
    {
        $signature_erreur = "Votre nouvelle signature est trop longue";
        $i++;
    }
 
    //Vérification de l'avatar
    if (!empty($_FILES['avatar']['size']))//MAP
    {
//print_r($_FILES['avatar']);
        //On définit les variables :
        $maxsize = $config["avatar_maxsize"]; //Poid de l'image
        $maxwidth = $config["avatar_maxl"]; //Largeur de l'image
        $maxheight = $config["avatar_maxh"]; //Longueur de l'image
        //Liste des extensions valides
        $extensions_valides = array( 'jpg' , 'jpeg' , 'gif' , 'png', 'bmp' );
 
        if ($_FILES['avatar']['error'] > 0)
        {
        $avatar_erreur = "Erreur lors du transfert de l'avatar : ";
        }
        
        //echo '<br />'.$config["avatar_maxsize"].'<'.$_FILES['avatar']['size'].'<br />';die;
        
        if ($_FILES['avatar']['size'] > $maxsize)
        {
        $i++;
        $avatar_erreur1 = "Le fichier est trop gros :
        (<strong>".$_FILES['avatar']['size']." Octets</strong>
        contre <strong>".$maxsize." Octets</strong>)";
        }
 
        $image_sizes = getimagesize($_FILES['avatar']['tmp_name']);
        if ($image_sizes[0] > $maxwidth OR $image_sizes[1] > $maxheight)
        {
        $i++;
        $avatar_erreur2 = "Image trop large ou trop longue :
        (<strong>".$image_sizes[0]."x".$image_sizes[1]."</strong> contre
        <strong>".$maxwidth."x".$maxheight."</strong>)";
        }
 
        $extension_upload = strtolower(substr(strrchr($_FILES['avatar']['name'], '.')  ,1));
        if (!in_array($extension_upload,$extensions_valides) )
        {
                $i++;
                $avatar_erreur3 = "Extension de l'avatar incorrecte";
        }
    }

    echo '<p><i>Vous êtes ici</i> : <a href="./index.php">Index du forum</a> --> Modification du profil';
    echo '<h1>Modification d\'un profil</h1>';
//vérifier l'enregistrement MAP 
    if ($i == 0) // Si $i est vide, il n'y a pas d'erreur
    {
        if (!empty($_FILES['avatar']['size']))
        {
                $nomavatar=move_avatar($_FILES['avatar']);
                $query=$db->prepare('UPDATE forum_membres
                SET membre_avatar = :avatar 
                WHERE membre_id = :id');
                $query->bindValue(':avatar',$nomavatar,PDO::PARAM_STR);
                $query->bindValue(':id',$id,PDO::PARAM_INT);
                $query->execute();
                $query->closeCursor();
        }
        //Une nouveauté ici : on peut choisir de supprimer l'avatar
        if (isset($_POST['delete']))
        {
                $query=$db->prepare('UPDATE forum_membres
		SET membre_avatar=0 WHERE membre_id = :id');
                $query->bindValue(':id',$id,PDO::PARAM_INT);
                $query->execute();
                $query->CloseCursor();
        }

        echo'<h1>Modification terminée</h1>';
        echo'<p>Votre profil a été modifié avec succès !</p>';
        echo'<p>Cliquez <a href="./index.php">ici</a> 
        pour revenir à la page d accueil</p>';
 
        //On modifie la table
 
        $query=$db->prepare('UPDATE forum_membres
        SET  membre_email=:mail, membre_msn=:msn, membre_siteweb=:website,
        membre_signature=:sign, membre_localisation=:loc
        WHERE membre_id=:id');
        $query->bindValue(':mail',$email,PDO::PARAM_STR);
        $query->bindValue(':msn',$msn,PDO::PARAM_STR);
        $query->bindValue(':website',$website,PDO::PARAM_STR);
        $query->bindValue(':sign',$signature,PDO::PARAM_STR);
        $query->bindValue(':loc',$localisation,PDO::PARAM_STR);
        $query->bindValue(':id',$id,PDO::PARAM_INT);
        $query->execute();
        $query->closeCursor();
    }
    else
    {
        echo'<h1>Modification interrompue</h1>';
        echo'<p>Une ou plusieurs erreurs se sont produites pendant la modification du profil&nbsp;:</p>';
        echo'<p>'.$i.' erreur(s)</p>';
        echo'<p>'.$email_erreur1.'</p>';
        echo'<p>'.$email_erreur2.'</p>';
        echo'<p>'.$msn_erreur.'</p>';
        echo'<p>'.$signature_erreur.'</p>';
        echo'<p>'.$avatar_erreur.'</p>';
        echo'<p>'.$avatar_erreur1.'</p>';
        echo'<p>'.$avatar_erreur2.'</p>';
        echo'<p>'.$avatar_erreur3.'</p>';
        echo'<p> Cliquez <a href="./voirprofil.php?action=modifier">ici</a> pour recommencer</p>';
    }
} //Fin du else if non empty

    break;
 
default; //Si jamais c'est aucun de ceux là c'est qu'il y a eu un problème :o
echo'<p>Cette action est impossible</p>';
 
} //Fin du switch
}//fin de if id = 0
echo '</div>';
include("../inclus/fin.php");
?>

