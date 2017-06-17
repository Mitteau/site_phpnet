<!--[if !IE]><!-->
<div>
<ul id="menu">
<li><span>Le site</span>
	<ul class="subMenu">
	 <li><a onclick="affichage_popup('/propos.php', 'f1')">&Agrave; propos</a></li>
	 <li><a href="http://www.jcmitteau.net">Le site</a></li>
	</ul>
</li>

<li><span>Navigation</span>
	<ul class="subMenu">
	 <li><a href="index.php">Accueil</a></li>
	 <li><a href="connexion.php">Se connecter</a></li>
<?php
if ($_SESSION['id']>0){
?>
	 <li><a href="deconnexion.php">Se d&eacute;connecter</a></li>
<?php
} else {
?>
	 <li><a href="register.php">S'enregistrer</a></li>
<?php
}
?>
	 
<?php
if ($_SESSION['level']>1){
?>
	 <li><a href="voirprofil.php?action=consulter">Voir son profil</a></li>
	 <li><a href="voirprofil.php?action=modifier">Modifier son profil</a></li>
	 <li><a href="messagesprives.php?action=consulter">Messages personnels</a></li>
	 <li><a href="memberlist.php?action=consulter">Liste des membres</a></li>
<?php
}
if ($_SESSION['level']>3){
?>
	 <li><a href="admin.php">Administrer</a></li>
<?php
}
?>
	</ul>
</li>
</ul>
</div>
<!--<![endif]-->
<!--[if IE]>

  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script LANGUAGE="JavaScript">

  $( function() {
    $( "#accordion" ).accordion();
  } );
</script>

<div id="accordion">
  <p style="background:black;color:white;margin:0">Le site</p>
  <div style="background:lightgrey;color:black;">
    <ul>
	 <li><a onclick="affichage_popup('/propos.php', 'f1')">&Agrave; propos</a></li>
	 <li><a href="/index.php">Le site</a></li>
    </ul>
  </div>
  <p style="background:black;color:white;margin:0">Navigation</p>
  <div style="background:lightgrey;color:black;">
	<ul>
	 <li><a href="index.php">Accueil</a></li>
	 <li><a href="connexion.php">Se connecter</a></li>
<?php
if ($_SESSION['id']>0){
?>
	 <li><a href="deconnexion.php">Se d&eacute;connecter</a></li>
<?php
} else {
?>
	 <li><a href="register.php">S'enregistrer</a></li>
<?php
}
?>
	 
<?php
if ($_SESSION['level']>1){
?>
	 <li><a href="voirprofil.php?action=consulter">Voir son profil</a></li>
	 <li><a href="voirprofil.php?action=modifier">Modifier son profil</a></li>
	 <li><a href="messagesprives.php?action=consulter">Messages personnels</a></li>
	 <li><a href="memberlist.php?action=consulter">Liste des membres</a></li>
<?php
}
if ($_SESSION['level']>3){
?>
	 <li><a href="admin.php">Administrer</a></li>
<?php
}
?>
	</ul>
  </div>
</div>

<![endif]-->
<div id="menu1" style="text-align:left;">
<?php if ($_SESSION['pseudo']!=""){ ?>
Vous &ecirc;tes&nbsp; 
<?php echo $_SESSION['pseudo']."&nbsp;".code(":D ");}
else echo "Vous n'&ecirc;tes pas connect&eacute;"."&nbsp;".code(":triste:").".";
//echo "<br />".$_SESSION['level'];
?>
<a href="mailto:site@jcmitteau.net?subject=Bogue sur site&bodu=Indiquez la bogue rencontré">Informez-moi d'un bogue, merci&nbsp;!</a>
</div>
<div id="mp_notification">&nbsp;
</div>
</div><!--sidebar-->
<div id="bugIE7"></div>

<div id="contenu">


