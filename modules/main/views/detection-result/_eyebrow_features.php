<?php

/* @var $eyebrowFeatures app\modules\main\controllers\DetectionResultController */

?>

<div class="row">
    <div class="col-md-12">
        <?php
            echo '<pre>';
            if ($eyebrowFeatures != null)
                print_r($eyebrowFeatures);
            else
                echo '<span class="not-set">(не задано)</span>';
            echo '</pre>';
        ?>
    </div>
</div>