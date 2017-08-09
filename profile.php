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

<!DOCTYPE html>
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
	<head>
		<meta charset="UTF-8">    
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
		<title>MyPortal: Your Profile</title>
		<meta name="description" content="My Portal">
		<meta name="author" content="Guido Gybels">
		<meta name="robots" content="noindex, nofollow">
		<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1.0">

		<link rel="icon" href="/img/favicon.ico">
		<link rel="apple-touch-icon" href="/img/icon57.png" sizes="57x57">
		<link rel="apple-touch-icon" href="/img/icon72.png" sizes="72x72">
		<link rel="apple-touch-icon" href="/img/icon76.png" sizes="76x76">
		<link rel="apple-touch-icon" href="/img/icon114.png" sizes="114x114">
		<link rel="apple-touch-icon" href="/img/icon120.png" sizes="120x120">
		<link rel="apple-touch-icon" href="/img/icon144.png" sizes="144x144">
		<link rel="apple-touch-icon" href="/img/icon152.png" sizes="152x152">

		<link rel="stylesheet" href="/css/bootstrap.min.css">
		<link rel="stylesheet" href="/css/plugins.css">
		<link rel="stylesheet" href="/css/main.css">
		<link rel="stylesheet" href="/css/themes.css">
		<link rel="stylesheet" href="/css/<?php echo $SYSTEM_SETTINGS["General"]["StyleSheet"]; ?>">

		<script src="/js/vendor/modernizr-2.7.1-respond-1.4.2.min.js"></script>
		<script src="/js/moment.min.js"></script>

<?php if(file_exists(CONSTLocalStorageRoot."googleanalytics.txt")){ echo file_get_contents(CONSTLocalStorageRoot."googleanalytics.txt"); } ?>
<?php if(file_exists(CONSTLocalStorageRoot."googletagmanager.txt")){ echo file_get_contents(CONSTLocalStorageRoot."googletagmanager.txt"); } ?>
	</head>
	<body>
		


<?php
require_once('ccontrols.inc');
?>
		<div id="page-container" class="sidebar-partial sidebar-visible-lg sidebar-no-animations footer-fixed header-fixed-top">
			<!-- Main Sidebar -->
<?php
require_once("sidebar.inc");
?>
			<div id="sidebar">
				<!-- Wrapper for scrolling functionality -->
				<div class="sidebar-scroll">
					<!-- Sidebar Content -->
					<div class="sidebar-content">
                        <!-- Brand -->
						<a href="/index.php" class="sidebar-brand">
							<i class="fa fa-thumb-tack"></i><?php echo $SYSTEM_SETTINGS["General"]["PortalName"]; ?>
						</a><!-- END Brand -->
                        
                        <!-- User Info -->
                        <div class="sidebar-section sidebar-user clearfix">
                            <div class="sidebar-user-avatar">
								<a href="javascript:void(0)">
                                <a href="<?php echo (!$NUCLEUS->CurrentUser->Guest ? '/profile.php' : 'javascript:void(0)'); ?>"<?php echo ($NUCLEUS->CurrentUser->Guest ? " onclick=\"LogIn(); return false;\"": ""); ?>>
									<img id="userAvatar" src="img/avatar/<?php echo ($NUCLEUS->CurrentUser->Guest ? "avatar_user.png" : $NUCLEUS->CurrentUser->PersonID.".jpg" ); ?>" alt="<?php echo ($NUCLEUS->CurrentUser->Guest ? "guest avatar": "avatar for ".$NUCLEUS->CurrentUser->Fullname ); ?>" onerror="if(this.src != 'img/avatar/avatar_user.png'){this.src = 'img/avatar/avatar_user.png';}">
								</a>
                            </div>
							<div class="sidebar-user-name"><?php echo ($NUCLEUS->CurrentUser->Guest ? "Guest" : $NUCLEUS->CurrentUser->Firstname); ?></div>
							<div class="sidebar-user-links">
<?php if(!$NUCLEUS->CurrentUser->Guest): ?>
								<a href="/profile.php" data-toggle="tooltip" data-placement="bottom" title="Your Personal Details"><i class="gi gi-user"></i></a>
<?php if($SYSTEM_SETTINGS["Security"]['AllowPasswordChange']): ?>
								<a id="sbShortcutChangePW" href="javascript:void(0)" data-toggle="tooltip" data-placement="bottom" title="Change Password" onclick="$('#changemypwmodal').modal('toggle'); return false;" ><i class="fa fa-key"></i></a>
<?php endif; ?>
<?php if(IsAdministrator($NUCLEUS)): ?>
								<a href="/admin.php" data-toggle="tooltip" data-placement="bottom" title="Administration"><?php echo Icon('gi-cogwheel'); ?></a>
<?php endif; ?>
								<a href="javascript:void(0)" data-toggle="tooltip" data-placement="bottom" title="Log out" onclick="LogOut(); return false;"><i class="gi gi-exit"></i></a>
<?php else: ?>
								<a href="javascript:void(0)" data-toggle="tooltip" data-placement="bottom" title="Log In" onclick="LogIn(); return false;"><i class="fa fa-sign-in"></i></a>
<?php endif; ?>                                
							</div>                          
                        </div>
<?php if($SYSTEM_SETTINGS["Customise"]["SidebarLogo"]): ?>                        
						<a href="<?php echo $SYSTEM_SETTINGS["General"]["Website"]; ?>" target="_blank" class="sidebar-logo">
							<img id="corpLogo" src="<?php echo $SYSTEM_SETTINGS["Customise"]["Logos"]["Sidebar"]; ?>" alt="<?php echo 'logo for '.$SYSTEM_SETTINGS["General"]["OrgLongName"]; ?>">
						</a><!-- END Logo -->
<?php endif; ?>                         
                        <!-- Sidebar Navigation -->
                        <ul class="sidebar-nav">
<?php
if((!$NUCLEUS->CurrentUser->Guest) && (!$NUCLEUS->CurrentUser->Membership->IsMember) && ($SYSTEM_SETTINGS["Customise"]["Navigation"]["Shortcuts"]["Website"]))
{
    SBShortcut(array('caption' => "Join now!", 'icon' => 'gi-nameplate_alt', 'url' => '/apply.php?section=ms'));
}
if($SYSTEM_SETTINGS["Customise"]["Navigation"]["Shortcuts"]["Website"])
{
    SBShortcut(array('caption' => $SYSTEM_SETTINGS["Customise"]['Navigation']['Shortcuts']["Website"]['Caption'], 'icon' => 'fa-external-link', 'url' => $SYSTEM_SETTINGS["General"]["Website"], 'target' => 'newwindow'));
}
foreach($SYSTEM_SETTINGS["Customise"]["Links"] AS $linkid => $link) {
    if(CanShowLink($link) && !empty($link['sidebar']) && (empty($link['members']) || ($NUCLEUS->CurrentUser->Membership->IsMember))) {
        $show = TRUE;
        if(!empty($link['subsfilter'])) {
            if(!$NUCLEUS->IsSubscribed($link['subsfilter'])) {
                $show = FALSE;
            }
        }        
        
        SBShortcut(array(
            'caption' => $link['caption'],
            'icon' => (!empty($link['icon']) ? $link['icon'] : (!empty($link['refer']) ? 'gi-right_arrow' : 'fa-external-link') ),
            'url' => $link['url'],
            'target' => (!empty($link['refer']) ? null : 'newwindow'),
        ));
    }
}
if(IsAdministrator($NUCLEUS))
{
    SBHeader(array('caption' => 'Administration'));
    SBShortcut(array('caption' => 'Settings', 'icon' => 'gi-settings', 'url' => '/admin.php'));
}
?>
					   </ul><!-- Sidebar Navigation -->
<?php
SBHeader(array('kind' => 'sectionheader', 'caption' => 'Notifications', 'id' => 'shNotifications',  'iconlist' => array(array('icon' => 'gi-refresh'))));
?>
					   <div class="sidebar-section" id="sbalerts">
					   </div>

					</div><!-- END Sidebar Content -->
				</div><!-- END Wrapper for scrolling functionality -->
			</div><!-- END Main Sidebar -->
            <div id="changemypwmodal" class="modal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title"><i class="fa fa-key"></i> Change your Password</h4>
                        </div>
                        <div class="modal-body">
                            <form id="frmChangeMyPassword" class="form-horizontal" enctype="multipart/form-data" method="post" name="frmChangeMyPassword">
                                <input id="frmChangeMyPassword:SYSTEM_SOURCE" type="hidden" value="frmChangeMyPassword" name="SYSTEM_SOURCE">
                                <div class="form-group">
                                    <label class="col-sm-4 control-label" for="frmChangeMyPassword:NewPassword1">
                                        Your New Password
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="col-sm-5">
                                        <input id="frmChangeMyPassword:NewPassword1" class="form-control" type="password" value="" name="NewPassword1">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label" for="frmChangeMyPassword:NewPassword2">
                                        Confirm New Password
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="col-sm-5">
                                        <input id="frmChangeMyPassword:NewPassword2" class="form-control" type="password" value="" name="NewPassword2">
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Cancel</button>
                            <button id="frmChangeMyPassword:btnChangeMyPassword" type="button" onclick="submitForm( 'frmChangeMyPassword', '/syscall.php?do=saveapiform&cmd=changepassword', { parseJSON: true, cbSuccess: function(){ dlgDataSaved('Your password has been changed! Next time you log in, you will need to use the new password.'); }, defErrorDlg: true } ); return false;" class="btn btn-sm btn-warning" disabled="disabled">Change</button>
                        </div>
                    </div>
                </div>
            </div>
<?php ADDPLUGIN('sidebar', 'sidebar.js'); ?>            

        <!-- Main Container -->
			<div id="main-container">
				<header class="navbar navbar-default navbar-fixed-top">
					<div class="navbar-header">
						<!-- Horizontal Menu Toggle for small screens (< 768px) -->
						<ul class="nav navbar-nav-custom pull-right visible-xs">
							<li>
								<a href="javascript:void(0)" data-toggle="collapse" data-target="#horizontal-menu-collapse"><i class="fa fa-bars fa-fw"></i> Menu</a>
							</li>
						</ul>
						<!-- Main Sidebar Toggle Button -->
						<ul class="nav navbar-nav-custom">
							<li>
								<a href="javascript:void(0)" onclick="App.sidebar('toggle-sidebar');">
								<i class="fa fa-ellipsis-h fa-fw"></i>
								</a>
							</li>
						</ul>
					</div><!-- END Navbar Header -->
					<!-- Horizontal Menu + Search -->
<?php
//Create a Menu Structure - built-in items first
define("CONST_MENU_HOME",            0);
define("CONST_MENU_LOGON",           1);
define("CONST_MENU_ACCOUNT",         3);
define("CONST_MENU_EVENTS",          5);
define("CONST_MENU_SERVICES",        7);
define("CONST_MENU_MEMBERSHIP",      8);
define("CONST_MENU_ADMINISTRATION", 99);

define("CONST_MENUITEM_LOGOFF",        99);
define("CONST_MENUITEM_SUBSCRIPTIONS",  0);
define("CONST_MENUITEM_PROFILE",        0);
define("CONST_MENUITEM_TRANSACTIONS",   1);
define("CONST_MENUITEM_DIRECTDEBIT",    2);
define("CONST_MENUITEM_SETTINGS",       0);

//var_dump($NUCLEUS->CurrentUser);
/*var_dump(IsMenuEnabled("DirectDebit"));
var_dump(!$SYSTEM_SETTINGS['Finance']['DDMSOnly']);
var_dump($NUCLEUS->CurrentUser->Membership->IsMember);
var_dump($NUCLEUS->CurrentUser->Membership->HasApplication);
var_dump((IsMenuEnabled("DirectDebit") && (!$SYSTEM_SETTINGS['Finance']['DDMSOnly'] || ($NUCLEUS->CurrentUser->Membership->IsMember || $NUCLEUS->CurrentUser->Membership->HasApplication))));
*/

$MAINMENU = array(
    'id' => 'horizontal-menu-collapse',
    'items' => array(
        CONST_MENU_HOME => array(
            'icon' => 'gi-home',
            'url' => '/index.php'
        ),
        CONST_MENU_LOGON => array(
            'caption' => 'Log In',
            'script' => 'LogIn();',
            'visible' => $NUCLEUS->CurrentUser->Guest,
        ),        
        CONST_MENU_ACCOUNT => array(
            'caption' => 'My Account',
            'visible' => !$NUCLEUS->CurrentUser->Guest,
            'items' => array(
                CONST_MENUITEM_PROFILE => array(
                    'caption' => 'Personal Details',
                    'icon' => 'gi-user',
                    'url' => '/profile.php'
                ),
                CONST_MENUITEM_TRANSACTIONS => array(
                    'caption' => 'My Billing',
                    'icon' => 'fa-credit-card',
                    'url' => '/finance.php',
                    'visible' => IsMenuEnabled("Financial")
                ),
                CONST_MENUITEM_DIRECTDEBIT => array(
                    'caption' => 'Direct Debit',
                    'icon' => 'gi-transfer',
                    'url' => '/finance.php?do=directdebit',
                    'visible' => (IsMenuEnabled("DirectDebit") && (!$SYSTEM_SETTINGS['Finance']['DDMSOnly'] || ($NUCLEUS->CurrentUser->Membership->IsMember || $NUCLEUS->CurrentUser->Membership->HasApplication)))
                ),
                CONST_MENUITEM_LOGOFF-3 => array(
                    'kind' => 'divider',
                    'visible' => $SYSTEM_SETTINGS["Security"]['AllowPasswordChange'],
                ),                
                CONST_MENUITEM_LOGOFF-2 => array(
                    'caption' => 'Change Password',
                    'icon' => 'fa-key',
                    'script' => "$('#changemypwmodal').modal('toggle'); return false;",
                    'visible' => $SYSTEM_SETTINGS["Security"]['AllowPasswordChange'],
                ),
                (CONST_MENUITEM_LOGOFF-1) => array(
                    'kind' => 'divider'
                ),                 
                CONST_MENUITEM_LOGOFF => array(
                    'caption' => 'Log out',
                    'icon' => 'gi-exit',
                    'script' => "LogOut();",
                ),
            )
        ),
        CONST_MENU_EVENTS => array(
            'visible' => IsMenuEnabled("Events"),
            'caption' => 'Events & Conferences',
            'url' => '/services.php?section=events'
        ),
        CONST_MENU_SERVICES => array(
            'caption' => (!empty($SYSTEM_SETTINGS["Customise"]['UseShortOrgName']) ? 'Me and the '.$SYSTEM_SETTINGS["General"]["OrgShortName"] : $SYSTEM_SETTINGS["General"]["PortalName"]),
            'visible' => IsMenuEnabled("Services") && (!$NUCLEUS->CurrentUser->Guest),
            'url' => '/services.php',
            'items' => array(
                CONST_MENUITEM_SUBSCRIPTIONS => array(
                    'caption' => 'My Subscriptions',
                    'icon' => 'fa-star',
                    'visible' => IsMenuEnabled("Services;Subscriptions"),
                    'url' => '/services.php?section=subscriptions',
                ),
                32766 => array(
                    'kind' => 'divider'
                ),
                32767 => array(
                    'caption' => $SYSTEM_SETTINGS["Customise"]['Navigation']['Shortcuts']["Website"]['Caption'],
                    'icon' => 'fa-external-link',
                    'url' => $SYSTEM_SETTINGS["General"]["Website"],
                    'target' => 'newwindow'
                )
            )
        ),
        CONST_MENU_ADMINISTRATION => array(
            'caption' => 'Administration',
            'icon' => 'gi-cogwheel',
            'visible' => IsAdministrator($NUCLEUS),
            'items' => array(
                CONST_MENUITEM_SETTINGS => array(
                    'caption' => 'Settings',
                    'icon' => 'gi-settings',
                    'url' => '/admin.php',
                ),
            )
        ),
    ) 
);
//Membership menu depends on user type
if($NUCLEUS->CurrentUser->Guest)
{
    $MAINMENU['items'][CONST_MENU_MEMBERSHIP] = array(
        'caption' => 'Join Us',
        'url' => $SYSTEM_SETTINGS['Customise']['Membership']['JoinUs'],
        'target' => 'newwindow'
    );
}
elseif(!$NUCLEUS->CurrentUser->Membership->IsMember)
{
    $MAINMENU['items'][CONST_MENU_MEMBERSHIP] = array(
        'caption' => 'Join Us',
        'url' => '/apply.php?section=ms',
    );
}
else
{
    //Members
    $MAINMENU['items'][CONST_MENU_MEMBERSHIP] = array(
        'caption' => 'Membership',
        'url' => '/membership.php',
        'items' => array(
            0 => array(
                'caption' => 'My Membership',
                'icon' => 'gi-nameplate_alt',
                'url' => '/membership.php',
            ),
            20 => array(
                'visible' => IsMenuEnabled("Membership;Directory"),
                'caption' => 'Directory',
                'icon' => 'gi-address_book',
                'url' => '/membership.php?activetab=directory',
            ),
            21 => array(
                'visible' => IsMenuEnabled("Membership;Branches"),
                'caption' => 'Branches',
                'icon' => 'gi-git_branch',
                'url' => '/membership.php?activetab=branches',
            ),
        )
    );
}
//Add Custom Links
$permenukey = array(CONST_MENU_SERVICES => 0, CONST_MENU_MEMBERSHIP => 0);
foreach($SYSTEM_SETTINGS["Customise"]["Links"] AS $linkid => $link) {
    if(!empty($link['menu']) && isset($MAINMENU['items'][$link['menu']])) {
        if(CanShowLink($link)) {
            if(!empty($link['dividerbefore'])) {
                $permenukey[$link['menu']] = NextAvailableKey($MAINMENU['items'][$link['menu']]['items'], $permenukey[$link['menu']]);
                $MAINMENU['items'][$link['menu']]['items'][$permenukey[$link['menu']]] = array(
                    'kind' => 'divider'
                );
            }
            $permenukey[$link['menu']] = NextAvailableKey($MAINMENU['items'][$link['menu']]['items'], $permenukey[$link['menu']]);
            $MAINMENU['items'][$link['menu']]['items'][$permenukey[$link['menu']]] = array(
                'caption' => $link['caption'],
                'url' => $link['url'],
                'target' => (!empty($link['refer']) ? null : 'newwindow'),
                'icon' => (!empty($link['icon']) ? $link['icon'] : null),
            );
            if(!empty($link['dividerafter'])) {
                $permenukey[$link['menu']] = NextAvailableKey($MAINMENU['items'][$link['menu']]['items'], $permenukey[$link['menu']]);
                $MAINMENU['items'][$link['menu']]['items'][$permenukey[$link['menu']]] = array(
                    'kind' => 'divider'
                );
            }
        }
    }    
}

//Temp arrangement
//Add service items (considered per org basis)
/*$key = NextAvailableKey($MAINMENU['items'][CONST_MENU_SERVICES]['items']);
$MAINMENU['items'][CONST_MENU_SERVICES]['items'][$key] = array('caption' => 'Scholarship Programme', 'icon' => 'fa-briefcase');
$key = NextAvailableKey($MAINMENU['items'][CONST_MENU_SERVICES]['items'], $key);
$MAINMENU['items'][CONST_MENU_SERVICES]['items'][$key] = array('caption' => 'Grant Opportunities', 'icon' => 'gi-money');
$key = NextAvailableKey($MAINMENU['items'][CONST_MENU_SERVICES]['items'], $key);
$MAINMENU['items'][CONST_MENU_SERVICES]['items'][$key] = array('caption' => 'I love Maths', 'icon' => 'gi-heart');
$key = NextAvailableKey($MAINMENU['items'][CONST_MENU_SERVICES]['items'], $key);
$MAINMENU['items'][CONST_MENU_SERVICES]['items'][$key] = array('caption' => 'Speaker Database', 'icon' => 'gi-keynote');
$key = NextAvailableKey($MAINMENU['items'][CONST_MENU_SERVICES]['items'], $key);
$MAINMENU['items'][CONST_MENU_SERVICES]['items'][$key] = array('caption' => 'Schools Speakers', 'icon' => 'gi-parents');
//Add Membership items (considered per org basis)
if(isset($MAINMENU['items'][CONST_MENU_MEMBERSHIP]['items']))
{
    $key = NextAvailableKey($MAINMENU['items'][CONST_MENU_MEMBERSHIP]['items']);
    $MAINMENU['items'][CONST_MENU_MEMBERSHIP]['items'][$key] = array('caption' => 'Chartered Mathematician', 'icon' => 'gi-certificate');
    $key = NextAvailableKey($MAINMENU['items'][CONST_MENU_MEMBERSHIP]['items'], $key);
    $MAINMENU['items'][CONST_MENU_MEMBERSHIP]['items'][$key] = array('caption' => 'Chartered Scientist', 'icon' => 'gi-global');
}*/
SortMenuItems($MAINMENU);
MainMenu($MAINMENU);

?><!-- END Horizontal Menu -->
				</header>
                
				<!-- Page content -->
				<div id="page-content">

					<!-- Page Header -->
					
                    
					<div class="content-header">
						<div class="header-section">
							<h1><i class="<?php echo substr("gi-user", 0, 2); ?> gi-user"></i>Your Profile<br><small><?php echo $SYSTEM_SETTINGS["General"]["OrgLongName"]; ?> &middot; Self Service</small></h1>
						</div>
					</div>
					<ul class="breadcrumb breadcrumb-top">
						<?php Breadcrumbs("Home:/index.php;Your Profile:/profile.php"); ?>
					</ul>
					<!-- END Page Header -->

                    <div class="row">
                        <div class="col-lg-8">
                            <div class="block">
<?php
    GuestRedirect('/index.php');
    $isnewuser = !empty($_GET['newuser']);    
//    print_r($Marvin->CurrentUser);
    $titleitem = array('caption' => trim($NUCLEUS->CurrentUser->Fullname));
    BlockTitle($titleitem, 8);
?>
                                <div class="block full">
<?php
//    var_dump($NUCLEUS->CurrentUser);
    $sections = $NUCLEUS->GetData('personprofilesections');
//    var_dump($NUCLEUS->CurrentUser);
//    print_r($sections);
/*    if(($Marvin->CurrentUser->Membership->IsMember) && (!$Marvin->CurrentUser->HasContactAddress))
    {
        $alert = array(
            'id' => 'warningNoAddress',
            'type' => 'warning',
            'title' => 'No postal address',
            'canhide' => TRUE,
            'items' => array(
                array(
                    'type' => 'item',
                    'caption' => 'You have no postal address on your profile. This means we cannot send you any magazines, notices about your membership or other important information and communications through the post.'
                ),
                array(
                    'type' => 'break'
                ),
                array(
                    'type' => 'item',
                    'caption' => 'Click here to go to the address section on your profile and add an address now.',
                    'script' => "$('a[href=&quot;#tab-address&quot;]').tab('show');" 
                )
            )
        );
        AlertBox($alert, 9);
    }*/
    if($isnewuser) {
        Article('welcome_newuser', $NUCLEUS);
    }
    $tabitems = array();
    foreach($sections AS $sectionname => $section) {
        $canshow = FALSE;
        if(!empty($section['permissions'])) {
            if(!is_array($section['permissions'])) {
                $section['permissions'] = array($section['permissions']);
            }
            foreach($section['permissions'] AS $permission) {
                if((strcasecmp($permission, 'member') == 0) && $NUCLEUS->CurrentUser->Membership->IsMember) {
                    $canshow = TRUE;
                } elseif((strcasecmp($permission, 'authenticated') == 0) && !$NUCLEUS->CurrentUser->Guest) {
                    $canshow = TRUE;
                }
                if($canshow) {
                    Break;
                }
            }
        } else {
            $canshow = TRUE;
        }
        $tabitems[$sectionname] = array('id' => $sectionname, 'icon' => $section['icon'], 'tooltip' => (!empty($section['title']) ? $section['title'] : ucfirst($sectionname)), 'section' => $section, 'visible' => $canshow);
    }
    PrepareTabs($tabitems, (!empty($_GET['activetab']) ? $_GET['activetab'] : null));
    Tabs($tabitems, 9);
?>
                                    <div class="tab-content">                                  
<?php
    foreach($tabitems AS $tabname => $tabitem) {
        if (OpenTabContent($tabitem, 9)) {
            switch($tabitem['section']['sectiontype']) {
                case 'form':
                    APIForm($tabitem['section'], array('id' => 'frm'.ucfirst($tabname)), 10);
                    break;
                case 'formgroup':
                    foreach($tabitem['section']['forms'] AS $formid => $form) {
                        $headingitem = array('caption' => $form['title'], 'level' => 5, 'style' => 'legend');
                        Heading($headingitem, 11);
                        if(!empty($form['intro'])) {
                            TabbedOutput($form['intro'], 11);
                        }
                        unset($form['title']);
                        APIForm($form, array('id' => 'frm'.ucfirst($tabname).$formid, 'formstyle' => 'vertical', 'FormID' => $formid, 'formstyle' => 'vertical'), 11);
                    }
                    break;
            }
            CloseTabContent($tabitem, 9);
        }
    }
/*    if (OpenTabContent($tabitems['avatar']))
    {
        EmptyFormHeading($tabitems['avatar']);        
        Div(array('class' => array('imageBox', 'pull-down')), 9);
        Div(array('class' => 'thumbBox'), 10);
        Div(null, 10);//
        Div(array('class' => 'avatarspinner', 'style' => 'display: none;'), 10);
        echo "Loading...";
        Div(null, 10);
        Div(null, 9);// close imageBox
        echo str_repeat("\t", 9)."<input id=\"avatar-file\" type=\"file\" name=\"avatar-file\" style=\"display:none\">";
        ADDPLUGIN('cropper', 'helpers/cropbox.js');    
        $buttongroup = array();
        $buttongroup['browse'] = array('icon' => 'fa-camera', 'iconalign' => 'left', 'caption' => 'Browse...', 'tooltip' => 'Select image to upload', 'type' => 'button', 'script' => "SelectAvatar();", 'id' => 'file', 'colour' => 'primary');
        $buttongroup['zoomout'] = array('icon' => 'fa-search-minus', 'iconalign' => 'left', 'tooltip' => 'Zoom out', 'type' => 'button', 'id' => 'btnZoomOut', 'colour' => 'info');
        $buttongroup['zoomin'] = array('icon' => 'fa-search-plus', 'iconalign' => 'left', 'tooltip' => 'Zoom in', 'type' => 'button', 'id' => 'btnZoomIn', 'colour' => 'info');
        $buttongroup['crop'] = array('icon' => 'fa-crop', 'iconalign' => 'left', 'caption' => 'Apply', 'tooltip' => 'Crop image and save as avatar', 'type' => 'button', 'id' => 'btnCrop', 'colour' => 'success');
        $buttongroup['clear'] = array('icon' => 'fa-times', 'iconalign' => 'left', 'tooltip' => 'Clear your Avatar', 'type' => 'button', 'id' => 'btnClear', 'colour' => 'warning');
        ButtonGroup($buttongroup, FALSE, NULL, 9);
        Div(array('class' => 'cropped'), 9);
        Div(null, 9);
        Para(array('well' => 'small', 'margin' => TRUE), 9);
        echo "To upload an avatar, click the 'Browse' button to upload an image file. Use the mouse and the zoom buttons to position (by dragging) and resize the image into the highlighted square. Click Apply to upload the selected portion of your image as your new avatar. As soon as the upload is complete, your new avatar will appear in the sidebar on the left. To delete your avatar, click the clear button in the titlebar.";
        Para(null, 9);        
        CloseTabContent($tabitems['avatar']);
    }*/
?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
<?php
portalWidgetMembership();
portalWidgetBecomeMember();
?>
                        </div>
                    </div>
				</div><!-- END Page Content -->

				<footer class="clearfix">
					<div class="pull-left">
						Developed by <a href="http://www.guidogybels.eu/" target="_blank">Guido Gybels</a>
					</div>
					<div class="pull-right">
						<a href="/about.php" target="_blank">About</a> &middot; <a href="#modal-terms" data-toggle="modal" class="register-terms">Terms</a><span class="hidden-xs"> &middot; Designed for <a href="http://www.mozilla.org/en-US/firefox/fx/" target="_blank">Firefox</a> &amp; <a href="https://www.google.com/chrome/browser/desktop/" target="_blank">Chrome</a></span>
					</div>
				</footer>

			</div><!-- END Main Container -->



            <!-- Containers for modal dialogs -->
            <div id="dlgStandard" class="modal" tabindex="-1" data-backdrop="static" role="dialog" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                    </div>    
                </div>
            </div>

            <div id="dlgLarge" class="modal" tabindex="-1" data-backdrop="static" data-keyboard="false" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                    </div>    
                </div>
            </div>
            
            <div id="dlgConfirmation" class="modal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h3 class="modal-title" id="dlgConfirmationTitle">Confirm</h3>
                        </div>
                        <div class="modal-body" id="dlgConfirmationBody">
                            <p>Are you sure?</p>
                        </div>
                        <div class="modal-footer">
					       <button id="dlgConfirmationBtnYes" type="button" class="btn btn-sm btn-success" name="yes"><i class="gi gi-ok_2"></i> Yes</button>
					       <button id="dlgConfirmationBtnNo" type="button" class="btn btn-sm btn-primary" data-dismiss="modal" name="no"><i class="gi gi-ban"></i> No</button>
					       <button id="dlgConfirmationBtnCancel" type="button" class="btn btn-sm btn-warning" data-dismiss="modal" name="cancel"><i class="gi fa-times"></i> Cancel</button>
					       <button id="dlgConfirmationBtnRetry" type="button" class="btn btn-sm btn-primary" data-dismiss="modal" name="cancel"><i class="gi hi-repeat"></i> Retry</button>
                        </div>
                    </div>    
                </div>
            </div>            

		</div><!-- END Page Container -->
        
		<!-- Scroll to top link, initialized in js/app.js - scrollToTop() -->
		<a href="#" id="to-top"><i class="fa fa-angle-double-up"></i></a>

		<!-- Include local copy of Jquery library -->
		<script src="/js/vendor/jquery-1.12.0.min.js"></script>

		<!-- Bootstrap.js, Bootbox.js, Jquery plugins and Custom JS code -->
		<!-- IMPORTANT: LOGIN.PHP HAS ITS OWN LOCAL VERSION OF THE SCRIPTS - KEEP UP TO DATE -->
		<script src="/js/vendor/bootstrap.min.js"></script>
		<script src="/js/plugins.js"></script>
		<script src="/js/datepicker.js"></script>
		<script src="/js/slib.js"></script>
		<script src="/js/proui.js"></script>
		<script src="/js/app.js"></script>

        <!-- Modal Terms -->
<?php TermsAndConditions(2); ?>
        <!-- END Modal Terms -->
<script type="text/javascript">
$(function() {
<?php
    foreach($tabitems AS $tabname => $tabitem) {
        switch($tabitem['section']['sectiontype']) {
            case 'collection':
                echo "\tLoadContent('tab-{$tabname}', '/load.php?do=loadcollection', { spinner: true, urlparams: { sectionname: '{$tabname}', section: '".http_build_query($tabitem['section'])."' } });\n";
                break;
            case 'collectiongroup':
                foreach($tabitem['section']['collections'] AS $collectionname => $collection) {
                    $section = $tabitem['section'];
                    unset($section['collections']);
                    $section = array_merge($section, $collection);
                    echo "\tLoadContent('tab-{$tabname}', '/load.php?do=loadcollection', { divid: 'tab-{$collectionname}', spinner: true, urlparams: { sectionname: '{$collectionname}', section: '".http_build_query($section)."' } });\n";
                }
                break;
        }        
    }
?>
//    LoadContent('tab-address', '/load.php?do=loadcollection', { spinner: true, urlparams: {   } });
    //Set up the avatar mechanics 
/*    var options =
    {
        thumbBox: '.thumbBox',
        spinner: '.avatarspinner',
        imgSrc: '',
    }    
    var cropper = $('.imageBox').cropbox(options);
    $('.avatarspinner').hide();
    $('#avatar-file').on('change', function(){
        var reader = new FileReader();
        reader.onload = function(e) {
            options.imgSrc = e.target.result;
            cropper = $('.imageBox').cropbox(options);
        }
        reader.readAsDataURL(this.files[0]);
        this.files = [];
    })
    $('#btnZoomIn').on('click', function(){
        cropper.zoomIn();
    })
    $('#btnZoomOut').on('click', function(){
        cropper.zoomOut();
    })
    $('#btnClear').on('click', function(){
        ClearAvatar();
    })
    $('#btnCrop').on('click', function(){
        var fd = new FormData();
        var url = 'https://'+window.location.host.concat('/syscall.php?do=setavatar');
        fd.append('imgData', cropper.getDataURL());
        $.ajax({
            type: 'POST',
            url: url,
            data: fd,
            processData: false,
            contentType: false
        })
        .done(function(data) {
            var response = jQuery.parseJSON( data );
            var date = new Date();
            $("#userAvatar").attr("src", "/img/avatar/"+response.filename+"?"+date.getTime());
        })
        .fail(function( data ) {
            bootbox.dialog({ message: 'Unable to complete. An error occurred while changing your avatar: '+jqxhr.statusText,
                             title: '<span class="text-danger"><i class="fa fa-times-circle"></i> <strong>Error</strong></span>',
                             buttons: { main: { label: "OK",
                                                className: "btn-primary",
                                                callback: function( data ) { window.location.reload(true) }
                                        }
                            }
            });
        })
        .always(function() {
        });
    })*/
});

function SelectAvatar()
{
    $("#avatar-file").trigger('click');
    return false;
}

function ClearAvatar()
{
    var url = 'https://'+window.location.host.concat('/syscall.php?do=clearavatar');
    var jqxhr = $.get( url, function() {
    })
    .done(function( data ) {
        var response = jQuery.parseJSON( data );
        var date = new Date();
        $("#userAvatar").attr("src", "/img/avatar/"+response.filename+"?"+date.getTime());
    })
    .fail(function( data ) {
        bootbox.dialog({ message: 'Unable to complete. An error occurred while clearing your avatar: '+jqxhr.statusText,
                         title: '<span class="text-danger"><i class="fa fa-times-circle"></i> <strong>Error</strong></span>',
                         buttons: { main: { label: "OK",
                                            className: "btn-primary",
                                            callback: function( data ) { window.location.reload(true) }
                                    }
                         }
        });
    })
    .always(function() {
    });
}

</script>

<?php
if((count($VALIDATION) > 0) || (isset($DATATABLES) && (count($DATATABLES) > 0)) || (count($HANDLERS) > 0))
{
	echo "\n\t\t<!-- JQuery routines for this Page -->\n";
    echo "\t\t<script type=\"text/javascript\">\n";
    echo "\t\t\tjQuery(function($) {\n\n";
    if(count($VALIDATION) > 0)
    {
        echo "\t\t\t\t//Form Validation\n";
        foreach($VALIDATION AS $aformid => $data)
        {
            if(isset($data['rules']) || isset($data['messages']))
            {
                echo "\t\t\t\t$('#".escSelector($aformid)."').validate({\n";
                echo "\t\t\t\t\terrorClass: 'help-block animation-slideDown',\n";
                echo "\t\t\t\t\terrorElement: 'div',\n";
                echo "\t\t\t\t\terrorPlacement: function(error, e) {\n";
                echo "\t\t\t\t\t\te.parents('.form-group > div').append(error);\n";
                echo "\t\t\t\t\t},\n";
                echo "\t\t\t\t\thighlight: function(e) {\n";
                echo "\t\t\t\t\t\t$(e).closest('.form-group').removeClass('has-success has-error').addClass('has-error');\n";
                echo "\t\t\t\t\t\t$(e).closest('.help-block').remove();\n";
                echo "\t\t\t\t\t},\n";
                echo "\t\t\t\t\tsuccess: function(e) {\n";
                echo "\t\t\t\t\t\te.closest('.form-group').removeClass('has-success has-error');\n";
                echo "\t\t\t\t\t\te.closest('.help-block').remove();\n";
                echo "\t\t\t\t\t}".(isset($data['rules']) ? ",": "")."\n";
                if(isset($data['rules']))
                {
                    echo "\t\t\t\t\trules: {\n";
                    $compcount = 0;
                    foreach($data['rules'] AS $componentid => $rules)
                    {
                        echo ($compcount > 0 ? ",\n": "")."\t\t\t\t\t\t'".escSelector($componentid)."': {\n";
                        $rulecount = 0;
                        foreach($rules AS $method => $value)
                        {
                            echo ($rulecount > 0 ? ",\n": "")."\t\t\t\t\t\t\t{$method}: ".OutputJSValue($value);
                            $rulecount++;
                        }
                        echo "\n\t\t\t\t\t\t}";
                        $compcount++;
                    }
                    echo "\n\t\t\t\t\t}".(isset($data['messages']) ? ",": "")."\n";
                    if(isset($data['messages']))
                    {
                        echo "\t\t\t\t\tmessages: {\n";
                        $compcount = 0;
                        foreach($data['messages'] AS $componentid => $messages)
                        {
                            echo ($compcount > 0 ? ",\n": "")."\t\t\t\t\t\t'".escSelector($componentid)."': ";
                            if(count($messages) == 1)
                            {
                                $avalues = array_values($messages);
                                echo OutputJSString(array_shift($avalues));                                
                            }
                            else
                            {
                                echo "{\n";
                                $msgcount = 0;
                                foreach($messages AS $method => $msgtext)
                                {
                                    echo ($msgcount > 0 ? ",\n": "")."\t\t\t\t\t\t\t{$method}: ".OutputJSString($msgtext);
                                    $msgcount++;
                                }
                                echo "\n\t\t\t\t\t\t}";
                            }
                            $compcount++;
                        }
                        echo "\n\t\t\t\t\t}\n";
                    }
                }
                echo "\t\t\t\t});\n";
            }
            if (isset($data['masks']) && (count($data['masks']) > 0))
            {
                foreach($data['masks'] AS $componentid => $mask)
                {
                    echo "\t\t\t\t$('#".escSelector($componentid)."').mask(".OutputJSString($mask).");\n";
                }
            }
        }
        echo "\n";
    }
    if(count($HANDLERS) > 0)
    {
        echo "\t\t\t\t//Attach Event Handlers\n";
        foreach($HANDLERS AS $controlid => $events)
        {
            foreach($events AS $eventname => $eventdata)
            {
                assert(isset($eventdata['function']));
                echo "\t\t\t\t$('#".escSelector($controlid)."').{$eventname}(function( event ) {\n";
                if(is_string($eventdata['function']))
                {
                    $lines = explode("\n", $eventdata['function']);
                    foreach($lines AS $line)
                    {
                        echo "\t\t\t\t\t".rtrim($line)."\n";
                    }
                }
                echo "\t\t\t\t})".(!empty($eventdata['firstrun']) ? ".".$eventname."()" : "").";\n";
            }
        }
//    $HANDLERS[$trusted_controlid][$trusted_event] = array('function' => $function, 'firstrun' => $firstrun);
    }
    if ((isset($DATATABLES)) && (count($DATATABLES) > 0))
    {
        echo "\t\t\t\t//Datatables Initialisation\n";
        echo "\t\t\t\tApp.datatables();\n\n";
        foreach($DATATABLES AS $datatable)
        {
            echo "\t\t\t\t".$datatable['id']." = $('#".escSelector($datatable['id'])."').dataTable({\n";
            echo "\t\t\t\t\t'aLengthMenu': [[10, 25, 50, 100, 250, 500, -1], [10, 25, 50, 100, 250, 500, 'All']],\n";
            echo "\t\t\t\t\t'iDisplayLength': ".(isset($datatable['initlength']) ? intval($datatable['initlength']) : 25).",\n";
            echo "\t\t\t\t\t'sAjaxSource': '".$datatable['ajaxsrc'];
            $paramcount = 0;
            if(!empty($datatable['params']))
            {
                
                foreach($datatable['params'] AS $key => $value)
                {
                    if(!empty($datatable['params'][$key]))
                    {
                        echo ($paramcount == 0 ? '?' : '&').$key.'='.urlencode($value);
                        $paramcount++;
                    }
                }
            }
            if(!empty($datatable['GET']))
            {
                foreach($datatable['GET'] AS $key)
                {
                    if(isset($_GET[$key]))
                    {
                        echo ($paramcount == 0 ? '?' : '&').$key.'='.urlencode($_GET[$key]);
                        $paramcount++;
                    }
                }
            }
            echo "',\n";
            if(!empty($datatable['drawcallback']))
            {
                echo "\t\t\t\t\t'fnDrawCallback': function( oSettings ) {\n";
                if(is_array($datatable['drawcallback']))
                {
                    foreach($datatable['drawcallback'] AS $line)
                    {
                        echo "\t\t\t\t\t\t".$line."\n";
                    }
                }
                else
                {
                    echo "\t\t\t\t\t\t".$datatable['drawcallback'].";\n";
                }
                echo "\t\t\t\t\t},\n";
            }
//            echo "\t\t\t\t\t'sPaginationType': 'full_numbers',\n";
            //aoColumnDefs?
            $colids = array();
            $classes = array();
            $nosort = array();
            $nosearch = array();
            $fieldnames = array();
            $widths = array();
            $colindex = 0;
            foreach($datatable['columns'] AS $column)
            {
                if(!empty($column['id']))
                {
                    $colids[$column['id']] = $colindex;
                }
                //Set some defaults
                foreach(array('sortable' => true, 'searchable' => true) AS $key => $value)
                {
                    if(!isset($column[$key]))
                    {
                        $column[$key] = $value;
                    }
                }
                //Add column classes for text alignment
                $class = PickValue($column, 'textalign', 'default', 'text', array('default', 'left', 'center', 'right'));
                if(!empty($class))
                {
                    if(!isset($classes[$colindex]))
                    {
                        $classes[$colindex] = array();
                    }
                    $classes[$colindex][] = $class;                                            
/*                    if(!isset($classes[$class]))
                    {
                        $classes[$class] = array();
                    }
                    $classes[$class][] = $colindex;*/
                }
                if(!empty($column['hide']))
                {
                    $hideclass = '';
                    if(is_string($column['hide']))
                    {
                        $hideclass = 'hidden-'.IdentifierStr($column['hide']);
                    }
                    elseif(is_array($column['hide']))
                    {
                        foreach($column['hide'] AS $size)
                        {
                            $hideclass .= (empty($hideclass) ? "": " ").'hidden-'.IdentifierStr($size);
                        } 
                    }
                    if(!isset($classes[$colindex]))
                    {
                        $classes[$colindex] = array();
                    }
                    $classes[$colindex][] = $hideclass;
/*                    if(!isset($classes[$hideclass]))
                    {
                        $classes[$hideclass] = array();
                    }
                    $classes[$hideclass][] = $colindex;*/
                }
                if($column['sortable'] == FALSE)
                {
                    $nosort[] = $colindex;
                }
                if($column['searchable'] == FALSE)
                {
                    $nosearch[] = $colindex;
                }
                if(isset($column['fieldname']))
                {
                    $fieldnames[$column['fieldname']] = $colindex;
                }
                if(isset($column['width']))
                {
                    if(!isset($widths[$column['width']]))
                    {
                        $widths[$column['width']] = array();
                    }
                    $widths[$column['width']][] = $colindex;
                }
                $colindex++;
            }
            //Add the aoColumnDefs if required
            if((count($classes) > 0) || (count($nosort) > 0) || (count($nosearch) > 0)
                    || (count($fieldnames) > 0) || (count($widths) > 0))
            {
                $itemcount = 0;
                echo "\t\t\t\t\t'aoColumnDefs': [ ";
                //sClass
                foreach(array('sClass' => $classes) AS $name => $collection)
                {
                    if(count($collection) > 0)
                    {                              
                        foreach($collection AS $colindex => $classes)
                        {
                            if(count($classes) > 0)
                            {
                                echo ($itemcount > 0 ? ", \n\t\t\t\t\t\t" : "")."{ '{$name}': '".implode(' ', $classes)."', 'aTargets': [";
                                echo $colindex."] }";
                                $itemcount++;
                            }
                        }
                    }
                //sWidth
                foreach(array('sWidth' => $widths) AS $name => $collection)
                {
                    if(count($collection) > 0)
                    {
                        foreach($collection AS $key => $values)
                        {
                            echo ($itemcount > 0 ? ", \n\t\t\t\t\t\t" : "")."{ '{$name}': '{$key}', 'aTargets': ";
                            echo OutputJSValue($values)." }";
                            $itemcount++;
                        }
                    }
                }
                }
                //bSortable, bSearchable
                foreach(array('bSortable' => $nosort, 'bSearchable' => $nosearch) AS $name => $values)
                {
                    if(count($values) > 0)
                    {
                        echo ($itemcount > 0 ? ", \n\t\t\t\t\t\t" : "")."{ '{$name}': false, 'aTargets': ";
                        echo OutputJSValue($values)." }";
                        $itemcount++;
                    }
                    
                }
                //sName
                foreach(array('mData' => $fieldnames) AS $name => $collection)
                {
                    if(count($collection) > 0)
                    {
                        foreach($collection AS $key => $value)
                        {
                            echo ($itemcount > 0 ? ", \n\t\t\t\t\t\t" : "")."{ '{$name}': '{$key}', 'aTargets': [ ";
                            echo OutputJSValue($value)." ] }";
                            $itemcount++;
                        }
                    }
                }
                echo " ],\n";
            }
            if(!empty($datatable['sortby']))
            {
                echo "\t\t\t\t\t'aaSorting': [[";
                $acol = $datatable['sortby']['column'];
                if(is_string($acol))
                {
                    if(isset($colids[$acol]))
                    {
                        $acol = $colids[$acol];
                    }
                    elseif(isset($fieldnames[$acol]))
                    {
                        $acol = $fieldnames[$acol];
                    }
                }
                echo intval($acol).", ";
                echo OutputJSValue((isset($datatable['sortby']['direction']) ? $datatable['sortby']['direction'] : "asc"));
                echo "]],\n";
            }
            echo "\t\t\t\t\t'bStateSave': true,\n";
            echo "\t\t\t\t\t'bProcessing': false,\n";
            echo "\t\t\t\t\t'bServerSide': true\n";
            echo "\t\t\t\t});\n\n";
            echo "\t\t\t\t//Add Bootstrap classes to select and input elements added by datatables above the table\n";
            echo "\t\t\t\t$('.dataTables_filter input').addClass('form-control').attr('placeholder', 'Search');\n";
            echo "\t\t\t\t$('.dataTables_length select').addClass('form-control');\n";
        }
        echo "\n";
    }    
    echo "\t\t\t});\n";
    echo "\t\t</script>\n";
}

if(count($PLUGINS) > 0)
{
	echo "\n\t\t<!-- Load Additional Plugins for this page -->\n";
	foreach($PLUGINS AS $name => $script)
	{
        if(is_string($script))
        {
            if(strcasecmp(substr($script, 0, 4), 'http') == 0)
            {
                echo "\t\t<script src=\"{$script}\"></script>\n";
            }
            else
            {
                echo "\t\t<script src=\"js/{$script}\"></script>\n";
            }
        }
        elseif(is_array($script))
        {
            echo "\t\t<script type=\"text/javascript\">\n";
            foreach($script AS $line)
            {
                echo "\t\t\t".$line."\n";
            }
            echo "\t\t</script>\n";
        }
	}
}
?>
	</body>
</html>
<?php ob_end_flush(); ?>