<?php
header('Content-Type: text/html; charset=utf-8');

$this->titre = "List of articles - Print";

$hours = date("H\hi");
$dates = date("d/m/Y");
?>
<div id="copy">
    <table width="70%" cellspacing="0" cellpading="0" >
        <tr>
            <td width="200px"><img width="180px" height="auto" src="./static/images/logo-cairn-int.png"></td><td>
                <p class="titresel">Selected Bibliography<br /><span class="refsel"><?php echo $dates .' , ' . $hours ?></span></p>
            </td>
        </tr>
    </table>
</div>

<p class="biblio">
    <ul>
        <?php 
                
        foreach($articles as $article)
        {
            $articleString = '';
            
            if(!empty($article['BIBLIO_AUTEURS']))
            {
                $authorNames = explode(':',$article['BIBLIO_AUTEURS']);
                $articleString .= $authorNames[1] . ' ' . $authorNames[0][0] . '.</span>, ';
            }
            if(!empty($article['ARTICLE_TITRE']))
            {
                $articleString.= '«' . $article['ARTICLE_TITRE'] . '», ';
            }
            if(!empty($article['REVUE_TITRE']))
            {
                $articleString.= '<em>' . $article['REVUE_TITRE'] . '</em> ';
            }
            if(!empty($article['NUMERO_ANNEE']))
            {
                $articleString.= $article['NUMERO_ANNEE'];
            }            
            if(!empty($article['NUMERO_NUMERO']))
            {
                $articleString.= '/' . $article['NUMERO_NUMERO'];
            }
            if(!empty($article['NUMERO_VOLUME']))
            {
                $articleString.= ' (' . $article['NUMERO_VOLUME'] . ')';
            }
            if(!empty($article['ARTICLE_PAGE_DEBUT']))
            {
                $articleString.= ', p.&#160;' . $article['ARTICLE_PAGE_DEBUT'];
            }
            if(!empty($article['ARTICLE_PAGE_FIN']))
            {
                $articleString.= ' to ' . $article['ARTICLE_PAGE_FIN'] . '.';
            }            
            
            echo '<li>' . $articleString . '</li>';
        }
        
        foreach($numeros as $numero)
        {
            $numeroString = '';
            
            if(!empty($numero['BIBLIO_AUTEURS']))
            {
                $authorNames = explode(':',$numero['BIBLIO_AUTEURS']);
                $numeroString .= $authorNames[1] . ' ' . $authorNames[0][0] . '.</span>, ';
            }
            if(!empty($numero['NUMERO_TITRE']))
            {
                $numeroString.= '<em>'.$numero['NUMERO_TITRE'] . '</em>,';
            }
            if(!empty($numero['EDITEUR_VILLE_EDITEUR']))
            {
                $numeroString.= ' '.$numero['EDITEUR_VILLE_EDITEUR'];
            }
            if(!empty($numero['EDITEUR_NOM_EDITEUR']))
            {
                $numeroString.= ', '.$numero['EDITEUR_NOM_EDITEUR'];
            }            
            if(!empty($numero['REVUE_TITRE']))
            {
                $numeroString.= ' «' . $numero['REVUE_TITRE'] . '»';
            }
            if(!empty($numero['NUMERO_ANNEE']))
            {
                $numeroString.= ', ' . $numero['NUMERO_ANNEE'];
            }
            if(!empty($numero['NUMERO_PAGES']))
            {
                $numeroString.= ', ' . $numero['NUMERO_PAGES'] . ' p.';
            }          
            
            echo '<li>' . $numeroString . '</li>';
        }
        ?>
    </ul>
</p>