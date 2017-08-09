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
 * @description This unit provides an interface for loading data into webpages
 */

require_once("tables.inc");        
require_once("advcontrols.inc");

//If an error has occurred, we'll render an alert box
$tabs = (!empty($_GET['tabs']) ? intval($_GET['tabs']) : 9);
$alert = array(
    'type' => 'error',
    'title' => 'Error',
    'canhide' => TRUE,
    'items' => array(
        'error' => array('type' => 'item', 'caption' => 'Unable to load data.')
    )
);

if(!empty($_GET['do']))
{
    $do = IdentifierStr($_GET['do']);
    switch($do)
    {
        case 'loadcollection':
            if(CheckRequiredParams(array('sectionname' => FALSE, 'section' => FALSE), $_GET, FALSE)) {
                $sectionname = IdentifierStr($_GET['sectionname']);
                $section = array();
                parse_str($_GET['section'], $section);
                CollectionPage($sectionname, $section);
            }
            break;
        case 'editcollectionitem':
            $sectionname = IdentifierStr($_GET['sectionname']);
            $section = array();
            parse_str($_GET['section'], $section);
            ModalHeader($section['title']);
            ModalBody(FALSE);
            $settings = $section;
            $settings['api']['set']['method'] = 'MODAL';
            //print_r($settings);
            $formID = 'frm'.ucfirst($sectionname);
            APIForm($settings, array('title' => '', 'id' => $formID, 'datasource' => $_POST));
            ModalBody(TRUE);
            if(!empty($section['selector'])) {
                $cbSuccess = "function(){ LoadAppPage('{$section['selector']}', '{$sectionname}'); }"; 
            } else {
                $cbSuccess = "function(){ LoadContent('tab-{$sectionname}', '/load.php?do=loadcollection', { spinner: false, urlparams: { sectionname: '{$sectionname}', section: '{$_GET['section']}' } }); }"; 
            }
            ModalFooter($formID, "/syscall.php?do=savecollectionitem", $cbSuccess);
            break;
        case 'LinkTable':
            if (IsAdministrator($NUCLEUS)) {
                $datasource = array();
                foreach($SYSTEM_SETTINGS["Customise"]["Links"] AS $linkid => $link) {
                    $link['linkid'] = $linkid;
                    $datasource[] = $link;
                }                
                $table = array(
                    'header' => (count($datasource) == 0 ? FALSE : TRUE),
                    'nodatamsg' => '<i>There are no links defined</i>',
                    'columns' => array(
                        array(
                            'field' => array('name' => 'caption', 'type' => 'string'),
                            'function' => 'linklistItem', 'caption' => 'Caption',
                        ),
                        array(
                            'field' => array('name' => 'menu', 'type' => 'string'),
                            'function' => 'linklistItem', 'caption' => 'Menu',
                        ),
                        array(
                            'field' => array('name' => 'settings', 'type' => 'string'),
                            'function' => 'linklistItem', 'caption' => 'Settings', 'hide' => 'xs'
                        ),
                        array(
                            'field' => array('name' => '__actions', 'type' => 'buttons'),
                            'function' => 'linklistItem', 'caption' => 'Actions'
                        )
                    ),
                );
                StaticTable($datasource, $table, array('table'), $tabs);
                $dtBtnGroup = array();
                $dtBtnGroup[] = array(
                    'icon' => 'fa-pencil', 'iconalign' => 'left', 'caption' => 'New Link', 'tooltip' => 'Create a new Link',
                    'script' => "OpenDialog('EditLinkItem', {}, true )",
                    'type' => 'button', 'colour' => 'info'
                );
                ButtonGroup($dtBtnGroup, FALSE, null, $tabs, FALSE);
            }
            break;
        case 'appframe':
            $selector = (!empty($_GET['selector']) ? IdentifierStr($_GET['selector']) : 'members');
            if(!empty($_GET['reload'])) {
                $appdata = $NUCLEUS->GetData('getapplication', array('selector' => $selector));
                $NUCLEUS->StoreInSession('appdata', $appdata);        
            } else {
                $appdata = $NUCLEUS->GetSessionValue('appdata');
            }            
//            $appdata = $NUCLEUS->GetData('getapplication', array('selector' => $selector));
            $column = IdentifierStr($_GET['column']);
            switch($selector) {
                case 'members':
                    switch($column) {
                        case 'left':
                            if($appdata['HasOpenApplication']) {
                                Block(array('id' => 'appColLeftNav', 'margin' => TRUE), 7);
                                BlockTitle(array('id' => 'appColLeftNavTitle', 'caption' => 'Navigation'), 8);
                                Div(array('id' => 'appColLeftNavContent'), 8);
                                Div(null, 8);
                                Block(null, 7);
                            };
                            Block(array('id' => 'appColLeftArt', 'style' => 'alt', 'margin' => TRUE), 7);
                            $article = Article('sb_msapplication_1', $NUCLEUS, FALSE);
                            echo str_repeat("\t", 8).$article['html'];
                            Block(null, 7);
                            break;
                        case 'main':
                            Block(array('margin' =>TRUE), 7);
                            BlockTitle(array('id' => 'appColMainTitle', 'caption' => (!empty($SYSTEM_SETTINGS["Customise"]['UseShortOrgName']) ? $SYSTEM_SETTINGS["General"]['OrgShortName'].' ' : "").$appdata['Category']['CategoryName']." Application"), 8);
                            Div(array('id' => 'appColMainContent'), 8);
                            
                            Div(null, 8);
                            Block(null, 7);
                            break;
                        case 'right':
                            Block(array('id' => 'appColRightInfo', 'margin' => 'TRUE'), 7);
                            BlockTitle(array('caption' => 'More info'), 8);
                            Article(($appdata['HasOpenApplication'] ? (!empty($appdata['Application']['UserCanModify']) ? 'sb_msapplication_2' : 'sb_msapplication_4') : 'sb_msapplication_3'), $NUCLEUS, TRUE);
                            Block(null, 7);
                            break;
                    }
                    break;
            }
            break;
        case 'appnavigation':
            $selector = (!empty($_GET['selector']) ? IdentifierStr($_GET['selector']) : 'members');
            $appdata = $NUCLEUS->GetData('getapplication', array('selector' => $selector));
            $NUCLEUS->StoreInSession('appdata', $appdata);        
            //$appdata = $NUCLEUS->GetSessionValue('appdata');
//            $appdata = $NUCLEUS->GetData('getapplication', array('selector' => $selector));
//            file_put_contents("d:\\temp\\appdata.txt", print_r($appdata, TRUE));
            if($appdata['HasOpenApplication']) {
                $stages = GetAppStages($appdata, $selector);
                Pills($stages, null, 8, TRUE);
            }
            break;
        case 'apppage':
            $selector = (!empty($_GET['selector']) ? IdentifierStr($_GET['selector']) : 'members');
            $appdata = $NUCLEUS->GetSessionValue('appdata');
            $stages = GetAppStages($appdata, $selector);
//            $appdata = $NUCLEUS->GetData('getapplication', array('selector' => $selector));
            //file_put_contents("d:\\temp\\appdata.txt", print_r($appdata, TRUE));
            $page = (!empty($_GET['page']) ? IdentifierStr($_GET['page']) : null);
            $nextpagescript = null;
            if(!empty($page) && !empty($appdata['Application']['UserCanModify'])) {
                $found = FALSE;
                foreach($stages AS $stage) {
                    if($found) {
                        $nextpagescript = $stage['script'];
                        break;
                    }
                    if($stage['active']) {
                        $found = TRUE;
                    }
                }
            }
            switch($selector) {
                case 'members':
                    if($appdata['HasOpenApplication']) {
                        if(empty($appdata['Application']['UserCanModify']) || empty($page)) {
                            Para(array('well' => 'small'), 8);
                            echo str_repeat("\t", 8).FmtText( "<b>".$NUCLEUS->CurrentUser->Fullname."</b>"
                                                             .'<br>Applying for: <b>'.$appdata['Application']['GradeCaption'].'</b>'
                                                             .'<br>Application Status: <b>'.$appdata['Application']['StageName'].'</b>'
                            );
                            Para(null, 8);
                        }
                        if(!empty($appdata['Application']['UserCanModify'])) {
                            if(($page == 'submit') && ($appdata['Application']['UserCanModify'] && empty($appdata['Application']['CanSubmit']))) {
                                $page = null;
                            }
                            switch($page) {
                                case 'submit':
                                    if (isset($appdata['ApplicationSections']['bylaws'])) {
                                        Article('app_bylaws', $NUCLEUS);
                                    } else {
                                        Article('app_nobylaws', $NUCLEUS);
                                    }
                                    $fieldsets = array();
                                    $fieldsets[] = array('fields' => array(
                                        array('name' => 'ApplicationID', 'kind' => 'hidden'),
                                        array('name' => 'Agreed', 'kind' => 'control', 'colour' => 'primary', 'type' => 'switch', 'tooltip' => 'I have read and understood this declaration.',
                                            'hint' => 'Yes, I have read and agree this declaration.'),
                                    ));
                                    $formitem = array(
                                        'id' => 'frmSubmitApplication', 'style' => 'standard',
                                        'onsubmit' => "submitForm( 'frmSubmitApplication', '/syscall.php?do=saveapiform&cmd=submitapplication', { parseJSON: true, defErrorDlg: true, cbSuccess: function(frmElement, jsonResponse){ if(jsonResponse.data.HasOpenApplication){ AppStarted(frmElement, jsonResponse); } else { window.location.href = '/membership.php'; } } } ); return false;",
                                        'datasource' => $appdata['Application'],
                                        'buttons' => DefFormButtons("Submit Application"),
                                        'fieldsets' => $fieldsets, 'borders' => TRUE
                                    );
                                    Form($formitem);
                                    break;
                                case 'msdir':
                                    Article('app_msdir', $NUCLEUS);
                                    $dirsettings = $NUCLEUS->GetData('getdirsettings');
                                    $NUCLEUS->SendData('markcomplete', array(), array('ApplicationID' => $appdata['Application']['ApplicationID'], '_componentname' => $page));
                                    $fieldsets = array();
                                    $fieldsets[] = array('caption' => 'Online Membership Directory', 'icon' => 'fa-angle-right', 'iconalign' => 'left', 'fields' => array(
                                        array('name' => 'WSCategoryID', 'kind' => 'hidden'),
                                        array('name' => 'Elements[]', 'caption' => 'To include in Directory', 'kind' => 'control', 'type' => 'multi', 'options' => $dirsettings['Elements'], 'selected' => $dirsettings['ShowElements'], 'allowempty' => TRUE, 'hint' => 'Select which items you want include in your membership directory entry'),
                                    ));
                                    $cbsuccess = (!empty($nextpagescript) ? "cbSuccess: function(){ {$nextpagescript}; }" : "defSuccessDlg: true");
                                    $buttons = DefFormButtons((!empty($nextpagescript) ? "Save and Continue" : "Save Changes"));
                                    if(!empty($nextpagescript)) {
                                        $buttons[] = array(
                                            'type' => 'button', 'colour' => 'info', 'icon' => 'fa-caret-right', 'iconalign' => 'left',
                                            'caption' => 'No changes, continue',
                                            'script' => $nextpagescript
                                        );
                                    }
                                    $formitem = array(
                                        'id' => 'frmAppMSDirectory', 'style' => 'vertical',
                                        'onsubmit' => "submitForm( 'frmAppMSDirectory', '/syscall.php?do=saveapiform&cmd=setdirsettings', { parseJSON: true, {$cbsuccess}, defErrorDlg: true } ); return false;",
                                        'datasource' => $dirsettings, 'buttons' => $buttons,
                                        'fieldsets' => $fieldsets, 'borders' => TRUE
                                    );
                                    Form($formitem);
                                    break;
                                case 'directdebit':
                                    Article('app_directdebit', $NUCLEUS);
                                    $NUCLEUS->SendData('markcomplete', array(), array('ApplicationID' => $appdata['Application']['ApplicationID'], '_componentname' => $page));
                                    if(empty($appdata['Application']['DDIID'])) {
                                        $dtBtnGroup[] = array(
                                            'icon' => 'gi-plus', 'iconalign' => 'left', 'caption' => 'Pay by Direct Debit', 'tooltip' => 'Create a Direct Debit Instruction',
                                            'script' => "OpenDialog('AppDirectDebit', { large: true, urlparams: { selector: '{$selector}' } } );",
                                            'type' => 'button', 'colour' => 'success'
                                        );
                                        $dtBtnGroup[] = array(
                                            'icon' => 'fa-chevron-right', 'iconalign' => 'left', 'caption' => 'No, thanks. Skip.', 'tooltip' => 'Do not set up a Direct Debit Instruction',
                                            'script' => (!empty($nextpagescript) ? $nextpagescript : "LoadAppPage({$selector});"),
                                            'type' => 'button', 'colour' => 'info'
                                        );
                                        ButtonGroup($dtBtnGroup, FALSE, null, 10, FALSE);                                    
                                    } else {
                                        Para(array('well' => 'small', 'lead' => TRUE), 9);
                                        echo str_repeat("\t", 10)."You have successfully set up a Direct Debit Instruction. We will take your payment for your new membership application shortly. Afterwards, we will send you a notification message prior to collecting your renewal payment.";
                                        Para(null, 9);
                                        if(!empty($nextpagescript)) {
                                            Button(array(
                                                'icon' => 'fa-chevron-right', 'iconalign' => 'left', 'caption' => 'Continue to next page',
                                                'script' => $nextpagescript,
                                                'type' => 'button', 'colour' => 'info'
                                            ), FALSE, 10);
                                        }
                                    }
                                    break;
                                default: //show intro if no page given
                                    if(!empty($page) && isset($appdata['ApplicationSections'][$page])) {
                                        if(!empty($appdata['ApplicationSections'][$page]['intro'])) {
                                            echo TabbedOutput($appdata['ApplicationSections'][$page]['intro'], 9);
                                        }
                                        switch($appdata['ApplicationSections'][$page]['sectiontype']) {
                                            case 'form':
                                                APIForm($appdata['ApplicationSections'][$page], array('id' => 'frm'.ucfirst($page), 'ApplicationID' => $appdata['Application']['ApplicationID'], 'selector' => $selector, 'page' => $page, 'nextpage' => $nextpagescript), 10);
                                                break;
                                            case 'formgroup':
                                                foreach($appdata['ApplicationSections'][$page]['forms'] AS $formid => $form) {
                                                    $headingitem = array('caption' => $form['title'], 'level' => 5, 'style' => 'legend');
                                                    Heading($headingitem, 10);
                                                    if(!empty($form['intro'])) {
                                                        TabbedOutput($form['intro'], 10);
                                                    }
                                                    unset($form['title']);
                                                    APIForm($form, array('id' => 'frm'.ucfirst($page).$formid, 'formstyle' => 'vertical', 'FormID' => $formid, 'ApplicationID' => $appdata['Application']['ApplicationID'], 'selector' => $selector, 'page' => $page), 10);
                                                };
                                                if(!empty($nextpagescript)) {
                                                    Div(array('class' => 'pull-down'), 10);
                                                    Button(array(
                                                        'icon' => 'fa-chevron-right', 'iconalign' => 'left', 'caption' => 'Continue to next page',
//                                                        'script' => $nextpagescript,
                                                        'script' => "execSyscall('/syscall.php?do=execute&cmd=markcomplete', { parseJSON: true, defErrorDlg: true, postparams: { ApplicationID: {$appdata['ApplicationSections'][$page]['applicationid']}, _sectionname: '{$page}' }, cbSuccess: function(){ {$nextpagescript}; } });",
                                                        'type' => 'button', 'colour' => 'info'
                                                    ), FALSE, 11);
                                                    Div(null, 10);
                                                }
                                                break;
                                             case 'collection':
                                                $count = CollectionPage($page, $appdata['ApplicationSections'][$page]);
                                                if(!empty($nextpagescript) && isset($appdata['ApplicationSections'][$page]['applicationid'])) {
                                                    Button(array(
                                                        'icon' => 'fa-chevron-right', 'iconalign' => 'left', 'caption' => 'Continue to next page',
//                                                        'script' => $nextpagescript,
                                                        'script' => "execSyscall('/syscall.php?do=execute&cmd=markcomplete', { parseJSON: true, defErrorDlg: true, postparams: { ApplicationID: {$appdata['ApplicationSections'][$page]['applicationid']}, _sectionname: '{$page}' }, cbSuccess: function(){ {$nextpagescript}; } });",
                                                        'type' => 'button', 'colour' => 'info',
                                                        'disabled' => (!empty($appdata['ApplicationSections'][$page]['required']) && ($count == 0)),
                                                    ), FALSE, 10);
                                                }
                                                break;
                                            case 'collectiongroup':
                                                $allowprogress = TRUE;
                                                foreach($appdata['ApplicationSections'][$page]['collections'] AS $collectionname => $collection) {
                                                    Div(array(), 9);
                                                    $section = $appdata['ApplicationSections'][$page];
                                                    $section['collectionname'] = $collectionname;
                                                    unset($section['collections']);
                                                    $section = array_merge($section, $collection);
                                                    $count = CollectionPage($page, $section);
                                                    if(!empty($collection['required']) && ($count == 0)) {
                                                        $allowprogress = FALSE;
                                                    }
                                                    Div(null, 9);
                                                }
                                                if(!empty($nextpagescript)) {
                                                    Div(array('class' => 'pull-down'), 10);
                                                    Button(array(
                                                        'icon' => 'fa-chevron-right', 'iconalign' => 'left', 'caption' => 'Continue to next page',
//                                                        'script' => $nextpagescript,
                                                        'script' => "execSyscall('/syscall.php?do=execute&cmd=markcomplete', { parseJSON: true, defErrorDlg: true, postparams: { ApplicationID: {$appdata['ApplicationSections'][$page]['applicationid']}, _sectionname: '{$page}' }, cbSuccess: function(){ {$nextpagescript}; } });",
                                                        'type' => 'button', 'colour' => 'info',
                                                        'disabled' => !$allowprogress
                                                    ), FALSE, 11);
                                                    Div(null, 10);
                                                }
                                                break;
                                        }
                                    } else {
                                        Article('sb_msapplication_resume', $NUCLEUS, TRUE);
                                        $items = $stages;
                                        unset($items[0]);
                                        LinksTable(array('items' => $items), 9);
                                        Button(array(
                                            'icon' => 'fa-ban', 'iconalign' => 'left', 'caption' => 'Cancel my Application',
                                            'script' => "confirmExecSyscall('Cancel', 'Are you sure you want to cancel this application? This action cannot be undone!', '/syscall.php?do=execute&cmd=cancelapplication', { parseJSON: true, defErrorDlg: true, postparams: { selector: '{$selector}' }, cbSuccess: function(){ window.location.href = '/index.php'; } })",
                                            'type' => 'button', 'colour' => 'warning'
                                        ), FALSE, 10);
                                    }
                            }
                        } elseif(!empty($appdata['Application']['DDPaid'])) {
                            Article('sb_msapplication_ddpaid', $NUCLEUS, TRUE);
                        } elseif(!empty($appdata['Application']['Paid'])) {
                            Article('sb_msapplication_paid', $NUCLEUS, TRUE);
                        } else {
                            Article('sb_msapplication_unpaid', $NUCLEUS, TRUE);
                            $datasource = array();
                            foreach(array('Description') AS $key) {
                                $datasource[$key] = $appdata['Application'][$key];
                            }
                            foreach(array('ItemNet', 'ItemVAT', 'ItemTotal') AS $key) {
                                $datasource[$key.'Txt'] = ScaledIntegerAsString($appdata['Application'][$key], "money", 100, TRUE, $appdata['Application']['Symbol']);
                            }
                            $datasource['InvoiceDateTxt'] = date('j F Y', strtotime($appdata['Application']['InvoiceDate'].' UTC'));
                            $isdue = (time() > strtotime($appdata['Application']['InvoiceDue'].' UTC') ? TRUE : FALSE);
                            $fmt = ($isdue ? "<info>" : "<danger>");
                            $fields = array(
                                array('name' => 'InvoiceDateTxt', 'caption' => 'Invoice Date', 'type' => 'string', 'kind' => 'static'),
                                array('name' => 'Description', 'caption' => 'Description', 'type' => 'string', 'kind' => 'static'),
                                array('name' => 'ItemNetTxt', 'caption' => 'Net', 'type' => 'string', 'kind' => 'static', 'formatting' => $fmt),
                                array('name' => 'ItemVATTxt', 'caption' => 'VAT', 'type' => 'string', 'kind' => 'static', 'formatting' => $fmt),
                                array('name' => 'ItemTotalTxt', 'caption' => 'TOTAL DUE', 'type' => 'string', 'kind' => 'static', 'formatting' => "{$fmt}<b>"),
                            );
                            $fieldsets = array(array('caption' => 'Payment Details', 'icon' => 'fa-angle-right', 'iconalign' => 'left', 'fields' => $fields)); 
                            $buttons = array(
                                array(
                                    'type' => 'button', 'id' => 'btndownload', 'iconalign' => 'left', 'colour' => 'info', 'caption' => 'Download as PDF',
                                    'icon' => 'hi-cloud_download',
                                    'url' => "/load.php?do=invoicepdf&InvoiceID={$appdata['Application']['InvoiceID']}", 'target' => 'newwindow'
                                ),
                                array(
                                    'type' => 'button', 'id' => 'btnpayonline', 'iconalign' => 'left', 'colour' => 'success', 'caption' => 'Pay Online',
                                    'icon' => ($appdata['Application']['Currency'] == 'EUR' ? 'gi-euro' : ($appdata['Application']['Currency'] == 'USD' ? 'gi-usd' : 'gi-gbp')),
                                    'url' => "/finance.php?do=pay&InvoiceID={$appdata['Application']['InvoiceID']}"
                                )
                            );
                            $formitem = array(
                                'id' => 'frmPayment', 'style' => 'standard',
                                'datasource' => $datasource,
                                'buttons' => $buttons,
                                'fieldsets' => $fieldsets, 'borders' => TRUE
                            );
                            Form($formitem);
                        }
                    } elseif(!$appdata['CanApply']) {
                        SimpleAlertBox('danger', "Sorry, you cannot apply for {$appdata['Category']['CategoryName']} at this moment. Please contact us for more information.", 8);
                    } else {
                        //Start new application form
                        Article('intro_msapplication', $NUCLEUS, TRUE);
                        $countries = $NUCLEUS->GetData('loadoptions', array('for' => 'ISO3166'));
                        $datasource = $NUCLEUS->GetData('getpersonal');
                        $discount = $NUCLEUS->GetData('locatediscount', array('selector' => 'members', 'mnemonic' => 'ms_new'));
                        $datasource['selector'] = $selector;
                        $datasource['NOY'] = 1;
                        $fields = array(
                            array('name' => 'selector', 'kind' => 'hidden'),
                            array('name' => 'Graduation', 'caption' => 'Graduation', 'kind' => 'control', 'type' => 'date', 'showage' => true, 'hint' => 'Date of postgraduate graduation, or expected graduation date'),
                            array('name' => 'NOY', 'caption' => 'No of Years', 'kind' => 'control', 'type' => 'combo', 'options' => array(1 => '1 Year', 3 => '3 Years'), 'size' => 3, 'required' => TRUE, 'Select the desired length of your membership'),
                            array('name' => 'ISO3166', 'caption' => 'Country of Residence', 'kind' => 'control', 'type' => 'advcombo', 'required' => TRUE, 'allowempty' => FALSE, 'options' => $countries, 'required' => TRUE),
//                            array('name' => 'MSGradeID', 'caption' => 'Select '.$SYSTEM_SETTINGS['Customise']['Membership']['GradeCaption'], 'kind' => 'control', 'type' => 'advcombo', 'size' => 6, 'allowempty' => FALSE, 'options' => $appdata['MSGrades'], 'required' => TRUE),
                        );
                        if(!empty($discount['Found'])) {
                            $fields[] = array('name' => 'DiscountID', 'kind' => 'hidden');
                            //$fields[] = array('name' => 'DiscountToPersonID', 'kind' => 'hidden');
                            $fields[] = array('name' => 'DiscountCode', 'caption' => 'Discount Code', 'kind' => 'static', 'formatting' => '<info><b>');
                            $datasource['DiscountID'] = $discount['DiscountID'];
                            $datasource['DiscountCode'] = $discount['DiscountCode'];
                        } else {
                            $fields[] = array('name' => 'DiscountCode', 'caption' => 'Discount Code', 'kind' => 'control', 'type' => 'string', 'size' => 4, 'hint' => 'If you have a valid discount code, enter it here to apply it to this application');
                        }
                        $fields[] = array('name' => 'MSGradeID', 'caption' => 'Select '.$SYSTEM_SETTINGS['Customise']['Membership']['GradeCaption'], 'kind' => 'control', 'type' => 'advcombo', 'size' => 6, 'allowempty' => FALSE, 'options' => $appdata['MSGrades'], 'required' => TRUE);
                        if($SYSTEM_SETTINGS["Customise"]["Applications"]["Membership"]['WhereDidYouHear'])
                        {
                            $fields[] = array(
                                'kind' => 'control',
                                'name' => 'WhereDidYouHear',
                                'type' => 'string',
                                'caption' => 'How did you hear about us?',
                                'hint' => 'We would be grateful if you could let us know where you heard about '.(!empty($SYSTEM_SETTINGS["Customise"]['UseShortOrgName']) ? 'the '.$SYSTEM_SETTINGS["General"]["OrgShortName"] : 'us').'.',
                            );
                        }
                        $fieldsets = array(array('caption' => 'Start your Application', 'icon' => 'fa-angle-right', 'iconalign' => 'left', 'fields' => $fields)); 
                        $init = array(
                            "$('#frmStartApplication\\\\:Graduation').on('change', function( ) {\n",
                            "\tLoadGrades();\n",
                            "});\n",
                            "$('#frmStartApplication\\\\:NOY').on('change', function() {\n",
                            "\tLoadGrades();\n",
                            "});\n",
                            "$('#frmStartApplication\\\\:ISO3166').on('change', function() {\n",
                            "\tLoadGrades();\n",
                            "});\n",
                            "$('#frmStartApplication\\\\:DiscountCode').on('change keyup paste cut', function( ) {\n",
                            "\tvar inputLen = $(this).val().length;\n",
                            "\tif((inputLen >= ".intval($SYSTEM_SETTINGS['Finance']['MinLenDiscountCode']).") || (inputLen == 0)){\n",
                            "\t\tLoadGrades();\n",
                            "\t}\n",
                            "});\n",
                        );
                        $formitem = array(
                            'id' => 'frmStartApplication', 'style' => 'standard',
                            'onsubmit' => "submitForm( 'frmStartApplication', '/syscall.php?do=saveapiform&cmd=startapplication', { parseJSON: true, defErrorDlg: true, cbSuccess: 'AppStarted' } ); return false;",
//                            'datasource' => array('selector' => $selector, 'MSGradeID' => $SYSTEM_SETTINGS['Customise']["Applications"]["Membership"]['DefaultGrade'], 'NOY' => 1, 'ISO3166' => 'GB'),
                            'datasource' => $datasource,
                            'buttons' => DefFormButtons("Start Application"),
                            'fieldsets' => $fieldsets, 'borders' => TRUE,
                            'oninitialise' => $init,
                        );
                        Form($formitem);
                        jsFormValidation($formitem['id'], TRUE, 6, TRUE);
                    }
                    break;
            }        
            break;
        case 'directdebit':
            Block(array('margin' => TRUE), 8);
            BlockTitle(array('id' => 'finMainTitle', 'caption' => 'Direct Debit'), 9);
            Article('intro_directdebit', $NUCLEUS);
            Div(array('id' => 'finMainContent'), 9);
            Div(null, 9);
            Block(null, 8);
            break;
        case 'ddilist':
            $ddilist = $NUCLEUS->GetData('ddinfo');
            SimpleHeading('Your Direct Debit instructions', 4, 'sub', 9);
            if(count($ddilist['DDIs']) > 0) {
                $listing = array(
                    'items' => array(
                    ),
                );
                foreach($ddilist['DDIs'] AS $ddi) {
                    $title = "";
                    if(!empty($ddi['InstructionScope'])) {
                        $scopes = explode(',', $ddi['InstructionScope']);
                        $title = "";
                        foreach($scopes AS $scope) {
                            if(isset($ddilist['New']['scopes'][$scope])) {
                                $title .= (empty($title) ? "" : ", ").$ddilist['New']['scopes'][$scope];
                            }
                        }
                    }
                    if(empty($title)) {
                        $title = "All payments";
                    }
                    $info = "Created ".date('j F Y', strtotime($ddi['Created'].' UTC')).", Reference ".$ddi['FormalDDReference'];
                    $listing['items'][] = array(
                        'title' => $title,
                        'caption' => $info,
//                        'script' => "$('a[href=&quot;#tab-branches&quot;]').tab('show');",
//                        'urlcolour' => 'default'
                        'badge' => $ddi['Status'],
                        'badgecolour' => $ddi['StatusColour'],
                    );
                }
                Listing($listing, 10);
            } else {
                Para(array('h5' => TRUE), 10);
                echo "<em>You have no active Direct Debit instructions</em>";
                Para(null, 10);
            }
            $dtBtnGroup = array();
            if(empty($SYSTEM_SETTINGS['Finance']['DDMSOnly']) || empty($ddilist['ValidDDI']['members'])) {
                $dtBtnGroup[] = array(
                    'icon' => 'gi-plus', 'iconalign' => 'left', 'caption' => 'New Direct Debit', 'tooltip' => 'Create a new Direct Debit Instruction',
                    'script' => "OpenDialog('NewDirectDebit', { large: true, urlparams: { } } );",
                    'type' => 'button', 'colour' => 'info'
                );
            }
            ButtonGroup($dtBtnGroup, FALSE, null, 10, FALSE);
            break;
        case 'AppDirectDebit':
            $selector = IdentifierStr($_GET['selector']);
        case 'NewDirectDebit':
            $ddi = $NUCLEUS->GetData('ddinfo');
            ModalHeader('Direct Debit Instruction');
            ModalBody(FALSE);
                Div(array('class' => array('row')), 9);
                Div(array('class' => 'col-xs-8'), 10);
                    $headingitem = array('caption' => 'Instruction to your bank or building society to pay by Direct Debit', 'level' => 4, 'style' => 'default', 'bold' => TRUE, 'colour' => 'primary');
                    Heading($headingitem, 11);
                    Article('dd_newinstr', $NUCLEUS);
                Div(null, 10);
                Div(array('class' => 'col-xs-4'), 10);
                    Article('finance_contactdetails', $NUCLEUS);
                Div(null, 10);
/*                Div(array('class' => 'col-xs-3'), 10);
                    echo str_repeat("\t", 11)."<img class=\"ddlogo\" alt=\"Direct Debit Logo\" src=\"img/ddlogo.png\">\n";
                Div(null, 10);*/
                Div(null, 9);            
                Div(array('class' => array('row')), 9);
                Div(array('class' => 'col-xs-12'), 10);
                    Div(array('id' => 'formErrors', 'class' => array('display-none')), 11);
                        echo str_repeat("\t", 12)."<p class=\"h4 text-danger bold\"></p>\n";
                    Div(null, 11);
                    $datasource = array('InstructionScope' => 'members');
                    $fields = array();
                    if(!empty($SYSTEM_SETTINGS['Finance']['DDMSOnly'])) {
                        $fields[] = array('name' => 'InstructionScope', 'kind' => 'hidden');
                        $datasource['InstructionScopeTxt'] = $ddi['New']['scopes']['members'].', '.$SYSTEM_SETTINGS['General']['OrgLongName'];
                        $fields[] = array('name' => 'InstructionScopeTxt', 'caption' => 'For', 'kind' => 'static', 'type' => 'set', 'formatting' => '<b><primary>');                    
                    } else {
                        $fields[] = array('name' => 'InstructionScope[]', 'caption' => 'For', 'kind' => 'control', 'type' => 'multi', 'options' => $ddi['New']['scopes'], 'required' => TRUE, 'allowempty' => FALSE, 'hint' => 'Select the type of payments you want to make via Direct Debit');
                    }
                    $fields[] = array('name' => 'AccountHolder', 'caption' => 'Account Holder', 'kind' => 'control', 'type' => 'string', 'required' => TRUE);
                    $fields[] = array('name' => 'SortCode', 'caption' => 'Sort Code', 'kind' => 'control', 'type' => 'string', 'required' => TRUE, 'size' => 6, 'encrypted' => TRUE);
                    $fields[] = array('name' => 'AccountNo', 'caption' => 'Account No', 'kind' => 'control', 'type' => 'string', 'required' => TRUE, 'size' => 6, 'encrypted' => TRUE);
                    if($ddi['Policies']['reqbankname']) {
                        $fields[] = array('name' => 'BankName', 'caption' => 'Bank Name', 'kind' => 'control', 'type' => 'string', 'required' => TRUE);
                    } else {
                        $fields[] = array('name' => 'BankName', 'kind' => 'hidden');
                    }
                    $fields[] = array('name' => 'Agreed', 'kind' => 'control', 'colour' => 'primary', 'type' => 'switch', 'tooltip' => 'I have read and understood this declaration.',
                          'hint' => 'I have read and understood this declaration. I confirm that I am the account holder and am the only person required to authorise debits on this account.'
                    );
                    $fieldsets[] = array('fields' => $fields);
                    $formitem = array(
                        'id' => 'frmNewDDI', 'style' => 'standard', 'spinner' => TRUE,
                        'datasource' => $datasource, 'buttons' => array(),
                        'fieldsets' => $fieldsets, 'borders' => TRUE
                    );
                    Form($formitem);
                Div(null, 10);
                Div(null, 9);            
                Div(array('class' => array('row', 'pull-down')), 9);
                Div(array('class' => 'col-xs-12'), 10);
                    echo str_repeat("\t", 11)."<img class=\"ddlogo\" alt=\"Direct Debit Logo\" src=\"img/ddlogo.png\">\n";
                    Article('ddguarantee', $NUCLEUS);
                Div(null, 10);
                Div(null, 9);            
            ModalBody(TRUE);
            if(!empty($selector)) {
                $cbSuccess = "function(){ LoadAppPage('{$selector}', 'directdebit'); }"; 
            } else {
                $cbSuccess = "function(){ LoadContent('finMainContent', '/load.php?do=ddilist', { spinner: true }); }"; 
            }
            ModalFooter(
                $formitem['id'],
                "/syscall.php?do=saveapiform&cmd=newddi",
                $cbSuccess,
                "Submit",
                null,
                "function( frmElement ) { return ValidateBankForm(frmElement, { method: 'pcapredict', apikey: '{$SYSTEM_SETTINGS["Credentials"]['PCAPredict']['APIKeys']['DDI']}' }); }"
            );  
            break;
        case 'mybilling':
            Block(array('margin' => TRUE), 8);
            BlockTitle(array('id' => 'finMainTitle', 'caption' => 'My Billing'), 9);
            Article('intro_mytransactions', $NUCLEUS);
            $datasource = array();
            foreach(array('startdate', 'enddate') AS $key) {
                if(!empty($_GET[$key])) {
                    $datasource[$key] = ValidDateStr($_GET[$key]);
                }
            }
            $fieldsets = array(array('fields' => array(
                array('id' => 'startdate', 'name' => 'startdate', 'hint' => 'Select the start date for the invoice history to view.', 'type' => 'date', 'kind' => 'control', 'placeholder' => 'From'),
                array('id' => 'enddate', 'name' => 'enddate', 'hint' => 'Optional. Select the end date for the invoice history to view.', 'type' => 'date', 'kind' => 'control', 'placeholder' => 'Until')
            )));
            $buttons = array(array(
                'type' => 'button', 'id' => 'frmSelectRange:btnupdate', 'colour' => 'success', 'icon' => 'fa-caret-right', 'iconalign' => 'left', 'caption' => 'Update View',
                'script' => "var startdate = $('#frmSelectRange input[name=startdate]').val(); var enddate = $('#frmSelectRange input[name=enddate]').val(); LoadContent('finMainContent', '/load.php?do=invoicelist', { spinner: true, urlparams: { startdate: startdate, enddate: enddate } });"
            ));
            $formitem = array(
                'id' => 'frmSelectRange',
                'style' => 'inline', 'datasource' => $datasource, 'buttons' => $buttons,
                'fieldsets' => $fieldsets, 'borders' => FALSE
            );
            Form($formitem, 9);
            Div(array('id' => 'finMainContent'), 9);
            Div(null, 9);
            Block(null, 8);
            break;
        case 'ddiwizard':
            Block(array('margin' => TRUE), 8);
            
            Block(null, 8);
            break;
        case 'pay':
            $invoice = $NUCLEUS->GetData('getinvoice', array('InvoiceID' => intval($_GET['InvoiceID'])));
            if(!empty($invoice)) {
                foreach(array('InvoiceID', 'InvoiceNo', 'CustNo', 'CustomerRef') AS $key) {
                    $datasource[$key] = $invoice['Invoice'][$key];
                }
                foreach(array('Net', 'VAT', 'Total', 'Outstanding') AS $key) {
                    $datasource[$key.'Txt'] = ScaledIntegerAsString($invoice['Invoice'][$key], "money", 100, TRUE, $invoice['Invoice']['Symbol']);
                }
                $datasource['AllocatedAmountTxt'] = ScaledIntegerAsString(-$invoice['Invoice']['AllocatedAmount'], "money", 100, TRUE, $invoice['Invoice']['Symbol']);
                $datasource['InvoiceDateTxt'] = date('j F Y', strtotime($invoice['Invoice']['InvoiceDate'].' UTC'));
                $datasource['StatusTxt'] = (empty($invoice['Invoice']['InvoiceNo']) ? "Open" : (($invoice['Invoice']['InvoiceType'] == 'creditnote') ? 'Settled' : ($invoice['Invoice']['Outstanding'] == 0 ? 'Paid' : 'Open')));
                Block(array('margin' => TRUE), 8);
                BlockTitle(array('id' => 'finMainTitle', 'caption' => 'Online Payment'), 9);
                Div(array('id' => 'finMainContent'), 9);
                $fields = array(
                    array('name' => 'InvoiceID', 'kind' => 'hidden'),
                );
                if(($invoice['Invoice']['InvoiceType'] == 'invoice') && empty($invoice['Invoice']['Settled'])) {
                    $isdue = (time() > strtotime($invoice['Invoice']['InvoiceDue'].' UTC') ? TRUE : FALSE);
                    $fmt = ($isdue ? "<info>" : "<danger>");
                    $fields = array_merge($fields, array(
                        array('name' => 'InvoiceDateTxt', 'caption' => 'Invoice Date', 'type' => 'string', 'kind' => 'static'),
                        array('name' => 'StatusTxt', 'caption' => 'Status', 'type' => 'string', 'kind' => 'static', 'formatting' => "{$fmt}<b>"),
                        array('name' => 'CustNo', 'caption' => 'Customer No.', 'type' => 'string', 'kind' => 'static'),
                        array('name' => 'CustomerRef', 'caption' => 'Customer Ref.', 'type' => 'string', 'kind' => 'static'),
                        array('name' => 'NetTxt', 'caption' => 'Net', 'type' => 'string', 'kind' => 'static', 'formatting' => $fmt),
                        array('name' => 'VATTxt', 'caption' => 'VAT', 'type' => 'string', 'kind' => 'static', 'formatting' => $fmt),
                        array('name' => 'TotalTxt', 'caption' => 'TOTAL DUE', 'type' => 'string', 'kind' => 'static', 'formatting' => "{$fmt}<b>"),
                    ));
                    if($invoice['Invoice']['AllocatedAmount'] <> 0) {
                        $fields = array_merge($fields, array(
                            array('name' => 'AllocatedAmountTxt', 'caption' => 'Received', 'type' => 'string', 'kind' => 'static', 'formatting' => "<info>"),
                            array('name' => 'OutstandingTxt', 'caption' => 'Remainder', 'type' => 'string', 'kind' => 'static', 'formatting' => "{$fmt}<b>"),
                        )); 
                    }
                    $fieldsets = array(array('caption' => $invoice['Invoice']['InvoiceCaption'], 'icon' => 'fa-angle-right', 'iconalign' => 'left', 'fields' => $fields)); 
                    $formitem = array(
                        'id' => 'frmInvoice', 'style' => 'standard',
                        'datasource' => $datasource,
                        'buttons' => array(),
                        'fieldsets' => $fieldsets, 'borders' => TRUE
                    );
                    Form($formitem);
                    $fields = array();
                    if($SYSTEM_SETTINGS['Finance']['TestMode']) {
                        $fields[] = array('name' => 'testMode', 'kind' => 'hidden');
                        $datasource['testMode'] = 100;
                        $datasource['name'] = 'AUTHORISED';
                    } else {
                        $datasource['name'] = $invoice['Invoice']['Fullname'];
                    }
                    $fields[] = array('name' => 'name', 'kind' => 'hidden');
                    $fields[] = array('name' => 'instId', 'kind' => 'hidden');
                    $datasource['instId'] = $SYSTEM_SETTINGS['Finance']['WorldPay']['instId'];
                    if(!empty($SYSTEM_SETTINGS['Finance']['WorldPay']['accId'])) {
                        if(stripos($SYSTEM_SETTINGS['Finance']['WorldPay']['accId'], "=") !== FALSE) {
                            $lr = LeftRight($SYSTEM_SETTINGS['Finance']['WorldPay']['accId'], "=");
                            if($lr['success'] && (substr($lr['strings']['left'], 0, 5) == 'accId')) {
                                $fields[] = array('name' => $lr['strings']['left'], 'kind' => 'hidden');
                                $datasource[$lr['strings']['left']] = $lr['strings']['right'];
                            }
                        } else {
                            //No =, so signal as accId1
                            $fields[] = array('name' => 'accId1', 'kind' => 'hidden');
                            $datasource['accId1'] = $SYSTEM_SETTINGS['Finance']['WorldPay']['accId'];
                        }
                    }
                    $fields[] = array('name' => 'MC_callback', 'kind' => 'hidden');
                    $datasource['MC_callback'] = $SYSTEM_SETTINGS['Finance']['WorldPay']['paymentResponseURL'];
                    $fields[] = array('name' => 'cartId', 'kind' => 'hidden');
                    $datasource['cartId'] = $invoice['Invoice']['InvoiceID'];
                    $fields[] = array('name' => 'desc', 'kind' => 'hidden');
                    $datasource['desc'] = $invoice['Invoice']['InvoiceCaption'];
                    $fields[] = array('name' => 'amount', 'kind' => 'hidden');
                    $datasource['amount'] = strval(round($invoice['Invoice']['Outstanding']/100, 2));
                    $fields[] = array('name' => 'currency', 'kind' => 'hidden');
                    $datasource['currency'] = $invoice['Invoice']['ISO4217'];
                    $fields[] = array('name' => 'hideCurrency', 'kind' => 'hidden');
                    $datasource['hideCurrency'] = 1;
                    $str = Decrypt($SYSTEM_SETTINGS["Finance"]["WorldPay"]["MD5Secret"]);
                    foreach($SYSTEM_SETTINGS["Finance"]["WorldPay"]["SigningFields"] AS $field) {
                        $str .= ':'.$datasource[$field];
                    }
                    $md5 = md5($str, FALSE);
                    $fields[] = array('name' => 'signature', 'kind' => 'hidden');
                    $datasource['signature'] = $md5;
                    if(!empty($SYSTEM_SETTINGS["Finance"]["TransactionTimeout"])){
                        $validTo = time() + (intval(max($SYSTEM_SETTINGS["Finance"]["TransactionTimeout"], 1))*60);
                        $fields[] = array('name' => 'authValidTo', 'kind' => 'hidden');
                        $datasource['authValidTo'] = $validTo*1000;
                    }
                    $fieldsets = array(array('fields' => $fields));
                    $formitem = array(
                        'id' => 'frmWorldPay', 'style' => 'standard', 'action' => $SYSTEM_SETTINGS['Finance']['WorldPay']['purchaseURL'],
                        'datasource' => $datasource,
                        'buttons' => array(array('type' => 'submit', 'id' => 'btnmakepayment', 'colour' => 'success', 'icon' => 'fa-caret-right', 'iconalign' => 'left', 'caption' => 'Pay online')),
                        'fieldsets' => $fieldsets, 'borders' => TRUE
                    );
                    Form($formitem);
                    Div(array('class' => 'pull-down'), 10);
                    Article('payonline_end', $NUCLEUS);
                    Div(null, 10);
                } else {
                    $fmt = ($invoice['Invoice']['InvoiceType'] == 'invoice' ? "<success>" : "<info>");
                    $fields = array_merge($fields, array(
                        array('name' => 'InvoiceDateTxt', 'caption' => 'Invoice Date', 'type' => 'string', 'kind' => 'static'),
                        array('name' => 'StatusTxt', 'caption' => 'Status', 'type' => 'string', 'kind' => 'static', 'formatting' => "{$fmt}<b>"),
                        array('name' => 'CustNo', 'caption' => 'Customer No.', 'type' => 'string', 'kind' => 'static'),
                        array('name' => 'CustomerRef', 'caption' => 'Customer Ref.', 'type' => 'string', 'kind' => 'static'),
                        array('name' => 'NetTxt', 'caption' => 'Net', 'type' => 'string', 'kind' => 'static', 'formatting' => $fmt),
                        array('name' => 'VATTxt', 'caption' => 'VAT', 'type' => 'string', 'kind' => 'static', 'formatting' => $fmt),
                        array('name' => 'TotalTxt', 'caption' => 'Total', 'type' => 'string', 'kind' => 'static', 'formatting' => "{$fmt}<b>"),
                    ));
                    $fieldsets = array(array('caption' => $invoice['Invoice']['InvoiceCaption'], 'icon' => 'fa-angle-right', 'iconalign' => 'left', 'fields' => $fields)); 
                    $formitem = array(
                        'id' => 'frmInvoice', 'style' => 'standard',
                        'datasource' => $datasource,
                        'buttons' => array(),
                        'fieldsets' => $fieldsets, 'borders' => TRUE
                    );
                    Form($formitem);
                    if(($invoice['Invoice']['InvoiceType'] == 'invoice') && !empty($invoice['Invoice']['Settled'])) {
                        $headingitem = array('caption' => 'Thank you! We have received your payment.', 'level' => 4, 'style' => 'default', 'bold' => TRUE, 'margin' => TRUE, 'colour' => 'success');
                        Heading($headingitem, 10);
                        Para(array('h5' => TRUE), 10);
                        echo LinkTo("Click here to download your invoice.", array('url' => "/load.php?do=invoicepdf&InvoiceID={$invoice['Invoice']['InvoiceID']}", 'target' => 'newwindow'))." If you need to download this document again later, just go to the My Billing section on this website.";
                        Para(null, 10);
                    }
                }
                Div(null, 9);
                Block(null, 8);
            }
            break;
        case 'invoicelist':
            $params = array();
            foreach(array('startdate', 'enddate') AS $key) {
                if(!empty($_GET[$key])) {
                    $params[$key] = ValidDateStr($_GET[$key]);
                }
            }
            $datasource = $NUCLEUS->GetData('listinvoices', $params);
            $invTable = array(
                'header' => !empty($datasource),
                'nodatamsg' => '<i>No items to show</i>',                
                'striped' => TRUE,
                'condensed' => TRUE,
                'borders' => 'none',
                'responsive' => FALSE,
                'valign' => 'centre',
                'margin' => TRUE,
                'columns' => array(
                    1 => array(
                        'field' => array('name' => 'date', 'type' => 'date'),
                        'function' => 'invListItem', 'caption' => 'Date'
                    ),
                    2 => array(
                        'field' => array('name' => 'description', 'type' => 'string'),
                        'function' => 'invListItem', 'caption' => 'Description'
                    ),
                    3 => array(
                        'field' => array('name' => 'amount', 'type' => 'money'),
                        'function' => 'invListItem', 'caption' => 'Amount'
                    ),
                    4 => array(
                        'field' => array('name' => 'status', 'type' => 'string'),
                        'function' => 'invListItem', 'caption' => 'Status'
                    ),
/*                    5 => array(
                        'field' => array('name' => 'reference', 'type' => 'string'),
                        'function' => 'invListItem', 'caption' => 'Reference', 'hide' => array('xs', 'sm')
                    ),*/
                    6 => array(
                        'field' => array('name' => '__actions', 'type' => 'buttons'),
                        'function' => 'invListItem', 'caption' => 'Action'
                    ),
                )
            );
            StaticTable($datasource['Invoices'], $invTable, array('table'), 9);
            break;
        case 'viewinvoice':
            $invoice = $NUCLEUS->GetData('getinvoice', array('InvoiceID' => intval($_GET['InvoiceID'])));
            ModalHeader(!empty($invoice['Invoice']['InvoiceCaption']) ? $invoice['Invoice']['InvoiceCaption'] : 'Document');
            ModalBody(FALSE);
            if(!empty($invoice)) {
                if(($invoice['Invoice']['InvoiceType'] == 'invoice')) {
                    if($invoice['Invoice']['Draft']) {
                        $label = array('caption' => 'This is not a VAT Invoice', 'type' => 'label', 'colour' => 'warning');
                        Label($label, 9);
                    } elseif ($invoice['Invoice']['Settled']) {
                        $label = array('caption' => 'Paid in full', 'type' => 'label', 'colour' => 'success', 'icon' => 'fa-check', 'iconalign' => 'left');
                        Label($label, 9);
                    }
                }
                Div(array('class' => array('row', 'pull-down')), 9);
                Div(array('class' => 'col-xs-12'), 10);
                echo "<b>Invoice Date:</b> ".date('j F Y', strtotime($invoice['Invoice']['InvoiceDate'].' UTC'));
                if(!empty($invoice['Invoice']['CustomerRef'])) {
                    echo "<b>Your Reference:</b> ".htmlspecialchars($invoice['Invoice']['CustomerRef']);
                }
                Div(null, 10);
                Div(null, 9);
                Div(array('class' => 'row'), 9);
                    Div(array('class' => 'table-responsive'), 9); //table container
                    echo str_repeat("\t", 10)."<table class=\"table table-vcenter\">\n";
                    echo str_repeat("\t", 11)."<thead>\n";
                    echo str_repeat("\t", 12)."<tr>\n";
                    echo str_repeat("\t", 13)."<th></th>\n";
                    echo str_repeat("\t", 13)."<th style=\"width: 60%;\">Description</th>\n";
                    echo str_repeat("\t", 13)."<th class=\"text-center\">VAT Rate</th>\n";
                    echo str_repeat("\t", 13)."<th class=\"text-right\">Cost</th>\n";
                    echo str_repeat("\t", 12)."</tr>\n";
                    echo str_repeat("\t", 11)."</thead>\n";
                    echo str_repeat("\t", 11)."<tbody>\n";
                    if(count($invoice['InvoiceItems']) > 0) {
                        foreach($invoice['InvoiceItems'] AS $invoiceitem) {
                            echo str_repeat("\t", 12)."<tr>\n";
                            echo str_repeat("\t", 13)."<td>".$invoiceitem['ItemIndex'].".</td>\n";
                            echo str_repeat("\t", 13)."<td>".FmtText($invoiceitem['Description']);
                            if($invoiceitem['ItemQty'] > 1) {
                                echo ", ".$invoiceitem['ItemQty']."x ".ScaledIntegerAsString($invoiceitem['ItemUnitPrice'], "money", 100, TRUE, $invoiceitem['Symbol']);
                            }
                            if(!empty($invoiceitem['DiscountID'])) {
                                echo ", Discount code ".$invoiceitem['DiscountCode']." (".$invoiceitem['Discount'].")";
                            }                            
                            echo "</td>\n";
                            echo str_repeat("\t", 13)."<td class=\"text-center\">".ScaledIntegerAsString($invoiceitem['ItemVATRate'], "percent", 100, TRUE)."</td>\n";
                            echo str_repeat("\t", 13)."<td class=\"text-right\"><b>".ScaledIntegerAsString($invoiceitem['ItemNet'], "money", 100, FALSE, $invoiceitem['Symbol'])."</b></td>\n";
                            echo str_repeat("\t", 12)."</tr>\n";
                        }
                    } else {
                        echo str_repeat("\t", 12)."<tr>\n";
                        echo str_repeat("\t", 13)."<td colspan=\"4\">".FmtText("<i>no line items</i>")."</td>\n";
                        echo str_repeat("\t", 12)."</tr>\n";
                    }
                    echo str_repeat("\t", 12)."<tr class=\"active\">\n";
                    echo str_repeat("\t", 13)."<td colspan=\"3\" class=\"text-right\"><span class=\"h5\">SUBTOTAL</span></td>\n";
                    echo str_repeat("\t", 13)."<td class=\"text-right\"><span class=\"h5\">".ScaledIntegerAsString($invoice['Invoice']['Net'], "money", 100, FALSE, $invoice['Invoice']['Symbol'])."</span></td>\n";
                    echo str_repeat("\t", 12)."</tr>\n";
                    echo str_repeat("\t", 12)."<tr class=\"active\">\n";
                    echo str_repeat("\t", 13)."<td colspan=\"3\" class=\"text-right\"><span class=\"h5\">VAT".($invoice['Invoice']['InvoiceType'] == 'invoice' ? " DUE" : "")."</span></td>\n";
                    echo str_repeat("\t", 13)."<td class=\"text-right\"><span class=\"h5\">".ScaledIntegerAsString($invoice['Invoice']['VAT'], "money", 100, FALSE, $invoice['Invoice']['Symbol'])."</span></td>\n";
                    echo str_repeat("\t", 12)."</tr>\n";
                    echo str_repeat("\t", 12)."<tr class=\"active\">\n";
                    echo str_repeat("\t", 13)."<td colspan=\"3\" class=\"text-right\"><span class=\"h5\">TOTAL ".($invoice['Invoice']['InvoiceType'] == 'invoice' ? ($invoice['Invoice']['Settled'] ? 'PAID' : 'DUE') : "")."</span></td>\n";
                    echo str_repeat("\t", 13)."<td class=\"text-right\"><span class=\"h5\"><strong>".ScaledIntegerAsString($invoice['Invoice']['Total'], "money", 100, FALSE, $invoice['Invoice']['Symbol'])."</strong></span></td>\n";
                    echo str_repeat("\t", 12)."</tr>\n";
                    if($invoice['Invoice']['AllocatedAmount'] <> 0) {
                        echo str_repeat("\t", 12)."<tr class=\"active\">\n";
                        echo str_repeat("\t", 13)."<td colspan=\"3\" class=\"text-right\"><span class=\"h5\">RECEIVED</span></td>\n";
                        echo str_repeat("\t", 13)."<td class=\"text-right\"><span class=\"h5\">".ScaledIntegerAsString(-$invoice['Invoice']['AllocatedAmount'], "money", 100, FALSE, $invoice['Invoice']['Symbol'])."</span></td>\n";
                        echo str_repeat("\t", 12)."</tr>\n";
                        if($invoice['Invoice']['Outstanding'] <> 0) {
                            echo str_repeat("\t", 12)."<tr class=\"active\">\n";
                            echo str_repeat("\t", 13)."<td colspan=\"3\" class=\"text-right\"><span class=\"h5\">REMAINDER</span></td>\n";
                            echo str_repeat("\t", 13)."<td class=\"text-right\"><span class=\"h5\"><strong>".ScaledIntegerAsString($invoice['Invoice']['Outstanding'], "money", 100, FALSE, $invoice['Invoice']['Symbol'])."</strong></span></td>\n";
                            echo str_repeat("\t", 12)."</tr>\n";
                        }
                    }
                    foreach(array('AddInfo', 'Terms') AS $key) {
                        if(!empty($invoice['Invoice'][$key])) {
                            echo str_repeat("\t", 12)."<tr>\n";
                            echo str_repeat("\t", 13)."<td colspan=\"3\">".FmtText($invoice['Invoice'][$key])."</td>\n";
                            echo str_repeat("\t", 12)."</tr>\n";
                        }
                    }
                    echo str_repeat("\t", 11)."</tbody>\n";
                    echo str_repeat("\t", 10)."</table>\n";
                    Div(null, 9); //table container
                Div(null, 9);
            } else {
                SimpleAlertBox('danger', 'Not found.', 9);
            }
            ModalBody(TRUE);
            ModalFooter(null);                
            break;
        case 'invoicepdf':
            $pdf = $NUCLEUS->GetData('invoicepdf', array('InvoiceID' => intval($_GET['InvoiceID'])));
            if(!empty($pdf['Data'])) {
                $pdf['Data'] = safe_b64decode($pdf['Data']);
                header("Content-Type: {$pdf['MimeType']}");
                header("Content-Length: ".strlen($pdf['Data']));
                header("Content-Disposition: inline; filename=\"{$pdf['Filename']}\"");
                header('Cache-Control: private, max-age=0, must-revalidate');
                header('Pragma: public');        
                print $pdf['Data'];
            } else {
                header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
            }
            die();
            break;
        case 'subscriptions':
            $subscriptions = $NUCLEUS->GetData('getpublications');
            if(!empty($subscriptions)) {
                $table = array(
                    'header' => FALSE,
                    'nodatamsg' => '<i>There are no publications available</i>',
                    'columns' => array(
/*                        1 => array(
                            'field' => array('name' => 'icon', 'type' => 'string'),
                            'function' => 'subsItem', 'caption' => ''
                        ),*/
                        2 => array(
                            'field' => array('name' => 'publication', 'type' => 'string'),
                            'function' => 'subsItem', 'caption' => 'Publication'
                        ),
                        3 => array(
                            'field' => array('name' => 'action', 'type' => 'string'),
                            'function' => 'subsItem', 'caption' => 'Action'
                        ),
                    ),
                );
                $subscribed = array();
                $notsubscribed = array();
                foreach($subscriptions AS $subscription) {
                    if(!empty($subscription['SubscriptionID']) && empty($subscription['OptedOut'])) {
                        $subscribed[] = $subscription;
                    } else {
                        $notsubscribed[] = $subscription;
                    }
                }
                $table['nodatamsg'] = '<i>You are not currently subscribed to any publication</i>';
                SimpleHeading("Your current subscriptions", 4, "default", 9);
                StaticTable($subscribed, $table, array('table'), 9);
                $table['nodatamsg'] = '<i>There are no publications available</i>';
                SimpleHeading((count($subscribed) > 0 ? "Other available" : "Available")." subscriptions", 4, "default", 9);
                StaticTable($notsubscribed, $table, array('table'), 9);
            } else {
                Para(array('well' => 'small'), 9);
                echo str_repeat("\t", 10)."There are no publications available.\n";
                Para(null, 9);
            }
            break;
        case 'settings':
            if (IsAdministrator($NUCLEUS)) {
                $menuitems = array();
                $titleitem = array('caption' => 'Settings', 'glyphbuttons' => $menuitems);
                BlockTitle($titleitem, 8);
                Div(array('class' => 'block-content'), 8);
                $tabitems = array();
                $tabitems['general'] = array('id' => 'general', 'icon' => 'gi-circle_info', 'tooltip' => 'General');
                $tabitems['customise'] = array('id' => 'customise', 'icon' => 'fa-sitemap', 'tooltip' => 'Customise');
                $tabitems['links'] = array('id' => 'links', 'icon' => 'gi-link', 'tooltip' => 'Links');
                $tabitems['finance'] = array('id' => 'finance', 'icon' => 'fa-credit-card', 'tooltip' => 'Finance');
                $tabitems['system'] = array('id' => 'system', 'icon' => 'fa-cogs', 'tooltip' => 'System');
                $tabitems['security'] = array('id' => 'security', 'icon' => 'fa-key', 'tooltip' => 'Security');
                PrepareTabs($tabitems, (!empty($_GET['activetab']) ? $_GET['activetab'] : null));
                Tabs($tabitems, 9);
                Div(array('class' => 'tab-content'), 9);
                $buttons = array();
                $buttons['btnsave'] = array(
                    'type' => 'button',
                    'colour' => 'success',
                    'icon' => 'fa-caret-right',
                    'iconalign' => 'left',
                    'caption' => "Apply Changes",
                    'script' => 'SaveSettings();',
                );
                if (OpenTabContent($tabitems['general'], 10))
                {
                    $fieldsets = array();
                    $fieldsets[] = array('caption' => 'Organisation', 'fields' => array(
                        array('name' => 'General.OrgLongName', 'caption' => 'Organisation', 'kind' => 'control', 'type' => 'string'),
                        array('name' => 'General.OrgSubjectArea', 'caption' => 'Subject Area', 'kind' => 'control', 'type' => 'string', 'asstringfunction' => @strtolower),
                        array('name' => 'General.StrapLine', 'caption' => 'Strapline', 'kind' => 'control', 'type' => 'string'),
                        array('name' => 'General.OrgShortName', 'caption' => 'Abbreviation', 'kind' => 'control', 'type' => 'string', 'size' => 2),
                        array('name' => 'General.Address.Lines', 'caption' => 'Address', 'kind' => 'control', 'type' => 'memo', 'rows' => 4),
                        array('name' => 'General.Address.Postcode', 'caption' => 'Postcode', 'kind' => 'control', 'type' => 'string', 'size' => 3),
                        array('name' => 'General.Address.Town', 'caption' => 'Town', 'kind' => 'control', 'type' => 'string', 'size' => 6),
                        array('name' => 'General.Address.County', 'caption' => 'County', 'kind' => 'control', 'type' => 'string', 'size' => 6),
                        array('name' => 'General.Address.Region', 'caption' => 'Region', 'kind' => 'control', 'type' => 'string', 'size' => 6),
                        array('name' => 'General.Address.CountryCode', 'caption' => 'CountryCode', 'kind' => 'control', 'type' => 'string', 'size' => 2, 'asstringfunction' => @strtoupper),
                        array('name' => 'General.Address.Country', 'caption' => 'Country', 'kind' => 'control', 'type' => 'string', 'size' => 6),
                        array('name' => 'General.Website', 'caption' => 'Website', 'kind' => 'control', 'type' => 'url'),
                    ));
                    $buttons['btnsave']['script'] = "SaveSettings('frmGeneral');";
                    $formitem = array(
                        'id' => 'frmGeneral', 'style' => 'standard', 
                        'datasource' => $SYSTEM_SETTINGS, 'buttons' => $buttons,
                        'fieldsets' => $fieldsets, 'borders' => TRUE
                    );
                    Form($formitem);
                    jsFormValidation('frmGeneral');
                    CloseTabContent($tabitems['general']);                    
                }
                if (OpenTabContent($tabitems['customise'], 10))
                {
/*                    $dtBtnGroup = array();
                    $dtBtnGroup[] = array(
                        'icon' => 'gi-link', 'iconalign' => 'left', 'caption' => 'Manage Links', 'tooltip' => 'Manage additional links',
                        'script' => "OpenDialog('EditLinkList', {}, true, function() { LoadData('divLinkList', 'LinkTable', null, true ); } )",
                        'type' => 'button', 'colour' => 'primary'
                    );
                    ButtonGroup($dtBtnGroup, FALSE, null, 11, FALSE);*/
                    $fieldsets = array();
                    $fieldsets[] = array('caption' => 'General', 'fields' => array(
                        array('name' => 'Customise.HeaderMedia', 'caption' => 'Main Banner', 'kind' => 'control', 'type' => 'string'),
                        array('name' => 'General.StyleSheet', 'caption' => 'Stylesheet', 'kind' => 'control', 'type' => 'string', 'size' => 3),
                        array('name' => 'Customise.AnimatedHeader', 'kind' => 'control', 'type' => 'switch', 'tooltip' => 'Header animation',
                              'hint' => 'Animate the header image'
                        ),
                        array('name' => 'Customise.DisabledButtonColour', 'kind' => 'control', 'type' => 'switch', 'tooltip' => 'Disabled Buttons',
                              'hint' => 'Visual indication of disabled buttons'
                        ),
                        array('name' => 'Customise.UseShortOrgName', 'kind' => 'control', 'type' => 'switch', 'tooltip' => 'Use Abbreviation',
                              'hint' => 'Use the Organisation Abbreviation'
                        ),
                    ));
                    $fieldsets[] = array('caption' => 'Sidebar', 'fields' => array(
                        array('name' => 'Customise.SidebarLogo', 'kind' => 'control', 'type' => 'switch', 'tooltip' => 'Show logo in the sidebar'),
                        array('name' => 'Customise.Logos.Sidebar', 'caption' => 'Logo', 'kind' => 'control', 'type' => 'string'),
                        array('name' => 'Customise.Navigation.Shortcuts.Website.Enable', 'kind' => 'control', 'type' => 'switch', 'tooltip' => 'Website shortcut',
                              'hint' => 'Show website shortcut in sidebar'
                        ),
                        array('name' => 'Customise.Navigation.Shortcuts.Website.Caption', 'caption' => 'Site Shortcut Caption', 'kind' => 'control', 'type' => 'string', 'size' => 4),
                        array('name' => 'Customise.Navigation.Shortcuts.Join', 'kind' => 'control', 'type' => 'switch', 'tooltip' => 'Join Us shortcut',
                              'hint' => 'Join Us shortcut in sidebar'
                        ),
                    ));
/*                    $fieldsets[] = array('caption' => 'Events', 'fields' => array(
                        array('name' => 'Customise.Menu.Events', 'kind' => 'control', 'type' => 'switch', 'tooltip' => 'Events Module',
                              'hint' => 'Enable Events Module'
                        ),
                        array('name' => 'Customise.Events.UpcomingInterval', 'caption' => 'Interval', 'kind' => 'control', 'type' => 'integer', 'size' => 3,
                              'hint' => 'Interval for upcoming events as number of <b>months</b>'
                        ),
                        array('name' => 'Customise.Events.UpcomingMax', 'caption' => 'List Size', 'kind' => 'control', 'type' => 'integer', 'size' => 3,
                              'hint' => 'Maximum number of upcoming events to display in listing'
                        ),
                        array('name' => 'Customise.Events.FutureOnly', 'kind' => 'control', 'type' => 'switch', 'tooltip' => 'Future Events only',
                              'hint' => 'Only show future events'
                        ),
                        
                    ));*/
                    $fieldsets[] = array('caption' => 'Membership', 'fields' => array(
                        array('name' => 'Customise.Menu.Membership.Branches', 'kind' => 'control', 'type' => 'switch', 'tooltip' => 'Use Branches',
                              'hint' => 'Enable Branches Module'
                        ),
                        array('name' => 'Customise.Menu.Membership.Directory', 'kind' => 'control', 'type' => 'switch', 'tooltip' => 'Enable Directory',
                              'hint' => 'Enable Membership Directory'
                        ),
                        array('name' => 'Customise.Membership.SearchRequired', 'kind' => 'control', 'type' => 'switch', 'tooltip' => 'Search only',
                              'hint' => 'Do not display the full directory, only allow searches'
                        ),
                        array('name' => 'Customise.Membership.MinSearchLength', 'kind' => 'control', 'caption' => 'Min. search length', 'type' => 'integer', 
                              'hint' => 'The minimum length of the search term.'),
/*                        array('name' => 'Customise.Applications.Membership.AllowLife', 'kind' => 'control', 'type' => 'switch', 'tooltip' => 'Allow Life Applications',
                              'hint' => 'Allow Life Applications'
                        ),
                        array('name' => 'Customise.Applications.Membership.LifeHint', 'kind' => 'control', 'caption' => 'Life hint text', 'type' => 'string'),
                        array('name' => 'Customise.Applications.Membership.AllowStudent', 'kind' => 'control', 'type' => 'switch', 'tooltip' => 'Allow Student Category Applications',
                              'hint' => 'Allow Student Category Applications'
                        ),
                        array('name' => 'Customise.Applications.Membership.StudentHint', 'kind' => 'control', 'caption' => 'Student hint text', 'type' => 'string'),
                        array('name' => 'Customise.Applications.Membership.AllowRetired', 'kind' => 'control', 'type' => 'switch', 'tooltip' => 'Allow Retired Category Applications',
                              'hint' => 'Allow Retired Category Applications'
                        ),
                        array('name' => 'Customise.Applications.Membership.RetiredHint', 'kind' => 'control', 'caption' => 'Retired hint text', 'type' => 'string'),*/
                        array('name' => 'Customise.Applications.Membership.WhereDidYouHear', 'kind' => 'control', 'type' => 'switch', 'tooltip' => "Show 'Where did you hear...' field",
                              'hint' => "Show 'Where did you hear...' field"
                        ),
                        array('name' => 'Customise.Membership.GradeCaption', 'caption' => 'Grade Caption', 'kind' => 'control', 'type' => 'string', 'size' => 3),
                        array('name' => 'Customise.Applications.Membership.DefaultGrade', 'caption' => 'Default Grade', 'kind' => 'control', 'type' => 'integer', 'size' => 3,
                              'hint' => 'Default grade code value for new applications'
                        ),
                        array('name' => 'Customise.Membership.JoinUs', 'kind' => 'control', 'caption' => 'Join Us', 'type' => 'url',
                              'hint' => 'Target for the Join Us link on the homepage for non-authenticated users'
                        ),
                        
                    ));
/*                    $fieldsets[] = array('caption' => 'CPD', 'fields' => array(
                        array('name' => 'Customise.Menu.CPD', 'kind' => 'control', 'type' => 'switch', 'tooltip' => 'CPD Module',
                              'hint' => 'Enable CPD Module'
                        ),
                    ));*/
                    $fieldsets[] = array('caption' => 'Publications', 'fields' => array(
                        array('name' => 'Customise.Menu.Services.Subscriptions', 'kind' => 'control', 'type' => 'switch', 'tooltip' => 'Enable Subscriptions',
                              'hint' => 'Enable Subscriptions Module'
                        ),
                        array('name' => 'Customise.Publications.ShowExpiry', 'kind' => 'control', 'type' => 'switch', 'tooltip' => 'Show Expiry',
                              'hint' => 'Show expiry details for a subscription'
                        ),
                    ));
                    $buttons['btnsave']['script'] = "SaveSettings('frmCustomise');";
                    $formitem = array(
                        'id' => 'frmCustomise', 'style' => 'standard', 
                        'datasource' => $SYSTEM_SETTINGS, 'buttons' => $buttons,
                        'fieldsets' => $fieldsets, 'borders' => TRUE
                    );
                    Form($formitem);
                    jsFormValidation('frmCustomise');
                    CloseTabContent($tabitems['customise']);
                }
                if (OpenTabContent($tabitems['links'], 10))
                {
                    Div(array('id' => 'divLinkList'), 8);
                    Div(null, 8);
                    echo str_repeat("\t", 8)."<script type=\"text/javascript\">\n";
                    echo str_repeat("\t", 9)."jQuery(function($) {\n";
                    echo str_repeat("\t", 10)."LoadData('divLinkList', 'LinkTable', null, true );\n";
                    echo str_repeat("\t", 9)."});\n";    
                    echo str_repeat("\t", 8)."</script>\n";
                    CloseTabContent($tabitems['links']);
                }
                if (OpenTabContent($tabitems['finance'], 10))
                {
                    $fieldsets = array();
                    $fieldsets[] = array('caption' => 'General', 'fields' => array(
                        array('name' => 'Customise.Menu.DirectDebit', 'kind' => 'control', 'type' => 'switch', 'tooltip' => 'Enable support for Direct Debit',
                              'hint' => 'Enable Direct Debit'),
                        array('name' => 'Finance.DDMSOnly', 'kind' => 'control', 'type' => 'switch', 'tooltip' => 'Allow Direct Debit for Membership only',
                              'hint' => 'Membership only Direct Debit'),
                        array('name' => 'Finance.AllowDonations', 'kind' => 'control', 'type' => 'switch', 'tooltip' => 'Enable donations',
                              'hint' => 'Enable donations'),
                        array('name' => 'Finance.TestMode', 'kind' => 'control', 'colour' => 'danger', 'type' => 'switch', 'tooltip' => 'Test Mode',
                              'hint' => 'Test Mode'),
                        array('name' => 'Finance.TransactionTimeout', 'kind' => 'control', 'caption' => 'Transaction Timeout', 'type' => 'integer', 
                              'hint' => 'The number of <b>minutes</b> a generated online transaction remains valid for. Set to zero to disable.'),
                        array('name' => 'Finance.LogFile', 'caption' => 'Response Log File', 'kind' => 'control', 'type' => 'string'),
                    ));
                    $fieldsets[] = array('caption' => 'WorldPay', 'fields' => array(
                        array('name' => 'Finance.WorldPay.instId', 'kind' => 'control', 'caption' => 'Installation ID', 'type' => 'string'),
                        array('name' => 'Finance.WorldPay.accId', 'kind' => 'control', 'caption' => 'Merchant Code', 'type' => 'string', 'hint' => 'Leave empty, enter any string for use as accId1 or use the format accIdn=string'),
                        array('name' => 'Finance.WorldPay.purchaseURL', 'kind' => 'control', 'caption' => 'Purchase URL', 'type' => 'url'),
                        array('name' => 'Finance.WorldPay.paymentResponseURL', 'kind' => 'control', 'caption' => 'Payment Response URL', 'type' => 'url', 'hint' => 'If this is predefined in the WorldPay portal, leave this empty'),
                        array('name' => 'Finance.WorldPay.MD5Secret', 'kind' => 'control', 'caption' => 'MD5 Secret', 'type' => 'password', 'encrypted' => TRUE, 'hint' => 'This string must be 20 to 30 characters'),
                        array('name' => '__SigningFields', 'kind' => 'control', 'caption' => 'Hash fields', 'type' => 'string', 'hint' => 'Allowed fields are instId, cartId, amount, currency'),
                        array('name' => 'Finance.WorldPay.callbackPW', 'kind' => 'control', 'caption' => 'Callback Password', 'type' => 'password', 'encrypted' => TRUE),
                    ));
                    $buttons['btnsave']['script'] = "SaveSettings('frmFinance');";
                    $formitem = array(
                        'id' => 'frmFinance', 'style' => 'standard', 
                        'datasource' => array_merge($SYSTEM_SETTINGS, array('__SigningFields' => implode(',', $SYSTEM_SETTINGS['Finance']['WorldPay']['SigningFields']))), 'buttons' => $buttons,
                        'fieldsets' => $fieldsets, 'borders' => TRUE
                    );
                    Form($formitem);
                    jsFormValidation('frmFinance');
                    CloseTabContent($tabitems['general']);   
                }
                if (OpenTabContent($tabitems['system'], 10)) {
                    $fieldsets = array();
                    $fieldsets[] = array('caption' => 'System', 'fields' => array(
                        array('name' => 'System.Timezone', 'caption' => 'Timezone', 'kind' => 'control', 'type' => 'string', 'size' => 3),
                        array('name' => 'General.PortalName', 'caption' => 'Name', 'kind' => 'control', 'type' => 'string', 'size' => 3),
                        array('name' => 'System.LogFile', 'caption' => 'API Log File', 'kind' => 'control', 'type' => 'string'),
                        array('name' => 'System.LogVerbosity', 'caption' => 'Log Verbosity', 'kind' => 'control', 'type' => 'integer', 'size' => 3),
                        array('name' => 'System.DebugMode', 'kind' => 'control', 'type' => 'switch', 'tooltip' => 'Debug Mode',
                              'hint' => 'Enable Debug Mode'
                        ),
                    ));
                    $fieldsets[] = array('caption' => 'Database', 'fields' => array(
                        array('name' => 'System.DB.Host', 'caption' => 'Host', 'kind' => 'control', 'type' => 'string', 'size' => 3),
                        array('name' => 'System.DB.Port', 'caption' => 'Port', 'kind' => 'control', 'type' => 'integer', 'size' => 3),
                        array('name' => 'System.DB.Schema', 'caption' => 'Schema', 'kind' => 'control', 'type' => 'string', 'size' => 3),
                        array('name' => 'System.DB.Username', 'caption' => 'Username', 'kind' => 'control', 'type' => 'string', 'size' => 3),
                        array('name' => 'System.DB.Password', 'caption' => 'Password', 'kind' => 'control', 'type' => 'password', 'size' => 6, 'encrypted' => TRUE),
                    ));
                    $fieldsets[] = array('caption' => 'Nucleus', 'fields' => array(
                        array('name' => 'System.Nucleus.URL', 'caption' => 'URL', 'kind' => 'control', 'type' => 'url'),
                        array('name' => 'System.Nucleus.AccessKey', 'caption' => 'Access Key', 'kind' => 'control', 'type' => 'string', 'encrypted' => TRUE),
                    ));
                    $buttons['btnsave']['script'] = "SaveSettings('frmSystem');";
                    $formitem = array(
                        'id' => 'frmSystem', 'style' => 'standard', 
                        'datasource' => $SYSTEM_SETTINGS, 'buttons' => $buttons,
                        'fieldsets' => $fieldsets, 'borders' => TRUE
                    );
                    Form($formitem);
                    jsFormValidation('frmSystem');
                    CloseTabContent($tabitems['system']);                    
                }
                if (OpenTabContent($tabitems['security'], 10))
                {
                    $fieldsets = array();
                    $fieldsets[] = array('caption' => 'Settings', 'fields' => array(
                        array('name' => 'Security.EncryptionKey', 'caption' => 'Encryption Key', 'kind' => 'control', 'type' => 'string'),
                        array('name' => 'Security.AllowPasswordChange', 'kind' => 'control', 'type' => 'switch', 'tooltip' => 'Allow Pasword Change',
                              'hint' => 'Allow Pasword Change'
                        ),
                    ));
                    $buttons['btnsave']['script'] = "SaveSettings('frmSecurity');";
                    $formitem = array(
                        'id' => 'frmSecurity', 'style' => 'standard', 
                        'datasource' => $SYSTEM_SETTINGS, 'buttons' => $buttons,
                        'fieldsets' => $fieldsets, 'borders' => TRUE
                    );
                    Form($formitem);
                    jsFormValidation('frmSecurity');
                    CloseTabContent($tabitems['security']);                    
                }
                Div(null, 9);
                Div(null, 8);
            }        
            break;
        default:
            $alert['items']['error']['caption'] = 'Unable to execute: invalid command';
            AlertBox($alert, $tabs);
    }
}
else
{
    $alert['items']['error']['caption'] = 'Unable to execute: missing command';
    AlertBox($alert, $tabs);
}
die();

function collTableItem($table, $data, $column, $isheader, $sourceindex) {
    global $NUCLEUS;
    $Result = '';
    $section = $table['section'];
    $sectionname = $table['sectionname'];
    //file_put_contents("D:\\temp\\section.txt", print_r($section, TRUE));
    //file_put_contents("D:\\temp\\data.txt", print_r($data, TRUE));
    if($isheader) {
        $Result = (isset($column['caption']) ? $column['caption'] : "");
    } else {
        switch($column['field']['name'])
        {
            case 'CollectionItem':
                $find = (!empty($section['selector']) && !empty($section['collectionname']) ? $section['collectionname'] : $sectionname);
                switch($find) {
                    case 'address':
                        $Result = "<span class=\"text-info\"><em>".(!empty($data['Title']) ? $data['Title'] : ucfirst($data['AddressType']))."</em></span><br>".AddressToMemo($data);
                        break;
                    case 'email':
                        if(IsValidEmailAddress($data['Email'])) {
                            $colour = 'success';
                            $icon = 'fa-check-circle';
                            $tooltip = 'This is a valid email address';
                        } else {
                            $colour = 'danger';
                            $icon = 'fa-exclamation-triangle';
                            $tooltip = 'This email address is not valid!';
                        }
                        $Result = AdvIcon(array('icon' => $icon, 'colour' => $colour, 'tooltip' => $tooltip, 'ttplacement' => 'top', 'fixedwidth' => TRUE))
                                . "&#8200;<a href=\"mailto:{$data['Email']}\" class=\"text-{$colour}\">".htmlspecialchars($data['Email'])."</a>";
                        break;
                    case 'phone':
                        $Result = AdvIcon(array('icon' => $data['Icon'], 'tooltip' => $data['PhoneType'], 'ttplacement' => 'top', 'fixedwidth' => TRUE))
                                . "&#8200;".htmlspecialchars($data['PhoneNo'])."";
                        break;
                    case 'online':
                        $Result = AdvIcon(array('icon' => $data['CategoryIcon'], 'tooltip' => $data['CategoryName'], 'ttplacement' => 'top', 'fixedwidth' => TRUE))
                                . "&#8200;<a href=\"{$data['URL']}\" target=\"_blank\">".htmlspecialchars($data['URL'])."</a>";
                        break;
                }
                break;
            case 'CollectionActions':
                $sectionstr = http_build_query($section, null, '&', PHP_QUERY_RFC3986);
                $post = OutputArrayAsJSObject(array_merge(array('PersonID' => $NUCLEUS->CurrentUser->PersonID, '_section' => $sectionstr, 'ApplicationID' => (isset($section['applicationid']) ? $section['applicationid'] : null)), $data), TypeHintFromFields($section['fields']));
                $sizestr = (!empty($section['dialog']['large']) ? "true" : "false");
                if(!empty($section['selector'])) {
                    $cbsuccess = "function(){ LoadAppPage('{$section['selector']}', '{$sectionname}'); }";
                } else {
                    $cbsuccess = "function(){ LoadContent('tab-{$sectionname}', '/load.php?do=loadcollection', { spinner: false, urlparams: { sectionname: '{$sectionname}', section: '{$sectionstr}' } }); }";
                }
                $dtBtnGroup = array(
                    array(
                        'icon' => 'fa-pencil', 'type' => 'button', 'colour' => 'info', 'tooltip' => 'Edit this item',
                        'script' => "OpenDialog('editcollectionitem', { large: {$sizestr}, urlparams: { sectionname: '{$sectionname}', section: '{$sectionstr}' }, postparams: {$post} })",
                    ),
                    array(
                        'icon' => 'fa-times', 'type' => 'button', 'colour' => 'danger', 'tooltip' => 'Delete this item',
                        'script' => "confirmExecSyscall('Delete', 'Are you sure you want to delete this item?', '/syscall.php?do=delcollectionitem', { parseJSON: true, defErrorDlg: true, postparams: {$post}, cbSuccess: {$cbsuccess} })",
                        'disabled' => (!empty($section['required']) && ($data['__COUNT__'] < 2))
                    ),
                );
                switch($sectionname) {
                    case 'address':
                        break;
                }
                if(!empty($dtBtnGroup)) {
                    $Result = ButtonGroup($dtBtnGroup, FALSE, null, 10, TRUE);
                }
                break;
        }        
    }
    return $Result;
}

function AddressToMemo($source, $name = null) {
    $Result = (!empty($name) ? $name : "");
    if(!empty($source)) {
        $lines = trim($source['Lines']);
        if(!empty($lines)) {
            $Result .= (empty($Result) ? "" : "<br>\n").str_replace("\n", "<br>\n", $lines);
        }
        if(!isset($source['PostcodeDisplay'])) {
            $source['PostcodeDisplay'] = 'uk';
        }
        switch($source['PostcodeDisplay']) {
            case 'beforetown':
                $str = trim($source['Postcode'].' '.$source['Town']);
                if(!empty($str)) {
                    $Result .= (empty($Result) ? "" : "<br>\n").$str;
                }
                foreach(array('County', 'Region', 'Country') AS $field) {
                    $str = trim($source[$field]);
                    if(!empty($str)) {
                        $Result .= (empty($Result) ? "" : "<br>\n").$str;
                    }
                }
                break;
            case 'aftertown':
                $str = trim($source['Town'].' '.$source['Postcode']);
                if(!empty($str)) {
                    $Result .= (empty($Result) ? "" : "<br>\n").$str;
                }
                foreach(array('County', 'Region', 'Country') AS $field) {
                    $str = trim($source[$field]);
                    if(!empty($str)) {
                        $Result .= (empty($Result) ? "" : "<br>\n").$str;
                    }
                }
                break;
            case 'linebeforetown':
                foreach(array('Postcode', 'Town', 'County', 'Region', 'Country') AS $field) {
                    $str = trim($source[$field]);
                    if(!empty($str)) {
                        $Result .= (empty($Result) ? "" : "<br>\n").$str;
                    }
                }
                break;
            case 'lineaftertown':
                foreach(array('Town', 'Postcode', 'County', 'Region', 'Country') AS $field) {
                    $str = trim($source[$field]);
                    if(!empty($str)) {
                        $Result .= (empty($Result) ? "" : "<br>\n").$str;
                    }
                }
                break;
            case 'uk':
            default:
                foreach(array('Town', 'County', 'Region', 'Postcode') AS $field) {
                    $str = trim($source[$field]);
                    if(!empty($str)) {
                        $Result .= (empty($Result) ? "" : "<br>\n").$str;
                    }
                }
                break;
        }        
    }
    return $Result;
}

function subsItem($table, $data, $column, $isheader, $sourceindex)
{
    global $SYSTEM_SETTINGS, $NUCLEUS;
    $Result = '';
    if($isheader) {
        $Result = (isset($column['caption']) ? $column['caption'] : "");
    } else {
        switch($column['field']['name']) {
            case 'icon':
//                $Result = AdvIcon(array('icon' => $icon, 'tooltip' => ucfirst($data['PublicationType']), 'ttplacement' => 'right', 'colour' => ($data['PublicationScope'] == 'public' ? 'success' : 'primary'), 'fixedwidth' => TRUE));
                break;
            case 'publication':
                $info = array();
                if(!empty($data['SubscriptionID'])) {
                    if(!empty($data['Suspended'])) {
                        $info[] = 'Suspended';
                    }
                    if(!empty($data['Complimentary'])) {
                        $info[] = 'Complimentary';
                    }
                    if(!empty($data['EndDate'])) {
                        $info[] = 'Expires '.date('j F Y', strtotime($data['EndDate'].' UTC'));
                    }
                };
                switch($data['PublicationType']) {
                    case 'email':
                        $icon = 'fa-at';
                        break;
                    case 'online':
                        $icon = 'fa-desktop';
                        break;
                    case 'paper':
                        $icon = 'fa-envelope-o';
                        break;
                    case 'sms':
                        $icon = 'gi-iphone';
                        break;
                    default:
                        $icon = 'fa-info';
                        break;
                }
                $Result =
                    AdvIcon(array('icon' => $icon, 'tooltip' => ucfirst($data['PublicationType']), 'ttplacement' => 'right', 'colour' => 'primary', 'fixedwidth' => TRUE))
                    .'&#8200;'                
                    .FmtText(
                        "<primary><b>".$data['Title']."</b></primary>"
                       .(count($info) > 0 ? " (".implode(", ", $info).")" : "")
                       .(!empty($data['OptedOut']) ? ", <warning>Opted out</warning>": "")
                       .(!empty($data['Description']) ? "<br><small>".$data['Description']."</small>" : "")
                );
                break;
            case 'action':
                if($data['CanUnsubscribe']) {
                    $Result = LinkTo("<b>Unsubscribe</b>", array('script' => "execSyscall('/syscall.php?do=unsubscribe', { parseJSON: true, defErrorDlg: true, postparams: { PublicationID: {$data['PublicationID']} }, cbSuccess: function(){ LoadContent('MySubscriptions', '/load.php?do=subscriptions', { spinner: true, urlparams: { } }); } });"));
                } elseif($data['CanSubscribe']) {
                    $Result = LinkTo("<b>Subscribe</b>", array('script' => "execSyscall('/syscall.php?do=subscribe', { parseJSON: true, defErrorDlg: true, postparams: { PublicationID: {$data['PublicationID']} }, cbSuccess: function(){ LoadContent('MySubscriptions', '/load.php?do=subscriptions', { spinner: true, urlparams: { } }); } });"));
                }
                break;
        }
    }
    return $Result;
}

function invListItem($table, $data, $column, $isheader, $sourceindex)
{
    global $SYSTEM_SETTINGS, $NUCLEUS;
    $Result = '';
    if($isheader) {
        $Result = (isset($column['caption']) ? $column['caption'] : "");
    } else {
        switch($column['field']['name'])
        {
            case 'date':
                $Result = date('Y-m-d', strtotime($data['InvoiceDate'].' UTC'));
                break;
            case 'description':
                $fmt = "<{$data['StatusColour']}><b>";
                $Result = LinkTo($fmt.$data['InvoiceCaption'].CloseFormattingString($fmt), array('script' => "OpenDialog('viewinvoice', { large: true, urlparams: { InvoiceID: {$data['InvoiceID']} } });")); 
                break;
            case 'amount':
                $Result = FmtText("<{$data['StatusColour']}><b>".ScaledIntegerAsString($data['Total'], "money", 100, FALSE, $data['Symbol'])."</b></{$data['StatusColour']}>");
                break;
            case 'status':
                $fmt = "<{$data['StatusColour']}>";
                $Result = FmtText($fmt.$data['StatusText'].CloseFormattingString($fmt));
                break;
            case 'reference':
                break;
            case '__actions':
                $dtBtnGroup = array();
                $dtBtnGroup['pdf'] = array(
                    'icon' => 'hi-cloud_download', 'tooltip' => 'Download as PDF', 'type' => 'button', 'colour' => 'info',
                    'url' => "/load.php?do=invoicepdf&InvoiceID={$data['InvoiceID']}", 'target' => 'newwindow'
                );
                $dtBtnGroup['view'] = array(
                    'icon' => 'gi-eye_open', 'tooltip' => 'View document', 'type' => 'button', 'colour' => 'info',
                    'script' => "OpenDialog('viewinvoice', { large: true, urlparams: { InvoiceID: {$data['InvoiceID']} } });"
                );
                $dtBtnGroup['pay'] = array(
                    'icon' => ($data['Currency'] == 'EUR' ? 'gi-euro' : ($data['Currency'] == 'USD' ? 'gi-usd' : 'gi-gbp')), 'tooltip' => 'Pay now', 'type' => 'button', 'colour' => 'success',
                    'url' => "/finance.php?do=pay&InvoiceID={$data['InvoiceID']}",
                    'disabled' => (($data['InvoiceType'] != 'invoice') || ($data['Outstanding'] == 0))
                );
                $Result = ButtonGroup($dtBtnGroup, FALSE, null, 0, TRUE);
                break;                
        }
    }
    return $Result;    
}

function linklistItem($table, $data, $column, $isheader, $sourceindex)
{
    global $SYSTEM_SETTINGS, $NUCLEUS;
    $Result = '';
    if($isheader) {
        $Result = (isset($column['caption']) ? $column['caption'] : "");
    } else {
        switch($column['field']['name'])
        {
            case 'caption':
                $Result = $data['caption'];
                break;
            case 'menu':
                switch($data['menu']) {
                    case 0:
                        $Result = '<b><warning>(none)</warning></b>';
                        break;
                    case 7:
                        $Result = '<b><info>Services</info></b>';
                        break;
                    case 8:
                        $Result = '<b><primary>Membership</primary></b>';
                        break;
                }
                $Result = FmtText($Result);
                break;
            case 'settings':
                if(!empty($data['members'])) {
                    $Result = 'Members Only';
                } else {
                    $Result = 'All Users';
                }
                if(!empty($data['sidebar'])) {
                    $Result .= ", Sidebar";
                }
                if(!empty($data['refer'])) {
                    $Result .= ", Refer";
                }
                if(!empty($data['subsfilter'])) {
                    $subscriptionList = $NUCLEUS->ListSubscriptions();
                    $sub = $subscriptionList->GetSubscription($data['subsfilter']);
                    if(!empty($sub->Name)) {
                        $Result .= ", <info>Filter: <b>".$sub->Name."</b></info>";
                    }
                }
                if(!empty($data['hidden'])) {
                    $Result .= " <warning><b>[Hidden]</b></warning>";
                }
                $Result = FmtText($Result);
                break;
            case '__actions':
                $dtBtnGroup = array();
                $dtBtnGroup['edit'] = array(
                    'icon' => 'fa-pencil', 'tooltip' => 'View/Edit this Link',
                    'script' => "OpenDialog('EditLinkItem', { linkid: {$data['linkid']} }, true )",
                    'type' => 'button', 'colour' => 'info',
                );
                $dtBtnGroup['del'] = array(
                    'icon' => 'fa-times', 'tooltip' => 'Delete this Person',
                    'script' => "DelLink( {$data['linkid']} );", 
                    'type' => 'button', 'colour' => 'danger',
                );
                $Result = ButtonGroup($dtBtnGroup, FALSE, null, 0, TRUE);
                break;
        }
    }
    return $Result;
}

function GetAppStages($appdata, $selector) {
    //file_put_contents("D:\\temp\\appdata.txt", print_r($appdata, TRUE));
    $page = (!empty($_GET['page']) ? IdentifierStr($_GET['page']) : null);
    uasort($appdata['ApplicationSections'], function($a, $b) { return($a['order'] > $b['order']); });
    $stages = array(
        0 => array(
            'id' => 'appstage_intro',
            'caption' => ($appdata['HasOpenApplication'] ? (!empty($appdata['Application']['UserCanModify']) ? 'Started' : $appdata['Application']['StageName'])  : 'Start'),
            'active' => empty($page),
            'iconalign' => 'left',
            'icon' => ($appdata['HasOpenApplication'] ? "fa-check-circle-o" : "fa-circle-o"),
            //'icon' => "fa-circle-o",
            'script' => "LoadAppPage('{$selector}')",
        ),
    );
    if(!empty($appdata['Application']['UserCanModify'])) {
                    //$sectionstr = http_build_query($section, null, '&', PHP_QUERY_RFC3986);
                    //$post = OutputArrayAsJSObject(array_merge(array('PersonID' => $NUCLEUS->CurrentUser->PersonID, '_section' => $sectionstr), $data), TypeHintFromFields($section['fields']));
        foreach($appdata['ApplicationSections'] AS $key => $section) {
            if(!empty($section['sectiontype']) && (strcasecmp($section['sectiontype'], 'none') != 0))
                $stages[$key] = array(
                    'id' => 'appstage_'.$key,
                    'caption' => (!empty($section['title']) ? $section['title'] : ucfirst($key)),
                    'active' => ($page == $key),
                    'iconalign' => 'left',
                    'disabled' => empty($appdata['Application']['UserCanModify']),
                    'icon' => (!empty($section['completed']) ? "fa-check-circle-o" : "fa-circle-o"),
//                    'icon' => "fa-circle-o",
                    'script' => "LoadAppPage('{$selector}', '{$key}')",
            );
        }
        $stages[] = array(
            'id' => 'appstage_msdir',
            'caption' => 'Membership Directory',
            'active' => ($page == 'msdir'),
            'iconalign' => 'left',
            'icon' => (isset($appdata['Application']['OtherComponents']) && is_array($appdata['Application']['OtherComponents']) && array_key_exists('msdir', $appdata['Application']['OtherComponents']) ? "fa-dot-circle-o" : "fa-circle-o"),
            'script' => "LoadAppPage('{$selector}', 'msdir')",
        );
        if(IsMenuEnabled("DirectDebit") && $appdata['Application']['ISO4217'] == 'GBP') {
            $stages[] = array(
                'id' => 'appstage_directdebit',
                'caption' => 'Direct Debit',
                'active' => ($page == 'directdebit'),
                'iconalign' => 'left',
                'icon' => (isset($appdata['Application']['OtherComponents']) && is_array($appdata['Application']['OtherComponents']) && array_key_exists('directdebit', $appdata['Application']['OtherComponents']) ? "fa-dot-circle-o" : "fa-circle-o"),
//                'icon' => "fa-circle-o",
                'script' => "LoadAppPage('{$selector}', 'directdebit')",
            );
        }
        $stages[] = array(
            'id' => 'appstage_submit',
            'caption' => ($appdata['Application']['UserCanModify'] ? 'Submit' : 'Submitted'),
            'active' => ($page == 'submit'),
            'iconalign' => 'left',
            'icon' => (!empty($appdata['Application']['UserCanModify']) ? "fa-circle-o" : "fa-check-circle-o"),
            'script' => "LoadAppPage('{$selector}', 'submit')",
            'disabled' => ($appdata['Application']['UserCanModify'] && empty($appdata['Application']['CanSubmit']))
        );
    } elseif(!empty($appdata['Application']['HasTransaction']) && empty($appdata['Application']['Paid'])) {
        $stages[] = array(
            'id' => 'appstage_pay',
            'caption' => 'Pay Online',
            'active' => FALSE,
            'iconalign' => 'left',
            'icon' => "fa-circle-o",
            'url' => "/finance.php?do=pay&InvoiceID={$appdata['Application']['InvoiceID']}",
        );
    }
    return $stages;
}

function CollectionPage($sectionname, $section) {
    global $NUCLEUS;
//    file_put_contents("D:\\temp\\{$sectionname}.txt", print_r($section, TRUE));
    $params = array();
    if(!empty($section['api']['get']['identifiers'])) {
        if(!is_array($section['api']['get']['identifiers'])) {
            $params[$section['api']['get']['identifiers']] = GetNucleusData($section['api']['get']['identifiers']);
        } else {
            foreach($section['api']['get']['identifiers'] AS $identifier) {
                $params[$identifier] = GetNucleusData($identifier);
            }
        }
    }
    $collTable = array(
        'section' => $section,
        'sectionname' => $sectionname,
        'header' => FALSE,
        'striped' => TRUE,
        'condensed' => TRUE,
        'borders' => 'none',
        'responsive' => FALSE,
        'valign' => 'centre',
        'margin' => TRUE,
        'columns' => array(
            array(
                'field' => array('name' => 'CollectionItem', 'type' => $sectionname),
                'function' => 'collTableItem'
            ),
            array(
                'field' => array('name' => 'CollectionActions', 'type' => 'control'),
                'function' => 'collTableItem'
            ),
        ),
    );
    $headingitem = array('caption' => $section['title'], 'level' => 5, 'style' => 'legend');
    Heading($headingitem, 9);                
    $sizestr = (!empty($section['dialog']['large']) ? "true" : "false");
    $post = OutputArrayAsJSObject(array('PersonID' => $NUCLEUS->CurrentUser->PersonID, 'ApplicationID' => (isset($section['applicationid']) ? $section['applicationid'] : null)), TypeHintFromFields($section['fields']));
    ButtonGroup(array(
        array(
            'icon' => 'fa-plus-square', 'caption' => 'Add '.$section['title'], 'Tooltip' => 'Add a new item',
            'script' => "OpenDialog('editcollectionitem', { large: {$sizestr}, urlparams: { sectionname: '{$sectionname}', section: '".http_build_query($section)."' }, postparams: {$post} })"
        ),
    ), FALSE, null, 8, FALSE);
    $data = $NUCLEUS->GetData($section['api']['get']['cmd'], $params);
    //file_put_contents("D:\\temp\\{$sectionname}_data.txt", print_r($data, TRUE));
    StaticTable($data, $collTable, array(), 9);
    return count($data);
}

function ModalHeader($title)
{
    echo str_repeat("\t", 6)."<div class=\"modal-header\">\n";
    echo str_repeat("\t", 7)."<button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-hidden=\"true\">&times;</button>\n";
    echo str_repeat("\t", 7)."<h3 class=\"modal-title\">".FmtText($title)."</h3>\n";
    echo str_repeat("\t", 6)."</div>\n";
}

function ModalBody($closing = FALSE, $errormsg = "The changes have not been saved!", $errorcaption = "Save Error")
{
    if($closing) {
        echo str_repeat("\t", 6)."</div>\n";
    } else {
        echo str_repeat("\t", 6)."<div class=\"modal-body\">\n";
        if(!empty($errormsg)) {
            echo str_repeat("\t", 7)."<div class=\"alert alert-danger alert-dismissable display-none\">\n";
            echo str_repeat("\t", 8)."<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>\n";
            echo str_repeat("\t", 8)."<h4><i class=\"fa fa-times-circle\"></i> ".htmlspecialchars($errorcaption)."</h4> ".FmtText($errormsg)."\n";
            echo str_repeat("\t", 7)."</div>\n";
        }
    }
}

//TODO: refactor for ModalFooter to take its parameters from an array, making this cleaner and more versatile
function ModalFooter($formID, $saveurl = '', $cbSuccess = '', $savecaption = "Save changes", $cbPosted = '', $validate = '')
{
    echo str_repeat("\t", 6)."<div class=\"modal-footer\">\n";
    if(!empty($formID)) {
        echo str_repeat("\t", 7)."<button type=\"button\" class=\"btn btn-sm btn-default\" data-dismiss=\"modal\">".(stripos($savecaption, 'cancel') !== FALSE ? 'Abort' : 'Cancel')."</button>\n";
    }
    if(!empty($formID) && !empty($saveurl)) {
        $script = "submitForm('{$formID}', '{$saveurl}', { defErrorDlg: ".(empty($cbPosted) ? "true" : "false").", defSuccessDlg: false, parseJSON: true, modal: true";
        if(!empty($validate)) {
            $script .= ", validate: ".$validate;
        }
        if(!empty($cbSuccess)) {
            $script .= ", cbSuccess: ".$cbSuccess;
        }
        if(!empty($cbPosted)) {
            $script .= ", cbPosted: ".$cbPosted;
        }
        $script .= " } );";
        echo str_repeat("\t", 7)."<button id=\"dlgConfirmationBtnSave\" type=\"button\" class=\"btn btn-sm btn-primary\" onclick=\"{$script}\">".htmlspecialchars($savecaption)."</button>\n";
    } elseif(!empty($saveurl)) {
        echo str_repeat("\t", 7)."<button id=\"dlgConfirmationBtnSave\" type=\"button\" class=\"btn btn-sm btn-primary\" onclick=\"{$saveurl}\">".htmlspecialchars($savecaption)."</button>\n";
    } else {
        //No form, so just present a close button
        echo str_repeat("\t", 7)."<button type=\"button\" class=\"btn btn-sm btn-info\" data-dismiss=\"modal\">Close</button>\n";
    }
    echo str_repeat("\t", 6)."</div>\n";
    if(!empty($formID)) {
        jsFormValidation($formID, TRUE, 6, TRUE);
    }
}

?>
