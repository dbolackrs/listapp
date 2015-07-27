<?php

/**
 * This is the model class for table "{{mglist}}".
 *
 * The followings are the available columns in table '{{mglist}}':
 * @property integer $id
 * @property string $address
 * @property string $name
 * @property string $description
 * @property string $access_level
 * @property string $created_at
 * @property string $modified_at
 *
 * The followings are the available model relations:
 * @property Membership[] $memberships
 */
class Mglist extends CActiveRecord
{
  
  private $_api_key;
  private $_api_url;
  public $output_str;
  
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Mglist the static model class
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
		return '{{mglist}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('address, name', 'required'),
			array('address, name,access_level', 'length', 'max'=>255),
			array('address, name', 'unique'),
			array('description', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, address, name, description, access_level, created_at, modified_at', 'safe', 'on'=>'search'),
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
			'memberships' => array(self::HAS_MANY, 'Membership', 'mglist_id'),
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
			'description' => 'Description',
			'access_level' => 'Access Level',
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

//		$criteria->compare('mglist_id',$this->id);
//		$criteria->compare('member.address',$this->address,true);
		$criteria->compare('id',$this->id);
		$criteria->compare('address',$this->address,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('access_level',$this->access_level);
		$criteria->compare('created_at',$this->created_at,true);
		$criteria->compare('modified_at',$this->modified_at,true);
		$criteria->order = Yii::app()->request->getParam('sort');;

		return new CActiveDataProvider($this, array(
				'pagination' => array(
	     			'pageSize' => 25,
	),			'criteria'=>$criteria,
		));
	}
	
	public function sync() {
	  // Sync all lists and their members
	  $this->output_str = '';
	  $yg = new Yiigun();
	  $my_lists = $yg->fetchLists();
	  foreach ($my_lists->items as $item) {
  	  $this->output_str.='<p>Synchronizing list: '.$item->name.'<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
  	  // add to local db
          $this->upsert($item);    	          
          $lookup_item=$this->findByAttributes(array('address'=>$item->address));        
    	  $this->syncListMembers($lookup_item['id'],true);
    	  $this->output_str.='<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;fetching members... <br />';  	  
    	  $this->output_str.='</p>';
    }
	  return $this->output_str;
	}
	
	public function syncListMembers($id=0,$in_batch=false) {
	  // fetch list members from Mailgun.com
	  // don't build membership detail report for batch list sync
	  if (is_null($id)) return false;
	  $runningCount=0;
	  $output_str = '';
    $mglist = $this->findByPk($id);
    // PRUNE ALL USERS FROM THE DB FOR THIS LIST
      //Membership::model()->deleteAll('mglist_id=:mglist_id', array(':mglist_id'=>$id));
	  $yg = new Yiigun();
	  // fetch list address based on $id
	  // Loop parameters  - Assume that a list is larger than $listLimit
	  $loadLimit = 100;
	  $currentPosition=0;
	  $fullImportCount=0;
	  do{
		  $my_members = $yg->fetchListMembers($mglist['address'], $currentPosition, $loadLimit );
		  if ( $fullImportCount == 0 )
		  {
		  	$fullImportCount = $my_members->total_count;
		  }	
		  foreach ($my_members->items as $member) 
		  {
		  	$output_str.='<p>Upserting member: '.$member->name.' &lt;'.$member->address.'&gt;<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		    $m = new Member();
		    $runningCount++;
		    // add to local db
	      	$temp_str=$m->upsert($member); 
	  	    $output_str.=$temp_str;
	      	// add to join table
		    $member=Member::model()->findByAttributes(array('address'=>$member->address));
		  	Member::model()->addToList($member['id'],$id);
		  }
		  $currentPosition += count ( $my_members->items );
		} while ( $currentPosition < $fullImportCount );
	  return $output_str;
	}
	
	public function upsert($item) {
	  // create if new, otherwise update fields
	  $lookup_item=$this->findByAttributes(array('address'=>$item->address));
	  if (!is_null($lookup_item)) {
	    $this->updateProperties($lookup_item,$item);
	  } else {
  	  $this->create($item);	    
	  }
	}

  public function updateProperties($mgl,$list) {
    $this->output_str.='updating properties from Mailgun';
    $mgl->name = $list->name;
    $mgl->access_level = $list->access_level;
    $mgl->address = $list->address;
    $mgl->description = $list->description;
    $mgl->modified_at =new CDbExpression('NOW()');
    $mgl->update();
  }
  
  public function create($list) {
    $this->output_str.='creating list...';
    $mgl = new Mglist();
    $mgl->name = $list->name;
    $mgl->access_level = $list->access_level;
    $mgl->address = $list->address;
    $mgl->description = $list->description;
    $mgl->created_at =new CDbExpression('NOW()'); 
    $mgl->modified_at =new CDbExpression('NOW()');          
    //$list->members_count
    $this->output_str.='Saving...'.$mgl->name.'<br />';
    if ($mgl->save()) {
      $this->output_str.='successful';
    } else {
      $this->output_str.='failed to save list - might be duplicate naming';
    }
    $this->output_str.='<br />';
  }	
  
  public function listMembers($id) {
    $membership = Yii::app()->db->createCommand()
             ->select('j.member_id,m.name,m.address')
             ->from(Yii::app()->getDb()->tablePrefix.'membership j')
             ->join(Yii::app()->getDb()->tablePrefix.'member m', 'm.id=j.member_id')
             ->where('j.mglist_id=:id', array(':id'=>$id))
             ->queryAll();
    return $membership;
  }
  
   public function getListOptions()
   {
     $listsArray = CHtml::listData(Mglist::model()->findAll(), 'id', 'name');
     return $listsArray;
  }	
}