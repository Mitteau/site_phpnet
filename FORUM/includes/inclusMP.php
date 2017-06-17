<?php

 $ok1=false;
 $ok2=false;
if (isset($_POST['password'])) {$pass = $_POST['password'];$ok1=true;} else $pass="";
if (isset($_POST['confirm'])) {$confirm = $_POST['confirm'];$ok2=true;} else $confirm="";
if (!($ok1 || $ok2)){// si l'un d'eux n'est pas vide
 echo '
  <form method="post" action="changeMP.php">
  <label>Entrez votre nouveau mot de passe&nbsp;: </label>
  <input type="password" name="password" id="password" autofocus/><br />
  <label for="confirm">Confirmez&nbsp;: </label>
  <input type="password" name="confirm" id="confirm"  /><br>
  <input type="submit" value="Modifier son mot de passe." />
  <input type="reset" value="Refaire" />
  </form>';

} else {

//Vérification du mdp
$ok=false;
if (empty($confirm) || empty($pass) || $pass != $confirm) {
//echo '<script type="text/javascript">location.assign("changeMP.php");</script>';
echo '<div style=\"color:red\">Votre mot de passe et votre confirmation diff&egrave;rent ou sont vides. <a href="changeMP.php">Recommencez</a>.</div>';
}
else {
$pass=password_hash($pass,PASSWORD_BCRYPT); // bcrypt, coût 10, MAP
        $query=$db->prepare('UPDATE forum_membres
        SET  membre_mdp = :mdp
        WHERE membre_id=:id');
        $query->bindValue(':mdp',$pass,PDO::PARAM_STR);
        $query->bindValue(':id',$id,PDO::PARAM_STR);
        $ok=$query->execute();
        $query->closeCursor();

//Pour le site

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
		if ($user == substr($n,0,strpos($n,':')) || $n == "") break;
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
$str=$user.':'.$pass.PHP_EOL;
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
if ($ok) echo 'Votre mot de passe a &eacute;t&eacute; chang&eacute;.'; else 'Erreur d\'enregistrement';
}//acceptable
} // $ok1 || $ok2




?>
