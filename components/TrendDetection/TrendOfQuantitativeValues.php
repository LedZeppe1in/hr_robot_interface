<?php
namespace app\components\TrendDetection;

include_once('../IndexedTrend.php');

class  TrendOfQuantitativeValues extends IndexedTrend
{
    public $CompareType=1;//1 - increase (>=), 2 - decrease (<=), 3 - strict increase, 4 - strict decrease, 5 - only equals

    public $QuantitativeValueKey=null;

    public $ValueForDetectionStarted=1;//1
    public $SufficientLevelToEnd=null;
    public $NecessaryValueForValidDetection=null;//not in use

    public $MaxDelta=0;

    public $ExtremumLevelValue=null;
    public $curLevelValue=0;
    public $curLevelIndex=0;

    public $EqualityConditions=array();





    public $curValue=null;


    function __construct($theValueKey,$theCompareType)
    {
        $this->QuantitativeValueKey=$theValueKey;
        $this->CompareType=$theCompareType;

        //  parent::__construct();
    }

    public function CheckConditions($theData)
    {

        if (isset($EqualityConditions) && is_array($EqualityConditions))
        {
            foreach ($EqualityConditions as $k=>$v)
            {
                if (isset($theData) && isset($theData[$k]))
                {
                    if ($theData[$k]!=$v) return false;
                }
            }
        }

        return true;

    }


    public function MakeNewStartIndexOfTrend($theCurVal,$theIndex)
    {

        if (isset($theCurVal) && $this->TrendStartedAt<0) {

            if ($this->ValueForDetectionStarted==null)
            {
                $this->UpdateLevel($theCurVal,$theIndex);
                $this->TrendStartedAt=$theIndex;
                $this->TrendLength++;
                return true;
            }
            else
            {

                switch ($this->CompareType)
                {
                    case 1:
                    case 3:

                        if ($theCurVal>=$this->ValueForDetectionStarted)
                        {
                            $this->TrendStartedAt=$theIndex;
                            $this->TrendCount++;
                            $this->UpdateLevel($theCurVal,$theIndex);
                            if ($this->IsSufficientLevelToEnd($theCurVal)) $this->TrendEndedAt =$theIndex;
                        }
                        break;
                    case 2:
                    case 4:
                        if ($theCurVal<=$this->ValueForDetectionStarted)
                        {
                            $this->TrendStartedAt=$theIndex;
                            $this->TrendCount++;
                            $this->UpdateLevel($theCurVal,$theIndex);
                            if ($this->IsSufficientLevelToEnd($theCurVal)) $this->TrendEndedAt =$theIndex;
                        }
                        break;
                    case 5:
                        if ($theCurVal==$this->ValueForDetectionStarted)
                        {
                            $this->TrendStartedAt=$theIndex;
                            $this->TrendCount++;
                            $this->UpdateLevel($theCurVal,$theIndex);
                        }
                        break;
                    default: return $this->TrendStartedAt;
                }

                return true;
            }
        }

        return false;
    }


    public function EndTrend($theIndex)
    {
        $this->TrendEndedAt=$theIndex-1;
        return true;
    }

    public function UpdateLevel($theCurVal,$theIndex)
    {

        switch ($this->CompareType) {
            case 1:
            case 3:
                if ($theCurVal >= $this->curLevelValue)//если пробили или обновили максимум, то обновляем параметры
                {
                    $this->curLevelValue = $theCurVal;
                    $this->curLevelIndex = $theIndex;
                }
                break;
            case 2:
            case 4:
                if ($theCurVal <= $this->curLevelValue)//если пробили или обновили минимум, то обновляем параметры
                {
                    $this->curLevelValue = $theCurVal;
                    $this->curLevelIndex = $theIndex;
                }
                break;
            case 5:
                $this->curLevelValue = $theCurVal;
                $this->curLevelIndex = $theIndex;
                break;
        }


    }


    public function IsSufficientLevelToEnd($theCurVal)
    {
        if (isset($this->SufficientLevelToEnd))
        {
            switch ($this->CompareType) {
                case 1:
                case 3:
                    if ($theCurVal >= $this->SufficientLevelToEnd) return true;
                    break;
                case 2:
                case 4:
                    if ($theCurVal <= $this->SufficientLevelToEnd) return true;
                    break;
                case 5:
                    if ($theCurVal == $this->SufficientLevelToEnd) return true;
                    break;
            }
        }

        return false;
    }

    public function IsTrendContinues($theCurData, $thePrevData,$theIndex)
    {
        $trendContinues=null;


        if (isset($theIndex) &&
            isset($theCurData) && isset($theCurData[$this->QuantitativeValueKey]) &&
            isset($thePrevData) && isset($thePrevData[$this->QuantitativeValueKey]))
        {


            $curVal=$theCurData[$this->QuantitativeValueKey];
            $prevVal=$thePrevData[$this->QuantitativeValueKey];

            if ($this->IsDebugging) echo "Начало проверки продолжения тренда: было ($prevVal) стало ($curVal), максимум ($this->curLevelValue) <br>";

            $this->UpdateLevel($curVal,$theIndex);


            switch ($this->CompareType) {
                case 1:
                    if ($prevVal<=$curVal)//значения увеличиваются
                    {
                        return true;
                    }
                    else
                    {
                        //значения уменьшаются, но впределах погрешности (относительно ближайшего максимума)
                        if ($this->curLevelValue-$curVal<=$this->MaxDelta)
                        {
                            return true;
                        }
                        return false;
                    }
                    break;
                case 2:
                    if ($prevVal>=$curVal)//значения уменьшаются
                    {
                        return true;
                    }
                    else
                    {
                        //значения увеличиваются, но впределах погрешности (относительно ближайшего минимума)
                        if ($curVal-$this->curLevelValue<=$this->MaxDelta)
                        {
                            return true;
                        }
                        return false;
                    }
                    break;
                case 3:
                    if ($prevVal<$curVal)//значения увеличиваются
                    {
                        return true;
                    }
                    else
                    {
                        //значения уменьшаются, но впределах погрешности (относительно ближайшего максимума)
                        if ($this->curLevelValue-$curVal<=$this->MaxDelta)
                        {
                            return true;
                        }
                        return false;
                    }
                    break;
                case 4:
                    if ($prevVal>$curVal)//значения уменьшаются
                    {
                        return true;
                    }
                    else
                    {
                        //значения увеличиваются, но впределах погрешности (относительно ближайшего минимума)
                        if ($curVal-$this->curLevelValue<=$this->MaxDelta)
                        {
                            $trendContinues=true;
                        }
                        return false;
                    }
                    break;
                case 5:
                    if ($prevVal==$curVal) return true;
                    break;
                default:
                    $trendContinues=false;
            }

            return false;
        }



        return $trendContinues;
    }

    public function DetectTrend($theCurData, $thePrevData, $theCurIndex,$theConditions)
    {



        if (isset($theCurData) && isset($theCurData[$this->QuantitativeValueKey]) && isset($theCurIndex))
        {

            $this->curValue=$theCurData[$this->QuantitativeValueKey];

            if ($this->IsDebugging) echo "Вход в определение количественного тренда выполнен ($this->curValue)<br>";

            if (($this->TrendStartedAt>=0) &&
                isset($thePrevData) && isset($thePrevData[$this->QuantitativeValueKey]))
            {

                if (isset($theConditions) && is_array($theConditions) && !$this->CheckConditions($theConditions))
                {
                    return $this->EndTrend($theCurIndex);
                }

                if ($this->IsSufficientLevelToEnd($this->curValue))
                {
                    if ($this->IsDebugging) echo "Требуемый уровень достигнут ($this->curValue)<br>";
                    $this->TrendLength++;
                    $this->TrendEndedAt=$theCurIndex;
                    return true;
                }


                if ($this->IsTrendContinues($theCurData,$thePrevData,$theCurIndex) )
                {
                    $this->TrendLength++;
                    if ($this->IsDebugging) echo "Тренд продолжается <br>";
                    return true;
                }
                else
                {
                    return $this->EndTrend($theCurIndex);
                }
            }



            if ($this->TrendStartedAt<0)
            {
                $this->MakeNewStartIndexOfTrend($this->curValue,$theCurIndex);
            }
        }

        return false;
    }

    public function ResetTrend()
    {
        parent::ResetTrend();


        if (!isset($this->ExtremumLevelValue) || $this->ExtremumLevelValue==null)
        {
            switch ($this->CompareType) {
                case 1:
                case 3:
                    $this->curLevelValue=0;
                    break;
                case 2:
                case 4:
                    $this->curLevelValue=100;
                    break;
            }
        }
        else
        {
            $this->curLevelValue=$this->ExtremumLevelValue;
        }


    }


}