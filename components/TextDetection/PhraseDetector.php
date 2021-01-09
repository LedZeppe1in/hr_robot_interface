<?php

namespace app\components\TextDetection;

use app\components\TextDetection\TextFrequencyDetector;


//include_once('../TextDetection/TextFrequencyDetector.php');

class PhraseDetector
{

//для большей точности нужно иметь данные о времени, когда слово закончили произносить

    public static $SecInLetter=0.1;

    public static $YesPhraseSynonyms= array("да","разумеется", "безусловно", "конечно", "несомненно", "действительно", "само собой", "разумеется", "подлинно");
    public static $NoPhraseSynonyms= array("нет","нет и нет","никогда", "увольте", "отсутствует", "никак нет", "нетушки", "нету", "нетути", "ничего подобного", "ни в коем случае", "ни под каким видом", "речи быть не может", "ни за что на свете", "ни за какие коврижки", "ни в коем разе", "ни за что", "не имеется", "не водится", "ни хрена", "ни в жизнь", "ни капли");

    public static function GetDumpResult()
    {

$result=array();

$result[]=array("time"=>0.8,"val"=>"Да","text"=>"Some word","frame"=>0,"startFrame"=>0,"endFrame"=>0);
$result[]=array("time"=>0.4,"val"=>"Да","text"=>"one more word","frame"=>1,"startFrame"=>0,"endFrame"=>2);
$result[]=array("time"=>2.4,"val"=>"Да","text"=>"final word","frame"=>1,"startFrame"=>0,"endFrame"=>2);

return result;
}


    public static function GetPhraseEndTime($theTextData,$theStartIndex,$theEndIndex,$thePhrase)
    {
        if (isset($theTextData) && is_array($theTextData) && isset($theStartIndex) && isset($theEndIndex) && isset($thePhrase) &&
            isset($theTextData[$theStartIndex]) && isset($theTextData[$theEndIndex]) && is_array($theTextData[$theStartIndex]) && is_array($theTextData[$theEndIndex]) &&
            isset($theTextData[$theStartIndex][0]) &&  isset($theTextData[$theStartIndex][1]) &&
            isset($theTextData[$theEndIndex][0]) &&  isset($theTextData[$theEndIndex][1]))
        {

            $lettersInWord=iconv_strlen($thePhrase);
            $possibleWordTimeEnd=$theTextData[$theStartIndex][1]+$lettersInWord*PhraseDetector::$SecInLetter;



            if (isset($theTextData[$theEndIndex+1]) && is_array($theTextData[$theEndIndex+1]) &&
                isset($theTextData[$theEndIndex+1][1]))
            {
                //echo "<br> Indexes [$theStartIndex, $theEndIndex] Word ($thePhrase) ends at $possibleWordTimeEnd и ".$theTextData[$theEndIndex+1][1];
                if ($possibleWordTimeEnd>$theTextData[$theEndIndex+1][1])
                {
                    return $theTextData[$theEndIndex+1][1];
                }
            }

            return $possibleWordTimeEnd;
        }

        return null;
    }


    public static function MakePhraseUnitSequence($theFPS,$theText,$theStartTime,$theEndTime,$theTextType)
    {


        $curResult=array();
        if (isset($theFPS) && $theFPS>0 &&
            isset($theEndTime) && isset($theStartTime) &&
            isset($theText) )
        {
            if (!isset($theTextType))$theTextType="";

          //  echo "Start at ($theStartTime) and End at ($theEndTime)";

            $startFrameIndex=round($theStartTime*$theFPS);;
            $endFrameIndex=round($theEndTime*$theFPS);


            for ($i=$startFrameIndex;$i<=$endFrameIndex;$i++)
            {
                $curResult[]=array("time"=>round($i/$theFPS,4),"val"=>$theTextType,"text"=>$theText,"frame"=>$i,"startFrame"=>$startFrameIndex,"endFrame"=>$endFrameIndex);
            }
        }



        return $curResult;
    }


    public static function detectExactPhrase($theSource,$theIndex,$thePattern,$thePhrase,$theResult)
    {
       // echo "<br> detectExactPhrase вызвано для фразы ($thePhrase) и индекса текста ($theIndex): ";

        if (isset($theIndex) &&  isset($thePattern) && is_array($thePattern) &&
                isset($theSource) && is_array($theSource) && isset($theSource[$theIndex]) &&
                    is_array($theSource[$theIndex]) && isset($theSource[$theIndex][0]))
        {
            $thePhrase.=$theSource[$theIndex][0];
            $curFilteredPattern=array();

            foreach ($thePattern as $curPattern)
            {
                if (strpos($curPattern,strtolower($thePhrase))===0)
                {
                    $curFilteredPattern[]=$curPattern;
                    if (strlen($thePhrase)==strlen($curPattern))
                    {
                        $theResult=array("found"=>true,"phrase"=>$thePhrase,"endindex"=>$theIndex);
                    }
                }
            }

            if (count($curFilteredPattern)>0)
            {
                return PhraseDetector::detectExactPhrase($theSource,$theIndex+1,$curFilteredPattern,$thePhrase." ",$theResult);
            }
        }
////////////////////////
        if (isset($theIndex) && isset($theSource) && is_array($theSource) && !isset($theSource[$theIndex]) && isset($thePhrase))
        {
            $lastSpace=strrpos($thePhrase," ");
            if ($lastSpace!==false) {
                $thePhrase = substr($thePhrase, 0, $lastSpace);
            }
            return array("found"=>true,"phrase"=>$thePhrase,"endindex"=>$theIndex-1);
        }
///////////////////////////////

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
                if (isset($startFromTime)) $startIndex = TextFrequencyDetector::ResponseStartIndexInWords($theTextData, $startFromTime);

                $result=array();
                for ($i = $startIndex; $i < $N; $i++)
                {

                    $curOutput=PhraseDetector::detectExactPhrase($theTextData,$i,$theCheckList,"",array("found"=>false));

                    if (isset($curOutput) && $curOutput["found"]==true &&
                        isset($curOutput["endindex"]) && isset($curOutput["phrase"]) && isset($theTextData[$i][1]))
                    {
                        $wordEndTime=PhraseDetector::GetPhraseEndTime($theTextData,$i,$curOutput["endindex"],$curOutput["phrase"]);

                        $curResult=PhraseDetector::MakePhraseUnitSequence($theFPS,$curOutput["phrase"],$theTextData[$i][1],$wordEndTime,$theAliasWord);
                        if (count($curResult))$result[]=$curResult;

                        $i=$curOutput["endindex"];
                    }

                }
                return $result;
            }
        }

        return null;

    }

}