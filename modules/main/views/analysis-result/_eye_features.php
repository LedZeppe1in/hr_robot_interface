<?php

/* @var $eyeFeatures app\modules\main\controllers\AnalysisResultController */

?>

<div class="row">
    <div class="col-md-12">
        <?php
            echo '<pre>';
            if ($eyeFeatures != null)
                print_r($eyeFeatures);
            else
                echo '<span class="not-set">(не задано)</span>';
            echo '</pre>';
        ?>
    </div>
</div>