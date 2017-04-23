<!-- START OF SmartSource Data Collector TAG v10.4.1 -->
<!-- Copyright (c) 2014 Webtrends Inc.  All rights reserved. -->
<script>
window.webtrendsAsyncInit=function(){
    var dcs=new Webtrends.dcs().init({
        dcsid:"<?= Configuration::get('webtrends_datasource') ?>",
        domain:"statse.webtrendslive.com",
        timezone:1,
        i18n:true,
        fpcdom:".<?= Configuration::get('webtrends_host', '') ?>",
        plugins:{
            //hm:{src:"//s.webtrends.com/js/webtrends.hm.js"}
        }
        }).track();
};
(function(){
    var s=document.createElement("script"); s.async=true; s.src="static/js/wt.min.js";
    var s2=document.getElementsByTagName("script")[0]; s2.parentNode.insertBefore(s,s2);
}());
</script>
<noscript><img alt="dcsimg" id="dcsimg" width="1" height="1" src="//statse.webtrendslive.com/<?= Configuration::get('webtrends_datasource') ?>/njs.gif?dcsuri=/nojavascript&amp;WT.js=No&amp;WT.tv=10.4.1&amp;dcsipa=1&amp;dcssip=www.<?= Configuration::get('webtrends_host', '') ?>"/></noscript>
<!-- END OF SmartSource Data Collector TAG v10.4.1 -->
<script type="text/javascript" src="./static/js/wtCairn.js"></script>


<!-- Start Alexa Certify Javascript -->
<script type="text/javascript">
_atrk_opts = { atrk_acct:"HJVzn1QolK10WR", domain:"cairn.info",dynamic: true};
(function() { var as = document.createElement('script'); as.type = 'text/javascript'; as.async = true; as.src = "https://d31qbv1cthcecs.cloudfront.net/atrk.js"; var s = document.getElementsByTagName('script')[0];s.parentNode.insertBefore(as, s); })();
</script>
<noscript><img src="https://d5nxst8fruw4z.cloudfront.net/atrk.gif?account=HJVzn1QolK10WR" style="display:none" height="1" width="1" alt="" /></noscript>
<!-- End Alexa Certify Javascript -->
