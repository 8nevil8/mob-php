<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title><?=$title?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta http-equiv="Content-Script-Type" content="text/javascript" />
        <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
        <META name="keywords" content="<?=$metaKeywords?>"/>
              <?= $appStyle ?>
              <?= $appJS ?>
        <script type="text/javascript">
            $(function(){
                var selectedMenuItem = '<?= $selectedMenuItem ?>';
                $('a#'+selectedMenuItem).addClass('sel');
            });
        </script>
    </head>

    <body id="content_container">
        <div id="center">
            <?= @$head ?>
            <?= $menu ?>
            <!--            <div class="clear"></div>-->
            <div class="main_content">
                <?= $content ?>
            </div>
        </div>
        <script type="text/javascript">
            if(typeof onDocumentReady == 'function') {
                $(document).ready(onDocumentReady());
            }
        </script>
    </body>
</html>
