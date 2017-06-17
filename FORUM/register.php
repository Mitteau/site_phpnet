<?php
include('includes/constants.php');
include("includes/identifiants.php");
include('includes/start_session.php');
$titre = "Enregistrement";
include("includes/debut_forum.php");
include("includes/menu_forum.php");

echo '<p><i>Vous êtes ici</i> : <a href="./index.php">Index du forum</a> --> Enregistrement';
//if ($id!=0) erreur(ERR_IS_CO);////////MAP
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
	while($data=$query->fetch()){$config[$data['config_nom']]=$data['config_valeur'];}
	$query->closeCursor();
	
if (empty($_POST['pseudo'])) // Si on la variable est vide, on peut considérer qu'on est sur la page de formulaire
{//formulaire
    echo '<h1>Inscription<!-- 1/2--></h1>';
    $avmxsz=$config["avatar_maxsize"];
    $avmxszko=floor($avmxsz/1024);
//    echo $avmxszko;die;
    echo '<form method="post" action="register.php" enctype="multipart/form-data">
    <fieldset><legend>Identifiants</legend>
    <label for="pseudo">* Pseudo :</label>  <input name="pseudo" type="text" id="pseudo" autofocus/> (le pseudo doit contenir entre '.$config["pseudo_minsize"].' et '.$config["pseudo_maxsize"].' caractères)<br />
    <label for="password">* Mot de Passe :</label><input type="password" name="password" id="password" /><br />
    <label for="confirm">* Confirmer le mot de passe :</label><input type="password" name="confirm" id="confirm" />
    </fieldset>
    <fieldset><legend>Contacts</legend>
    <label for="email">* Votre adresse Mail :</label><input type="text" name="email" id="email" /><br />
    <label for="msn">Votre adresse MSN :</label><input type="text" name="msn" id="msn" /><br />
    <label for="website">Votre site web :</label><input type="text" name="website" id="website" />
    </fieldset>
    <fieldset><legend>Informations supplémentaires</legend>
    <label for="localisation">Localisation :</label><input type="text" name="localisation" id="localisation" />
    </fieldset>
    <fieldset><legend>Profil sur le forum</legend>
    <label for="avatar">Choisissez votre avatar :</label><input type="file" name="avatar" id="avatar"  accept=".jpg, .gif, .png"/><br /><div style="font-size:small">(jpg, png ou gif seulement, taille max : '.$avmxszko.' ko)</div><br />
    <label for="signature">Signature&nbsp;: </label><textarea cols="40" rows="4" name="signature" placeholder="La signature est limit&eacute;e à '.$config["sign_maxl"].' caractères." id="signature" style="text-align:left"></textarea>
    </fieldset>
    <p>Les champs pr&eacute;c&eacute;d&eacute;s d\'un * sont obligatoirement remplis</p>
    <p><input type="submit" value="S\'inscrire" /></p></form>';
} //Fin de la partie formulaire
else //On est dans le cas traitement
{
$mdp_fixe=array(
"EGYPTE2000",
"JMMG",
"famille",
"Canon",
"Tamanrasset",
"RUSSIA",
"supaero",
"arpeggio",
"Loire",
"canal",
"promo59"
); // doit coïncider avec fichier jldo
//print_r($mdp_fixe);die;

    $pseudo_erreur0 = NULL;
    $pseudo_erreur1 = NULL;
    $pseudo_erreur2 = NULL;
    $mdp_erreur = NULL;
    $email_erreur1 = NULL;
    $email_erreur2 = NULL;
    $msn_erreur = NULL;
    $signature_erreur = NULL;
    $avatar_erreur = NULL;
    $avatar_erreur1 = NULL;
    $avatar_erreur2 = NULL;
    $avatar_erreur3 = NULL;
    //On récupère les variables
    $i = 0;
    $temps = time(); 
    $pseudo=$_POST['pseudo'];
    $signature = $_POST['signature'];
    $email = $_POST['email'];
    $msn = $_POST['msn'];
    $website = $_POST['website'];
    $localisation = $_POST['localisation'];
    $pass = $_POST['password'];
    $confirm =$_POST['confirm'];
    
    //Vérification du pseudo
	if (in_array($pseudo,$mdp_fixe))
    {
        $pseudo_erreur0 = "Ce pseudo est r&eacute;serv&eacute; pour le site.";
        $i++;
    }


    $query=$db->prepare('SELECT COUNT(*) AS nbr FROM forum_membres WHERE membre_pseudo =:pseudo');
    $query->bindValue(':pseudo',$pseudo, PDO::PARAM_STR);
    if (!$query->execute()) echo "<p>La lecture ne s'est pas d&eacute;roul&eacute;e correctement.</p>";
    $pseudo_free=($query->fetchColumn()==0)?1:0;
    $query->closeCursor();



    if(!$pseudo_free)
    {
        $pseudo_erreur1 = "Votre pseudo est d&eacute;j&agrave; utilis&eacute; par un membre.";
        $i++;
    }
    //echo strlen($pseudo);die;
    if (strlen($pseudo) < $config["pseudo_minsize"] || strlen($pseudo) > $config["pseudo_maxsize"])
    {
        $pseudo_erreur2 = "Votre pseudo est soit trop grand, soit trop petit.";
        $i++;
    }
    //Vérification du mdp
    if ($pass != $confirm || empty($confirm) || empty($pass))
    {
        $mdp_erreur = "Votre mot de passe et votre confirmation diff&egrave;rent, ou sont vides.";
        $i++;
    }
    else $pass=password_hash($pass,PASSWORD_BCRYPT); // bcrypt, coût 10, MAP
    //Vérification de l'adresse email
    //Il faut que l'adresse email n'ait jamais été utilisée
    $query=$db->prepare('SELECT COUNT(*) AS nbr FROM forum_membres WHERE membre_email =:mail');
    $query->bindValue(':mail',$email, PDO::PARAM_STR);
    if (!$query->execute()) echo "<p>La lecture ne s'est pas d&eacute;roul&eacute;e correctement.</p>";
    $mail_free=($query->fetchColumn()==0)?1:0;
    $query->closeCursor();
    
    if(!$mail_free)
    {
        $email_erreur1 = "Votre adresse email est d&eacute;j&agrave; utilis&eacute;e par un membre.";
        $i++;
    }
    //On vérifie la forme maintenant
    if (!preg_match("#^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]{2,}\.[a-z]{2,4}$#", $email) || empty($email))
    {
        $email_erreur2 = "Votre adresse E-Mail n'a pas un format valide.";//MAP
        $i++;
    }
    //Vérification de l'adresse MSN
    if (!preg_match("#^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]{2,}\.[a-z]{2,4}$#", $msn) && !empty($msn))
    {
        $msn_erreur = "Votre adresse MSN n'a pas un format valide.";
        $i++;
    }
    //Vérification de la signature

    if (strlen($signature) > $config['sign_maxl'])
    {
        $signature_erreur = "Votre signature est trop longue.";
        $i++;
    }
    //Vérification de l'avatar :
    if (!empty($_FILES['avatar']['size']))
    {
        //On définit les variables :
        $maxsize = $config["avatar_maxsize"]; //Poid de l'image
        $maxwidth = $config["avatar_maxl"]; //Largeur de l'image
        $maxheight = $config["avatar_maxh"]; //Longueur de l'image
        $extensions_valides = array( 'jpg' , 'jpeg' , 'gif' , 'png', 'bmp' ); //Liste des extensions valides
        
        if ($_FILES['avatar']['error'] > 0)
        {
                $avatar_erreur = "Erreur lors du transfert de l'avatar : ";
        }
        if ($_FILES['avatar']['size'] > $maxsize)
        {
                $i++;
                $avatar_erreur1 = "Le fichier est trop gros : (<strong>".$_FILES['avatar']['size']." Octets</strong>    contre <strong>".$maxsize." Octets.</strong>)";
        }
        $image_sizes = getimagesize($_FILES['avatar']['tmp_name']);
        if ($image_sizes[0] > $maxwidth OR $image_sizes[1] > $maxheight)
        {
                $i++;
                $avatar_erreur2 = "Image trop large ou trop longue : 
                (<strong>".$image_sizes[0]."x".$image_sizes[1]."</strong> contre <strong>".$maxwidth."x".$maxheight.".</strong>)";
        }
        
        $extension_upload = strtolower(substr(strrchr($_FILES['avatar']['name'], '.')  ,1));
        if (!in_array($extension_upload,$extensions_valides) )
        {
                $i++;
                $avatar_erreur3 = "Extension de l'avatar incorrecte.";
        }
    }

if ($i==0){
   
        //La ligne suivante sera commentée plus bas
    $nomavatar=(!empty($_FILES['avatar']['size']))?move_avatar($_FILES['avatar']):''; 
   
        $query=$db->prepare('INSERT INTO forum_membres (membre_pseudo, membre_mdp, membre_email,             
        membre_msn, membre_siteweb, membre_avatar,
        membre_signature, membre_localisation, membre_inscrit,   
        membre_derniere_visite,membre_rang,membre_post)
        VALUES (:pseudo, :pass, :email, :msn, :website, :nomavatar, :signature, :localisation, :temps, :temps, :rang, :post)');
    $query->bindValue(':pseudo', $pseudo, PDO::PARAM_STR);
    $query->bindValue(':pass', $pass, PDO::PARAM_INT);
    $query->bindValue(':email', $email, PDO::PARAM_STR);
    $query->bindValue(':msn', $msn, PDO::PARAM_STR);
    $query->bindValue(':website', $website, PDO::PARAM_STR);
    $query->bindValue(':nomavatar', $nomavatar, PDO::PARAM_STR);
    $query->bindValue(':signature', $signature, PDO::PARAM_STR);
    $query->bindValue(':localisation', $localisation, PDO::PARAM_STR);
    $query->bindValue(':temps', $temps, PDO::PARAM_INT);
    $query->bindValue(':rang', '2', PDO::PARAM_INT);
    $query->bindValue(':post', '0', PDO::PARAM_INT);
	 try {$query->execute();}
	 catch (Exception $e){die ('Erreur : ' . $e->getMessage());}

// insertion dans mots de passe  site
$fichier=fopen("file:///home/users4/z/ziboul/opt/jldo","r");
if ($fichier) {
   while (!feof($fichier)) {
       $contenu[] = fgets($fichier, 4096);
   }
 fclose($fichier);
}

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

//echo '<pre>';
//print_r($contenu);
//echo '</pre>';


//array_pop($contenu);
$contenu=array_values($contenu);
reset($contenu);
$str=$pseudo.':'.$pass.PHP_EOL;
$contenu=array_values($contenu);
array_push($contenu,$str);
array_push($contenu,PHP_EOL);
$str=implode("",$contenu);
//echo $str.'----';

$fichier=fopen("file:///home/users4/z/ziboul/opt/jldo","wb+");
if ($fichier) {
// if ($ok && fwrite($fichier,$str)>0) $ok=true; else $ok=false;
 if (fwrite($fichier,$str)>0) $ok=true; else $ok=false;
 fclose($fichier);
}

        
        
if ($ok) echo '<p>Mot de passe g&eacute;n&eacute;r&eacute; pour '.$pseudo.'</p>';
else echo '&eacute;chec &nbsp;!<br />';



//mail de confirmation";

    //Et on définit les variables de sessions
        $_SESSION['pseudo'] = $pseudo;
        $_SESSION['id'] = $db->lastInsertId();
        $_SESSION['rang'] = 2;
        $_SESSION['level'] = 2;
        
//message de congratulations
echo 'Merci de vous &ecirc;tre int&eacute;ress&eacute; &agrave; ce forum. Bonne navigation&nbsp;!';        
?>
<!--MAPMAP
<script type="text/javascript">
window.open("mailto:'.
<?php echo $email;?>.'?subject=Votre inscription&body=Merci. Vous êtes inscrite avec le pseudo "
<?php echo $pseudo;?>
" et le MP "
<?php echo $pass;?>
". Vous devriez changer votre MP (page \"Modifier son profil).Jean-Claude Mitteau\")");-->

<script type="text/javascript">
function retour(){
   location.assign("index.php");}
</script>
	<p><button onClick="retour()">Retour à l'accueil.</button>
	</p>
<!--<script type="text/javascript">
retour();
</script>-->
<?php    
    }
    else
    {
        echo'<h1>Inscription interrompue</h1>';
        echo'<p>Une ou plusieurs erreurs se sont produites pendant l\'incription</p>';
        echo'<p>'.$i.' erreur(s)</p>';
        echo'<p>'.$pseudo_erreur0.'</p>';
        echo'<p>'.$pseudo_erreur1.'</p>';
        echo'<p>'.$pseudo_erreur2.'</p>';
        echo'<p>'.$mdp_erreur.'</p>';
        echo'<p>'.$email_erreur1.'</p>';
        echo'<p>'.$email_erreur2.'</p>';
        echo'<p>'.$msn_erreur.'</p>';
        echo'<p>'.$signature_erreur.'</p>';
        echo'<p>'.$avatar_erreur.'</p>';
        echo'<p>'.$avatar_erreur1.'</p>';
        echo'<p>'.$avatar_erreur2.'</p>';
        echo'<p>'.$avatar_erreur3.'</p>';
       
        echo'<p>Cliquez <a href="./register.php">ici</a> pour recommencer</p>';
    }
}
echo'</div>';
include("../inclus/fin.php");
?>

