<style>

    #searchWhere,.where-group{
        display: inline-block;
        line-height: 10px;
        margin: 0px 50px 0px 10px;
    }
    .where-group select{
        width: 176px;
        height: 34px;

        display: block;
        height: 34px;
        padding: 6px 14px;
        font-size: 14px;
        line-height: 1.42857143;
        color: #000;
        background-color: #fff;
        background-image: none;
        border: 1px solid #cfd0d2;
        border-radius: 4px;
        -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
        box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
        -webkit-transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
        -o-transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
        transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
    }

    #searchWhere{
        float: right;
        margin-right: 186px;
    }
    .where-group label{
        display: none;
    }
    .pull-left{
        width: 100%;
    }
    .pull-right{
        top: -55px;
    }
    .fixed-table-container{
        top: -55px;
    }

#showError{
    position: fixed;
    top: 10px;
    left: 10px;
    color: #fff;
    background: #d25c5c;
    z-index: 9999;
    min-width: 150px;
    width: auto;
    padding: 8px 15px;
    border: 1px dotted #fff;
    border-radius: 8px;
    box-shadow: 0 0 0 3px #d25c5c;
	display:none;
}

#showError>i{ margin-right:5px; }
#showError>i:hover{ cursor:pointer;color:yellow; }

#editExtendedEAV, #addExtendedEAV {
	display: none;
	position: fixed;
	z-index: 1000;
	background: rgba(0, 0, 0, 0.6);
	top: 0;
	bottom: 0;
	right: 0;
	left: 0;
	margin: auto;
}

#editExtendedEAV.show, #addExtendedEAV.show {
	display: block;
}

#editExtendedEAV > form, #addExtendedEAV > form {
	position: absolute;
	margin: auto;
	top: 0;
	bottom: 0;
	right: 0;
	left: 0;
	width: 280px;
	height: 370px;
	width: fit-content;
	height: fit-content;
	background: white;
	padding: 15px;
}

#editExtendedEAV > form input, #addExtendedEAV > form input {
	width: 250px;
}

.react-bs-table-bordered, .react-bs-container-body {
	height: auto !important;
}

.row-actions {
	text-align: center;
}

.row-actions > a:first-child {
	padding-right: 10px;
}



</style>
<div id="extendedeav"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
<script type="text/javascript">
	var apiURL = "<?=env('API_URL');?>";
	var orgID  = "<?=$orgID;?>";
	var userID = "<?=session()->get('userID');?>";
</script>
<script src="/public/js/app.js"></script>
<link  href="/public/layui/css/layui.css" rel="stylesheet">
<!--采用模块化方式-->
<script  src="/public/layui/layui.js"></script>
<script  src="/public/js/jquery.js"></script>
<!-- 关于layUI的配置 可使用“dialog.fun()”调用 -->
<script>

  /*自定义弹出框格式 (常用的弹出框形式)*/
  /*layui.use('layer', function(){
    var layer = layui.layer;
    var dialog = {
      error: function(message,url) {
        layer.open({
          content:message,
          title:false,
          icon:3,
          yes : function(){
            location.href=url;
          },
        });
      },
      /!*用于弹出框提示*!/
      tip:function (message,timer) {
        if(!timer){timer = 2;}
        layer.msg(message,{time:timer * 1000});
      },
      /!*显示加载的效果 type:样式 [null,1,2] *!/
      loading:function (type) {
        layer.load(type);
        //此处演示关闭
        setTimeout(function(){
          layer.closeAll('loading');
        }, 2000);
      },
    };
  });*/

  <!-- end 自定义弹出框格式 -->

  <!-- 全局配置 -->
  layui.config({
    version: false //一般用于更新模块缓存，默认不开启。设为true即让浏览器不缓存。也可以设为一个固定的值，如：201610
    ,debug: false //用于开启调试模式，默认false，如果设为true，则JS模块的节点会保留在页面
    ,base: '' //设定扩展的 Layui 模块的所在目录，一般用于外部模块扩展
  });
  <!-- end 自定义弹出框格式 -->



</script>
<script >

</script>
