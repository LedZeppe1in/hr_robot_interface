<?php

namespace app\components\TextDetection;

class PhraseDetector
{

//для большей точности нужно иметь данные о времени, когда слово закончили произносить

    public static $SecInLetter=0.1;

    public static $YesPhraseSynonyms= array("да","разумеется", "безусловно", "конечно", "несомненно", "действительно", "само собой", "разумеется", "подлинно");
    public static $NoPhraseSynonyms= array("нет","никогда", "увольте", "отсутствует", "никак нет", "нетушки", "нету", "нетути", "ничего подобного", "ни в коем случае", "ни под каким видом", "речи быть не может", "ни за что на свете", "ни за какие коврижки", "ни в коем разе", "ни за что", "не имеется", "не водится", "ни хрена", "ни в жизнь", "ни капли");

    public static function GetDumpResult()
    {

        $result=array();

        $result[]=array("time"=>0.8,"val"=>"Да","text"=>"Some word","frame"=>0,"startFrame"=>0,"endFrame"=>0);
        $result[]=array("time"=>0.4,"val"=>"Да","text"=>"one more word","frame"=>1,"startFrame"=>0,"endFrame"=>2);
        $result[]=array("time"=>2.4,"val"=>"Да","text"=>"final word","frame"=>1,"startFrame"=>0,"endFrame"=>2);

        return result;
    }


    public static function GetWordEndTime($theTextData,$theIndex)
    {
        if (isset($theTextData) && is_array($theTextData) && isset($theTextData[$theIndex])
            && is_array($theTextData[$theIndex]) && isset($theTextData[$theIndex][0]))
        {


            $lettersInWord=iconv_strlen($theTextData[$theIndex][0]);
            $possibleWordTimeLength=$theTextData[$theIndex][1]+$lettersInWord*PhraseDetector::$SecInLetter;

            if (isset($theTextData[$theIndex+1]) && is_array($theTextData[$theIndex+1]) &&
                isset($theTextData[$theIndex+1][1]))
            {

                if ($possibleWordTimeLength>$theTextData[$theIndex+1][1])
                {
                    return $theTextData[$theIndex+1][1];
                }
            }

            return $possibleWordTimeLength;
        }

        return null;
    }


    public static function MakePhraseUnitSequence($theResult,$theFPS,$theTextElement,$endTime,$theTextType)
    {
        if (isset($theResult) && is_array($theResult) &&
            isset($theFPS) && $theFPS>0 &&
            isset($endTime) &&
            isset($theTextElement) && is_array($theTextElement) && isset($theTextElement[0]) && $theTextElement[1])
        {

            if (!isset($theTextType))$theTextType="";

            $startTime=$theTextElement[1];
            $startFrameIndex=round($theTextElement[1]*$theFPS);
            $endFrameIndex=round($endTime*$theFPS);


            for ($i=$startFrameIndex;$i<=$endFrameIndex;$i++)
            {
                $theResult[]=array("time"=>round($startTime+$i/$theFPS,4),"val"=>$theTextType,"text"=>$theTextElement[0],"frame"=>$i,"startFrame"=>$startFrameIndex,"endFrame"=>$endFrameIndex);
            }
        }


        return $theResult;
    }

    public static function GetPhrase($theTextData,$theFPS,$theCheckList,$theAliasWord,$startFromTime)
    {

        if (isset($theTextData) && is_array($theTextData) &&
            isset($theCheckList) && is_array($theCheckList) &&
            isset($theFPS) && isset($theAliasWord))
        {

            $N=count($theTextData);

            if ($N>0)
            {

                $startIndex = 0;
                if (isset($startFromTime)) $startIndex = FrequencyDetector::ResponseStartIndexInWords($theTextData, $startFromTime);

                $result=array();

                for ($i = $startIndex; $i < $N; $i++)
                {

                    if (isset($theTextData[$i]) && isset($theTextData[$i][0]) && isset($theTextData[$i][1]))
                    {
                        $wordEndTime=PhraseDetector::GetWordEndTime($theTextData,$i);


                        if ( in_array(strtolower($theTextData[$i][0]), $theCheckList))
                        {
                            // echo "слово '{$theTextData[$i][0]}' начинается в {$theTextData[$i][1]} и заканчивается в $wordEndTime<br>";

                            array_push($result,PhraseDetector::MakePhraseUnitSequence($result,$theFPS,$theTextData[$i],$wordEndTime,$theAliasWord));

                        }



                    }
                }
                return $result;
            }


        }

        return null;

    }

}