<?php

namespace app\components\TrendDetection;

class IndexedTrend
{
    public $Optional=false;
    public $TrendStartedAt=-1;
    public $TrendEndedAt=-1;
    public $TrendLength=0;

    public $IsDebugging=false;

    public $SufficientTrendLength=null;//not in use


    public function ResetTrend()
    {
        $this->TrendStartedAt=-1;
        $this->TrendEndedAt=-1;
        $this->TrendLength=0;
    }

    public function ForcedEnding()
    {

    }


    public function DetectTrend($theCurData,$thePrevData,$theCurIndex,$theConditions)
    {

    }

}