<?php

namespace app\components;

/**
 * FaceFeatureDetector - класс обнаружения лицевых признаков на основе анализа видео-кадров.
 *
 * @package app\components
 */
class FaceFeatureDetector
{
    public function EyeDetector($theFaceData)
    {
        $theFaceData["Characteristics"]["eye"]["right_eye_inner"]["MaxX"] = self::FaceDataMaxForKey($theFaceData["eye"]["right_eye_inner"], "X")[0];
        $theFaceData["Characteristics"]["eye"]["right_eye_inner"]["MaxY"] = self::FaceDataMaxForKey($theFaceData["eye"]["right_eye_inner"], "Y")[0];
        $theFaceData["Characteristics"]["eye"]["right_eye_inner"]["MinX"] = self::FaceDataMinForKey($theFaceData["eye"]["right_eye_inner"], "X")[0];
        $theFaceData["Characteristics"]["eye"]["right_eye_inner"]["MinY"] = self::FaceDataMinForKey($theFaceData["eye"]["right_eye_inner"], "Y")[0];
        $theFaceData["Characteristics"]["eye"]["right_eye_inner"]["NatX"] = 422;//здесь и далее опрделено экспертно на основе визуального анализа точек (кадры 115 и 119)
        $theFaceData["Characteristics"]["eye"]["right_eye_inner"]["NatY"] = 250;

        $theFaceData["Characteristics"]["eye"]["right_eye_outer"]["MaxX"] = self::FaceDataMaxForKey($theFaceData["eye"]["right_eye_outer"], "X")[0];
        $theFaceData["Characteristics"]["eye"]["right_eye_outer"]["MaxY"] = self::FaceDataMaxForKey($theFaceData["eye"]["right_eye_outer"], "Y")[0];
        $theFaceData["Characteristics"]["eye"]["right_eye_outer"]["MinX"] = self::FaceDataMinForKey($theFaceData["eye"]["right_eye_outer"], "X")[0];
        $theFaceData["Characteristics"]["eye"]["right_eye_outer"]["MinY"] = self::FaceDataMinForKey($theFaceData["eye"]["right_eye_outer"], "Y")[0];
        $theFaceData["Characteristics"]["eye"]["right_eye_outer"]["NatX"] = 538;
        $theFaceData["Characteristics"]["eye"]["right_eye_outer"]["NatY"] = 250;

        $theFaceData["Characteristics"]["eye"]["right_upper_eyelid"]["MaxX"] = self::FaceDataMaxForKey($theFaceData["eye"]["right_upper_eyelid"], "X")[0];
        $theFaceData["Characteristics"]["eye"]["right_upper_eyelid"]["MaxY"] = self::FaceDataMaxForKey($theFaceData["eye"]["right_upper_eyelid"], "Y")[0];
        $theFaceData["Characteristics"]["eye"]["right_upper_eyelid"]["MinX"] = self::FaceDataMinForKey($theFaceData["eye"]["right_upper_eyelid"], "X")[0];
        $theFaceData["Characteristics"]["eye"]["right_upper_eyelid"]["MinY"] = self::FaceDataMinForKey($theFaceData["eye"]["right_upper_eyelid"], "Y")[0];
        $theFaceData["Characteristics"]["eye"]["right_upper_eyelid"]["NatX"] = 505;
        $theFaceData["Characteristics"]["eye"]["right_upper_eyelid"]["NatY"] = 232;

        $theFaceData["Characteristics"]["eye"]["right_lower_eyelid"]["MaxX"] = self::FaceDataMaxForKey($theFaceData["eye"]["right_lower_eyelid"], "X")[0];
        $theFaceData["Characteristics"]["eye"]["right_lower_eyelid"]["MaxY"] = self::FaceDataMaxForKey($theFaceData["eye"]["right_lower_eyelid"], "Y")[0];
        $theFaceData["Characteristics"]["eye"]["right_lower_eyelid"]["MinX"] = self::FaceDataMinForKey($theFaceData["eye"]["right_lower_eyelid"], "X")[0];
        $theFaceData["Characteristics"]["eye"]["right_lower_eyelid"]["MinY"] = self::FaceDataMinForKey($theFaceData["eye"]["right_lower_eyelid"], "Y")[0];
//$theFaceData["Characteristics"]["eye"]["right_lower_eyelid"]["MinYFrame"]=FaceDataMinForKey($theFaceData["eye"]["right_lower_eyelid"],"Y")[1];
        $theFaceData["Characteristics"]["eye"]["right_lower_eyelid"]["NatX"] = 505;
        $theFaceData["Characteristics"]["eye"]["right_lower_eyelid"]["NatY"] = 258;

//Всё сделано для правого глаза
//right_upper_eyelid - Верхнее веко, движение верхнего века (N вверх, S вниз)
        self::Ymoves($theFaceData["eye"]["right_upper_eyelid"], $theFaceData["Characteristics"]["eye"]["right_upper_eyelid"]);

//right_lower_eyelid - Нижнее веко, движение нижнего века (X без движения, N вверх, S вниз)
        self::Ymoves($theFaceData["eye"]["right_lower_eyelid"], $theFaceData["Characteristics"]["eye"]["right_lower_eyelid"]);

//Внутренний уголок глаза, движение внутреннего уголка глаза (N вверх, S вниз)
//right_eye_inner
        self::Ymoves($theFaceData["eye"]["right_eye_inner"], $theFaceData["Characteristics"]["eye"]["right_eye_inner"]);

//Внешний уголок глаза, движение внешнего уголка глаза (N вверх, S вниз)
//right_eye_outer
        self::Ymoves($theFaceData["eye"]["right_eye_outer"], $theFaceData["Characteristics"]["eye"]["right_eye_outer"]);


//right_eye_width - ширина глаз по Y ("+" увеличение ,"-" уменьшение)


        //создаем FaceFeature
        $theFaceData["eye"]["right_eye_width"] = [];

        //расчет Width для right_eye_width
        self::AddOneDimDistance($theFaceData["eye"]["right_eye_width"], "Width", $theFaceData["eye"]["right_lower_eyelid"], "Y", $theFaceData["eye"]["right_upper_eyelid"], "Y");

        //расчет характерисик для right_eye_width , сохранение характеристик

        $right_eye_width_nat = self::FaceDataAvrForKey($theFaceData["eye"]["right_eye_width"], "Width");//Естесвенная ширина - это среднее за время наблюдений
        $right_eye_width_max = self::FaceDataMaxForKey($theFaceData["eye"]["right_eye_width"], "Width")[0];
        $right_eye_width_min = self::FaceDataMinForKey($theFaceData["eye"]["right_eye_width"], "Width")[0];

        $theFaceData["Characteristics"]["eye"]["right_eye_width"] = [];
        $theFaceData["Characteristics"]["eye"]["right_eye_width"]["Max"] = $right_eye_width_max;
        $theFaceData["Characteristics"]["eye"]["right_eye_width"]["Min"] = $right_eye_width_min;
        $theFaceData["Characteristics"]["eye"]["right_eye_width"]["Nat"] = $right_eye_width_nat;

        //расчет WidthChange и WidthChangeForce для right_eye_width
        self::Dmoves($theFaceData["eye"]["right_eye_width"], "Width", $right_eye_width_max, $right_eye_width_min, $right_eye_width_nat);

        return 1;
    }

    public function AddOneDimDistance(& $theOutput, $theOutputKey, $theArray1, $theArrayKey1, $theArray2, $theArrayKey2)
    {
        $N1 = count($theArray1);
        if ($N1 <= 0) return 0;

        $N2 = count($theArray2);
        if ($N2 <= 0) return 0;

        if ($N1 != $N2) return 0;

        for ($i = 0; $i < $N1; $i++) {
            $theOutput[] = [];
            if ($theArray1[$i] && $theArray1[$i][$theArrayKey1] && $theArray2[$i] && $theArray2[$i][$theArrayKey2]) {
                $theOutput[$i][$theOutputKey] = $theArray1[$i][$theArrayKey1] - $theArray2[$i][$theArrayKey2];
            }
        }

        return 1;
    }

// увеличение + , уменьшение - 
    public function Dmoves(& $theArray, $theKey, $theMax, $theMin, $theNat)
    {
        $N = count($theArray);
        if ($N <= 0) return 0;

        $deltaForMinus = $theMin - $theNat;
        $deltaForPlus = $theMax - $theNat;

        for ($i = 0; $i < $N; $i++) {
            if ($theArray[$i] && $theArray[$i][$theKey]) {
                if ($theArray[$i][$theKey] < $theNat)//уменьшение ширины
                {
                    $theArray[$i]["WidthChange"] = "-";
                    $theArray[$i]["WidthChangeForce"] = round((($theArray[$i][$theKey] - $theNat) / $deltaForMinus), 2);

                } elseif ($theArray[$i][$theKey] > $theNat)//увеличение шиирны
                {
                    $theArray[$i]["WidthChange"] = "+";
                    $theArray[$i]["WidthChangeForce"] = round((($theArray[$i][$theKey] - $theNat) / $deltaForPlus), 2);
                } else {
                    //ввести погрешность для определения отсутсвтия движения
                    $theArray[$i]["WidthChange"] = "X";
                    $theArray[$i]["WidthChangeForce"] = 0;
                }
            }
        }
    }

// движение N - вверх , S - вниз
    public function Ymoves(& $theArray, $Characteristics)
    {
        $N = count($theArray);
        if ($N <= 0) return 0;

        $deltaForN = $Characteristics["MinY"] - $Characteristics["NatY"];
        $deltaForS = $Characteristics["MaxY"] - $Characteristics["NatY"];

        for ($i = 0; $i < $N; $i++) {
            if ($theArray[$i] && $theArray[$i]["Y"]) {
                if ($theArray[$i]["Y"] < $Characteristics["NatY"]) {
                    $theArray[$i]["MovementDirection"] = "N";
                    $theArray[$i]["MovementForce"] = round((($theArray[$i]["Y"] - $Characteristics["NatY"]) / $deltaForN), 2);

                } elseif ($theArray[$i]["Y"] > $Characteristics["NatY"]) {
                    $theArray[$i]["MovementDirection"] = "S";
                    $theArray[$i]["MovementForce"] = round((($theArray[$i]["Y"] - $Characteristics["NatY"]) / $deltaForS), 2);
                } else {
                    //ввести погрешность для определения отсутсвтия движения
                    $theArray[$i]["MovementDirection"] = "X";
                    $theArray[$i]["MovementForce"] = 0;
                }
            }
        }
    }

//    public function FaceDataMinMaxForKey($theArray, $theKey)
//    {
//        return array("max" => $max, "max_el" => $maxFrame, "min" => $min, "min_el" => $minFrame);
//    }

    public function FaceDataAvrForKey($theArray, $theKey)
    {
        $N = count($theArray);
        if ($N <= 0) return 0;


        $Avr = 0;
        for ($i = 0; $i < $N; $i++) {
            if ($theArray[$i] && $theArray[$i][$theKey]) {
                $Avr += $theArray[$i][$theKey];
            }
        }
        return round($Avr / $N, 0);
    }

    public function FaceDataMaxForKey($theArray, $theKey)
    {
        $N = count($theArray);
        if ($N <= 0) return 0;

        $max = $theArray[0][$theKey];
        $maxFrame = 0;
        for ($i = 0; $i < $N; $i++) {
            if ($theArray[$i] && $theArray[$i][$theKey]) {
                if ($theArray[$i][$theKey] > $max) {
                    $max = $theArray[$i][$theKey];
                    $maxFrame = $i;
                }
            }
        }
        return array(0 => $max, 1 => $maxFrame);
    }

    public function FaceDataMinForKey($theArray, $theKey)
    {
        $N = count($theArray);
        if ($N <= 0) return 0;

        $min = $theArray[0][$theKey];
        $minFrame = 0;

        for ($i = 0; $i < $N; $i++) {
            if ($theArray[$i] && $theArray[$i][$theKey]) {
                if ($theArray[$i][$theKey] < $min) {
                    $min = $theArray[$i][$theKey];
                    $minFrame = $i;
                }
            }
        }
        return array(0 => $min, 1 => $minFrame);
    }






    public function getForce($val1,$val2)
    {
        $af = $val1 / 5;
        $res = abs(round($val2/$af));
        return $res;
    }

    public function mouthDetector($FaceData_)
    {
        //first frame for standard (norm values)
        //get initial values
        //echo $FaceData_['normmask'][0][48][X];

        for($ii = 1; $ii < count($FaceData_['normmask'][0]); $ii++){
            $FacePoints[$ii] = array(array($FaceData_['normmask'][0][$ii]['X'], 500, 0, 0),
                array($FaceData_['normmask'][0][$ii]['Y'], 500, 0, 0));
        }

        //get min and max values
        for($i = 1; $i < count($FaceData_['normmask']); $i++){
            for($ii = 0; $ii < count($FaceData_['normmask'][$i]); $ii++)
            {
                //min
                //x
//    echo $FacePoints[$ii][0][1].' :: '.$FaceData_['frame_#'.$i]['NORM_POINTS'][$ii][0].'<br>';
                if (($FaceData_['normmask'][$i][$ii]['X']<$FacePoints[$ii][0][1])and
                    ($FaceData_['normmask'][$i][$ii]['X']!=0)) {
                    $FacePoints[$ii][0][1] = $FaceData_['normmask'][$i][$ii]['X'];
                }
                //y
                if (($FaceData_['normmask'][$i][$ii]['Y']<$FacePoints[$ii][1][1])and
                    ($FaceData_['normmask'][$i][$ii]['Y']!=0)) {
                    $FacePoints[$ii][1][1] = $FaceData_['normmask'][$i][$ii]['X'];
                }
                //max
                //x
                if ($FaceData_['normmask'][$i][$ii]['X']>$FacePoints[$ii][0][2]) {
                    $FacePoints[$ii][0][2] = $FaceData_['normmask'][$i][$ii]['X'];
                }
                //y
                if ($FaceData_['normmask'][$i][$ii]['Y']>$FacePoints[$ii][1][2]) {
                    $FacePoints[$ii][1][2] = $FaceData_['normmask'][$i][$ii]['Y'];
                }
            }
        }

        //get scale for x and y
        //lenght of the scale for power detection
        for($i = 0; $i < count($FacePoints); $i++){
            $FacePoints[$i][0][3] = $FacePoints[$i][0][2] - $FacePoints[$i][0][1];
            $FacePoints[$i][1][3] = $FacePoints[$i][1][2] - $FacePoints[$i][1][1];
        }

// print_r($FacePoints);
//изменнеие длины рта
//NORM_POINTS 48 54
//echo $FaceData_['normmask'][0][48][X];

        for($i = 0; $i < count($FaceData_['normmask']); $i++)
        {
            $x = $FacePoints[48][0][0] - $FaceData_['normmask'][$i][48]['X'];


            $FaceData["mouth"]["left_corner_mouth"][$i]["MovmentForce"] =
                self::getForce($FacePoints[48][0][3],$x);

//   echo $FacePoints[48][0][0].'-'.$FaceData_['normmask'][$i][48]['X'].'='.
//    $x.' scale='.$FacePoints[48][0][3].' force='.
//	 $FaceData["mouth"]["left_corner_mouth"][$i]["MovmentForce"].'<br>';

            if ($FaceData["mouth"]["left_corner_mouth"][$i]["MovmentForce"] == 0) {
                $FaceData["mouth"]["left_corner_mouth"][$i]["MovmentDirection"]='none';
            } else {
                if ($x>0) {$FaceData["mouth"]["left_corner_mouth"][$i]["MovmentDirection"]='left';}
                else {$FaceData["mouth"]["left_corner_mouth"][$i]["MovmentDirection"]='right';}
            }

            $x = $FacePoints[54][0][0] - $FaceData_['normmask'][$i][54]['X'];

            $FaceData["mouth"]["right_corner_mouth"][$i]["MovmentForce"] =
                self::getForce($FacePoints[54][0][3], $x);

            if ($FaceData["mouth"]["right_corner_mouth"][$i]["MovmentForce"] == 0) {
                $FaceData["mouth"]["right_corner_mouth"][$i]["MovmentDirection"]='none';
            } else {
                if ($x<0) {$FaceData["mouth"]["right_corner_mouth"][$i]["MovmentDirection"]='left';}
                else {$FaceData["mouth"]["right_corner_mouth"][$i]["MovmentDirection"]='right';}
            }
        }

//изменение ширины рта
//NORM_POINTS 51 57
        for($i = 0; $i < count($FaceData_['normmask']); $i++)
        {
            $y = $FacePoints[51][1][0] - $FaceData_['normmask'][$i][51]['Y'];


            $FaceData["mouth"]["mouth_upper_lip_outer_center"][$i]["MovmentForce"] =
                self::getForce($FacePoints[51][1][3],$y);

            if ($FaceData["mouth"]["mouth_upper_lip_outer_center"][$i]["MovmentForce"] == 0) {
                $FaceData["mouth"]["mouth_upper_lip_outer_center"][$i]["MovmentDirection"]='none';
            } else {
                if ($y>0) {$FaceData["mouth"]["mouth_upper_lip_outer_center"][$i]["MovmentDirection"]='up';}
                else {$FaceData["mouth"]["mouth_upper_lip_outer_center"][$i]["MovmentDirection"]='down';}
            }

            $y = $FacePoints[57][1][0] - $FaceData_['normmask'][$i][57]['Y'];

            $FaceData["mouth"]["mouth_lower_lip_outer_center"][$i]["MovmentForce"] =
                self::getForce($FacePoints[57][0][3], $y);

            if ($FaceData["mouth"]["mouth_lower_lip_outer_center"][$i]["MovmentForce"] == 0) {
                $FaceData["mouth"]["mouth_lower_lip_outer_center"][$i]["MovmentDirection"]='none';
            } else {
                if ($y<0) {$FaceData["mouth"]["mouth_lower_lip_outer_center"][$i]["MovmentDirection"]='down';}
                else {$FaceData["mouth"]["mouth_lower_lip_outer_center"][$i]["MovmentDirection"]='up';}
            }
        }

//определение формы рта
//NORM_POINTS 61 62 63 65 66 67


//движение уголков рта
//NORM_POINTS 48 54
        return $FaceData;
    }
}