<!DOCTYPE html>
<html>
<?php include('header.php'); ?>
<body>
    <!-- menu -->
    <?php include('menu.php'); ?>
     <!-- main -->
    <main class="canvi-content main-content" style="height:100%;" >
        <div class="container" style="height:100%;" >
            <button class="canvi-btn canvi-open-btn menu-open" type="button">
                <span class="icon-bar bar1"></span>
                <span class="icon-bar bar2"></span>
                <span class="icon-bar bar3"></span>
            </button>
            <div class="row p-0" style="height:100%;" >
                <div class="col-12" style="height:100%;">
                    <?php include('content.php');?>
                </div>
            </div>
        </div>
    </main>
    <!--footer-->
    <?php include('footer.php'); ?>
</body>
</html>