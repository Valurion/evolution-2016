<div class="window_modal" id="error_div_modal" style="display: none;">
    <div class="info_modal">
        <h2>Mot de passe incorrect</h2>
        <?php         
            switch($error_num){
                case '1':
                    echo '<p>Le mot de passe saisi ne correspond pas au compte Cairn.info.</p>';
                    break;
                case '2':
                    echo '<p>La confirmation du mot de passe est incorrecte.</p>';
                    break;
                case '3':
                    echo '<p>
                            Le mot de passe que vous avez choisi n\'est pas valable.<br />
                            Merci de n\'utiliser que :<br />
                            - des chiffres (0-9)<br />
                            - des lettres minuscules non accentuées (a-z)<br />
                            - des lettres majuscules non accentuées (A-Z)<br />
                            - et/ou les signes !$%@#<br />
                            avec un minimum de 6 et un maximum de 20 caractères.
                          </p>';
                    break;
            }
        ?>
        <div class="buttons">
            <span onclick="cairn.close_modal()" class="blue_button ok">Fermer</span>
        </div>
        
    </div>
</div>
