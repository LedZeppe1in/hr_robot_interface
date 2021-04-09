<?php

/* @var $mouthFeatures app\modules\main\controllers\DetectionResultController */

?>

<div class="row">
    <div class="col-md-12">
        <?php
            echo '<pre>';
            if ($mouthFeatures != null)
                print_r($mouthFeatures);
            else
                echo '<span class="not-set">(не задано)</span>';
            echo '</pre>';
        ?>
    </div>
</div>