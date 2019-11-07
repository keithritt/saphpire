<?

class MasterDomainsModel extends Model
{
  public function __construct($oDb)
  {
    parent::__construct('master', 'domains', $oDb);
  }
}
