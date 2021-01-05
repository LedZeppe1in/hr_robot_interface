<?php
namespace app\components\TrendDetection;

include_once('../IndexedTrend.php');

class TrendSequence extends IndexedTrend
{
    public $trendSequence=array();

    public $curTrendIndex=0;

    public function IsForcedEnding()
    {
        if ($this->TrendStartedAt>0 && $this->TrendLength>1 )
        {
            return true;
        }

        return false;
    }

    public function isEnding()
    {
        if ($this->trendSequence[$this->curTrendIndex]->TrendEndedAt>=0 &&
            $this->curTrendIndex+1>=count($this->trendSequence))
        {
            return true;
        }
    }

    public function NextTrend()
    {

    }


    public function DetectTrend($theCurData,$thePrevData,$theCurIndex,$theConditions)
    {
        if (isset($theCurIndex) && isset ($this->trendSequence) && is_array($this->trendSequence) && count($this->trendSequence)>0)
        {
            $N=count($this->trendSequence);

            if (isset($this->trendSequence[$this->curTrendIndex]))
            {

                $this->trendSequence[$this->curTrendIndex]->DetectTrend($theCurData,$thePrevData,$theCurIndex,$theConditions);

                if ($this->TrendStartedAt<0 && $this->trendSequence[$this->curTrendIndex]->TrendStartedAt>0)
                    $this->TrendStartedAt=$this->trendSequence[$this->curTrendIndex]->TrendStartedAt;

                if ($this->IsDebugging) {
                    echo "После вызова элемента трендовой последовательности для " . $theCurIndex . "<br>";
                    echo "Начало тренда " . $this->trendSequence[$this->curTrendIndex]->TrendStartedAt . "<br>";
                    echo "Конец тренда " . $this->trendSequence[$this->curTrendIndex]->TrendEndedAt . "<br>";
                    echo "<br>";
                }



                if ($this->trendSequence[$this->curTrendIndex]->TrendEndedAt>=0)
                {
                    $endIndex=$this->trendSequence[$this->curTrendIndex]->TrendEndedAt;
                    $this->curTrendIndex++;

                    if ($this->curTrendIndex>=$N)
                    {
                        //$this->TrendEndedAt=$theCurIndex;//-1?????

                        $this->TrendStartedAt=$this->trendSequence[0]->TrendStartedAt;
                        $this->TrendEndedAt=$endIndex;

                        if ($this->IsDebugging) echo "Обнаружение последовательности трендов завершено!!! Начало ($this->TrendStartedAt)  и Конец ($this->TrendEndedAt) <br>";

                        return;
                    }

                    if (isset($this->trendSequence[$this->curTrendIndex]))
                    {
                        if ($this->IsDebugging) echo "Конец тренда обнаружен!!! Определитель тренда с номером $this->curTrendIndex активен<br>";

                        //конец старого тренда - это начало нового
                        $this->trendSequence[$this->curTrendIndex]->DetectTrend($thePrevData,null,$theCurIndex-1,$theConditions);
                        //обработка текущего индекса
                        $this->trendSequence[$this->curTrendIndex]->DetectTrend($theCurData,$thePrevData,$theCurIndex,$theConditions);
                        //если сразу завершаем
                        if ($this->trendSequence[$this->curTrendIndex]->TrendEndedAt>=0)
                        {
                            $endIndex=$this->trendSequence[$this->curTrendIndex]->TrendEndedAt;
                            if ($this->curTrendIndex+1>=count($this->trendSequence))
                            {
                                $this->TrendEndedAt=$endIndex;
                                if ($this->IsDebugging) echo "Обнаружение последовательности трендов завершено!!! Начало ($this->TrendStartedAt)  и Конец ($this->TrendEndedAt) <br>";
                                return;
                            }
                            else
                            {
                                $this->curTrendIndex++;
                            }

                        }

                        //если сразу достишли желаемого
                        //(SufficientTrendLength)?
                    }

                }
            }
        }
    }

    public function ResetTrend()
    {
        parent::ResetTrend();
        $this->curTrendIndex=0;
        foreach ($this->trendSequence as $curTrend)
        {
            $curTrend->ResetTrend();
        }
    }

}