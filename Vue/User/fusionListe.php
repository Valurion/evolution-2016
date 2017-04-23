<div class="window_modal" id="error_div_modal" style="display: none;">
    <div class="info_modal">
        <p>Des articles ou des chapitres sont déjà enregistrés via votre compte "Mon Cairn.info"</p>
        <p>Souhaitez-vous les associer à <?= $from=="demande"?"cette demande":"ce panier"?> ?</p>
        <div class="buttons">
            <!--span onclick="cairn.close_modal()" class="blue_button ok">Fermer</span-->
            <span onclick="ajax.merge<?= ucFirst($from)?>()" class="blue_button ok">Oui</span>
            &nbsp;
            <span onclick="ajax.erase<?= ucFirst($from)?>()" class="blue_button">Non</span>
        </div>        
    </div>
</div>

