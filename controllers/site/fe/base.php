<?php
namespace site\fe;
/**
 * 站点前端访问控制器基类
 */
class base extends \TMS_CONTROLLER {
	/**
	 * 当前访问的站点ID
	 */
	protected $siteId;
	/**
	 * 当前访问用户
	 */
	protected $who;
	/**
	 * 对请求进行通用的处理
	 */
	public function __construct() {
		empty($_GET['site']) && die('参数错误！');
		$siteId = $_GET['site'];
		$this->siteId = $siteId;
		/* 获得访问用户的信息 */
		$modelWay = $this->model('site\fe\way');
		$this->who = $modelWay->who($siteId);
	}
	/**
	 * 检查是否当前的请求是OAuth后返回的请求
	 */
	public function afterSnsOAuth() {
		/* 当前用户的身份信息 */
		$auth = array();
		if (isset($_GET['mocker'])) {
			/* 指定的模拟用户 */
			list($snsName, $openid) = explode(',', $_GET['mocker']);
			$snsUser = new \stdclass;
			$snsUser->openid = $openid;
			$auth['sns'][$snsName] = $snsUser;
		} else if ($this->myGetcookie("_{$this->siteId}_oauthpending") === 'Y') {
			/* oauth回调 */
			$this->mySetcookie("_{$this->siteId}_oauthpending", '', time() - 3600);
			if (isset($_GET['state']) && isset($_GET['code'])) {
				$state = $_GET['state'];
				if (strpos($state, 'snsOAuth-') === 0) {
					$code = $_GET['code'];
					$snsName = explode('-', $state)[1];
					$snsUser = $this->snsOAuthUserByCode($this->siteId, $code, $snsName);
					$auth['sns'][$snsName] = $snsUser;
				}
			}
		}
		if (!empty($auth)) {
			/* 如果获得了用户的身份信息，更新保留的用户信息 */
			$modelWay = $this->model('site\fe\way');
			$this->who = $modelWay->who($this->siteId, $auth);
			return true;
		}

		return false;
	}
	/**
	 * 检查当前用户是否已经登录，且在有效期内
	 */
	public function authenticated() {
		$modelWay = $this->model('site\fe\way');
		return $modelWay->isLogined($this->siteId, $this->who);
	}
	/**
	 * 进行用户认证的URL
	 */
	public function authenticateURL() {
		$url = '/site/fe/user/login';
		return $url;
	}
	/**
	 * 执行OAuth操作
	 *
	 * 会在cookie保留结果5分钟
	 *
	 * $site
	 * $controller OAuth的回调地址
	 * $state OAuth回调时携带的参数
	 */
	protected function snsOAuth(&$snsConfig, $snsName) {
		$ruri = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		switch ($snsName) {
		case 'qy':
			$mpproxy = $this->model('sns\qy', $snsConfig);
			$oauthUrl = $mpproxy->oauthUrl($ruri, 'snsOAuth-' . $snsName);
			break;
		case 'wx':
			if ($snsConfig->can_oauth === 'Y') {
				$mpproxy = $this->model('sns\wx', $snsConfig);
				$oauthUrl = $mpproxy->oauthUrl($ruri, 'snsOAuth-' . $snsName, 'snsapi_userinfo');
			}
			break;
		case 'yx':
			if ($snsConfig->can_oauth === 'Y') {
				$mpproxy = $this->model('sns\yx', $snsConfig);
				$oauthUrl = $mpproxy->oauthUrl($ruri, 'snsOAuth-' . $snsName);
			}
			break;
		}
		if (isset($oauthUrl)) {
			/* 通过cookie判断是否是后退进入 */
			$this->mySetcookie("_{$snsConfig->siteid}_oauthpending", 'Y');
			$this->redirect($oauthUrl);
		}

		return false;
	}
	/**
	 * 通过OAuth接口获得用户信息
	 *
	 * $site
	 * $code
	 */
	protected function snsOAuthUserByCode($site, $code, $snsName) {
		$snsConfig = $this->model('site\sns\\' . $snsName)->bySite($site);
		$mpproxy = $this->model('sns\\' . $snsName, $snsConfig);
		$rst = $mpproxy->getOAuthUser($code);
		$rst[0] === false && die('oauth2 failed:' . $rst[1]);
		/**
		 * 将openid保存在cookie，可用于进行用户身份绑定
		 * openid不一定是关注用户
		 */
		$user = $rst[1];

		return $user;
	}
	/**
	 * 客户端应用名称
	 */
	protected function &userAgent() {
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		if (preg_match('/yixin/i', $user_agent)) {
			$ca = 'yx';
		} elseif (preg_match('/MicroMessenger/i', $user_agent)) {
			$ca = 'wx';
		} else {
			$ca = false;
		}

		return $ca;
	}
	/**
	 *
	 */
	protected function outputError($err, $title = '程序错误') {
		\TPL::assign('title', $title);
		\TPL::assign('body', $err);
		\TPL::output('error');
		exit;
	}
	/**
	 *
	 * 要求关注
	 *
	 * @param string $runningMpid
	 * @param string $openid
	 *
	 */
	protected function askFollow($runningMpid, $openid = false) {
		$isfollow = false;
		if ($openid !== false) {
			$isfollow = $this->model('user/fans')->isFollow($runningMpid, $openid);
		}
		if (!$isfollow) {
			/*$modelMpa = $this->model('mp\mpaccount');
				$fea = $modelMpa->getFeature($runningMpid);
				if ($fea->follow_page_id === '0') {
					$mpa = $this->model('mp\mpaccount')->byId($runningMpid);
					$html = '请关注公众号：' . $mpa->name;
				} else {
					$page = $this->model('code/page')->byId($fea->follow_page_id);
					$html = $page->html;
					$css = $page->css;
					$js = $page->js;
			*/
			$protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';
			header($protocol . ' 401 Unauthorized');
			header('Cache-Control:no-cache,must-revalidate,no-store');
			header('Pragma:no-cache');
			header("Expires:-1");
			\TPL::assign('follow_ele', empty($html) ? '请关注公众号' : $html);
			\TPL::assign('follow_css', empty($css) ? '' : $css);
			\TPL::output('follow');
			exit;
		}

		return true;
	}
	/**
	 * 返回全局的邀请关注页面
	 */
	public function askFollow_action($site) {
		$this->askFollow($site);
	}
	/**
	 * 微信jssdk包
	 *
	 * $site
	 * $url
	 */
	public function wxjssdksignpackage_action($site, $url) {
		if ($sns = $this->model('site\sns\wx')->bySite($site)) {
			$mpproxy = $this->model('sns\wx', $sns);
		} else if ($sns = $this->model('site\sns\qy')->bySite($site)) {
			$mpproxy = $this->model('sns\qy', $sns);
		}

		if (isset($mpproxy)) {
			$rst = $mpproxy->getJssdkSignPackage(urldecode($url));
			header('Content-Type: text/javascript');
			if ($rst[0] === false) {
				die("alert('{$rst[1]}');");
			}
			die($rst[1]);
		} else {
			die("alert('site is not joined.')");
		}
	}
	/**
	 * 清除用户登录信息
	 */
	public function cleanCookieLogin_action($site) {
		$this->model('site\fe\way')->cleanCookieLogin($site);
		return new \ResponseData('ok');
	}
	/**
	 * 清除用户登录信息
	 */
	public function cleanCookieUser_action($site) {
		$this->model('site\fe\way')->cleanCookieUser($site);
		return new \ResponseData('ok');
	}
}