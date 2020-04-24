<?php

/* @var $browFeatures app\modules\main\controllers\DetectionResultController */

?>

<div class="row">
    <div class="col-md-12">
        <?php
            echo '<pre>';
            if ($browFeatures != null)
                print_r($browFeatures);
            else
                echo '<span class="not-set">(не задано)</span>';
            echo '</pre>';
        ?>
    </div>
</div>