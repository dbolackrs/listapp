<?php

/**
 * This is the model class for table "{{member}}".
 *
 * The followings are the available columns in table '{{member}}':
 * @property integer $id
 * @property string $address
 * @property string $name
 * @property string $vars
 * @property integer $status
 * @property string $created_at
 * @property string $modified_at
 *
 * The followings are the available model relations:
 * @property Membership[] $memberships
 */
class Member extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Member the static model class
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
		return '{{member}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('address, modified_at', 'required'),
			array('status', 'numerical', 'integerOnly'=>true),
			array('address, name', 'length', 'max'=>255),
			array('vars, created_at', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, address, name, vars, status, created_at, modified_at', 'safe', 'on'=>'search'),
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
			'memberships' => array(self::HAS_MANY, 'Membership', 'member_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'address' => 'Address',
			'name' => 'Name',
			'vars' => 'Vars',
			'status' => 'Status',
			'created_at' => 'Created At',
			'modified_at' => 'Modified At',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('address',$this->address,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('vars',$this->vars,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('created_at',$this->created_at,true);
		$criteria->compare('modified_at',$this->modified_at,true);
    
		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	
	// create a new member, or update fields of existing member
	public function upsert($member) {
$lookup_item=$this->findByAttributes(array('address'=>$member->address));
	  if (!is_null($lookup_item)) {
	    $this->updateProperties($lookup_item,$member);
      return 'updating properties from Mailgun';
	  } else {
  	  return $this->create($member);	          
	  }
	}

  // update member properties in local db
  public function updateProperties($member_in_db,$member_at_mg) {
    $member_in_db->name = $member_at_mg->name;
    $member_in_db->address = $member_at_mg->address;
    if ($member_at_mg->subscribed)
      $member_in_db->status = 1;
    else
      $member_in_db->status = 0; // unsubscribed
    $member_in_db->modified_at =new CDbExpression('NOW()');
    $member_in_db->update();
  }

  // create member in local db
  public function create($member) {
    $output_str='creating member...';
    $this->name = $member->name;
    $this->address = $member->address;
    if ($member->subscribed)
      $this->status = 1;
    else
      $this->status = 0; // unsubscribed
    $this->created_at =new CDbExpression('NOW()'); 
    $this->modified_at =new CDbExpression('NOW()');          
    $output_str.='Saving...'.$this->name.'<br />';
    if ($this->save()) {
      $output_str='successful';
    } else {
      $output_str.='failed';
    }
    $output_str.='<br />';
    return $output_str;
  }

	public function addToList($member_id,$mglist_id) {
	  if (!$this->isMember($member_id,$mglist_id)) {
  	  $m=new Membership;
  	  $m->member_id=$member_id;
  	  $m->mglist_id=$mglist_id;
      $m->created_at = new CDbExpression('NOW()'); 
      $m->modified_at = new CDbExpression('NOW()');          	  
  	  $m->save();
	  } else
	    return false; // already a member
	}

	public function removeFromList($member_id,$mglist_id) {
	  if ($this->isMember($member_id,$mglist_id)) {
      Membership::model()->deleteAll('mglist_id='.$mglist_id.' and member_id='.$member_id);
      return true;
    } else
      return false;
	}

  public function isMember($member_id,$mglist_id) {
    if (Membership::model()->find(array(
          'select'=>'id', 'condition'=>'member_id=:member_id and mglist_id=:mglist_id', 'params'=>array(':member_id'=>$member_id,':mglist_id'=>$mglist_id)))
        ===null)
        return false;
      else
        return true;
  }

	
}