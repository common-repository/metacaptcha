<?php
//Your keys go here
define('PRIVATE_KEY','7e28LOO5ShrYGX2mFCPv'); //PRIVATE KEY
define('PUBLIC_ID','51e724fd16d3129418000000'); //PUBLIC ID

define('INTERVAL', 60*5); //interval  where two keys can exist at the same time.
define('REFRESHING_TIME', 24 * 3600);

/*
* Intergrating metaCATPCHA into the webform
 * @param  $processPath link to metacaptcha_lib.php in the server
 * @param  $formID ID of the form
 * @return metaCATPCHA initialization's html and javascript code
*/

function initialize_metacaptcha( $processPath, $formID=null)
{
    $html = '<script src="//metacaptcha.com/services/application/js/metacaptcha.js"></script>';
    if ($formID===null)
    {
        $html.= '<input type="hidden" name="metacaptchaField" id="metacaptchaField" value="" />';
        $html.= "<script> initialize_metacaptcha('$processPath');</script>";
    }
    else
        $html.= "<script>initialize_metacaptcha('$processPath','$formID');</script>";
    return $html;
}

/*
* Verify the answer
 * @param  $metacaptchaField value of the embedded hidden field created by metaCAPTCHA
 * @param  $msg Content of the message
 * @return the object containing the puzzle request message
*/
function metacaptcha_verify($metacaptchaField, $msg)
{
    try
    {
        //extract data from object
        $metaCAPTCHA =  json_decode($metacaptchaField);
        $ts =$metaCAPTCHA->ts;
        $te = $metaCAPTCHA->te;
        //calculate S1
        $C = $ts.$msg;
        $ID= PUBLIC_ID;
        $S2 = $metaCAPTCHA->S2;
        //verify
        $Ks_array = generateKsArray();
        foreach($Ks_array as $Ks)
        {
            $S1 = $C.$ID.hash_hmac("sha256",$C.$ID, $Ks);
            if ($ts.$te. hash_hmac("sha256",$ts.$te.$S1, $Ks)===$S2)
                return true;
        }
    }
    catch (Exception $e)
    {
        return false;
    }
    return false;
}

/*
* Issue puzzle request message
* @param  $msg
* @return the object containing the puzzle request message
*/
function _metacaptcha_return_initial_cookie($msg){
    $Ks = getKsFromPK(PRIVATE_KEY);
    $ip = preg_replace( '/[^0-9., ]/', '', $_SERVER['REMOTE_ADDR'] );
    $S = 10; // Local score
    $ts = time();
    //prepare data for HMAC
    $C = $ts.$msg;
    $ID= PUBLIC_ID;
    $S1 = $C.$ID.hash_hmac("sha256",$C.$ID, $Ks);
    return array(
        'S1' =>  $S1,
        'ts' => $ts,
        'S'     => $S,
        'comment_author_ip' => $ip,
        'comment_date' => $ts,
        'comment_author_url' => 'http://google.com',
        'comment_content' => $msg,
        'api_key' => PUBLIC_ID
    );
}

/*
* Generate new Ks from the private key. If generation happens at the beginning of a period, two Ks are created.
* @param  $private_key
* @return  $Ks_array array contains key(s)
*/
function generateKsArray()
{
    $dateTime = new DateTime("now");
    $ts = $dateTime->getTimestamp();
    if (intval($ts%REFRESHING_TIME) < INTERVAL)
        $Ks_array = array ( getKsFromPK(PRIVATE_KEY),  getKsFromPK(PRIVATE_KEY,true));
    else
        $Ks_array = array ( getKsFromPK(PRIVATE_KEY));
    return $Ks_array;
}

/*
 * Generate new Ks from the private key. The generated key is different for each period indicated by REFRESHING_TIME
 * It can also be used to generated Ks from the previous period by setting the $previous param to true
 * @param  $private_key
 * @param  $previous: set to true if wanting to generate key of previous period
 * @return  Ks
 */
function getKsFromPK($private_key, $previous = false)
{
    if ($previous == true)
        $k = 1;
    else
        $k = 0;
    $dateTime = new DateTime("now");
    $ts = $dateTime->getTimestamp();
    $concat = (intval( $ts /REFRESHING_TIME) - $k). $private_key ;
    return hash ('sha256', $concat);
    //return $concat;
}
