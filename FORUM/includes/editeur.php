<fieldset><legend>Mise en forme</legend>
<input type="button" id="gras" name="gras" value="gras" onClick="javascript:bbcode('[g]', '[/g]');return(false)" />
<input type="button" id="italic" name="italic" value="italic" onClick="javascript:bbcode('[i]', '[/i]');return(false)" />
<input type="button" id="souligne" name="souligné" value="souligné" onClick="javascript:bbcode('[s]', '[/s]');return(false)" />
<input type="button" id="lien" name="lien" value="lien" onClick="javascript:bbcode('[url]', '[/url]');return(false)" />
<input type="button" id="image" name="image" value="image, photo" onClick="javascript:bbcode('[img]', '[/img]');return(false)" />
<br /><br />
<img src="./images/smileys/heureux.png" title="heureux" alt="heureux" onClick="javascript:smilies(':D ');return(false)" />
<img src="./images/smileys/lol.png" title="lol" alt="lol" onClick="javascript:smilies(':lol:');return(false)" />
<img src="./images/smileys/triste.png" title="triste" alt="triste" onClick="javascript:smilies(':triste:');return(false)" />
<img src="./images/smileys/cool.gif" title="cool" alt="cool" onClick="javascript:smilies(':frime:');return(false)" />
<img src="./images/smileys/rire.gif" title="rire" alt="rire" onClick="javascript:smilies('XD');return(false)" />
<img src="./images/smileys/confus.png" title="confus" alt="confus" onClick="javascript:smilies(':s');return(false)" />
<img src="./images/smileys/choc.gif" title="choc" alt="choc" onClick="javascript:smilies(':O');return(false)" />
<img src="./images/smileys/question.gif" title="?" alt="?" onClick="javascript:smilies(':interrogation:');return(false)" />
<img src="./images/smileys/exclamation.png" title="!" alt="!" onClick="javascript:smilies(':exclamation:');return(false)" /></fieldset>
<br />
<fieldset><legend>Message</legend>
<textarea cols="80" rows="8" id="message" name="message"><?php echo $_SESSION['message'];?></textarea></fieldset><br />
