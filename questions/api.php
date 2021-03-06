<?php

include("../config.php");
include("../dbFunctions.php");

$task = $_GET["task"];


// for debugging

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function getSortedUniqueArray($elements){
  $unique_array = array_unique($elements);
  sort($unique_array);
  return $unique_array;
}

if ($task == "status") {
	// this now requires POST requests so you can use the same interface for multiple
	// status requests (including those that that have to send lots of docids, and thus
	// require a POST
	
	// return 0 if last answer was correct,
	//        1 if last answer was wrong,
	//        2 if unanswered,
	//        3 if there is no question
	
	$json = file_get_contents('php://input');
	$obj = json_decode($json, true);
	
	$grp = $obj["grp"];
	$usr = $obj["usr"];
	$docids = $obj["docids"];
	
	$arr = array();
	
	foreach ($docids as $docid) {
		$status = 2;
		$questionIds = docidToQuestionIds($docid);
		
		if (count($questionIds) == 0) {
			$status = 3;
		} else {
			$lastStatuses = array();
			foreach ($questionIds as $id) {
				array_push($lastStatuses, getLastAnswerStatus($usr, $grp, $id));
			}
			if (in_array(1, $lastStatuses)) {
				$status = 1;
			} else {
				// if we got here, all questions are either correct or unanswered
					
				if (!in_array(2, $lastStatuses)) {
					// if all correct return correct
					$status = 0;
				} else if (in_array(0, $lastStatuses)) {
					// if we have both correct and unanswered, return incorrect
					$status = 1;
				} else {
					// if all unanswered, return unanswered
					$status = 2;
				}
			}
		}
		$arr[$docid] = $status;
	}
	
	echo(json_encode($arr));
} else if ($task == "questions") {
	$docid = $_GET["docid"];
	
	$questions = array();
	
	$questionIds = docidToQuestionIds($docid);
	
	foreach ($questionIds as $id) {
		$questionText = getQuestion($id);
		$answers = getAnswers($id);
		$question = array("question" => $questionText, "answers" => $answers);
		array_push($questions, $question);
	}
	
	echo(json_encode($questions));
} else if ($task == "lastanswer") {
	$docid = $_GET["docid"];
	$usr = $_GET["usr"];
	$grp = $_GET["grp"];
	// returns the last answer of a user for a question
	$lasts = array();
	
	$questionIds = docidToQuestionIds($docid);
	foreach ($questionIds as $id) {
		$last = getLastAnswer($usr, $grp, $id);
		
		array_push($lasts, $last);
	}	
	
	echo(json_encode($lasts));
	
} else if ($task == "submit") {
	// checks an answer, and increments the counter
	
	// unlike the other endpoints, this comes as a POST request.
	// data is in json
	$json = file_get_contents('php://input');
	$obj = json_decode($json, true);
	
	$docid = $obj["docid"];
	$grp = $obj["grp"];
	$usr = $obj["usr"];
	$sid = $obj["sid"];
	$answers = $obj["answers"];
	
	$questionIds = docidToQuestionIds($docid);
	
	$correctAnswers = array();
	foreach ($questionIds as $id) {
		array_push($correctAnswers, getCorrectAnswerIndices($id));
	}
	
	$status = 2;
	
	$count = 0;
	foreach ($answers as $array) {
		$count += count($array);
	}
	
	$incorrectIndices = array();
	
	// only consider when answer submitted? (if so, change >= to >)
	if ($count >= 0) {
		for ($i = 0; $i < count($correctAnswers); $i++) {
			$correct = true;
			if ($answers[$i] != $correctAnswers[$i]) {
				$correct = false;
				array_push($incorrectIndices, $i);
			}
			$id = $questionIds[$i];
			insertAnswer($usr, $grp, $sid, $id, json_encode($answers[$i]), $correct);
		}
	}
	
	if (count($incorrectIndices) == 0) {
		$status = 0;
	} else {
		$status = 1;
	}
	
	// TODO: return status the same way it is for the status API (0 is correct, etc.)
	$arr = array('status' => $status, 'incorrect' => $incorrectIndices);
	echo(json_encode($arr));
} else if ($task == "samequestions") {
	// given some docid, return the other docids that share the same question
	
	$docid = $_GET["docid"];
	$docids = docidToDocids($docid);
	
	echo(json_encode($docids));
} else if ($task == "subsectionstatus") {
	// this now requires POST requests so you can use the same interface for multiple
	// status requests (including those that that have to send lots of docids, and thus
	// require a POST
	
	// return 0 if last answer was correct,
	//        1 if last answer was wrong,
	//        2 if unanswered,
	//        3 if there is no question
	//        4 if a user does not read more than 80% of the required documents.
	
	$json = file_get_contents('php://input');
	$obj = json_decode($json, true);
	
	$grp = $obj["grp"];
	$usr = $obj["usr"];
	$docids = $obj["docids"];
	
	$arr = array();
	
	// Concatenates all document ids that need to be queries from database.
	$all_docnos = "(";
	$all_docids = "(";
	$docno_to_ids = array();
	for ($i = 0; $i < count($docids); ++$i) {
       foreach(explode(",", $docids[$i]) as $docid_each) {
	    list($docid, $docno) = explode('@', $docid_each);
		$docno_to_ids[$docno] = $docid;
	    $all_docnos = $all_docnos."'".$docno."',";
		$all_docids = $all_docids."'".$docid."',";
	  } 
    }
	$all_docnos = $all_docnos."'-1')";
	$all_docids = $all_docids."'-1')";
	
	// Queries database.
	$docid_total_pages = getTotalPageForDocs($all_docnos);
	$docid_read_pages = getTotalPageReadForDocs($usr, $grp, $all_docnos);
	$docid_questionids = getTotalQuestionIdsForDocs($all_docids);

	foreach ($docids as $docids_each) {
		$status = 2;
		
		$docid_key = "";
		$docid = explode(",", $docids_each);
		$questionIds = array();
		$percentage_per_page = 0.0;
		$total_docs = 0;
		foreach($docid as $docid_each_raw) {
		  list($docid_each, $docno_each) = explode('@', $docid_each_raw);
		  $docid_key = $docid_key.$docid_each.",";
		  $page_for_read = 0;
		  $page_read = 0;
		  
		  if(array_key_exists($docid_each, $docid_questionids)) {
		    $questionIds = array_merge($questionIds, explode(',', $docid_questionids[$docid_each]));
		  }
		  if(array_key_exists($docid_each, $docid_total_pages)) {
		    $page_for_read = $docid_total_pages[$docid_each];
		  }
		  if(array_key_exists($docno_each, $docid_read_pages)) {
		    $page_read = $docid_read_pages[$docno_each];
		  }		  
		  $percentage_per_page = $page_for_read > 0 ? $percentage_per_page + $page_read / ($page_for_read + 0.0) : 0.0;
		  $total_docs = $total_docs + 1;
		  //echo $docid_each."-".$page_for_read."-".$page_read."-".count($questionIds)."\n";
		}
		
		if($total_docs > 0 and ($percentage_per_page / $total_docs < 0.8) ) {
		    $status = 4;
		} else if (count($questionIds) == 0) {
			$status = 3;
		} else {
			$lastStatuses = array();
			foreach ($questionIds as $id) {
				array_push($lastStatuses, getLastAnswerStatus($usr, $grp, $id));
			}
			if (in_array(1, $lastStatuses)) {
				$status = 1;
			} else {
				// if we got here, all questions are either correct or unanswered
					
				if (!in_array(2, $lastStatuses)) {
					// if all correct return correct
					$status = 0;
				} else if (in_array(0, $lastStatuses)) {
					// if we have both correct and unanswered, return incorrect
					$status = 1;
				} else {
					// if all unanswered, return unanswered
					$status = 2;
				}
			}
		}
		$docid_key = ($docid_key == "") ? $docid_key : str_replace(",-1", "", $docid_key."-1");
		$arr[$docid_key] = $status;
	}
	
	echo(json_encode($arr));
} else if ($task == "subsectionquestions") {
	$docid_array = explode(",", $_GET["docids"]);
	$questionIds = array();
	$questions = array();
	
	foreach($docid_array as $docid) {	  
	  $questionIds = array_merge($questionIds, docidToQuestionIds($docid));
	}
	
	$questionIds = getSortedUniqueArray($questionIds);
	
	foreach ($questionIds as $id) {
		$questionText = getQuestion($id);
		$answers = getAnswers($id);
		$question = array("question" => $questionText, "answers" => $answers);
		array_push($questions, $question);
	}
	
	echo(json_encode($questions));
} else if ($task == "subsectionsubmit") {
	// checks an answer, and increments the counter
	
	// unlike the other endpoints, this comes as a POST request.
	// data is in json
	$json = file_get_contents('php://input');
	$obj = json_decode($json, true);
	
	$docid = $obj["docid"];
	$docids = $obj["docids"];
	$grp = $obj["grp"];
	$usr = $obj["usr"];
	$sid = $obj["sid"];
	$answers = $obj["answers"];
	
	$docid_array = explode(",", $docids);
	$questionIds = array();	
	foreach($docid_array as $docid) {
	  $questionIds = array_merge($questionIds, docidToQuestionIds($docid));
	}
	
	$questionIds = getSortedUniqueArray($questionIds);
	
	$correctAnswers = array();
	foreach ($questionIds as $id) {
		array_push($correctAnswers, getCorrectAnswerIndices($id));
	}
	
	$status = 2;
	
	$count = 0;
	foreach ($answers as $array) {
		$count += count($array);
	}
	
	$incorrectIndices = array();
	
	// only consider when answer submitted? (if so, change >= to >)
	if ($count >= 0) {
		for ($i = 0; $i < count($correctAnswers); $i++) {
			$correct = true;
			if ($answers[$i] != $correctAnswers[$i]) {
				$correct = false;
				array_push($incorrectIndices, $i);
			}
			$id = $questionIds[$i];
			insertAnswer($usr, $grp, $sid, $id, json_encode($answers[$i]), $correct);
		}
	}
	
	if (count($incorrectIndices) == 0) {
		$status = 0;
	} else {
		$status = 1;
	}
	
	// TODO: return status the same way it is for the status API (0 is correct, etc.)
	$arr = array('status' => $status, 'incorrect' => $incorrectIndices);
	echo(json_encode($arr));
} else if ($task == "subsectionlastanswer") {
    $docid_array = explode(",", $_GET["docids"]);
	$usr = $_GET["usr"];
	$grp = $_GET["grp"];
	
	$questionIds = array();
	$lasts = array();
	
	foreach($docid_array as $docid) {	  
	  $questionIds = array_merge($questionIds, docidToQuestionIds($docid));
	}
	
	$questionIds = getSortedUniqueArray($questionIds);
	
	foreach ($questionIds as $id) {
		$last = getLastAnswer($usr, $grp, $id);
		array_push($lasts, $last);
	}
	
	echo(json_encode($lasts));
}
?>
