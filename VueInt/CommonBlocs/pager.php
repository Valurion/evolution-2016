<!--
    Il faut fournir à cette vue:
        - $nbPerPage = nombre de résultats affichés
        - $nbAround = nombre de page que l'on affiche autour de la page courante
        - $limit = valeur du LIMIT dans la requete
        - $countNum = nombre total d'items à présenter

        - $urlPager = l'url derrière les boutons, à laquelle sera ajouté le paramètre &LIMIT
        OU
        - $jsPager = la méthode JS à appeler derrière les boutons, qui recevra en LIMIT en paramètre
        
    Pour la pagination:
        - on affiche toujours la première et la dernière page
        - on affiche "précédent" et "suivant" sauf si il n'y a pas
        - on affiche la page courante
        - si possible, $nbAround avant et $nbAround après. Si pas possible dans un sens, on reporte sur l'autre sens
-->
<?php
$currentPage = ($limit / $nbPerPage) + 1;
$lastPage = ((int) ($countNum / $nbPerPage)) + ($countNum % $nbPerPage == 0 ? 0 : 1);
if ($lastPage != 1) {
    //On calcule les possibilités initiales pour les pages avant/après
    $nbBefore = ($currentPage > 4 ? $nbAround : ($currentPage - 2));
    if ($nbBefore < 0)
        $nbBefore = 0;
    $nbAfter = ($currentPage < ($lastPage - 4) ? $nbAround : ($lastPage - 1 - $currentPage));
    if ($nbAfter < 0)
        $nbAfter = 0;
    //On essaie si possible de reporter vers l'avant/après pour conserver le même nombre de boutons
    if ($nbAfter < $nbAround)
        $nbBefore = $currentPage > ($nbBefore + ($nbAround - $nbAfter)) ? $nbBefore + ($nbAround - $nbAfter) : ($currentPage - 2);
    if ($nbBefore < $nbAround)
        $nbAfter = $currentPage < $lastPage - ($nbAfter + ($nbAround - $nbBefore)) ? $nbAfter + ($nbAround - $nbBefore) : ($lastPage - $currentPage - 1);
    ?>
    <div class="pager">
        <ul>
            <?php
            if ($currentPage != 1) {
                if (!isset($urlPager) || $urlPager == '') {
                    ?>
                    <li class="prev"><a href="javascript:void(0)" onclick="<?php echo $jsPager; ?>(<?php echo $limit - $nbPerPage; ?>)">Previous</a></li>
                    <li class="first"><a href="javascript:void(0)" onclick="<?php echo $jsPager; ?>(0)">1</a></li>    
                    <?php
                } else {
                    ?>
                    <li class="prev"><a href="<?php echo $urlPager; ?>&amp;LIMIT=<?php echo $limit - $nbPerPage; ?>">Previous</a></li>
                    <li class="first"><a href="<?php echo $urlPager; ?>&amp;LIMIT=0">1</a></li>
                    <?php
                }
            }
            if ($currentPage - $nbBefore > 2) {
                ?>
                <li class="last"><a href="#">...</a></li>
                <?php
            }
            for ($i = 0; $i < $nbBefore; $i++) {
                if (!isset($urlPager) || $urlPager == '') {
                    ?>
                    <li class="nb"><a href="javascript:void(0)" onclick="<?php echo $jsPager; ?>(<?php echo $limit - (($nbBefore - $i) * $nbPerPage) ?>)"><?php echo $currentPage - ($nbBefore - $i); ?></a></li>                
                    <?php
                } else {
                    ?> 
                    <li class="nb"><a href="<?php echo $urlPager; ?>&amp;LIMIT=<?php echo $limit - (($nbBefore - $i) * $nbPerPage) ?>"><?php echo $currentPage - ($nbBefore - $i); ?></a></li>                
                    <?php
                }
            }
            ?>

            <li class="current"><span><?php echo $currentPage; ?></span></li>

            <?php
            for ($i = 0; $i < $nbAfter; $i++) {
                if (!isset($urlPager) || $urlPager == '') {
                    ?>
                    <li class="nb"><a href="javascript:void(0)" onclick="<?php echo $jsPager; ?>(<?php echo $limit + (($i + 1) * $nbPerPage) ?>)"><?php echo $currentPage + ($i + 1); ?></a></li>                
                    <?php
                } else {
                    ?>
                    <li class="nb"><a href="<?php echo $urlPager; ?>&amp;LIMIT=<?php echo $limit + (($i + 1) * $nbPerPage) ?>"><?php echo $currentPage + ($i + 1); ?></a></li>                
                    <?php
                }
            }
            if ($currentPage + $nbAfter < $lastPage - 1) {
                ?>
                <li class="last"><a href="javascript:void(0)" style="cursor:default; text-decoration:none;">...</a></li>
                <?php
            }
            if ($currentPage != $lastPage) {
                if (!isset($urlPager) || $urlPager == '') {
                    ?>
                    <li class="last"><a href="javascript:void(0)" onclick="<?php echo $jsPager; ?>(<?php echo ($lastPage - 1) * $nbPerPage ?>)"><?php echo $lastPage; ?></a></li>
                    <li class="next"><a href="javascript:void(0)" onclick="<?php echo $jsPager; ?>(<?php echo $limit + $nbPerPage; ?>)">Next</a></li>
                    <?php
                } else {
                    ?>
                    <li class="last"><a href="<?php echo $urlPager; ?>&amp;LIMIT=<?php echo ($lastPage - 1) * $nbPerPage ?>"><?php echo $lastPage; ?></a></li>
                    <li class="next"><a href="<?php echo $urlPager; ?>&amp;LIMIT=<?php echo $limit + $nbPerPage; ?>">Next</a></li>
                        <?php
                    }
                }
                ?>
        </ul>
    </div>
    <?php
}
?>

