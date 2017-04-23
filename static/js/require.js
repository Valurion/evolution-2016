$(document).ready(function()
{
    var browser = navigator.userAgent.toLowerCase();
    
    if(!Modernizr.input.required || (browser.indexOf("safari") != -1 && browser.indexOf("chrome") == -1))
    {        
        $('input[type="submit"]').on('click',function(event)
        {
            that = this;
            
            var count = 0;
            var formID = $(this).parent('form').attr('id');
            
            $('form#' + formID + ' *[required="required"]').each(function(index,value){
                if( $(value).val().length == 0 )
                {
                    count++;
                }
            });

            if(count > 0)
            {   
                //cancel submit..
                event.preventDefault();
            
                //check if form is in a modal_popup..
                if( $(this).parents('.window_modal').length > 0 ) 
                {
                    var subAlertContent = 'Il faut remplir les champs obligatoire.';                    
                    
                    $('.window_modal form *[required="required"]').each(function(index,value){
                        if( $(value).val().length == 0 )
                        {
                            $(value).addClass('mustFill');
                            $(value).val(subAlertContent) || $(value).text('subAlertContent');                        
                            $(value).css('color','red');
                        }                        
                    });
                    
                    $(this).attr('disabled',true);
                    
                    var clear = setTimeout(function(){
                        $('.mustFill').css('color','#000000');
                        $('.mustFill').val('');
                        $('input.mustFill').removeClass('mustFill');
                        $(that).attr('disabled',false);
                    }, 3000);                    
                }
                else
                {                    
                    cairn.show_modal('#modal_empty_input');
                }
            }
        });
    }
    else
    {
        //do the regular thing..
    }
});


