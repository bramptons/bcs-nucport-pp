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

/**
 * @author Guido Gybels
 * @copyright 2016
 * @project NUCLEUS Portal
 * @description This unit provides a json interface for a number of system functions
 */

if(!empty($_GET['do']))
{
    $do = IdentifierStr($_GET['do']);
    $response = array();
    switch($do)
    {
        case 'login':
            $APIResponse = $NUCLEUS->LogIn(trim($_GET['username']), $_GET['password']);
            break;
        case 'logout':
            $APIResponse = $NUCLEUS->LogOut();
            break;
        case 'resetpw':
            $APIResponse = $NUCLEUS->ResetPW(trim($_GET['username']));
            break;
        case 'newuser':
            $APIResponse = $NUCLEUS->NewUser(trim($_GET['email']), trim($_GET['firstname']), trim($_GET['lastname']));
            break;
        case 'execute':
        case 'saveapiform':
            if(!empty($_GET['cmd'])) {
                $request = new NucleusAPIRequest($_GET['cmd'], array(), $_POST);
                $APIResponse = $NUCLEUS->ExecuteRequest($request);
                //file_put_contents("D:\\temp\\apiresponse2", print_r($APIResponse, TRUE));
            } else {
                header($_SERVER["SERVER_PROTOCOL"]." 400 Bad Request");
                die();
            }
            break;
        case 'unsubscribe':
        case 'subscribe':
            $request = new NucleusAPIRequest($do, array(), $_POST);
            $APIResponse = $NUCLEUS->ExecuteRequest($request);
            break;
        case 'changepassword':
            $request = new NucleusAPIRequest('changepassword', array(), $_POST);
            $APIResponse = $NUCLEUS->ExecuteRequest($request);
            break;
        case 'eligiblegrades':
            $request = new NucleusAPIRequest('eligiblegrades', array(), $_POST);
            $APIResponse = $NUCLEUS->ExecuteRequest($request);
            break;
        case 'savecollectionitem':
            $section = array();
            parse_str($_POST['_section'], $section);
            unset($_POST['_section']);
            $request = new NucleusAPIRequest($section['api']['set']['cmd'], array(), $_POST);
            $APIResponse = $NUCLEUS->ExecuteRequest($request);
            break;
        case 'delcollectionitem':
            $section = array();
            parse_str($_POST['_section'], $section);
            unset($_POST['_section']);
            $request = new NucleusAPIRequest($section['api']['del']['cmd'], array(), $_POST);
            $APIResponse = $NUCLEUS->ExecuteRequest($request);            
            break;
        case 'savesettings':
            if (IsAdministrator($NUCLEUS)) {
                switch($_GET['form']) {
                    case 'frmSystem':
                        $fields = array(
                            'System.Timezone' => 'mimetype',
                            'General.PortalName' => 'punctuatedtext',
                            'System.LogFile' => 'filepath',
                            'System.LogVerbosity' => 'integer',
                            'System.DebugMode' => 'boolean',
                            'System.DB.Host' => 'url',
                            'System.DB.Port' => array('fieldtype' => 'integer', 'default' => 3306),
                            'System.DB.Schema' => 'varname',
                            'System.DB.Username' => 'name',
                            'System.DB.Password' => array('fieldtype' => 'string', 'encrypted' => TRUE),
                            'System.Nucleus.URL' => 'url',
                            'System.Nucleus.AccessKey' => array('fieldtype' => 'string', 'encrypted' => TRUE),
                        );
                        break;
                    case 'frmGeneral':
                        $fields = array(
                            'General.OrgLongName' => 'punctuatedtext',
                            'General.OrgSubjectArea' =>  array('fieldtype' => 'punctuatedtext', 'processfn' => @strtolower),
                            'General.StrapLine' => 'punctuatedtext',
                            'General.OrgShortName' => 'name',
                            'General.Address.Lines' => 'text',
                            'General.Address.Postcode' => 'name',
                            'General.Address.Town' => 'name',
                            'General.Address.County' => 'name',
                            'General.Address.Region' => 'name',
                            'General.Address.CountryCode' => array('fieldtype' => 'name', 'processfn' => @strtoupper),
                            'General.Address.Country' => 'name',                            
                            'General.Website' => 'url',
                        );
                        break;
                    case 'frmSecurity':
                        $fields = array(
                            'Security.EncryptionKey' => 'filepath',
                            'Security.AllowPasswordChange' => 'boolean',
                        );
                        break;
                    case 'frmFinance':
                        $fields = array(
                            'Customise.Menu.DirectDebit' => 'boolean',
                            'Finance.DDMSOnly' => 'boolean',
                            'Finance.AllowDonations' => 'boolean',
                            'Finance.TestMode' => 'boolean',
                            'Finance.TransactionTimeout' => 'integer',
                            'Finance.LogFile' => 'filepath',
                            'Finance.WorldPay.instId' => 'string',
                            'Finance.WorldPay.accId' => 'string',
                            'Finance.WorldPay.purchaseURL' => 'url',
                            'Finance.WorldPay.paymentResponseURL' => 'url',
                            'Finance.WorldPay.MD5Secret' => array('fieldtype' => 'string', 'encrypted' => TRUE),
                            'Finance.WorldPay.SigningFields' => array('fieldtype' => 'array', 'sourcename' => '__SigningFields', 'delimiter' => ',', 'processfn' => @TrimAllValues),
                            'Finance.WorldPay.callbackPW' => array('fieldtype' => 'string', 'encrypted' => TRUE),
                        );
                        break;
                    case 'frmCustomise':
                        $fields = array(
                            'Customise.HeaderMedia' => 'filepath',
                            'General.StyleSheet' => 'filepath',
                            'Customise.AnimatedHeader' => 'boolean',
                            'Customise.DisabledButtonColour' => 'boolean',
                            'Customise.UseShortOrgName' => 'boolean',
                            'Customise.SidebarLogo' => 'boolean',
                            'Customise.Logos.Sidebar' => 'filepath',
                            'Customise.Navigation.Shortcuts.Website.Enable' => 'boolean',
                            'Customise.Navigation.Shortcuts.Website.Caption' => 'punctuatedtext',
                            'Customise.Navigation.Shortcuts.Join' => 'boolean',
/*                            'Customise.Menu.Events' => 'boolean',
                            'Customise.Events.UpcomingInterval' => 'integer',
                            'Customise.Events.UpcomingMax' => 'integer',
                            'Customise.Events.FutureOnly' => 'boolean',*/
                            'Customise.Menu.Membership.Branches' => 'boolean',
                            'Customise.Menu.Membership.Directory' => 'boolean',
                            'Customise.Membership.SearchRequired' => 'boolean',
                            'Customise.Membership.MinSearchLength' => array('fieldtype' => 'integer', 'default' => 2),
/*                            'Customise.Applications.Membership.AllowLife' => 'boolean',
                            'Customise.Applications.Membership.LifeHint' => 'punctuatedtext',
                            'Customise.Applications.Membership.AllowStudent' => 'boolean',
                            'Customise.Applications.Membership.StudentHint' => 'punctuatedtext',
                            'Customise.Applications.Membership.AllowRetired' => 'boolean',
                            'Customise.Applications.Membership.RetiredHint' => 'punctuatedtext',*/
                            'Customise.Applications.Membership.WhereDidYouHear' => 'boolean',
                            'Customise.Applications.Membership.DefaultGrade' => 'integer',
                            'Customise.Membership.GradeCaption' => 'punctuatedtext',
                            'Customise.Membership.JoinUs' => 'url',
//                            'Customise.Menu.CPD' => 'boolean',
                            'Customise.Menu.Services.Subscriptions' => 'boolean',
                            'Customise.Publications.ShowExpiry' => 'boolean',
                        );
                        break;
                }
                SaveSettings($fields, $SYSTEM_SETTINGS, CONSTConfigFile);
            }
            break;                        
        default:
            header($_SERVER["SERVER_PROTOCOL"]." 400 Bad Request");
            die();
    }
    if(!empty($APIResponse)) {
        foreach(array('Success', 'ErrorCode', 'ErrorMessage', 'Cmd', 'Data') AS $key) {
            $response[strtolower($key)] = $APIResponse->$key;
        }
        $response['data'] = $APIResponse->Data['data'];
    }
    echo json_encode($response);
}
else
{
    header($_SERVER["SERVER_PROTOCOL"]." 400 Bad Request");
}
ob_end_flush();

?>
