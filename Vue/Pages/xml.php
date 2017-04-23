<?php
    $xmlPath = $fileFullPath.'/'.$currentArticle['ARTICLE_ID_ARTICLE'].'.xml';
    if (file_exists($xmlPath)) {
        if ($onlyFirstPage != 1) {
            readfile($xmlPath);
        } else {
            // Seul la première page du feuilleteur doit être lu. Pour cela, on supprime les balises <page> dont l'index est supérieur ou égal à 1
            $dom = new DOMDocument();
            $dom->load($xmlPath);
            $xpath = new DOMXPath($dom);
            foreach ($xpath->query('/data/pages/page') as $index => $elem) {
                if ($index < 1) { continue; }
                $elem->parentNode->removeChild($elem);
            }
            echo $dom->saveXML();
        }
    } else {
?>

<?php
echo '<?xml version="1.0" encoding="utf-8" ?>';
?>
<data>
    <pages>
        <?php
        $stop = 0;
        //var_dump($currentArticle);
        for ($indPage = $currentArticle["ARTICLE_PAGE_DEBUT"]; $indPage <= $currentArticle["ARTICLE_PAGE_FIN"] && $stop == 0; $indPage++) {
            if(file_exists($fileFullPath.'/page-' . $indPage . '.swf')){
                echo '<page><![CDATA[load_swf.php?ID_ARTICLE=' . $currentArticle["ARTICLE_ID_ARTICLE"] . '&PAGE=page-' . $indPage . '.swf]]></page>';
            }else{
                $page = substr(('0000'.$indPage),strlen('0000'.$indPage)-4);
                if(file_exists($fileFullPath.'/'.$currentArticle["ARTICLE_ID_ARTICLE"].'_'.$page.'.swf')){
                    echo '<page><![CDATA[load_swf.php?ID_ARTICLE=' . $currentArticle["ARTICLE_ID_ARTICLE"] . '&PAGE='.$currentArticle["ARTICLE_ID_ARTICLE"].'_'.$page.'.swf]]></page>';
                }/*else{
                    echo 'NO FILE FOUND:'.$fileFullPath.'/'.$currentArticle["ARTICLE_ID_ARTICLE"].'_'.$page.'.swf';
                }*/
            }
            if ($onlyFirstPage == 1) {
                $stop = 1;
            }
        }
        ?>
    </pages>
</data>
<?php } ?>
