<?php
require_once("initialise.inc");
?>
<!DOCTYPE html>
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
	<head>
		<meta charset="UTF-8">    
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
		<title>MyPortal: Log In</title>
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
		


        <!-- Login Background -->
        <div id="login-background">
            <!-- For best results use an image with a resolution of 2560x400 pixels (prefer a blurred image for smaller file size) -->
            <img src="img/<?php echo (!empty($SYSTEM_SETTINGS['Customise']['HeaderMedia']) ? $SYSTEM_SETTINGS['Customise']['HeaderMedia'] : "mainbanner.jpg"); ?>" alt="Login Background" class="<?php echo (!empty($SYSTEM_SETTINGS['Customise']['AnimatedHeader']) ? "animation-pulseSlow" : ""); ?>">
        </div>
        <!-- END Login Background -->

        <!-- Login Container -->
        <div id="login-container" class="animation-fadeIn">
            <!-- Login Title -->
            <div class="login-title text-center">
                <h1><i class="gi gi-lock"></i> <strong><?php echo $SYSTEM_SETTINGS["General"]['PortalName']; ?></strong><br><small>Please <strong>Login</strong> or <strong>Register</strong></small></h1>
            </div>
            <!-- END Login Title -->

            <!-- Login Block -->
            <div class="block remove-margin">
                <!-- Login Form -->
                <form id="frmLogin" class="form-horizontal form-bordered form-control-borderless">
                    <div class="form-group errormessage display-none">
                        <div class="col-xs-12">
                            <p class="text-center text-danger"></p>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-xs-12">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="gi gi-user"></i></span>
                                <input type="text" id="loginEmail" name="loginEmail" class="form-control input-lg" placeholder="Email or Membership No">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-xs-12">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="gi gi-asterisk"></i></span>
                                <input type="password" id="loginPassword" name="loginPassword" class="form-control input-lg" placeholder="Password">
                            </div>
                        </div>
                    </div>
                    <div class="form-group form-actions">
                        <div class="col-xs-8 text-right beforeaction">
                            <button type="submit" class="btn btn-sm btn-success"><i class="fa fa-sign-in"></i> Log me in</button>
                        </div>
                        <div class="col-xs-12 text-right afteraction display-none">
                            <p class="text-center text-primary"><strong>Please wait...</strong> <i class="fa fa-spinner fa-2x spinner"></i></p>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-xs-12">
                            <p class="text-center remove-margin"><small>Forgot your password?</small> <a href="javascript:void(0)" id="link-reset"><small>Click here to request a new one.</small></a></p>
                            <p class="text-center remove-margin"><small>Don't have an account?</small> <a href="javascript:void(0)" id="link-register"><small>Create one for free!</small></a></p>
                        </div>
                    </div>
                </form>
                <!-- END Login Form -->

                <!-- Register Form -->
                <form id="frmRegister" class="form-horizontal form-bordered form-control-borderless display-none" onsubmit="return false;">
                    <div class="form-group errormessage display-none">
                        <div class="col-xs-12">
                            <p class="text-center text-danger"></p>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-xs-12">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="gi gi-user"></i></span>
                                <input type="text" id="Firstname" name="Firstname" class="form-control input-lg" placeholder="Firstname">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-xs-12">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="gi gi-user"></i></span>
                                <input type="text" id="Lastname" name="Lastname" class="form-control input-lg" placeholder="Lastname">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-xs-12">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="gi gi-envelope"></i></span>
                                <input type="text" id="NewEmail" name="NewEmail" class="form-control input-lg" placeholder="Email">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-xs-12">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="gi gi-envelope"></i></span>
                                <input type="text" id="NewEmail2" name="NewEmail2" class="form-control input-lg" placeholder="Confirm Email">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-xs-12">
                            <p class="text-center">Once you have completed the fields above, enable the left button to accept <a href="#modal-terms" data-toggle="modal">our terms of use</a>, then click "Create Account".</p>
                        </div>
                    </div>
                    <div class="form-group form-actions">
                        <div class="col-xs-6 beforeaction">
                            <a href="#modal-terms" data-toggle="modal" class="register-terms">Terms</a>
                            <label class="switch switch-primary" data-toggle="tooltip" title="I Agree to the terms">
                                <input type="checkbox" id="AcceptTerms" name="AcceptTerms">
                                <span></span>
                            </label>
                        </div>
                        <div class="col-xs-6 text-right beforeaction">
                            <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-plus"></i> Create Account</button>
                        </div>
                        <div class="col-xs-12 text-right afteraction display-none">
                            <p class="text-center text-primary"><strong>Please wait...</strong> <i class="fa fa-spinner fa-2x spinner"></i></p>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-xs-12">
                            <p class="text-center remove-margin"><small>Oops, you have an account?</small> <a href="javascript:void(0)" id="link-login"><small>Login!</small></a></p>
                        </div>
                    </div>
                </form>
                <!-- END Register Form -->
                
                <!-- Reset Password Form -->
                <form id="frmResetPassword" class="form-horizontal form-bordered form-control-borderless display-none" onsubmit="return false;">
                    <div class="form-group errormessage display-none">
                        <div class="col-xs-12">
                            <p class="text-center text-danger"></p>
                        </div>
                    </div>
                    <div class="form-group successmessage display-none">
                        <div class="col-xs-12">
                            <p class="text-center text-success"></p>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-xs-12">
                            <p class="text-center">If you cannot remember your password, you can reset it and receive a new one by entering your email address or membership number below and clicking the button.</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-xs-12">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="gi gi-user"></i></span>
                                <input type="text" id="resetEmail" name="resetEmail" class="form-control input-lg" placeholder="Email or Membership No">
                            </div>
                        </div>
                    </div>
                    <div class="form-group form-actions">
                        <div class="col-xs-8 text-right beforeaction">
                            <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-unlock-alt"></i> Get new Password</button>
                        </div>
                        <div class="col-xs-12 text-right afteraction display-none">
                            <p class="text-center text-primary"><strong>Please wait...</strong> <i class="fa fa-spinner fa-2x spinner"></i></p>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-xs-12">
                            <p class="text-center remove-margin"><small>Remembered your password?</small> <a href="javascript:void(0)" id="link-login2"><small>Go back to login.</small></a></p>
                        </div>
                    </div>
                </form>
                <!-- END Reset Password Form -->
                
            </div>
            <!-- END Login Block -->
        </div>
        <!-- END Login Container -->

        <!-- Modal Terms -->
<?php TermsAndConditions(2); ?>
        <!-- END Modal Terms -->
        
		<!-- Include local copy of Jquery library -->
		<script src="js/vendor/jquery-1.11.0.min.js"></script>

        <!-- Bootstrap.js, Jquery plugins and Custom JS code -->
        <script src="js/vendor/bootstrap.min.js"></script>
        <script src="js/plugins.js"></script>
        <script src="js/datepicker.js"></script>
        <script src="js/slib.js"></script>
        <script src="js/app.js"></script>

        <!-- Load and execute javascript code used only in this page -->
        <script type="text/javascript">
            jQuery(function($) {
                var frmLogin         = $('#frmLogin'),
                    frmRegister      = $('#frmRegister'); 
                    frmResetPassword = $('#frmResetPassword');
                var URLParams = getUrlVars();

                $(".errormessage").hide();                    

                $('#link-reset').click(function(){
                    frmLogin.slideUp(250);
                    frmResetPassword.slideDown(250, function(){$('input').placeholder(); ResetActions(); }); 
                });
                $('#link-register').click(function(){
                    frmLogin.slideUp(250);
                    frmRegister.slideDown(250, function(){$('input').placeholder(); ResetActions(); }); 
                });
                $('#link-login').click(function(){
                    frmRegister.slideUp(250);
                    frmLogin.slideDown(250, function(){$('input').placeholder(); ResetActions(); }); 
                });
                $('#link-login2').click(function(){
                    frmResetPassword.slideUp(250);
                    frmLogin.slideDown(250, function(){$('input').placeholder(); ResetActions(); }); 
                });
                
                if ( URLParams.register ) {
                    $('#link-register').click();
                }
                
                frmLogin.submit(function() {
                    var email = $.trim($('#loginEmail').val());
                    var password = $.trim($('#loginPassword').val());
                    if(( email == '' ) || (password == '')) {
                        ShowError( $(this), "You must enter both an email address or membership number and a password." );
                    } else {
                        BeginProcessing( $(this) );
                        var params = $.param( { username: email, password: password } );
                        var url = 'https://'+window.location.host.concat('/syscall.php?do=login&', params );
                        var jqxhr = $.get( url, function() {
                        })
                        .done(function( response ) {
                            var output = $.parseJSON( response );
                            if( output.success ) {
                                var URLParams = getUrlVars();
                                if ( URLParams.redirect ) {
                                    var url = 'https://'+window.location.host.concat('/', URLParams.redirect, '?sender=login.php' );
                                    delete URLParams.redirect;
                                    if( Object.keys(URLParams).length > 0 )
                                    {
                                        url = url.concat( '&', $.param(URLParams) );
                                    }
                                    window.location.href = url;
                                } else {
                                    window.location.href = 'https://'+window.location.host.concat('/index.php');
                                }

                            } else {
                                ShowError( frmLogin, output.errormessage );
                            } 
                        })
                        .fail(function( jqxhr ) {
                            ShowError( frmLogin, "Unable to execute request: "+jqxhr.statusText );
                        })
                        .always(function( response ) {
                            ResetActions();
                        });
                    }
                    return false;
                });
                frmRegister.submit(function() {
                    $(".errormessage").hide(); 
                    var email = $.trim($('#NewEmail').val());
                    if( email.length > 0) {
                        var emailvfy = $.trim($('#NewEmail2').val());
                        if( email == emailvfy ) {
                            var firstname = $.trim($('#Firstname').val());
                            var lastname = $.trim($('#Lastname').val());
                            if(( firstname.length > 0 ) && ( lastname.length > 0 )) {
                                if ( $( '#AcceptTerms' ).is( ":checked" ) ) {
                                    BeginProcessing( $(this) );
                                    var params = $.param( { email: email, firstname: firstname, lastname: lastname } );
                                    var url = 'https://'+window.location.host.concat('/syscall.php?do=newuser&', params );
                                    var jqxhr = $.get( url, function() {
                                    })
                                    .done(function( response ) {
                                        var output = $.parseJSON( response );
                                        if( output.success ) {
                                            window.location.href = 'https://'+window.location.host.concat('/profile.php?newuser=1');
                                        } else {
                                            ShowError( frmRegister, output.errormessage+(output.errorcode == -7 ? ' You can reset the password via the login page.' : '') );
                                        }
                                    })
                                    .fail(function( jqxhr ) {
                                        ShowError( frmRegister, "Unable to execute request: "+jqxhr.statusText );
                                    })
                                    .always(function( response ) {
                                        ResetActions();
                                    });
                                } else {
                                    ShowError( $(this), "You must accept the terms and conditions first.", true );
                                    $('#AcceptTerms').closest('label').popover({
                                        html: true,
                                        placement: 'right',
                                        content: '<span class="text-primary"><b>Click the switch to accept the terms and conditions.</b></span>',
                                        trigger: 'manual',
                                    }).popover('show');
                                    setTimeout(function(){ $('#AcceptTerms').closest('label').popover('destroy'); }, 5000);
                                }
                            }
                            else {
                                ShowError( $(this), "Please provide your first name and last name.", true );
                            }
                        }
                        else {
                            ShowError( $(this), "The email address does not match the email address confirmation.", true );
                        }
                    } else {
                        ShowError( $(this), "Please provide an email address. You need this to register.", true );
                    }
                    return false;
                });
                frmResetPassword.submit(function() {
                    var email = $.trim($('#resetEmail').val());
                    if(email == '') {
                        ShowError( $(this), "Please enter your membership number or the email address registered to your account." );
                    } else {
                        BeginProcessing( $(this) );
                        var params = $.param( { username: email } );
                        var url = 'https://'+window.location.host.concat('/syscall.php?do=resetpw&', params );
                        var jqxhr = $.get( url, function() {
                        })
                        .done(function( response ) {
                            var output = $.parseJSON( response );
                            if( output.success ) {
                                if( output.data.confirmationsent ) {
                                    bootbox.dialog({ message: '<b>Your password has been successfully reset and an email has been sent to the registered email address.</b> Please note that it can take up to 15 minutes for the email to be received.',
                                                     title: '<span class="text-success"><i class="fa fa-times-circle"></i> <strong>Password Reset</strong></span>',
                                                     buttons: { main: { label: "OK",
                                                                         className: "btn-primary",
                                                                         callback: function( ) { 
                                                                            frmResetPassword.slideUp(250);
                                                                            frmLogin.slideDown(250, function(){$('input').placeholder(); ResetActions(); }); 
                                                                         }
                                                                }
                                                     }
                                    });
                                } else {
                                    ShowError( frmResetPassword, 'Unable to send the password reset confirmation email' );
                                }
                            } else {
                                ShowError( frmResetPassword, output.errormessage );
                            } 
                        })
                        .fail(function( jqxhr ) {
                            ShowError( frmResetPassword, "Unable to execute request: "+jqxhr.statusText );
                        })
                        .always(function( response ) {
                            ResetActions();
                        });
                    }                    
                    return false;
                });
            });
            
            function ResetActions() {
                $(".spinner").removeClass('fa-spin');
                $(".afteraction").hide();
                $(".beforeaction").show();
            }
            
            function BeginProcessing( form ) {
                    var idStr = '#'+form.attr('id');
                    $(idStr+" .errormessage").hide();
                    $(idStr+" .beforeaction").hide();
                    $(idStr+" .afteraction").show();
                    $(idStr+" .spinner").addClass('fa-spin');
            }
            
            function ShowError( form, msg, resetactions ) {
                    if(( typeof resetactions != 'undefined' ) && (resetactions)) {
                        ResetActions();
                    }
                    var idStr = '#'+form.attr('id')+' .errormessage';
                    $(idStr+" p").text( msg );
                    $(idStr).show();
            }
            
        </script>        
    </body>
</html>