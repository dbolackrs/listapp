<?php

class MglistController extends Controller
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
				'actions'=>array('add','verify','remove'),
				'users'=>array('*'),
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('index','view','syncAll','syncList','create','update','send', '_searchemail'),
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
  
  public function actionSyncAll() {
    // sync all lists from Mailgun.com
    # http://www.yiiframework.com/wiki/49/update-content-in-ajax-with-renderpartial/
    $mgl = new Mglist();
    $result = $mgl->sync();
   $this->render('sync',array('result'=>$result));
  }

  public function actionSyncList($id = 0) {
    // syncs an individual list's members from Mailgun.com
     $mgl = new Mglist();
     $result = $mgl->syncListMembers($id);
    $this->render('sync',array('result'=>$result));
  }
  
  
	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id=0)
	{
		$this->render('view',array(
			'model'=>$this->loadModel($id),
			'membership'=> Membership::model()->search($id),
		));
	}
	
	public function action_searchemail($id=0)
	{
		$this->render('_searchemail',array(
			'model'=>$this->loadModel($id),
			'membership'=> Membership::model()->search($id),
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new Mglist;
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);
		if(isset($_POST['Mglist']))
		{
			$model->attributes=$_POST['Mglist'];
			$model->access_level = $_POST['access_level'];
			if ($model->save()) {
			  $yg = new Yiigun();
  		  $yg->listCreate($model);
				$this->redirect(array('view','id'=>$model->id));
			}
		}

		$this->render('create',array(
			'model'=>$model,
		));
	}

  public function showStatus($membership) {    
    if (!isset($membership->member->status))
      return 'n/a';
    if ($membership->member->status == 0)
      return 'unsubscribed';
    else
      return 'subscribed';
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
		$existing_address = $model->address;
		if(isset($_POST['Mglist']))
		{
			$model->attributes=$_POST['Mglist'];
			$model->access_level = $_POST['access_level'];
		  $yg = new Yiigun;        
		  $result = $yg->listUpdate($existing_address,$model);			
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
			$model = $this->loadModel($id);
			// delete at Mailgun
		  $yg = new Yiigun;        
		  $yg->listDelete($model->address);			
		  // delete locally
		  $model->delete(); 

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
	    $this->actionAdmin();
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{	  
		$model=new Mglist('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Mglist']))
			$model->attributes=$_GET['Mglist'];
    	$cnt = Mglist::model()->count();
		$this->render('admin',array(
			'model'=>$model,'cnt'=>$cnt
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model=Mglist::model()->findByPk($id);
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
		if(isset($_POST['ajax']) && $_POST['ajax']==='mglist-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

}
