<?php
//Cette fonction doit être appelée avant tout code html
session_start();
//print_r($_SESSION);
//echo SID;



//On donne ensuite un titre à la page, puis on appelle notre fichier debut.php
$titre = "J.-C. Mitteau - ligne";
include("inclus/debut_index.php");
?>
<body id="global">
<?php include('corps.php');?>
</body>
</html>


