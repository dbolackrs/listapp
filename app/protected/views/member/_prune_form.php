<?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm',array(
	'id'=>'prune-form',
	'enableAjaxValidation'=>false,
)); ?>

	<p class="help-block">Fields with <span class="required">*</span> are required.</p>

	<?php // echo $form->errorSummary($model); ?>

	<?php echo $form->textAreaRow($model,'email_list',array('rows'=>6, 'cols'=>50, 'class'=>'span8')); ?>

	<div class="form-actions">
		<?php $this->widget('bootstrap.widgets.TbButton', array(
			'buttonType'=>'submit',
			'type'=>'primary',
			'label'=>'Submit',
		)); ?>
	</div>

<?php $this->endWidget(); ?>
