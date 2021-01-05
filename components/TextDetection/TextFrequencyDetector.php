<?php

namespace app\components\TextDetection;

class TextFrequencyDetector
{

    public static function CountSpeechFrequencyByWords($theTextData,$AnswerTime)
    {

        if (isset($theTextData) && is_array($theTextData) && isset($AnswerTime))
        {

            $WordsCount=count($theTextData);

            if ($WordsCount>0 && $AnswerTime>=0)
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
            }
        }
        return null;
    }


}