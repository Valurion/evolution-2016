<div class="window_modal" id="error_div_modal" style="display: none;">
    <div class="info_modal">
        <?php         
            switch($error_num){
                case '1':        
                    echo '<h2>Compte non reconnu</h2>
                        <p>Cette adresse e-mail ne correspond pas à un compte Cairn.info.</p>';
                    break;
                case '2':
                    echo '<h2>Compte déjà existant</h2>
                        <p>Cette adresse e-mail correspond à un compte déjà enregistré sur Cairn.info.
                        <br>Si vous avez perdu ou oublié votre mot de passe, 
                        <a href="./mdp_oublie.php"><u>veuillez cliquez ici</u></a></p>';
                    break;
                case '3':
                    echo '<h2>Compte en cours d\'utilisation</h2>
                        <p>Le nombre maximum d\'utilisateurs actuellement connectés avec cette adresse email est atteint.
                        <br>Veuillez réessayer un peu plus tard.</p>';
                    break;
                case '4':
                    echo '<h2>Validation impossible</h2>
                          <p>La confirmation de l\'adresse e-mail est incorrecte.</p>';
                    break;
            }
        ?>
        <div class="buttons">
            <span onclick="cairn.close_modal()" class="blue_button ok">Fermer</span>
        </div>
        
    </div>
</div>