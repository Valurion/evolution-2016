<?php
       // On inclut les fonctions communes à la gestion des citations
    include_once('citation-tools.php');
    
    // Initialisation
    $citation_label = "";
    $array_label    = array(
                        "numero"    => array("ouvrage" => "this issue", "encyclopedie" => "this issue"),
                        "article"   => array("ouvrage" => "this chapter", "encyclopedie" => "this chapter", "encyclopédie" => "this chapter", "revue" => "this article", "magazine" => "this article")
                      );

    // Définition des titres et des paramètres
    // CHAPITRES et ARTICLES
    if(isset($currentArticle)) {
        // Définition du label
        $citation_label = $array_label["article"][$typePub];
        // Définition de l'ID de l'article
        $export_id_article = $currentArticle["ARTICLE_ID_ARTICLE"];
    }
    // NUMERO
    else {
        // Définition du label
        $citation_label = $array_label["numero"][$typePub];
        // Définition de l'ID de l'article
        $export_id_article = $numero["NUMERO_ID_NUMPUBLIE"];
    }

?>

<div id="modal_citation" class="window_modal" style="display:none;">    
    <div class="info_modal"><a class="close_modal" href="javascript:void(0);" onclick="cairn.close_modal();"></a>
        <div class="citation">
            <h2>Citation export</h2>            
            <table class="format">
                <tr>
                    <th>ISO 690</th>
                    <td>
                        <span class="blue_milk" id="iso-690"><?php include_once('citation-format-iso-690.php'); ?></span>
                        <a href="javascript:void(0);" class="btnCopyClipboard" data-clipboard-action="copy" data-clipboard-target="#iso-690">
                            Copy
                        </a>
                    </td>                    
                </tr>
                <tr>
                    <th>MLA</th>
                    <td>
                        <span class="blue_milk" id="mla"><?php include_once('citation-format-mla.php'); ?></span>
                        <a href="javascript:void(0);" class="btnCopyClipboard" data-clipboard-action="copy" data-clipboard-target="#mla">
                            Copy
                        </a>
                    </td>                    
                </tr>
                <tr>
                    <th>APA</th>
                    <td>
                        <span class="blue_milk" id="apa"><?php include_once('citation-format-apa.php'); ?></span>
                        <a href="javascript:void(0);" class="btnCopyClipboard" data-clipboard-action="copy" data-clipboard-target="#apa">
                            Copy
                        </a>
                    </td>                    
                </tr>
            </table>

            <h2>Export</h2>

            <ul class="text-center">
                <li><a href="http://www.refworks.com/express/ExpressImport.asp?vendor=Cairn&amp;filter=Refworks%20Tagged%20Format&amp;encoding=65001&amp;url=<?= Configuration::get('refworks') ?>?t=uniq&cairnint&ID_ARTICLE=<?php echo $export_id_article; ?>">RefWorks</a></li>
                <li><a href="<?= Configuration::get('zotero') ?>?t=uniq&cairnint&ID_ARTICLE=<?php echo $export_id_article; ?>">Zotero <span>(.ris)</span></a></li>
                <li><a href="<?= Configuration::get('endnote') ?>?t=uniq&cairnint&ID_ARTICLE=<?php echo $export_id_article; ?>">EndNote <span>(.enw)</span></a></li>
            </ul>
        </div>
    </div>
</div>

<?php
    $this->javascripts[] = "new Clipboard('.btnCopyClipboard');";
?>