$(function () {

	//头部定位边线
	$( window ).scroll( function () {
		if ( $( this ).scrollTop() > 0 ) {
			$( '#top-fixed' ).addClass( 'fixed' );
		} else {
			$( '#top-fixed' ).removeClass( 'fixed' );
		}
	} );

	//搜索按钮
	$( '.sech-btn' ).hover( function () {
		$( this ).addClass( 'sech-btn-cur' );
	}, function () {
		$( this ).removeClass('sech-btn-cur');
	} );

	$('.ask-btn').hover( function () {
		$( this ).addClass( 'ask-btn-cur' );
	}, function (){
		$( this ).removeClass( 'ask-btn-cur' );
	} );


	//注册出弹框
	$( '.register' ).click( function () {
		var obj = $( '#register' );
		dialog( obj );

		obj.find( 'input[type=submit]' ).hover( function () {
			$( this ).addClass( 'reg-btn-cur' );
		}, function () {
			$( this ).removeClass( 'reg-btn-cur' );
		} );

		return false;
	} );

	$( '#login-now' ).click( function () {
		$( '#register' ).fadeOut();
		dialog( $( '#login' ) );

		return false;
	} );


	//登录弹出框
	$( '.login' ).click( function () {
		var obj = $( '#login' );
		dialog( obj );

		obj.find( 'input[type=submit]' ).hover( function () {
			$( this ).addClass( 'login-btn-cur' );
		}, function () {
			$( this ).removeClass( 'login-btn-cur' );
		} );

		return false;
	} );

	$( '#regis-now' ).click( function () {
		$( '#login' ).fadeOut();
		dialog( $( '#register' ) );

		return false;
	} );


	//关闭弹出框
	$( '.close-window' ).click( function () {
		$( this ).parent().parent().fadeOut();
		$( '#background' ).hide();

		return false;
	} );


	//问题分类下拉
	$( '.ask-cate' ).hover( function () {
		$( this ).find('ul').show();
	}, function () {
		$( this ).find('ul').hide();
	} );

	//点击分类弹出框
	$( '#sel-cate' ).click( function () {
		dialog($( '#category' ));
	} );

	$( 'textarea[name=content]' ).keyup( function () {
		var content = $( this ).val();
		//调用check函数取得当前字数
		var lengths = check(content);
		//最大允许输入50字个
		if (lengths[0] >= 50) {
			$( this ).val(content.substring(0, Math.ceil(lengths[1])));
		}
		var num = 50 - Math.ceil(lengths[0]);
		var msg = num < 0 ? 0 : num;
		//当前字数同步到显示提示
		$( '#num' ).html( msg );
	} );


});


/**********函数**********/

/**
 * 弹出框
 */
function dialog (obj) {
	obj.css( {
		left : ( $( window ).width() - obj.width() ) / 2,
		top : $( document ).scrollTop() + ( $( window ).height() - obj.height() ) / 2
	} ).fadeIn();

	$( '#background' ).css( {
		opacity : 0.3,
    	filter : 'Alpha(Opacity = 30)',
		'height' : $( document ).height()
	} ).show();
}
/**
 * 统计字数
 * @param  字符串
 * @return 数组[当前字数, 最大字数]
 */
function check (str) {
	var num = [0, 50];
	for (var i=0; i<str.length; i++) {
		//字符串不是中文时
		if (str.charCodeAt(i) >= 0 && str.charCodeAt(i) <= 255){
			num[0] = num[0] + 0.5;//当前字数增加0.5个
			num[1] = num[1] + 0.5;//最大输入字数增加0.5个
		} else {//字符串是中文时
			num[0]++;//当前字数增加1个
		}
	}
	return num;
}