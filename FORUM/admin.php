<?php
include('includes/constants.php');
include("includes/identifiants.php");
include('includes/start_session.php');
$titre = "Administration du forum";
include("includes/debut_forum.php");
include("includes/menu_forum.php");

// On indique où l'on se trouve
$cat = (isset($_GET['cat']))?htmlspecialchars($_GET['cat']):'';

echo'<p><i>Vous êtes ici</i> : <a href="./index.php">Accueil du forum</a> --> <a href="./admin.php">Administration du forum</a><br>';

if (!verif_auth(ADMIN)) erreur(ERR_AUTH_ADMIN);

switch($cat) //1er switch
{
case "config":
//ici configuration
	echo'<h1>Configuration générale du forum</h1>';
	echo '<form method="post" action="adminok.php?cat=config">';

	//Le tableau associatif
	$config_name = array(
	"avatar_maxsize" => "Taille maximale de l'avatar (octets)",
	"avatar_maxh" => "Hauteur maximale de l'avatar",
	"avatar_maxl" => "Largeur maximale de l'avatar",
	"sign_maxl" => "Taille maximale de la signature",
	"auth_bbcode_sign" => "Autoriser le bbcode dans la signature",
	"pseudo_maxsize" => "Taille maximale du pseudo",
	"pseudo_minsize" => "Taille minimale du pseudo",
	"topic_par_page" => "Nombre de sujets par page",
	"post_par_page" => "Nombre de posts par page",
	"forum_titre" => "Titre du forum",
	"temps_flood" => "Franchise de temps anti-flood",
	"lign_par_page" => "Nombres de lignes par page"
	);
	try{$query = $db->query('SELECT config_nom, config_valeur FROM forum_config');}
	catch (Exception $e){die ('Erreur : ' . $e->getMessage());}

	while($data=$query->fetch())
	{
           echo '<p><label for='.$data['config_nom'].'>'.$config_name[$data['config_nom']].' </label> :
           <input type="text" id="'.$data['config_nom'].'" value="'.$data['config_valeur'].'" name="'.$data['config_nom'].'"></p>';
	}
	echo '<p><input type="submit" value="Envoyer" /></p></form>';
	$query->closeCursor();
break;


case "forum":
//Ici forum
$action = htmlspecialchars($_GET['action']); //On récupère la valeur de action
        switch($action) //2eme switch
        {
        case "creer":
        //Création d'un forum

        //1er cas : pas de variable c
        if(empty($_GET['c']))
        {
                echo'<br /><br /><br />Que voulez-vous faire?<br />
                <a href="./admin.php?cat=forum&action=creer&c=f">Créer un forum</a><br />
                <a href="./admin.php?cat=forum&action=creer&c=c">Créer une catégorie</a></br>';
        }

        //2ème cas : on cherche à créer un forum (c=f)
        elseif($_GET['c'] == "f")
        {
                $query=$db->query('SELECT cat_id, cat_nom FROM forum_categorie
                ORDER BY cat_ordre ASC');
                echo'<h1>Création d un forum</h1>';
                echo'<form method="post" action="./adminok.php?cat=forum&action=creer&c=f">';
                echo'<label>Nom :</label><input type="text" id="nom" name="nom" /><br /><br />
                <label>Description :</label>
                <textarea cols=40 rows=4 name="desc" id="desc"></textarea>
                <br /><br />
                <label>Cat&eacute;gorie : </label><select name="cat">';
                while($data = $query->fetch())
                {
		    echo'<option value="'.$data['cat_id'].'">'.$data['cat_nom'].'</option>';
                }
                echo'</select><br /><br />
                <input type="submit" value="Envoyer"></form>';
		$query->closeCursor();
        }       
        //3ème cas : on cherche à créer une catégorie (c=c)
        elseif($_GET['c'] == "c")
        {
                echo'<h1>Création d une catégorie</h1>';
                echo'<form method="post" action="./adminok.php?cat=forum&action=creer&c=c">';
                echo'<label> Indiquez le nom de la catégorie :</label>
                <input type="text" id="nom" name="nom" /><br /><br />   
                <label> Indiquez l\'ordre de pr&eacute;sentation de la catégorie :</label>
                <input type="text" id="ordre" name="ordre" /><br /><br />   
                <input type="submit" value="Envoyer"></form>';
        }

        break;
        
        case "edit":
        //Edition d'un forum
        echo'<h1>&Eacute;dition d un forum</h1>';
       
        if(!isset($_GET['e']))
        {
                echo'<p>Que voulez-vous faire&nbsp;?<br />
                <a href="./admin.php?cat=forum&action=edit&amp;e=editf">
                &Eacute;diter un forum</a><br />
                <a href="./admin.php?cat=forum&action=edit&amp;e=editc">
                &Eacute;diter une cat&eacute;gorie</a><br />
                <a href="./admin.php?cat=forum&action=edit&amp;e=ordref">
                Changer l\'ordre des forums</a><br />
                <a href="./admin.php?cat=forum&action=edit&amp;e=ordrec">
                Changer l\'ordre des cat&eacute;gories</a>
                <br /></p>';
        }

        elseif($_GET['e'] == "editf")
        {
//            echo $_POST['forum'];
            //On affiche dans un premier temps la liste des forums
//            if(!isset$_POST['forum')
//            {
		$query=$db->query('SELECT forum_id, forum_name
		FROM forum_forum ORDER BY forum_ordre DESC');
			   
		echo'<form method="post" action="admin.php?cat=forum&amp;action=edit&amp;e=editf">';
		echo'<p>Choisir un forum :</br /></h2>
		<select name="forum">
		</form>';
				   
		while($data = $query->fetch())
		{
		    echo'<option value="'.$data['forum_id'].'">
		    '.stripslashes(htmlspecialchars($data['forum_name'])).'</option>';
		}
		echo'<input type="submit" value="Envoyer"></p></form>';
		$query->closeCursor();

            //Ensuite, on affiche les renseignements sur le forum choisi

	    if (isset($_POST['forum'])){
	    $query = $db->prepare('SELECT forum_id, forum_name, forum_desc,
		forum_cat_id
		FROM forum_forum
		WHERE forum_id = :forum');
		$query->bindValue(':forum',(int) $_POST['forum'],PDO::PARAM_INT);
		$query->execute();
				
		$data1 = $query->fetch();

		echo'<p>Edition du forum
		<strong>'.stripslashes(htmlspecialchars($data1['forum_name'])).'</strong></p>';
				   
		echo'<form method="post" action="adminok.php?cat=forum&amp;action=edit&amp;e=editf">
		<label>Nom du forum : </label><input type="text" id="nom"
		name="nom" value="'.$data1['forum_name'].'" />
		<br />
				   
		<label>Description :</label><textarea cols=40 rows=4 name="desc"
		id="desc">'.$data1['forum_desc'].'</textarea><br /><br />';
		$query->closeCursor();				  
		//A partir d'ici, on boucle toutes les catégories,
		//On affichera en premier celle du forum

		$query = $db->query('SELECT cat_id, cat_nom
		FROM forum_categorie ORDER BY cat_ordre DESC');
		echo'<label>Déplacer le forum vers : </label>
		<select name="depl">';
		while($data2 = $query->fetch())
		{
		    if($data2['cat_id'] == $data1['forum_cat_id'])
		    {
		    echo'<option value="'.$data2['cat_id'].'" 
                    selected="selected">'.stripslashes(htmlspecialchars($data2['cat_nom'])).' 
                    </option>';
		    }
		    else
		    {
		        echo'<option value="'.$data2['cat_id'].'">'.$data2['cat_nom'].'</option>';
		    }
	        }
	        echo'</select><input type="hidden" name="forum_id" value="'.$data1['forum_id'].'">';
	        echo'<p><input type="submit" value="Envoyer"></p></form>';
	        $query->closeCursor();				  
				
            }
        }
/////
        elseif($_GET['e'] == "editc")
        {
            //On commence par afficher la liste des catégories
            if(!isset($_POST['cat']))
            {
	        try{$query = $db->query('SELECT cat_id, cat_nom
		FROM forum_categorie ORDER BY cat_ordre DESC');}
		catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
		echo'<form method="post" action="admin.php?cat=forum&amp;action=edit&amp;e=editc">';
		echo'<p>Choisir une catégorie :</br />
		<select name="cat">';
		while($data = $query->fetch())
		{
		    echo'<option value="'.$data['cat_id'].'">'.$data['cat_nom'].'</option>';
		}
		echo'<input type="submit" value="Envoyer"></p></form>';		
                $query->closeCursor();				  					
            }         
            //Puis le formulaire
            else
            {
	        $query = $db->prepare('SELECT cat_nom FROM forum_categorie
	        WHERE cat_id = :cat');
		$query->bindValue(':cat',(int) $_POST['cat'],PDO::PARAM_INT);
		try{$query->execute();}
		catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
		$data = $query->fetch();
		echo'<form method="post" action="./adminok.php?cat=forum&amp;action=edit&amp;e=editc">';

		echo'<label> Indiquez le nom de la catégorie&bsp;:</label>
		<input type="text" id="nom" name="nom"
		value="'.stripslashes(htmlspecialchars($data['cat_nom'])).'" />
		<br />
		<input type="hidden" name="cat" value="'.$_POST['cat'].'" />
		<input type="submit" value="Envoyer" /></p></form>';
		$query->closeCursor();			  
				
            }
        }
////
        elseif($_GET['e'] == "ordref")
        {
            $categorie="";
            $query = $db->query('SELECT forum_id, forum_name, forum_ordre,
            forum_cat_id, cat_id, cat_nom
            FROM forum_categorie
            LEFT JOIN forum_forum ON cat_id = forum_cat_id
            ORDER BY cat_ordre DESC');

            echo'<form method="post"
            action="adminok.php?cat=forum&amp;action=edit&amp;e=ordref">';
               
            echo '<table>';

            while($data = $query->fetch())
            {
	        if( $categorie !== $data['cat_id'] )
		{
		    $categorie = $data['cat_id'];
		    echo'
		    <tr>       
	            <th><strong>'.stripslashes(htmlspecialchars($data['cat_nom'])).'</strong></th>
		    <th><strong>Ordre</strong></th>
		    </tr>';
		}
		echo'<tr><td>
                <a href="./voirforum.php?f='.$data['forum_id'].'">'.$data['forum_name'].'</a></td>
		<td><input type="text" value="'.$data['forum_ordre'].'" name="'.$data['forum_id'].'" />
                </td></tr>';
            }
            echo'</table>
            <p><input type="submit" value="Envoyer" /></p></form>';
				
        }

        elseif($_GET['e'] == "ordrec")
        {
            $query = $db->query('SELECT cat_id, cat_nom, cat_ordre
            FROM forum_categorie
            ORDER BY cat_ordre DESC');
 
            echo'<form method="post" action="adminok.php?cat=forum&amp;action=edit&amp;e=ordrec">';
            while($data = $query->fetch())
            {
		echo'<label>'.stripslashes(htmlspecialchars($data['cat_nom'])).' :</label>
		<input type="text" value="'.$data['cat_ordre'].'"name="'.$data['cat_id'].'" /><br /><br />';
            }
            echo '<input type="submit" value="Envoyer" /></form>';
            $query->closeCursor();				  					
        }
    break;

    case "droits":
        //Gestion des droits
        echo'<h1>&Eacute;dition des droits</h1>';     
       
        if(!isset($_POST['forum']))
        {
            $query=$db->query('SELECT forum_id, forum_name
            FROM forum_forum ORDER BY forum_ordre DESC');
            echo'<form method="post" action="admin.php?cat=forum&action=droits">';
            echo'<p>Choisir un forum :</br />
            <select name="forum">';
            while($data = $query->fetch())
            {
                echo'<option value="'.$data['forum_id'].'">'.$data['forum_name'].'</option>';
            }
            echo'<input type="submit" value="Envoyer"></p></form>';
            $query->closeCursor();				  					
        }
        else
        {
	    $query = $db->prepare('SELECT forum_id, forum_name, auth_view,
	    auth_post, auth_topic, auth_annonce, auth_modo
	    FROM forum_forum WHERE forum_id = :forum');
	    $query->bindValue(':forum',(int) $_POST['forum'], PDO::PARAM_INT);
	    $query->execute();
 
            echo '<form method="post" action="adminok.php?cat=forum&action=droits"><p><table><tr>
	    <th>Lire</th>
	    <th>Répondre</th>
	    <th>Poster</th>
	    <th>Annonce</th>
	    <th>Modérer</th>
	    </tr>';
	    $data = $query->fetch();
		   
	    //Ces deux tableaux vont permettre d'afficher les résultats
	    $rang = array(
            VISITEUR=>"Visiteur",
            INSCRIT=>"Membre", 
            MODO=>"Modérateur",
            ADMIN=>"Administrateur");
	    $list_champ = array("auth_view", "auth_post", "auth_topic","auth_annonce", "auth_modo");
	 
	    //On boucle
	    foreach($list_champ as $champ)
	    {
	        echo'<td><select name="'.$champ.'">';
		for($i=1;$i<5;$i++)
		{
		    if ($i == $data[$champ])
		    {
		        echo'<option value="'.$i.'" selected="selected">'.$rang[$i].'</option>';
		    }	
		    else
		    {
		        echo'<option value="'.$i.'">'.$rang[$i].'</option>';
		    }
		}
		echo'</td></select>';
	    }	
	    echo'<br /><input type="hidden" name="forum_id" value="'.$data['forum_id'].'" />
	    <input type="submit" value="Envoyer"></p></form>';			          

            $query->closeCursor();				  					

        }
        echo '</table>';
    break;

case "deplace":
	echo'<h1>Déplacement d\'un forum</h1>';
	//choix du forum
            try{$query=$db->query('SELECT forum_id, forum_name, forum_cat_id, forum_categorie.cat_nom
            FROM forum_forum
            LEFT JOIN forum_categorie ON forum_forum.forum_cat_id = forum_categorie.cat_id
            ORDER BY forum_id ASC');}
			catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
			$data = $query->fetchAll();
			$query->closeCursor();

            $i=0;
            echo'<p>Choisir un forum&nbsp;:<br />';//MAP réaffichage de la valeur choisie
            echo'<form method="post">
            <select name="forum" >';
            while($data[$i]){
              echo'<option value="'.$i.'">'.$data[$i][1].' &isin; '.$data[$i][3].'</option>';
              $i++;}
            echo'</select>
            <input type="submit" value="Choisir">
            </form></p>';
            $j = $_POST['forum'];
            echo "for. n° ".$j."<br>";//exact
            echo "for. n° ".$j." soit ".$data[$j][0]." soit ".$data[$j][1]."<br>";
            $cat=$data[$j][3];
            echo "Cat. ".$cat."<br>";
//            $f=$_POST['forum'];
            
            
            
            
//            echo $f;
	//choix de la catégorie
            $i=O;//echo xxxxxxxxxxxxx;die;
            try{$query=$db->query('SELECT cat_id, cat_nom
            FROM forum_categorie ORDER BY cat_nom ASC');}
            catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
			$data = $query->fetchAll();
print_r($data[0]);
/*print_r($data[1]);
print_r($data[2]);
print_r($data[3]);
print_r($data[4]);
print_r($data[5]);
print_r($data[6]);
print_r($data[7]);
print_r($data[8]);
print_r($data[9]);
print_r($data[10]);
print_r($data[11]);
print_r($data[12]);*/
//while($i<2){print_r($data[$i]);$i++;}
die;
//            echo'<form method="post" action="adminok.php?cat=forum&action=deplacer">';
            echo'<p>Choisir la nouvelle catégorie d\'accueil :<br>';//MAP réaffichage de la valeur choisie
            echo'<form method="post">
            <select name="cat">';
            while($data[$i]){
              echo'<option value="'.$i.'">'.$data[$i][0].'</option>';
              $i++;}
            echo'</select><input type="submit" value="Choisir"></form></p>';
            $query->closeCursor();
            echo $i."<br>";
            echo "<<".$POST['cat']."<br>";
            echo $cat.'-------'.$data[$POST['cat']][1];
//            if ($cat<>$POST['cat']) echo "différent";
  //          else echo "identique";

	
	break;

case "supprime":
	if (!verif_auth(ADMIN)) erreur(ERR_AUTH_ADMIN);// de nouveau
	echo'<h1>Suppression d\'un forum</h1>';
	//choix du forum
            $query=$db->query('SELECT forum_id, forum_name
            FROM forum_forum ORDER BY forum_ordre DESC');
            echo'<form method="post" action="adminok.php?cat=forum&action=supprimer">';
            echo'<p>Choisir un forum :</br />
            <select name="forum">';
            while($data = $query->fetch())
            {
                echo'<option value="'.$data['forum_id'].'">'.$data['forum_name'].'</option>';
            }
            echo'<input type="submit" value="Choisir"></p></form>';
            $query->closeCursor();

	
	break;



        
        default; //action n'est pas remplie, on affiche le menu
        echo'<h1>Administration des forums</h1>';
        echo'<p>Bonjour, cher administrateur, que voulez-vous faire&nbsp;?</p>
        <p>
        <a href="./admin.php?cat=forum&amp;action=creer">Créer un forum</a>
        <br />
        <a href="./admin.php?cat=forum&amp;action=edit">Modifier un forum</a>
        <br />
        <a href="./admin.php?cat=forum&amp;action=droits">Modifier les droits d\'un forum</a
        ><br />
        <a href="./admin.php?cat=forum&amp;action=supprime">Supprimer un forum</a></p>
        <p>Les sujets sont g&eacute;r&eacute;s depuis leur page de présentation.</p>';
        break;
        }
break;


//XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxMEMBRES

case "membres":
//Ici membres
    $action = htmlspecialchars($_GET['action']); //On récupère la valeur de action
    switch($action) //2ème switch
    {
        case "edit"://à compléter MAP
            echo'<h1>&Eacute;dition du profil d\'un membre</h1>';
            
            if(!isset($_POST['membre'])) //Si la variable $_POST['membre'] n'existe pas
            {
                echo'De quel membre voulez-vous éditer le profil ?<br />';
                echo'<br /><form method="post" action="./admin.php?cat=membres&amp;action=edit">
                <p><label for="membre">Inscrivez le pseudo : </label> 
                <input type="text" id="membre" name="membre">
                <input type="submit" name="Chercher">
                </p></form>';
            }
        
        else //sinon
        {
            $pseudo_d = $_POST['membre'];
            echo '<div style="display:inline" id="zone">ici</div>';


            //Requête qui ramène des info sur le membre à 
            //Partir de son pseudo
            $query = $db->prepare('SELECT membre_id, 
            membre_pseudo, membre_email,
            membre_siteweb, membre_signature, 
            membre_msn, membre_localisation, membre_avatar
            FROM forum_membres WHERE LOWER(membre_pseudo)=:pseudo');
	    	$query->bindValue(':pseudo',strtolower($pseudo_d),PDO::PARAM_STR);
            $query->execute();    
            //Si la requête retourne un truc, le membre existe
            if ($data = $query->fetch()) 
            {
		?>
<script type="text/javascript">
function debloque(pseudo){
	document.getElementById("zone").innerHTML="d&eacute;bocage de "+pseudo;
	location.assign("adminok.php?cat=membres&action=debloque&pseudo="+pseudo);
}
function changerMDP(pseudo){
	document.getElementById("zone").innerHTML="r&eacute;initialisation du mot de passe de "+pseudo;
	location.assign("adminok.php?cat=membres&action=change_mdp&pseudo="+pseudo);
}
</script>


        
        <form method="post" action="adminok.php?cat=membres&amp;action=edit" enctype="multipart/form-data">
		<fieldset><legend>Identifiants</legend>
		<label for="pseudo">Pseudo :</label>
		<input type="text" name="pseudo" id="pseudo" 
		value="<?php echo stripslashes(htmlspecialchars($data['membre_pseudo'])) ?>" />&nbsp;
		<input type="button" name="dblk" id="dblk" value="d&eacute;blocage" onclick="debloque('<?php echo stripslashes(htmlspecialchars($data['membre_pseudo'])) ?>')"/><div style="display:inline" id="pseudo" />
		<input type="button" name="mdp" id="mdp" value="changer MP" onclick="changerMDP('<?php echo stripslashes(htmlspecialchars($data['membre_pseudo'])) ?>')"/><br />
		</fieldset>

		<fieldset><legend>Contacts</legend>
		<label for="email">Adresse E_Mail :</label>
		<input type = "text" name="email" id="email"
		value="<?php echo stripslashes(htmlspecialchars($data['membre_email'])) ?>" /><br />
		<label for="msn">Adresse MSN :</label>
		<input type = "text" name="msn" id="msn"
		value="<?php echo stripslashes(htmlspecialchars($data['membre_msn'])) ?>" /><br />
		<label for="website">Site web :</label>
		<input type = "text" name="website" id="website"
		value="<?php echo stripslashes(htmlspecialchars($data['membre_siteweb'])) ?>"/><br />
		</fieldset>

		<fieldset><legend>Informations supplémentaire</legend>
		<label for="localisation">Localisation :</label>
		<input type = "text" name="localisation" id="localisation"
		value="<?php echo stripslashes(htmlspecialchars($data['membre_localisation'])) ?>" />
		<br />
		</fieldset>
			   
		<fieldset><legend>Profil sur le forum</legend>
		<label for="avatar">Changer l'avatar&nbsp;: </label>
		<input type="file" name="avatar" id="avatar" />
		<br /><br />
		<label><input type="checkbox" name="delete" value="Delete" /> Supprimer l avatar</label>
		Avatar actuel :
		<?php echo'
		<img src="./images/avatars/'.$data['membre_avatar'].'" alt="pas d\'avatar" />' ?>
		 
		<br /><br />
		<label for="signature">Signature :</label>
		<textarea cols=40 rows=4 name="signature" id="signature">
		<?php echo $data['membre_signature'] ?></textarea>
		
		<br /></h2>
		</fieldset>
		<?php
		echo'<input type="hidden" value="'.stripslashes($pseudo_d).'" name="pseudo_d">
		<input type="submit" value="Modifier le profil" /></form>';
                $query->closeCursor();

            }
            else echo' <p>Erreur : Ce membre n\'existe pas, <br />
            cliquez <a href="./admin.php?cat=membres&amp;action=edit">ici</a> pour réessayez</p>';
        }
    break;

    case "droits":
        //Droits d'un membre (rang)
        echo'<h1>&Eacute;dition des droits d\'un membre</h1>';  

        if(!isset($_POST['membre']))
        {
                echo'De quel membre voulez-vous modifier les droits ?<br />';
                echo'<br /><form method="post" action="./admin.php?cat=membres&action=droits">
                <p><label for="membre">Inscrivez le pseudo : </label> 
                <input type="text" id="membre" name="membre">
                <input type="submit" value="Chercher"></p></form>';
        }
        else
        {
            $pseudo_d = $_POST['membre'];
            $query = $db->prepare('SELECT membre_pseudo,membre_rang
            FROM forum_membres WHERE LOWER(membre_pseudo) = :pseudo');
	    $query->bindValue(':pseudo',strtolower($pseudo_d),PDO::PARAM_STR);
            $query->execute();
	    if ($data = $query->fetch())
            {       
                echo'<form action="./adminok.php?cat=membres&amp;action=droits" method="post">';
                $rang = array
                (0 => "Bannis",
                1 => "Visiteur", 
                2 => "Membre", 
                3 => "Modérateur", 
                4 => "Administrateur"); //Ce tableau associe numéro de droit et nom
                echo'<label>'.$data['membre_pseudo'].'</label>';
                echo'<select name="droits">';
                for($i=0;$i<5;$i++)
                {
		    if ($i == $data['membre_rang'])
		        {
			    echo'<option value="'.$i.'" selected="selected">'.$rang[$i].'</option>';
			}
			else
			{
			    echo'<option value="'.$i.'">'.$rang[$i].'</option>';
			}
                }
		echo'</select>
		<input type="hidden" value="'.stripslashes($pseudo_d).'" name="pseudo">               
		<input type="submit" value="Envoyer"></form>';
                $query->closeCursor();
            }				  					
            else echo' <p>Erreur : Ce membre n\'existe pas, <br />
            cliquez <a href="./admin.php?cat=membres&amp;action=edit">ici</a> pour réessayer</p>';
        }
    break;

    case "ban":
        //Bannissement
        echo'<h1>Gestion du bannissement</h1>'; 

        //Zone de texte pour bannir le membre
        echo'Quel membre voulez-vous bannir&nbsp;?<br />';
        echo'<br />
        <form method="post" action="./adminok.php?cat=membres&amp;action=ban">
        <label for="membre">Inscrivez le pseudo : </label> 
        <input type="text" id="membre" name="membre">
        <input type="submit" value="Envoyer"><br />';

        //Ici, on boucle : pour chaque membre banni, on affiche une checkbox
        //Qui propose de le débannir
        $query = $db->query('SELECT membre_id, membre_pseudo 
        FROM forum_membres WHERE membre_rang = 0');
        
        //Bien sur, on ne lance la suite que s'il y a des membres bannis !
        if ($query->rowCount() > 0)
        {
        
	    while($data = $query->fetch())
            {
                echo'<br /><label><a href="./voirprofil.php?action=consulter&amp;m='.$data['membre_id'].'">
                '.stripslashes(htmlspecialchars($data['membre_pseudo'])).'</a></label>
                <input type="checkbox" name="'.$data['membre_id'].'" />
                Débannir<br />';
            }
            echo'<p><input type="submit" value="Go !" /></p></form>';
        }
        else echo' <p>Aucun membre banni pour le moment :p</p>';
        $query->closeCursor();
    break;














        echo'<h1>Gestion du bannissement</h1>'; 

        //Zone de texte pour bannir le membre
        echo'Quel membre voulez-vous bannir ?<br />';
        echo'<br />
        <form method="post" action="./adminok.php?cat=membres&amp;action=ban">
        <label for="membre">Inscrivez le pseudo : </label> 
        <input type="text" id="membre" name="membre">
        <input type="submit" value="Envoyer"><br />';

        //Ici, on boucle : pour chaque membre banni, on affiche une checkbox
        //Qui propose de le débannir
        $query = $db->query('SELECT membre_id, membre_pseudo 
        FROM forum_membres WHERE membre_rang = 0');
        
        //Bien sur, on ne lance la suite que s'il y a des membres bannis !
        if ($query->rowCount() > 0)
        {
        
	    while($data = $query->fetch())
            {
                echo'<br /><label><a href="./voirprofil.php?action=consulter&amp;m='.$data['membre_id'].'">
                '.stripslashes(htmlspecialchars($data['membre_pseudo'])).'</a></label>
                <input type="checkbox" name="'.$data['membre_id'].'" />
                Débannir<br />';
            }
            echo'<p><input type="submit" value="Go !" /></p></form>';
        }
        else echo' <p>Aucun membre banni pour le moment :p</p>';
        $query->closeCursor();
    break;

        
        default; //action n'est pas remplie, on affiche le menu 
        echo'<h1>Administration des membres</h1>';
        echo'<p>Salut mon p\'tit, alors tu veux faire quoi ?<br />
        <a href="./admin.php?cat=membres&amp;action=edit">
        &Eacute;diter le profil d\'un membre</a><br />
        <a href="./admin.php?cat=membres&amp;action=droits">
        Modifier les droits d\'un membre</a><br />
        <a href="./admin.php?cat=membres&amp;action=ban">
        Bannir / D&eacute;bannir un membre</a><br /></p>';
        }//switch action membres


break;
case "info_php":
echo '<div><a href="./admin.php">Retour</a></div>';
phpinfo();
break;
default; //cat n'est pas remplie, on affiche le menu général
echo'<h1>Index de l\'administration</h1>';
echo'<p>Bienvenue sur la page d\'administration.</p>
<p>
<a href="./admin.php?cat=config">Configuration du forum</a><br />
<a href="./admin.php?action=&cat=forum">Administration des forums</a><br />
<a href="./admin.php?action=&cat=membres">Administration des membres</a><br />
<a href="./memberlist.php?action=admin">Membres en ligne</a><br />
<a href="./journal.php?action=consulter">Journal des connections</a><br />
<a href="./admin.php?cat=info_php">Informations php</a>';
/*<br />
<a href="./joindre.php">Joindre un ficher</a></p>';*/
break;
}
echo '</div>';
include("../inclus/fin.php");
?>
