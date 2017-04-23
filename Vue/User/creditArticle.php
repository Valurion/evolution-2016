<?php
$this->titre = "Crédit d'articles";
include (__DIR__ . '/../CommonBlocs/tabs.php');
?>
<div id="breadcrump">
    <a class="inactive" href="./">Accueil</a> <span class="icon-breadcrump-arrow icon"></span>
    <a href="#">Crédit d'articles</a>
</div>

<div id="body-content">
    <div id="free_text">
        <h1 class="main-title">Mon crédit d'articles</h1>

        <a target="_blank" href=" http://aide.cairn.info/le-credit-darticles/"><span class="question-mark">
                <span class="tooltip">En savoir plus sur le crédit d'articles</span>
            </span></a>

        <?php if(empty($credit)){ ?>
        <h2 class="section">
            <span>A propos des crédits d'articles</span>
        </h2>
        <p>
            Un crédit d'articles Cairn.info vous permet d'acheter des contenus
            sur le site sans devoir procéder à un paiement à chaque
            commande. Aprèss avoir acheté un crédit d'articles, il vous sera proposé, à l'étape de paiement dans le processus de commande, de déduire l’achat de votre crédit d’articles.<br> Les crédits d'articles sont d'un montant
            de 50 à 200 euros et peuvent être acquis sous forme de cartes
            prépayées expédiées par poste. Ils sont valables pendant plus de 12
            mois. <br>   
        </p>
        <?php } ?>
        <h2 class="section">
            <span>Votre crédit d'articles</span>
        </h2>
        <?php if(empty($credit)){ ?>
        <p>Vous ne disposez pas de crédit d'articles valide pour l'instant.</p>
        <?php }else{ ?>
        <p>
            Solde : <?= $credit['solde'] ?> euros 
            <br> Valable jusque : <?= $credit['expire']?>
            <br> <a href="mon_credit.php">Voir le détail de votre crédit d'articles</a>
        </p>
        <?php } ?>
        <h2 class="section">
            <span>Acheter un crédit immédiat</span>
        </h2>
        <p>
            <?php $year = date('Y', mktime(0, 0, 0, date('m'), date('d'), date('Y') + 1)) ?>
            Montant :<br> <input type="radio" value="50" name="credit">
            50,00 € - valables jusque fin <?= $year ?><br> <input type="radio" value="100" name="credit"> 100,00 € -
            valables jusque fin <?= $year ?><br> <input type="radio" value="200" name="credit"> 200,00 € - valables jusque
            fin <?= $year ?><br> <br> <input type="button" value="Acheter" class="button submit_button" onclick="cairn.panierCredit()">
        </p>
    </div>
</div>