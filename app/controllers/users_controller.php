<?php

class UsersController extends Controller {

	var $helpers = array('Form');

	function add() {
		if(!empty($this->data)) {
			$this->User->save($this->data);
		}

		$displayUsers = $this->User->find('all');
		$this->set(compact('displayUsers'));
	}

	function delete() {
		$this->User->deleteAll('1 = 1');
		$this->redirect('add');
	}

}
