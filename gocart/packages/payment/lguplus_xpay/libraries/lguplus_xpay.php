<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Lguplus_xpay
{
	var $CI;

	//this can be used in several places
	var	$method_name;
    var $config_path;
    var $xpay = null;

	function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->library('session');
		$this->CI->lang->load('lguplus_xpay');

		$this->method_name	= lang('package_name');
        $this->config_path = getcwd().'/'.APPPATH.'packages/payment/lguplus_xpay/libraries/lgdacom';
	}

	/*
	checkout_form()
	this function returns an array, the first part being the name of the payment type
	that will show up beside the radio button the next value will be the actual form if there is no form, then it should equal false
	there is also the posibility that this payment method is not approved for this purchase. in that case, it should return a blank array
	*/

	//these are the front end form and check functions
	function checkout_form($post = false)
	{
		$settings	= $this->CI->Settings_model->get_settings('lguplus_xpay');
		$enabled	= $settings['enabled'];

        if($enabled)
		{
            $customer = $this->CI->go_cart->customer();
            $cart_contents = $this->CI->go_cart->contents();

            /*
             * [결제 인증요청 페이지(STEP2-1)]
             *
             * 샘플페이지에서는 기본 파라미터만 예시되어 있으며, 별도로 필요하신 파라미터는 연동메뉴얼을 참고하시어 추가 하시기 바랍니다.
             */

            /*
             * 1. 기본결제 인증요청 정보 변경
             *
             * 기본정보를 변경하여 주시기 바랍니다.(파라미터 전달시 POST를 사용하세요)
             */

            $pdata['cst_platform']              = $settings['cst_platform'];      //LG유플러스 결제 서비스 선택(test:테스트, service:서비스)
            $pdata['cst_mid']                   = $settings['cst_mid'];           //상점아이디(LG유플러스으로 부터 발급받으신 상점아이디를 입력하세요)
                                                                                //테스트 아이디는 't'를 반드시 제외하고 입력하세요.
            $pdata['lgd_mid']                   = (("test" == $pdata['cst_platform'])?"t":"").$pdata['cst_mid'];  //상점아이디(자동생성)
            $pdata['lgd_oid']                   = "test1234";           //주문번호(상점정의 유니크한 주문번호를 입력하세요)
            $pdata['lgd_amount']                = $this->CI->go_cart->total();    //결제금액("," 를 제외한 결제금액을 입력하세요)
            $pdata['lgd_buyer']                 = $customer['lastname']." ".$customer['firstname'];         //구매자명
            $pdata['lgd_productinfo']           = sprintf (lang('product_name'), array_values($cart_contents)[0]['name'], sizeof($cart_contents));;   //상품명
            $pdata['lgd_buyermail']             = $customer['email'];    //구매자 이메일
            $pdata['lgd_timestamp']             = date('YmdHms');                         //타임스탬프
            $pdata['lgd_custom_skin']           = "red";                                //상점정의 결제창 스킨 (red, purple, yellow)
            $pdata['lgd_window_ver']            = "2.5";                                //결제창 버젼정보
            $pdata['lgd_mertkey']               = "";									//상점MertKey(mertkey는 상점관리자 -> 계약정보 -> 상점정보관리에서 확인하실수 있습니다)
            $pdata['lgd_buyerid']               = "";
            $pdata['lgd_buyerip']               = "";
            $pdata['lgd_oid']                   = date('YmdHms');                      //주문번호(상점정의 유니크한 주문번호를 입력하세요)


            /*
             * 가상계좌(무통장) 결제 연동을 하시는 경우 아래 LGD_CASNOTEURL 을 설정하여 주시기 바랍니다.
             */
            //$pdata['lgd_casnoteurl']    		= "http://상점URL/cas_noteurl.php";
            $pdata['lgd_casnoteurl']    		= base_url('cas_noteurl.php');

            /*
             *************************************************
             * 2. MD5 해쉬암호화 (수정하지 마세요) - BEGIN
             *
             * MD5 해쉬암호화는 거래 위변조를 막기위한 방법입니다.
             *************************************************
             *
             * 해쉬 암호화 적용( LGD_MID + LGD_OID + LGD_AMOUNT + LGD_TIMESTAMP + LGD_MERTKEY )
             * LGD_MID          : 상점아이디
             * LGD_OID          : 주문번호
             * LGD_AMOUNT       : 금액
             * LGD_TIMESTAMP    : 타임스탬프
             * LGD_MERTKEY      : 상점MertKey (mertkey는 상점관리자 -> 계약정보 -> 상점정보관리에서 확인하실수 있습니다)
             *
             * MD5 해쉬데이터 암호화 검증을 위해
             * LG유플러스에서 발급한 상점키(MertKey)를 환경설정 파일(lgdacom/conf/mall.conf)에 반드시 입력하여 주시기 바랍니다.
             */

            require_once("lgdacom/XPayClient.php");
            $this->xpay = new XPayClient($this->config_path, $pdata['cst_platform']);
            $this->xpay->Init_TX($pdata['lgd_mid']);
            $pdata['lgd_hashdata'] = md5($pdata['lgd_mid'].$pdata['lgd_oid'].$pdata['lgd_amount'].$pdata['lgd_timestamp'].$this->xpay->config[$pdata['lgd_mid']]);
            $pdata['lgd_custom_processtype'] = "TWOTR";

            /*
             *************************************************
             * 2. MD5 해쉬암호화 (수정하지 마세요) - END
             *************************************************
             */

            $form			= array();
            $form['name'] = $this->method_name;
            $form['form'] = $this->CI->load->view('xpay_checkout', $pdata, true);

            return $form;
        } else return array();
	}


	function checkout_check()
	{
        $error_msg = lang('fix_errors')."<br/><ul>";
        $error_list = "";

        //Verify security code
        if( empty($_POST["CST_PLATFORM"]))
        {
            $error_list .= "<li>".lang('enter_platform')."</li>";
        }
        if( empty($_POST["CST_MID"]))
        {
            $error_list .= "<li>".lang('enter_mid')."</li>";
        }
        if( empty($_POST["LGD_PAYKEY"]))
        {
            $error_list .= "<li>".lang('enter_paykey')."</li>";
        }

        // We need to store the credit card information temporarily
        $xpay_tmp_data["xpay_data"] = $_POST;
        $this->CI->session->set_userdata($xpay_tmp_data);

        if( $error_list )
            return $error_msg . $error_list . "</ul>";
        else
        {
            return false;
        }
	}

	function description()
	{
		return lang('package_name');
	}

	//back end installation functions
	function install()
	{
        $config['enabled'] = '0';
		$config['cst_platform'] = 'test';
		$config['cst_mid'] = '';
        $config['mert_key'] = '';
		$this->CI->Settings_model->save_settings('lguplus_xpay', $config);
	}

	function uninstall()
	{
		$this->CI->Settings_model->delete_settings('lguplus_xpay');
	}

	//payment processor
	function process_payment()
	{
        $err_msg = "";
        $post_data = $this->CI->session->userdata('xpay_data');
        $this->CI->session->unset_userdata('xpay_data');

		/*
         *************************************************
         * 1.최종결제 요청 - BEGIN
         *  (단, 최종 금액체크를 원하시는 경우 금액체크 부분 주석을 제거 하시면 됩니다.)
         *************************************************
         */
        $CST_PLATFORM               = $post_data["CST_PLATFORM"];
        $CST_MID                    = $post_data["CST_MID"];
        $LGD_MID                    = (("test" == $CST_PLATFORM)?"t":"").$CST_MID;
        $LGD_PAYKEY                 = $post_data["LGD_PAYKEY"];

        //require_once("lgdacom/XPayClient.php");
        //$this->xpay = new XPayClient($this->config_path, $CST_PLATFORM);
        $this->xpay->Init_TX($LGD_MID);

        $this->xpay->Set("LGD_TXNAME", "PaymentByKey");
        $this->xpay->Set("LGD_PAYKEY", $LGD_PAYKEY);

        //금액을 체크하시기 원하는 경우 아래 주석을 풀어서 이용하십시요.
        //$DB_AMOUNT = "DB나 세션에서 가져온 금액"; //반드시 위변조가 불가능한 곳(DB나 세션)에서 금액을 가져오십시요.
        //$xpay->Set("LGD_AMOUNTCHECKYN", "Y");
        //$xpay->Set("LGD_AMOUNT", $DB_AMOUNT);

        /*
         *************************************************
         * 1.최종결제 요청(수정하지 마세요) - END
         *************************************************
         */

        /*
         * 2. 최종결제 요청 결과처리
         *
         * 최종 결제요청 결과 리턴 파라미터는 연동메뉴얼을 참고하시기 바랍니다.
         */
        if ($this->xpay->TX()) {
            //1)결제결과 화면처리(성공,실패 결과 처리를 하시기 바랍니다.)

            /*
            echo "결제요청이 완료되었습니다.  <br>";
            echo "TX Response_code = " . $xpay->Response_Code() . "<br>";
            echo "TX Response_msg = " . $xpay->Response_Msg() . "<p>";

            echo "거래번호 : " . $xpay->Response("LGD_TID",0) . "<br>";
            echo "상점아이디 : " . $xpay->Response("LGD_MID",0) . "<br>";
            echo "상점주문번호 : " . $xpay->Response("LGD_OID",0) . "<br>";
            echo "결제금액 : " . $xpay->Response("LGD_AMOUNT",0) . "<br>";
            echo "결과코드 : " . $xpay->Response("LGD_RESPCODE",0) . "<br>";
            echo "결과메세지 : " . $xpay->Response("LGD_RESPMSG",0) . "<p>";

            $keys = $xpay->Response_Names();
            foreach($keys as $name) {
                echo $name . " = " . $xpay->Response($name, 0) . "<br>";
            }

            echo "<p>";
            */

            if( "0000" == $this->xpay->Response_Code() ) {
                //최종결제요청 결과 성공 DB처리
                //echo "최종결제요청 결과 성공 DB처리하시기 바랍니다.<br>";

                //최종결제요청 결과 성공 DB처리 실패시 Rollback 처리
                /*
                $isDBOK = true; //DB처리 실패시 false로 변경해 주세요.
                if( !$isDBOK ) {
                    $err_msg += "데이터베이스 처리 실패!<br/>";
                    $xpay->Rollback("상점 DB처리 실패로 인하여 Rollback 처리 [TID:" . $xpay->Response("LGD_TID",0) . ",MID:" . $xpay->Response("LGD_MID",0) . ",OID:" . $xpay->Response("LGD_OID",0) . "]");

                    if( "0000" == $xpay->Response_Code() ) {
                        $err_msg += "자동취소가 정상적으로 완료 되었습니다.<br>";
                    }else{
                        $err_msg += "자동취소가 정상적으로 처리되지 않았습니다.<br>";
                    }
                } else {
                */
                    return false; // transaction success
                //}
            }else{
                //최종결제요청 결과 실패 DB처리
                $err_msg .= "결제요청이 실패하였습니다.<br>";
                $err_msg .= "TX Response_code = " . $this->xpay->Response_Code() . "<br>";
                $err_msg .= "TX Response_msg = " . $this->xpay->Response_Msg() . "<p>";
            }
        } else {
            //2)API 요청실패 화면처리
            $err_msg .= "결제요청이 실패하였습니다.  <br>";
            $err_msg .= "TX Response_code = " . $this->xpay->Response_Code() . "<br>";
            $err_msg .= "TX Response_msg = " . $this->xpay->Response_Msg() . "<p>";
        }

		// If we get to this step at all, something went wrong
		return $err_msg;

	}

	//admin end form and check functions
	function form($post	= false)
	{
		//this same function processes the form
		if(!$post)
		{
			$settings	= $this->CI->Settings_model->get_settings('lguplus_xpay');
		}
		else
		{
			$settings = $post;
		}
		//retrieve form contents
		return $this->CI->load->view('xpay_form', array('settings'=>$settings), true);
	}

	function check()
	{
		$error	= false;

		//count the errors
		if($error)
		{
			return $error;
		}
		else
		{
            $settings = $this->CI->Settings_model->get_settings('lguplus_xpay');

            $path_to_file = $this->config_path.'/conf/mall.conf';
            $file_contents = file_get_contents($path_to_file);

            // Find mertkey and mid
            $pattern = '/('.$settings['cst_mid'].')(\s*\=\s*)(\S+)/';
            $matches = array();
            preg_match($pattern, $file_contents, $matches);

            // Replace mertkey and mid
            $new_contents = str_replace($matches[3], $_POST['mert_key'], $file_contents);
            $new_contents = str_replace($settings['cst_mid'], $_POST['cst_mid'], $new_contents);

            file_put_contents($path_to_file, $new_contents);


			//we save the settings if it gets here
			$this->CI->Settings_model->save_settings('lguplus_xpay', $_POST);


			return false;
		}
	}
}

