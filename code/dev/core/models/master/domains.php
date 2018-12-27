<?

class MasterDomainsModel extends Model
{
  public function __construct($oDb)
  {
    //parent::__construct(SCHEMA_PREFIX.'master_'.ENV, 'domains', $oDb);
    parent::__construct('master', 'domains', $oDb);
  }


  public function savexx()
  {
    //pr('PersonModel->save()');
    foreach($this->aData as $sKey => $vVal)
    {
      //expose($vVal);
    }
  }
}