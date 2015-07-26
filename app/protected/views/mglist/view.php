<?php
$this->breadcrumbs=array(
	'Mglists'=>array('index'),
	$model->name,
);

$this->menu=array(
	array('label'=>'Send a message','url'=>Yii::app()->createUrl("message/create", array("id"=>$model->id))),
	array('label'=>'Add a member','url'=>Yii::app()->createUrl("member/create", array("id"=>$model->id))),
	array('label'=>'Import members','url'=>Yii::app()->createUrl("member/import", array("id"=>$model->id))),
	array('label'=>'Remove Users','url'=>Yii::app()->createUrl("member/prune", array("id"=>$model->id))),
	
	array('label'=>'Update Properties','url'=>array('update','id'=>$model->id)),
	array('label'=>'Delete','url'=>'#','linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'View subscribe form','url'=>array('request/create','id'=>$model->id)),
  
);
?>

<h1>List: <?php echo $model->name; ?></h1>
<?php 

$this->widget('bootstrap.widgets.TbGridView',array(
	'id'=>'mglist-grid',
	'dataProvider'=>$membership,
	'type'=>'striped',
//	'filter'=>$membership,
	'columns'=>array(
		'member.name',
  	'member.address',
    array(            
                'name'=>'member.status',
                //call the method 'gridDataColumn' from the controller
                'value'=>array($this,'showStatus'), 
            ),
	),
)); ?>
