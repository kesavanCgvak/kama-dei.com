<script>
  $.ajax({
    url: '/gettoken',
    headers: {
      //'apikey': apikey
    },

    /*data: data,*/
    type: 'GET',
    async: true,
    error: (e) => {
      console.dir(e);
    },
    success: (data) => {
      console.dir(data)
      if(data&&data.result){
        //window.location.href='http://localhost:5001/set1?'+data.msg;
        /*window.location.href='http://167.99.79.150:5001/set1?'+data.msg;*/
       /* window.open('http://52.14.155.255:6001/set1?'+data.msg)
       * https://kama-dei.com:6001
       * window.open('https://staging.kama-dei.com:6001/set1?'+data.msg)
       * */
        /*window.open('https://kama-dei.com:6001/set1?'+data.msg)*/

//console.log(data);
        window.open('<?php echo env('CHAT_BOT_ADMIN_URL') ?>'+data.msg);
      }

    }
  });
</script>
