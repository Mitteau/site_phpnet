<?php
include('includes/constants.php');
include("includes/identifiants.php");
include('includes/start_session.php');
$titre = "Administration du forum";
include("includes/debut_forum.php");
include("includes/menu_forum.php");

if (!verif_auth(ADMIN)) erreur(ERR_AUTH_ADMIN);
// On indique où l'on se trouve
$cat = (isset($_GET['cat']))?htmlspecialchars($_GET['cat']):'';//MAP

echo'<p><i>Vous êtes ici</i> : <a href="./index.php">Accueil du forum</a> --> <a href="./admin.php">Administration du forum</a>';

$cat = htmlspecialchars($_GET['cat']); //on récupère dans l'url la variable cat//MAP
switch($cat) //1er switch
{

case "config":
    echo'<h1>Configuration du forum</h1>';
//$query->closeCursor();
    //On récupère les valeurs et le nom de chaque entrée de la table
    $query=$db->query('SELECT config_nom, config_valeur FROM forum_config');
    //Avec cette boucle, on va pouvoir contrôler le résultat pour voir s'il a changé
    $j=0;
    while($data = $query->fetch())
    {
        if ($data['config_valeur'] != $_POST[$data['config_nom']])
		{
            $j++;
            //On met ensuite à jour
            $valeur = htmlspecialchars($_POST[$data['config_nom']]);
		    $query1=$db->prepare('UPDATE forum_config SET config_valeur = :valeur
            WHERE config_nom = :nom');
            $query1->bindValue(':valeur', $valeur, PDO::PARAM_STR);
            
            $query1->bindValue(':nom', $data['config_nom'],PDO::PARAM_STR);
try {$query1->execute(); }  //MAP
catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
			$query1->closeCursor();
		}//fin de if
    }// fin de while
    $query->closeCursor();
    //Et le message !
    if ($j>0) echo'<br /><br />La nouvelle configuration a été mise à jour,<br />  
    Cliquez <a href="./admin.php">ici</a> pour revenir à l\'administration.';
    else echo'<br /><br />Pas de changement dans la configuration,<br />  
    Cliquez <a href="./admin.php">ici</a> pour revenir à l\'administration.';
break;

case "forum":
    //Ici forum
    $action = htmlspecialchars($_GET['action']); //On récupère la valeur de action



    switch($action) //2ème switch15
    {
    case "creer":

        //On commence par les forums
	if ($_GET['c'] == "f")
	{
	    $titre = $_POST['nom'];
	    $desc = $_POST['desc'];
	    $cat = (int) $_POST['cat'];
	    if (!$db) erreur("Pas d'accès base");

	
	    $query=$db->prepare('INSERT INTO forum_forum (forum_cat_id, forum_name, forum_desc,forum_ordre,forum_last_post_id,forum_topic,forum_post,auth_view,auth_post,
	    auth_topic,auth_annonce,auth_modo) 
	    VALUES (:cat, :titre, :desc, 20,0,0,0,1,2,2,3,3)');
            $query->bindValue(':cat',$cat,PDO::PARAM_INT);
            $query->bindValue(':titre',$titre, PDO::PARAM_STR);
            $query->bindValue(':desc',$desc,PDO::PARAM_STR);

try{$query->execute();}   //MAP
catch (Exception $e){die ('Erreur : ' . $e->getMessage());}


//            if (!$query->execute()) erreur("forum non créé");
	    echo'<br /><br />Le forum a été créé !<br />
	    Cliquez <a href="./admin.php">ici</a> pour revenir à l\'administration';
	    $query->closeCursor();
        }
        //Puis par les catégories
        elseif ($_GET['c'] == "c")
        {
            $titre = $_POST['nom'];
            $ordre = $_POST['ordre'];
            $query=$db->prepare('INSERT INTO forum_categorie (cat_nom, cat_ordre) VALUES (:titre,:ordre)');
            $query->bindValue(':titre',$titre, PDO::PARAM_STR); 
            $query->bindValue(':ordre',$ordre, PDO::PARAM_STR); 

try{$query->execute();}   //MAP
catch (Exception $e){die ('Erreur : ' . $e->getMessage());}

            echo'<p>La catégorie a été créée !<br /> Cliquez <a href="./admin.php">ici</a> 
            pour revenir à l\'administration</p>';
	    $query->closeCursor();
        }
    break;

    case "edit":
        echo'<h1>Edition d\'un forum</h1>';
        if($_GET['e'] == "editf")
        {   //Récupération d'informations
	    $titre = $_POST['nom'];
	    $desc = $_POST['desc'];
	    $cat = (int) $_POST['depl'];       
            //Vérification
            $query=$db->prepare('SELECT COUNT(*) 
            FROM forum_forum WHERE forum_id = :id');
            $query->bindValue(':id',(int) $_POST['forum_id'],PDO::PARAM_INT);
            $query->execute();
            $forum_existe=$query->fetchColumn();
try{$query->execute();}   //MAP
catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
            $query->closeCursor();
            if ($forum_existe == 0) erreur(ERR_FOR_EXIST);
            //Mise à jour
            $query=$db->prepare('UPDATE forum_forum 
            SET forum_cat_id = :cat, forum_name = :name, forum_desc = :desc 
            WHERE forum_id = :id');
            $query->bindValue(':cat',$cat,PDO::PARAM_INT);  
            $query->bindValue(':name',$titre,PDO::PARAM_STR);
            $query->bindValue(':desc',$desc,PDO::PARAM_STR);
            $query->bindValue(':id',(int) $_POST['forum_id'],PDO::PARAM_INT);
            $query->execute();
            $query->closeCursor();
            //Message
            echo'<p>Le forum a été modifié !<br />Cliquez <a href="./admin.php">ici</a> 
            pour revenir à l\'administration</p>';        
        }

        elseif($_GET['e'] == "editc")
        {   //Récupération d'informations
            $titre = $_POST['nom'];
            //Vérification
            $query=$db->prepare('SELECT COUNT(*) 
            FROM forum_categorie WHERE cat_id = :cat');
            $query->bindValue(':cat',(int) $_POST['cat'],PDO::PARAM_INT);
try{$query->execute();}   //MAP
catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
            $cat_existe=$query->fetchColumn();
            $query->closeCursor();
            if ($cat_existe == 0) erreur(ERR_CAT_EXIST);
            
            //Mise à jour
            $query=$db->prepare('UPDATE forum_categorie
            SET cat_nom = :name WHERE cat_id = :cat');
            $query->bindValue(':name',$titre,PDO::PARAM_STR);
            $query->bindValue(':cat',(int) $_POST['cat'],PDO::PARAM_INT);
            $query->execute();
            $query->closeCursor();

            //Message
            echo'<p>La catégorie a été modifiée !<br />
            Cliquez <a href="./admin.php">ici</a> 
            pour revenir à l\'administration</p>';
        }

       elseif($_GET['e'] == "ordref")
        {
            //On récupère les id et l'ordre de tous les forums
            $query=$db->query('SELECT forum_id, forum_ordre FROM forum_forum');
            
            //On boucle les résultats
            while($data= $query->fetch())
            {
                $ordre = (int) $_POST[$data['forum_id']]; 
        
                //Si et seulement si l'ordre est différent de l'ancien, on le met à jour
                if ($data['forum_ordre'] != $ordre)
                {
                    $query=$db->prepare('UPDATE forum_forum SET forum_ordre = :ordre
                    WHERE forum_id = :id');
                    $query->bindValue(':ordre',$ordre,PDO::PARAM_INT);
                    $query->bindValue(':id',$data['forum_id'],PDO::PARAM_INT);
try{$query->execute();}   //MAP
catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
                    $query->closeCursor();
                }
            } 
        $query->closeCursor();
        //Message
        echo'<p>L ordre a été modifié !<br /> 
        Cliquez <a href="./admin.php">ici</a> pour revenir à l\'administration</p>';
        }

       elseif($_GET['e'] == "ordrec")
        {   //On récupère les id et les ordres de toutes les catégories
            $query=$db->query('SELECT cat_id, cat_ordre FROM forum_categorie');
           //On boucle le tout
            while($data = $query->fetch())
            {
                $ordre = (int) $_POST[$data['cat_id']]; 
        
                //On met à jour si l'ordre a changé
                if($data['cat_ordre'] != $ordre)
                {
                    $query=$db->prepare('UPDATE forum_categorie SET cat_ordre = :ordre
                    WHERE cat_id = :id');
                    $query->bindValue(':ordre',$ordre,PDO::PARAM_INT);
                    $query->bindValue(':id',$data['cat_id'],PDO::PARAM_INT);
try{$query->execute();}   //MAP
catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
                    $query->closeCursor();
                }
            }
        echo'<p>L\'ordre a été modifié !<br />
        Cliquez <a href="./admin.php">ici</a> pour revenir à l\'administration</p>';
        }
    break;

    case "droits":    
        //Récupération d'informations
        $auth_view = (int) $_POST['auth_view'];
        $auth_post = (int) $_POST['auth_post'];
        $auth_topic = (int) $_POST['auth_topic'];
        $auth_annonce = (int) $_POST['auth_annonce'];
        $auth_modo = (int) $_POST['auth_modo'];
        
        //Mise à jour
        $query=$db->prepare('UPDATE forum_forum
        SET auth_view = :view, auth_post = :post, auth_topic = :topic,
        auth_annonce = :annonce, auth_modo = :modo WHERE forum_id = :id');
        $query->bindValue(':view',$auth_view,PDO::PARAM_INT);
        $query->bindValue(':post',$auth_post,PDO::PARAM_INT);
        $query->bindValue(':topic',$auth_topic,PDO::PARAM_INT);
        $query->bindValue(':annonce',$auth_annonce,PDO::PARAM_INT);
        $query->bindValue(':modo',$auth_modo,PDO::PARAM_INT);
        $query->bindValue(':id',(int) $_POST['forum_id'],PDO::PARAM_INT);
try{$query->execute();}   //MAP
catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
        $query->closeCursor();
      
        //Message
        echo'<p>Les droits ont été modifiés !<br />
        Cliquez <a href="./admin.php">ici</a> pour revenir à l\'administration</p>';
    break;

	case "deplacer":
		$cat = $_POST['cat'];
		echo "<p>Vous avez choisi de déplacer le forum vers la catégorie n°".$cat."</p>";
		if ($_GET['f']){
			$f=$_GET['f'];
			$d=1;}
		else $d=0;
if ($d){

        $query=$db->prepare('UPDATE forum_forum
        SET forum_cat_id = :cat 
        WHERE forum_id = :id');
        $query->bindValue(':cat',$cat,PDO::PARAM_INT);
        $query->bindValue(':id',$f,PDO::PARAM_INT);
try{$query->execute();}   //MAP
catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
        $query->closeCursor();

        echo'<p>Le forum a été transféré !<br />
        Cliquez <a href="./admin.php">ici</a> pour revenir à l\'administration</p>';

}else {
echo '
	<div>
	<button onClick="confirmer('.$cat.','.$forum.')">Déplacer</button>
	<button onClick="annuler()" autofocus>Annuler</button>
		</div>';
?>
<script type="text/javascript">
function confirmer(i,j){
   alert('On transfère');
   location.assign("./adminok.php?cat=forum&action=deplacer&c="+i+"&f"+j);}
   
function annuler(){
   location.assign("./admin.php");}
</script>
	
<?php
    }
    break;

	case "supprimer":
		$forum = $_POST['forum'];
		

            echo "<p>Vous avez choisi de supprimer le forum ".$forum."</p>";			  					

echo '
	<div>
	<button onClick="confirmer(';
	echo $forum.')">Supprimer</button>
	<button onClick="annuler()" autofocus>Annuler</button>
		</div>';
?>
<script type="text/javascript">
function confirmer(i){
   alert('On supprime');
   location.assign("./suppression.php?cat=forum&f="+i);}
   
function annuler(){
   location.assign("./admin.php");}
</script>
	
<?php
    break;

    } //Fin du switch interne

break; 

//XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxxMEMBRES
//Le pseudo doit être unique !
//Il faut donc vérifier s'il a été modifié, si c'est le cas, on vérifie bien 
//l'unicité

case "membres":
    $action = htmlspecialchars($_GET['action']); //On récupère la valeur de action

    switch($action)//2ème switch
    {

    case "edit": //très incomplet MAP

$query=$db->prepare('SELECT COUNT(*) AS nbr FROM forum_membres WHERE membre_pseudo =:pseudo');
$query->execute(array('pseudo'=>$pseudo));
$pseudo_free=($query->fetchColumn()==0)?1:0;
$i=0;
    if(!$pseudo_free)
    {
        $pseudo_erreur1 = "Votre pseudo est déjà utilisé par un membre";
       $i++;
    }
$query->closeCursor();
	break;

    case "droits":
	$membre =$_POST['pseudo'];
	$rang = (int) $_POST['droits'];
	$query=$db->prepare('UPDATE forum_membres SET membre_rang = :rang
	WHERE LOWER(membre_pseudo) = :pseudo');
        $query->bindValue(':rang',$rang,PDO::PARAM_INT);
        $query->bindValue(':pseudo',strtolower($membre), PDO::PARAM_STR);
try{$query->execute();}   //MAP
catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
        $query->closeCursor();
	echo'<p>Le niveau du membre a été modifié !<br />
	Cliquez <a href="./admin.php">ici</a> pour revenir à l\'administration</p>';
    break;

    case "ban":
        //Bannissement dans un premier temps
        //Si jamais on n'a pas laissé vide le champ pour le pseudo
        if (isset($_POST['membre']) AND !empty($_POST['membre']))
        {
            $membre = $_POST['membre'];
            $query=$db->prepare('SELECT membre_id 
            FROM forum_membres WHERE LOWER(membre_pseudo) = :pseudo');    
            $query->bindValue(':pseudo',strtolower($membre), PDO::PARAM_STR);
try{$query->execute();}   //MAP
catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
            //Si le membre existe
            if ($data = $query->fetch())
            {
                //On le bannit
                $query=$db->prepare('UPDATE forum_membres SET membre_rang = 0 
                WHERE membre_id = :id');
                $query->bindValue(':id',$data['membre_id'], PDO::PARAM_INT);
try{$query->execute();}   //MAP
catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
                $query->closeCursor();
                echo'<br /><br />
                Le membre '.stripslashes(htmlspecialchars($membre)).' a bien été banni !<br />';
            }
            else 
            {
                echo'<p>Désolé, le membre '.stripslashes(htmlspecialchars($membre)).' n existe pas !
                <br />
                Cliquez <a href="./admin.php?cat=membres&action=ban">ici</a> 
                pour réessayer</p>';
            }
        }
        //Debannissement ici        
        $query = $db->query('SELECT membre_id FROM forum_membres 
        WHERE membre_rang = 0');
        //Si on veut débannir au moins un membre
        if ($query->rowCount() > 0)
        {
	    $i=0;
            while($data= $query->fetch())
            {
                if(isset($_POST[$data['membre_id']]))//MAP
                {
	            $i++;
                    //On remet son rang à 2
                    $query=$db->prepare('UPDATE forum_membres SET membre_rang = 2 
                    WHERE membre_id = :id');
                    $query->bindValue(':id',$data['membre_id'],PDO::PARAM_INT);
try{$query->execute();}   //MAP
catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
                    $query->closeCursor();
                }
            }
	    if ($i!=0)
            echo'<p>Les membres ont été débannis<br />
            Cliquez <a href="./admin.php">ici</a> pour retourner à l\'administration</p>';
        }
    
	break;
	case "debloque":
		$pseudo=$_GET['pseudo'];
		$query=$db->prepare('SELECT online_index FROM forum_whosonline
		LEFT JOIN forum_membres ON forum_membres.membre_id = forum_whosonline.online_id
		WHERE forum_membres.membre_pseudo = :psd');
		$query->bindValue(':psd',$pseudo,PDO::PARAM_STR);
try{$query->execute();}   //MAP
catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
		$data=$query->fetch();
		$ind=$data['online_index'];
		$query->closeCursor();
		$query=$db->prepare('UPDATE forum_whosonline SET online_id = 0
		WHERE online_index = :ind');
		$query->bindValue(':ind',$ind,PDO::PARAM_STR);
try{$query->execute();}   //MAP
catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
		$query->closeCursor();
		echo '<p>Fait&nbsp; utilisateur '.$pseudo.' d&eacute;bloqu&eacute;.</p>';
	break;
	
	case "change_mdp":
		$pseudo=$_GET['pseudo'];
		$query=$db->prepare('SELECT membre_id FROM forum_membres
		WHERE forum_membres.membre_pseudo = :psd');
		$query->bindValue(':psd',$pseudo,PDO::PARAM_STR);
try{$query->execute();}   //MAP
catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
		$data=$query->fetch();
		$id=$data['membre_id'];
		$query->closeCursor();
		$pass="";
		for ($i=0;$i<8;$i++){
			$pass=$pass.=chr(random_int(48,126));
		}
		$pass0=password_hash($pass,PASSWORD_BCRYPT); // bcrypt, coût 10, MAP
        $query=$db->prepare('UPDATE forum_membres
        SET  membre_mdp = :mdp
        WHERE membre_id=:id');
        $query->bindValue(':mdp',$pass0,PDO::PARAM_STR);
        $query->bindValue(':id',$id,PDO::PARAM_STR);
        $ok=$query->execute();
        $query->closeCursor();
        
$contenu=file("file:///home/users4/z/ziboul/opt/jldo");
//$contenu=file("file:///var/opt/jldo");
reset($contenu);
//echo '<pre>';
//print_r($contenu);
//echo '</pre>';

$vide=false;
while (!$vide){
	$i=0;
	while ($l=each($contenu)){
		$n=substr($l[1],0,strlen($l[1])-1);
		//echo $i.'  '.substr($n,0,strpos($n,':'));
		if ($pseudo == substr($n,0,strpos($n,':')) || $n == "") break;
		$i++;
	}
	unset($contenu[$i]);
	$contenu=array_values($contenu);
	//echo count($contenu).'   '.$i.'  '.$j.'<br />';
	if ($i == count($contenu)) $vide=true;
}

//array_pop($contenu);
$contenu=array_values($contenu);
reset($contenu);
$str=$pseudo.':'.$pass0.PHP_EOL;
$contenu=array_values($contenu);
array_push($contenu,$str);
array_push($contenu,PHP_EOL);
$str=implode("",$contenu);

$fichier=fopen("file:///home/users4/z/ziboul/opt/jldo","wb+");
//$fichier=fopen("file:///var/opt/jldo","wb+");
if ($fichier){
 if ($ok && fwrite($fichier,$str)>0) $ok=true; else $ok=false;
 fclose($fichier);
}

        
        
if ($ok) echo '<p>Mot de passe al&eacute;atoire g&eacute;n&eacute;r&eacute; pour '.$pseudo.'&nbsp;: '.$pass.'</p>';
else echo '&eacute;chec &nbsp;!';
	break;
    } //Fin du switch interne
break;
}//fin du switch principal
echo "</div>";
include("includes/bas.php");
?>


