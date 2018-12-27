<?

// currently assumes that people and emails are a 1:1 ratio to member
class MasterMembersModel extends Model
{
  public function __construct($oDb)
  {

    //parent::__construct(SCHEMA_PREFIX.'master_'.ENV, 'members', $oDb);
    parent::__construct('master', 'members', $oDb);
    //$this->oPerson = Model::init('master', 'person', $this->oDb);
    //$this->aSubModels['person'] =  Model::init('master', 'person', $oDb);
    //$this->aSubModels['member_email_xref']= Model::init('master', 'member_email_xref', $oDb);
    //$this->aSubModels['email_addresses']= Model::init('master', 'email_addresses', $oDb);
  }

  //@TODO - note that this sql will allow for duplicate rows if a member is related to multiple domains
  // the domain id is not returned
  public function fetch($vLookup, $vExpected = 1)
  {
    //pr('Members->fetch()');
    $this->sGetSqlStart = "
    SELECT
      members.id,
      members.password,
      people.id person_id,
      people.first_name,
      people.last_name,
      member_email_xref.id email_xref_id,
      email_addresses.id email_id,
      email_addresses.email
    FROM
      ".$this->sFqTable."
    LEFT JOIN
      ".$this->sFqSchema.".member_email_xref ON (member_email_xref.member_id = members.id)
    LEFT JOIN
      ".$this->sFqSchema.".email_addresses ON (email_addresses.id = member_email_xref.email_id)
    LEFT JOIN
      ".$this->sFqSchema.".people ON (people.id = members.person_id)
    LEFT JOIN
      ".$this->sFqSchema.".member_domain_xref ON (member_domain_xref.member_id = members.id)
      ";

    //WHERE
    //  ".Db::build_where($aLookup);

    //expose($this->sGetSqlStart);
    //die();

    // iterate through $vLookup (if its an array) and clarify table origin
    if(is_array($vLookup))
    {
      $aTmp = array();
      foreach($vLookup as $sColumn => $vValue)
      {
        switch($sColumn)
        {
          case 'email':
            $aTmp['email_addresses.email'] = $vValue; // @TODO - make sure we dont need to escape this
            break;
          case 'domain_id':
            $aTmp['member_domain_xref.domain_id'] = $vValue;
            break;
          default:
            $aTmp[$sColumn] = $vValue;
            break;
        }
      }
      $vLookup = $aTmp;
    }

      parent::fetch($vLookup, $vExpected);

      //$this->InitSubModel('master', 'people');
      //$this->aSubModels[$this->iCursor]['master.people']->$sCol = $vVal;

      //$this->

      //expose($this->aData);
      //die();

      //if(isset($this->aData[$this->iCursor]['']))


  }

  public function __set($sCol, $vVal)
  {
    //pr('members->set('.$sCol.', '.$vVal.')');
    switch($sCol)
    {
      case 'first_name':
      case 'last_name':
        $this->InitSubModel('master', 'people');
        $this->aSubModels[$this->iCursor]['master.people']->$sCol = $vVal;
        break;
      case 'email':
        $this->InitSubModel('master', 'email_addresses');
        $this->InitSubModel('master', 'member_email_xref');
        $this->aSubModels[$this->iCursor]['master.email_addresses']->$sCol = $vVal;
        break;
      default:
        parent::__set($sCol, $vVal);
        break;
    }

  }

  //TODO - check for changes before saving
  public function save($bClear = false)
  {
    pr('MemberModel->save()');
    if(isset($this->aSubModels[$this->iCursor]['master.people']))
    {
      $this->aSubModels[$this->iCursor]['master.people']->save();
    //expose($this->aSubModels[$this->iCursor]['master.people']->id);
    //die();

      $this->aData[$this->iCursor]['person_id'] = $this->aSubModels[$this->iCursor]['master.people']->id;
    }
    //expose($this->aData);
    parent::save();
    //$this->aSubModels[$this->iCursor]['email_addresses']->member_id = $this->aData[$this->iCursor->id];
    if(isset($this->aSubModels[$this->iCursor]['master.email_addresses']))
    {
      //line();
      $this->aSubModels[$this->iCursor]['master.email_addresses']->save();
    }

    //expose($this->aSubModels[$this->iCursor]['master.email_addresses']->id);

    if(isset($this->aSubModels[$this->iCursor]['master.member_email_xref']))
    {
      //line();
      $this->aSubModels[$this->iCursor]['master.member_email_xref']->member_id = $this->aData[$this->iCursor]['id'];
      $this->aSubModels[$this->iCursor]['master.member_email_xref']->email_id = $this->aSubModels[$this->iCursor]['master.email_addresses']->id;
      //expose($this->aSubModels[$this->iCursor]['email_addresses']->id);
      //expose($this->aSubModels[$this->iCursor]['master.member_email_xref']->email_id);
      $this->aSubModels[$this->iCursor]['master.member_email_xref']->save();
    }
    //pr('end MemberModel->save()');
  }

  public function delete($bClear = true)
  {
    //hit();
    //pr('MemberModel->delete()');
    //expose($this->iCursor);
    //expose($this->aData);
    //expose($this->aSubModels);
    if(isset($this->aData[$this->iCursor]['person_id']))
    {
      //line();
      //expose($this->aData[$this->iCursor]['person_id']);
      $this->InitSubModel('master', 'people', $this->aData[$this->iCursor]['person_id'], 'any');
      //line();
      $this->aSubModels[$this->iCursor]['master.people']->delete($bClear);
      //line();
    }
    //else
    //  expose($this->aData[$this->iCursor]);

    //line();

    if(isset($this->aData[$this->iCursor]['email_id']))
    {
      $this->InitSubModel('master', 'email_addresses', $this->aData[$this->iCursor]['email_id'], 'any');
      $this->aSubModels[$this->iCursor]['master.email_addresses']->delete($bClear);
    }
    //line();

    if(isset($this->aData[$this->iCursor]['member_email_xref']))
    {
      $this->InitSubModel('master', 'email_addresses', $this->aData[$this->iCursor]['email_xref_id'], 'any');
      $this->aSubModels[$this->iCursor]['master.email_addresses']->delete($bClear);
    }
    //line();
    parent::delete($bClear);
    //pr('end MemberModel->delete()');
  }

  public function set_employment($iEmployerId, $iPositionId = null, $iStatusId = TYPE_STATUS_ACTIVE, $iEmployerTypeId = TYPE_EMPLOYER_BAR)
  {
    //pr('members->set_employment()');
    //expose($this->aData);
    // commenting out because person_id is not guarenteed to be set
    //$this->InitSubModel('master', 'people', $this->aData[$this->iCursor]['person_id']);
    $this->InitSubModel('master', 'people');
    $this->aSubModels[$this->iCursor]['master.people']->set_employment($iEmployerId, $iPositionId, $iStatusId, $iEmployerTypeId);
  }

  public function fetch_employment()
  {
    //pr('members->fetch_employment()');
    //expose($this->aData);
    $this->InitSubModel('master', 'people', $this->aData[$this->iCursor]['person_id']);
    return $this->aSubModels[$this->iCursor]['master.people']->fetch_employment();
  }
}