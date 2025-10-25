<?php

namespace app\components\TextDetection;

class TextFrequencyDetector
{

    //null - ошибка, проблемы и т.п.
    //-1 - текста в указанный момент времени нет
    //0,... - текст в указнное время есть и возварщается индекс элемента в массиве распознанных слов

    public static function IsTextInTime($theTime, $theTextData,$theStartIndex)
    {
        if (isset($theTime) && isset($theTextData) && is_array($theTextData)  && $theTime>0)
        {
            $N=count($theTextData);
            $startFrom=0;
            if (isset($theStartIndex) && $theStartIndex>=0) $startFrom=$theStartIndex;

            for ($i=$startFrom; $i<$N; $i++)
            {
                if (isset($theTextData[$i][1]) && isset($theTextData[$i][2]))
                {
                    $startTime=$theTextData[$i][1];
                    $durationTime=$theTextData[$i][2];
                    if ($theTime>$startTime && $theTime<$startTime+$durationTime)return $i;
                }
            }
            return -1;
        }
        return null;
    }



    public static function CountSpeechFrequencyByWords($theTextData,$AnswerTime,$theStartIndex)
    {

        if (isset($theTextData) && is_array($theTextData) && isset($AnswerTime))
        {

            $WordsCount=count($theTextData);
            if (isset($theStartIndex))
            {

                if ($theStartIndex>=0)
                {
                    $WordsCount-=$theStartIndex;
                }
                else{
                    return null;
                }

            }


            if ($WordsCount>0 && $AnswerTime>0)
            {
                return (double)$WordsCount/$AnswerTime;
            }


        }


        return null;

    }

    public static function ResponseStartTimeByWords($theTextData,$theOtherVoiceEndsAtSec)
    {
        if (isset($theTextData) && is_array($theTextData) && isset($theOtherVoiceEndsAtSec))
        {

            $WordCount=count($theTextData);
            if ($WordCount>0 )
            {
                for ($i=0;$i<$WordCount;$i++)
                {
                    if ( isset($theTextData[$i]) && isset($theTextData[$i][1]))
                    {
                        if ($theTextData[$i][1]>$theOtherVoiceEndsAtSec) return $theTextData[$i][1];
                    }
                }
            }
        }
        return null;
    }

    public static function ResponseStartIndexInWords($theTextData,$theOtherVoiceEndsAtSec)
    {
        if (isset($theTextData) && is_array($theTextData) && isset($theOtherVoiceEndsAtSec))
        {

            $WordCount=count($theTextData);
            if ($WordCount>0 )
            {
                for ($i=0;$i<$WordCount;$i++)
                {
                    if ( isset($theTextData[$i]) && isset($theTextData[$i][1]))
                    {
                        if ($theTextData[$i][1]>$theOtherVoiceEndsAtSec) return $i;
                    }
                }
                return -1;
            }
        }
        return null;
    }
}