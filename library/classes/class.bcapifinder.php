<?php

/**
 * File: ./class.bcapifinder.php
 * 
 * @package library.1.0
 * @author Alexander Donichenko (adonichenko@gmail.com)
 */

/**
 * Work with Basecamp API
 * 
 * @package library.1.0
 * @author Alexander Donichenko (adonichenko@gmail.com)
 */
class Bcapifinder extends Datafinder {

	/**
	 * The api login parameters 
	 *
	 * @var array
	 */
	protected $apisetts;

	/**
	 * Construct method
	 */
	public function __construct() {
		parent::__construct();
		parent::__construct(strtolower(__CLASS__));
		//$this->_getts .= 'apisetts/';
		$this->apisetts = $this->ini[strtolower(__CLASS__)]['bcapi'];
	}

	/**
	 * Gets data from API
	 * 
	 * @param string $query
	 * @return array response content
	 */
	public function apiOpen($query) {
		$url = "{$this->apisetts['apiurl']}/{$query}";
		$this->urlOpen(array($url), null, array(
			 array(
				  'userpwd' => $this->apisetts['login'] . ':' . $this->apisetts['password'],
			 ),
		));
		return $this->apiResponse($url);
	}

	/**
	 * Posts data to API
	 * 
	 * @param string $query
	 * @param string $postfields
	 * @return array response content
	 */
	public function apiPost($query, $postfields) {
		$url = "{$this->apisetts['apiurl']}/{$query}";
		$this->urlOpen(array($url), null, array(
			 array(
				  'userpwd' => $this->apisetts['login'] . ':' . $this->apisetts['password'],
				  'httpheader' => array('Accept: application/json', 'Content-Type: application/json'),
				  'post' => $postfields,
			 ),
		));
		return $this->apiResponse($url);
	}

	/**
	 * Delete data API
	 * 
	 * @param string $query
	 * @return array response content
	 */
	public function apiDelete($query) {
		$url = "{$this->apisetts['apiurl']}/{$query}";
		$this->urlOpen(array($url), null, array(
			 array(
				  'userpwd' => $this->apisetts['login'] . ':' . $this->apisetts['password'],
				  'httpheader' => array('Accept: application/json', 'Content-Type: application/json'),
				  'customrequest' => 'DELETE',
			 ),
		));
		return $this->apiResponse($url);
	}

	/**
	 * Processing response API
	 * 
	 * @param string $url URL API
	 * @return array response content
	 */
	public function apiResponse($url) {
		return isset($this->urlopen[$url]) ? json_decode($this->urlopen[$url], true) : $this->getinfo;
	}

	/**
	 * Gets all projects
	 *
	 * @return array response content
	 */
	public function getProjects() {
		$aret = $this->apiOpen('projects.json');
		if (empty($aret['records'])) {
			throw new Exception("Not gets projects \n");
		}
		return $aret['records'];
	}

	/**
	 * Get todo lists & id todo list items for a ID project
	 *
	 * @param int $idproj
	 * @return array response content
	 */
	public function getTodoLists($idproj) {
		$aret = $this->apiOpen("projects/{$idproj}/todo_lists.json?filter=all");
		if (empty($aret['records'])) {
			throw new Exception("Not gets to do lists " . $idproj . "\n");
		}
		return $aret['records'];
	}

	/**
	 * Get id todo lists for all projects(<=25) or ID project
	 * 
	 * @param string|null $idproj ID project
	 * @return array response content
	 */
	public function getAllTodoLists($idproj = null) {
		$aret = array();
		$arprojs = isset($idproj) ? array(array('id' => $idproj)) : $this->getProjects();
		foreach ($arprojs as $proj) {
			usleep($this->apisetts['timeout']);
			$artdls = $this->getTodoLists($proj['id']);
			foreach ($artdls as $tdls) {
				$aret[] = $tdls['id'];
			}
		}
		return $aret;
	}

	/**
	 * get all todo items for a list
	 *
	 * @param int $idtdlist
	 * @return array response content
	 */
	public function getTodoItems($idtdlist) {
		$aret = $this->apiOpen("todo_lists/{$idtdlist}/todo_items.json");
		if (empty($aret['records'])) {
			throw new Exception("Not gets to do items for " . $idtdlist . "\n");
		}
		return $aret['records'];
	}

	/**
	 * get all todo items for all projects(<=25) or ID project
	 * 
	 * @param string|null $idproj ID project
	 * @return array response content
	 */
	public function getAllTodoItems($idproj = null) {
		$aret = array();
		$artdls = $this->getAllTodoLists($idproj);
		foreach ($artdls as $idtdls) {
			usleep($this->apisetts['timeout']);
			$artdits = $this->getTodoItems($idtdls);
			foreach ($artdits as $tdit) {
				$aret[$tdit['id']] = $tdit;
				$aret[$tdit['id']]['fullhash'] = md5(serialize($tdit));
				$aret[$tdit['id']]['projectID'] = $idproj;
			}
		}
		return $aret;
	}

	/**
	 * Get a message
	 *
	 * @param int $idmsg ID message
	 * @return array response content
	 */
	public function getMessage($idmsg) {
		return $this->apiOpen("posts/{$idmsg}.json");
	}

	/**
	 * Get a comment
	 *
	 * @param int $idcomm ID comment
	 * @return array response content
	 */
	public function getComment($idcomm) {
		return $this->apiOpen("comments/{$idcomm}.json");
	}
	
	/**
	 * Creates a new message for a project
	 *
	 * @param int $project_id
	 * @param string $title title of message
	 * @param string $body the body of the message (opt)
	 * @param bool $private set if message is private (opt, default false)
	 * @return int ID new message
	 */
	public function createMessageForProject($idproj, $title = 'Test message', $body = null, $private = false) {
		$body = array(
			 'post' => array(
				  'title' => $title,
				  'body' => $body,
				  'private' => $private
			 )
		);

		$aret = $this->apiPost("projects/{$idproj}/posts.json", json_encode($body));
		if (!isset($aret)) {
			throw new Exception("message for {$idproj} not created.");
		}
		return $aret['id'];
	}

	/**
	 * Creates a new comment for a resource
	 *
	 * @param string $resource
	 * @param int $idresource
	 * @param string $body the body of the comment
	 * @return int ID new comment
	 */
	public function createComment($resource = 'posts', $idresource, $body = 'Test comment') {
		$body = array(
			 'comment' => array(
				  'body' => $body,
			 )
		);
		$aret = $this->apiPost("{$resource}/{$idresource}/comments.json", json_encode($body));
		if (!isset($aret)) {
			throw new Exception("comment for {$resource}/{$idresource} not created.");
		}
		return $aret['id'];
	}

	/**
	 * Delete a message
	 *
	 * @param int $idmsg
	 * @return array response content
	 */
	public function deleteMessage($idmsg) {
		return $this->apiDelete("posts/{$idmsg}.json");
	}

	/**
	 * Delete a comment
	 *
	 * @param int $idcomment
	 * @return array response content
	 */
	public function deleteComment($idcomment) {
		return $this->apiDelete("comments/{$idcomment}.json");
	}
}
?>
