<?php
namespace app\components\TrendDetection;

//include_once('../IndexedTrend.php');

use app\components\TrendDetection\IndexedTrend;


class TrendOfQualitativeValues extends IndexedTrend
{

    public $QualitativeValueKey=null;
    public $PossibleValues=array();//up; down

    public $QuantitativeTrendDetector=null;


    function __construct($theQualityValueKey, $thePossibleValues)
    {
        $this->QualitativeValueKey=$theQualityValueKey;

        if (isset($thePossibleValues) && is_array($thePossibleValues)) $this->PossibleValues=$thePossibleValues;
    }

    public function ResetTrend()
    {
        parent::ResetTrend();

        if (isset($this->QuantitativeTrendDetector)) $this->QuantitativeTrendDetector->ResetTrend();
    }


    public function DetectTrend($theCurData,$thePrevData,$theCurIndex,$theConditions)
    {
        // echo "Возможные значения ".print_r($this->PossibleValues)."<br>";
        /* echo "Текущие данные: "."<br>";
            // print_r($theCurData);
             echo $theCurData[$this->QualitativeValueKey];
         echo "<br>";*/



        if ( isset($this->PossibleValues) && is_array($this->PossibleValues) &&
            isset($theCurData) &&  isset($theCurData[$this->QualitativeValueKey]))
        {

            if (in_array($theCurData[$this->QualitativeValueKey],$this->PossibleValues))
            {
                if ($this->IsDebugging) echo "<br>Качественный тренд (вызов) = ".$theCurIndex."<br>";

                if (isset($this->QuantitativeTrendDetector))
                {
                    $this->QuantitativeTrendDetector->DetectTrend($theCurData,$thePrevData,$theCurIndex,$theConditions);

                    if($this->QuantitativeTrendDetector->TrendEndedAt>0)
                    {
                        $this->TrendStartedAt = $this->QuantitativeTrendDetector->TrendStartedAt;
                        $this->TrendEndedAt = $this->QuantitativeTrendDetector->TrendEndedAt;

                        if ($this->IsDebugging) echo "Обнаружение качественного тренда завершено!!! Начало ($this->TrendStartedAt)  и Конец ($this->TrendEndedAt) <br>";
                    }

                }
                else
                {
                    if ($this->TrendStartedAt<0)
                    {
                        $this->TrendStartedAt=$theCurIndex;
                    }
                }
            }
            else
            {
                //if ($this->IsDebugging) echo "Значение качественного тренда отличается от требуемого ({$theCurData[$this->QualitativeValueKey]}) в кадре ($theCurIndex)<br>";
                if (isset($this->QuantitativeTrendDetector) ) {

                    if ($this->QuantitativeTrendDetector->TrendStartedAt > 0) {

                        $this->TrendStartedAt = $this->QuantitativeTrendDetector->TrendStartedAt;
                        $this->TrendEndedAt = $theCurIndex-1;

                        if ($this->IsDebugging) echo "Обнаружение качественного тренда завершено!!! Начало ($this->TrendStartedAt)  и Конец ($this->TrendEndedAt) <br>";
                    }
                }
                else
                {
                    $this->TrendEndedAt = $theCurIndex;
                }
            }
        }




        return false;
    }


}