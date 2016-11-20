<?php 
 	// Yo tout le monde, un exelent moyen de reduire ses codes pour une compilation plus rapide
	//pas tres compliker a mettre en oeuvre mais ouf
	function r($a,$b,$char){return str_replace($a,$b,$char);}
	function s($tr){return r("\'","'",htmlspecialchars($tr));}
	//Et ouaip, j'ai take xa dans darkDB
	define('X', "\x1A"); // un joli placeholder
	$SS = '"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'';
	$CC = '\/\*[\s\S]*?\*\/';
	$CH = '<\!--[\s\S]*?-->';
	function __compressor_x($input) {
	    return str_replace(array("\n", "\t", ' '), array(X . '\n', X . '\t', X . '\s'), $input);
	}
	function __compressor_v($input) {
	    return str_replace(array(X . '\n', X . '\t', X . '\s'), array("\n", "\t", ' '), $input);
	}
	/**
	 * =======================================================
	 *  COUPEUR de HTML 
	 * =======================================================
	 * -- CODE: ----------------------------------------------
	 *
	 *    echo compressor_html(file_get_contents('test.html'));
	 *
	 * -------------------------------------------------------
	 */
	function _compressor_html($input) {
	    return preg_replace_callback('#<\s*([^\/\s]+)\s*(?:>|(\s[^<>]+?)\s*>)#', function($m) {
		if(isset($m[2])) {
		    // Declaration CSS 
		    if(stripos($m[2], ' style=') !== false) {
			$m[2] = preg_replace_callback('#( style=)([\'"]?)(.*?)\2#i', function($m) {
			    return $m[1] . $m[2] . compressor_css($m[3]) . $m[2];
			}, $m[2]);
		    }
		    return '<' . $m[1] . preg_replace(
			array(
			    // De `defer="defer"`, `defer='defer'`, `defer="true"`, `defer='true'`, `defer=""` and `defer=''` to `defer` [^1]
			    '#\s(checked|selected|async|autofocus|autoplay|controls|defer|disabled|hidden|ismap|loop|multiple|open|readonly|required|scoped)(?:=([\'"]?)(?:true|\1)?\2)#i',
			    // Enlever extra white-space(s) entre les attributs HTML [^2]
			    '#\s*([^\s=]+?)(=(?:\S+|([\'"]?).*?\3)|$)#',
			    // De `<img />` a `<img/>` [^3]
			    '#\s+\/$#'
			),
			array(
			    // [^1]
			    ' $1',
			    // [^2]
			    ' $1$2',
			    // [^3]
			    '/'
			),
		    str_replace("\n", ' ', $m[2])) . '>';
		}
		return '<' . $m[1] . '>';
	    }, $input);
	}
	function compressor_html($input) {
	    if( ! $input = trim($input)) return $input;
	    global $CH;
	    // bn on garde les espace pour les tags HTML
	    $input = preg_replace('#(<(?:img|input)(?:\s[^<>]*?)?\s*\/?>)\s+#i', '$1' . X . '\s', $input);
	    // Créer des blocs de balises HTML, groupe (s) HTML ignoré (s), commentaire (s) HTML et texte
	    $input = preg_split('#(' . $CH . '|<pre(?:>|\s[^<>]*?>)[\s\S]*?<\/pre>|<code(?:>|\s[^<>]*?>)[\s\S]*?<\/code>|<script(?:>|\s[^<>]*?>)[\s\S]*?<\/script>|<style(?:>|\s[^<>]*?>)[\s\S]*?<\/style>|<textarea(?:>|\s[^<>]*?>)[\s\S]*?<\/textarea>|<[^<>]+?>)#i', $input, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
	    $output = "";
	    foreach($input as $v) {
		if($v !== ' ' && trim($v) === "") continue;
		if($v[0] === '<' && substr($v, -1) === '>') {
		    if($v[1] === '!' && strpos($v, '<!--') === 0) { // HTML commentaires ...
			// Supprimer si elle n'est pas détectée comme commentaire (s) IE ...
			if(substr($v, -12) !== '<![endif]-->') continue;
			$output .= $v;
		    } else {
			$output .= __compressor_x(_compressor_html($v));
		    }
		} else {

		// Forcer la coupure de ligne avec `& # 10;` ou `& # xa;`
		    $v = str_replace(array('&#10;', '&#xA;', '&#xa;'), X . '\n', $v);
		// Forcer l'espace blanc avec `& # 32;` ou `& # x20;`
		    $v = str_replace(array('&#32;', '&#x20;'), X . '\s', $v);
		// Remplacer plusieurs espace (s) en blanc par un espace
		    $output .= preg_replace('#\s+#', ' ', $v);
		}
	    }
	    // on lave tout ...
	    $output = preg_replace(
		array(
			// Supprime deux espaces blancs ou plus entre la balise [^ 1]
		    '#>([\n\r\t]\s*|\s{2,})<#',
			// Supprime le (s) espace (s) blanc avant tag-close [^ 2]
		    '#\s+(<\/[^\s]+?>)#'
		),
		array(
		    // [^1]
		    '><',
		    // [^2]
		    '$1'
		),
	    $output);
	    $output = __compressor_v($output);

	// Supprime le (s) espace (s) blanc (s) après avoir ignoré tag-open et avant d'ignorer tag-close (sauf `<textarea>`)
	    return preg_replace('#<(code|pre|script|style)(>|\s[^<>]*?>)\s*([\s\S]*?)\s*<\/\1>#i', '<$1$2$3</$1>', $output);
	}
	/**
	 * =======================================================
	 *  Diminueur de CSS
	 * =======================================================
	 * -- CODE: ----------------------------------------------
	 *
	 *    echo compressor_css(file_get_contents('test.css'));
	 *
	 * -------------------------------------------------------
	 */
	function _compressor_css($input) {
		// Conserve les espaces blancs importants dans `calc ()`
	    if(stripos($input, 'calc(') !== false) {
		$input = preg_replace_callback('#\b(calc\()\s*(.*?)\s*\)#i', function($m) {
		    return $m[1] . preg_replace('#\s+#', X . '\s', $m[2]) . ')';
		}, $input);
	    }
	    // dininueur de compressor en action ...
	    return preg_replace(
		array(

		// Correction du cas `#foo [bar =" baz "]` et `#foo: first-child` [^ 1]
		    '#(?<![,\{\}])\s+(\[|:\w)#',
		// Correction de cas pour `[bar =" baz "] .foo` et` @media (foo: bar) et (baz: qux) `[^ 2]
		    '#\]\s+#', '#\b\s+\(#', '#\)\s+\b#',
		// Comprsse le code couleur HEX ... [^ 3]
		    '#\#([\da-f])\1([\da-f])\2([\da-f])\3\b#i',
		    '#\s*([~!@*\(\)+=\{\}\[\]:;,>\/])\s*#',
		    '#\b(?:0\.)?0([a-z]+\b|%)#i',
		    '#\b0+\.(\d+)#',
		    '#:(0\s+){0,3}0(?=[!,;\)\}]|$)#',
		    '#\b(background(?:-position)?):(0|none)\b#i',
		    '#\b(border(?:-radius)?|outline):none\b#i',
		    '#(^|[\{\}])(?:[^\{\}]+)\{\}#',
		    '#;+([;\}])#',
		    '#\s+#'
		),
		array(
		    // [^1]
		    X . '\s$1',
		    // [^2]
		    ']' . X . '\s', X . '\s(', ')' . X . '\s',
		    // [^3]
		    '#$1$2$3',
		    // [^4]
		    '$1',
		    // [^5]
		    '0',
		    // [^6]
		    '.$1',
		    // [^7]
		    ':0',
		    // [^8]
		    '$1:0 0',
		    // [^9]
		    '$1:0',
		    // [^10]
		    '$1',
		    // [^11]
		    '$1',
		    // [^12]
		    ' '
		),
	    $input);
	}
	function compressor_css($input) {
	    if( ! $input = trim($input)) return $input;
	    global $SS, $CC;

	    $input = preg_replace('#(' . $CC . ')\s+(' . $CC . ')#', '$1' . X . '\s$2', $input);

	    $input = preg_split('#(' . $SS . '|' . $CC . ')#', $input, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
	    $output = "";
	    foreach($input as $v) {
		if(trim($v) === "") continue;
		if(
		    ($v[0] === '"' && substr($v, -1) === '"') ||
		    ($v[0] === "'" && substr($v, -1) === "'") ||
		    (strpos($v, '/*') === 0 && substr($v, -2) === '*/')
		) {

		    if($v[0] === '/' && strpos($v, '/*!') !== 0) continue;
		    $output .= $v; 
		} else {
		    $output .= _compressor_css($v);
		}
	    }
	    // Remove quote(s) where possible ...
	    $output = preg_replace(
		array(
		    '#(' . $CC . ')|\b(url\()([\'"])([^\s]+?)\3(\))#i'
		),
		array(
		    // '$1$3',
		    '$1$2$4$5'
		),
	    $output);
	    return __compressor_v($output);
	}
	/**
	 * =======================================================
	 *  compressor JAVASCRIPT
	 * =======================================================
	 * -- CODE: ----------------------------------------------
	 *
	 *    echo compressor_js(file_get_contents('test.js'));
	 *
	 * -------------------------------------------------------
	 */
	function _compressor_js($input) {
	    return preg_replace(
		array(
		    '#\s*\/\/.*$#m',
		    '#\s*([!%&*\(\)\-=+\[\]\{\}|;:,.<>?\/])\s*#',
		    '#[;,]([\]\}])#',
		    '#\btrue\b#', '#\bfalse\b#', '#\breturn\s+#'
		),
		array(
		    // [^1]
		    "",
		    // [^2]
		    '$1',
		    // [^3]
		    '$1',
		    // [^4]
		    '!0', '!1', 'return '
		),
	    $input);
	}
	function compressor_js($input) {
	    if( ! $input = trim($input)) return $input;
	    global $SS, $CC;
	    $input = preg_split('#(' . $SS . '|' . $CC . '|\/[^\n]+?\/(?=[.,;]|[gimuy]|$))#', $input, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
	    $output = "";
	    foreach($input as $v) {
		if(trim($v) === "") continue;
		if(
		    ($v[0] === '"' && substr($v, -1) === '"') ||
		    ($v[0] === "'" && substr($v, -1) === "'") ||
		    ($v[0] === '/' && substr($v, -1) === '/')
		) {
		    if(strpos($v, '//') === 0 || (strpos($v, '/*') === 0 && strpos($v, '/*!') !== 0 && strpos($v, '/*@cc_on') !== 0)) continue;
		    $output .= $v;
		} else {
		    $output .= _compressor_js($v);
		}
	    }
	    return preg_replace(
		array(
		    '#(' . $CC . ')|([\{,])([\'])(\d+|[a-z_]\w*)\3(?=:)#i',
		    '#([\w\)\]])\[([\'"])([a-z_]\w*)\2\]#i'
		),
		array(
		    // [^1]
		    '$1$2$4',
		    // [^2]
		    '$1.$3'
		),
	    $output);
	}
// Ok coul, ttes ce fonctions wakka mo mais le html juste en bas un peu ancien, dc tenez pas compte

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
