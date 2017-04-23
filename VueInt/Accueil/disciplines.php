<?php
/**
 *
 *
 * @version 0.1
 * @author ©Pythagoria - www.pythagoria.com - Pierre-Yves THOMAS
 * @todo : documentation du modèle transmis
 */
?>
<?php
$typePub = 'revue';
include (__DIR__ . '/../CommonBlocs/tabs.php');
$curDisc = array();
foreach($disciplines as $discipline){
    if($discipline['POS_DISC'] == $curDisciplinePos){
        $curDisc = $discipline;
    }
}
$this->titre = $this->titre = $curDisc['DISCIPLINE_EN'] . " - Journals";
$allRevues = array_merge($revues, $revuesAbo);
// var_dump($revue, $revuesAbo);die();
// Vu qu'il y a deux listes différentes, l'ordre des revues par trishow doit être recalculé pour la fusion de ces deux listes
if (count($revues) && count($revuesAbo)) {
    usort($allRevues, function($r1, $r2) {
        return strtolower($r1['TRISHOW']) > strtolower($r2['TRISHOW']);
    });
}
?>
<div id="breadcrump">
    <a class="inactive" href="/">Home</a>
    <span class="icon-breadcrump-arrow icon"></span>
    <?php if(empty($curDisc)){?>
    <a href="/listrev.php">Journal List</a>
    <?php }else{ ?>
    <a href="/disc-<?=$curDiscipline?>.htm"><?= $curDisc['DISCIPLINE_EN']?></a>
    <?php } ?>
</div>

<div id="body-content" style="margin-top: 0px;">
    <?php if($curDiscipline == 'ALL'){?>
        <div id="pagination" class="listIssues">
        <h3 class="main-title">More Journals</h3>
        <p class="letters">
            <?php foreach ($letters as $letter): ?>
                <?php if ($letter['A'] == 1): ?>
                    <?php if (isset($LET) && $LET == $letter['LET']): ?>
                        <span><a class='activated'><?= $this->nettoyer($letter['LET']) ?></a></span>
                    <?php else: ?>
                        <span><a href="listrev.php?id=<?= $curDiscipline . '&TITRE=' . $this->nettoyer($letter['LET']) ?>">
                                <?= $this->nettoyer($letter['LET']) ?>
                            </a></span>
                    <?php endif; ?>
                <?php else: ?>
                    <!--span class="desactivate"><?= $this->nettoyer($letter['LET']) ?></span-->
                <?php endif; ?>
            <?php endforeach; ?>
            <?php if (isset($LET) && $LET == 'ALL'): ?>
                    <span><a href="#" class="activated all">All</a></span>
            <?php else: ?>
                    <span><a href="listrev.php?" class="all">All</a></span>
            <?php endif; ?>
        </div>
        <h3 class="main-title"><?= count($allRevues) ?> Journals</h3>
    <?php }else{ ?>
        <div class="researchArea">
        <h1><?= $curDisc['DISCIPLINE_EN']?><span>[<?= count($allRevues)?> <span style="padding:0;" id="js_journal">Journals</span>]</span>
            <span class="frenchVersion"><a href="<?= Service::get('ParseDatas')->getCrossDomainUrl(); ?>/disc-<?= $curDisc['URL_REWRITING'] ?>.htm">Switch to French Edition</a></span></h1>
        </div>
    <?php } ?>

    <?php
    // Contrairement à cairn, on ne fait pas le distinguo entre les revues abonnées et les autres
    $arrayForList = $allRevues;
    $arrayFieldsToDisplay = array("STITRE","SAVOIR_PLUS_EN");
    include (__DIR__ . '/../CommonBlocs/liste_2col.php');
    ?>

</div>
