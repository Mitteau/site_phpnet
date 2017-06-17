<?php
include('includes/constants.php');
include("includes/identifiants.php");
include('includes/start_session.php');
$titre = "Connection";
include("includes/debut_forum.php");
include("includes/menu_forum.php");
$_SESSION['in']=1;

if (!$db) erreur("pas de base");
echo'<p><i>Vous êtes ici</i> : <a href="./index.php">Accueil du forum</a> -->  <a href="./connexion.php">Connection</a><br>';
//echo "<br>".$id."<br>";
if ($id!=0)  erreur(ERR_IS_CO);
else{

echo '<h1>Connection</h1>';

if (!isset($_POST['pseudo'])) //On est dans la page de formulaire
{
    echo '<form method="post" action="connexion.php">
    <fieldset>
    <legend>Connection</legend>
    <p>
    <label for="pseudo">Pseudo&nbsp;: </label><input name="pseudo" type="text" id="pseudo" autofocus/><br />
    <label for="password">Mot de Passe :</label><input type="password" name="password" id="password" />
    </p>
    </fieldset>
    <p><input type="submit" value="Connection" />
    <input type="reset" value="Refaire" /></p></form>
    <a href="./register.php">Pas encore inscrit ?</a></p><br />';
}
//On reprend la suite du code : traitement de la saisie
else{

?>
<script type="text/javascript">
function forcer(name){
    if (confirm("Confirmer") == true)
        location.assign("deconnexion.php?ind="+name);
    else if (confirm("Retour à l\'accueil") == true)
    	location.assign("./index.php");
}
</script>
<?php

//vérifier que vous n'êtes pas déjà connecté ailleurs
$pseudo=$_POST['pseudo'];
$str="";
$rang=0;
        $query=$db->prepare('SELECT online_id, online_index, membre_rang FROM forum_whosonline
        LEFT JOIN forum_membres ON forum_whosonline.online_id = forum_membres.membre_id
        WHERE membre_pseudo = :pseudo');
        $query->bindValue(':pseudo',$pseudo,PDO::PARAM_STR);
        $query->execute();
		$present = 0;
		while ($data=$query->fetch()) {
		 $str=$str.$data['online_index'].',';
		 $present++;
		 $id=$data['online_id'];
		 $rang=$data['membre_rang'];
		}
        $query->closeCursor();
        $str=substr($str,0,strlen($str)-1);
//echo "<br />Index ".$ind." Présent ".$present;die;
//echo $str.'   '.$present;die;
if ($present > 0) {
//echo ERR_AUTRE_IS_CO;
//echo '>'.$rang;die;
echo 'Vous &ecirc;tes d&eacute;j&agrave; en ligne&nbsp;!<br >Pensez &agrave; vous d&eacute;connecter la prochaine fois.';
if ($rang >3){//echo OKKKKKK;
echo '<br /><button onclick="forcer(\''.$str.'\')"> Forcer la d&eacute;connection&nbsp;?</button>';}else{echo '<br />Attendez 2 heures avant de r&eacute;essayer ou contactez le <a href="mailto:site@jcmitteau.net?subject=bloqué&body=merci de débloquer mon compte. Mon pseudo est " style="display:inline;border:0px">webmestre</a>.';}
echo '</div>';
include("../inclus/fin.php");
die;}


    $message='';
    if (empty($_POST['pseudo']) || empty($_POST['password']) ) //Oubli d'un champ
    {
        $message = '<p>une erreur s\'est produite pendant votre identification.
    Vous devez remplir tous les champs</p>
    <p>Cliquez <a href="./connexion.php">ici</a> pour revenir</p>';
    }
    else //On check le mot de passe
    {
//echo '>'.$_POST['pseudo'].'<';
        
        //optimiser les appels
        $query=$db->prepare('SELECT membre_mdp, membre_id, membre_rang, membre_pseudo
        FROM forum_membres WHERE membre_pseudo = :pseudo');
        $query->bindValue(':pseudo',$_POST['pseudo'], PDO::PARAM_STR);
		$query->execute();
        $data=$query->fetch();
        if ($query->rowCount()<1) erreur(ERR_NON_MEMBRE);
        $query->closeCursor();

    if ($data['membre_rang']==0)erreur('vous êtes bannis de ce forum. Vous pouvez contacter l\'administrateur par e-mail &agrave; l\'adresse "site@jcmitteau.net".');

//echo '>'.$data['membre_mdp'].'--'.$data['membre_mdp'].'--'.$data['membre_mdp'].'--'.$data['membre_mdp'].'--'.$data['membre_mdp'].'<<br />';die
//echo '>'.$data['membre_mdp'].'--'.$data['membre_id'].'--'.$data['membre_mdp'].'--'.$data['membre_rang'].'--'.$data['membre_pseudo'].'<<br />';die;
//    if ($data['membre_mdp'] == md5($_POST['password'])) // Acces OK !
    
//    echo $data['membre_rang'];
    if (password_verify($_POST['password'],$data['membre_mdp'])) // Acces OK !
    {
        $_SESSION['pseudo'] = $data['membre_pseudo'];
        $_SESSION['level'] = $data['membre_rang'];
        $_SESSION['id'] = $data['membre_id'];
        $_SESSION['rang'] = $data['membre_rang'];
echo '<p>Bienvenu(e) '.$data['membre_pseudo'].', 
            vous &ecirc;tes maintenant connect&eacute;(e)&nbsp;!<br />
            Retour &agrave; l\'accueil dans un instant.';
$time=time();
$_SESSION['online_time']=$time;
$query=$db->prepare('UPDATE forum_whosonline 
SET online_id=:id, online_time=:time
WHERE online_index=:index');
$query->bindValue(':index',$_SESSION['online_index'],PDO::PARAM_INT);
$query->bindValue(':time',$time,PDO::PARAM_INT);
$query->bindValue(':id',$_SESSION['id'],PDO::PARAM_INT);
$query->execute();
if ($query->rowCount()<1) erreur('inactif');

?>
<script type="text/javascript">
alert('Vous êtes connecté(e)');
location.assign("./index.php");
</script>
<?php
    }
    else // Accès pas OK !
    {
        $message = '<p>Une erreur s\'est produite 
        pendant votre identification.<br /> Le mot de passe ou le pseudo 
            entr&eacute;s ne sont pas corrects.</p><p>Cliquez <a href="./connexion.php">ici</a> 
        pour revenir à la page pr&eacute;c&eacute;dente
        <br /><br />Cliquez <a href="./index.php">ici</a> 
        pour revenir &agrave; la page d\'accueil</p>';
/*>
<script type="text/javascript">document.getElementById("menu1").innerHTML="Revenir à la page d'accueil."</script>
<?php*/
    }
    $query->closeCursor();
    }
    echo $message;
}
//$page = htmlspecialchars($_POST['page']);
//echo 'Cliquez <a href="'.$page.'">ici</a> pour revenir à la page pr&eacute;c&eacute;dente.';
}//if ($id!=0)
echo '</div>';
include("../inclus/fin.php");
?>


