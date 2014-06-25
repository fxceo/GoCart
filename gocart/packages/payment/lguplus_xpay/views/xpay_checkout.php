<table width="100%" border="0" cellpadding="5">
  <tr>
    <td><img  src="https://www.paypal.com/en_US/i/logo/PayPal_mark_180x113.gif" border="0" alt="Acceptance Mark"></td>
  </tr>
  <tr>
    <td><?php echo lang('paypal_desc') ?></td>
  </tr>
</table>


<script language = 'javascript'>
<!--
/*
 * 상점결제 인증요청후 PAYKEY를 받아서 최종결제 요청.
 */

function doPay_ActiveX(){
    ret = xpay_check(document.getElementById('form-lguplus_xpay'), '<?php echo $cst_platform ?>');

    if (ret=="00"){     //ActiveX 로딩 성공
        var LGD_RESPCODE        = dpop.getData('LGD_RESPCODE');       //결과코드
        var LGD_RESPMSG         = dpop.getData('LGD_RESPMSG');        //결과메세지

        if( "0000" == LGD_RESPCODE ) { //인증성공
            var LGD_PAYKEY      = dpop.getData('LGD_PAYKEY');         //LG유플러스 인증KEY
            //var msg = "인증결과 : " + LGD_RESPMSG + "\n";
            //msg += "LGD_PAYKEY : " + LGD_PAYKEY +"\n\n";
            //alert(msg);
            document.getElementById('LGD_PAYKEY').value = LGD_PAYKEY;
            document.getElementById('form-lguplus_xpay').submit();
        } else { //인증실패
            alert("인증이 실패하였습니다. " + LGD_RESPMSG);
            /*
             * 인증실패 화면 처리
             */
        }
    } else {
        alert("LG U+ 전자결제를 위한 ActiveX Control이  설치되지 않았습니다.");
        /*
         * 인증실패 화면 처리
         */
    }
}

$(document).ready(function() {
    $("input[type=submit]").click(function() {
        if(lgdacom_atx_flag == true) {
            doPay_ActiveX();
        } else {
            alert("결제를 위한 모듈을 다운 중이거나, 모듈을 설치하지 않았습니다.");
        }
    });
});

//-->
</script>


<div id="LGD_ACTIVEX_DIV"></div> <!-- ActiveX 설치 안내 Layer 입니다. 수정하지 마세요. -->
<table>
    <tr>
        <td>아이디 </td>
        <td><?php echo $lgd_mid ?></td>
    </tr>
    <tr>
        <td>구매자 이름 </td>
        <td><?php echo $lgd_buyer ?></td>
    </tr>
    <tr>
        <td>구매자 IP </td>
        <td><?php echo $lgd_buyerip ?></td>
    </tr>
    <tr>
        <td>구매자 ID </td>
        <td><?php echo $lgd_buyerid ?></td>
    </tr>
    <tr>
        <td>상품정보 </td>
        <td><?php echo $lgd_productinfo ?></td>
    </tr>
    <tr>
        <td>결제금액 </td>
        <td><?php echo number_format($lgd_amount) ?></td>
    </tr>
    <tr>
        <td>구매자 이메일 </td>
        <td><?php echo $lgd_buyermail ?></td>
    </tr>
    <tr>
        <td>주문번호 </td>
        <td><?php echo $lgd_oid ?></td>
    </tr>
    <tr>
        <td colspan="2">* 추가 상세 결제요청 파라미터는 메뉴얼을 참조하시기 바랍니다.</td>
    </tr>
    <tr>
        <td colspan="2"></td>
    </tr>
</table>
<br>

<br>
<input type="hidden" name="CST_PLATFORM"                id="CST_PLATFORM"		value="<?php echo $cst_platform ?>">                   <!-- 테스트, 서비스 구분 -->
<input type="hidden" name="CST_MID"                     id="CST_MID"			value="<?php echo $cst_mid ?>">                        <!-- 상점아이디 -->
<input type="hidden" name="LGD_MID"                     id="LGD_MID"			value="<?php echo $lgd_mid ?>">                        <!-- 상점아이디 -->
<input type="hidden" name="LGD_OID"                     id="LGD_OID"			value="<?php echo $lgd_oid ?>">                        <!-- 주문번호 -->
<input type="hidden" name="LGD_BUYER"                   id="LGD_BUYER"			value="<?php echo $lgd_buyer ?>">           			<!-- 구매자 -->
<input type="hidden" name="LGD_PRODUCTINFO"             id="LGD_PRODUCTINFO"	value="<?php echo $lgd_productinfo ?>">     			<!-- 상품정보 -->
<input type="hidden" name="LGD_AMOUNT"                  id="LGD_AMOUNT"			value="<?php echo $lgd_amount ?>">                     <!-- 결제금액 -->
<input type="hidden" name="LGD_BUYEREMAIL"              id="LGD_BUYEREMAIL"		value="<?php echo $lgd_buyermail ?>">                 <!-- 구매자 이메일 -->
<!--input type="hidden" name="LGD_CUSTOM_SKIN"             id="LGD_CUSTOM_SKIN"   	value="<?php echo $lgd_custom_skin ?>"-->                <!-- 결제창 SKIN -->
<!--input type="hidden" name="LGD_WINDOW_VER"         	    id="LGD_WINDOW_VER"	    value="<?php echo $lgd_window_ver ?>"--> 				<!-- 결제창버전정보 (삭제하지 마세요) -->
<input type="hidden" name="LGD_CUSTOM_PROCESSTYPE"      id="LGD_CUSTOM_PROCESSTYPE"		value="<?php echo $lgd_custom_processtype ?>">         <!-- 트랜잭션 처리방식 -->
<input type="hidden" name="LGD_TIMESTAMP"               id="LGD_TIMESTAMP"		value="<?php echo $lgd_timestamp ?>">                  <!-- 타임스탬프 -->
<input type="hidden" name="LGD_HASHDATA"                id="LGD_HASHDATA"		value="<?php echo $lgd_hashdata ?>">                   <!-- MD5 해쉬암호값 -->
<input type="hidden" name="LGD_PAYKEY"                  id="LGD_PAYKEY">                                <!-- LG유플러스 PAYKEY(인증후 자동셋팅)-->
<input type="hidden" name="LGD_VERSION"         		id="LGD_VERSION"		value="PHP_XPay_2.5">							<!-- 버전정보 (삭제하지 마세요) -->
<input type="hidden" name="LGD_BUYERIP"                 id="LGD_BUYERIP"		value="<?php echo $lgd_buyerip ?>">           			<!-- 구매자IP -->
<input type="hidden" name="LGD_BUYERID"                 id="LGD_BUYERID"		value="<?php echo $lgd_buyerid ?>">           			<!-- 구매자ID -->

<!-- 가상계좌(무통장) 결제연동을 하시는 경우  할당/입금 결과를 통보받기 위해 반드시 LGD_CASNOTEURL 정보를 LG 유플러스에 전송해야 합니다 . -->
<input type="hidden" name="LGD_CASNOTEURL"          	id="LGD_CASNOTEURL"		value="<?php echo $lgd_casnoteurl ?>">					<!-- 가상계좌 NOTEURL -->

<form method="post" name ="LGD_PAYINFO" id="LGD_PAYINFO" action="payres.php">
</form>

<!--  xpay.js는 반드시 body 밑에 두시기 바랍니다. -->
<!--  UTF-8 인코딩 사용 시는 xpay.js 대신 xpay_utf-8.js 을  호출하시기 바랍니다.-->
<script language="javascript" src="<?php echo $_SERVER['SERVER_PORT']!=443?"http":"https" ?>://xpay.uplus.co.kr<?php echo($cst_platform == "test")?($_SERVER['SERVER_PORT']!=443?":7080":":7443"):""?>/xpay/js/xpay_utf-8.js" type="text/javascript"></script>

