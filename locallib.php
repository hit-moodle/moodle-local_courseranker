<?php


/**
 * get data of all the course from db 
 *
 * @Copyright(c) 2012, Gao Jiayang All Rights Reserved.
 * @Author Gao Jiayang http://windwild.net
 * @param none
 * @return array of courses
 */

function get_course_table(){
	$starttime = get_config('courseranker')->starttime;
	$weight = array('view'=>1,
		'view forum'=>1,
		'view discussion'=>1,
		'view forum'=>1,
		'add post'=>10,
		'login'=>0,
		'mailer'=>0,
		'add discussion'=>10,
		'update'=>0);
	global $DB;
	$output = '';
	
	$sql = 'SELECT l.id,c.id AS `courseid`,c.fullname,l.`action`,COUNT(`action`) AS times
	FROM {course} c LEFT JOIN {log} l ON l.course = c.id WHERE l.`time` > ? GROUP BY `action`';
	$param = array($starttime);
	$db_results = $DB->get_records_sql($sql,$param);
	if(count($db_results) == 0){
		return array();
	}
	$results = array();
	foreach($db_results as $db_result){
		if(!isset($results[$db_result->courseid])){
			$results[$db_result->courseid]['score'] = 0;
			$results[$db_result->courseid]['courseid'] = $db_result->courseid;
			$results[$db_result->courseid]['fullname'] = $db_result->fullname;
		}
		if(isset($weight[$db_result->action]))
			$results[$db_result->courseid]['score'] += $db_result->times * $weight[$db_result->action];
	}
	
	foreach ($results as $key => $row){
			$score_a[$key] = $row['score'];
		}
	array_multisort($score_a,SORT_DESC,$results);
	return $results;
}

/**
 * get data of $course_id from db
 *
 * @Copyright(c) 2012, Gao Jiayang All Rights Reserved.
 * @Author Gao Jiayang http://windwild.net
 * @param int $course_id
 * @return array of data
 */

function get_user_rank($course_id){
	$starttime = get_config('courseranker')->starttime;
	$weight = array('view'=>1,
		'view forum'=>1,
		'view discussion'=>1,
		'view forum'=>1,
		'add post'=>10,
		'login'=>0,
		'mailer'=>0,
		'add discussion'=>10,
		'update'=>0);
	global $DB;
	
	
	$sql = 'SELECT l.id,re1.userid,username,re1.firstname,re1.lastname,re1.email,ACTION,COUNT(`action`) AS times FROM 
		((SELECT u.id AS userid, u.username,u.firstname,u.lastname,u.email,c.id AS courseid
		FROM {role_assignments} ra, {user} u, {course} c, {context} cxt
		WHERE ra.userid = u.id
		AND ra.contextid = cxt.id
		AND cxt.contextlevel =50
		AND cxt.instanceid = c.id
		AND c.id = ?
		AND roleid IN (SELECT id FROM {role} r WHERE archetype = "student")) AS re1
		LEFT JOIN {log} l ON re1.userid = l.userid AND re1.courseid = l.course)
		WHERE l.`time` >= ?
		GROUP BY `action`';
	$param_array = array($course_id,$starttime);
	$db_results = $DB->get_records_sql($sql,$param_array);
	if(count($db_results) == 0){
		return array();
	}
	$results = array();
	foreach($db_results as $db_result){
		if(!isset($results[$db_result->userid]))
			$results[$db_result->userid]['score'] = 0;
		$results[$db_result->userid]['userid'] = $db_result->userid;
		$results[$db_result->userid]['username'] = $db_result->username;
		$results[$db_result->userid]['firstname'] = $db_result->firstname;
		$results[$db_result->userid]['lastname'] = $db_result->lastname;
		$results[$db_result->userid]['email'] = $db_result->email;
		if(isset($weight[$db_result->action]))
			$results[$db_result->userid]['score'] += $db_result->times * $weight[$db_result->action];
	}
	
	foreach ($results as $key => $row){
			$score_a[$key] = $row['score'];
		}
	array_multisort($score_a,SORT_DESC,$results);
	return $results;
}

/**
 * get user score detail data from db
 *
 * @Copyright(c) 2012, Gao Jiayang All Rights Reserved.
 * @Author Gao Jiayang http://windwild.net
 * @param int $user_id, int $course_id
 * @return array of user score detail
 */

function get_rank_detail($user_id,$course_id){
	$starttime = get_config('courseranker')->starttime;
	global $DB;
	$sql = "SELECT l.id,l.userid,l.course, l.ACTION,COUNT(`action`) AS times FROM {log} l
		WHERE userid = ? AND course = ? AND l.`time` >= ? GROUP BY `action`";
	$param_array = array($user_id,$course_id,$starttime);
	$db_results = $DB->get_records_sql($sql,$param_array);
	if(count($db_results) == 0){
		return array();
	}
	return $db_results;
}


/**
 * get course score detail data from db
 *
 * @Copyright(c) 2012, Gao Jiayang All Rights Reserved.
 * @Author Gao Jiayang http://windwild.net
 * @param int $course_id
 * @return array of score details of the course
 */

function get_course_score_detail($course_id){
	$starttime = get_config('courseranker')->starttime;
	global $DB;
	$sql = "SELECT l.id,l.course, l.`action`,COUNT(`action`) AS times FROM {log} l
		WHERE course = ? AND l.`time` >= ? GROUP BY `action`";
	$param_array = array($course_id,$starttime);
	$db_results = $DB->get_records_sql($sql,$param_array);
	if(count($db_results) == 0){
		return array();
	}
	return $db_results;
}

function is_cached($course_id,$user_id,$course_detail_id){
	global $DB;
	$result = $DB->get_record('courseranker',array('course_id'=>$course_id,'user_id'=>$user_id,'course_detail_id'=>$course_detail_id));;
	if(isset($result->value)){
		print_r($result);
		return $result->value;
	}else{
		return false;
	}
}

function cache_it($course_id,$user_id,$course_detail_id,$value){
	global $DB;
	$DB->insert_record('courseranker',array('course_id'=>$course_id,'user_id'=>$user_id,'course_detail_id'=>$course_detail_id,'value'=>$value,'time'=>time()));
}

function flush_cache(){
	global $DB;
	$DB->delete_records('courseranker',array());
}
