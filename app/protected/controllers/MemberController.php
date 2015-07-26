<?php

class MemberController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/column2';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('remove'),
				'users'=>array('*'),
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('create','update','import','index','view','prune'),
				'users'=>array('@'),
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('admin','delete'),
				'users'=>array('admin'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionRemove($id,$mglist_id,$hash)
	{
	  // check hash is correct
	  // remove member from list
	  $member = Member::model()->findByPk($id);
	  $mglist = Mglist::model()->findByPk($mglist_id);
	  $member->removeFromList($id,$mglist_id);
		$this->render('remove',array(
			'member'=>$member,
			'mglist'=>$mglist,
		));
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$this->render('view',array(
			'model'=>$this->loadModel($id),
		));
	}

	/**
	 * Creates a new member.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate($id)
	{
		$model=new Member;
		$mglist_id = $id;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Member']))
		{
			$model->attributes=$_POST['Member'];
      $model->status=1;
			$model->created_at =new CDbExpression('NOW()'); 
      $model->modified_at =new CDbExpression('NOW()');                
			if($model->save())
			  $model->addToList($model->id,$mglist_id);
			  $lookup_list = Mglist::model()->findByPk($mglist_id);
			  // to do fetch list address
			  $yg = new Yiigun();
			  $yg->memberAdd($lookup_list['address'],$model->address,$model->name);
				$this->redirect('/mglist/'.$mglist_id);
		}

		$this->render('create',array(
			'model'=>$model,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Member']))
		{
			$model->attributes=$_POST['Member'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('update',array(
			'model'=>$model,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		if(Yii::app()->request->isPostRequest)
		{
			// we only allow deletion via POST request
			$this->loadModel($id)->delete();

			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			if(!isset($_GET['ajax']))
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
		}
		else
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$dataProvider=new CActiveDataProvider('Member');
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model=new Member('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Member']))
			$model->attributes=$_GET['Member'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

  /**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionPrune($id=0)
	{
	        $model = new Prune();
    		$model->mglist_id = $id;
    		// Uncomment the following line if AJAX validation is needed
    		// $this->performAjaxValidation($model);
    		if(isset($_POST['Prune']))
    		{
    			$temp_email_list = $_POST['Prune']['email_list'];
          include('Mail/RFC822.php');
          $parser = new Mail_RFC822();
          // replace the backslash quotes 
          $tolist=str_replace('\\"','"',$temp_email_list); 
          // split the elements by line and by comma 
          $to_email_array = preg_split ("(\r|\n|,)", $tolist, -1, PREG_SPLIT_NO_EMPTY);
          $num_emails = count ($to_email_array); 
          $temp ='';
          // construct bulk list of new members for mailgun api call
          $yg = new Yiigun;
          $list_item = Mglist::model()->findByPk($id);
          for ($count = 0; $count < $num_emails && $count <= 500; $count++) 
          {
            $toAddress=trim($to_email_array[$count]);
            if ($toAddress<>'') {
              $addresses = $parser->parseAddressList('my group:'.$toAddress,'yourdomain.com', false,true);
              foreach ($addresses as $i) {
                if ($i->mailbox<>'' and $i->host<>'') {
                  $temp.=$i->mailbox.'@'.$i->host.',';
                }
                $m = new Member();
                if ($i->personal<>'') {
                  $m->name = $i->personal;
                }
                else 
                  $m->name ='';
                $m->address=$i->mailbox.'@'.$i->host;
                $m->status=1;
                $m->created_at = new CDbExpression('NOW()'); 
                $m->modified_at = new CDbExpression('NOW()');          	                  
                 // echo $m->name.' '.$m->address.' ->'.$id.'<br />';
 			    $lookup_item=Member::model()->findByAttributes(array('address'=>$m->address));
            	  if (!is_null($lookup_item)) {
            	       // member exists
                      echo 'exists'.$lookup_item['id'];
                      $m->removeFromList($lookup_item['id'],$id);
		              $yg->memberDelete($list_item['address'],$m->address);
            	  }
              } 
            }
          }      
          
    	if($model->save()) {
            Yii::app()->user->setFlash('prune_success','Thank you! Your messages have been submitted.');
            // echo $json_upload;
            // echo $list_item['address'];
  				  $this->redirect('/mglist/'.$id);			  
    			}
    		}

    		$this->render('prune',array(
    			'model'=>$model,'mglist_id'=>$id,
    		));
	}

  /**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionImport($id=0)
	{
	      $model = new Import();
    		$model->mglist_id = $id;
    		// Uncomment the following line if AJAX validation is needed
    		// $this->performAjaxValidation($model);
    		if(isset($_POST['Import']))
    		{
    			$temp_email_list = $_POST['Import']['email_list'];
          include('Mail/RFC822.php');
          $parser = new Mail_RFC822();
          // replace the backslash quotes 
          $tolist=str_replace('\\"','"',$temp_email_list); 
          // split the elements by line and by comma 
          $to_email_array = preg_split ("(\r|\n|,)", $tolist, -1, PREG_SPLIT_NO_EMPTY);
          $num_emails = count ($to_email_array); 
          $temp ='';
          // construct bulk list of new members for mailgun api call
          $json_upload ='[';          
          for ($count = 0; $count < $num_emails && $count <= 500; $count++) 
          {
            $json_upload.='{';
            $toAddress=trim($to_email_array[$count]);
            if ($toAddress<>'') {
              $addresses = $parser->parseAddressList('my group:'.$toAddress,'yourdomain.com', false,true);
              foreach ($addresses as $i) {
                if ($i->mailbox<>'' and $i->host<>'') {
                  $temp.=$i->mailbox.'@'.$i->host.',';
                }
                $m = new Member();
                if ($i->personal<>'') {
                  $m->name = $i->personal;
                  $json_upload.='"name": "'.$m->name.'", ';                  
                }
                else 
                  $m->name ='';
                $m->address=$i->mailbox.'@'.$i->host;
                $json_upload.='"address": "'.$m->address.'"';                  
                $m->status=1;
                $m->created_at = new CDbExpression('NOW()'); 
                $m->modified_at = new CDbExpression('NOW()');          	                  
                 // echo $m->name.' '.$m->address.' ->'.$id.'<br />';
 $lookup_item=Member::model()->findByAttributes(array('address'=>$m->address));
            	  if (!is_null($lookup_item)) {
            	       // member exists
                     // echo 'exists'.$lookup_item['id'];
            	      $m->addToList($lookup_item['id'],$id);
            	  } else {
            	    // new member
                  $m->save();
                  $last_id = Yii::app()->db->getLastInsertID();
                  // echo 'saved'.$last_id;
                  $m->addToList($last_id,$id);
            	  }
              } 
            }
            $json_upload.='},';            
          }      
          $temp=trim($temp,',');
          $model->email_list = $temp;
          $json_upload=trim($json_upload,',');
          $json_upload .=']';
          
    			if($model->save()) {
            Yii::app()->user->setFlash('import_success','Thank you! Your messages have been submitted.');
            $yg = new Yiigun;
            // echo $json_upload;
            $list_item = Mglist::model()->findByPk($id);
            // echo $list_item['address'];
            $yg->memberBulkAdd($list_item['address'],$json_upload);
  				  $this->redirect('/mglist/'.$id);			  
    			}
    		}

    		$this->render('import',array(
    			'model'=>$model,'mglist_id'=>$id,
    		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model=Member::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='member-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
