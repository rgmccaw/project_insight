<?php

class UsersController extends Controller {

	var $helpers = array('Form');

	function add() {
		if(!empty($this->data)) {
			if(empty($this->data['User']['username'])) {
				$this->Session->setFlash('You cannot leave the username blank');
			} elseif (strlen($this->data['User']['username']) < 3) {
				$this->Session->setFlash('Username length must be at least 3 characters.');
			} elseif (!ctype_alnum($this->data['User']['username'])) {
				$this->Session->setFlash('Username must be alphanumeric.');
			} else {
				$this->User->save($this->data);
				$this->Session->setFlash('User saved.');
			}
		}

		/**
		 * Eg: find('all', array(
		 *			'conditions' => array('name' => 'Thomas Anderson'),
		 *			'fields' => array('name', 'email'),
		 *			'order' => 'field3 DESC',
		 *			'recursive' => 2,
		 *			'group' => 'type'));
		 */

		$users = $this->User->find('all', array());

		$size  = sizeof($users);
		for ($i=0; $i<$size; $i++) {
			for ($c=0; $c<$size; $c++) {
				if ($users[$i]['User']['username'] < $users[$c]['User']['username']) {
					$tmp = $users[$c];
					$users[$c] = $users[$i];	
					$users[$i] = $tmp;
				}
			}

		}

		$displayUsers = array();
		foreach ($users as $user) {
			$displayUsers[] = '<b>'.$user['User']['username'].'</b> has the ID '.$user['User']['id'].'.<br />';
		}

		$this->set(compact('displayUsers'));
	}

	function delete() {
		$this->User->deleteAll('1 = 1');
		$this->redirect('add');
	}

}
