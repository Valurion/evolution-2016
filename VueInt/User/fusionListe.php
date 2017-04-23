<div class="window_modal" id="error_div_modal" style="display: none;">
    <div class="info_modal">
        <p>Some items are already saved into your Cairn info account's cart</p>
        <p>Would you like to associate them to this cart ?</p>
        <div class="buttons">
            <!--span onclick="cairn.close_modal()" class="blue_button ok">Fermer</span-->
            <span onclick="ajax.merge<?= ucFirst($from)?>()" class="blue_button ok">Yes</span>
            &nbsp;
            <span onclick="ajax.erase<?= ucFirst($from)?>()" class="blue_button">No</span>
        </div>        
    </div>
</div>

