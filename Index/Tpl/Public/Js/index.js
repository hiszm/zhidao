$(function () {
	
	//左侧问题分类选项卡
	$( '.list-l1' ).hover( function () {
		$( this ).children( 'div:eq(0)' ).addClass( 'list-cur' ).next().show();
	}, function () {
		$( this ).children( 'div:eq(0)' ).removeClass( 'list-cur' ).next().hide();
	} );

	//中部轮换版
	$( '.imgs-wrap ul li:not(:first)' ).css( {opacity : 0, filter : 'Alpha(Opacity = 0)'} );
	$( '.ani-btn li' ).mouseover( function () {
		$( this ).addClass( 'ani-btn-cur' ).siblings().removeClass( 'ani-btn-cur' );

		var index = $( this ).index();
		var obj = $( '.imgs-wrap' );
		var ulObj = obj.find( 'ul' );
		var liObj = obj.find( 'li' );

		ulObj.stop().animate( {
			left : -558 * index + 'px'
		}, 500).find('li').stop().animate( {
			opacity : 0,
    		filter : 'Alpha(Opacity = 0)'
		}, 100);

		liObj.stop().eq( index ).animate( {
			opacity : 1,
    		filter : 'Alpha(Opacity = 100)'
		}, 600).siblings().animate( {
			opacity : 0,
    		filter : 'Alpha(Opacity = 0)'
		}, 600);
	} );
	
});