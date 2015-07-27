<?php
$this->breadcrumbs=array(
	'Mglists'=>array('index'),
	'Manage',
);

if ($cnt ==0)
  $this->menu=array(
  	array('label'=>'Create a list','url'=>array('create')),
  );
else
$this->menu=array(
  array('label'=>'Send a message','url'=>array('message/create')),
	array('label'=>'Create a list','url'=>array('create')),
);


Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('mglist-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Manage Mailing Lists</h1>
<div class="search-form" style="display:none">
<?php
 $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->

<?php $this->widget('bootstrap.widgets.TbGridView',array(
	'id'=>'mglist-grid',
	'dataProvider'=>$model->search(),
	'type'=>'striped',
//	'filter'=>$model,
	'columns'=>array(
    array(
            'type' => 'raw',
            'header' => 'Name',
            'name'=>'name',
            'value' => 'CHtml::link($data->name,array(\'mglist/view\',\'id\'=>$data->id))',
            'htmlOptions'=>array('width'=>'150px')
          ),
          array(
                  'type' => 'raw',
                  'header' => 'Address',
                  'name'=>'address',
                  'value' => 'CHtml::link($data->address,array(\'mglist/view\',\'id\'=>$data->id))',
                  'htmlOptions'=>array('width'=>'150px')
            ),
		'access_level',
		/*
		'created_at',
		'modified_at',
		*/
		array(
		  'htmlOptions'=>array('width'=>'150px'),  		
			'class'=>'bootstrap.widgets.TbButtonColumn',
			'header'=>'Options',
      'template'=>'{send}{manage}{sync}{update}{delete}{subscribe}',
          'buttons'=>array
          (
              'send' => array
              (
                'options'=>array('title'=>'Send'),
                'label'=>'<i class="icon-envelope icon-large" style="margin:5px;"></i>',
                'url'=>'Yii::app()->createUrl("message/create", array("id"=>$data->id))',
              ),
              'manage' => array
              (
              'options'=>array('title'=>'Manage'),
                'label'=>'<i class="icon-list icon-large" style="margin:5px;"></i>',
                'url'=>'Yii::app()->createUrl("mglist/view", array("id"=>$data->id))',
              ),
              'sync' => array
              (
              'options'=>array('title'=>'sync'),
                'label'=>'<i class="icon-refresh icon-large" style="margin:5px;"></i>',
                'url'=>'Yii::app()->createUrl("mglist/syncList", array("id"=>$data->id))',
              ),
              'subscribe' => array
              (
              'options'=>array('title'=>'subscribe form'),
                'label'=>'<i class="icon-user icon-large" style="margin:5px;"></i>',
                'url'=>'Yii::app()->createUrl("request/create", array("id"=>$data->id))',
              ),
          ),			
		),
	),
)); ?>
