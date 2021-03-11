$(function(){
	cateID =0;
	$('select[name=cate-one]').change(function(){
		var obj=$(this);
		if(obj.index()<3){
		var pid =obj.val();
			//异步发送
			
				//异步发送
			$.post(APP +　"/Ask/get_cate", {pid : pid} , function(data){
				if(data){
					var option = '';

					$.each(data, function(i, k){
						option += '<option value="' + k.cid + '">' + k.title + '</option>';
							
					})
					obj.next().html(option).show();
				}
			}, 'json');



		}

		cateID=obj.val();
	
	})
			//点击确定触发 的情况
			$('#ok').click(function(){

				if(!cateID){
					alert('请选择一个分类');
					return;
				}

				//隐藏的input  加cid
				$('input[name=cid]').val(cateID);
				//点击确定后就可以关闭按钮
				$('.close-window').click();

			});

			//判断内容是否完整
			$('.send-btn').click(function(){

			var cons = $('textarea[name=content]');
			//如果内容为空
			if(cons.val() == ''){
				alert('请输入提问内容！');
				cons.focus();
				return false;
			}

			//如果没有ID说明没有选择分类
			if(!cateID) {
				alert('请选择一个分类');
				return false;
			}

			//判断用户是否登录
			if(!on){
				$('.login').click();
				return false;
			}


		})

		//设置金币可以选择的范围
		var opt = $('select[name=reward] option');
		for (var i = 0; i < opt.length; i++){

			if(opt.eq(i).val() > point ){
				opt.eq(i).attr('disabled', 'disabled');
			}

		}

})