<div class="window_modal" id="error_div_modal" style="display: none;">
    <div class="info_modal">
        <?php         
            switch($error_num){
                case '1':        
                    echo '<h2>Unrecognized Account</h2>
                        <p>This email address does not match a Cairn.info account.</p>';
                    break;
                case '2':
                    echo '<h2>Existing account</h2>
                        <p>This email address is already registered on Cairn.info.
                        <br>If you have lost or forgotten your password, 
                        <a href="./password_forgotten.php"><u>click here</u></a></p>';
                    break;
                case '3':
                    echo '<h2>Account already logged in</h2>
                        <p>The maximum number of users currently connected to this email address is reached.
                        <br>Please try again later.</p>';
                    break;
                case '4':
                    echo '<h2>Invalid confirmation</h2>
                          <p>The confirmation of the email address is incorrect.</p>';
                    break;
            }
        ?>
        <div class="buttons">
            <span onclick="cairn.close_modal()" class="blue_button ok">Close</span>
        </div>
        
    </div>
</div>