<?php
//Cette fonction doit être appelée avant tout code html
session_start();
//print_r($_SESSION);
//echo SID;



//On donne ensuite un titre à la page, puis on appelle notre fichier debut.php
$titre = "J.-C. Mitteau 2 - ligne";
include("inclus/debut_index.php");
echo '<body id="global" style="width:'.$largeur_e.'">';
?>
<?php include('corps.php');?>
</body>
</html>


