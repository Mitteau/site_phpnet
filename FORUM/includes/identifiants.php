<?php
//Dépendant de l'hébergement; ici local host
//$dsn='mysql:host:localhost;dbname=forum';
//$dsn='mysql:127.0.0.1;dbname=forum;unix_socket=/var/run/mysqld/mysqld.sock;';
//$dsn='mysql:localhost;dbname=forum;unix_socket=/var/run/mysqld/mysqld.sock;';
/*$dsn='mysql:dbname=forum;unix_socket=/var/run/mysqld/mysqld.sock;';
try{
$db = new PDO($dsn,'root', 'root',array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));}
catch (Exception $e){die ('Erreur : ' . $e->getMessage());}
*/
//CAS PHPNET
//$dsn='mysql:host=46.255.163.252;dbname=lud97321';
$dsn='mysql:host=46.255.164.248;dbname=lud97321;port=3306';
try{
//$db = new PDO($dsn,'lud97321', 'iJ4XVSXKpcdX',array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));}
$db = new PDO($dsn,'lud97321', 'iJ4XVSXKpcdX',array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));}
catch (Exception $e){ die ('Erreur : ' . $e->getMessage());}

?>
