<?php
$this->breadcrumbs=array(
	'Mglists'=>array('index'),
	'Prune',
);
/*
$this->menu=array(
	array('label'=>'Send a message','url'=>array('send')),
	array('label'=>'Create a list','url'=>array('create')),
);
*/
?>

<h1>Prune Members from List</h1>

<?php echo $this->renderPartial('_prune_form',array('model'=>$model)); ?>