<?php 
function r($a,$b,$char){return str_replace($a,$b,$char);}
function s($tr){return r("\'","'",htmlspecialchars($tr));}
if(isset($_POST['text'])){
	$f_1=$_POST['text'];

	$fichier1='SC_Compress';
	$f1=fopen($fichier1,'w');
	
	if(isset($_GET['css']))
		$f_1=r('	','',r(' :', ':',r(' :',':',r(', ',',',r(' ,',',',r(': ',':',r(' (','(',r('( ', '(',r(' ;',';',r('; ',';', r('{ ','{', r('} ','}', r(' }','}', r(' {','{', $f_1))))))))))))));
	
	else if(isset($_GET['html']))
		$f_1=r('?> ','?>',r(' ?>', ' ?>',r('<?php ', '<?php ',r('> ','>',r(' >','>',r('< ','<',r(' <','<',r('"> ','">',r(' ">','">', $f_1)))))))));

	fwrite($f1,'S@n1x-Compressor
	'.$f_1);
	fclose($f1);
	$go1=1;
}
?>
<html>
<head><title>#Compressor#</title></head>
	<body style="background:grey;">
		<center/>
		<br><p>
		<div style="width:40%;padding:10px;background:white;border:1px solid grey;">
			<h4>S@n1x-Compressor</h4>
			<a href="?css=45">CSS</a> | | <a href="?html=45">HTML/PHP</a> | | <a href="?js">JAVA S</a>
			<hr>
			<?php if(isset($_GET['css'])){?>
			Compresser Votre CSS
			<form action="?css=34" method="post">
			<textarea style="width:90%;height:100px;" name="text" placeholder="Entrez le Texte a compresser."></textarea>
			<input type="submit" value="Compresser le texte" style="width:90%;padding:5px;color:white;background:black;border:0;">
			</form>
			<?php if(isset($go1)){?>
				<div style="max-width:500px;min-width:500px;max-height:350px;min-height:100px;overflow:auto;border:1px dashed grey;">
				<?php echo $f_1;?>
				</div>
				Votre Fichier a ete enregistrer;
				<a href="<?php echo 'SC_Compress';?>" target="_blank">Voir le Fichier compresser</a>
			<?php }
			}
			else if(isset($_GET['html'])){?>
			Compresser Votre HTML
			<form action="?html=34" method="post">
			<textarea style="width:90%;height:100px;" name="text" placeholder="Entrez le Texte a compresser."></textarea>
			<input type="submit" value="Compresser le texte" style="width:90%;padding:5px;color:white;background:black;border:0;">
			</form>
			<?php if(isset($go1)){?>
				<div style="max-width:500px;min-width:500px;max-height:350px;min-height:100px;overflow:auto;border:1px dashed grey;">
				<?php echo s($f_1);?>
				</div>
				Votre Fichier a ete enregistrer;
				<a href="<?php echo 'SC_Compress';?>" target="_blank">Voir le Fichier compresser</a>
			<?php }
			}
			else{
				echo '<h1>Option pas encore disponible</h1><br><p>';
			}
			?>
			<div style="font-size:13px;color:white;background:#1abc9c;">S@n1x-Compressor est un Compresseur de fichier css et html pour un rendu plus rapide pour le Navigateur du client, il supprime tous les Espaces, et autres elements Superflus de l'identation.</div>
		</div>
	</body>
</html>