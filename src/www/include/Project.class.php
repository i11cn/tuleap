<?php

//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$


//require_once('common/include/Error.class.php');
require_once('common/project/Service.class.php');
/*

	An object wrapper for project (as opposed to foundry) data

	Extends the base object, Group

	Tim Perdue, August 28, 2000



	Example of proper use:

	//get a local handle for the object
	$grp=project_get_object($group_id);

	//now use the object to get the unix_name for the project
	$grp->getUnixName();


*/



/*
	associative array of group objects
	helps prevent the same object from being created more than once
	which would create unnecessary database calls
*/
$PROJECT_OBJ=array();

function project_get_object($group_id,$force_update=false) {
	//create a common set of group objects
	//saves a little wear on the database
	global $PROJECT_OBJ;
	if (!isset($PROJECT_OBJ["_".$group_id."_"]) || !$PROJECT_OBJ["_".$group_id."_"] || $force_update) {
		$PROJECT_OBJ["_".$group_id."_"]= new Project($group_id);
		return $PROJECT_OBJ["_".$group_id."_"];
	} else {
		return $PROJECT_OBJ["_".$group_id."_"];
	}
}



class Project extends Group {

    var $project_data_array;

    // All data concerning services for this project
    var $service_data_array;
    var $use_service;
    var $services;
    
    /*
		basically just call the parent to set up everything
                and set up services arrays
    */
    function Project($id) {
	global $Language;
	$Language->loadLanguageMsg('project/project');

        $this->Group($id);
        
        //for right now, just point our prefs array at Group's data array
        //this will change later when we split the project_data table off from groups table
        $this->project_data_array=$this->data_array;
        
        // Get Service data
        $db_res=db_query("SELECT * FROM service WHERE group_id='$id' ORDER BY rank");
        $rows=db_numrows($db_res); 
        if ($rows < 1) {
            //function in class we extended
            $this->setError($Language->getText('include_project','services_not_found'));
            $this->service_data_array=array();
        } else {
            for ($j = 0; $j < $rows; $j++) { 
                $res_row = db_fetch_array($db_res);
                $short_name=$res_row['short_name'];
                if (!$short_name) { $short_name=$j;}

                $em =& EventManager::instance();
                $em->processEvent("plugin_load_language_file", null);

		// needed for localisation
        $matches = array();
		if ($res_row['description'] == "service_".$short_name."_desc_key") {
		  $res_row['description'] = $Language->getText('project_admin_editservice',$res_row['description']);
		}
        elseif(preg_match('/(.*):(.*)/', $res_row['description'], $matches)) {
            $res_row['description'] = $Language->getText($matches[1], $matches[2]);
        }
		if ($res_row['label'] == "service_".$short_name."_lbl_key") {
		  $res_row['label'] = $Language->getText('project_admin_editservice',$res_row['label']);
		}
        elseif(preg_match('/(.*):(.*)/', $res_row['label'], $matches)) {
            $res_row['label'] = $Language->getText($matches[1], $matches[2]);
        }
                $this->service_data_array[$short_name] = $res_row;
                if ($short_name) {
                    $this->use_service[$short_name]= $res_row['is_used'];
                }
                
                $s =& new Service($res_row);
                $this->services[] =& $s;
                unset($s);
            }
        }
    }


    function usesHomePage() {
        return $this->use_service['homepage'];
    }
    
    function usesAdmin() {
        return $this->use_service['admin'];
    }
    
    function usesSummary() {
        return $this->use_service['summary'];
    }

    function usesTracker() {
        return $this->use_service['tracker'];
    }

    function usesCVS() {
        return $this->use_service['cvs'];
    }

    function usesSVN() {
        return $this->use_service['svn'];
    }

    function usesBugs() {
        return isset($this->use_service['bugs']) && $this->use_service['bugs'];
    }

    function usesSupport() {
        return isset($this->use_service['support']) && $this->use_service['support'];
    }

    function usesDocman() {
        return isset($this->use_service['doc']) && $this->use_service['doc'];
    }

    function usesPatch() {
        return isset($this->use_service['patch']) && $this->use_service['patch'];
    }

    function usesFile() {
        return $this->use_service['file'];
    }

    function usesPm() {
        return isset($this->use_service['task']) && $this->use_service['task'];
    }


    //whether or not this group has opted to use mailing lists
    function usesMail() {
        return $this->use_service['mail'];
    }

    //whether or not this group has opted to use news
    function usesNews() {
        return $this->use_service['news'];
    }

    //whether or not this group has opted to use discussion forums
    function usesForum() {
        return $this->use_service['forum'];
    }       

    //whether or not this group has opted to use surveys
    function usesSurvey() {
        return $this->use_service['survey'];
    }       

    //whether or not this group has opted to use wiki
    function usesWiki() {
        return $this->use_service['wiki'];
    }   


    // Generic versions

    function usesService($service_short_name) {
        return $this->use_service[$service_short_name];
    }


    /*
		The URL for this project's home page
    */
    function getHomePage() {
        return $this->service_data_array['homepage']['link'];
    }


    /*
		email address to send new 
		bugs/patches/support requests to
    */
    function getNewBugAddress() {
        return $this->project_data_array['new_bug_address'];
    }

    function getNewSupportAddress() {
        return $this->project_data_array['new_support_address'];
    }

    function getNewPatchAddress() {
        return $this->project_data_array['new_patch_address'];
    }

    function getNewTaskAddress() {
        return $this->project_data_array['new_task_address'];
    }

    /*

    boolean flags to determine whether or not to send
		an email on every bug/patch/support update

    */
    function sendAllBugUpdates() {
        return $this->project_data_array['send_all_bugs'];
    }

    function sendAllSupportUpdates() {
        return $this->project_data_array['send_all_support'];
    }

    function sendAllPatchUpdates() {
        return $this->project_data_array['send_all_patches'];
    }

    function sendAllTaskUpdates() {
        return $this->project_data_array['send_all_tasks'];
    }

    /*

    Subversion and CVS settings

    */

    function cvsMailingList() {
        return $this->project_data_array['cvs_events_mailing_list'];
    }

    function getCVSMailingHeader() {
        return $this->project_data_array['cvs_events_mailing_header'];
    }

    function isCVSTracked() {
        return $this->project_data_array['cvs_tracker'];
    }

    function getCVSpreamble() {
        return $this->project_data_array['cvs_preamble'];
    }

    function getSVNMailingList() {
        return $this->project_data_array['svn_events_mailing_list'];
    }

    function getSVNMailingHeader() {
        return $this->project_data_array['svn_events_mailing_header'];
    }

    function isSVNTracked() {
        return $this->project_data_array['svn_tracker'];
    }

    function getSVNpreamble() {
        return $this->project_data_array['svn_preamble'];
    }

    
}

/*

	Everything below here is deprecated

*/

//deprecated
function group_getname ($group_id = 0) {
	$grp = project_get_object($group_id);
	return $grp->getPublicName();
}

//deprecated
function group_getunixname ($group_id) {
	$grp = project_get_object($group_id);
	return $grp->getUnixName();
}

//deprecated - should be getting objects instead
function group_get_result($group_id=0) {
	$grp = project_get_object($group_id);
	return $grp->getData();
}       
	
?>
