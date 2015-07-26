<?php

class Prune extends CFormModel
{
  public $mglist_id;
  public $email_list;
  
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{message}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('mglist_id', 'numerical', 'integerOnly'=>true),
			array('email_list', 'length', 'max'=>5000),
			array('mglist_id, email_list', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'mglist_id' => 'Mglist',
			'email_list' => 'List of emails',
		);
	}

  public function save() {
    return true;
  }

}