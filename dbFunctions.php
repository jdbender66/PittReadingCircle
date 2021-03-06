<?php
function dbConnectMySQL($host, $user, $pass, $db, $port) {
    if ($host!="" && $user!="" && $pass!=""&& $db!="") {
        $connection = new mysqli($host,$user,$pass, $db);
        if ($connection->connect_errno){
            echo "Failed to connect to MySQL: (" . $connection->connect_errno . ") " . $connection->connect_error;
            return false;
        }
        else {
            $connection->set_charset("utf8");
            return $connection;
        }
    }
    return false;
}

function dbDisconnectMySQL($connection) {
    if ((mysqli_close($connection))) {
        return true;
    }
    return false;
}

function getConn() {
	global $config_dbHost, $config_dbUser, $config_dbPass, $config_dbName, $config_dbPort;
	return dbConnectMySQL($config_dbHost, $config_dbUser, $config_dbPass, $config_dbName, $config_dbPort);
}


function insertTracking($usr, $grp, $sid, $actionsrc, $actiontype, $docsrc, $docno, $filename, $result, $comment){
    global $config_dbHost, $config_dbUser, $config_dbPass, $config_dbName, $config_dbPort;
    $sql =  "INSERT INTO tracking (actiondate,uid,grp,domain,sid,actionsrc,actiontype,docno,docsrc,filename,result,comment) ";
    //$sql .= " values (now(),'".$usr."','".$grp."','".$course."','".$sid."','".$actionsrc."','".$actiontype."','".$docno."','".$docsrc."','".$filename."','".$result."','".$comment."');";
    $sql .= " values (now(),'".$usr."','".$grp."',(SELECT C.domain FROM course C, groups G where G.grp='".$grp."' and G.courseid=C.courseid),'".$sid."','".$actionsrc."','".$actiontype."','".$docno."','".$docsrc."','".$filename."','".$result."','".$comment."');";
    //echo $sql."<br />";
    $id = 0;
    $connection = dbConnectMySQL($config_dbHost, $config_dbUser, $config_dbPass, $config_dbName, $config_dbPort);
    if ($connection){
        mysqli_query($connection, $sql);
        $id = mysqli_insert_id($connection);
        dbDisconnectMySQL($connection);
    }
    return $id;
}

function insertProgress($usr, $grp, $sid, $bookid, $docno, $page, $question, $top, $bottom, $time) {
    $docInfo = getDocInfo($docno);
	$start_page = $docInfo["spage"];
	$current_page = $start_page + $page - 1;
	
	global $config_dbHost, $config_dbUser, $config_dbPass, $config_dbName, $config_dbPort;	    
    $sql = "SELECT docno, (".$current_page." - spage + 1) AS page FROM document WHERE spage <= ".$current_page." && epage >= ".$current_page." AND docsrc = '".$bookid."'";
    $connection = dbConnectMySQL($config_dbHost, $config_dbUser, $config_dbPass, $config_dbName, $config_dbPort);
	$docid_pages = array();
    if ($connection){
        if($res = mysqli_query($connection, $sql)){
            if(mysqli_num_rows($res) > 0){
                while($row = mysqli_fetch_array($res)){
				  insertProgressForEach($usr, $grp, $sid, $bookid, $row["docno"], $row["page"], $question, $top, $bottom, $time);                   
                }
            }
            mysqli_free_result($res);
        }
        dbDisconnectMySQL($connection);
    }
}

function insertProgressForEach($usr, $grp, $sid, $bookid, $docno, $page, $question, $top, $bottom, $time) {
   $mysqli = getConn();
	$stmt = $mysqli->prepare("INSERT INTO progress (date,usr,grp,sid,bookid,docno,page,question,top,bottom,time) VALUES (now(),?,?,?,?,?,?,?,?,?,?)");
	$stmt->bind_param('ssssssiddi', $usr, $grp, $sid, $bookid, $docno, $page, $question, $top, $bottom, $time);
	$stmt->execute();
	$stmt->fetch();
}

function getTotalPageReadForDocs($usr, $grp, $all_docnos) {
	global $config_dbHost, $config_dbUser, $config_dbPass, $config_dbName, $config_dbPort;	    
    $sql = "SELECT docno, COUNT(DISTINCT page) AS numPage FROM progress WHERE usr = '".$usr."' AND grp = '".$grp."' AND docno in ".$all_docnos. " GROUP BY docno";
	//echo $sql."\n";
    $connection = dbConnectMySQL($config_dbHost, $config_dbUser, $config_dbPass, $config_dbName, $config_dbPort);
	$docid_pages = array();
    if ($connection){
        if($res = mysqli_query($connection, $sql)){
            if(mysqli_num_rows($res) > 0){
                while($row = mysqli_fetch_array($res)){
                    $docid_pages[$row["docno"]] = $row["numPage"];
                }
            }
            mysqli_free_result($res);
        }
        dbDisconnectMySQL($connection);
    }
    return $docid_pages;
}

function getTotalPageRead($usr, $grp, $docid) {
	$mysqli = getConn();
	$stmt = $mysqli->prepare("SELECT COUNT(DISTINCT page) AS numPage FROM progress WHERE usr = ? AND grp = ? AND docno like ?");
	$newdocid = '%-'.$docid;
	$stmt->bind_param('sss', $usr, $grp, $newdocid);
	$stmt->execute();
	$stmt->bind_result($numPage);
	$stmt->fetch();
	return $numPage;
}

function getTotalPageForDocs($all_docnos) {
	global $config_dbHost, $config_dbUser, $config_dbPass, $config_dbName, $config_dbPort;	    
    $sql = "SELECT docid, ((epage - spage) + 1) AS numPage FROM document WHERE docno in ".$all_docnos;
    $connection = dbConnectMySQL($config_dbHost, $config_dbUser, $config_dbPass, $config_dbName, $config_dbPort);
	$docid_pages = array();
    if ($connection){
        if($res = mysqli_query($connection, $sql)){
            if(mysqli_num_rows($res) > 0){
                while($row = mysqli_fetch_array($res)){
                    $docid_pages[$row["docid"]] = $row["numPage"];
                }
            }
            mysqli_free_result($res);
        }
        dbDisconnectMySQL($connection);
    }
    return $docid_pages;
}

function getTotalPageForDoc($docid) {
	$mysqli = getConn();
	$stmt = $mysqli->prepare("SELECT ((epage - spage) + 1) AS numPage FROM document WHERE docid = ?");
	$stmt->bind_param('s', $docid);
	$stmt->execute();
	$stmt->bind_result($numPage);
	$stmt->fetch();
	return $numPage;
}

function insertAnswer($usr, $grp, $sid, $id, $last, $last_correct) {
	$mysqli = getConn();
	$stmt = $mysqli->prepare("INSERT INTO submitted_answers (usr,grp,sid,idquestions,time,answer,correct) VALUES (?,?,?,?,now(),?,?)");
	$stmt->bind_param('sssisi', $usr, $grp, $sid, $id, $last, $last_correct);
	$stmt->execute();
	$stmt->fetch();
}

function getLastAnswer($usr, $grp, $id) {
	$mysqli = getConn();
	$stmt = $mysqli->prepare("SELECT answer FROM submitted_answers WHERE usr = ? AND grp = ? AND idquestions = ? ORDER BY time DESC LIMIT 1");
	$stmt->bind_param('ssi', $usr, $grp, $id);
	$stmt->execute();
	$stmt->bind_result($last);
	$stmt->store_result(); // without this, num_rows won't work
	$stmt->fetch();
	if ($stmt->num_rows == 0) {
		$last = '[]';
	}
	return json_decode($last);
}

function getLastAnswerStatus($usr, $grp, $id) {
	$mysqli = getConn();
	$stmt = $mysqli->prepare("SELECT correct FROM submitted_answers WHERE usr = ? AND grp = ? AND idquestions = ? ORDER BY time DESC LIMIT 1");
	$stmt->bind_param('ssi', $usr, $grp, $id);
	$stmt->execute();
	$stmt->bind_result($last_correct);
	$stmt->store_result(); // without this, num_rows won't work
	$stmt->fetch();
	$status = 3;
	if ($stmt->num_rows == 0) {
		$status = 2;
	} else if ($last_correct == 1) {
		$status = 0;
	} else if ($last_correct == 0) {
		$status = 1;
	}
	return $status;
}

// given some docid, return the other docids that share a question
function docidToDocids($docid) {
	$mysqli = getConn();
	$docids = array();
	
	// get questionId for all documents that share the same start page as this document
	// could probably do this in less database calls (using join?)
	$stmt = $mysqli->prepare("SELECT docsrc,spage FROM document WHERE docid = ?");
	$stmt->bind_param('i', $docid);
	$stmt->execute();
	$stmt->bind_result($docsrc, $spage);
	
	while ($stmt->fetch()) {
		// connection had to be re-established here, or it didn't work
		$mysqli = getConn();
		$stmt2 = $mysqli->prepare("SELECT docid FROM document WHERE docsrc = ? AND spage = ?");
		$stmt2->bind_param('si', $docsrc, $spage);
		$stmt2->execute();
		$stmt2->bind_result($did);
		while ($stmt2->fetch()) {
			array_push($docids, $did);
		}
	}
	return $docids;
}

function docidToQuestionIds($docid) {
	$mysqli = getConn();
	
	$results = array();
	
	$docids = docidToDocids($docid);
	
	foreach ($docids as $id) {
		$mysqli = getConn();
		$stmt = $mysqli->prepare("SELECT idquestions FROM questions WHERE docid = ?");
		$stmt->bind_param('i', $id);
		$stmt->execute();
		$stmt->bind_result($questions);
		while ($stmt->fetch()) {
			array_push($results, $questions);
		}
	}
	return $results;
}

function getTotalQuestionIdsForDocs($all_docids) {
	global $config_dbHost, $config_dbUser, $config_dbPass, $config_dbName, $config_dbPort;	    
    $sql = "SELECT docid, GROUP_CONCAT(CONVERT(idquestions, CHAR(20))) AS questionids FROM questions WHERE docid in ".$all_docids." GROUP BY docid";
	$connection = dbConnectMySQL($config_dbHost, $config_dbUser, $config_dbPass, $config_dbName, $config_dbPort);
	$docid_questionids = array();
    if ($connection){
        if($res = mysqli_query($connection, $sql)){
            if(mysqli_num_rows($res) > 0){
                while($row = mysqli_fetch_array($res)){
                    $docid_questionids[$row["docid"]] = $row["questionids"];					
                }
            }
            mysqli_free_result($res);
        }
        dbDisconnectMySQL($connection);
    }
    return $docid_questionids;
    
}

function getQuestion($questionId) {
	$mysqli = getConn();
	$stmt = $mysqli->prepare("SELECT question FROM questions WHERE idquestions = ?");
	$stmt->bind_param('i', $questionId);
	$stmt->execute();
	$stmt->bind_result($question);
	$stmt->fetch();
	return $question;
}

function getAnswers($questionId) {
	$mysqli = getConn();
	$stmt = $mysqli->prepare("SELECT answer_text FROM answers WHERE idquestions = ?");
	$stmt->bind_param('i', $questionId);
	$stmt->execute();
	$results = array();
	$stmt->bind_result($answer);
	while ($stmt->fetch()) {
		array_push($results, $answer);
	}
	return $results;
}

function getCorrectAnswerIndices($questionId) {
	$mysqli = getConn();
	$stmt = $mysqli->prepare("SELECT answer_num FROM answers WHERE idquestions = ? AND correct = 1");
	$stmt->bind_param('i', $questionId);
	$stmt->execute();
	$results = array();
	$stmt->bind_result($answer);
	while ($stmt->fetch()) {
		array_push($results, $answer);
	}
	return $results;
}

function getDocInfo($docno){
    global $config_dbHost, $config_dbUser, $config_dbPass, $config_dbName, $config_dbPort;
    $result = array();
    $sql = "select docid,docsrc,title,spage,epage from document where docno = '".$docno."';";
    $connection = dbConnectMySQL($config_dbHost, $config_dbUser, $config_dbPass, $config_dbName, $config_dbPort);
    if ($connection){
        if($res = mysqli_query($connection, $sql)){
            if(mysqli_num_rows($res) > 0){
                while($row=mysqli_fetch_array($res)){
                    $result["docid"] = $row["docid"];
                    $result["docsrc"] = $row["docsrc"];
                    $result["title"] = $row["title"];
                    $result["spage"] = $row["spage"];
                    $result["epage"] = $row["epage"];
                }
            }
            mysqli_free_result($res);
        }
        dbDisconnectMySQL($connection);
    }
    return $result;
}
function getDocInfoById($docid,$bookid){
    global $config_dbHost, $config_dbUser, $config_dbPass, $config_dbName, $config_dbPort;
    $result = array();
    $sql = "select docid,docsrc,title,spage,epage from document where docid = '".$docid."' and docsrc='".$bookid."';";
    $connection = dbConnectMySQL($config_dbHost, $config_dbUser, $config_dbPass, $config_dbName, $config_dbPort);
    if ($connection){
        if($res = mysqli_query($connection, $sql)){
            if(mysqli_num_rows($res) > 0){
                while($row=mysqli_fetch_array($res)){
                    $result["docid"] = $row["docid"];
                    $result["docsrc"] = $row["docsrc"];
                    $result["title"] = $row["title"];
                    $result["spage"] = $row["spage"];
                    $result["epage"] = $row["epage"];
                }
            }
            mysqli_free_result($res);
        }
        dbDisconnectMySQL($connection);
    }
    return $result;
}

function getBookInfo($bookid){
    global $config_dbHost, $config_dbUser, $config_dbPass, $config_dbName, $config_dbPort;
    $result = array();
    $sql = "select bookkey,format,folder,title,authors from book where bookkey = '".$bookid."';";
    $connection = dbConnectMySQL($config_dbHost, $config_dbUser, $config_dbPass, $config_dbName, $config_dbPort);
    if ($connection){
        if($res = mysqli_query($connection, $sql)){
            if(mysqli_num_rows($res) > 0){
                while($row=mysqli_fetch_array($res)){
                    $result["bookkey"] = $row["bookkey"];
                    $result["format"] = $row["format"];
                    $result["folder"] = $row["folder"];
                    $result["title"] = $row["title"];
                    $result["authors"] = $row["authors"];
                }
            }
            mysqli_free_result($res);
        }
        dbDisconnectMySQL($connection);
    }
    return $result;
}

// returns section url
// pages are real (book) pages values
// $disp_page = current page
// $target_page = page to move
//change by jennifer
// @@@@
function getSectionByPage($bookid, $disp_page, $target_page) {
    global $config_dbHost, $config_dbUser, $config_dbPass, $config_dbName, $config_dbPort;
    $i_disp_page = pconv($bookid, $disp_page, 'page');
	$i_target_page = pconv($bookid, $target_page, 'page');
	$docno = "";
	$in_section_page = "";
    $sql = "SELECT docno, spage, epage FROM document where docno like '".$bookid."%' and spage <= ".$i_target_page." and ".$i_target_page." <= epage order by docid asc;";
    $connection = dbConnectMySQL($config_dbHost, $config_dbUser, $config_dbPass, $config_dbName, $config_dbPort);
    if ($connection){
        if($res = mysqli_query($connection, $sql)){
            $candid_count = mysqli_num_rows($res);
            // direct destination
            if($candid_count == 1) {
                $row = mysqli_fetch_array($res);
                $docno = $row["docno"];
                $in_section_page = $i_target_page - $row["spage"] + 1;
            }
            // multimple candidates
            else if($candid_count > 1) {
                // going forward
                if($i_target_page > $i_disp_page) {
                    $row = mysqli_fetch_array($res);
                    $docno = $row["docno"];
                    $in_section_page = $i_target_page - $row["spage"] + 1;
                }
                // going backward
                if($i_target_page < $i_disp_page) {
                    while($row = mysqli_fetch_array($res)) {
                        $docno = $row["docno"];
                        $section_start_page = $row["spage"];
                    }
                    $in_section_page = $i_target_page - $section_start_page + 1;
                }
            }
            // no direct destination
            else {
                // going forward
                $res2 = null;
                if($i_target_page > $i_disp_page) {
                    $sql2 = "SELECT docno, spage, epage FROM document where docno like '$bookid%' and spage > $i_target_page order by docno asc limit 0, 1;";
        
                    $res2 = mysqli_query($connection, $sql2);
                    $row2 = mysqli_fetch_row($res2);
                    $docno = $row2[0];
                    $in_section_page = 1;
                }
                // going backward
                if($i_target_page < $i_disp_page) {
                    $sql2 = "SELECT docno, spage, epage FROM document where docno like '$bookid%' and spage < $i_target_page order by docno desc limit 0, 1;";
        
                    $res2 = mysqli_query($connection, $sql2);
                    $row2 = mysqli_fetch_row($res2);
                    $docno = $row2[0];
                    $in_section_page = $row2[2] - $row2[1] + 1;
        
                }
                mysqli_free_result($res2);
            }       
/*            
            if(mysqli_num_rows($res) > 0){
                while($row=mysqli_fetch_array($res)){
                    $result["docno"] = $row["docno"];
                    $result["spage"] = $row["spage"];
                    $result["epage"] = $row["epage"];
                }
            }
*/            
            mysqli_free_result($res);
        }
        dbDisconnectMySQL($connection);
        
    }

	
    //return docno;


	if($docno == "")
		return false;
	//change by jennifer
//	$res = "reader.php?bookid=".$bookid."&docno=".$docno."&page=".$in_section_page."&page_nav=".$i_target_page."&usr=".$usr."&grp=".$grp."&sid=".$sid;

	//$res = "reader.php?bookid=$bookid&docno=$docno&page=".$in_section_page."&page_nav=$i_target_page";

	return "&docno=".$docno."&page=".$in_section_page."&page_nav=".$i_target_page;
}

// converts file number <-> real (book) page
// $mode == 'file' : from file -> page
// $mode == 'page' : from page -> file
function pconv($bookid, $source, $mode = 'file') {

	$offset = array(
		"shnm" => 18, 
		"dix" => 27,  
		"preece" => 24, 
		"lamming" => 0,
		"tdo" => 0);
	
	if($mode == 'file')
		$res = $source - $offset[$bookid];
	else
		$res = $source + $offset[$bookid];

	return $res;
}


?>