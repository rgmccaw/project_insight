<?php

class User extends Model {

	var $validate = array(
		'username' => array('notempty'),
	);

}
