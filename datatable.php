<?php

/**
 * @author Guido Gybels
 * @copyright 2016
 * @project NUCLEUS Portal
 * @description This unit provides server side datatable functionality
 */

require_once("initialise.inc");

//Initialise request and response
$request = $_GET;

if(defined('__DEBUGMODE') && __DEBUGMODE)
{
    file_put_contents(IncTrailingPathDelimiter(sys_get_temp_dir())."MyPortal.datatable.php.request.txt", print_r($request, TRUE));
}
$response = array(
    "sEcho" => intval($request['sEcho']),
    "iTotalRecords" => 0,
    "iTotalDisplayRecords" => 0,
    "aaData" => array(),
);

if(empty($request['accesslevel']))
{
    $hasaccess = TRUE;
}
else
{
    switch($request['accesslevel'])
    {
        case 'authenticated':
            //$hasaccess = !$Marvin->CurrentUser->Guest;
            break;
        case 'member':
            //$hasaccess = $Marvin->CurrentUser->Membership->IsMember;
            break;
        case 'guest':
            //$hasaccess = TRUE;
            break;
        default:
            $hasaccess = FALSE;
    }
}

if($hasaccess)
{
    //Prepare
    $colcount = intval($request['iColumns']);
    $fieldnames = array();
    for($i = 0; $i < $colcount; $i++)
    {
        $fieldnames[$i] = (isset($request['mDataProp_'.$i]) ? $request['mDataProp_'.$i] : $i);
    }
    $SEARCHES = array();
    $SEARCHTERM = '';
    if(!empty($request['sSearch']) && (strlen($request['sSearch']) > 1))
    {
        $SEARCHTERM = TextStr($request['sSearch']);
    }
    $STARTRECORD = (isset($request['iDisplayStart']) ? intval($request['iDisplayStart']) : 0);
    $ENDRECORD = $STARTRECORD+intval((isset($request['iDisplayLength']) ? $request['iDisplayLength'] : 25))-1;
    //load a file from the include directory to perform the query
    if(!empty($request['inc']))
    {
        require(IdentifierStr($request['inc']));
    }
    //Make a call to establish the total number of records
    if(!empty($request['fnTotalRecords']))
    {
        $response['iTotalRecords'] = call_user_func($request['fnTotalRecords'], $request, $STARTRECORD, $ENDRECORD, $SEARCHTERM);
    }
    //Get the data
    $count = 0;
    if(!empty($request['fnRow']))
    {
        $i = $STARTRECORD;
        while($i <= $ENDRECORD)
        {
            $row = call_user_func($request['fnRow'], $count, $i, $request);
            if(!empty($row))
            {
                if(!empty($request['rowid']) && isset($data[$request['rowid']]))
                {
                    $row['DT_RowId'] = $data[$request['rowid']];
                }
                $response['aaData'][] = $row;
                $count++;
            }
            $i++;
        }
    }
    $response['iTotalDisplayRecords'] = $response['iTotalRecords'];
}

//Send the response
if(defined('__DEBUGMODE') && __DEBUGMODE)
{
    file_put_contents(IncTrailingPathDelimiter(sys_get_temp_dir())."MyPortal.datatable.php.response.txt", print_r($response, TRUE));
}
echo json_encode($response);

?>