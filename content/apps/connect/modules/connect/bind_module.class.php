<?php
//
//    ______         ______           __         __         ______
//   /\  ___\       /\  ___\         /\_\       /\_\       /\  __ \
//   \/\  __\       \/\ \____        \/\_\      \/\_\      \/\ \_\ \
//    \/\_____\      \/\_____\     /\_\/\_\      \/\_\      \/\_\ \_\
//     \/_____/       \/_____/     \/__\/_/       \/_/       \/_/ /_/
//
//   上海商创网络科技有限公司
//
//  ---------------------------------------------------------------------------------
//
//   一、协议的许可和权利
//
//    1. 您可以在完全遵守本协议的基础上，将本软件应用于商业用途；
//    2. 您可以在协议规定的约束和限制范围内修改本产品源代码或界面风格以适应您的要求；
//    3. 您拥有使用本产品中的全部内容资料、商品信息及其他信息的所有权，并独立承担与其内容相关的
//       法律义务；
//    4. 获得商业授权之后，您可以将本软件应用于商业用途，自授权时刻起，在技术支持期限内拥有通过
//       指定的方式获得指定范围内的技术支持服务；
//
//   二、协议的约束和限制
//
//    1. 未获商业授权之前，禁止将本软件用于商业用途（包括但不限于企业法人经营的产品、经营性产品
//       以及以盈利为目的或实现盈利产品）；
//    2. 未获商业授权之前，禁止在本产品的整体或在任何部分基础上发展任何派生版本、修改版本或第三
//       方版本用于重新开发；
//    3. 如果您未能遵守本协议的条款，您的授权将被终止，所被许可的权利将被收回并承担相应法律责任；
//
//   三、有限担保和免责声明
//
//    1. 本软件及所附带的文件是作为不提供任何明确的或隐含的赔偿或担保的形式提供的；
//    2. 用户出于自愿而使用本软件，您必须了解使用本软件的风险，在尚未获得商业授权之前，我们不承
//       诺提供任何形式的技术支持、使用担保，也不承担任何因使用本软件而产生问题的相关责任；
//    3. 上海商创网络科技有限公司不对使用本产品构建的商城中的内容信息承担责任，但在不侵犯用户隐
//       私信息的前提下，保留以任何方式获取用户信息及商品信息的权利；
//
//   有关本产品最终用户授权协议、商业授权与技术服务的详细内容，均由上海商创网络科技有限公司独家
//   提供。上海商创网络科技有限公司拥有在不事先通知的情况下，修改授权协议的权力，修改后的协议对
//   改变之日起的新授权用户生效。电子文本形式的授权协议如同双方书面签署的协议一样，具有完全的和
//   等同的法律效力。您一旦开始修改、安装或使用本产品，即被视为完全理解并接受本协议的各项条款，
//   在享有上述条款授予的权力的同时，受到相关的约束和限制。协议许可范围以外的行为，将直接违反本
//   授权协议并构成侵权，我们有权随时终止授权，责令停止损害，并保留追究相关责任的权力。
//
//  ---------------------------------------------------------------------------------
//
defined('IN_ECJIA') or exit('No permission resources.');

class connect_bind_module extends api_front implements api_interface {
    public function handleRequest(\Royalcms\Component\HttpKernel\Request $request) {	
    	$this->authSession();	
		$open_id      = $this->requestData('openid');
		$connect_code = $this->requestData('code');
		$username	  = $this->requestData('username');
		$password	  = $this->requestData('password');
		$profile	  = $this->requestData('profile');
		$device		  = $this->device;
		$api_version = $this->request->header('api-version');
		
		if (version_compare($api_version, '1.14', '>=')) {
			//短信验证码验证，$username手机号，$password短信验证码
			//判断校验码是否过期
			if (!empty($username) && (!isset($_SESSION['bindcode_lifetime']) || $_SESSION['bindcode_lifetime'] + 180 < RC_Time::gmtime())) {
				//过期
				return new ecjia_error('code_timeout', __('验证码已过期，请重新获取！', 'connect'));
			}
			//判断校验码是否正确
			if (!empty($username) && (!isset($_SESSION['bindcode_lifetime']) || $password != $_SESSION['bind_code'] )) {
				return new ecjia_error('code_error', __('验证码错误，请重新填写！', 'connect'));
			}
		}
		
		if (empty($open_id) || empty($connect_code) || empty($username) || empty($password)) {
			return new ecjia_error('invalid_parameter', __('参数错误', 'connect'));
		}
		
		$connect_user = new \Ecjia\App\Connect\ConnectUser($connect_code, $open_id, 'user');
		if ($connect_user->checkUser()) {
			return new ecjia_error('connect_userbind', __('您已绑定过会员用户！', 'connect'));
		}
		/**
		 * $code
		 * login_weibo
		 * sns_qq
		 * sns_wechat
		 * login_mobile
		 * login_mail
		 * login_username
		 * login_alipay
		 * login_taobao
		 **/
		$ecjia_integrate = ecjia_integrate::init_users();
		
		$is_mobile = false;
		
		if (version_compare($api_version, '1.14', '<')) {
			/* 判断是否为手机号*/
		    $check_mobile = Ecjia\App\Sms\Helper::check_mobile($username);
		    if($check_mobile === true) {
				$db_user     = RC_Model::model('user/users_model');
				$user_count  = $db_user->where(array('mobile_phone' => $username))->count();
				if ($user_count > 1) {
					return new ecjia_error('user_repeat', __('用户重复，请与管理员联系！', 'connect'));
				}
				$check_user = $db_user->where(array('mobile_phone' => $username))->get_field('user_name');
				/* 获取用户名进行判断验证*/
				if (!empty($check_user)) {
					if ($ecjia_integrate->login($check_user, $password)) {
						$is_mobile = true;
					}
				}
			}
			
			/* 如果不是手机号码*/
			if (!$is_mobile) {
				if (! $ecjia_integrate->login($username, $password)) {
					return new ecjia_error('password_error', __('密码错误！', 'connect'));
				}
			}
		} else {
			/* 判断是否为手机号*/
		    $check_mobile = Ecjia\App\Sms\Helper::check_mobile($username);
		    if($check_mobile === true) {
				$db_user     = RC_Model::model('user/users_model');
				$user_count  = $db_user->where(array('mobile_phone' => $username))->count();
				if ($user_count > 1) {
					return new ecjia_error('user_repeat', __('用户重复，请与管理员联系！', 'connect'));
				}
				$check_user = $db_user->where(array('mobile_phone' => $username))->get_field('user_name');
				/* 获取用户名进行判断验证*/
				if (!empty($check_user)) {
					if ($ecjia_integrate->login($check_user)) {
						$is_mobile = true;
					}
					if ($is_mobile) {
						if (!empty($username)) {
							RC_DB::table('users')->where('user_id', $_SESSION['user_id'])->update(array('mobile_phone' => $username));
						}
					}
				}
			}
			
			/* 如果不是手机号码*/
			if (!$is_mobile) {
				if (!$ecjia_integrate->login($username, $password)) {
					return new ecjia_error('password_error', __('密码错误！', 'connect'));
				}
			}
		}
		
		/* 用户帐号是否已被关联使用过*/
		$connect_user_exit = RC_DB::table('connect_user')->where('connect_code', $connect_code)->where('user_type', 'user')->where('user_id', $_SESSION['user_id'])->first();
		if (!empty($connect_user_exit)) {
			return new ecjia_error('connect_userbind', __('您已绑定过会员用户！', 'connect'));
		}
		$curr_time = RC_Time::gmtime();
		$data = array(
			'connect_code'	=> $connect_code,
			'open_id'		=> $open_id,
			'create_at'     => $curr_time,
			'user_id'		=> $_SESSION['user_id'],
		);
		if (!empty($profile)) {
			$data['profile'] = serialize($profile);
		}
		RC_DB::table('connect_user')->insert($data);
		
		if (!empty($profile['avatar_img'])) {
			/* 获取远程用户头像信息*/
			RC_Api::api('connect', 'update_user_avatar', array('avatar_url' => $profile['avatar_img']));
		}
		
		//ecjia账号同步登录用户信息更新
		$connect_options = [
			'connect_code'  => 'app',
			'user_id'       => $_SESSION['user_id'],
			'is_admin'      => '0',
			'user_type'     => 'user',
			'open_id'       => md5(RC_Time::gmtime() . $_SESSION['user_id']),
			'access_token'  => RC_Session::session_id(),
			'refresh_token' => md5($_SESSION['user_id'] . 'user_refresh_token'),
		];
		$ecjiaAppUser = RC_Api::api('connect', 'ecjia_syncappuser_add', $connect_options);
		if (is_ecjia_error($ecjiaAppUser)) {
			return $ecjiaAppUser;
		}
		
		RC_Loader::load_app_func('admin_user', 'user');
		$user_info = EM_user_info($_SESSION['user_id']);
		update_user_info(); // 更新用户信息
		RC_Loader::load_app_func('cart','cart');
		recalculate_price(); // 重新计算购物车中的商品价格
		
		$out = array(
			'token' => RC_Session::session_id(),
			'user' => $user_info
		);
		
//		$this->feedback_batch_userid($_SESSION['user_id'], $_SESSION['user_name'], $device);
		
		//修正关联设备号
		$result = ecjia_app::validate_application('mobile');
		if (!is_ecjia_error($result)) {
			if (!empty($device['udid']) && !empty($device['client']) && !empty($device['code'])) {
				RC_DB::table('mobile_device')
						->where('device_udid', $device['udid'])
						->where('device_client', $device['client'])
						->where('device_code', $device['code'])
						->where('user_type', 'user')
						->update(array('user_id' => $_SESSION['user_id'], 'update_time' => RC_Time::gmtime()));
			}
		}
		return $out;
	}
	
	/**
	 * 修正咨询信息
	 * @param string $user_id
	 * @param string $device
	 */
	private function feedback_batch_userid($user_id, $user_name, $device) {
//		$device_udid	  = $device['udid'];
//		$device_client	  = $device['client'];
		
		//$pra = array(
		//	'object_type'	=> 'ecjia.feedback',
		//	'object_group'	=> 'feedback',
		//	'item_key2'		=> 'device_udid',
		//	'item_value2'	=> $device_udid 
		//);
		//$object_id = Ecjia\App\User\TermRelationship::GetObjectIds($pra);
		
		//更新未登录用户的咨询
		//$db_term_relation->where(array('item_key2' => 'device_udid', 'item_value2' => $device_udid))->update(array('item_key2' => '', 'item_value2' => ''));
//		RC_DB::table('term_relationship')->where('item_key2', 'device_udid')->where('item_value2', $device_udid)->update(array('item_key2' => '', 'item_value2' => ''));
		
		//if (!empty($object_id)) {
		//	$db = RC_Model::model('feedback/feedback_model');
		//	$db->where(array('msg_id' => $object_id, 'msg_area' => '4'))->update(array('user_id' => $user_id, 'user_name' => $user_name));
		//	$db->where(array('parent_id' => $object_id, 'msg_area' => '4'))->update(array('user_id' => $user_id, 'user_name' => $user_name));
		//}
	}
}

// end