<?php
echo $form->create('User');
echo $form->input('username');
echo $form->submit('Add a new User!!');
echo $form->end();

echo $form->create('User', array('action' => 'delete'));
echo $form->submit('Delete All Users!!!!!');
echo $form->end();

foreach ($displayUsers as $user) {
	echo $user;
}
?>
