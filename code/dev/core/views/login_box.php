<?

$this->add_js('/third_party/js_md5/1/md5.js');
$this->add_js('/code/'.CODE_ENV.'/core/js/login.js');

$aHtml = array();

$aHtml[] = '
  <div class="container">
    <div class="row">
      <div class="col-md-offset-5 col-md-3">
        <div class="form-login">
          <form id="navbar_login" class="navbar-form navbar-right" action="/login" method="post" onsubmit="oNs.log_in();">
            <input id="pw_hash" name="pw_hash" type="hidden">
            <input type="text" name="email" class="form-control input-sm chat-input" placeholder="email" autofocus/>
            </br>
            <input type="password" id="password" class="form-control input-sm chat-input" placeholder="password" />
            </br>
            <button class="btn btn-primary" style="font-weight:bold;">Log in</button>
          </form>
        </div>
      </div>
    </div>
  </div>';




return implode($aHtml, "\n");