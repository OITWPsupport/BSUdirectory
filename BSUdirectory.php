<?php
/*
Plugin Name: BSU Directory Search
Author: David Ferro
Description: A searchable directory for Boise State Employees
Version: 0.4

*/
/*
ver 0.1: initial release
ver 0.2: updated search scope for department
ver 0.3: fixed bug to allow stylesheet to load, changed display to accommodate screen readers
ver 0.4: changed 'if ($_SERVER["SERVER_PORT"] != "80")' to 'if ($_SERVER["SERVER_PORT"] != "443")' in 
the curPageURL function to accommodate our domain-wide switch to https (May, 2016)
*/

ini_set('session.cookie_lifetime', 0);
session_start();



$siteURL=get_site_url('wpurl');
add_action('template_redirect', 'startup');

add_shortcode('BSUDIRECTORY', 'run_BSUdir');

function run_BSUdir(){
$site=curPageURL();
//print $site;
//$site='http://directory.boisestate.edu/searchable-faculty-and-staff-directory/';//change to page directory is hosted on.

//error_reporting(E_ALL);
//ini_set('display_errors', '1');

   $ref = getenv("HTTP_REFERER");
//print_r($site);
//print_r($ref);
   if (!stristr($ref,$site)){
       unset($_SESSION['fields']);
       unset($_SESSION['searchResult']);
   }
//print_r($_SESSION['searchResult']);
    $BSUlast='';
    $BSUBSUfirst='';
    $BSUphone='';
    $BSUdept='';
    $BSUtitle='';
    $BSUmail='';
    $BSUemail='';
    $fields=array('BSUlast'=>$BSUlast,'BSUfirst'=>$BSUfirst,'BSUphone'=>$BSUphone,'BSUdept'=>$BSUdept,'BSUtitle'=>$BSUtitle,'BSUmail'=>$BSUmail,'BSUemail'=>$BSUemail);
    //print_r($_POST);

    if(isset($_POST['BSUsubmit'])){//todo sanitize inputs
        //echo 'true';
        $BSUlast=check_input($_POST['BSUlast']);
        $BSUfirst=check_input($_POST['BSUfirst']);
        $BSUphone=check_input($_POST['BSUphone']);
        $BSUdept=check_input($_POST['BSUdept']);
        $BSUtitle=check_input($_POST['BSUtitle']);
        $BSUmail=check_input($_POST['BSUmail']);
        $BSUemail=check_input($_POST['BSUemail']);

            $fields=array('BSUlast'=>$BSUlast,'BSUfirst'=>$BSUfirst,'BSUphone'=>$BSUphone,'BSUdept'=>$BSUdept,'BSUtitle'=>$BSUtitle,'BSUmail'=>$BSUmail,'BSUemail'=>$BSUemail);
            $searchResult=BSUsearch($fields);
            //print_r($searchResult);
            searchForm($fields);
            $_SESSION['searchResult']=$searchResult;
            $_SESSION['fields']=$fields;
        //}

    }else{
        if(isset($_SESSION['fields'])){
            $fields=$_SESSION['fields'];
        }
        searchForm($fields);
    }

    if(isset($_SESSION['searchResult'])){
        if(count($_SESSION['searchResult'])<=0){
            print("<br /><div  id='results2'>");
            print('<div class="cfct-module cfct-notice ">
<div class="cfct-mod-content">
<p>Your search did not match any results.</p>



<p>Suggestions:</p>
  <ul>
    <li>Make sure all words are spelled correctly.</li>
    <li>For names, try alternative spellings.</li>
    <li>Try a wildcard search by entering a portion of  the word (example: anders will find both anderson and andersen) </li>
    <li>Asterisks (example: anders*) are not necessary for wildcard searches.</li>
  </ul>
</div>
</div> ');
           // print_r($fields['BSUdept']."hello");
		//print($_POST['BSUdept']);
		//print($BSUdept);
            print("</div>");
        }else{
            require_once 'pagination.class.php';
        $paginator = new pagination;
        $numPages= count($_SESSION['searchResult']);

        if($numPages){
            $results = $paginator->generate($_SESSION['searchResult'], $perPage);


            if($numPages !=0){
                print("<br /><div  id='results2'>");
                echo 'Total Matches: '.$numPages.'<br/>';
                echo $paginator->links()."<br />";
                print("<br /><div class='BSUalign' id='results'>");
                displaySearchResults($results);
                echo "</div>".$paginator->links();
                print("</div>");
            }
        }
    }
    }
}


function searchForm($array){

    extract($array);

print("<div id='BSUcontent' style='a.selected{font-weight: bold;'}>");

//print("<h2>Searchable Faculty and Staff Directory</h2>");
print("<p>All searches are wild card searches. To search for any form of a name, enter the first portion of the name (example: anders will find both anderson and andersen). If you place an asterisk in your search it will not be a wild card search. Boise State's area code is 208.  The data source for Directory information has changed.  If your information is not correct, please submit a <a href=' https://secureforms.boisestate.edu/oit/directory-update-form/'>Directory Change Request Form</a>.</p>");

print("<form action='".get_permalink()."' method='post' name='srchForm'>");

print("<p>Type in your search below:</p>");
print("<div class='directoryLeftEntry'><label for='lname' class='directory'>Last Name</label> <input type='text' id='lname' name='BSUlast' value='");
 echo($BSUlast);
print("' alt='Search by Last Name'/></div>
	<div class='directoryRightEntry'><label for='fname' class='directory'>First Name</label> <input type='text' id='fname' name='BSUfirst' value='");
 echo($BSUfirst);
 print("' alt='Search by First Name'/></div>
    <br/>");
print("<div class='directoryLeftEntry'><label for='phone' class='directory'>Telephone</label> <input type='text' id='phone' name='BSUphone' value='");
echo($BSUphone);
print("' alt='Search by Telephone'/></div>
	<div class='directoryRightEntry'><label for='dept' class='directory'>Department</label> <input type='text' id='dept' name='BSUdept' value='");
echo($BSUdept);
print("' alt='Search by Department'/></div>
    <br/>");
print("	<div class='directoryLeftEntry'><label for='title' class='directory'>Employee Title</label> <input type='text' id='title' name='BSUtitle' value='");
echo($BSUtitle);
print("' alt='Search by Employee Title'/></div>
	<div class='directoryRightEntry'><label for='mails' class='directory'>Mail Stop</label> <input type='text' id='mails' name='BSUmail' value='");
echo($BSUmail);
print("' alt='Search by Mail Stop'/></div>
    <br/>");
print("<div class='directoryLeftEntry'><label for='email' class='directory'>Email Address</label> <input type='text' id='email' name='BSUemail' value='");
echo($BSUemail);
print("' alt='Search by Email Address'/></div>
	<br/>");
print("<div class='directoryCenterEntry'>
	<input type='submit' name='BSUsubmit' id='submitbutton' value=' Search ' /> 
	<input type='button' name='Reset' id='button' value='  Reset  ' onclick= resetForm();  /></div>");
print("	</form></div>");
}

function displaySearchResults($searchResult){

         if (isset($searchResult)){
                foreach($searchResult as $person){
                    print("<span class='BSUname' style='font-size: 1.1em; font-weight: bold;'>".$person[0].", ".$person[1]."</span> ".$person[2]."<br/>");
                    print('<span class="BSUtitle" style="margin-left: 25px;">Title: '.$person[3].'</span><br/>');
                    print('<span class="BSUdepartment" style="margin-left: 25px;">Department: '.$person[4].'-- '.$person[5].'</span><br/>');
                    print('<span class="BSUemail" style="margin-left: 25px;">Email: <a href="mailto:'.$person[6].'">'.$person[6].'</a></span><br/>');
                    print('<span class="BSUmail" style="margin-left: 25px;">Mail Stop: '.$person[7].'</span><br/>');
                    print('<span class="BSUfax" style="margin-left: 25px;">Fax: '.$person[8].'</span><br/>');
                    print('</br>');
                }
            }
}

function BSUsearch($array){//array('last'=>$last,'first'=>$first,'phone'=>$phone,'dept'=>$dept,'title'=>$title,'mail'=>$mail,'email'=>$email)
//$siteURL=get_site_url('wpurl');
$data=BSUreadFiles('./wp-content/plugins/BSUdirectory/Ilist.dat');


//print_r($array);



$length = sizeof($data);
$i=0;
for($i;$i<$length;$i++) {
    $haystack=$data[$i];
    //print_r($haystack);

    $lastFound=FALSE;
    $firstFound=FALSE;
    $phoneFound=FALSE;
    $deptFound=FALSE;
    $titleFound=FALSE;
    $mailFound=FALSE;
    $emailFound=FALSE;


    $delete=FALSE; //false means to remove
    //last
    if(($array['BSUlast']!=='') && strpos(strtolower($haystack[0]), strtolower($array['BSUlast']))!==FALSE){
        $lastFound=TRUE;
    }elseif($array['BSUlast']==''){
        $lastFound=TRUE;
    }
    //first
    if(($array['BSUfirst']!=='')&&strpos(strtolower($haystack[1]), strtolower($array['BSUfirst']))!==FALSE){
        $firstFound=TRUE;
    }elseif($array['BSUfirst']==''){
        $firstFound=TRUE;
    }
    //phone
    if(($array['BSUphone']!=='')&&strpos(strtolower($haystack[2]), strtolower($array['BSUphone']))!==FALSE){
        $phoneFound=TRUE;
    }elseif($array['BSUphone']==''){
        $phoneFound=TRUE;
    }
    //dept
    if(($array['BSUdept']!=='')&&strpos(strtolower($haystack[4]." ".$haystack[5]), strtolower($array['BSUdept']))!==FALSE){
        $deptFound=TRUE;
    }elseif($array['BSUdept']==''){
        $deptFound=TRUE;
    }
    //title
    if(($array['BSUtitle']!=='')&&strpos(strtolower($haystack[3]), strtolower($array['BSUtitle']))!==FALSE){
        $titleFound=TRUE;
    }elseif($array['BSUtitle']==''){
        $titleFound=TRUE;
    }
    //mail

    //print strval($haystack[7])."<br/>";
    //print strval($array['BSUmail'])."<br/>";
    if(($array['BSUmail']!=='')&&strpos(strval($haystack[7]), strval($array['BSUmail']))!==FALSE){
        $mailFound=TRUE;
    }elseif($array['BSUmail']==''){
        $mailFound=TRUE;
    }
    //email
    if(($array['BSUemail']!=='')&&strpos(strtolower($haystack[6]), strtolower($array['BSUemail']))!==FALSE){
        $emailFound=TRUE;
    }elseif($array['BSUemail']==''){
        $emailFound=TRUE;
    }
    if($lastFound&&$firstFound&&$phoneFound&&$deptFound&&$titleFound&&$mailFound&&$emailFound){
       $delete=TRUE;
    }
    if(!$delete){
        //print 'deleted';
        unset($data[$i]);
    }
}
$data= array_values($data);

return $data;



}

function BSUreadFiles($BSUfile){

//        $ch=curl_init();
//        curl_setopt($ch, CURLOPT_URL, $BSUfile);
//        curl_setopt($ch, CURLOPT_HEADER,0);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//        $openFile= curl_exec($ch);


        $BSUdirectoryArray;
        $BSUfilename = $BSUfile;

        $BSUfile = $BSUfilename or die('Could not find file!');
        $openFile = fopen($BSUfile,'r') or die('Could not open file!');

        $i=0;
        while($data =  fgetcsv($openFile,'',"\t")){
            //print_r($data);
            //echo'<br/>';
            $BSUdirectoryArray[$i]=$data;
            //print_r($directoryArray[$i]);
            $i++;
        }
       fclose($openFile);
       //curl_close($ch);
       return $BSUdirectoryArray;
    }

function startup(){
    wp_enqueue_script('BSUscript', $siteURL.'/wp-content/plugins/BSUdirectory/BSUjsScripts.js' );
	wp_register_style('BSUdirectory', plugins_url('BSUdirStylesnew.css',__FILE__));
    wp_enqueue_style('BSUdirectory');
    //echo("<link type='text/css' rel='stylesheet' href='".$siteURL."/wp-content/plugins/BSUdirectory/BSUdirStyles.css '/>" . "\n");
}

function check_input($data) {
	$data = trim($data);
	$data = stripslashes($data);
	//$data = htmlspecialchars($data);
	return $data;
};


function curPageURL() {
 $pageURL = 'http';
 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "443") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 }

 $pageURL=substr($pageURL,0,strrpos($pageURL,"?"));

 return $pageURL;
}
?>
