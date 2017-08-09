/*
 *  Document   : app.js
 *  Author     : pixelcave, Guido Gybels
 *  Description: Nucleus Portal Custom scripts and plugin initializations (available to all pages)
 *
 */
 
function LogIn( ) {
    var url = 'https://'+window.location.host.concat('/login.php', '?redirect=', window.location.pathname.substring(1));
    if(window.location.search.length > 0)
    {
        url = url.concat('&', window.location.search.substring(1));
    }
    if(window.location.hash.length > 0)
    {
        url = url.concat(window.location.hash);
    }
    window.location.href = url;
    return url;
}

function LogOut() {
    var url = 'https://'+window.location.host.concat('/syscall.php?do=logout');
    var jqxhr = $.get( url, function() {
    })
    .done(function( response ) {
        var url = 'https://'+window.location.host.concat('/index.php');
        window.location.href = url;
//        window.location.reload(true);
    })
    .fail(function( jqxhr ) {
        bootbox.dialog({ message: 'An error occurred while logging out: '+jqxhr.status,
                         title: '<span class="text-danger"><i class="fa fa-times-circle"></i> <strong>Error</strong></span>',
                         buttons: { main: { label: "OK",
                                            className: "btn-primary",
                                            callback: function() { }
                                    }
                         }
        });
    })
    .always(function( response ) {
    });
}

function OpenDialog( dialogname, dlgoptions) {
    var dialog;
    var options = dlgoptions;
    if( (typeof options.large === "undefined") || (!options.large) ) {
        dialog = $( '#dlgStandard' );
    } else {
        dialog = $( '#dlgLarge' );
    }
    var divContent = dialog.find('.modal-content');
    divContent.empty();
    var url = 'https://'+window.location.host.concat('/load.php?do=', dialogname);
    if( (typeof options.urlparams !== 'undefined') && ( Object.keys(options.urlparams).length > 0 )) {
        url = url.concat('&', $.param( options.urlparams ));
    }
/*    var formData;
    if( typeof options.postparams == "object" ) {
        formData = new FormData();
        for ( var key in options.postparams ) {
            formData.append(key, options.postparams[key]);
        }
    }*/
    var postData;
    if( typeof options.postparams == "object" ) {
        postData = options.postparams;
    }
    divContent.load( url, postData, function( responseText, statusText, jqxhr ) {
        InitControls( dialog );
        //console.log('dialog initialised');
        dialog.modal('show');
        if( typeof options.cbCompleted == 'function' ) {
            options.cbCompleted( dialog, jqxhr );
        } else if( typeof options.cbCompleted == 'string' ) {
            window[options.cbCompleted]( dialog, jqxhr );
        }            
    });
}

function GotoTab( objParams ) {
    if( typeof objParams == 'string') {
        $('[href=#tab-'+objParams+']').tab('show');
    } else if( typeof objParams.tab !== 'undefined' ) {
        $('[href=#tab-'+objParams.tab+']').tab('show');
    }
}

function LoadData( divID, action, invalidate, showSpinner ) {
    var params = $.param( { divid: divID, invalidate: (typeof invalidate !== 'undefined' ? invalidate : null) } );
    var url = 'https://'+window.location.host.concat('/load.php?do='+action+'&', params);
    if(( typeof showSpinner !== 'undefined' ) && showSpinner)
    {
        LoadSpinner( divID );
    }
    $( '#'+divID ).load( url, function() {
        
    } );    
    return;
}

function SaveSettings( frmID )
{
    var frmElement = $( '#'+frmID );
    $('.tooltip').not(this).hide();
    var formData = new FormData( frmElement[0] );
    $.validator.setDefaults({ ignore: ":hidden:not(.chosen-select)" });
    frmElement.validate();
    CanClose = ( frmElement.valid() );
    if( CanClose ){
        $.ajax({
            url: 'https://'+window.location.host.concat('/syscall.php?do=savesettings&form=', frmID),
            data: formData,
            processData: false,
            contentType: false,
            type: 'POST',
        })
        .done(function( ) {
            bootbox.dialog({ message: 'Your changes have been saved.',
                             title: '<span class="text-success"><i class="fa fa-check-circle"></i> <strong>Saved</strong></span>',
                             buttons: { main: { label: "OK",
                                                className: "btn-success",
                                                callback: function( ) { }
                                        }
                             }
            });
        })
        .fail(function( jqxhr ) {
            bootbox.dialog({ message: 'Not saved! An error occurred while saving your data: '+jqxhr.statusText,
                             title: '<span class="text-danger"><i class="fa fa-check-circle"></i> <strong>Error</strong></span>',
                             buttons: { main: { label: "OK",
                                                className: "btn-danger",
                                                callback: function( ) { }
                                        }
                             }
            });
        })
        .always(function ( ) {
        });
    }
    return false;
}

function LoadContent( div, url, inputoptions ) {
    var destDiv;
    var options = inputoptions;
    if ( typeof div == 'string') {
        //treat the div as an id
        destDiv = $('#'+div);
    } else if( typeof div == 'object' ) {
        destDiv = div;
    }
    //For partial loading - if a divid is given, load in there
    if ( typeof options.divid !== 'undefined' ) {
        var subDiv;
        if( typeof options.divid == 'string' ) {
            subDiv = $('#'+options.divid);
            if (subDiv.length == 0) {
                //This sub div does not yet exist
                subDiv = $( '<div/>', { id: options.divid } );
                destDiv.append( subDiv );
            }
        } else if ( typeof options.divid == 'object' ) {
            subDiv = options.divid
        }
        destDiv = subDiv;
    }
    $('.tooltip').not(this).hide();
	if ( options.hide ) {
		var hideDiv;
		if( typeof options.hide == 'string' ) {
			hideDiv = $('#'+options.hide);
			hideDiv.hide();
		} else if ( options.hide.constructor === Array ) {
			$.each( options.hide, function(index, tohide) {
				hideDiv = $('#'+tohide);
				hideDiv.hide();
			});
		} else if ( typeof options.hide == 'object' ) {
			options.hide.hide();
		}
	}
    if( options.spinner ) {
        destDiv.empty();
        var spSize = 5;
        if( typeof options.spinnersize == 'number' ) {
            spSize = options.spinnersize;
        }
        //console.log( spSize );
        destDiv.html( "<p class=\"text-center text-primary pull-down\"><i class=\"fa fa-spinner fa-"+spSize.toString()+"x fa-spin\"></i></p>" );
    }
    var src = url;
    if( typeof options.urlparams == 'object' ) {
        var arr = url.split('?');
        var sep = '?';
        if (url.length > 1 && arr[1] !== '') {
            //url already has parameters
            sep = '&';
        }
        src = src.concat(sep, $.param(options.urlparams) );
    }
    destDiv.load( src, function( responseText, statusText, jqxhr ) {
        if(  jqxhr.status == 200 ) {
            InitControls( destDiv );
/*            if ( ( typeof options.divid == 'undefined' ) && ( (typeof options.nopagetitle === "undefined") || (!options.nopagetitle) ) ) {
                var pageTitle = $('#stdTitleBlock').find('h2').text();
                if( pageTitle.length > 0 ) {
                    $('head title', window.parent.document).text( pageTitle );
                }
            }*/
            if( typeof options.cbSuccess == 'function' ) {
                options.cbSuccess( destDiv );
            } else if( typeof options.cbSuccess == 'string' ) {
                window[options.cbSuccess]( destDiv );
            }            
        } else {
            if( typeof options.cbError == 'function' ) {
                options.cbError( destDiv, jqxhr );
            } else if( typeof options.cbError == 'string' ) {
                window[options.cbError]( destDiv, jqxhr );
            }
        }
        if( typeof options.cbCompleted == 'function' ) {
            options.cbCompleted( destDiv, jqxhr );
        } else if( typeof options.cbCompleted == 'string' ) {
            window[options.cbCompleted]( destDiv, jqxhr );
        }
    });
}

function dlgDataSaved( msgText )
{
    bootbox.dialog({ message: (typeof msgText !== 'undefined' ? msgText : 'Your changes have been saved.'),
                     title: '<span class="text-success"><i class="fa fa-check-circle"></i> <strong>Saved</strong></span>',
                     buttons: { main: { label: "OK",
                                        className: "btn-success",
                                        callback: function( ) { }
                                }
                     }
    });
}

function dlgDataError( data )
{
    bootbox.dialog({ message: (typeof data == 'string' ? (data.length > 0 ? data : 'An unknown error occurred while processing your request.') : 'Not saved! An error occurred while saving your data'+(data && data.promise ? ': '+data.statusText : '.')),
                     title: '<span class="text-danger"><i class="fa fa-times-circle"></i> <strong>Error</strong></span>',
                     buttons: { main: { label: "OK",
                                        className: "btn-danger",
                                        callback: function( ) { }
                                }
                     }
    });
}

function confirmExecSyscall( title, message, url, execoptions ) {
    bootbox.dialog({ message: (typeof message == 'string' ? message : 'Are you sure?'),
                     title: title,
                     buttons: { Cancel: {
                                    label: "Cancel",
                                    className: "btn-default",
                                    callback: function( ) { }
                                },
                                OK: {
                                    label: "OK",
                                    className: "btn-danger",
                                    callback: function( ) { execSyscall( url, execoptions); }
                                },
                     }
    });
}

function execSyscall( url, execoptions ) {
    var options = execoptions;
    $('.tooltip').not(this).hide();
    var target = url;
    if( typeof options.urlparams == 'object' ) {
        var arr = url.split('?');
        var sep = '?';
        if (url.length > 1 && arr[1] !== '') {
            //url already has parameters
            sep = '&';
        }
        target = target.concat(sep, $.param(options.urlparams) );
    }
    var formData;
    if( typeof options.formdata == 'object' ) {
        formData = options.formdata;
    } else {
        formData = new FormData();
        
    }
    if( typeof options.postparams == 'object' ) {
        for ( var key in options.postparams ) {
            formData.append(key, options.postparams[key]);
        }
    }
    $.ajax({
        url: target,
        data: formData,
        processData: false,
        contentType: false,
        type: 'POST',
    })    
    .done(function( response ) {
        if( (typeof options.parseJSON !== 'undefined')  && options.parseJSON == true ) {
            var jsonResponse = jQuery.parseJSON( response );
            if( jsonResponse.success ) {
                if( typeof options.cbSuccess == 'function' ) {
                    options.cbSuccess( jsonResponse );
                } else if( typeof options.cbSuccess == 'string' ) {
                    window[options.cbSuccess]( jsonResponse );
                }
                if( (typeof options.defSuccessDlg !== 'undefined')  && (options.defSuccessDlg == true) ) {
                    dlgDataSaved('The operation has been completed.');
                }
            } else {
                if( typeof options.cbError == 'function' ) {
                    options.cbError( jsonResponse );
                } else if( typeof options.cbError == 'string' ) {
                    window[options.cbError]( jsonResponse );
                }
                if( (typeof options.defErrorDlg !== 'undefined')  && (options.defErrorDlg == true) ) {
                    dlgDataError( jsonResponse.errormessage );
                }
            }
        } else {
            if( typeof options.cbSuccess == 'function' ) {
                options.cbSuccess( response );
            } else if( typeof options.cbSuccess == 'string' ) {
                window[options.cbSuccess]( response );
            }
            if( (typeof options.defSuccessDlg !== 'undefined')  && (options.defSuccessDlg == true) ) {
                dlgDataSaved('The operation has been completed.');
            }
        }    
    })
    .fail(function( jqxhr ) {
        if( typeof options.cbError == 'function' ) {
            options.cbError( jqxhr );
        } else if( typeof options.cbError == 'string' ) {
            window[options.cbError]( jqxhr );
        }
            if( (typeof options.defErrorDlg !== 'undefined')  && (options.defErrorDlg == true) ) {
                dlgDataError( 'Your request raised an error: '+jqxhr.statusText ); 
        }
    })
    .always(function() {
        if( typeof options.cbCompleted == 'function' ) {
            options.cbCompleted( );
        } else if( typeof options.cbCompleted == 'string' ) {
            window[options.cbCompleted]( );
        }
    });
    if( typeof options.cbPosted == 'function' ) {
        options.cbPosted( options );
    } else if( typeof options.cbPosted == 'string' ) {
        window[options.cbPosted]( options );
    }
}

/*options
    urlparams
    postparams
    parseJSON (true/false) - look for value of "success" in response (and errormessage if success = false)
    cbSuccess (either a function or the name of a function)
    cbError (either a function or the name of a function)
    cbCompleted (either a function or the name of a function)
    cbPosted (either a function or the name of a function)
    defSuccessDlg (if true shows a standard dialogue message that the data has been saved)
    defErrorDlg (if true shows a standard dialogue message that an error has occurred)
    receiver (if url is empty, the formdata is sent to the received function instead)
*/
function submitForm( form, url, submitoptions ) {
    var frmElement;
    var options = submitoptions;
    if ( typeof form == 'string') {
        //treat the form as an id
        frmElement = $('#'+form);
    } else if( typeof form == 'object' ) {
        frmElement = form;
    }
    $('.tooltip').not(this).hide();
    var frmSpinner = frmElement.find('.formSpinner');
    //Validate the form (or skip if options.validate = 'none' )
    var CanClose = false;
    if( typeof options.validate == 'string' ) {
        switch( options.validate ) {
            case 'none':
                CanClose = true;
                break;
            case 'default':
                $.validator.setDefaults({ ignore: ":hidden:not(.select-chosen)" });
                frmElement.validate();
                CanClose = ( frmElement.valid() );            
                break;
            default: //treat string as a function name
                CanClose = window[options.validate]( frmElement );
        }
    } else if (typeof options.validate == 'function' ) {
        $.validator.setDefaults({ ignore: ":hidden:not(.select-chosen)" });
        frmElement.validate();
        if( frmElement.valid() ) {
            CanClose = options.validate( frmElement );
        }
    } else {
        $.validator.setDefaults({ ignore: ":hidden:not(.chosen-select)" });
        frmElement.validate();
        CanClose = ( frmElement.valid() );
    }
    if( CanClose ){
        if( frmSpinner.length > 0 ) {
            frmSpinner.addClass('fa-spin');
            frmSpinner.css('visibility','visible');
        }
        //is there a modal dialogue to close?
        if( (typeof options.modal !== 'undefined')  && options.modal ) {
            frmElement.closest('div.modal').modal('hide');
        }
        //Post the form to the URL given
        var formData = new FormData( frmElement[0] );
        var target = url;
        if( typeof options.urlparams == 'object' ) {
            var arr = url.split('?');
            var sep = '?';
            if (url.length > 1 && arr[1] !== '') {
                //url already has parameters
                sep = '&';
            }
            target = target.concat(sep, $.param(options.urlparams) );
        }
        if( typeof options.postparams == 'object' ) {
            for ( var key in options.postparams ) {
                formData.append(key, options.postparams[key]);
            }
        }
        if ( target.length > 0 ) {
            $.ajax({
                url: target,
                data: formData,
                processData: false,
                contentType: false,
                type: 'POST',
            })
            .done(function( response ) {
                if( (typeof options.parseJSON !== 'undefined')  && options.parseJSON == true ) {
                    var jsonResponse = jQuery.parseJSON( response );
                    if( jsonResponse.success ) {
                        if( typeof options.cbSuccess == 'function' ) {
                            options.cbSuccess( frmElement, jsonResponse );
                        } else if( typeof options.cbSuccess == 'string' ) {
                            window[options.cbSuccess]( frmElement, jsonResponse );
                        }
                        if( (typeof options.defSuccessDlg !== 'undefined')  && (options.defSuccessDlg == true) ) {
                            dlgDataSaved();
                        }
                    } else {
                        if( typeof options.cbError == 'function' ) {
                            options.cbError( frmElement, jsonResponse );
                        } else if( typeof options.cbError == 'string' ) {
                            window[options.cbError]( frmElement, jsonResponse );
                        }
                        if( (typeof options.defErrorDlg !== 'undefined')  && (options.defErrorDlg == true) ) {
                            dlgDataError( jsonResponse.errormessage );
                        }
                    }
                } else {
                    if( typeof options.cbSuccess == 'function' ) {
                        options.cbSuccess( frmElement, response );
                    } else if( typeof options.cbSuccess == 'string' ) {
                        window[options.cbSuccess]( frmElement, response );
                    }
                    if( (typeof options.defSuccessDlg !== 'undefined')  && (options.defSuccessDlg == true) ) {
                        dlgDataSaved();
                    }
                }
            })
            .fail(function( jqxhr ) {
                if( typeof options.cbError == 'function' ) {
                    options.cbError( frmElement, jqxhr );
                } else if( typeof options.cbError == 'string' ) {
                    window[options.cbError]( frmElement, jqxhr );
                }
                if( (typeof options.defErrorDlg !== 'undefined')  && (options.defErrorDlg == true) ) {
                    dlgDataError( jqxhr );
                }
            })
            .always(function ( ) {
                if( typeof options.cbCompleted == 'function' ) {
                    options.cbCompleted( frmElement );
                } else if( typeof options.cbCompleted == 'string' ) {
                    window[options.cbCompleted]( frmElement );
                }
                if( frmSpinner ) {
                    frmSpinner.css('visibility','hidden');
                    frmSpinner.removeClass('fa-spin');
                }
            });
        } else {
            if(typeof options.receiver == 'function' ) {
                //Send the form data to a function
                options.receiver( frmElement, formData );
            } else if(typeof options.receiver == 'string') {
                window[options.receiver]( frmElement, formData );
            }
            if( frmSpinner ) {
                frmSpinner.css('visibility','hidden');
                frmSpinner.removeClass('fa-spin');
            }
        }
        if( typeof options.cbPosted == 'function' ) {
            options.cbPosted( frmElement );
        } else if( typeof options.cbPosted == 'string' ) {
            window[options.cbPosted]( frmElement );
        }
    }
    return false;
}

function ValidateBankForm( frmElement, voptions ) {
    var options = voptions;  
    var frmSpinner = frmElement.find('.formSpinner');
    if( frmSpinner.length > 0 ) {
        frmSpinner.addClass('fa-spin');
        frmSpinner.css('visibility','visible');
    }
    var inputSortCode = frmElement.find("[name='SortCode']");
    var inputAccountNo = frmElement.find("[name='AccountNo']");
    var inputBankName = frmElement.find("[name='BankName']");
    var validation = ValidateAccount( frmElement, { method: options.method, apikey: options.apikey, account: inputAccountNo, sortcode: inputSortCode } );
    if(validation.valid) {
        //inputSortCode.attr('value', validation.sortcode);
        inputSortCode.val(validation.sortcode);
        //inputAccountNo.attr('value', validation.account);
        inputAccountNo.val(validation.account);
        //inputBankName.attr('value', validation.bank);
        inputBankName.val(validation.bank);
        if( !validation.directdebitcapable ) {
            $('#formErrors p').text('The account and sort code are valid, but this bank account cannot be used for Direct Debit.');
            $('#formErrors').show();
            validation.valid = false;
        }
        var inputAgreed = frmElement.find("[name='SortCode']");
        if ( !frmElement.find("[name='Agreed']").is( ":checked" ) ) {
            $('#formErrors p').text('You must activate the switch to indicate that you have read and understood this declaration and are authorised to submit it.');
            $('#formErrors').show();
            validation.valid = false;
        }
    } else {
            $('#formErrors p').text(validation.errormessage);
            $('#formErrors').show();
    };
    if( frmSpinner ) {
        frmSpinner.css('visibility','hidden');
        frmSpinner.removeClass('fa-spin');
    }
    return validation.valid;
}

function ValidateAccount( frmElement, request ) {
    var response = {
        valid: false,
        errormessage: 'Unable to execute: input parameters are incorrect',
        account: '',
        sortcode: '',
        bank: '',
        directdebitcapable: false,
    };
    var inputSortCode, inputAccountNo;
    if ( typeof request.account == 'string') {
        //Look for a control with this name
        inputAccountNo = frmElement.find("[name='"+request.account+"']");
    } else if( typeof request.account == 'object' ) {
        inputAccountNo = request.account;
    }
    if ( typeof request.sortcode == 'string') {
        //Look for a control with this name
        inputSortCode = frmElement.find("[name='"+request.sortcode+"']");
    } else if( typeof request.sortcode == 'object' ) {
        inputSortCode = request.sortcode;
    }
    if(( typeof request.method !== 'undefined') && ( typeof inputAccountNo !== 'undefined' ) && ( typeof inputSortCode !== 'undefined' )) {
        response.account = inputAccountNo.val();
        response.sortcode = inputSortCode.val();
        switch( request.method ) {
            case 'pcapredict':
                if( request.apikey.length > 0 ) {
                    //var url = "https://services.postcodeanywhere.co.uk/BankAccountValidation/Interactive/Validate/v1.00/json3.ws?";
                    var url = "https://services.postcodeanywhere.co.uk/BankAccountValidation/Interactive/Validate/v2.00/json3.ws?";
                    url = url.concat( $.param( { Key: request.apikey, AccountNumber: response.account, SortCode: response.sortcode } ) );
                    $.ajax({
                        type: 'GET',
                        url: url,
                        dataType: 'json',
                        async: false
                    })
                    .done(function( data ) {
                        if ( data.Items.length == 1 && typeof(data.Items[0].Error) != "undefined" ) {
                            response.errormessage = 'The sort code and account number failed validation: '+data.Items[0].Description;
                        } else if ( (data.Items.length == 1) && (data.Items[0].IsCorrect) ) {
                            response.account  = data.Items[0].CorrectedAccountNumber;
                            response.sortcode = data.Items[0].CorrectedSortCode;
                            response.bank = data.Items[0].Bank;
                            response.directdebitcapable = data.Items[0].IsDirectDebitCapable;
                            response.valid = true;
                            response.errormessage = '';
                        } else {
                            response.errormessage = 'The sort code and account number failed validation. Please correct your entry.';
                        }
                    })
                    .fail(function( jqxhr ) {
                        response.errormessage = 'Error during validation: '+jqxhr.statusText;
                    });
                } else {
                    response.errormessage = 'The API key is missing';
                }
                break;
            default:
                response.errormessage = 'There is no available validation method';
            
        }
    }
    return response;
}

//options:
//  maxuploadsize
//  postparams
//  cbSuccess
function AddUploadHandler( input, options ) {
    var inputControl;
    var settings = options
    if ( typeof input == 'string') {
        //treat the input parameter as an id
        inputControl = $('#'+input);
    } else if( typeof input == 'object' ) {
        inputControl = input;
    }
    $(inputControl).on('change', function() {
        var file = this.files[0];
        if ( file.size <= settings.maxuploadsize ) {
            bootbox.prompt({ 
                title: "Enter a title for the file you are uploading:", 
                callback: function( description ){
                    var formData = new FormData();
                    if( typeof settings.postparams == 'object' ) {
                        for ( var key in settings.postparams ) {
                            formData.append(key, settings.postparams[key]);
                        }
                    }
                    formData.append('File', file);
                    if( (description !== null) && (description.length > 0) ) {
                        formData.append('DocTitle', description);
                    }
                    $.ajax({
                        url: 'https://'+window.location.host.concat('/syscall.php?do=uploadfile'),
                        data: formData,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                    })
                    .done(function( response ) {
                        if( typeof settings.cbSuccess == 'function' ) {
                            settings.cbSuccess( input );
                        } else if( typeof settings.cbSuccess == 'string' ) {
                            window[settings.cbSuccess]( input );
                        }
                    })
                    .fail(function( jqxhr ) {
                        bootbox.dialog({ message: 'An error occurred while uploading the file: '+jqxhr.statusText,
                                         title: '<span class="text-danger"><i class="fa fa-times-circle"></i> <strong>Error</strong></span>',
                                         buttons: { main: { label: "OK",
                                                            className: "btn-primary",
                                                            callback: function( ) { }
                                                    }
                                         }
                        });  
                    });
                }
            })            
        }
    });
}

function InitControls( div ) {
    //Tabs, tooltips and popovers
    div.find('[data-toggle="tabs"] a, .enable-tabs a').click(function(e){ e.preventDefault(); $(this).tab('show'); });
    div.find('[data-toggle="tooltip"], .enable-tooltip').tooltip({container: 'body', animation: false});
    div.find('[data-toggle="popover"], .enable-popover').popover({container: 'body', animation: true});
    //Ajax loaded popovers
    $('*[data-poload]').popover({
        html: true,
        trigger: 'manual',
        content: function() {
            var item = $(this);
            var div_id =  "tmp-id-" + $.now();
            return loadPopover( item, div_id );
        }
    }).click(function() {
        $(this).popover('toggle');
    });    
    // Initialize Editor
    div.find('.textarea-editor').wysihtml5({
        "font-styles": false,
        "emphasis": true, //Italics, bold, etc.
        "lists": true, //(Un)ordered lists, e.g. Bullets, Numbers.
        "html": true, //Button which allows you to edit the generated HTML.
        "link": true, //Button to insert a link.
        "image": false, //Button to insert an image.
        "color": false //Button to change color of font  
    });
    // Initialize Chosen, Bootstrap slider, Tags Input, placeholders
    div.find('.select-chosen').chosen({width: "100%", allow_single_deselect: true});
    div.find('.input-slider').slider();
    div.find('.input-tags').tagsInput({ width: 'auto', height: 'auto'});
    div.find('input, textarea').placeholder();            
    // Initialize Datepicker
    div.find('.input-datepicker, .input-daterange').datepicker({weekStart: 1});
    div.find('.input-datepicker-close').datepicker({weekStart: 1}).on('changeDate', function(e){ $(this).datepicker('hide'); });
    // Initialize Timepicker
    div.find('.input-timepicker').timepicker({minuteStep: 1,showSeconds: false,showMeridian: true});
    div.find('.input-timepicker24').timepicker({minuteStep: 1,showSeconds: false,showMeridian: false});
    //Form submission spinner
    div.find('.formSpinner').css('visibility','hidden');
    div.find( '.agetext' ).each(function() { EnableAgeText( $(this) ) });
    div.find( '.autoenable' ).each(function() { AddonBtnAutoEnable( $(this), false ) });
    div.find( '.autoenablevalidated' ).each(function() { AddonBtnAutoEnable( $(this), true ) });

    // Toggle block's content
    div.find( '[data-toggle="block-toggle-content"]' ).on('click', function(){
        var blockContent = $(this).closest('.block').find('.block-content');
        if ($(this).hasClass('active')) {
            blockContent.slideDown(125);
        } else {
            blockContent.slideUp(125);
        }
        $(this).toggleClass('active');
    });
    // Toggle block fullscreen
    div.find('[data-toggle="block-toggle-fullscreen"]').on('click', function(){
        var block = $(this).closest('.block');
        if ($(this).hasClass('active')) {
            block.removeClass('block-fullscreen');
        } else {
            block.addClass('block-fullscreen');
        }
        $(this).toggleClass('active');
    });
    // Hide block
    div.find('[data-toggle="block-hide"]').on('click', function(){
        $(this).closest('.block').fadeOut();
    });
    
    // Easy Pie Chart
    div.find('.pie-chart').easyPieChart({
        barColor: $(this).data('bar-color') ? $(this).data('bar-color') : '#777777',
        trackColor: $(this).data('track-color') ? $(this).data('track-color') : '#eeeeee',
        lineWidth: $(this).data('line-width') ? $(this).data('line-width') : 3,
        size: $(this).data('size') ? $(this).data('size') : '80',
        animate: 800,
        scaleColor: false
    });    
    
    return true;
}

function LoadSpinner( divID ) {
    var parentDiv = $( '#'+divID );
    parentDiv.empty();
    parentDiv.html( "<p class=\"text-center text-primary pull-down\"><i class=\"fa fa-spinner fa-5x fa-spin\"></i></p>" );
}

function LoadNotifications( silent ) {
    var issilent = ( (typeof silent !== 'undefined')  && ( silent == true ) );
    var iconNotifications = $( '#shNotifications' ).find('a i');
    iconNotifications.addClass('fa-spin');
    $( '#shNotifications' ).find('a').blur();
    var parentDiv = $( '#sbalerts' );
    if( parentDiv.length > 0 ) {
        //the ts parameter is solely intended to avoid caching of the result
        var date = new Date();
        var url = 'https://'+window.location.host.concat('/load.php?do=notifications', '&ts=', date.getTime());
        parentDiv.load( url, function() {
            InitControls( parentDiv );
            if ( !issilent && (parentDiv.find('.alert-notify').length > 0) ) {
                Notification();
            }
            setTimeout(function() {
                iconNotifications.removeClass('fa-spin');
            }, 750);
        });
    }
}

var App = function() {

    /* Cache variables of some often used jquery objects */
    var page            = $('#page-container');
    var pageContent     = $('#page-content');
    var header          = $('header');
    var footer          = $('#page-content + footer');

    /* Sidebar */
    var sidebar         = $('#sidebar');
    var sidebarAlt      = $('#sidebar-alt');
    var sScroll         = $('.sidebar-scroll');

    /* Initialization UI Code */
    var uiInit = function() {
        //Replace built-in URL validator with version that prepends the https protocol if this is missing
        $.validator.methods.url = function(t, e) {
            if(!/^(https?|ftp):\/\//i.test(t)) {
                t = 'https://'+t;
            }
            return this.optional(e)||/^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(t);
        }

        $.validator.addMethod("valueNotEmptyOrEqual", function(value, element, arg){
            return (value) && (value.trim().length > 0) && (value.toUpperCase() != arg.trim().toUpperCase());
        }, 'Please select the correct value');
    
		$('body').on('hidden.bs.modal', '.modal', function () {
			$(this).removeData('bs.modal');
		});
        
/*        $('#inputQuickSearch').focus(function() {
            $(this).prev('.input-group-addon.search').addClass('themed-border');
        });

        $('#inputQuickSearch').blur(function() {
            $(this).prev('.input-group-addon.search').removeClass('themed-border');
        });*/

        //Inputs with showage, autoenable enabled
        $( '.agetext' ).each(function() { EnableAgeText( $(this) ) });
        $( '.autoenable' ).each(function() { AddonBtnAutoEnable( $(this), false ) });
        $( '.autoenablevalidated' ).each(function() { AddonBtnAutoEnable( $(this), true ) });
        
        // Initialize sidebars functionality
        handleSidebar('init');

        // Sidebar navigation functionality
        handleNav();

        // Interactive blocks functionality
        interactiveBlocks();

        // Scroll to top functionality
        scrollToTop();

        // Resize #page-content to fill empty space if exists (also add it to resize and orientationchange events)
        resizePageContent();
        $(window).resize(function(){ resizePageContent(); });
        $(window).bind('orientationchange', resizePageContent);

        // Initialize tabs
        $('[data-toggle="tabs"] a, .enable-tabs a').click(function(e){ e.preventDefault(); $(this).tab('show'); });

        uiGeneral();
        
        $('#dlgLarge').on('hidden.bs.modal', function ( e ) {
            $(this).removeData('bs.modal');
            $('#dlgLarge').find('.modal-content').empty();
        })

        $('#dlgStandard').on('hidden.bs.modal', function ( e ) {
            $(this).removeData('bs.modal');
            $('#dlgStandard').find('.modal-content').empty();
        })
        
        // Initialize single image lightbox
        $('[data-toggle="lightbox-image"]').magnificPopup({type: 'image', image: {titleSrc: 'title'}});

        // Initialize image gallery lightbox
        $('[data-toggle="lightbox-gallery"]').magnificPopup({
            delegate: 'a.gallery-link',
            type: 'image',
            gallery: {
                enabled: true,
                navigateByImgClick: true,
                arrowMarkup: '<button type="button" class="mfp-arrow mfp-arrow-%dir%" title="%title%"></button>',
                tPrev: 'Previous',
                tNext: 'Next',
                tCounter: '<span class="mfp-counter">%curr% of %total%</span>'
            },
            image: {titleSrc: 'title'}
        });

        // Easy Pie Chart
        $('.pie-chart').easyPieChart({
            barColor: $(this).data('bar-color') ? $(this).data('bar-color') : '#777777',
            trackColor: $(this).data('track-color') ? $(this).data('track-color') : '#eeeeee',
            lineWidth: $(this).data('line-width') ? $(this).data('line-width') : 3,
            size: $(this).data('size') ? $(this).data('size') : '80',
            animate: 800,
            scaleColor: false
        });

    };

    /* Sidebar Navigation functionality */
    var handleNav = function() {

        // Animation Speed, change the values for different results
        var upSpeed     = 125;
        var downSpeed   = 125;

        // Get all vital links
        var allTopLinks     = $('.sidebar-nav a');
        var menuLinks       = $('.sidebar-nav-menu');
        var submenuLinks    = $('.sidebar-nav-submenu');

        // Primary Accordion functionality
        menuLinks.click(function(){
            var link = $(this);

            if (link.parent().hasClass('active') !== true) {
                if (link.hasClass('open')) {
                    link.removeClass('open').next().slideUp(upSpeed);

                    // Resize #page-content to fill empty space if exists
                    setTimeout(resizePageContent, upSpeed);
                }
                else {
                    $('.sidebar-nav-menu.open').removeClass('open').next().slideUp(upSpeed);
                    link.addClass('open').next().slideDown(downSpeed);

                    // Resize #page-content to fill empty space if exists
                    setTimeout(resizePageContent, ((upSpeed > downSpeed) ? upSpeed : downSpeed));
                }
            }

            return false;
        });
        
        $('#menurecentitems').click();

        // Submenu Accordion functionality
        submenuLinks.click(function(){
            var link = $(this);

            if (link.parent().hasClass('active') !== true) {
                if (link.hasClass('open')) {
                    link.removeClass('open').next().slideUp(upSpeed);

                    // Resize #page-content to fill empty space if exists
                    setTimeout(resizePageContent, upSpeed);
                }
                else {
                    link.closest('ul').find('.sidebar-nav-submenu.open').removeClass('open').next().slideUp(upSpeed);
                    link.addClass('open').next().slideDown(downSpeed);

                    // Resize #page-content to fill empty space if exists
                    setTimeout(resizePageContent, ((upSpeed > downSpeed) ? upSpeed : downSpeed));
                }
            }

            return false;
        });
    };

    /* General UI Functionality, also loaded by subsequent modal forms */
    var uiGeneral = function() {
        // Initialize Tooltips
        $('[data-toggle="tooltip"], .enable-tooltip').tooltip({container: 'body', animation: false});

        // Initialize Popovers
        $('[data-toggle="popover"], .enable-popover').popover({container: 'body', animation: true});
        //Ajax loaded popovers
        $('*[data-poload]').popover({
            html: true,
            trigger: 'manual',
            content: function() {
                var item = $(this);
                var div_id =  "tmp-id-" + $.now();
                return loadPopover( item, div_id );
            }
        }).click(function() {
            $(this).popover('toggle');
        });

           // Initialize Editor
        $('.textarea-editor:visible').wysihtml5({
            "font-styles": false,
            "emphasis": true, //Italics, bold, etc.
            "lists": true, //(Un)ordered lists, e.g. Bullets, Numbers.
            "html": true, //Button which allows you to edit the generated HTML.
            "link": true, //Button to insert a link.
            "image": false, //Button to insert an image.
            "color": false //Button to change color of font  
        });

        // Initialize Chosen
        $('.select-chosen').chosen({width: "100%", allow_single_deselect: true});

        // Initialize Slider for Bootstrap
        $('.input-slider').slider();

        // Initialize Tags Input
        $('.input-tags').tagsInput({ width: 'auto', height: 'auto'});

        // Initialize Datepicker
        $('.input-datepicker, .input-daterange').datepicker({weekStart: 1});
        $('.input-datepicker-close').datepicker({weekStart: 1}).on('changeDate', function(e){ $(this).datepicker('hide'); });

        // Initialize Timepicker
        $('.input-timepicker').timepicker({minuteStep: 1,showSeconds: false,showMeridian: true});
        $('.input-timepicker24').timepicker({minuteStep: 1,showSeconds: false,showMeridian: false});

        // Initialize Placeholder
        $('input, textarea').placeholder();
	   
	   //Start Carousels
	   $('.carousel').carousel('cycle');
    };
    
    /* Sidebar Functionality */
    var handleSidebar = function(mode, extra) {
        if (mode === 'init') {
            // Init sidebars scrolling (if we have a fixed header)
            if (header.hasClass('navbar-fixed-top') || header.hasClass('navbar-fixed-bottom')) {
                handleSidebar('sidebar-scroll');
            }

            // Close the other sidebar if we hover over a partial one
            // In smaller screens (the same applies to resized browsers) two visible sidebars
            // could mess up our main content (not enough space), so we hide the other one :-)
            $('.sidebar-partial #sidebar')
                .mouseenter(function(){ handleSidebar('close-sidebar-alt'); });
            $('.sidebar-alt-partial #sidebar-alt')
                .mouseenter(function(){ handleSidebar('close-sidebar'); });
        } else {
            var windowW = window.innerWidth
                        || document.documentElement.clientWidth
                        || document.body.clientWidth;

            if (mode === 'toggle-sidebar') {
                if ( windowW > 991) { // Toggle main sidebar in large screens (> 991px)
                    page.toggleClass('sidebar-visible-lg');

                    if (page.hasClass('sidebar-visible-lg')) {
                        handleSidebar('close-sidebar-alt');
                    }

                    // If 'toggle-other' is set, open the alternative sidebar when we close this one
                    if (extra === 'toggle-other') {
                        if (!page.hasClass('sidebar-visible-lg')) {
                            handleSidebar('open-sidebar-alt');
                        }
                    }
                } else { // Toggle main sidebar in small screens (< 992px)
                    page.toggleClass('sidebar-visible-xs');

                    if (page.hasClass('sidebar-visible-xs')) {
                        handleSidebar('close-sidebar-alt');
                    }
                }
            } else if (mode === 'toggle-sidebar-alt') {
                if ( windowW > 991) { // Toggle alternative sidebar in large screens (> 991px)
                    page.toggleClass('sidebar-alt-visible-lg');

                    if (page.hasClass('sidebar-alt-visible-lg')) {
                        handleSidebar('close-sidebar');
                    }

                    // If 'toggle-other' is set open the main sidebar when we close the alternative
                    if (extra === 'toggle-other') {
                        if (!page.hasClass('sidebar-alt-visible-lg')) {
                            handleSidebar('open-sidebar');
                        }
                    }
                } else { // Toggle alternative sidebar in small screens (< 992px)
                    page.toggleClass('sidebar-alt-visible-xs');

                    if (page.hasClass('sidebar-alt-visible-xs')) {
                        handleSidebar('close-sidebar');
                    }
                }
            }
            else if (mode === 'open-sidebar') {
                if ( windowW > 991) { // Open main sidebar in large screens (> 991px)
                    page.addClass('sidebar-visible-lg');
                } else { // Open main sidebar in small screens (< 992px)
                    page.addClass('sidebar-visible-xs');
                }

                // Close the other sidebar
                handleSidebar('close-sidebar-alt');
            }
            else if (mode === 'open-sidebar-alt') {
                if ( windowW > 991) { // Open alternative sidebar in large screens (> 991px)
                    page.addClass('sidebar-alt-visible-lg');
                } else { // Open alternative sidebar in small screens (< 992px)
                    page.addClass('sidebar-alt-visible-xs');
                }

                // Close the other sidebar
                handleSidebar('close-sidebar');
            }
            else if (mode === 'close-sidebar') {
                if ( windowW > 991) { // Close main sidebar in large screens (> 991px)
                    page.removeClass('sidebar-visible-lg');
                } else { // Close main sidebar in small screens (< 992px)
                    page.removeClass('sidebar-visible-xs');
                }
            }
            else if (mode === 'close-sidebar-alt') {
                if ( windowW > 991) { // Close alternative sidebar in large screens (> 991px)
                    page.removeClass('sidebar-alt-visible-lg');
                } else { // Close alternative sidebar in small screens (< 992px)
                    page.removeClass('sidebar-alt-visible-xs');
                }
            }
            else if (mode == 'sidebar-scroll') { // Init sidebars scrolling
                if (sScroll.length && (!sScroll.parent('.slimScrollDiv').length)) {
                    // Initialize Slimscroll plugin on both sidebars
                    sScroll.slimScroll({ height: $(window).height(), color: '#fff', size: '3px', touchScrollStep: 100 });

                    // Resize sidebars scrolling height on window resize or orientation change
                    $(window).resize(sidebarScrollResize);
                    $(window).bind('orientationchange', sidebarScrollResizeOrient);
                }
            }
        }

        return false;
    };

    // Sidebar Scrolling Resize Height on window resize and orientation change
    var sidebarScrollResize         = function() { sScroll.css('height', $(window).height()); };
    var sidebarScrollResizeOrient   = function() { setTimeout(sScroll.css('height', $(window).height()), 500); };

    /* Resize #page-content to fill empty space if exists */
    var resizePageContent = function() {
        var windowH         = $(window).height();
        var sidebarH        = sidebar.outerHeight();
        var sidebarAltH     = sidebarAlt.outerHeight();
        var headerH         = header.outerHeight();
        var footerH         = footer.outerHeight();

        // If we have a fixed sidebar/header layout or each sidebars’ height < window height
        if (header.hasClass('navbar-fixed-top') || header.hasClass('navbar-fixed-bottom') || ((sidebarH < windowH) && (sidebarAltH < windowH))) {
            if (page.hasClass('footer-fixed')) { // if footer is fixed don't remove its height
                pageContent.css('min-height', windowH - headerH + 'px');
            } else { // else if footer is static, remove its height
                pageContent.css('min-height', windowH - (headerH + footerH) + 'px');
            }
        }  else { // In any other case set #page-content height the same as biggest sidebar's height
            if (page.hasClass('footer-fixed')) { // if footer is fixed don't remove its height
                pageContent.css('min-height', ((sidebarH > sidebarAltH) ? sidebarH : sidebarAltH) - headerH + 'px');
            } else { // else if footer is static, remove its height
                pageContent.css('min-height', ((sidebarH > sidebarAltH) ? sidebarH : sidebarAltH) - (headerH + footerH) + 'px');
            }
        }
    };

    /* Interactive blocks functionality */
    var interactiveBlocks = function() {

        // Toggle block's content
        $('[data-toggle="block-toggle-content"]').on('click', function(){
            var blockContent = $(this).closest('.block').find('.block-content');

            if ($(this).hasClass('active')) {
                blockContent.slideDown(125);
            } else {
                blockContent.slideUp(125);
            }

            $(this).toggleClass('active');
        });

        // Toggle block fullscreen
        $('[data-toggle="block-toggle-fullscreen"]').on('click', function(){
            var block = $(this).closest('.block');

            if ($(this).hasClass('active')) {
                block.removeClass('block-fullscreen');
            } else {
                block.addClass('block-fullscreen');
            }

            $(this).toggleClass('active');
        });

        // Hide block
        $('[data-toggle="block-hide"]').on('click', function(){
            $(this).closest('.block').fadeOut();
        });
    };

    /* Scroll to top functionality */
    var scrollToTop = function() {
        // Get link
        var link = $('#to-top');
        var windowW = window.innerWidth
                        || document.documentElement.clientWidth
                        || document.body.clientWidth;

        $(window).scroll(function() {
            // If the user scrolled a bit (150 pixels) show the link in large resolutions
            if (($(this).scrollTop() > 150) && (windowW > 991)) {
                link.fadeIn(100);
            } else {
                link.fadeOut(100);
            }
        });

        // On click get to top
        link.click(function() {
            $('html, body').animate({scrollTop: 0}, 400);
            return false;
        });
    };

    /* Datatables Basic Bootstrap integration (pagination integration included under the Datatables plugin in plugins.js) */
    var dtIntegration = function() {
        $.extend(true, $.fn.dataTable.defaults, {
            "sDom": "<'row'<'col-sm-6 col-xs-5'l><'col-sm-6 col-xs-7'f>r>t<'row'<'col-sm-5 hidden-xs'i><'col-sm-7 col-xs-12 clearfix'p>>",
            "sPaginationType": "bootstrap",
            "oLanguage": {
                "sLengthMenu": "_MENU_",
                "sSearch": "<div class=\"input-group\"><span class=\"input-group-addon\"><i class=\"fa fa-search\"></i></span>_INPUT_<span class=\"input-group-btn\"><button class=\"btn btn-primary\" type=\"button\" onclick=\"    RefreshDataTable($(this).closest('.dataTables_wrapper').find('table').dataTable());\"><i class=\"fa fa-refresh\"></i></button></span></div>",
                "sInfo": "<strong>_START_</strong>-<strong>_END_</strong> of <strong>_TOTAL_</strong>",
                "oPaginate": {
                    "sPrevious": "",
                    "sNext": ""
                }
            }
        });
    };

    return {
        init: function() {
            uiInit(); // Initialize UI Code
        },
        sidebar: function(mode, extra) {
            handleSidebar(mode, extra); // Handle sidebars - access functionality from everywhere
        },
        datatables: function() {
            dtIntegration(); // Datatables Bootstrap integration
        },
        general: function() {
            uiGeneral(); //General initialisation code
        },
    };
}();

/* Initialize app when page loads */
$(function(){ App.init(); });
