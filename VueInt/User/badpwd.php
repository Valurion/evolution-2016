<div class="window_modal" id="error_div_modal" style="display: none;">
    <div class="info_modal">
        <h2>Incorrect password</h2>
        <?php         
            switch($error_num){
                case '1':
                    echo '<p>The password entered does not match the account Cairn.info.</p>';
                    break;
                case '2':
                    echo '<p>The confirmation of the password is incorrect.</p>';
                    break;
                case '3':
                    echo '<p>
                            The new password you entered is not valid.<br />
                            Please only use :<br />
                            - numbers ;<br />
                            - punctuation marks ;<br />
                            - and/or letters ;<br />
                            with a minimum of 6 and a maximum of 20 characters.
                          </p>';
                    break;
            }
        ?>
        <div class="buttons">
            <span onclick="cairn.close_modal()" class="blue_button ok">Close</span>
        </div>
        
    </div>
</div>
