

// Ajax Handler
jQuery('#commentform').submit(function(){
    Validate();
    return false;
});


function Validate()
{
    var form = $("#commentform");
    var author = form.find("input[name=author]").val();
    var comment = form.find("textarea[name=message]").val();
    var email = form.find("input[name=email]").val();
    //kapowCheck
    content = $("#comment").val();
    metaCAPTCHA.execute( content);
    return false;
}
