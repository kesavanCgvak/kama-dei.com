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

#editExtendedType, #addExtendedType {
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

#editExtendedType.show, #addExtendedType.show {
	display: block;
}

#editExtendedType > form, #addExtendedType > form {
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

#editExtendedType > form input, #addExtendedType > form input {
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

#extendedtype table th{ font-size:13px; }
#extendedtype table th>div:nth-child(1){ padding-right:10px; }

#extendedtype table td:nth-child(1){ font-size:12px;vertical-align:middle;width:50px;text-align:right; }
#extendedtype table td:nth-child(2){ font-size:13px;vertical-align:middle;text-align:left; }
#extendedtype table td:nth-child(3){ font-size:12px;vertical-align:middle;width:105px;text-align:center; }
#extendedtype table td:nth-child(4){ font-size:12px;vertical-align:middle;width:150px;text-align:left; }
#extendedtype table td:nth-child(5){ font-size:12px;vertical-align:middle;width:150px;text-align:left; }
#extendedtype table td:nth-child(6){ font-size:12px;vertical-align:middle;width:90px;text-align:center; }
#extendedtype table td:nth-child(7){ font-size:12px;vertical-align:middle;width:90px;text-align:center; }
#extendedtype table td:nth-child(8){ font-size:12px;vertical-align:middle;width:90px;text-align:center; }
#extendedtype table td:nth-child(9){ font-size:12px;vertical-align:middle;width:40px;text-align:center; }
</style>
<div id="extendedtype"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
<script type="text/javascript">
	var apiURL = "<?=env('API_URL');?>";
	var orgID  = "<?=$orgID;?>";
	var userID = "<?=session()->get('userID');?>";
</script>
<script src="/public/js/app.js"></script>
