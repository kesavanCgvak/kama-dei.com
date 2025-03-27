<style>
	#feedbackDIV th{ font-size: 13px !important; }
	#feedbackDIV td{ font-size: 12px !important; }
	#feedbackDIV td:last-child{ font-size: 10px !important; }
	
	#feedbackDIV th:nth-child(0), #feedbackDIV td:nth-child(0){ width: 70px; }
	#feedbackDIV th:nth-child(1), #feedbackDIV td:nth-child(1),
	#feedbackDIV th:nth-child(2), #feedbackDIV td:nth-child(2){ width: 160px; }
	#feedbackDIV th:nth-child(3), #feedbackDIV td:nth-child(3){ text-align: center; width: 120px; }
	#feedbackDIV th:nth-child(4), #feedbackDIV td:nth-child(4){ width: 130px; }
	#feedbackDIV th:nth-child(5), #feedbackDIV td:nth-child(5){ text-align: center; width: 70px; vertical-align: middle; }
	#feedbackDIV th:nth-child(7), #feedbackDIV td:nth-child(7){ text-align: center; width: 70px; vertical-align: middle; }
	/*
	#feedbackDIV th:nth-child(6), #feedbackDIV td:nth-child(6){ width: 140px; }
	#feedbackDIV th:nth-child(7), #feedbackDIV td:nth-child(7){ width: 100px; }
	*/

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
	
</style>
<script type="text/javascript">
	var apiURL = "<?=env('API_URL');?>";
	var orgID  = "<?=$orgID;?>";
	var userId = "<?=\Session::get('userID');?>";
	var table;	
</script>

<!-- ---------------------------------------------------------------------------------------- -->
<!-- ---------------------------------------------------------------------------------------- -->
<div id="feedbackDIV"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
<!-- ---------------------------------------------------------------------------------------- -->
<!-- ---------------------------------------------------------------------------------------- -->
<script src="/public/js/app.js"></script>
<!-- ---------------------------------------------------------------------------------------- -->
<!-- ---------------------------------------------------------------------------------------- -->
<script type="application/javascript">
	$(function(){
		$("#insertBtn").hide();
	})
</script>
<!-- ---------------------------------------------------------------------------------------- -->
<!-- ---------------------------------------------------------------------------------------- -->
<div class="modal fade" id="kamaLogModal" tabindex="-1" role="dialog" aria-labelledby="kamaLogModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="kamaLogModalLabel">Kama Log</h4>
			</div>
			<div class="modal-body"></div>
			<div class="modal-footer" style="display: flex">
				<div style="width: 50%; text-align: left">
					<button type="button" class="btn btn-primary" id="sendEmaiSHOW" >Send to an email</button>
				</div>
				<div style="width: 50%; text-align: right">
					<button type="button" class="btn btn-danger"  data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- ---------------------------------------------------------------------------------------- -->
<!-- ---------------------------------------------------------------------------------------- -->
<div class="modal fade" id="kamaLogEmailModal" tabindex="-1" role="dialog" aria-labelledby="kamaLogEmailModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close closemode"  data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title" id="kamaLogEmailModalLabel">Send email</h4>
			</div>
			<div class="modal-body">
				<div class="input-group input-group-lg">
					<span class="input-group-addon">To</span>
					<input type="text" class="form-control" placeholder="Email Address(es)" id="emailTo">
				</div>
				<div class="input-group input-group-lg" style="margin-top:10px;">
					<span class="input-group-addon">Subject</span>
					<input type="text" class="form-control" placeholder="Subject" id="emailSubject">
				</div>
				<div class="input-group input-group-lg" style="margin-top:10px;">
					<span class="input-group-addon" style="padding:15px;">Introduction</span>
					<textarea
							  class="form-control"
							  placeholder="Introduction"
							  id="emailInstructions"
								style="max-height:100px;min-height:100px;min-width:99%;max-width:100%;"
					>Hi<?="\n";?>This is a log from Kama-DEI</textarea>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" id="closeSendEmail" style="float:left;width:80px;">Cancel</button>
				<button type="button" class="btn btn-primary" id="sendEmaiBTN" style="width:80px;" >Send</button>
			</div>
		</div>
	</div>
</div>
<!-- ---------------------------------------------------------------------------------------- -->
<!-- ---------------------------------------------------------------------------------------- -->
