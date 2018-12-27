<?

$sNavBarClass = @Util::coalesce($this->sNavBarClass, 'navbar navbar-inverse navbar-fixed-top');
$bShowLogin = @Util::coalesce($this->bShowLogin, false);
//expose(Session::get_cookie('login_email'));
$sLoginEmail = @Util::coalesce($this->sLoginEmail, Session::get_cookie('login_email'));
$sEmailValue = '';
if(isset($sLoginEmail))
  $sEmailValue = 'value="'.$sLoginEmail.'"';

//expose($sEmailValue);

//expose($this->aNavLinks);

$aHtml = array();

$aHtml[] = '
										<nav class="navbar '.$sNavBarClass.'">
											<div class="container">
												<div class="navbar-header">
													<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
														<span class="sr-only">Toggle navigation</span>
														<span class="icon-bar"></span>
														<span class="icon-bar"></span>
														<span class="icon-bar"></span>
													</button>';
if(isset($this->sBrand))
{
   $aHtml[]= '
	 												<a class="navbar-brand" href="/">'.$this->sBrand.'</a>';
}
$aHtml[] = '
												</div>
												<div id="navbar" class="collapse navbar-collapse">';
if(isset($this->aNavLinks))
{
	$aHtml[]=  '
													<ul class="nav navbar-nav">';
	foreach($this->aNavLinks as $aNavLink)
	{
    //expose($aNavLink);
    if(isset($aNavLink['options']))
    {

      @$aHtml[] = '

                          <li class="dropdown '.$aNavLink['class'].'">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                             '.$aNavLink['text'].'<span class="caret"></span></a>
                            <ul class="dropdown-menu" role="menu">

            ';
    }
    else
    {
      @$aHtml[] =  '
                            <li class="'.$aNavLink['class'].'"><a href="'.$aNavLink['href'].'" target="'.$aNavLink['target'].'">'.$aNavLink['text'].'</a></li>';
    }

    if(isset($aNavLink['options']))
    {

      foreach($aNavLink['options'] as $aOption)
      {
        @$aHtml[] = '
                              <li><a href="'.$aOption['href'].'" target="'.$aOption['target'].'">'.$aOption['text'].'</a></li>';
      }

      $aHtml[] = '
                            </ul>
                            </li>';

    }
  }
	$aHtml[] =  '
													</ul>';
}

if($bShowLogin)
{
	if(isset($this->aLogin['member_data']) && count($this->aLogin['member_data']))
	{

		$aHtml[] =  '
													<span class="navbar-right">
														<button type="button" class="btn btn-default navbar-btn disabled">Logged in as: <b>';

		$sNames = '';
		foreach($this->aLogin['member_data'] as $sKey => $aUser)
				 $sNames.=  $aUser['first_name'].' & ';

		$sNames = rtrim($sNames, ' & ');
		$aHtml[] =
														$sNames;
		$aHtml[] =  '
															</b>
														</button>';

		$aHtml[]=  '
														<a href="/login/leave" class="btn btn-primary bold">Log out</a>
													</span>';
	}
	else
	{

    $aHtml[]=  '
													<form class="navbar-form navbar-right" action="/login" method="post">
														<div class="form-group">
															<input name="email" type="text" placeholder="Email" '.$sEmailValue.' class="form-control">
														</div>
														<div class="form-group">
															<input name="password" type="password" placeholder="Password" class="form-control">
														</div>
														<button type="submit" class="btn btn-primary" style="font-weight:bold;">Log in</button>
													</form>';
	}
}

$aHtml[] = '
												</div><!--/.nav-collapse -->
											</div>
										</nav>';

return implode("\n", $aHtml);

//line();
//$a
//$this->add_css('/application/css/default.css');
//line();



//line();
$aHtml = array();
$aHtml[] = '
<div class="'.$this->sNavBarClass.'">
  <div class="container">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <div class="container">';

					//@expose($sBrand);
					if(isset($this->sBrand))
        		$aHtml[]= '<a class="navbar-brand" href="/">'.$this->sBrand.'</a>';

      //line();
      $aHtml[] = '
      </div>
    </div>
    <div class="navbar-collapse collapse">';

      if(isset($this->aNavLinks))
      {
        $aHtml[]=  '<ul class="nav navbar-nav">';
        foreach($this->aNavLinks as $aNavLink)
        {
          if(isset($aNavLink['options']))
          {
            line();
            $aHtml[] = '<li class="dropdown">';
            $aHtml[] = '<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">';
            $aHtml[] = $aNavLink['text'] ;
            $aHtml[] = '<span class="caret"></span></a>';
          }
          else
            @$aHtml[] =  '<li class="'.$aNavLink['class'].'"><a href="'.$aNavLink['href'].'">'.$aNavLink['text'].'</a></li>';

          if(isset($aNavLink['options']))
          {

            foreach($aNavLink['options'] as $aOption)
            {
              $aHtml[] = '<li><a href="'.$aOption['href'].'">'.$aOption['text'].'</a></li>';
            }

            $aHtml[] = '</li>';

          }




//              <ul class="dropdown-menu" role="menu">
//
//                <li><a href="#">Another action</a></li>
//                <li><a href="#">Something else here</a></li>
//                <li class="divider"></li>
//                <li class="dropdown-header">Nav header</li>
//                <li><a href="#">Separated link</a></li>
//                <li><a href="#">One more separated link</a></li>
//              </ul>
//            </li>


        }
        $aHtml[]=  '</ul>';
      }

      //line();

			if($bShowLogin)
			{
				if(isset($this->aLogin['member_data']) && count($this->aLogin['member_data']))
				{

						$aHtml[]=  '<span class="navbar-right">

							<button type="button" class="btn btn-default navbar-btn disabled">Logged in as: <b>';
							//expose($aLogin);
							//print $aLogin['first_name'];
							$sNames = '';
							foreach($this->aLogin['member_data'] as $sKey => $aUser)
							{
									 $sNames.=  $aUser['first_name'].' & ';
							}
							$sNames = rtrim($sNames, ' & ');
							$aHtml[]=  $sNames;
							$aHtml[]=  '</b></button>';
				//<a data-toggle="modal" href="#job_title_modal" class="btn btn-primary" style="font-weight:bold;">Log in another user</a>

							$aHtml[]=  ' <a href="/login/leave" class="btn btn-primary bold">Log out</a>
							</span>
						';
				}
				else
				{
					$aHtml[]=  '
					<form class="navbar-form navbar-right" action="/login" method="post">
						<div class="form-group">
							<input name="email" type="text" placeholder="Email" class="form-control">
						</div>
						<div class="form-group">
							<input name="password" type="password" placeholder="Password" class="form-control" style="width: 135px;">
						</div>
						<button type="submit" class="btn btn-primary" style="font-weight:bold;">Log in</button>
					</form>
					';
				}
			}
      //line();
    $aHtml[]= '

    </div>
  </div>
</div>';


//expose($aHtml);
return implode("\n", $aHtml);







