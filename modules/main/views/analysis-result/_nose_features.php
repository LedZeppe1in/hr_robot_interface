<?php

/* @var $noseFeatures app\modules\main\controllers\AnalysisResultController */

?>

<div class="row">
    <div class="col-md-12">
        <?php
            echo '<pre>';
            if ($noseFeatures != null)
                print_r($noseFeatures);
            else
                echo '<span class="not-set">(не задано)</span>';
            echo '</pre>';
        ?>
    </div>
</div>