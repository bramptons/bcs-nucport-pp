<?php
require_once("initialise.inc");
$NUCLEUS = new NucleusAPI(
    $SYSTEM_SETTINGS["System"]["Nucleus"]["URL"],
    Decrypt($SYSTEM_SETTINGS["System"]["Nucleus"]["AccessKey"]),
    (!empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : getHostByName(getHostName())),
    $SYSTEM_SETTINGS["System"]["LogFile"],
    $SYSTEM_SETTINGS["System"]["DB"]["Host"],
    $SYSTEM_SETTINGS["System"]["DB"]["Username"],
    Decrypt($SYSTEM_SETTINGS["System"]["DB"]["Password"]),
    $SYSTEM_SETTINGS["System"]["DB"]["Port"],
    $SYSTEM_SETTINGS["System"]["DB"]["Schema"]
);
$NUCLEUS->ResponseCallback = function($request, $reponse) use ($NUCLEUS, $SYSTEM_SETTINGS, $NOTIFICATIONS) {

};

//$NUCLEUS->Logging = FALSE;
$NUCLEUS->LogVerbosity = intval($SYSTEM_SETTINGS["System"]["LogVerbosity"]);
ob_start();

?>

<?php
//Log response
if(!empty($SYSTEM_SETTINGS['Finance']['LogFile'])) {
    file_put_contents($SYSTEM_SETTINGS['Finance']['LogFile'], date('c')."\r\n".print_r($_POST, TRUE)."\r\n".str_repeat('-', 108)."\r\n", FILE_APPEND);
}
if(!empty($SYSTEM_SETTINGS['Finance']['WorldPay']['callbackPW']) && (Decrypt($SYSTEM_SETTINGS['Finance']['WorldPay']['callbackPW']) !== $_POST['callbackPW'])) {
    //Callback PW does not match
    die(); 
}

//Get details of transaction and create the parameters for our submission to Nucleus
$InvoiceID = intval($_POST['cartId']);
$RedirectTo = "https://".$_SERVER['HTTP_HOST']."/finance.php?do=pay&InvoiceID=".$InvoiceID;

$params = array(
    'InvoiceID' => $InvoiceID,
    'Gateway' => 'WorldPay',
);
$payment = array(
    'AddInfo' => array(),
);

$wpStatus = (isset($_POST['transStatus']) ? IdentifierStr($_POST['transStatus']) : null);
//Cancelled Payments require no change to the back end transaction; all other status codes will be processed
$response = null;
if($wpStatus <> 'C') {
    $payment['TransactionReference'] = NameStr($_POST['transId']);
    $payment['ReceivedAmount'] = round(floatvalExt($_POST['authAmount'])*100, 0);
    $payment['ISO4217'] = $_POST['authCurrency'];
    $payment['GWData'] = array(
        
    );
/*    $data = "";
    foreach($_POST AS $key => $value) {
        $data .= $key.' = ';
        if(is_array($value)) {
            $data .= print_r($value, TRUE);
        } else {
            $data .= str_replace("\n", '; ', str_replace("\r", "", $value));
        }
        $data .= "\r\n";
    }*/
    //$AddInfo = array('Gateway' => 'WorldPay');
    foreach(array('cartId', 'transId', 'transStatus', 'transTime', 'authAmount', 'authCurrency', 'authAmountString', 'rawAuthMessage', 'rawAuthCode', 'cardType', 'countryMatch', 'AVS') AS $key) {
        if(isset($_POST[$key])) {
            $payment['AddInfo'][$key] = $_POST[$key];
            $payment['GWData'][$key] = $_POST[$key];
        }
    }
/*    foreach(array('callbackPW') AS $key) {
        if(isset($_POST[$key])) {
            $payment['GWData'][$key] = $_POST[$key];
        }
    }*/
    $params['Success'] = ($wpStatus == 'Y');
    $request = new NucleusAPIRequest('onlinepayment', $params, $payment);
    $response = $NUCLEUS->ExecuteRequest($request);
}

//Create an entry in the database log (separate from any file logging)
$sql =
"INSERT INTO tblpaymentlog (Sender, Reference, TransactionTime, Data, APIRequest, APIResponse)
 VALUES('{$params['Gateway']}', '".mysqli_real_escape_string($SYSTEM_SETTINGS["Database"], $payment['TransactionReference'])."', FROM_UNIXTIME(".intval($_POST['transTime'])."), '".mysqli_real_escape_string($SYSTEM_SETTINGS["Database"], print_r($_POST, TRUE))."', '".mysqli_real_escape_string($SYSTEM_SETTINGS["Database"], print_r($request, TRUE))."', '".mysqli_real_escape_string($SYSTEM_SETTINGS["Database"], print_r($response, TRUE))."')
 ";
mysqli_query($SYSTEM_SETTINGS["Database"], $sql);
?>
<!DOCTYPE html>
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
	<head>
        <title>Transaction</title>
		<meta charset="UTF-8">
        <meta http-equiv="refresh" content="3; url=<?php echo $RedirectTo; ?>">
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
		<title>mySociety</title>
		<meta name="author" content="Guido Gybels">
		<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1.0">
        <style type="text/css">
            body { font-family: Helvetica, Arial, sans-serif; background-color: <wpdisplay disp=body.bg>; <wpdisplay disp=body.bg.image pre=" background-image: url('" post="');"> margin: 0;}
            table.header { background-color: <wpdisplay disp=header.bg>; width: 760px; border: 0;}
            td.headerlogo1 {background-color: <wpdisplay disp=header.bg>; vertical-align: <wpdisplay disp=header.logo1.valign>; width: 383px; text-align: <wpdisplay disp=header.logo1.halign>;}
            td.headerlogo2 {background-color: <wpdisplay disp=body.bg>; vertical-align: <wpdisplay disp=header.logo2.valign>; width: 272px; text-align: <wpdisplay disp=header.logo2.halign>;}
            table.nav { background-image:url('/images/wp/navbar.gif'); width: 760px; border: 0;}
            table.container { background-color: <wpdisplay disp=wp.container.border.bg>; width: <wpdisplay disp=wp.container.width>; border: 0;}
            td.title { background-color: <wpdisplay disp=title.bg>; width: 100%; border: 0;}
            table.containercell { background-color: <wpdisplay disp=1.bg>; width: 100%; border: <wpdisplay disp=wp.container.cellBorder>;}
            td.authenticationbanner { background-color: <wpdisplay disp=1.bg>; vertical-align: top; text-align: left; border: 0;}
            td.bannercontainer { background-color: <wpdisplay disp=banner.border.bg>; width: <wpdisplay disp=banner.width empty=95%>; border: <wpdisplay disp=banner.cellBorder>; margin-right: auto; margin-left: auto;}
            table.banner { background-color: <wpdisplay disp=banner.bg>; width: <wpdisplay disp=banner.width>; vertical-align: <wpdisplay disp=header.logo2.valign>; text-align: <wpdisplay disp=header.logo2.halign>; border: <wpdisplay disp=banner.cellBorder>; border-color: <wpdisplay disp=banner.border.bg>;}
            td.banner { background-color: <wpdisplay disp=banner.bg>; vertical-align: top; text-align: left; border: <wpdisplay disp=banner.cellBorder>;}
            td.bannererror { background-color: <wpdisplay disp=banner.bg>; vertical-align: top; text-align: center;}
            h1 {font-size: <wpdisplay fontsize=|disp=title.font.size empty=|disp=title.font.size>; font-family: <wpdisplay disp=title.font>; color: <wpdisplay disp=title.fg>;}
            A.header:Link {text-decoration: none; color: <wpdisplay disp=header.font.fg>; font-family: <wpdisplay disp=header.font>; font-size: <wpdisplay fontsize=|disp=header.font.size empty=|disp=header.font.size>; font-weight: bold;}
            A.header:Visited {text-decoration: none; color: <wpdisplay disp=header.font.fg>; font-family: <wpdisplay disp=header.font>; font-size: <wpdisplay fontsize=|disp=header.font.size empty=|disp=header.font.size>; font-weight: bold;}
            A.header:Active {text-decoration: none; color: <wpdisplay disp=header.font.fg>; font-family: <wpdisplay disp=header.font>; font-size: <wpdisplay fontsize=|disp=header.font.size empty=|disp=header.font.size>; font-weight: bold;}
            A.header:Hover {text-decoration: underlined; color: <wpdisplay disp=header.font.ahover.fg>; font-family: <wpdisplay disp=header.font>; font-size: <wpdisplay fontsize=|disp=header.font.size empty=|disp=header.font.size>; font-weight: bold;}
            td.footerdivider {<wpdisplay disp=footer.divider.bg.image pre=" background-image: url('" post="');">; vertical-align: <wpdisplay disp=footer.valign>; text-align: <wpdisplay disp=footer.halign>;}
            td.footer {background-color: <wpdisplay disp=body.bg>; vertical-align: <wpdisplay disp=footer.valign>; text-align: <wpdisplay disp=footer.halign>;}
            div.message {
                margin-top: 20px;
            }
        </style>     
	</head>
	<body>
        <div>
            <WPDISPLAY ITEM=banner>
        </div>
        <div class="message">
            <p>You will now be taken back to <?php echo $SYSTEM_SETTINGS["General"]['OrgLongName']; ?>. <a href="<?php echo $RedirectTo; ?>">Click here if you are not redirected within 10 seconds.</a> </p>
        </div>
    </body>
 </html>
<?php
ob_end_flush();
?>

