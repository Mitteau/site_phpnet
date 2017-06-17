<?php
include('includes/constants.php');
include("includes/identifiants.php");
include('includes/start_session.php');
$titre = "\"Profil\"";
include("includes/debut_forum.php");
include("includes/menu_forum.php");

if ($id==0 || $_SESSION['level'] < 2) erreur(ERR_NO_PROFIL);
else {
echo'<p><i>Vous êtes ici</i> : <a href="./index.php">Accueil du forum</a> -->  <a href="./connexion.php">Connection</a> --> Changer le mot de passe<br>';

$ok=false;
if (!isset($_SESSION['premier_niveau_acquis'])) $_SESSION['premier_niveau_acquis']=false; //passage premier niveau

if (!isset($data['membre_mdp'])){
        //echo 'accès base<br />';
        $query=$db->prepare('SELECT membre_mdp, membre_pseudo
        FROM forum_membres
        WHERE membre_id=:id');
        $query->bindValue(':id',$id,PDO::PARAM_STR);
        $query->execute();
        $data=$query->fetch();
        $query->closeCursor();
        $user=$data['membre_pseudo'];
//echo $data['membre_mdp'];
}

if (!$_SESSION['premier_niveau_acquis']){

if (!isset($_POST['password_init'])){
echo '
 <form method="post" action="changeMP.php">
 <label>Votre ancien mot de passe&nbsp;: </label>
 <input type="password" name="password_init" id="password_init" autofocus/><br />
 <input type="submit" value="Suite" />
 <input type="reset" value="Refaire" /></p></form>
 </form>';
}

else {
//print_r($_POST);
 if (password_verify($_POST['password_init'],$data['membre_mdp'])){
  $ok=true;
  $_SESSION['premier_niveau_acquis']=true;
//  echo 'accès accepté';
  //$_SESSION['ok']=true;//
 } else {
  $ok=false;
  //$$_SESSION['ok']=false;
  echo "<div style=\"color:red\">Passe erron&eacute;, recommencez.</div>";
  echo '<script type="text/javascript">location.assign("changeMP.php");</script>';
 }// verification mot de passe
}//isset password_init	
} // 	acquisition premier niveau
/////////////////////SECOND NIVEAU
if ($_SESSION['premier_niveau_acquis']){

include ('includes/inclusMP.php');


} //premier niveau acquis
}//$id>=2
echo'</div><p><a href="./index.php">Retour &agrave; l\'accueil</a>';
echo '</p>'; 
include("../inclus/fin.php");
?>
