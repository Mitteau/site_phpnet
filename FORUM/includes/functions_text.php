<?php
function code($texte)
{
//Smileys
$texte = str_replace(':D ', '<img src="./images/smileys/heureux.png" title="heureux" alt="heureux" />', $texte);
$texte = str_replace(':lol: ', '<img src="./images/smileys/lol.gif" title="lol" alt="lol" />', $texte);
$texte = str_replace(':triste:', '<img src="./images/smileys/triste.png" title="triste" alt="triste" />', $texte);
$texte = str_replace(':frime:', '<img src="./images/smileys/cool.gif" title="cool" alt="cool" />', $texte);
$texte = str_replace(':rire:', '<img src="./images/smileys/rire.gif" title="rire" alt="rire" />', $texte);
$texte = str_replace(':s', '<img src="./images/smileys/confus.gif" title="confus" alt="confus" />', $texte);
$texte = str_replace(':O', '<img src="./images/smileys/choc.gif" title="choc" alt="choc" />', $texte);
$texte = str_replace(':question:', '<img src="./images/smileys/question.gif" title="?" alt="?" />', $texte);
$texte = str_replace(':exclamation:', '<img src="./images/smileys/exclamation.gif" title="!" alt="!" />', $texte);

//Mise en forme du texte
//gras
$texte = preg_replace('`\[g\](.+)\[/g\]`isU', '<strong>$1</strong>', $texte); 
//italique
$texte = preg_replace('`\[i\](.+)\[/i\]`isU', '<em>$1</em>', $texte);
//souligné
$texte = preg_replace('`\[s\](.+)\[/s\]`isU', '<u>$1</u>', $texte);
//lien
{$texte = preg_replace('#\[url\]http://[a-z0-9._/-]+#i', '<a href="$0">$0</a>', $texte);
$texte=str_replace("[url]","",$texte);
$texte=str_replace("[/url]","",$texte);
}
//Images, photos
{$texte = preg_replace('#\[img\]http://[a-z0-9._/-]+#i', '<img src="$0" height=300px/>', $texte);
$texte=str_replace("[img]","",$texte);
$texte=str_replace("[/img]","",$texte);}
//etc., etc.

//On retourne la variable texte
return $texte;
}
?>

