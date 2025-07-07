<style>
	.table-condensed>tbody>tr>td,
	.table-condensed>tbody>tr>th,
	.table-condensed>tfoot>tr>td,
	.table-condensed>tfoot>tr>th,
	.table-condensed>thead>tr>td,
	.table-condensed>thead>tr>th{ font-size:13px; }

	.no-records-found{ color:red; }
    #searchWhere,.where-group{
        display: inline-block;
        line-height: 10px;
        margin: 0px .7rem 0px 0
    }
    .where-group select{
        width: 150px;
        height: 34px;
        display: block;
        height: 34px;
        padding: 6px 10px;
        font-size: 12px;
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
    .showArchived{
    }
    #archiveDialog {
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
    #archiveDialog > div {
        position: absolute;
        width: 320px;
        height: 100px;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        margin: auto;
        background: white;
        padding: 10px;
        border-radius: 2px;
    }
    #archiveDialog .archiveActions {
        position: absolute;
        bottom: 10px;
        right: 0px;
		width:100%;
		padding: 0 10px;
    }
    #archiveDialog .archiveActions .btn {
        margin: 0.5rem;
		width:60px;
    }
	
	
    #deleteDialog {
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
    #deleteDialog > div {
        position: absolute;
        width: 320px;
        height: 100px;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        margin: auto;
        background: white;
        padding: 10px;
        border-radius: 2px;
    }
    #deleteDialog .deleteActions {
        position: absolute;
        bottom: 10px;
        right: 0px;
		width:100%;
		padding: 0 10px;
		text-align:right;
    }
    #deleteDialog .deleteActions .btn {
        margin: 0.5rem;
		width:80px;
    }	
    #deleteDialog .deleteActions .btn-danger { float:left; }
    .where-group label{
        display: none;
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

#editExtendedEntity, #addExtendedEntity {
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

#editExtendedEntity.show, #addExtendedEntity.show {
	display: block;
}

#editExtendedEntity > form, #addExtendedEntity > form {
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

#editExtendedEntity > form input, #addExtendedEntity > form input {
	width: 250px;
}

.react-bs-table-bordered, .react-bs-container-body {
	height: auto !important;
}

.row-actions {
	text-align: center;
}

.row-actions > a {
	padding-right: 10px;
}
#editEavItem {
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
#editEavItem > form {
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
	
#kamalog table th{ font-size:13px; }	
#kamalog table th:nth-child(6),
#kamalog table th:nth-child(8)
	{ font-size:9px; }	
#kamalog table td{ font-size:12px; }	

#kamaLogModal td{ padding:5px; }

div.chat_v.bot{ 
	height: auto;display: flex;justify-content: flex-start;
	align-items: center;padding: 0.25rem 0.25rem 0 0.25rem;
}
div.chat_v.bot>.chat_b{
	position: relative;overflow: hidden;max-width: 80%;
	background: #efefef;width: auto; height: auto;min-height: 0.6rem;
	font-size: 12px;border-radius: 0.15rem;padding: 0.5rem;
}
div.chat_v.bot>.chat_b>p{ margin: 0.15rem 0.2rem;line-height: 2rem; word-wrap: break-word; }
div.chat_v.bot>.chat_b>div.rd{ padding: 0.2rem;margin-top: -0.2rem; }
div.chat_v.bot>.chat_b>div.rd>span.myYes{ 
	box-sizing: border-box;border: 1px solid rgb(140, 198, 63);
	border-radius: 0.25rem;color: rgb(140, 198, 63);margin-right: 0.2rem;
	padding: 0.1rem 0.25rem 0.1rem 0.25rem;line-height: 2rem;font-size: 12px;
	box-shadow: 0 0 1px 1px rgba(140, 198, 63, 0.2);float: left;margin-bottom: 0.2rem;
}
div.chat_v.bot>.chat_b>div.rd>span.myYes{ 
	box-sizing: border-box;border: 1px solid rgb(140, 198, 63);
	border-radius: 0.25rem;color: rgb(140, 198, 63);margin-right: 0.2rem;
	padding: 0.1rem 0.25rem 0.1rem 0.25rem;line-height: 2rem;font-size: 12px;
	box-shadow: 0 0 1px 1px rgba(140, 198, 63, 0.2);float: left;margin-bottom: 0.2rem;
}
.my_radiobutton{
	box-sizing: border-box;border: 1px solid rgb(140, 198, 63);border-radius: 0.5rem;color: rgb(140, 198, 63);margin-right: .3rem;
	padding: 0.5rem;line-height: 2rem;font-size: 12px;box-shadow: 0 0 1px 1px rgba(140, 198, 63, 0.2);margin-bottom: .5rem;
	display: inline-block;
}
div.sd_g>p{ display:block;width:5rem;max-width:5rem;margin-bottom:0;margin-top:0;font-size:12px; }

div.chat_v.bot>.chat_b>div.sd>.sd_sb{ 
	box-sizing: border-box;
	border: 1px solid rgb(140, 198, 63);
	border-radius: 0.25rem;
	color: rgb(140, 198, 63);
	margin-right: 0.2rem;
	height: 0.5rem;
	margin: 0 auto;
	margin-top: 0.2rem;
	width: 3rem;
	margin-bottom: 0.2rem;
	text-align: center;
	line-height: 2rem;
	font-size: 12px;
	box-shadow: 0 0 1px 1px rgba(140, 198, 63, 0.2);
}

div.chat_v.mine{
	height: auto;display: flex;justify-content: flex-end;align-items: center;
	padding: 0.25rem 0.25rem 0 0.25rem;
	margin-top: 1.2rem;
}
div.chat_v.mine>.chat_b{
	 -webkit-transform-origin: top right;width: auto;height: auto;
	 background: rgb(140, 198, 63);min-height: 0.6rem;font-size:12px;
	 border-radius: 0.5rem;padding: 0.5rem; max-width:50%;
}
div.chat_v.mine>.chat_b>p{ margin: 0.15rem 0.2rem;line-height: 2rem; word-wrap: break-word; }

	#start_time:hover, #end_time:hover{ cursor:pointer !important; }
	#start_time, #end_time{ background:#fff !important; border:1px solid #cfd0d2 !important; }

	table td:nth-child(1){ max-width:40px;}
	table th:nth-child(1)>input:hover,
	table td:nth-child(1)>input:hover
		{ cursor:pointer; }
	table td:nth-child(2){ max-width:200px; word-break:break-all;}
</style>
<div id="kamalog"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
<script type="text/javascript">
	var apiURL = "<?=env('API_URL');?>";
	var orgID  = "<?=$orgID;?>";
	var userID = "<?=session()->get('userID');?>";
$(function(){
	$(".pull-right.search input").attr('placeholder', 'Search on message')
	$("#deleteDialog .deleteActions .btn-danger").text("No");
});
</script>
<link  href="/public/layui/css/layui.css" rel="stylesheet">
<script  src="/public/layui/layui.js"></script>
<script  src="/public/js/jquery.js"></script>
<script src="/public/js/app.js"></script>
<script type="text/javascript">
$("#searc_org_id").ready(function(){
	if(orgID!=0){ $("#searc_org_id").hide(); }
});
</script>
<?php
