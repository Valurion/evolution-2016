<?php

/**
 * Converts all accent characters to ASCII characters.
 *
 * If there are no accent characters, then the string given is just returned.
 *
 * @param string $string Text that might have accent characters
 * @return string Filtered string with replaced "nice" characters.
 */
function formatrewriting($chaine) {
    //les accents
    //$chaine=  strtolower(trim($chaine));

    $chaine = remove_accents($chaine);
    $chaine = strtolower(trim($chaine));
    //les caracètres spéciaux (aures que lettres et chiffres en fait)
    $chaine = preg_replace('/([^.a-z0-9]+)/i', '-', $chaine);
    if (substr($chaine, 0, 6) == 'revue-')
        $chaine = substr($chaine, 6);
    return $chaine;
}

$this->javascripts[] = <<<'EOD'
    $(document).ready(function() {

    $(".checkme").change(function() {
    $("input:checkbox", $(this).parent().parent()).prop('checked', $(this).prop("checked"));
    });
            $('#allfacettes').bind('submit', function(e, data) {
    var formElement = document.getElementById("filter_type");
            formData = new FormData(formElement);
            console.log(formData);
            $('form', $('#filter_search')).each(function(index2) {
    $('input[type=hidden],input:checked', $(this)).each(function(index) {
    console.log(index + ": " + $(this).prop("tagName") + '::' + $(this).attr("name") + '::' + $(this).val());
            $('#allfacettes').append($('<input>', {
    type: 'hidden',
            name: $(this).attr("name"),
            value: $(this).val()
    }));
    });
    });
            return true;
    });
    });
EOD;


$this->javascripts[] = <<<'EOD'
    // A utiliser avec le script override_select.js.
    // Cette fonction ajoute dynamiquement une fonction à l'attribut onclick de chaque option.
    $(function() {
    $('#filter_online-since').children('option').attr('onclick',
            '$("#alter_filter_online-since .container-option").hide();'
            );
    });
EOD;

?>
<style>
    .pth_gray
    {
        background-color: graytext;
    }
    .contexte b {
        background-color:yellow;
    }
</style>
<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
//var_dump($results);
?>
<?php $this->titre = "Que sais-je ? / Repères"; ?>
<div id="body-content" class="searchResult">
    <div id="search_header">
        <form method="POST" name="mel2" id="mel2" class="left">
            <input name="OPERATE" value="T" type="hidden">
            <input type="hidden" name="TRI" value="DMEL">
            <input type="hidden" name="periode">
            <!-- Le select ci-dessous est "transformé" par le script override_select.js. Il récupère les ids et classe, et les préfixe par un alter_ -->
            <span id="label_filter_online-since">En ligne depuis</span>
            <select id="filter_online-since" onchange="alert('coming soon'); //$('#mel2').submit();" name="periode">
                <option value="ALL" selected="" onclick="$( & quot; #alter_filter_online - since.container - option & quot; ).hide();">toujours</option>
                <option value="SEM" onclick="$( & quot; #alter_filter_online - since.container - option & quot; ).hide();">Une semaine</option>
                <option value="MOIS" onclick="$( & quot; #alter_filter_online - since.container - option & quot; ).hide();">Un mois</option>
                <option value="DEUX" onclick="$( & quot; #alter_filter_online - since.container - option & quot; ).hide();">Deux mois</option>
                <option value="SIX" onclick="$( & quot; #alter_filter_online - since.container - option & quot; ).hide();">Six mois</option>
            </select>
        </form>
        <h1>Résultats de recherche</h1>
        <a href="#" onclick="rssrech();">
<!--                 <span class="icon-rss icon" style="margin-left:0.6em;"></span> -->
            <img src="./static/images/icon/rss-grey.png" alt="rss logo grey" style="margin-left:0.6em; vertical-align:top;">
        </a>
        <span id="trigger_filtering" class="search_button right" style="margin-left:1.5em">Préciser</span>

        <a  class="search_button right" style="margin-left:1.5em">Modifier</a>

    </div>
    <div id="search_navbar">
        <span>Votre recherche :</span>
        <span class="title_little_blue" style="margin-left:0.5em; font-size:17px;">
            <span id="librechav"><?= $searchTerm ?></span>
        </span>
        <span style="margin-left:1.5em" class="right"><?php
            echo $stats->TotalFiles . " résultat";
            if ($stats->TotalFiles > 1)
                echo "s ";
            ?></span>
    </div>

    <div id="wrapper_filter_search" style="display:none">
        <span class="close" onclick="$('#wrapper_filter_search').toggle();">
            <img src="./static/images/icon/lightbox-close.png">
        </span>
        <div id="filter_search" class="filter_search" >

            <div><h2>Termes associés</h2>
                <?php var_dump($concepts); ?></div>


            <?php
            foreach ($hiddenFacettes as $hiddenFacetteK => $hiddenFacetteV) {
                echo "<input type='hidden' name='$hiddenFacetteK" . "_hidden' value='$hiddenFacetteV'  >\n";
            }
            ?>



            <?php foreach ($facettes as $key => $value): ?>

                <form id="filter_<?php
                switch ($key) {
                    case 'Disciplines': $dsp = 'disc';
                        break;
                    case 'Types': $dsp = 'type';
                        break;
                    case 'Revues/collect.': $dsp = 'revue';
                        break;
                    case 'Dates de parution': $dsp = 'year';
                        break;
                } echo $dsp;
                ?>"  action='index.php?controleur=Recherche' method="post">
                          <?php
                          echo "<input type='hidden' name='searchTerm' value=\"" . htmlspecialchars($searchTerm) . "\" />\n";
                          echo "<input type='hidden' name='" . $label2facette[$key] . "_hidden' value='" . $hiddenFacettes[$label2facette[$key]] . "'  >\n";
                          ?>
                    <h2><?= $key ?></h2>
                    <ul>
                        <li><input type="checkbox"  name="p" class="checkme" id="bt_<?= $label2facette[$key] ?>" value="Tout cocher/décocher" />
                            <label for="bt_<?= $label2facette[$key] ?>">Tout cocher/décocher</label></li>


                        <?php foreach ($value as $key1 => $value1): ?>
                            <li>
                                <input type="checkbox" checked id="<?= $label2facette[$key] . '_' . $value1['mkey'] ?>" name='<?= $label2facette[$key] ?>[]' value="<?= $value1['mkey'] ?>" />
                                <label for="<?= $label2facette[$key] . '_' . $value1['mkey'] ?>"><?= $key1 . "(" . $value1['nb'] . ')' ?></label><br/>
                            </li>
                        <?php endforeach; ?>
                        <?php
                        $missing = false;
                        foreach (explode(',', $hiddenFacettes[$label2facette[$key]]) as $hiddenFacetteV) {
                            $namef = $label2facette[$key];
                            if ($key == 'Disciplines') {
                                if (!(isset($facettes[$key][$disciplines[$hiddenFacetteV]]))) {
                                    $missing = true;
                                    echo "<li><input type='checkbox' id='$namef" . '_' . "$hiddenFacetteV'  name='$namef" . "[]' value='$hiddenFacetteV'  /><label for='$namef" . '_' . "$hiddenFacetteV'>" . $disciplines[$hiddenFacetteV] . "</li></label>\n";
                                }
                            } elseif ($key == 'Types') {
                                if (!(isset($facettes[$key][$typepub[$hiddenFacetteV]]))) {
                                    echo "<li><input type='checkbox'  id='$namef" . '_' . "$hiddenFacetteV'  name='$namef" . "[]' value='$hiddenFacetteV'  /><label for='$namef" . '_' . "$hiddenFacetteV'>" . $typepub[$hiddenFacetteV] . "</li>\n";
                                    $missing = true;
                                }
                            } elseif ($key == 'Revues/collect.') {
                                if (!(isset($facettes[$key][$hiddenFacetteV]))) {
                                    echo "<li><input type='checkbox'  id='$namef" . '_' . "$hiddenFacetteV'  name='$namef" . "[]' value='$hiddenFacetteV'  /><label for='$namef" . '_' . "$hiddenFacetteV'>" . $hiddenFacetteV . "</li></label>\n";
                                    $missing = true;
                                }
                            } elseif ($key == 'Dates de parution')
                                if (!(isset($facettes[$key][$hiddenFacetteV]))) {
                                    echo "<li><input type='checkbox'  id='$namef" . '_' . "$hiddenFacetteV'  name='$namef" . "[]' value='" . str_replace('avant ', '~~', $hiddenFacetteV) . "'  /><label for='$namef" . '_' . "$hiddenFacetteV'>" . $hiddenFacetteV . "</label></li>\n";
                                    $missing = true;
                                }
                        }
                        ?>
                    </ul>
                    <br/>
                    <?php if (!$missing) : ?>
                        <?php
                            $this->javascripts[] = '$("#bt_' . $label2facette[$key] . '").attr("checked", "checked")';
                        ?>
                    <?php endif; ?>
                    <input type="submit" name="refine<?= $label2facette[$key] ?>" value="Rafraîchir"/>
                </form>


            <?php endforeach; ?>
            <input  type="button" name="refine" value="Tout rafraîchir"  onclick="$('#allfacettes').submit();"/>
        </div>
    </div>
    <div >
        <form style="display:none" id="allfacettes"  action="index.php?controleur=Recherche" method="post">

        </form>
    </div>


    <div class="results_list list_articles">
        <?php foreach ($results as $result) : ?>
            <div class="result article revue
            <?php
            echo $result->item->docId . "/" . count($accessibleArticles) . " ";
            if (!empty($accessibleArticles) && !in_array($result->item->docId, $accessibleArticles))
                echo "pth_gray"
                ?>"  id="">
                <h2><?php
                    if ((int) $result->item->packed == '1') {
                        $pack = 1;
                    } else {
                        $pack = 0;
                    } if ((int) $result->userFields->tp == 3) {
                        $offset = (int) $result->userFields->tp + 2 * (int) $result->userFields->tnp;
                    } else {
                        $offset = (int) $result->userFields->tp;
                    } echo $typeDocument[$pack][$offset];
                    ?></h2>
                <?php
                if ($result->item->packed)
                    echo "<h2>PACKED</h2>";
                else
                    echo "<h2>NO PACKED</h2>"
                    ?></h2>

                <div class="wrapper_meta">
                    <a href="revue-<?= formatrewriting($result->userFields->rev0) . '-' . $result->userFields->an . '-' . $result->userFields->NUM0 . '-page-' . $result->userFields->pgd ?>.htm">
                        <img src="/<?= $vign_path ?>/<?= $result->userFields->id_r . '/' . $result->userFields->np ?>_L61.jpg" class="small_cover" />
                    </a>
                    <div class="meta">
                        <div class="title0"><a href="revue-<?= formatrewriting($result->userFields->rev0) . '-' . $result->userFields->an . '-' . $result->userFields->NUM0 . '-page-' . $result->userFields->pgd ?>.htm"><strong><?php if ($pack > 0) echo $result->userFields->titnum; ?></strong></a></div>
                        <div class="title"><a href="revue-<?= formatrewriting($result->userFields->rev0) . '-' . $result->userFields->an . '-' . $result->userFields->NUM0 . '-page-' . $result->userFields->pgd ?>.htm"><strong><?= $result->userFields->tr ?></strong></a></div>
                        <div class="authors">
                            <span class="author"><?php
                                $authors = explode('|', $result->userFields->auth0);
                                if (sizeof($authors) > 2) {
                                    $authors2 = explode('#', $authors[0]);
                                    echo "<a class=\"yellow\" href=\"publications-de-" . trim($authors2[1]) . '-' . $authors2[2] . '--' . $authors2[3] . '.htm' . '">';
                                    echo $authors2[1] . ' ' . $authors2[2] . "</a> et al.";
                                } else if (sizeof($authors) == 2) {
                                    $authors2 = explode('#', $authors[0]);
                                    echo "<a class=\"yellow\" href=\"publications-de-" . $authors2[1] . '-' . $authors2[2] . '--' . $authors2[3] . '.htm' . '">' . $authors2[2] . ' ' . $authors2[1] . "</a> et ";
                                    $authors2 = explode('#', $authors[1]);
                                    echo "<a class=\"yellow\" href=\"publications-de-" . trim($authors2[1]) . '-' . $authors2[2] . '--' . $authors2[3] . '.htm' . '">' . $authors2[2] . ' ' . $authors2[1] . "</a> ";
                                } else if (sizeof($authors) == 1) {
                                    $authors2 = explode('#', $authors[0]);
                                    echo "<a class=\"yellow\" href=\"publications-de-" . $authors2[1] . '-' . $authors2[2] . '--' . $authors2[3] . '.htm' . '">' . $authors2[2] . ' ' . $authors2[1] . "</a>";
                                }
                                ?></span>
                        </div>
                        <div class="revue_title">Dans <a href="revue-<?= formatrewriting($result->userFields->rev0) ?>" class="title_little_blue"><span class="title_little_blue"><?= $result->userFields->rev0 ?></span></a> <strong><?= $result->userFields->an . '/' . $result->userFields->NUM0 . ' (' . $result->userFields->vol . ')' ?></strong></div>
                    </div>
                </div>
                <div class="contexte"><?= strip_tags($result->item->Synopsis, '<b>') ?></div>
                <div class="state">

                    <a href="resume.php?ID_ARTICLE=<?= $result->userFields->id ?>" class="button">
                        Résumé
                    </a>

                    <a href="article.php?ID_ARTICLE=<?= $result->userFields->id ?>&amp;DocId=182067&amp;Index=%2Fcairn2Idx%2Fcairn&amp;TypeID=226&amp;BAL=anMl6Ia9VcGtI&amp;HitCount=146&amp;hits=2ef1+2dc7+2d5e+2d4a+2d2d+2a90+29fd+298d+295a+2928+28cb+28a4+2888+285f+284c+2829+280c+27ee+27de+27b5+2770+2752+26a7+2672+2655+2590+2526+221a+21d7+21be+21ab+218e+2147+20bf+2066+2004+1eb1+1e42+1daf+1d9b+1d78+1d6c+1d61+1d49+1d3f+1d32+1d0c+1cf1+1cda+1c9f+1c58+1c39+1bbf+1b69+1b5d+1b52+1b3f+1b35+1b28+1b0d+1a84+1a5b+19b2+17b3+17a9+179a+1719+16fe+16b1+169c+1659+161a+15e3+1525+14db+14a8+148a+1474+1463+1450+13c3+13b7+13b2+135b+1359+1358+1356+131d+12c3+12a8+12a3+1271+1240+1223+1204+11f1+11bf+116f+114a+112e+10a9+103c+100a+fde+f9d+f7a+f62+f53+f32+f15+ee9+ed8+ec5+e89+e7b+e67+e12+df4+ddc+d95+d8b+d2c+cd3+a25+97a+92d+853+657+620+575+509+496+43d+408+3f3+383+32d+31b+307+2b3+1f8+1ac+1a0+158+13b+4+0&amp;fileext=html#hit1" class="button">
                        Version HTML
                    </a>

                    <a href="load_pdf.php?ID_ARTICLE=<?= $result->userFields->id ?>" class="button">
                        Version PDF
                    </a>

                    <a href="resultats_recherche.php<?= str_replace('controleur=Recherche&', '?', $_SERVER['QUERY_STRING']) ?>&amp;AJOUTBIBLIO=<?= $result->userFields->id . '#' . $result->userFields->id ?>" class="icon icon-add-biblio">&nbsp;</a>

                </div>
                <!--<span class='Z3988' title="ctx_ver=Z39.88-2004&amp;rft_val_fmt=info%3Aofi%2Ffmt%3Akev%3Amtx%3Ajournal&amp;rfr_id=info%3Asid%2Focoins.info%3Agenerator&amp;rft.genre=article&amp;rft.atitle=Une ville sans voiture : utopie ?&amp;rft.title=Revue d’Économie Régionale & Urbaine&amp;rft.issn=0180-7307&amp;rft.date=2004&amp;rft.volume=décembre&amp;rft.issue=5&amp;rft.spage=753&amp;rft.epage=778&amp;rft.au=<span class="author">
                                    <a href="publications-de-Massot-Marie-Hélène--20109.htm" class="yellow">Marie-Hélène Massot</a>
                                     <em>et al.</em>

                                </span>
                            &amp;rft_id=info:doi/10.3917/reru.045.0753&amp;rft_id=revue-d-economie-regionale-et-urbaine-2004-5-page-753.htm"></span>-->
            </div>
            <p> <?php //var_dump($result->userFields)                 ?></p>
            <hr/>

<?php endforeach; ?>
    </div>
</div>
<script>
            function move(LIMIT){
            alert(LIMIT);
            }
</script>
<?php
$nbPerPage = 20;
$nbAround = 2;
$jsPager = "move";
$limit = 0;
$countNum = $stats->TotalFiles;
?>
<?php require_once __DIR__ . '/../CommonBlocs/pager.php'; ?>
