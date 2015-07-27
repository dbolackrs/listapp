<?php

/**
 * This is the model class for table "{{membership}}".
 *
 * The followings are the available columns in table '{{membership}}':
 * @property integer $id
 * @property integer $mglist_id
 * @property integer $member_id
 * @property string $created_at
 * @property string $modified_at
 *
 * The followings are the available model relations:
 * @property Member $member
 * @property Mglist $mglist
 */
class Membership extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Membership the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{membership}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('modified_at', 'required'),
			array('mglist_id, member_id', 'numerical', 'integerOnly'=>true),
			array('created_at', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, mglist_id, member_id, created_at, modified_at', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'member' => array(self::BELONGS_TO, 'Member', 'member_id'),
			'mglist' => array(self::BELONGS_TO, 'Mglist', 'mglist_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'mglist_id' => 'Mglist',
			'member_id' => 'Member',
			'created_at' => 'Created At',
			'modified_at' => 'Modified At',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search($mglist_id)
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->limit = -1;
		$criteria->compare('mglist_id',$this->mglist_id);
		$criteria->compare('member_id',$this->member_id);
		$criteria->compare('created_at',$this->created_at,true);
		$criteria->compare('modified_at',$this->modified_at,true);
		$criteria->condition='mglist_id='.$mglist_id;
	    $criteria->join='left join la_member on la_member.id = member_id';
        $criteria->order = Yii::app()->request->getParam('sort');
        
		return new CActiveDataProvider($this, array(
				'pagination' => array(
	     		'pageSize' => 50,
				),			
	'criteria'=>$criteria,
		));
	}
}