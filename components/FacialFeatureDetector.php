<?php

namespace app\components;

use stdClass;

/**
 * FacialFeatureDetector - класс обнаружения лицевых признаков.
 */
class FacialFeatureDetector
{
    /** определение интенсивности проявления признака
     * @param $val1 - диапазон значений
     * @param $val2 - текущее значение
     * @return float|int - интенсивность проявления по относительной шкале от 1 до 100%
     */
    public function getForce($val1, $val2)
    {
        if($val1 != 0) $res = abs(round((100*$val2 / $val1)));
         else $res = 0;
        return $res;
    }

     /**
     * Вычисление максимального значения характеристики лица за время наблюдений.
     *
     * @param $facialCharacteristics - массив с характеристикой лица
     * @param $key - название характеристики
     * @return array|bool - возвращаемое значение
     */
    public function getFaceDataMaxForKeyV2($facialCharacteristics, $pointNum, $key)
    {
        $facialCharacteristicsNumber = count($facialCharacteristics);
        if ($facialCharacteristicsNumber <= 0)
            return false;
        $max = -1000;
        if (isset($facialCharacteristics[0][$pointNum][$key]))
            $max = $facialCharacteristics[0][$pointNum][$key];
        $maxFrame = 0;
        for ($i = 0; $i < $facialCharacteristicsNumber; $i++)
            if (isset($facialCharacteristics[$i][$pointNum]) && isset($facialCharacteristics[$i][$pointNum][$key]))
                if ($facialCharacteristics[$i][$pointNum][$key] > $max) {
                    $max = $facialCharacteristics[$i][$pointNum][$key];
                }

        return $max;
    }

    /**
     * Вычисление максимального значения характеристики относительно определенных точек.
     *
     * @param $facialCharacteristics - массив с характеристикой лица
     * @param $key - название характеристики
     * @return array|bool - возвращаемое значение
     */
    public function getFaceDataMaxOnPoints($facialCharacteristics, $pointNum, $key, $point1, $point2)
    {
        $facialCharacteristicsNumber = count($facialCharacteristics);
        if ($facialCharacteristicsNumber <= 0)
            return false;
        $max = -1000;
        for ($i = 0; $i < $facialCharacteristicsNumber; $i++)
            if (isset($facialCharacteristics[$i][$pointNum]) && isset($facialCharacteristics[$i][$pointNum][$key])
                && isset($facialCharacteristics[$i][$point1][$key])
                && isset($facialCharacteristics[$i][$point2][$key])) {
                $mid = round(($facialCharacteristics[$i][$point2][$key] -
                            $facialCharacteristics[$i][$point1][$key])/2) +
                            $facialCharacteristics[$i][$point1][$key];
                $relPointValue = abs($facialCharacteristics[$i][$pointNum][$key] - $mid);
                if ($relPointValue > $max) {
                    $max = $relPointValue;
                }
            }
        return $max;
    }

    /**
     * Вычисление минимального значения характеристики относительно определенных точек.
     *
     * @param $facialCharacteristics - массив с характеристикой лица
     * @param $key - название характеристики
     * @return array|bool - возвращаемое значение
     */
    public function getFaceDataMinOnPoints($facialCharacteristics, $pointNum, $key, $point1, $point2)
    {
        $facialCharacteristicsNumber = count($facialCharacteristics);
        if ($facialCharacteristicsNumber <= 0)
            return false;
        $min = 1000;
        for ($i = 0; $i < $facialCharacteristicsNumber; $i++)
            if (isset($facialCharacteristics[$i][$pointNum]) && isset($facialCharacteristics[$i][$pointNum][$key])
                && isset($facialCharacteristics[$i][$point1][$key])
                && isset($facialCharacteristics[$i][$point2][$key])) {
                $mid6167 = round(($facialCharacteristics[$i][$point2][$key] -
                            $facialCharacteristics[$i][$point1][$key])/2) +
                    $facialCharacteristics[$i][$point1][$key];
                $relPointValue = abs($facialCharacteristics[$i][$pointNum][$key] - $mid6167);
                if ($relPointValue < $min) {
                    $min = $relPointValue;
                }
            }
        return $min;
    }

    /**
     * Вычисление минимального значения характеристики лица за время наблюдений.
     *
     * @param $facialCharacteristics - массив с характеристикой лица
     * @param $key - название характеристики
     * @return array|bool - возвращаемое значение
     */
    public function getFaceDataMinForKeyV2($facialCharacteristics, $pointNum, $key)
    {
        $facialCharacteristicsNumber = count($facialCharacteristics);
        if ($facialCharacteristicsNumber <= 0)
            return false;
        $min = 1000;
        if (isset($facialCharacteristics[0][$pointNum][$key]))
            $min = $facialCharacteristics[0][$pointNum][$key];

        for ($i = 0; $i < $facialCharacteristicsNumber; $i++)
            if (isset($facialCharacteristics[$i][$pointNum]) && isset($facialCharacteristics[$i][$pointNum][$key]))
                if ($facialCharacteristics[$i][$pointNum][$key] < $min) {
                    $min = $facialCharacteristics[$i][$pointNum][$key];
                }
        return $min;
    }

    public function getFaceDataMaxForKeyV3($facialCharacteristics, $pointNum, $key)
    {
        $facialCharacteristicsNumber = 0;
        if (is_array($facialCharacteristics))
            $facialCharacteristicsNumber = count($facialCharacteristics);
        if ($facialCharacteristicsNumber <= 0)
            return false;

        $max = 0;
        if (isset($facialCharacteristics[$pointNum]))
            $max = $facialCharacteristics[$pointNum][0][$key];
 //       $minFrame = 0;

        for ($i = 0; $i <= $facialCharacteristicsNumber; $i++)
            if (isset($facialCharacteristics[$pointNum][$i]) && isset($facialCharacteristics[$pointNum][$i][$key]))
                if ($facialCharacteristics[$pointNum][$i][$key] > $max) {
                    $max = $facialCharacteristics[$pointNum][$i][$key];
//                    $minFrame = $i;
                }

//        return array(0 => $min, 1 => $minFrame);
        return $max;
    }

    /**
     * Обнаружение признаков глаза по абсолютным координатам
     *
     * @param $faceData - входной массив с лицевыми точками (landmarks)
     * @return mixed - выходной массив с обработанным массивом для глаза
     */
    public function detectEyeFeaturesByAbsCoordinates($sourceFaceData, $facePart)
    {
        //Анализируемые точки: левый глаз – 36-41
        // (верхнее веко – 36-37-38-39, нижнее веко – 39-40-41-36, левый зрачок - ???),
        // правый глаз – 42-47 (верхнее веко – 42-43-44-45, нижнее веко – 45-46-47-42, правый зрачок - ???).
        //ширина глаза. для левого расстояние между точками 37 и 41, для правого- 43 и 47

        //Верхнее веко, движение верхнего века (вверх, вниз)
        //для левого глаза 37 38
        //для правого глаза 43 44

        //относительно середины между внутренними уголками глаз - точками 39 и 42

        if (isset($sourceFaceData[0][37])
            && isset($sourceFaceData[0][41])
            && isset($sourceFaceData[0][43])
            && isset($sourceFaceData[0][47])
            && isset($sourceFaceData[0][44])
            && isset($sourceFaceData[0][46])
            && isset($sourceFaceData[0][39])
            && isset($sourceFaceData[0][42])
            && isset($sourceFaceData[0][36])
            && isset($sourceFaceData[0][45])
        ) {

            $facePart = $facePart.'_by_abs';

            $yN37 = $sourceFaceData[0][37]['Y'];
            $yN41 = $sourceFaceData[0][41]['Y'];
            $leftEyeWidthN = $yN41 - $yN37;
            $yN43 = $sourceFaceData[0][43]['Y'];
            $yN47 = $sourceFaceData[0][47]['Y'];
            $rightEyeWidthN = $yN47 - $yN43;
            //38 и 40 для левого глаза, для правого - 44 и 46
            $yN38 = $sourceFaceData[0][38]['Y'];
            $yN40 = $sourceFaceData[0][40]['Y'];
            $leftEyeWidthN2 = $yN40 - $yN38;
            $yN44 = $sourceFaceData[0][44]['Y'];
            $yN46 = $sourceFaceData[0][46]['Y'];
            $rightEyeWidthN2 = $yN46 - $yN44;

            $xN39 = $sourceFaceData[0][39]['X'];
            $xN42 = $sourceFaceData[0][42]['X'];
            $xN36 = $sourceFaceData[0][36]['X'];
            $yN39 = $sourceFaceData[0][39]['Y'];
            $yN42 = $sourceFaceData[0][42]['Y'];
            $yN36 = $sourceFaceData[0][36]['Y'];
            $yN45 = $sourceFaceData[0][45]['Y'];
            $xN45 = $sourceFaceData[0][45]['X'];

            //100% - круг с диаметром длиной отрезка, соединяющего внешнюю и внутреннюю точки глаза * 65%
            $leftEyeWidthMaxByCircle = ($xN39 - $xN36)*0.65;
            //min - это нормальное положение
            $leftEyeWidthScaleByCircle = $leftEyeWidthMaxByCircle - $leftEyeWidthN;
            //100% - круг с диаметром длиной отрезка, соединяющего внешнюю и внутреннюю точки глаза * 65%
            $rightEyeWidthMaxByCircle = ($xN45 - $xN42)*0.65;
            //min - это нормальное положение
            $rightEyeWidthScaleByCircle = $rightEyeWidthMaxByCircle - $rightEyeWidthN;

//            $maxY37 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 37, "Y");
//            $minY37 = $this->getFaceDataMinForKeyV2($sourceFaceData, 37, "Y");
//            $scaleY37 = $maxY37 - $minY37;
//            $maxY43 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 43, "Y");
//            $minY43 = $this->getFaceDataMinForKeyV2($sourceFaceData, 43, "Y");
//            $scaleY43 = $maxY43 - $minY43;
//            $maxY41 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 41, "Y");
//            $minY41 = $this->getFaceDataMinForKeyV2($sourceFaceData, 41, "Y");
//            $scaleY41 = $maxY41 - $minY41;
//            $maxLeftEyeWidth = $maxY41 - $minY37;
//            $maxY47 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 47, "Y");
//            $minY47 = $this->getFaceDataMinForKeyV2($sourceFaceData, 47, "Y");
//            $scaleY47 = $maxY47 - $minY47;
//            $maxRightEyeWidth = $maxY47 - $minY43;
 //           //38 и 40 для левого глаза, для правого - 44 и 46
//            $maxY38 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 38, "Y");
//            $minY38 = $this->getFaceDataMinForKeyV2($sourceFaceData, 38, "Y");
/*            $scaleY38 = $maxY38 - $minY38;
            $maxY44 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 44, "Y");
            $minY44 = $this->getFaceDataMinForKeyV2($sourceFaceData, 44, "Y");
            $scaleY44 = $maxY44 - $minY44;
            $maxY40 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 40, "Y");
            $minY40 = $this->getFaceDataMinForKeyV2($sourceFaceData, 40, "Y");
            $scaleY40 = $maxY40 - $minY40;
            $maxLeftEyeWidth2 = $maxY40 - $minY38;
            $maxY46 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 46, "Y");
            $minY46 = $this->getFaceDataMinForKeyV2($sourceFaceData, 46, "Y");
            $scaleY46 = $maxY46 - $minY46;
            $maxRightEyeWidth2 = $maxY46 - $minY44;

            $maxX39 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 39, "X");
            $minX39 = $this->getFaceDataMinForKeyV2($sourceFaceData, 39, "X");
            $scaleX39 = $maxX39 - $minX39;
//            echo '$maxX39:'.$maxX39.' $minX39:'.$minX39.' $scaleX39:'.$scaleX39.'<br>';

            $maxX42 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 42, "X");
            $minX42 = $this->getFaceDataMinForKeyV2($sourceFaceData, 42, "X");
            $scaleX42 = $maxX42 - $minX42;
            $maxY39 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 39, "Y");
            $minY39 = $this->getFaceDataMinForKeyV2($sourceFaceData, 39, "Y");
            $scaleY39 = $maxY39 - $minY39;
            $maxY42 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 42, "Y");
            $minY42 = $this->getFaceDataMinForKeyV2($sourceFaceData, 42, "Y");
            $scaleY42 = $maxY42 - $minY42;
            $maxY36 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 36, "Y");
            $minY36 = $this->getFaceDataMinForKeyV2($sourceFaceData, 36, "Y");
            $scaleY36 = $maxY36 - $minY36;
            $maxY45 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 45, "Y");
            $minY45 = $this->getFaceDataMinForKeyV2($sourceFaceData, 45, "Y");
            $scaleY45 = $maxY45 - $minY45;*/

            for ($i = 0; $i < count($sourceFaceData); $i++) {
                //----------------------------------------------------------------------------------------
                //Верхнее веко, движение верхнего века (вверх, вниз)
                //left_eye_upper_eyelid_movement
                if (isset($sourceFaceData[$i][38]))
                    $leftEyeUpperEyelidH = $sourceFaceData[$i][38]['Y'] - $yN38;
                if (isset($sourceFaceData[$i][43]))
                    $rightEyeUpperEyelidH = $sourceFaceData[$i][43]['Y'] - $yN43;

                $targetFaceData[$facePart]['VALUES_REL']["left_eye_upper_eyelid_movement"]["max"] = round($leftEyeWidthMaxByCircle/2);
                $targetFaceData[$facePart]['VALUES_REL']["left_eye_upper_eyelid_movement"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_upper_eyelid_movement"]["max"] = round($rightEyeWidthMaxByCircle/2);
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_upper_eyelid_movement"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["left_eye_upper_eyelid_movement"][$i]["delta"] = $leftEyeUpperEyelidH;
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_upper_eyelid_movement"][$i]["delta"] = $rightEyeUpperEyelidH;

                $targetFaceData[$facePart]["left_eye_upper_eyelid_movement"][$i]["force"] = $this->getForce(
                    round($leftEyeWidthMaxByCircle/2), abs($leftEyeUpperEyelidH)
                );
                $targetFaceData[$facePart]["right_eye_upper_eyelid_movement"][$i]["force"] = $this->getForce(
                    round($rightEyeWidthMaxByCircle/2), abs($rightEyeUpperEyelidH)
                );

                if ($leftEyeUpperEyelidH < 0) $targetFaceData[$facePart]["left_eye_upper_eyelid_movement"][$i]["val"] = 'up';
                if ($leftEyeUpperEyelidH > 0) $targetFaceData[$facePart]["left_eye_upper_eyelid_movement"][$i]["val"] = 'down';
                if ($leftEyeUpperEyelidH == 0) {
                    $targetFaceData[$facePart]["left_eye_upper_eyelid_movement"][$i]["force"] = 0;
                    $targetFaceData[$facePart]["left_eye_upper_eyelid_movement"][$i]["val"] = 'none';
                }
                if ($rightEyeUpperEyelidH < 0) $targetFaceData[$facePart]["right_eye_upper_eyelid_movement"][$i]["val"] = 'up';
                if ($rightEyeUpperEyelidH > 0) $targetFaceData[$facePart]["right_eye_upper_eyelid_movement"][$i]["val"] = 'down';
                if ($rightEyeUpperEyelidH == 0) {
                    $targetFaceData[$facePart]["right_eye_upper_eyelid_movement"][$i]["force"] = 0;
                    $targetFaceData[$facePart]["right_eye_upper_eyelid_movement"][$i]["val"] = 'none';
                }
                //------------------------------------------------------------------------------------------------
                //Нижнее веко, движение нижнего века (без движения, вверх, вниз, к центру и вверх)
                //left_eye_lower_eyelid_movement
                if (isset($sourceFaceData[$i][40]))
                    $leftEyeLowerEyelidH = $sourceFaceData[$i][40]['Y'] - $yN40;
                if (isset($sourceFaceData[$i][47]))
                    $rightEyeLowerEyelidH = $sourceFaceData[$i][47]['Y'] - $yN47;
                if (isset($sourceFaceData[$i][39]))
                    $leftEyeInnerCorner = $sourceFaceData[$i][39]['X'] - $xN39;
                if (isset($sourceFaceData[$i][42]))
                    $rightEyeInnerCorner = $sourceFaceData[$i][42]['X'] - $xN42;
                $leftEyeInnerCornerForce = $this->getForce(round($leftEyeWidthMaxByCircle/4), abs($leftEyeInnerCorner));
                $rightEyeInnerCornerForce = $this->getForce(round($rightEyeWidthMaxByCircle/4), abs($rightEyeInnerCorner));

                $targetFaceData[$facePart]["left_eye_lower_eyelid_movement_y"][$i]["force"] = $this->getForce(
                    round($leftEyeWidthMaxByCircle/2), abs($leftEyeLowerEyelidH)
                );
                $targetFaceData[$facePart]["right_eye_lower_eyelid_movement_y"][$i]["force"] = $this->getForce(
                    round($rightEyeWidthMaxByCircle/2), abs($rightEyeLowerEyelidH)
                );
                $targetFaceData[$facePart]['VALUES_REL']["left_eye_lower_eyelid_movement_y"]["max"] = round($leftEyeWidthMaxByCircle/2);
                $targetFaceData[$facePart]['VALUES_REL']["left_eye_lower_eyelid_movement_y"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["left_eye_lower_eyelid_movement_y"][$i]["delta"] = $leftEyeLowerEyelidH;
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_lower_eyelid_movement_y"]["max"] = round($leftEyeWidthMaxByCircle/2);
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_lower_eyelid_movement_y"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_lower_eyelid_movement_y"][$i]["delta"] =  $rightEyeLowerEyelidH;

                if ($leftEyeLowerEyelidH < 0) $targetFaceData[$facePart]["left_eye_lower_eyelid_movement_y"][$i]["val"] = 'up';
                if ($leftEyeLowerEyelidH > 0) $targetFaceData[$facePart]["left_eye_lower_eyelid_movement_y"][$i]["val"] = 'down';
                if ($leftEyeLowerEyelidH == 0) {
                    $targetFaceData[$facePart]["left_eye_lower_eyelid_movement_y"][$i]["force"] = 0;
                    $targetFaceData[$facePart]["left_eye_lower_eyelid_movement_y"][$i]["val"] = 'none';
                }
                $targetFaceData[$facePart]["left_eye_lower_eyelid_movement_x"][$i]["force"] = $leftEyeInnerCornerForce;
                if ($leftEyeInnerCorner > 0) $targetFaceData[$facePart]["left_eye_lower_eyelid_movement_x"][$i]["val"] = 'to center';
                if ($leftEyeInnerCorner < 0) $targetFaceData[$facePart]["left_eye_lower_eyelid_movement_x"][$i]["val"] = 'from center';
                if ($leftEyeInnerCorner == 0) $targetFaceData[$facePart]["left_eye_lower_eyelid_movement_x"][$i]["val"] = 'none';

                if ($rightEyeLowerEyelidH < 0) $targetFaceData[$facePart]["right_eye_lower_eyelid_movement_y"][$i]["val"] = 'up';
                if ($rightEyeLowerEyelidH > 0) $targetFaceData[$facePart]["right_eye_lower_eyelid_movement_y"][$i]["val"] = 'down';
                if ($rightEyeLowerEyelidH == 0) {
                    $targetFaceData[$facePart]["right_eye_lower_eyelid_movement_y"][$i]["force"] = 0;
                    $targetFaceData[$facePart]["right_eye_lower_eyelid_movement_y"][$i]["val"] = 'none';
                }
                $targetFaceData[$facePart]["right_eye_lower_eyelid_movement_x"][$i]["force"] = $rightEyeInnerCornerForce;
                if ($rightEyeInnerCorner < 0) $targetFaceData[$facePart]["right_eye_lower_eyelid_movement_x"][$i]["val"] = 'to center';
                if ($rightEyeInnerCorner > 0) $targetFaceData[$facePart]["right_eye_lower_eyelid_movement_x"][$i]["val"] = 'from center';
                if ($rightEyeInnerCorner == 0) $targetFaceData[$facePart]["right_eye_lower_eyelid_movement_x"][$i]["val"] = 'none';

                $targetFaceData[$facePart]['VALUES_REL']["left_eye_lower_eyelid_movement_x"]["max"] = round($leftEyeWidthMaxByCircle/4);
                $targetFaceData[$facePart]['VALUES_REL']["left_eye_lower_eyelid_movement_x"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["left_eye_lower_eyelid_movement_x"][$i]["delta"] = $leftEyeInnerCorner;
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_lower_eyelid_movement_x"]["max"] = round($rightEyeWidthMaxByCircle/4);
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_lower_eyelid_movement_x"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_lower_eyelid_movement_x"][$i]["delta"] = $rightEyeInnerCorner;
                //------------------------------------------------------------------------------------------------
                //width, расстояние между 37 и 41 для левого глаза, для правого - 43 и 47
                if (isset($sourceFaceData[$i][37]) &&
                    isset($sourceFaceData[$i][41]) &&
                    isset($sourceFaceData[$i][43]) &&
                    isset($sourceFaceData[$i][47])) {
                    $leftEyeWidth = $sourceFaceData[$i][41]['Y'] - $sourceFaceData[$i][37]['Y'];
                    $rightEyeWidth = $sourceFaceData[$i][47]['Y'] - $sourceFaceData[$i][43]['Y'];

                    $targetFaceData[$facePart]["left_eye_width"][$i]["force"] = $this->getForce(
                        $leftEyeWidthScaleByCircle, abs($leftEyeWidth - $leftEyeWidthN));
                    $targetFaceData[$facePart]["left_eye_width"][$i]["val"] = $leftEyeWidth;

                    $targetFaceData[$facePart]["right_eye_width"][$i]["force"] = $this->getForce(
                        $rightEyeWidthScaleByCircle, abs($rightEyeWidth - $rightEyeWidthN));
                    $targetFaceData[$facePart]["right_eye_width"][$i]["val"] = $rightEyeWidth;

                    $targetFaceData[$facePart]['VALUES_REL']["left_eye_width"]["max"] = $leftEyeWidthMaxByCircle;
                    $targetFaceData[$facePart]['VALUES_REL']["left_eye_width"]["min"] = $leftEyeWidthN;
                    $targetFaceData[$facePart]['VALUES_REL']["left_eye_width"][$i]["delta"] = $leftEyeWidth - $leftEyeWidthN;
                    $targetFaceData[$facePart]['VALUES_REL']["left_eye_width"][$i]["val"] = $leftEyeWidth;
                    $targetFaceData[$facePart]['VALUES_REL']["right_eye_width"]["max"] = $rightEyeWidthMaxByCircle;
                    $targetFaceData[$facePart]['VALUES_REL']["right_eye_width"]["min"] = $rightEyeWidthN;
                    $targetFaceData[$facePart]['VALUES_REL']["right_eye_width"][$i]["delta"] = $rightEyeWidth - $rightEyeWidthN;
                    $targetFaceData[$facePart]['VALUES_REL']["right_eye_width"][$i]["val"] = $rightEyeWidth;

                    //альтернативно: width, расстояние между 38 и 40 для левого глаза, для правого - 44 и 46
                    $leftEyeWidth2 = $sourceFaceData[$i][40]['Y'] - $sourceFaceData[$i][38]['Y'];
                    $rightEyeWidth2 = $sourceFaceData[$i][46]['Y'] - $sourceFaceData[$i][44]['Y'];

                    $targetFaceData[$facePart]["left_eye_width2"][$i]["force"] = $this->getForce(
                        $leftEyeWidthMaxByCircle, abs($leftEyeWidth2 - $leftEyeWidthN2));

                    $targetFaceData[$facePart]["left_eye_width2"][$i]["val"] = $leftEyeWidth2;

                    $targetFaceData[$facePart]["right_eye_width2"][$i]["force"] = $this->getForce(
                        $rightEyeWidthMaxByCircle, abs($rightEyeWidth2 - $rightEyeWidthN2));
                    $targetFaceData[$facePart]["right_eye_width2"][$i]["val"] = $rightEyeWidth2;

                    //Глаза, ширина глаз (увеличение, уменьшение) через изменение ширины
                    $targetFaceData[$facePart]["left_eye_width_changing"][$i]["force"] =
                        $targetFaceData[$facePart]["left_eye_width"][$i]["force"];
                    $targetFaceData[$facePart]["left_eye_width_changing"][$i]["val"] = 'none';
                    if ($targetFaceData[$facePart]["left_eye_width"][$i]["val"] > $leftEyeWidthN)
                        $targetFaceData[$facePart]["left_eye_width_changing"][$i]["val"] = '+';
                    if ($targetFaceData[$facePart]["left_eye_width"][$i]["val"] < $leftEyeWidthN)
                        $targetFaceData[$facePart]["left_eye_width_changing"][$i]["val"] = '-';

                    $targetFaceData[$facePart]["right_eye_width_changing"][$i]["force"] =
                        $targetFaceData[$facePart]["right_eye_width"][$i]["force"];
                    $targetFaceData[$facePart]["right_eye_width_changing"][$i]["val"] = 'none';
                    if ($targetFaceData[$facePart]["right_eye_width"][$i]["val"] > $rightEyeWidthN)
                        $targetFaceData[$facePart]["right_eye_width_changing"][$i]["val"] = '+';
                    if ($targetFaceData[$facePart]["right_eye_width"][$i]["val"] < $rightEyeWidthN)
                        $targetFaceData[$facePart]["right_eye_width_changing"][$i]["val"] = '-';
                }
                //------------------------------------------------------------------------------------------------
                //Внешний уголок глаза, движение внешнего уголка глаза (вверх, вниз)
                if (isset($sourceFaceData[$i][36]))
                    $leftEyeOuterCornerH = $sourceFaceData[$i][36]['Y'] - $yN36;
                if (isset($sourceFaceData[$i][45]))
                    $rightEyeOuterCornerH = $sourceFaceData[$i][45]['Y'] - $yN45;

                $targetFaceData[$facePart]["left_eye_outer_movement"][$i]["force"] = $this->getForce(
                    round($leftEyeWidthMaxByCircle/2), abs($leftEyeOuterCornerH));
                $targetFaceData[$facePart]["right_eye_outer_movement"][$i]["force"] = $this->getForce(
                    round($rightEyeWidthMaxByCircle/2), abs($rightEyeOuterCornerH));

                if ($leftEyeOuterCornerH < 0) $targetFaceData[$facePart]["left_eye_outer_movement"][$i]["val"] = 'up';
                if ($leftEyeOuterCornerH > 0) $targetFaceData[$facePart]["left_eye_outer_movement"][$i]["val"] = 'down';
                if ($leftEyeOuterCornerH == 0) {
                    $targetFaceData[$facePart]["left_eye_outer_movement"][$i]["force"] = 0;
                    $targetFaceData[$facePart]["left_eye_outer_movement"][$i]["val"] = 'none';
                }
                if ($rightEyeOuterCornerH < 0) $targetFaceData[$facePart]["right_eye_outer_movement"][$i]["val"] = 'up';
                if ($rightEyeOuterCornerH > 0) $targetFaceData[$facePart]["right_eye_outer_movement"][$i]["val"] = 'down';
                if ($rightEyeOuterCornerH == 0) {
                    $targetFaceData[$facePart]["right_eye_outer_movement"][$i]["force"] = 0;
                    $targetFaceData[$facePart]["right_eye_outer_movement"][$i]["val"] = 'none';
                }

                $targetFaceData[$facePart]['VALUES_REL']["left_eye_outer_movement"]["max"] = round($leftEyeWidthMaxByCircle/2);
                $targetFaceData[$facePart]['VALUES_REL']["left_eye_outer_movement"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["left_eye_outer_movement"][$i]["delta"] = $leftEyeOuterCornerH;
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_outer_movement"]["max"] = round($rightEyeWidthMaxByCircle/2);
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_outer_movement"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_outer_movement"][$i]["delta"] = $rightEyeOuterCornerH;
                //------------------------------------------------------------------------------------------------
                //Внутренний уголок глаза, движение внутреннего уголка глаза (вверх, вниз)
                if (isset($sourceFaceData[$i][39]))
                    $leftEyeInnerCornerH = $sourceFaceData[$i][39]['Y'] - $yN39;
                if (isset($sourceFaceData[$i][42]))
                    $rightEyeInnerCornerH = $sourceFaceData[$i][42]['Y'] - $yN42;

                $targetFaceData[$facePart]["left_eye_inner_movement"][$i]["force"] = $this->getForce(
                    round($leftEyeWidthMaxByCircle/2), abs($leftEyeInnerCornerH));
                $targetFaceData[$facePart]["right_eye_inner_movement"][$i]["force"] = $this->getForce(
                    round($rightEyeWidthMaxByCircle/2), abs($rightEyeInnerCornerH));

                if ($leftEyeInnerCornerH < 0) $targetFaceData[$facePart]["left_eye_inner_movement"][$i]["val"] = 'up';
                if ($leftEyeInnerCornerH > 0) $targetFaceData[$facePart]["left_eye_inner_movement"][$i]["val"] = 'down';
                if ($leftEyeInnerCornerH == 0) {
                    $targetFaceData[$facePart]["left_eye_inner_movement"][$i]["force"] = 0;
                    $targetFaceData[$facePart]["left_eye_inner_movement"][$i]["val"] = 'none';
                }
                if ($rightEyeInnerCornerH < 0) $targetFaceData[$facePart]["right_eye_inner_movement"][$i]["val"] = 'up';
                if ($rightEyeInnerCornerH > 0) $targetFaceData[$facePart]["right_eye_inner_movement"][$i]["val"] = 'down';
                if ($rightEyeInnerCornerH == 0) {
                    $targetFaceData[$facePart]["right_eye_inner_movement"][$i]["force"] = 0;
                    $targetFaceData[$facePart]["right_eye_inner_movement"][$i]["val"] = 'none';
                }
                $targetFaceData[$facePart]['VALUES_REL']["left_eye_inner_movement"]["max"] = round($leftEyeWidthMaxByCircle/2);
                $targetFaceData[$facePart]['VALUES_REL']["left_eye_inner_movement"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["left_eye_inner_movement"][$i]["delta"] = $leftEyeInnerCornerH;
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_inner_movement"]["max"] = round($rightEyeWidthMaxByCircle/2);
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_inner_movement"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_inner_movement"][$i]["delta"] = $rightEyeInnerCornerH;
                //------------------------------------------------------------------------------------------------
            }
            return $targetFaceData[$facePart];
        } else return false;
    }

    /**
     * Обнаружение признаков глаза.
     *
     * @param $faceData - входной массив с лицевыми точками (landmarks)
     * @return mixed - выходной массив с обработанным массивом для глаза
     */
    public function detectEyeFeatures($sourceFaceData, $facePart, $point1,$point2)
    {
       //Анализируемые точки: левый глаз – 36-41
       // (верхнее веко – 36-37-38-39, нижнее веко – 39-40-41-36, левый зрачок - ???),
       // правый глаз – 42-47 (верхнее веко – 42-43-44-45, нижнее веко – 45-46-47-42, правый зрачок - ???).
        //ширина глаза. для левого расстояние между точками 37 и 41, для правого- 43 и 47

        //Верхнее веко, движение верхнего века (вверх, вниз)
        //для левого глаза 37 38
        //для правого глаза 43 44

        //относительно середины между внутренними уголками глаз - точками 39 и 42

        if (isset($sourceFaceData[0][37])
            && isset($sourceFaceData[0][41])
            && isset($sourceFaceData[0][43])
            && isset($sourceFaceData[0][47])
            && isset($sourceFaceData[0][44])
            && isset($sourceFaceData[0][46])
            && isset($sourceFaceData[0][39])
            && isset($sourceFaceData[0][42])
            && isset($sourceFaceData[0][36])
            && isset($sourceFaceData[0][45])
        ) {
             $log = '';
            $midNY3942 = round(($sourceFaceData[0][$point2]['Y'] - $sourceFaceData[0][$point1]['Y'])/2) +
                $sourceFaceData[0][$point1]['Y'];
            $midNX3942 = round(($sourceFaceData[0][$point2]['X'] - $sourceFaceData[0][$point1]['X'])/2) +
                $sourceFaceData[0][$point1]['X'] ;

            $yN37 = $sourceFaceData[0][37]['Y'] - $midNY3942;
            $yN41 = $sourceFaceData[0][41]['Y'] - $midNY3942;
            $leftEyeWidthN = $yN41 - $yN37;
            $yN43 = $sourceFaceData[0][43]['Y'] - $midNY3942;
            $yN47 = $sourceFaceData[0][47]['Y'] - $midNY3942;
            $rightEyeWidthN = $yN47 - $yN43;
            //38 и 40 для левого глаза, для правого - 44 и 46
            $yN38 = $sourceFaceData[0][38]['Y'] - $midNY3942;
            $yN40 = $sourceFaceData[0][40]['Y'] - $midNY3942;
            $leftEyeWidthN2 = $yN40 - $yN38;
            $yN44 = $sourceFaceData[0][44]['Y'] - $midNY3942;
            $yN46 = $sourceFaceData[0][46]['Y'] - $midNY3942;
            $rightEyeWidthN2 = $yN46 - $yN44;

            $xN39 = $sourceFaceData[0][39]['X'] - $midNX3942;
            $xN42 = $sourceFaceData[0][42]['X'] - $midNX3942;
            $xN36 = $sourceFaceData[0][36]['X'] - $midNX3942;
            $yN39 = $sourceFaceData[0][39]['Y'] - $midNY3942;
            $yN42 = $sourceFaceData[0][42]['Y'] - $midNY3942;
            $yN36 = $sourceFaceData[0][36]['Y'] - $midNY3942;
            $yN45 = $sourceFaceData[0][45]['Y'] - $midNY3942;
            $xN45 = $sourceFaceData[0][45]['X'] - $midNX3942;

            //100% - круг с диаметром длиной отрезка, соединяющего внешнюю и внутреннюю точки глаза * 65%
            $leftEyeWidthMaxByCircle = ($xN39 - $xN36)*0.65;
            //min - это нормальное положение
            $leftEyeWidthScaleByCircle = $leftEyeWidthMaxByCircle - $leftEyeWidthN;
            //100% - круг с диаметром длиной отрезка, соединяющего внешнюю и внутреннюю точки глаза * 65%
            $rightEyeWidthMaxByCircle = ($xN45 - $xN42)*0.65;
            //min - это нормальное положение
            $rightEyeWidthScaleByCircle = $rightEyeWidthMaxByCircle - $rightEyeWidthN;

            for ($i = 0; $i < count($sourceFaceData); $i++) {
                if (isset($sourceFaceData[$i][$point2]) && isset($sourceFaceData[$i][$point1])){
                $midY3942 = round(($sourceFaceData[$i][$point2]['Y'] - $sourceFaceData[$i][$point1]['Y'])/2) +
                    $sourceFaceData[$i][$point1]['Y'];
                $midX3942 = round(($sourceFaceData[$i][$point2]['X'] - $sourceFaceData[$i][$point1]['X'])/2) +
                    $sourceFaceData[$i][$point1]['X'];
                }
                //----------------------------------------------------------------------------------------
                //Верхнее веко, движение верхнего века (вверх, вниз)
                //left_eye_upper_eyelid_movement
                if (isset($sourceFaceData[$i][38]))
                    $leftEyeUpperEyelidH = $sourceFaceData[$i][38]['Y'] - $yN38 - $midY3942;
                if (isset($sourceFaceData[$i][43]))
                    $rightEyeUpperEyelidH = $sourceFaceData[$i][43]['Y'] - $yN43 - $midY3942;

                $targetFaceData[$facePart]['VALUES_REL']["left_eye_upper_eyelid_movement"]["max"] = round($leftEyeWidthMaxByCircle/2);
                $targetFaceData[$facePart]['VALUES_REL']["left_eye_upper_eyelid_movement"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_upper_eyelid_movement"]["max"] = round($rightEyeWidthMaxByCircle/2);
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_upper_eyelid_movement"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["left_eye_upper_eyelid_movement"][$i]["delta"] = $leftEyeUpperEyelidH;
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_upper_eyelid_movement"][$i]["delta"] = $rightEyeUpperEyelidH;
                $targetFaceData[$facePart]['VALUES_REL']["left_eye_upper_eyelid_movement"][$i]["val"] = $sourceFaceData[$i][38]['Y'] - $midY3942;
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_upper_eyelid_movement"][$i]["val"] = $sourceFaceData[$i][43]['Y'] - $midY3942;

                $targetFaceData[$facePart]["left_eye_upper_eyelid_movement"][$i]["force"] = $this->getForce(
                    round($leftEyeWidthMaxByCircle/2), abs($leftEyeUpperEyelidH)
                );
                $targetFaceData[$facePart]["right_eye_upper_eyelid_movement"][$i]["force"] = $this->getForce(
                    round($rightEyeWidthMaxByCircle/2), abs($rightEyeUpperEyelidH)
                );

                if ($leftEyeUpperEyelidH < 0) $targetFaceData[$facePart]["left_eye_upper_eyelid_movement"][$i]["val"] = 'up';
                if ($leftEyeUpperEyelidH > 0) $targetFaceData[$facePart]["left_eye_upper_eyelid_movement"][$i]["val"] = 'down';
                if ($leftEyeUpperEyelidH == 0) {
                    $targetFaceData[$facePart]["left_eye_upper_eyelid_movement"][$i]["force"] = 0;
                    $targetFaceData[$facePart]["left_eye_upper_eyelid_movement"][$i]["val"] = 'none';
                }
                if ($rightEyeUpperEyelidH < 0) $targetFaceData[$facePart]["right_eye_upper_eyelid_movement"][$i]["val"] = 'up';
                if ($rightEyeUpperEyelidH > 0) $targetFaceData[$facePart]["right_eye_upper_eyelid_movement"][$i]["val"] = 'down';
                if ($rightEyeUpperEyelidH == 0) {
                    $targetFaceData[$facePart]["right_eye_upper_eyelid_movement"][$i]["force"] = 0;
                    $targetFaceData[$facePart]["right_eye_upper_eyelid_movement"][$i]["val"] = 'none';
                }
                //------------------------------------------------------------------------------------------------
                //Нижнее веко, движение нижнего века (без движения, вверх, вниз, к центру и вверх)
                //left_eye_lower_eyelid_movement
                if (isset($sourceFaceData[$i][40]))
                    $leftEyeLowerEyelidH = $sourceFaceData[$i][40]['Y'] - $yN40 - $midY3942;
                if (isset($sourceFaceData[$i][47]))
                    $rightEyeLowerEyelidH = $sourceFaceData[$i][47]['Y'] - $yN47 - $midY3942;
                if (isset($sourceFaceData[$i][39]))
                    $leftEyeInnerCorner = $sourceFaceData[$i][39]['X'] - $xN39 - $midX3942;
                if (isset($sourceFaceData[$i][42]))
                    $rightEyeInnerCorner = $sourceFaceData[$i][42]['X'] - $xN42 - $midX3942;

                $leftEyeInnerCornerForce = $this->getForce(round($leftEyeWidthMaxByCircle/4), abs($leftEyeInnerCorner));
                $rightEyeInnerCornerForce = $this->getForce(round($rightEyeWidthMaxByCircle/4), abs($rightEyeInnerCorner));

                $targetFaceData[$facePart]["left_eye_lower_eyelid_movement_y"][$i]["force"] = $this->getForce(
                    round($leftEyeWidthMaxByCircle/2), abs($leftEyeLowerEyelidH)
                );
                $targetFaceData[$facePart]["right_eye_lower_eyelid_movement_y"][$i]["force"] = $this->getForce(
                     round($rightEyeWidthMaxByCircle/2), abs($rightEyeLowerEyelidH)
                );
                $targetFaceData[$facePart]['VALUES_REL']["left_eye_lower_eyelid_movement_y"]["max"] = round($leftEyeWidthMaxByCircle/2);
                $targetFaceData[$facePart]['VALUES_REL']["left_eye_lower_eyelid_movement_y"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["left_eye_lower_eyelid_movement_y"][$i]["delta"] = $leftEyeLowerEyelidH;
                $targetFaceData[$facePart]['VALUES_REL']["left_eye_lower_eyelid_movement_y"][$i]["val"] = $sourceFaceData[$i][40]['Y'] - $midY3942;
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_lower_eyelid_movement_y"]["max"] = round($leftEyeWidthMaxByCircle/2);
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_lower_eyelid_movement_y"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_lower_eyelid_movement_y"][$i]["delta"] =  $rightEyeLowerEyelidH;
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_lower_eyelid_movement_y"][$i]["val"] =  $sourceFaceData[$i][47]['Y'] - $midY3942;

                if ($leftEyeLowerEyelidH < 0) $targetFaceData[$facePart]["left_eye_lower_eyelid_movement_y"][$i]["val"] = 'up';
                if ($leftEyeLowerEyelidH > 0) $targetFaceData[$facePart]["left_eye_lower_eyelid_movement_y"][$i]["val"] = 'down';
                if ($leftEyeLowerEyelidH == 0) {
                    $targetFaceData[$facePart]["left_eye_lower_eyelid_movement_y"][$i]["force"] = 0;
                    $targetFaceData[$facePart]["left_eye_lower_eyelid_movement_y"][$i]["val"] = 'none';
                }
                $targetFaceData[$facePart]["left_eye_lower_eyelid_movement_x"][$i]["force"] = $leftEyeInnerCornerForce;
                if ($leftEyeInnerCorner > 0) $targetFaceData[$facePart]["left_eye_lower_eyelid_movement_x"][$i]["val"] = 'to center';
                if ($leftEyeInnerCorner < 0) $targetFaceData[$facePart]["left_eye_lower_eyelid_movement_x"][$i]["val"] = 'from center';
                if ($leftEyeInnerCorner == 0) $targetFaceData[$facePart]["left_eye_lower_eyelid_movement_x"][$i]["val"] = 'none';

                if ($rightEyeLowerEyelidH < 0) $targetFaceData[$facePart]["right_eye_lower_eyelid_movement_y"][$i]["val"] = 'up';
                if ($rightEyeLowerEyelidH > 0) $targetFaceData[$facePart]["right_eye_lower_eyelid_movement_y"][$i]["val"] = 'down';
                if ($rightEyeLowerEyelidH == 0) {
                    $targetFaceData[$facePart]["right_eye_lower_eyelid_movement_y"][$i]["force"] = 0;
                    $targetFaceData[$facePart]["right_eye_lower_eyelid_movement_y"][$i]["val"] = 'none';
                }
                $targetFaceData[$facePart]["right_eye_lower_eyelid_movement_x"][$i]["force"] = $rightEyeInnerCornerForce;
                if ($rightEyeInnerCorner < 0) $targetFaceData[$facePart]["right_eye_lower_eyelid_movement_x"][$i]["val"] = 'to center';
                if ($rightEyeInnerCorner > 0) $targetFaceData[$facePart]["right_eye_lower_eyelid_movement_x"][$i]["val"] = 'from center';
                if ($rightEyeInnerCorner == 0) $targetFaceData[$facePart]["right_eye_lower_eyelid_movement_x"][$i]["val"] = 'none';

                $targetFaceData[$facePart]['VALUES_REL']["left_eye_lower_eyelid_movement_x"]["max"] = round($leftEyeWidthMaxByCircle/4);
                $targetFaceData[$facePart]['VALUES_REL']["left_eye_lower_eyelid_movement_x"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["left_eye_lower_eyelid_movement_x"][$i]["delta"] = $leftEyeInnerCorner;
                $targetFaceData[$facePart]['VALUES_REL']["left_eye_lower_eyelid_movement_x"][$i]["val"] = $sourceFaceData[$i][39]['X'] - $midX3942;
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_lower_eyelid_movement_x"]["max"] = round($rightEyeWidthMaxByCircle/4);
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_lower_eyelid_movement_x"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_lower_eyelid_movement_x"][$i]["delta"] = $rightEyeInnerCorner;
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_lower_eyelid_movement_x"][$i]["val"] = $sourceFaceData[$i][42]['X'] - $midX3942;

                //------------------------------------------------------------------------------------------------
                //width, расстояние между 37 и 41 для левого глаза, для правого - 43 и 47
                if (isset($sourceFaceData[$i][37]) &&
                    isset($sourceFaceData[$i][41]) &&
                    isset($sourceFaceData[$i][43]) &&
                    isset($sourceFaceData[$i][47])) {
                    $leftEyeWidth = $sourceFaceData[$i][41]['Y'] - $sourceFaceData[$i][37]['Y'];
                    $rightEyeWidth = $sourceFaceData[$i][47]['Y'] - $sourceFaceData[$i][43]['Y'];

                    $targetFaceData[$facePart]["left_eye_width"][$i]["force"] = $this->getForce(
                        $leftEyeWidthScaleByCircle, abs($leftEyeWidth - $leftEyeWidthN));
                    $targetFaceData[$facePart]["left_eye_width"][$i]["val"] = $leftEyeWidth;

                    $targetFaceData[$facePart]["right_eye_width"][$i]["force"] = $this->getForce(
                        $rightEyeWidthScaleByCircle, abs($rightEyeWidth - $rightEyeWidthN));
                    $targetFaceData[$facePart]["right_eye_width"][$i]["val"] = $rightEyeWidth;

                    $targetFaceData[$facePart]['VALUES_REL']["left_eye_width"]["max"] = $leftEyeWidthMaxByCircle;
                    $targetFaceData[$facePart]['VALUES_REL']["left_eye_width"]["min"] = $leftEyeWidthN;
                    $targetFaceData[$facePart]['VALUES_REL']["left_eye_width"][$i]["delta"] = $leftEyeWidth - $leftEyeWidthN;
                    $targetFaceData[$facePart]['VALUES_REL']["left_eye_width"][$i]["val"] = $leftEyeWidth;
                    $targetFaceData[$facePart]['VALUES_REL']["right_eye_width"]["max"] = $rightEyeWidthMaxByCircle;
                    $targetFaceData[$facePart]['VALUES_REL']["right_eye_width"]["min"] = $rightEyeWidthN;
                    $targetFaceData[$facePart]['VALUES_REL']["right_eye_width"][$i]["delta"] = $rightEyeWidth - $rightEyeWidthN;
                    $targetFaceData[$facePart]['VALUES_REL']["right_eye_width"][$i]["val"] = $rightEyeWidth;

                    //альтернативно: width, расстояние между 38 и 40 для левого глаза, для правого - 44 и 46
                    $leftEyeWidth2 = $sourceFaceData[$i][40]['Y'] - $sourceFaceData[$i][38]['Y'];
                    $rightEyeWidth2 = $sourceFaceData[$i][46]['Y'] - $sourceFaceData[$i][44]['Y'];

                    $targetFaceData[$facePart]["left_eye_width2"][$i]["force"] = $this->getForce(
                        $leftEyeWidthMaxByCircle, abs($leftEyeWidth2 - $leftEyeWidthN2));

                    $targetFaceData[$facePart]["left_eye_width2"][$i]["val"] = $leftEyeWidth2;

                    $targetFaceData[$facePart]["right_eye_width2"][$i]["force"] = $this->getForce(
                        $rightEyeWidthMaxByCircle, abs($rightEyeWidth2 - $rightEyeWidthN2));
                    $targetFaceData[$facePart]["right_eye_width2"][$i]["val"] = $rightEyeWidth2;

                    //Глаза, ширина глаз (увеличение, уменьшение) через изменение ширины
                    $targetFaceData[$facePart]["left_eye_width_changing"][$i]["force"] =
                        $targetFaceData[$facePart]["left_eye_width"][$i]["force"];
                    $targetFaceData[$facePart]["left_eye_width_changing"][$i]["val"] = 'none';
                    if ($targetFaceData[$facePart]["left_eye_width"][$i]["val"] > $leftEyeWidthN)
                        $targetFaceData[$facePart]["left_eye_width_changing"][$i]["val"] = '+';
                    if ($targetFaceData[$facePart]["left_eye_width"][$i]["val"] < $leftEyeWidthN)
                        $targetFaceData[$facePart]["left_eye_width_changing"][$i]["val"] = '-';

                    $targetFaceData[$facePart]["right_eye_width_changing"][$i]["force"] =
                        $targetFaceData[$facePart]["right_eye_width"][$i]["force"];
                    $targetFaceData[$facePart]["right_eye_width_changing"][$i]["val"] = 'none';
                    if ($targetFaceData[$facePart]["right_eye_width"][$i]["val"] > $rightEyeWidthN)
                        $targetFaceData[$facePart]["right_eye_width_changing"][$i]["val"] = '+';
                    if ($targetFaceData[$facePart]["right_eye_width"][$i]["val"] < $rightEyeWidthN)
                        $targetFaceData[$facePart]["right_eye_width_changing"][$i]["val"] = '-';
                }
                //------------------------------------------------------------------------------------------------
                //Внешний уголок глаза, движение внешнего уголка глаза (вверх, вниз)
                if (isset($sourceFaceData[$i][36]))
                    $leftEyeOuterCornerH = $sourceFaceData[$i][36]['Y'] - $yN36 - $midY3942;
                if (isset($sourceFaceData[$i][45]))
                    $rightEyeOuterCornerH = $sourceFaceData[$i][45]['Y'] - $yN45 - $midY3942;

                $targetFaceData[$facePart]["left_eye_outer_movement"][$i]["force"] = $this->getForce(
                    round($leftEyeWidthMaxByCircle/2), abs($leftEyeOuterCornerH));
                $targetFaceData[$facePart]["right_eye_outer_movement"][$i]["force"] = $this->getForce(
                    round($rightEyeWidthMaxByCircle/2), abs($rightEyeOuterCornerH));

                if ($leftEyeOuterCornerH < 0) $targetFaceData[$facePart]["left_eye_outer_movement"][$i]["val"] = 'up';
                if ($leftEyeOuterCornerH > 0) $targetFaceData[$facePart]["left_eye_outer_movement"][$i]["val"] = 'down';
                if ($leftEyeOuterCornerH == 0) {
                    $targetFaceData[$facePart]["left_eye_outer_movement"][$i]["force"] = 0;
                    $targetFaceData[$facePart]["left_eye_outer_movement"][$i]["val"] = 'none';
                }
                if ($rightEyeOuterCornerH < 0) $targetFaceData[$facePart]["right_eye_outer_movement"][$i]["val"] = 'up';
                if ($rightEyeOuterCornerH > 0) $targetFaceData[$facePart]["right_eye_outer_movement"][$i]["val"] = 'down';
                if ($rightEyeOuterCornerH == 0) {
                    $targetFaceData[$facePart]["right_eye_outer_movement"][$i]["force"] = 0;
                    $targetFaceData[$facePart]["right_eye_outer_movement"][$i]["val"] = 'none';
                }

                $targetFaceData[$facePart]['VALUES_REL']["left_eye_outer_movement"]["max"] = round($leftEyeWidthMaxByCircle/2);
                $targetFaceData[$facePart]['VALUES_REL']["left_eye_outer_movement"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["left_eye_outer_movement"][$i]["delta"] = $leftEyeOuterCornerH;
                $targetFaceData[$facePart]['VALUES_REL']["left_eye_outer_movement"][$i]["val"] = $sourceFaceData[$i][36]['Y'] - $midY3942;
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_outer_movement"]["max"] = round($rightEyeWidthMaxByCircle/2);
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_outer_movement"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_outer_movement"][$i]["delta"] = $rightEyeOuterCornerH;
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_outer_movement"][$i]["val"] = $sourceFaceData[$i][45]['Y'] - $midY3942;
                //------------------------------------------------------------------------------------------------
                //Внутренний уголок глаза, движение внутреннего уголка глаза (вверх, вниз)
                if (isset($sourceFaceData[$i][39]))
                    $leftEyeInnerCornerH = $sourceFaceData[$i][39]['Y'] - $yN39 - $midY3942;
                if (isset($sourceFaceData[$i][42]))
                    $rightEyeInnerCornerH = $sourceFaceData[$i][42]['Y'] - $yN42 - $midY3942;

                $targetFaceData[$facePart]["left_eye_inner_movement"][$i]["force"] = $this->getForce(
                    round($leftEyeWidthMaxByCircle/2), abs($leftEyeInnerCornerH));
                $targetFaceData[$facePart]["right_eye_inner_movement"][$i]["force"] = $this->getForce(
                    round($rightEyeWidthMaxByCircle/2), abs($rightEyeInnerCornerH));

                if ($leftEyeInnerCornerH < 0) $targetFaceData[$facePart]["left_eye_inner_movement"][$i]["val"] = 'up';
                if ($leftEyeInnerCornerH > 0) $targetFaceData[$facePart]["left_eye_inner_movement"][$i]["val"] = 'down';
                if ($leftEyeInnerCornerH == 0) {
                    $targetFaceData[$facePart]["left_eye_inner_movement"][$i]["force"] = 0;
                    $targetFaceData[$facePart]["left_eye_inner_movement"][$i]["val"] = 'none';
                }
                if ($rightEyeInnerCornerH < 0) $targetFaceData[$facePart]["right_eye_inner_movement"][$i]["val"] = 'up';
                if ($rightEyeInnerCornerH > 0) $targetFaceData[$facePart]["right_eye_inner_movement"][$i]["val"] = 'down';
                if ($rightEyeInnerCornerH == 0) {
                    $targetFaceData[$facePart]["right_eye_inner_movement"][$i]["force"] = 0;
                    $targetFaceData[$facePart]["right_eye_inner_movement"][$i]["val"] = 'none';
                }
                $targetFaceData[$facePart]['VALUES_REL']["left_eye_inner_movement"]["max"] = round($leftEyeWidthMaxByCircle/2);
                $targetFaceData[$facePart]['VALUES_REL']["left_eye_inner_movement"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["left_eye_inner_movement"][$i]["delta"] = $leftEyeInnerCornerH;
                $targetFaceData[$facePart]['VALUES_REL']["left_eye_inner_movement"][$i]["val"] = $sourceFaceData[$i][39]['Y'] - $midY3942;
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_inner_movement"]["max"] = round($rightEyeWidthMaxByCircle/2);
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_inner_movement"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_inner_movement"][$i]["delta"] = $rightEyeInnerCornerH;
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_inner_movement"][$i]["val"] = $sourceFaceData[$i][42]['Y'] - $midY3942;
                //------------------------------------------------------------------------------------------------
            }
            return $targetFaceData[$facePart];
        } else return false;
    }

    public function addPointsToResults($pointsName,$sectionName,$sourceFaceData,$resFaceData,$info)
    {
        if($info != '') $info = '(' . $info . ')';
        if (isset($sourceFaceData[$pointsName])) {
            $resFaceData['MASK_NAMES'][] = $sectionName . $info;
            for ($i = 0; $i < count($sourceFaceData[$pointsName]); $i++) {
                if (isset($sourceFaceData[$pointsName][$i]))
                    foreach ($sourceFaceData[$pointsName][$i] as $k1 => $v1) { //points
                        $resFaceData['frame_#' . $i][$sectionName . $info][$k1][0] = $sourceFaceData[$pointsName][$i][$k1]['X'];
                        $resFaceData['frame_#' . $i][$sectionName . $info][$k1][1] = $sourceFaceData[$pointsName][$i][$k1]['Y'];
                    }
            }
        }
        return $resFaceData;
    }

    /**
     * конвертация входного файла A (Кулижского) в массив АБ
     * @param $iFaceData - массив из json в формате И
     * @return array - массив в формате АБ
     */
    public function convertAJson($iFaceData)
    {
        $FaceData_ = array();
        for ($i = 0; $i < count($iFaceData); $i++)
        {
            foreach ($iFaceData[$i] as $k => $v) {
                $ii = $iFaceData[$i]['frame'];
                if (isset($v)) {
                    //points processing
                    if ($k == 'landmarks_2D')
                        for ($i2 = 0; $i2 < count($v); $i2++)
                            if(isset($v[$i2]) && ($v[$i2] != 'count')) {
                                $FaceData_['points'][$ii][$i2]['X'] = $v[$i2]['x'];
                                $FaceData_['points'][$ii][$i2]['Y'] = $v[$i2]['y'];
                            }
                    //gaze angle
                    if ($k == "gaze angle")
                        {
                            $FaceData_["gazeangle"][$ii]['X'] = $v['x'];
                            $FaceData_["gazeangle"][$ii]['Y'] = $v['y'];
                        }
                }
            }
    }
//        echo json_encode($FaceData_["gazeangle"]).'<br>';
        return $FaceData_;
    }

    /**
     * конвертация входного файла И (Савкина) в массив АБ
     * @param $iFaceData - массив из json в формате И
     * @return array - массив в формате АБ
     */
    public function convertIJson($iFaceData)
    {
        $i = 0;
        foreach ($iFaceData as $k=>$v)
            if (strpos(Trim($k), 'frame_') !== false) {
                if(isset($v)) {
                    //norm points processing
                    if (isset($v['NORM_POINTS']))
                        foreach ($v['NORM_POINTS'] as $k1 => $v1) {
                            $FaceData_['normmask'][$i][$k1]['X'] = $v1[0];
                            $FaceData_['normmask'][$i][$k1]['Y'] = $v1[1];
                        }
                    //norm irises processing
                    if (isset($v['NORM_IRISES']))
                        foreach ($v['NORM_IRISES'] as $k1 => $v1) {
                            $FaceData_['normirises'][$i][$k1]['X'] = $v1[0];
                            $FaceData_['normirises'][$i][$k1]['Y'] = $v1[1];
                        }
                    //orig irises processing
                    if (isset($v['ORIG_IRISES']))
                        foreach ($v['ORIG_IRISES'] as $k1 => $v1) {
                            $FaceData_['origirises'][$i][$k1]['X'] = $v1[0];
                            $FaceData_['origirises'][$i][$k1]['Y'] = $v1[1];
                        }
                    //points processing
                    if (isset($v['POINTS']))
                        foreach ($v['POINTS'] as $k1 => $v1) {
                            $FaceData_['points'][$i][$k1]['X'] = $v1[0];
                            $FaceData_['points'][$i][$k1]['Y'] = $v1[1];
                        }
                    //CONTOURS processing
                    if (isset($v['CONTOURS']))
                        foreach ($v['CONTOURS'] as $k1 => $v1) {
                            $sWrinkles = 0;
                            $s2Wrinkles = 0;
                            $pWrinkles = 0;
                            //31x48x74 31x40x74  - left_nasolabial_fold
                            //35x54x75 35x47x75 - right_nasolabial_fold
                            //27x35x42 и 27x31x39 - right and left nose wrinkle zones
                            //21х22х28 - central nose wrinkle zone
                            foreach ($v1 as $k2 => $v2){
                              $sWrinkles = $sWrinkles + $v2[2];
                              $s2Wrinkles = $s2Wrinkles + $v2[3];
                              $pWrinkles = $pWrinkles + $v2[4];
                            }
                            $cntWrinkles = count($v1);
                            $FaceData_['contours'][$i][$k1]['cnt_wrinkles'] = $cntWrinkles;
                            $FaceData_['contours'][$i][$k1]['s_wrinkles'] = $sWrinkles;
                            $FaceData_['contours'][$i][$k1]['s2_wrinkles'] = $s2Wrinkles;
                            $FaceData_['contours'][$i][$k1]['p_wrinkles'] = $pWrinkles;
                        }
                    //brow points processing
                    if (isset($v['brow']))
                        foreach ($v['brow'] as $k1 => $v1) {
                            $FaceData_['brow'][$i][$k1]['X'] = $v1[0];
                            $FaceData_['brow'][$i][$k1]['Y'] = $v1[1];
                        }
                    //eyebrow points processing
                    if (isset($v['eyebrow']))
                        foreach ($v['eyebrow'] as $k1 => $v1) {
                            $FaceData_['eyebrow'][$i][$k1]['X'] = $v1[0];
                            $FaceData_['eyebrow'][$i][$k1]['Y'] = $v1[1];
                        }
                    //eye points processing
                    if (isset($v['eye']))
                        foreach ($v['eye'] as $k1 => $v1) {
                            $FaceData_['eye'][$i][$k1]['X'] = $v1[0];
                            $FaceData_['eye'][$i][$k1]['Y'] = $v1[1];
                        }
                    //left_nasolabial_fold processing
                    if (isset($v['31x48x74'])){
                            $FaceData_['left_nasolabial_fold'][$i][0]['X'] = $v['31x48x74'][0][0];
                            $FaceData_['left_nasolabial_fold'][$i][0]['Y'] = $v['31x48x74'][0][1];
                            $FaceData_['left_nasolabial_fold'][$i][0]['X2'] = $v['31x48x74'][0][2];
                            $FaceData_['left_nasolabial_fold'][$i][0]['Y2'] = $v['31x48x74'][0][3];
                            $FaceData_['left_nasolabial_fold'][$i][0]['SUMX'] = $v['31x48x74'][1][0];
                            $FaceData_['left_nasolabial_fold'][$i][0]['SUMY'] = $v['31x48x74'][1][1];
                            $FaceData_['left_nasolabial_fold'][$i][0]['SUMX2'] = $v['31x48x74'][1][2];
                            $FaceData_['left_nasolabial_fold'][$i][0]['SUMY2'] = $v['31x48x74'][1][3];
                            $FaceData_['left_nasolabial_fold'][$i][0]['NNN'] = $v['31x48x74'][1][4];

                    }
                    if (isset($v['31x40x74'])){
                        $FaceData_['left_nasolabial_fold_2'][$i][0]['X'] = $v['31x40x74'][0][0];
                        $FaceData_['left_nasolabial_fold_2'][$i][0]['Y'] = $v['31x40x74'][0][1];
                        $FaceData_['left_nasolabial_fold_2'][$i][0]['X2'] = $v['31x40x74'][0][2];
                        $FaceData_['left_nasolabial_fold_2'][$i][0]['Y2'] = $v['31x40x74'][0][3];
                        $FaceData_['left_nasolabial_fold_2'][$i][0]['SUMX'] = $v['31x40x74'][1][0];
                        $FaceData_['left_nasolabial_fold_2'][$i][0]['SUMY'] = $v['31x40x74'][1][1];
                        $FaceData_['left_nasolabial_fold_2'][$i][0]['SUMX2'] = $v['31x40x74'][1][2];
                        $FaceData_['left_nasolabial_fold_2'][$i][0]['SUMY2'] = $v['31x40x74'][1][3];
                        $FaceData_['left_nasolabial_fold_2'][$i][0]['NNN'] = $v['31x40x74'][1][4];
                    }
                    if (isset($v['40x41x74'])){
                        $FaceData_['left_nasolabial_fold_3'][$i][0]['X'] = $v['40x41x74'][0][0];
                        $FaceData_['left_nasolabial_fold_3'][$i][0]['Y'] = $v['40x41x74'][0][1];
                        $FaceData_['left_nasolabial_fold_3'][$i][0]['X2'] = $v['40x41x74'][0][2];
                        $FaceData_['left_nasolabial_fold_3'][$i][0]['Y2'] = $v['40x41x74'][0][3];
                        $FaceData_['left_nasolabial_fold_3'][$i][0]['SUMX'] = $v['40x41x74'][1][0];
                        $FaceData_['left_nasolabial_fold_3'][$i][0]['SUMY'] = $v['40x41x74'][1][1];
                        $FaceData_['left_nasolabial_fold_3'][$i][0]['SUMX2'] = $v['40x41x74'][1][2];
                        $FaceData_['left_nasolabial_fold_3'][$i][0]['SUMY2'] = $v['40x41x74'][1][3];
                        $FaceData_['left_nasolabial_fold_3'][$i][0]['NNN'] = $v['40x41x74'][1][4];
                    }
                    //right_nasolabial_fold processing
                    if (isset($v['35x54x75'])) {
                            $FaceData_['right_nasolabial_fold'][$i][0]['X'] = $v['35x54x75'][0][0];
                            $FaceData_['right_nasolabial_fold'][$i][0]['Y'] = $v['35x54x75'][0][1];
                            $FaceData_['right_nasolabial_fold'][$i][0]['X2'] = $v['35x54x75'][0][2];
                            $FaceData_['right_nasolabial_fold'][$i][0]['Y2'] = $v['35x54x75'][0][3];
                            $FaceData_['right_nasolabial_fold'][$i][0]['SUMX'] = $v['35x54x75'][1][0];
                            $FaceData_['right_nasolabial_fold'][$i][0]['SUMY'] = $v['35x54x75'][1][1];
                            $FaceData_['right_nasolabial_fold'][$i][0]['SUMX2'] = $v['35x54x75'][1][2];
                            $FaceData_['right_nasolabial_fold'][$i][0]['SUMY2'] = $v['35x54x75'][1][3];
                            $FaceData_['right_nasolabial_fold'][$i][0]['NNN'] = $v['35x54x75'][1][4];
                        }
                    if (isset($v['35x47x75'])) {
                        $FaceData_['right_nasolabial_fold_2'][$i][0]['X'] = $v['35x47x75'][0][0];
                        $FaceData_['right_nasolabial_fold_2'][$i][0]['Y'] = $v['35x47x75'][0][1];
                        $FaceData_['right_nasolabial_fold_2'][$i][0]['X2'] = $v['35x47x75'][0][2];
                        $FaceData_['right_nasolabial_fold_2'][$i][0]['Y2'] = $v['35x47x75'][0][3];
                        $FaceData_['right_nasolabial_fold_2'][$i][0]['SUMX'] = $v['35x47x75'][1][0];
                        $FaceData_['right_nasolabial_fold_2'][$i][0]['SUMY'] = $v['35x47x75'][1][1];
                        $FaceData_['right_nasolabial_fold_2'][$i][0]['SUMX2'] = $v['35x47x75'][1][2];
                        $FaceData_['right_nasolabial_fold_2'][$i][0]['SUMY2'] = $v['35x47x75'][1][3];
                        $FaceData_['right_nasolabial_fold_2'][$i][0]['NNN'] = $v['35x47x75'][1][4];
                    }
                    if (isset($v['46x47x75'])) {
                        $FaceData_['right_nasolabial_fold_3'][$i][0]['X'] = $v['46x47x75'][0][0];
                        $FaceData_['right_nasolabial_fold_3'][$i][0]['Y'] = $v['46x47x75'][0][1];
                        $FaceData_['right_nasolabial_fold_3'][$i][0]['X2'] = $v['46x47x75'][0][2];
                        $FaceData_['right_nasolabial_fold_3'][$i][0]['Y2'] = $v['46x47x75'][0][3];
                        $FaceData_['right_nasolabial_fold_3'][$i][0]['SUMX'] = $v['46x47x75'][1][0];
                        $FaceData_['right_nasolabial_fold_3'][$i][0]['SUMY'] = $v['46x47x75'][1][1];
                        $FaceData_['right_nasolabial_fold_3'][$i][0]['SUMX2'] = $v['46x47x75'][1][2];
                        $FaceData_['right_nasolabial_fold_3'][$i][0]['SUMY2'] = $v['46x47x75'][1][3];
                        $FaceData_['right_nasolabial_fold_3'][$i][0]['NNN'] = $v['46x47x75'][1][4];
                    }               }
                $i++;
            }
        return $FaceData_;
    }

    /**
     * Обнаружение признаков носа по абсолютным координатам
     *
     * @param $sourceFaceData - входной массив с лицевыми точками (landmarks)
     * @return array - выходной массив с обработанным массивом для лба
     */
    public function detectNoseFeaturesByAbsCoordinates($sourceFaceData, $facePart)
    {
        //анализируемые точки низа носа
        // 31 (left_nose_wing),
        // 35 (right_nose_wing),
        // получение нормированного значения по кадру 0
        //относительно центра между внутренними уголками глаз, точки 39 и 42

        if (isset($sourceFaceData[0][31])
            && isset($sourceFaceData[0][33])
            && isset($sourceFaceData[0][35])
        ) {
            $facePart = $facePart.'_by_abs';

            // интенсивность носа - средняя величина длин правой (тт. 33-35) и левой (тт. 31-33)  крыльев носа. * 50%
            $maxYWing = round(
                (($sourceFaceData[0][33]['X'] - $sourceFaceData[0][31]['X']) +
                    ($sourceFaceData[0][35]['X'] - $sourceFaceData[0][33]['X']))/4
            );

            $yN31 = $sourceFaceData[0][31]['Y'];
            $yN35 = $sourceFaceData[0][35]['Y'];
//            $scaleLeftWing = $maxLeftWing - $yN31;
//            $scaleRightWing = $maxRightWing - $yN35;

            $maxY31 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 31, "Y");
            $minY31 = $this->getFaceDataMinForKeyV2($sourceFaceData, 31, "Y");
            $scaleY31 = $maxY31 - $minY31;
            $maxY35 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 35, "Y");
            $minY35 = $this->getFaceDataMinForKeyV2($sourceFaceData, 35, "Y");
            $scaleY35 = $maxY35 - $minY35;


            for ($i = 0; $i < count($sourceFaceData); $i++) {
                if (isset($sourceFaceData[$i][31]) && $sourceFaceData[$i][35]) {
                    $leftNoseWingMovement = $sourceFaceData[$i][31]['Y'] - $yN31;
                    $rightNoseWingMovement = $sourceFaceData[$i][35]['Y'] - $yN35;

                    //                   $leftNoseWingMovementForce = $this->getForce($scaleLeftWing, abs($leftNoseWingMovement));
                    //                   $rightNoseWingMovementForce = $this->getForce($scaleRightWing, abs($rightNoseWingMovement));
                    $leftNoseWingMovementForce = $this->getForce($maxYWing, abs($leftNoseWingMovement));
                    $rightNoseWingMovementForce = $this->getForce($maxYWing, abs($rightNoseWingMovement));
                    $noseWingsMovementForce = round(($leftNoseWingMovementForce + $rightNoseWingMovementForce) / 2); //среднее значение
                }
                $targetFaceData[$facePart]['VALUES_REL']["left_nose_wing_movement"]["max"] = $maxYWing;
                $targetFaceData[$facePart]['VALUES_REL']["left_nose_wing_movement"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["left_nose_wing_movement"][$i]["delta"] = $leftNoseWingMovement;
                $targetFaceData[$facePart]['VALUES_REL']["right_nose_wing_movement"]["max"] = $maxYWing;
                $targetFaceData[$facePart]['VALUES_REL']["right_nose_wing_movement"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["right_nose_wing_movement"][$i]["delta"] = $rightNoseWingMovement;

                $targetFaceData[$facePart]["nose_wing_movement"][$i]["force"] = $noseWingsMovementForce;
                if (($leftNoseWingMovement < 0) || ($rightNoseWingMovement < 0)) $targetFaceData[$facePart]["nose_wing_movement"][$i]["val"] = 'up';
                if (($leftNoseWingMovement > 0) || ($rightNoseWingMovement > 0)) $targetFaceData[$facePart]["nose_wing_movement"][$i]["val"] = 'down';
                if (($leftNoseWingMovement == 0) && ($rightNoseWingMovement == 0)) {
                    $targetFaceData[$facePart]["nose_wing_movement"][$i]["force"] = 0;
                    $targetFaceData[$facePart]["nose_wing_movement"][$i]["val"] = 'none';
                }

            }

//        echo json_encode($sourceFaceData['letf_nasolabial_fold'][0][0]);
//        echo '<br><br>';
//        json_encode($sourceFaceData['letf_nasolabial_fold'][0][0]);
//        json_encode($sourceFaceData['right_nasolabial_fold'][0][0]);
            //анализ носогубных складок на основе треугольников
            /*           $normFrameIndex = -1;
            !!!! убрать промежуточный [0]
                       if (isset($sourceFaceData['left_nasolabial_fold'][0][0])
                           && isset($sourceFaceData['right_nasolabial_fold'][0][0])
                       ) $normFrameIndex = 0;
                       if (isset($sourceFaceData['left_nasolabial_fold'][1][0])
                           && isset($sourceFaceData['right_nasolabial_fold'][1][0])
                       )
                       {
                           if ($normFrameIndex != 0) $normFrameIndex = 1;
                           $xRightNF = $sourceFaceData['right_nasolabial_fold'][$normFrameIndex][0]['X2'];
                           $xLeftNF = $sourceFaceData['left_nasolabial_fold'][$normFrameIndex][0]['X2'];
            //               $xRightNF2 = $sourceFaceData['right_nasolabial_fold_2'][$normFrameIndex][0]['NNN'];
           //                $xLeftNF2 = $sourceFaceData['left_nasolabial_fold_2'][$normFrameIndex][0]['NNN'];

                           $maxRightNF = $this->getFaceDataMaxForKeyV2($sourceFaceData['right_nasolabial_fold'], 0, "X2");
                           $minRightNF = $this->getFaceDataMinForKeyV2($sourceFaceData['right_nasolabial_fold'], 0, "X2");
                           $scaleRightNF = $maxRightNF - $minRightNF;
           //                $maxRightNF2 = $this->getFaceDataMaxForKeyV2($sourceFaceData['right_nasolabial_fold_2'], 0, "NNN");
           //                $minRightNF2 = $this->getFaceDataMinForKeyV2($sourceFaceData['right_nasolabial_fold_2'], 0, "NNN");
           //                $scaleRightNF2 = $maxRightNF2 - $minRightNF2;
                           $maxLeftNF = $this->getFaceDataMaxForKeyV2($sourceFaceData['left_nasolabial_fold'], 0, "X2");
                           $minLeftNF = $this->getFaceDataMinForKeyV2($sourceFaceData['left_nasolabial_fold'], 0, "X2");
                           $scaleLeftNF = $maxLeftNF - $minLeftNF;
           //                $maxLeftNF2 = $this->getFaceDataMaxForKeyV2($sourceFaceData['left_nasolabial_fold_2'], 0, "NNN");
           //                $minLeftNF2 = $this->getFaceDataMinForKeyV2($sourceFaceData['left_nasolabial_fold_2'], 0, "NNN");
           //                $scaleLeftNF2 = $maxLeftNF2 - $minLeftNF2;

                           for ($i = 0; $i < count($sourceFaceData['right_nasolabial_fold']); $i++) {
                               if (isset($sourceFaceData['right_nasolabial_fold'][$i][0]) && $sourceFaceData['left_nasolabial_fold'][$i][0]) {
                                   $rightNFMovement = $sourceFaceData['right_nasolabial_fold'][$i][0]['X2'] - $xRightNF;
                                   $leftNFMovement = $sourceFaceData['left_nasolabial_fold'][$i][0]['X2'] - $xLeftNF;
           //                        $rightNFMovement2 = $sourceFaceData['right_nasolabial_fold'][$i][0]['NNN'] - $xRightNF2;
           //                        $leftNFMovement2 = $sourceFaceData['left_nasolabial_fold'][$i][0]['NNN'] - $xLeftNF2;
            //                       echo $i.' '.($sourceFaceData['right_nasolabial_fold'][$i][0]['X2']-
            //                           $sourceFaceData['right_nasolabial_fold'][$i][0]['X']).'/'.($sourceFaceData['right_nasolabial_fold'][$i][0]['Y2']-
            //                               $sourceFaceData['right_nasolabial_fold'][$i][0]['Y']).'<br>';

                                   $targetFaceData["nose"]["right_nasolabial_fold_movement"][$i]["force"] =
                                       $this->getForce($scaleRightNF, abs($rightNFMovement));
           //                        $targetFaceData["nose"]["right_nasolabial_fold_movement_2"][$i]["force"] =
           //                            $this->getForce($scaleRightNF2, abs($rightNFMovement2));

                                   if ($rightNFMovement > 0) $targetFaceData["nose"]["right_nasolabial_fold_movement"][$i]["val"] = 'from center';
                                   if ($rightNFMovement < 0) $targetFaceData["nose"]["right_nasolabial_fold_movement"][$i]["val"] = 'to center';
                                   if (($rightNFMovement == 0) ||
                                       ($targetFaceData["nose"]["right_nasolabial_fold_movement"][$i]["force"] == 0))
                                        $targetFaceData["nose"]["right_nasolabial_fold_movement"][$i]["val"] = 'none';
           //                        if ($rightNFMovement2 > 0) $targetFaceData["nose"]["right_nasolabial_fold_movement_2"][$i]["val"] = 'from center aside';
           //                        if ($rightNFMovement2 < 0) $targetFaceData["nose"]["right_nasolabial_fold_movement_2"][$i]["val"] = 'to center';
           //                        if (($rightNFMovement2 == 0) ||
           //                            ($targetFaceData["nose"]["right_nasolabial_fold_movement_2"][$i]["force"] == 0))
           //                            $targetFaceData["nose"]["right_nasolabial_fold_movement_2"][$i]["val"] = 'none';

                                   $targetFaceData["nose"]["left_nasolabial_fold_movement"][$i]["force"] =
                                       $this->getForce($scaleLeftNF, abs($leftNFMovement));
           //                        $targetFaceData["nose"]["left_nasolabial_fold_movement_2"][$i]["force"] =
           //                            $this->getForce($scaleLeftNF2, abs($leftNFMovement2));

                                   if ($leftNFMovement < 0) $targetFaceData["nose"]["left_nasolabial_fold_movement"][$i]["val"] = 'from center';
                                   if ($leftNFMovement > 0) $targetFaceData["nose"]["left_nasolabial_fold_movement"][$i]["val"] = 'to center';
                                   if (($leftNFMovement == 0) ||
                                       ($targetFaceData["nose"]["left_nasolabial_fold_movement"][$i]["force"] == 0))
                                        $targetFaceData["nose"]["left_nasolabial_fold_movement"][$i]["val"] = 'none';
           /*
                                   if ($leftNFMovement2 < 0) $targetFaceData["nose"]["left_nasolabial_fold_movement_2"][$i]["val"] = 'from center aside';
                                   if ($leftNFMovement2 > 0) $targetFaceData["nose"]["left_nasolabial_fold_movement_2"][$i]["val"] = 'to center';
                                   if (($leftNFMovement2 == 0) ||
                                       ($targetFaceData["nose"]["left_nasolabial_fold_movement_2"][$i]["force"] == 0))
                                       $targetFaceData["nose"]["left_nasolabial_fold_movement_2"][$i]["val"] = 'none';*/
            //                   }
            //               }
//            }*/
            return $targetFaceData[$facePart];
        }else return false;
    }

    /**
     * Обнаружение признаков носа
     *
     * @param $sourceFaceData - входной массив с лицевыми точками (landmarks)
     * @return array - выходной массив с обработанным массивом для лба
     */
    public function detectNoseFeatures($sourceFaceData, $facePart,$point1,$point2)
    {
        //анализируемые точки низа носа
        // 31 (left_nose_wing),
        // 35 (right_nose_wing),
        // получение нормированного значения по кадру 0
        //относительно центра между внутренними уголками глаз, точки 39 и 42

        if (isset($sourceFaceData[0][31])
            && isset($sourceFaceData[0][33])
            && isset($sourceFaceData[0][35])
            && isset($sourceFaceData[0][$point1])
            && isset($sourceFaceData[0][$point2])
        ) {
            $midNY3942 = round(($sourceFaceData[0][$point2]['Y'] - $sourceFaceData[0][$point1]['Y'])/2) +
                $sourceFaceData[0][$point1]['Y'];
            $midNX3942 = round(($sourceFaceData[0][$point2]['X'] - $sourceFaceData[0][$point1]['X'])/2) +
                $sourceFaceData[0][$point1]['X'];

            // интенсивность носа - средняя величина длин правой (тт. 33-35) и левой (тт. 31-33)  крыльев носа. * 50%
            $maxYWing = round(
                (($sourceFaceData[0][33]['X'] - $sourceFaceData[0][31]['X']) +
                    ($sourceFaceData[0][35]['X'] - $sourceFaceData[0][33]['X']))/4
            );

            $yN31 = $sourceFaceData[0][31]['Y'] - $midNY3942;
            $yN35 = $sourceFaceData[0][35]['Y'] - $midNY3942;
            $xN33 = $sourceFaceData[0][33]['X'] - $midNY3942;
            $noseWidthN = $sourceFaceData[0][35]['X']  - $sourceFaceData[0][31]['X'];
            $maxNoseMov = $noseWidthN*0.3;
            $minNoseMov = 0;
            $scaleNoseCenterMovement = $maxNoseMov - $minNoseMov;
//            $scaleLeftWing = $maxLeftWing - $yN31;
//            $scaleRightWing = $maxRightWing - $yN35;


            for ($i = 0; $i < count($sourceFaceData); $i++) {
                if (isset($sourceFaceData[$i][$point2]) && isset($sourceFaceData[$i][$point1])){
                    $midY3942 = round(($sourceFaceData[$i][$point2]['Y'] - $sourceFaceData[$i][$point1]['Y'])/2) +
                        $sourceFaceData[$i][$point1]['Y'];
                    $midX3942 = round(($sourceFaceData[$i][$point2]['X'] - $sourceFaceData[$i][$point1]['X'])/2) +
                        $sourceFaceData[$i][$point1]['X'];
                }
                if (isset($sourceFaceData[$i][31]) && isset($sourceFaceData[$i][33]) && isset($sourceFaceData[$i][35])) {
                    $leftNoseWingMovement = $sourceFaceData[$i][31]['Y'] - $yN31 - $midY3942;
                    $rightNoseWingMovement = $sourceFaceData[$i][35]['Y'] - $yN35 - $midY3942;

                    $leftNoseWingMovementForce = $this->getForce($maxYWing, abs($leftNoseWingMovement));
                    $rightNoseWingMovementForce = $this->getForce($maxYWing, abs($rightNoseWingMovement));
                    $noseWingsMovementForce = round(($leftNoseWingMovementForce + $rightNoseWingMovementForce) / 2); //среднее значение
                }
                $targetFaceData[$facePart]['VALUES_REL']["left_nose_wing_movement"]["max"] = $maxYWing;
                $targetFaceData[$facePart]['VALUES_REL']["left_nose_wing_movement"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["left_nose_wing_movement"][$i]["delta"] = $leftNoseWingMovement;
                $targetFaceData[$facePart]['VALUES_REL']["left_nose_wing_movement"][$i]["val"] = $sourceFaceData[$i][31]['Y'] - $midY3942;
                $targetFaceData[$facePart]['VALUES_REL']["right_nose_wing_movement"]["max"] = $maxYWing;
                $targetFaceData[$facePart]['VALUES_REL']["right_nose_wing_movement"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["right_nose_wing_movement"][$i]["delta"] = $rightNoseWingMovement;
                $targetFaceData[$facePart]['VALUES_REL']["right_nose_wing_movement"][$i]["val"] = $sourceFaceData[$i][35]['Y'] - $midY3942;

                $targetFaceData[$facePart]["nose_wing_movement"][$i]["force"] = $noseWingsMovementForce;
                if (($leftNoseWingMovement < 0) || ($rightNoseWingMovement < 0)) $targetFaceData[$facePart]["nose_wing_movement"][$i]["val"] = 'up';
                if (($leftNoseWingMovement > 0) || ($rightNoseWingMovement > 0)) $targetFaceData[$facePart]["nose_wing_movement"][$i]["val"] = 'down';
                if (($leftNoseWingMovement == 0) && ($rightNoseWingMovement == 0)) {
                    $targetFaceData[$facePart]["nose_wing_movement"][$i]["force"] = 0;
                    $targetFaceData[$facePart]["nose_wing_movement"][$i]["val"] = 'none';
                }

                //nose movement
                $noseCenterMovement = $sourceFaceData[$i][33]['X'] - $xN33 - $midY3942;
                $noseCenterMovementForce = $this->getForce($scaleNoseCenterMovement, abs($noseCenterMovement));
                $targetFaceData[$facePart]["nose_movement"][$i]["force"] = $noseCenterMovementForce;
//                $targetFaceData[$facePart]["nose_movement"][$i]["val"] = 'none';
                if ($noseCenterMovement < 0) $targetFaceData[$facePart]["nose_movement"][$i]["val"] = 'up';
                if ($noseCenterMovement > 0) $targetFaceData[$facePart]["nose_movement"][$i]["val"] = 'down';
                if (($noseCenterMovement == 0)) {
                    $targetFaceData[$facePart]["nose_movement"][$i]["force"] = 0;
                    $targetFaceData[$facePart]["nose_movement"][$i]["val"] = 'none';
                }

                $targetFaceData[$facePart]['VALUES_REL']["nose_movement"]["max"] = $maxNoseMov;
                $targetFaceData[$facePart]['VALUES_REL']["nose_movement"]["min"] = $minNoseMov;
                $targetFaceData[$facePart]['VALUES_REL']["nose_movement"][$i]["delta"] = $noseCenterMovement;
                $targetFaceData[$facePart]['VALUES_REL']["nose_movement"][$i]["val"] = $sourceFaceData[$i][33]['X'] - $midY3942;

                //nose width
                $curNoseWidth = $sourceFaceData[$i][35]['X'] - $sourceFaceData[$i][31]['X'];
                $noseWidth = $curNoseWidth - $noseWidthN;
                $noseWidthForce = $this->getForce($scaleNoseCenterMovement, abs($noseWidth));
                $targetFaceData[$facePart]["nose_width"][$i]["force"] = $noseWidthForce;
                $targetFaceData[$facePart]["nose_width"][$i]["val"] = $curNoseWidth;
                $targetFaceData[$facePart]["nose_width_changing"][$i]["force"] = $noseWidthForce;

                if ($noseWidth < 0) $targetFaceData[$facePart]["nose_width_changing"][$i]["val"] = '-';
                if ($noseWidth > 0) $targetFaceData[$facePart]["nose_width_changing"][$i]["val"] = '+';
                if (($noseWidth == 0)) {
                    $targetFaceData[$facePart]["nose_width"][$i]["force"] = 0;
                    $targetFaceData[$facePart]["nose_width_changing"][$i]["force"] = 0;
                    $targetFaceData[$facePart]["nose_width_changing"][$i]["val"] = 'none';
                }

                $targetFaceData[$facePart]['VALUES_REL']["nose_width"]["max"] = $maxNoseMov;
                $targetFaceData[$facePart]['VALUES_REL']["nose_width"]["min"] = $minNoseMov;
                $targetFaceData[$facePart]['VALUES_REL']["nose_width"][$i]["delta"] = $noseWidth;
                $targetFaceData[$facePart]['VALUES_REL']["nose_width"][$i]["val"] = $curNoseWidth;
            }

//        echo json_encode($sourceFaceData['letf_nasolabial_fold'][0][0]);
//        echo '<br><br>';
//        json_encode($sourceFaceData['letf_nasolabial_fold'][0][0]);
//        json_encode($sourceFaceData['right_nasolabial_fold'][0][0]);
            //анализ носогубных складок на основе треугольников
 /*           $normFrameIndex = -1;
 !!!! убрать промежуточный [0]
            if (isset($sourceFaceData['left_nasolabial_fold'][0][0])
                && isset($sourceFaceData['right_nasolabial_fold'][0][0])
            ) $normFrameIndex = 0;
            if (isset($sourceFaceData['left_nasolabial_fold'][1][0])
                && isset($sourceFaceData['right_nasolabial_fold'][1][0])
            )
            {
                if ($normFrameIndex != 0) $normFrameIndex = 1;
                $xRightNF = $sourceFaceData['right_nasolabial_fold'][$normFrameIndex][0]['X2'];
                $xLeftNF = $sourceFaceData['left_nasolabial_fold'][$normFrameIndex][0]['X2'];
 //               $xRightNF2 = $sourceFaceData['right_nasolabial_fold_2'][$normFrameIndex][0]['NNN'];
//                $xLeftNF2 = $sourceFaceData['left_nasolabial_fold_2'][$normFrameIndex][0]['NNN'];

                $maxRightNF = $this->getFaceDataMaxForKeyV2($sourceFaceData['right_nasolabial_fold'], 0, "X2");
                $minRightNF = $this->getFaceDataMinForKeyV2($sourceFaceData['right_nasolabial_fold'], 0, "X2");
                $scaleRightNF = $maxRightNF - $minRightNF;
//                $maxRightNF2 = $this->getFaceDataMaxForKeyV2($sourceFaceData['right_nasolabial_fold_2'], 0, "NNN");
//                $minRightNF2 = $this->getFaceDataMinForKeyV2($sourceFaceData['right_nasolabial_fold_2'], 0, "NNN");
//                $scaleRightNF2 = $maxRightNF2 - $minRightNF2;
                $maxLeftNF = $this->getFaceDataMaxForKeyV2($sourceFaceData['left_nasolabial_fold'], 0, "X2");
                $minLeftNF = $this->getFaceDataMinForKeyV2($sourceFaceData['left_nasolabial_fold'], 0, "X2");
                $scaleLeftNF = $maxLeftNF - $minLeftNF;
//                $maxLeftNF2 = $this->getFaceDataMaxForKeyV2($sourceFaceData['left_nasolabial_fold_2'], 0, "NNN");
//                $minLeftNF2 = $this->getFaceDataMinForKeyV2($sourceFaceData['left_nasolabial_fold_2'], 0, "NNN");
//                $scaleLeftNF2 = $maxLeftNF2 - $minLeftNF2;

                for ($i = 0; $i < count($sourceFaceData['right_nasolabial_fold']); $i++) {
                    if (isset($sourceFaceData['right_nasolabial_fold'][$i][0]) && $sourceFaceData['left_nasolabial_fold'][$i][0]) {
                        $rightNFMovement = $sourceFaceData['right_nasolabial_fold'][$i][0]['X2'] - $xRightNF;
                        $leftNFMovement = $sourceFaceData['left_nasolabial_fold'][$i][0]['X2'] - $xLeftNF;
//                        $rightNFMovement2 = $sourceFaceData['right_nasolabial_fold'][$i][0]['NNN'] - $xRightNF2;
//                        $leftNFMovement2 = $sourceFaceData['left_nasolabial_fold'][$i][0]['NNN'] - $xLeftNF2;
 //                       echo $i.' '.($sourceFaceData['right_nasolabial_fold'][$i][0]['X2']-
 //                           $sourceFaceData['right_nasolabial_fold'][$i][0]['X']).'/'.($sourceFaceData['right_nasolabial_fold'][$i][0]['Y2']-
 //                               $sourceFaceData['right_nasolabial_fold'][$i][0]['Y']).'<br>';

                        $targetFaceData["nose"]["right_nasolabial_fold_movement"][$i]["force"] =
                            $this->getForce($scaleRightNF, abs($rightNFMovement));
//                        $targetFaceData["nose"]["right_nasolabial_fold_movement_2"][$i]["force"] =
//                            $this->getForce($scaleRightNF2, abs($rightNFMovement2));

                        if ($rightNFMovement > 0) $targetFaceData["nose"]["right_nasolabial_fold_movement"][$i]["val"] = 'from center';
                        if ($rightNFMovement < 0) $targetFaceData["nose"]["right_nasolabial_fold_movement"][$i]["val"] = 'to center';
                        if (($rightNFMovement == 0) ||
                            ($targetFaceData["nose"]["right_nasolabial_fold_movement"][$i]["force"] == 0))
                             $targetFaceData["nose"]["right_nasolabial_fold_movement"][$i]["val"] = 'none';
//                        if ($rightNFMovement2 > 0) $targetFaceData["nose"]["right_nasolabial_fold_movement_2"][$i]["val"] = 'from center aside';
//                        if ($rightNFMovement2 < 0) $targetFaceData["nose"]["right_nasolabial_fold_movement_2"][$i]["val"] = 'to center';
//                        if (($rightNFMovement2 == 0) ||
//                            ($targetFaceData["nose"]["right_nasolabial_fold_movement_2"][$i]["force"] == 0))
//                            $targetFaceData["nose"]["right_nasolabial_fold_movement_2"][$i]["val"] = 'none';

                        $targetFaceData["nose"]["left_nasolabial_fold_movement"][$i]["force"] =
                            $this->getForce($scaleLeftNF, abs($leftNFMovement));
//                        $targetFaceData["nose"]["left_nasolabial_fold_movement_2"][$i]["force"] =
//                            $this->getForce($scaleLeftNF2, abs($leftNFMovement2));

                        if ($leftNFMovement < 0) $targetFaceData["nose"]["left_nasolabial_fold_movement"][$i]["val"] = 'from center';
                        if ($leftNFMovement > 0) $targetFaceData["nose"]["left_nasolabial_fold_movement"][$i]["val"] = 'to center';
                        if (($leftNFMovement == 0) ||
                            ($targetFaceData["nose"]["left_nasolabial_fold_movement"][$i]["force"] == 0))
                             $targetFaceData["nose"]["left_nasolabial_fold_movement"][$i]["val"] = 'none';
/*
                        if ($leftNFMovement2 < 0) $targetFaceData["nose"]["left_nasolabial_fold_movement_2"][$i]["val"] = 'from center aside';
                        if ($leftNFMovement2 > 0) $targetFaceData["nose"]["left_nasolabial_fold_movement_2"][$i]["val"] = 'to center';
                        if (($leftNFMovement2 == 0) ||
                            ($targetFaceData["nose"]["left_nasolabial_fold_movement_2"][$i]["force"] == 0))
                            $targetFaceData["nose"]["left_nasolabial_fold_movement_2"][$i]["val"] = 'none';*/
 //                   }
 //               }
//            }*/
            return $targetFaceData[$facePart];
        }else return false;
    }

    /**
     * Обнаружение признаков подбородка по абсолютным координатам
     *
     * @param $sourceFaceData - входной массив с лицевыми точками (landmarks)
     * @return array - выходной массив с обработанным массивом для лба
     */
    public function detectChinFeaturesByAbsCoordinates($sourceFaceData, $facePart){
        //анализируемые точки:
        // 8 (нижняя центральная точка подбородка),
        //относительно центральной точки, определяемой по точкам 39 и 42
        //Интенсивность подбородока - 100% - максимальный диаметр рта, деленный на 2.

        if ((isset($sourceFaceData[0][8]))
            && isset($sourceFaceData[0][48])
            && isset($sourceFaceData[0][54])
        ) {
            $facePart = $facePart.'_by_abs';

            $yN8 = $sourceFaceData[0][8]['Y'];

            $minX48 = $this->getFaceDataMinForKeyV2($sourceFaceData, 48, "X");
            $maxX54 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 54, "X");
            $mouthLengthMax = $maxX54 - $minX48;
            $maxChinForce = round($mouthLengthMax/2);
//            $scaleChinForce = $maxChinForce - $yN8;

            $maxY8 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 8,"Y");
            $minY8 = $this->getFaceDataMinForKeyV2($sourceFaceData,8, "Y");
            $scaleY8 = $maxY8 - $minY8;

            for ($i = 0; $i < count($sourceFaceData); $i++) {
                if ((isset($sourceFaceData[$i][8]))
                    ){
                    $chinMovement = $sourceFaceData[$i][8]['Y'] - $yN8;
//                    $chinMovementForce = $this->getForce($scaleY8, abs($chinMovement));
                    $chinMovementForce = $this->getForce($maxChinForce, abs($chinMovement));
                }
                $targetFaceData[$facePart]['VALUES_REL']["chin_movement"]["max"] = $maxChinForce;
                $targetFaceData[$facePart]['VALUES_REL']["chin_movement"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["chin_movement"][$i]["delta"] = $chinMovement;

                $targetFaceData[$facePart]["chin_movement"][$i]["force"] = $chinMovementForce;
                if ($chinMovement < 0) $targetFaceData[$facePart]["chin_movement"][$i]["val"] = 'up';
                if ($chinMovement > 0) $targetFaceData[$facePart]["chin_movement"][$i]["val"] = 'down';
                if ($chinMovement == 0) $targetFaceData[$facePart]["chin_movement"][$i]["val"] = 'none';
            }
            return $targetFaceData[$facePart];
        } else return false;
    }

    /**
     * Обнаружение признаков подбородка.
     *
     * @param $sourceFaceData - входной массив с лицевыми точками (landmarks)
     * @return array - выходной массив с обработанным массивом для лба
     */
    public function detectChinFeatures($sourceFaceData, $facePart,$point1,$point2){
        //анализируемые точки:
        // 8 (нижняя центральная точка подбородка),
        //относительно центральной точки, определяемой по точкам 39 и 42
        //Интенсивность подбородока - 100% - максимальный диаметр рта, деленный на 2.

        if ((isset($sourceFaceData[0][8]))
            && isset($sourceFaceData[0][48])
            && isset($sourceFaceData[0][54])
            && (isset($sourceFaceData[0][$point1])) && (isset($sourceFaceData[0][$point2]))
        ) {
            $midNY6167 = round(($sourceFaceData[0][$point2]['Y'] - $sourceFaceData[0][$point1]['Y'])/2) +
                $sourceFaceData[0][$point1]['Y'];
            $yN8 = $sourceFaceData[0][8]['Y'] - $midNY6167;

            $maxX48 = $this->getFaceDataMaxOnPoints($sourceFaceData, 48, "X", $point1,$point2);
            $maxX54 = $this->getFaceDataMaxOnPoints($sourceFaceData, 54, "X",$point1,$point2);
            $mouthLengthMax = $maxX54 + $maxX48;
//            $scaleChin = ($sourceFaceData[0][54]['X'] - $sourceFaceData[0][48]['X'])/2;
            $scaleChin = $mouthLengthMax*0.65; //2020-05-27
//           $maxChinForce = round($mouthLengthMax/2);
//            $scaleChinForce = $maxChinForce - $yN8;

            $maxY8 = $this->getFaceDataMaxOnPoints($sourceFaceData, 8,"Y",$point1,$point2);
            $minY8 = $this->getFaceDataMinOnPoints($sourceFaceData,8, "Y",$point1,$point2);
            $scaleY8 = $maxY8 - $minY8;

            for ($i = 0; $i < count($sourceFaceData); $i++) {
                if ((isset($sourceFaceData[$i][8]))
                    && (isset($sourceFaceData[$i][$point1])) && (isset($sourceFaceData[$i][$point2]))){
                    $midY6167 = round(($sourceFaceData[$i][$point2]['Y'] - $sourceFaceData[$i][$point1]['Y'])/2) +
                        $sourceFaceData[$i][$point1]['Y'];

                    $chinMovement = $sourceFaceData[$i][8]['Y'] - $yN8 - $midY6167;
//                    $chinMovementForce = $this->getForce($scaleY8, abs($chinMovement));
                    $chinMovementForce = $this->getForce($scaleChin, abs($chinMovement));

                }
                $targetFaceData[$facePart]['VALUES_REL']["chin_movement"]["max"] = $scaleChin;
                $targetFaceData[$facePart]['VALUES_REL']["chin_movement"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["chin_movement"][$i]["delta"] = $chinMovement;
                $targetFaceData[$facePart]['VALUES_REL']["chin_movement"][$i]["val"] = $sourceFaceData[$i][8]['Y'] - $midY6167;

                $targetFaceData[$facePart]["chin_movement"][$i]["force"] = $chinMovementForce;
                if ($chinMovement < 0) $targetFaceData[$facePart]["chin_movement"][$i]["val"] = 'up';
                if ($chinMovement > 0) $targetFaceData[$facePart]["chin_movement"][$i]["val"] = 'down';
                if ($chinMovement == 0) $targetFaceData[$facePart]["chin_movement"][$i]["val"] = 'none';
            }
            return $targetFaceData[$facePart];
        } else return false;
    }

    /**
     * Обнаружение направление взгляда по данным Кулижского.
     *
     */
    public function detectIrisesA($targetFaceData, $sourceFaceData0, $facePart, $postFix){

 //       echo json_encode($sourceFaceData0).'<br>';
            for ($i = 0; $i < count($sourceFaceData0); $i++) {

                $eyePupilYMov = $sourceFaceData0[$i+1]['Y'];
                $eyePupilXMov = $sourceFaceData0[$i+1]['X'];
//               echo $eyePupilXMov.' '.$eyePupilYMov.'<br>';
 //               print_r($eyePupilYMov);
                //80 градусов
                $eyePupilYMovForce = $this->getForce((3.14/2), abs($eyePupilYMov));
                $eyePupilXMovForce = $this->getForce((3.14/2), abs($eyePupilXMov));

                $targetFaceData[$facePart]['VALUES_REL']["left_eye_pupil_movement_x".$postFix]["max"] = (3.14/2);
                $targetFaceData[$facePart]['VALUES_REL']["left_eye_pupil_movement_x".$postFix]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["left_eye_pupil_movement_x".$postFix][$i]["val"] = $eyePupilXMov;
                $targetFaceData[$facePart]['VALUES_REL']["left_eye_pupil_movement_y".$postFix][$i]["val"] = $eyePupilYMov;

                $targetFaceData[$facePart]["left_eye_pupil_movement_x".$postFix][$i]["force"] = $eyePupilXMovForce;
                $targetFaceData[$facePart]["left_eye_pupil_movement_y".$postFix][$i]["force"] = $eyePupilYMovForce;
                $targetFaceData[$facePart]["left_eye_pupil_movement_d".$postFix][$i]["force"] =
                    round(($eyePupilXMovForce + $eyePupilYMovForce)/2);
                $targetFaceData[$facePart]["right_eye_pupil_movement_x".$postFix][$i]["force"] = $eyePupilXMovForce;
                $targetFaceData[$facePart]["right_eye_pupil_movement_y".$postFix][$i]["force"] = $eyePupilYMovForce;
                $targetFaceData[$facePart]["right_eye_pupil_movement_d".$postFix][$i]["force"] =
                    round(($eyePupilXMovForce + $eyePupilYMovForce)/2);

                $xMov = 'none';
                if ($eyePupilYMov > 0) $yMov = 'up';
                if ($eyePupilYMov < 0) $yMov = 'down';
                if ($eyePupilYMov == 0) $yMov = 'none';
                if ($eyePupilXMov < 0) $xMov = 'right';
                if ($eyePupilXMov > 0) $xMov = 'left';

                $targetFaceData[$facePart]["left_eye_pupil_movement_x".$postFix][$i]["val"] = $xMov;
                $targetFaceData[$facePart]["left_eye_pupil_movement_y".$postFix][$i]["val"] = $yMov;
                $targetFaceData[$facePart]["left_eye_pupil_movement_d".$postFix][$i]["val"] = $yMov.' and '.$xMov;

                $targetFaceData[$facePart]["right_eye_pupil_movement_x".$postFix][$i]["val"] = $xMov;
                $targetFaceData[$facePart]["right_eye_pupil_movement_y".$postFix][$i]["val"] = $yMov;
                $targetFaceData[$facePart]["right_eye_pupil_movement_d".$postFix][$i]["val"] = $yMov.' and '.$xMov;
            }
            return $targetFaceData;
    }

    /**
     * Обнаружение направление взгляда по данным Кулижского.
     *
     */
    public function detectAdditionalNoseFeatures($targetFaceData, $sourceFaceData0, $facePart, $postFix){
        //31x48x74 31x40x74  - left_nasolabial_fold
        //35x54x75 35x47x75 - right_nasolabial_fold
        //27x35x42 и 27x31x39 - right and left nose wrinkle zones
        //21x22x28 central nose wrinkle zones

        if ((isset($sourceFaceData0[0]['31x48x74']))
            && isset($sourceFaceData0[0]['31x40x74'])
            && isset($sourceFaceData0[0]['35x54x75'])
            && (isset($sourceFaceData0[0]['35x47x75']))
//            && (isset($sourceFaceData0[0]['27x35x42']))
 //           && (isset($sourceFaceData0[0]['27x31x39']))
//            && (isset($sourceFaceData0[0]['21x22x28']))
        ) {
            $nLNF1 = $sourceFaceData0[0]['31x48x74']['s_wrinkles'];
            $nLNF2 = $sourceFaceData0[0]['31x40x74']['s_wrinkles'];
            $nRNF1 = $sourceFaceData0[0]['35x54x75']['s_wrinkles'];
            $nRNF2 = $sourceFaceData0[0]['35x47x75']['s_wrinkles'];
            $nLNWZ = $sourceFaceData0[0]['27x31x39']['s_wrinkles'];
            $nRNWZ = $sourceFaceData0[0]['27x35x42']['s_wrinkles'];
            $nCNWZ = $sourceFaceData0[0]['21x22x28']['s_wrinkles'];

            $maxLNF1 = $this->getFaceDataMaxForKeyV2($sourceFaceData0, '31x48x74', 's_wrinkles');
            $minLNF1 = $this->getFaceDataMinForKeyV2($sourceFaceData0, '31x48x74', 's_wrinkles');
            $scaleLNF1 = $maxLNF1 - $minLNF1;
            $maxLNF2 = $this->getFaceDataMaxForKeyV2($sourceFaceData0, '31x40x74', 's_wrinkles');
            $minLNF2 = $this->getFaceDataMinForKeyV2($sourceFaceData0, '31x40x74', 's_wrinkles');
            $scaleLNF2 = $maxLNF2 - $minLNF2;
            $maxRNF1 = $this->getFaceDataMaxForKeyV2($sourceFaceData0, '35x54x75', 's_wrinkles');
            $minRNF1 = $this->getFaceDataMinForKeyV2($sourceFaceData0, '35x54x75', 's_wrinkles');
            $scaleRNF1 = $maxRNF1 - $minRNF1;
            $maxRNF2 = $this->getFaceDataMaxForKeyV2($sourceFaceData0, '35x47x75', 's_wrinkles');
            $minRNF2 = $this->getFaceDataMinForKeyV2($sourceFaceData0, '35x47x75', 's_wrinkles');
            $scaleRNF2 = $maxRNF2 - $minRNF2;
            $maxRNWZ = $this->getFaceDataMaxForKeyV2($sourceFaceData0, '27x35x42', 's_wrinkles');
            $minRNWZ = $this->getFaceDataMinForKeyV2($sourceFaceData0, '27x35x42', 's_wrinkles');
            $scaleRNWZ = $maxRNWZ - $minRNWZ;
            $maxLNWZ = $this->getFaceDataMaxForKeyV2($sourceFaceData0, '27x31x39', 's_wrinkles');
            $minLNWZ = $this->getFaceDataMinForKeyV2($sourceFaceData0, '27x31x39', 's_wrinkles');
            $scaleLNWZ = $maxLNWZ - $minLNWZ;
            $maxCNWZ = $this->getFaceDataMaxForKeyV2($sourceFaceData0, '21x22x28', 's_wrinkles');
            $minCNWZ = $this->getFaceDataMinForKeyV2($sourceFaceData0, '21x22x28', 's_wrinkles');
            $scaleCNWZ = $maxCNWZ - $minCNWZ;

        for ($i = 0; $i < count($sourceFaceData0); $i++) {

            $cLNF1 = $sourceFaceData0[$i]['31x48x74']['s_wrinkles'] - $nLNF1;
            $cLNF2 = $sourceFaceData0[$i]['31x40x74']['s_wrinkles'] - $nLNF2;
            $cRNF1 = $sourceFaceData0[$i]['35x54x75']['s_wrinkles'] - $nRNF1;
            $cRNF2 = $sourceFaceData0[$i]['35x47x75']['s_wrinkles'] - $nRNF2;
            $cLNWZ = $sourceFaceData0[$i]['27x31x39']['s_wrinkles'] - $nLNWZ;
            $cRNWZ = $sourceFaceData0[$i]['27x35x42']['s_wrinkles'] - $nRNWZ;
            $cCNWZ = $sourceFaceData0[$i]['21x22x28']['s_wrinkles'] - $nCNWZ;

            $forceLNF1 = $this->getForce($scaleLNF1, abs($cLNF1));
            $forceLNF2 = $this->getForce($scaleLNF2, abs($cLNF2));
            $forceRNF1 = $this->getForce($scaleRNF1, abs($cRNF1));
            $forceRNF2 = $this->getForce($scaleRNF2, abs($cRNF2));
            $forceLNWZ = $this->getForce($scaleLNWZ, abs($cLNWZ));
            $forceRNWZ = $this->getForce($scaleRNWZ, abs($cRNWZ));
            $forceCNWZ = $this->getForce($scaleCNWZ, abs($cCNWZ));

            $targetFaceData[$facePart]['VALUES_REL']["left_nasolabial_fold_movement".$postFix]["max"] = $maxLNF1;
            $targetFaceData[$facePart]['VALUES_REL']["left_nasolabial_fold_movement".$postFix]["min"] = $minLNF1;
            $targetFaceData[$facePart]['VALUES_REL']["left_nasolabial_fold_movement".$postFix][$i]["val"] = $sourceFaceData0[$i]['31x48x74']['s_wrinkles'];
            $targetFaceData[$facePart]['VALUES_REL']["left_nasolabial_fold_movement".$postFix][$i]["delta"] = $cLNF1;

            $targetFaceData[$facePart]["left_nasolabial_fold_movement".$postFix][$i]["force"] = $forceLNF1;
            $val = 'none';
            if ($cLNF1 > 0) $val = '+';
            if ($cLNF1 < 0) $val = '-';
            $targetFaceData[$facePart]["left_nasolabial_fold_movement".$postFix][$i]["val"] = $val;

            $targetFaceData[$facePart]['VALUES_REL']["left_nasolabial_fold_movement_2".$postFix]["max"] = $maxLNF2;
            $targetFaceData[$facePart]['VALUES_REL']["left_nasolabial_fold_movement_2".$postFix]["min"] = $minLNF2;
            $targetFaceData[$facePart]['VALUES_REL']["left_nasolabial_fold_movement_2".$postFix][$i]["val"] = $sourceFaceData0[$i]['31x40x74']['s_wrinkles'];
            $targetFaceData[$facePart]['VALUES_REL']["left_nasolabial_fold_movement_2".$postFix][$i]["delta"] = $cLNF2;

            $targetFaceData[$facePart]["left_nasolabial_fold_movement_2".$postFix][$i]["force"] = $forceLNF2;
            $val = 'none';
            if ($cLNF2 > 0) $val = '+';
            if ($cLNF2 < 0) $val = '-';
            $targetFaceData[$facePart]["left_nasolabial_fold_movement_2".$postFix][$i]["val"] = $val;

            $targetFaceData[$facePart]['VALUES_REL']["right_nasolabial_fold_movement".$postFix]["max"] = $maxRNF1;
            $targetFaceData[$facePart]['VALUES_REL']["right_nasolabial_fold_movement".$postFix]["min"] = $minRNF1;
            $targetFaceData[$facePart]['VALUES_REL']["right_nasolabial_fold_movement".$postFix][$i]["val"] = $sourceFaceData0[$i]['35x54x75']['s_wrinkles'];
            $targetFaceData[$facePart]['VALUES_REL']["right_nasolabial_fold_movement".$postFix][$i]["delta"] = $cRNF1;

            $targetFaceData[$facePart]["right_nasolabial_fold_movement".$postFix][$i]["force"] = $forceRNF1;
            $val = 'none';
            if ($cRNF1 > 0) $val = '+';
            if ($cRNF1 < 0) $val = '-';
            $targetFaceData[$facePart]["right_nasolabial_fold_movement".$postFix][$i]["val"] = $val;

            $targetFaceData[$facePart]['VALUES_REL']["right_nasolabial_fold_movement_2".$postFix]["max"] = $maxRNF2;
            $targetFaceData[$facePart]['VALUES_REL']["right_nasolabial_fold_movement_2".$postFix]["min"] = $minRNF2;
            $targetFaceData[$facePart]['VALUES_REL']["right_nasolabial_fold_movement_2".$postFix][$i]["val"] = $sourceFaceData0[$i]['35x47x75']['s_wrinkles'];
            $targetFaceData[$facePart]['VALUES_REL']["right_nasolabial_fold_movement_2".$postFix][$i]["delta"] = $cRNF2;

            $targetFaceData[$facePart]["right_nasolabial_fold_movement_2".$postFix][$i]["force"] = $forceRNF2;
            $val = 'none';
            if ($cRNF2 > 0) $val = '+';
            if ($cRNF2 < 0) $val = '-';
            $targetFaceData[$facePart]["right_nasolabial_fold_movement_2".$postFix][$i]["val"] = $val;

            $targetFaceData[$facePart]['VALUES_REL']["left_nose_wrinkle_zone".$postFix]["max"] = $maxLNWZ;
            $targetFaceData[$facePart]['VALUES_REL']["left_nose_wrinkle_zone".$postFix]["min"] = $minLNWZ;
            $targetFaceData[$facePart]['VALUES_REL']["left_nose_wrinkle_zone".$postFix][$i]["val"] = $sourceFaceData0[$i]['27x31x39']['s_wrinkles'];
            $targetFaceData[$facePart]['VALUES_REL']["left_nose_wrinkle_zone".$postFix][$i]["delta"] = $cLNWZ;

            $targetFaceData[$facePart]["left_nose_wrinkle_zone".$postFix][$i]["force"] = $forceLNWZ;
            $val = 'none';
            if ($cLNWZ > 0) $val = '+';
            if ($cLNWZ < 0) $val = '-';
            $targetFaceData[$facePart]["left_nose_wrinkle_zone".$postFix][$i]["val"] = $val;

            $targetFaceData[$facePart]['VALUES_REL']["right_nose_wrinkle_zone".$postFix]["max"] = $maxRNWZ;
            $targetFaceData[$facePart]['VALUES_REL']["right_nose_wrinkle_zone".$postFix]["min"] = $minRNWZ;
            $targetFaceData[$facePart]['VALUES_REL']["right_nose_wrinkle_zone".$postFix][$i]["val"] = $sourceFaceData0[$i]['27x35x42']['s_wrinkles'];
            $targetFaceData[$facePart]['VALUES_REL']["right_nose_wrinkle_zone".$postFix][$i]["delta"] = $cRNWZ;

            $targetFaceData[$facePart]["right_nose_wrinkle_zone".$postFix][$i]["force"] = $forceRNWZ;
            $val = 'none';
            if ($cRNWZ > 0) $val = '+';
            if ($cRNWZ < 0) $val = '-';
            $targetFaceData[$facePart]["right_nose_wrinkle_zone".$postFix][$i]["val"] = $val;

            $targetFaceData[$facePart]['VALUES_REL']["central_nose_wrinkle_zone".$postFix]["max"] = $maxCNWZ;
            $targetFaceData[$facePart]['VALUES_REL']["central_nose_wrinkle_zone".$postFix]["min"] = $minCNWZ;
            $targetFaceData[$facePart]['VALUES_REL']["central_nose_wrinkle_zone".$postFix][$i]["val"] = $sourceFaceData0[$i]['21x22x28']['s_wrinkles'];
            $targetFaceData[$facePart]['VALUES_REL']["central_nose_wrinkle_zone".$postFix][$i]["delta"] = $cCNWZ;

            $targetFaceData[$facePart]["central_nose_wrinkle_zone".$postFix][$i]["force"] = $forceCNWZ;
            $val = 'none';
            if ($cCNWZ > 0) $val = '+';
            if ($cCNWZ < 0) $val = '-';
            $targetFaceData[$facePart]["central_nose_wrinkle_zone".$postFix][$i]["val"] = $val;
        }
        return $targetFaceData;
        } else return false;
    }

    /**
     * Обнаружение признаков зрачков.
     *
     * @param $sourceFaceData - входной массив с лицевыми точками (landmarks)
     * @return array - выходной массив с обработанным массивом для лба
     */
    public function detectIrises($targetFaceData, $sourceFaceData0, $facePart, $postFix){
        //анализируемые точки:
        // 0 (left),
        // 1 (right),
        //на основе абсолютных значений, т.к. они не привязаны к точкам маски

        // получение нормированного значения по кадру 0
//        echo '$sourceFaceData0[0][0] /'.$sourceFaceData0[0][0].' $sourceFaceData0[0][1]/'.$sourceFaceData0[0][1].' /'.
//            $sourceFaceData[0][$point1].'<br>';
        if (isset($sourceFaceData0[0][0]) && isset($sourceFaceData0[0][1])) {
            $leftEyeNWidthForIrises = 0;
            if (isset($targetFaceData[$facePart]['VALUES_REL']["left_eye_width"][0]["val"]))
                $leftEyeNWidthForIrises = round($targetFaceData[$facePart]['VALUES_REL']["left_eye_width"][0]["val"] / 2);
            $rightEyeNWidthForIrises = 0;
            if (isset($targetFaceData[$facePart]['VALUES_REL']["right_eye_width"][0]["val"]))
                $rightEyeNWidthForIrises = round($targetFaceData[$facePart]['VALUES_REL']["right_eye_width"][0]["val"] / 2);

            $yN0 = $sourceFaceData0[0][0]['Y'];
            $xN0 = $sourceFaceData0[0][0]['X'];
            $yN1 = $sourceFaceData0[0][1]['Y'];
            $xN1 = $sourceFaceData0[0][1]['X'];

 /*           $maxY0 = $this->getFaceDataMaxForKeyV2($sourceFaceData0, 0, "Y");
            $minY0 = $this->getFaceDataMinForKeyV2($sourceFaceData0, 0, "Y");
            $scaleY0 = $maxY0 - $minY0;
            $maxX0 = $this->getFaceDataMaxForKeyV2($sourceFaceData0, 0, "X");
            $minX0 = $this->getFaceDataMinForKeyV2($sourceFaceData0, 0, "X");
            $scaleX0 = $maxX0 - $minX0;
            $maxY1 = $this->getFaceDataMaxForKeyV2($sourceFaceData0, 1, "Y");
            $minY1 = $this->getFaceDataMinForKeyV2($sourceFaceData0, 1, "Y");
            $scaleY1 = $maxY1 - $minY1;
            $maxX1 = $this->getFaceDataMaxForKeyV2($sourceFaceData0, 1, "X");
            $minX1 = $this->getFaceDataMinForKeyV2($sourceFaceData0, 1, "X");
            $scaleX1 = $maxX1 - $minX1;*/

            for ($i = 0; $i < count($sourceFaceData0); $i++) {
                $leftEyePupilYMov = 0;
                if (isset($sourceFaceData0[$i][0])) {
                    $leftEyePupilYMov = $sourceFaceData0[$i][0]['Y'] - $yN0;
                    $leftEyePupilXMov = $sourceFaceData0[$i][0]['X'] - $xN0;
                }
                $rightEyePupilYMov = 0;
                if (isset($sourceFaceData0[$i][1])) {
                    $rightEyePupilYMov = $sourceFaceData0[$i][1]['Y'] - $yN1;
                    $rightEyePupilXMov = $sourceFaceData0[$i][1]['X'] - $xN1;
                }
                $leftEyePupilYMovForce = $this->getForce($leftEyeNWidthForIrises, abs($leftEyePupilYMov));
                $leftEyePupilXMovForce = $this->getForce($leftEyeNWidthForIrises, abs($leftEyePupilXMov));
                $rightEyePupilYMovForce = $this->getForce($rightEyeNWidthForIrises, abs($rightEyePupilYMov));
                $rightEyePupilXMovForce = $this->getForce($rightEyeNWidthForIrises, abs($rightEyePupilXMov));

                $targetFaceData[$facePart]['VALUES_REL']["left_eye_pupil_movement_x".$postFix]["max"] = $leftEyeNWidthForIrises;
                $targetFaceData[$facePart]['VALUES_REL']["left_eye_pupil_movement_x".$postFix]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["left_eye_pupil_movement_x".$postFix][$i]["delta"] = $leftEyePupilXMov;
                $targetFaceData[$facePart]['VALUES_REL']["left_eye_pupil_movement_y".$postFix]["max"] = $leftEyeNWidthForIrises;
                $targetFaceData[$facePart]['VALUES_REL']["left_eye_pupil_movement_y".$postFix]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["left_eye_pupil_movement_y".$postFix][$i]["delta"] = $leftEyePupilYMov;
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_pupil_movement_x".$postFix]["max"] = $rightEyeNWidthForIrises;
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_pupil_movement_x".$postFix]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_pupil_movement_x".$postFix][$i]["delta"] = $rightEyePupilXMov;
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_pupil_movement_y".$postFix]["max"] = $rightEyeNWidthForIrises;
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_pupil_movement_y".$postFix]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_pupil_movement_y".$postFix][$i]["delta"] = $rightEyePupilYMov;

                $targetFaceData[$facePart]["left_eye_pupil_movement_x".$postFix][$i]["force"] = $leftEyePupilXMovForce;
                $targetFaceData[$facePart]["left_eye_pupil_movement_y".$postFix][$i]["force"] = $leftEyePupilYMovForce;
                $targetFaceData[$facePart]["left_eye_pupil_movement_d".$postFix][$i]["force"] =
                    round(($leftEyePupilXMovForce + $leftEyePupilYMovForce)/2);
                $targetFaceData[$facePart]["right_eye_pupil_movement_x".$postFix][$i]["force"] = $rightEyePupilXMovForce;
                $targetFaceData[$facePart]["right_eye_pupil_movement_y".$postFix][$i]["force"] = $rightEyePupilYMovForce;
                $targetFaceData[$facePart]["right_eye_pupil_movement_d".$postFix][$i]["force"] =
                    round(($rightEyePupilYMovForce + $rightEyePupilXMovForce)/2);

                $xMov = 'none';
                if ($leftEyePupilYMov > 0) $yMov = 'down';
                if ($leftEyePupilYMov < 0) $yMov = 'up';
                if ($leftEyePupilYMov == 0) $yMov = 'none';
                if ($leftEyePupilXMov > 0) $xMov = 'right';
                if ($leftEyePupilXMov < 0) $xMov = 'left';

                $targetFaceData[$facePart]["left_eye_pupil_movement_x".$postFix][$i]["val"] = $xMov;
                $targetFaceData[$facePart]["left_eye_pupil_movement_y".$postFix][$i]["val"] = $yMov;
                $targetFaceData[$facePart]["left_eye_pupil_movement_d".$postFix][$i]["val"] = $yMov.' and '.$xMov;

                $xMov = 'none';
                if ($rightEyePupilYMov > 0) $yMov = 'down';
                if ($rightEyePupilYMov < 0) $yMov = 'up';
                if ($rightEyePupilYMov == 0) $yMov = 'none';
                if ($rightEyePupilXMov < 0) $xMov = 'left';
                if ($rightEyePupilXMov > 0) $xMov = 'right';

                $targetFaceData[$facePart]["right_eye_pupil_movement_x".$postFix][$i]["val"] = $xMov;
                $targetFaceData[$facePart]["right_eye_pupil_movement_y".$postFix][$i]["val"] = $yMov;
                $targetFaceData[$facePart]["right_eye_pupil_movement_d".$postFix][$i]["val"] = $yMov.' and '.$xMov;
            }
                       return $targetFaceData;
        } else return false;
    }

    /**
     * Обнаружение признаков лба по абсолютным координатам
     *
     * @param $sourceFaceData - входной массив с лицевыми точками (landmarks)
     * @return array - выходной массив с обработанным массивом для лба
     */
    public function detectBrowFeaturesByAbsCoordinates($sourceFaceData, $facePart){
        //анализируемые точки:
        // 19 (left_eyebrow_center),
        // 24 (right_eyebrow_center),

        //относительно точки 39 (уголок глаза)
        //изменение ширины лба по движению бровей
        // получение нормированного значения по кадру 0

        if (isset($sourceFaceData[0][19])
            && isset($sourceFaceData[0][24])
        ) {
            $facePart = $facePart.'_by_abs';

            $yN19 = $sourceFaceData[0][19]['Y'];
            $yN24 = $sourceFaceData[0][24]['Y'];
            $maxY19 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 19,"Y");
            $minY19 = $this->getFaceDataMinForKeyV2($sourceFaceData,19, "Y");
            $scaleY19 = $maxY19 - $minY19;
            $maxY24 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 24,"Y");
            $minY24 = $this->getFaceDataMinForKeyV2($sourceFaceData,24, "Y");
            $scaleY24 = $maxY24 - $minY24;

            for ($i = 0; $i < count($sourceFaceData); $i++) {
                if (isset($sourceFaceData[$i][19]) && $sourceFaceData[$i][24]){
                    $leftEyebrowMovement = $sourceFaceData[$i][19]['Y'] - $yN19;
                    $rightEyebrowMovement = $sourceFaceData[$i][24]['Y'] - $yN24;

                    $leftEyebrowMovementForce = $this->getForce($scaleY19, abs($leftEyebrowMovement));
                    $rightEyebrowMovementForce = $this->getForce($scaleY24, abs($rightEyebrowMovement));
                    $eyebrowMovementForce = round(($leftEyebrowMovementForce+$rightEyebrowMovementForce)/2); //среднее значение
                }
                $targetFaceData[$facePart]['VALUES_REL']["left_eye_brow_movement"]["max"] = $maxY19;
                $targetFaceData[$facePart]['VALUES_REL']["left_eye_brow_movement"]["min"] = $minY19;
                $targetFaceData[$facePart]['VALUES_REL']["left_eye_brow_movement"][$i]["delta"] = $leftEyebrowMovement;
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_brow_movement"]["max"] = $maxY24;
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_brow_movement"]["min"] = $minY24;
                $targetFaceData[$facePart]['VALUES_REL']["right_eye_brow_movement"][$i]["delta"] = $rightEyebrowMovement;

                $targetFaceData[$facePart]["brow_width"][$i]["force"] = $eyebrowMovementForce;
                if (($leftEyebrowMovement < 0)||($rightEyebrowMovement < 0)) $targetFaceData[$facePart]["brow_width"][$i]["val"] = '-';
                if (($leftEyebrowMovement > 0)||($rightEyebrowMovement > 0)) $targetFaceData[$facePart]["brow_width"][$i]["val"] = '+';
                if (($leftEyebrowMovement == 0)&&($rightEyebrowMovement == 0)) {
                    $targetFaceData[$facePart]["brow_width"][$i]["force"] = 0;
                    $targetFaceData[$facePart]["brow_width"][$i]["val"] = 'none';
                }
            }
            return $targetFaceData[$facePart];
        } else return false;
    }

     /**
     * Обнаружение признаков лба.
     *
     * @param $sourceFaceData - входной массив с лицевыми точками (landmarks)
     * @return array - выходной массив с обработанным массивом для лба
     */
    public function detectBrowFeatures($sourceFaceData, $facePart,$point1,$point2){
        //анализируемые точки:
        // 19 (left_eyebrow_center),
        // 24 (right_eyebrow_center),

        //относительно точки 39 (уголок глаза)
        //изменение ширины лба по движению бровей
        // получение нормированного значения по кадру 0

        if (isset($sourceFaceData[0][19])
            && isset($sourceFaceData[0][24])
            && isset($sourceFaceData[0][$point1])

        ) {
            $yN19 = $sourceFaceData[0][$point1]['Y'] - $sourceFaceData[0][19]['Y'];
            $yN24 = $sourceFaceData[0][$point1]['Y'] - $sourceFaceData[0][24]['Y'];

            $maxY19 = $this->getFaceDataMaxOnPoints($sourceFaceData, 19,"Y",$point1,$point2);
            $minY19 = $this->getFaceDataMinOnPoints($sourceFaceData,19, "Y",$point1,$point2);
            $scaleY19 = $maxY19 - $minY19;

            $maxY24 = $this->getFaceDataMaxOnPoints($sourceFaceData, 24,"Y",$point1,$point2);
            $minY24 = $this->getFaceDataMinOnPoints($sourceFaceData,24, "Y",$point1,$point2);
            $scaleY24 = $maxY24 - $minY24;


        for ($i = 0; $i < count($sourceFaceData); $i++) {
            if (isset($sourceFaceData[$i][19]) && $sourceFaceData[$i][24] && $sourceFaceData[$i][$point1]){
                $leftEyebrowMovement = $sourceFaceData[$i][$point1]['Y'] - $sourceFaceData[$i][19]['Y'] - $yN19;
                $rightEyebrowMovement = $sourceFaceData[$i][$point1]['Y'] - $sourceFaceData[$i][24]['Y'] - $yN24;

                $leftEyebrowMovementForce = $this->getForce($scaleY19, abs($leftEyebrowMovement));
                $rightEyebrowMovementForce = $this->getForce($scaleY24, abs($rightEyebrowMovement));
                $eyebrowMovementForce = round(($leftEyebrowMovementForce+$rightEyebrowMovementForce)/2); //среднее значение
            }
            $targetFaceData[$facePart]['VALUES_REL']["left_eye_brow_movement"]["max"] = $maxY19;
            $targetFaceData[$facePart]['VALUES_REL']["left_eye_brow_movement"]["min"] = $minY19;
            $targetFaceData[$facePart]['VALUES_REL']["left_eye_brow_movement"][$i]["delta"] = $leftEyebrowMovement;
            $targetFaceData[$facePart]['VALUES_REL']["left_eye_brow_movement"][$i]["val"] = $sourceFaceData[$i][$point1]['Y'] - $sourceFaceData[$i][19]['Y'];
            $targetFaceData[$facePart]['VALUES_REL']["right_eye_brow_movement"]["max"] = $maxY24;
            $targetFaceData[$facePart]['VALUES_REL']["right_eye_brow_movement"]["min"] = $minY24;
            $targetFaceData[$facePart]['VALUES_REL']["right_eye_brow_movement"][$i]["delta"] = $rightEyebrowMovement;
            $targetFaceData[$facePart]['VALUES_REL']["right_eye_brow_movement"][$i]["val"] = $sourceFaceData[$i][$point1]['Y'] - $sourceFaceData[$i][24]['Y'];

            $targetFaceData[$facePart]["brow_width"][$i]["force"] = $eyebrowMovementForce;
            if (($leftEyebrowMovement < 0)||($rightEyebrowMovement < 0)) $targetFaceData[$facePart]["brow_width"][$i]["val"] = '-';
            if (($leftEyebrowMovement > 0)||($rightEyebrowMovement > 0)) $targetFaceData[$facePart]["brow_width"][$i]["val"] = '+';
            if (($leftEyebrowMovement == 0)&&($rightEyebrowMovement == 0)) {
                $targetFaceData[$facePart]["brow_width"][$i]["force"] = 0;
                $targetFaceData[$facePart]["brow_width"][$i]["val"] = 'none';
            }
        }
        return $targetFaceData[$facePart];
        } else return false;
    }

    /**
     * Обнаружение признаков бровей по абсолютным координатам
     *
     * @param $sourceFaceData - входной массив с лицевыми точками (landmarks)
     * @return array - выходной массив с обработанным массивом для лба
     */
    public function detectEyeBrowFeaturesByAbsCoordinates($sourceFaceData, $facePart){
        //-------------------------------------------------------------------------------------
        //Анализируемые точки бровей: левая – 17, 19, 21, правая – 22, 24, 26.
        //Брови, движение бровей (вверх, вниз, к центру, к центру и вверх)
        // относительно центральной точке между внутренними уголками глаз 39 и 42
        //--------------------------------------------------------------------------------------

        if (isset($sourceFaceData[0][17])
            && isset($sourceFaceData[0][19])
            && isset($sourceFaceData[0][21])
            && isset($sourceFaceData[0][22])
            && isset($sourceFaceData[0][24])
            && isset($sourceFaceData[0][20])
            && isset($sourceFaceData[0][23])
            && isset($sourceFaceData[0][26])
            && isset($sourceFaceData[0][38])
            && isset($sourceFaceData[0][43])
        ) {
            $facePart = $facePart.'_by_abs';

            $yN17 = $sourceFaceData[0][17]['Y'];
            $xN17 = $sourceFaceData[0][17]['X'];
            $yN21 = $sourceFaceData[0][21]['Y'];
            $xN21 = $sourceFaceData[0][21]['X'];
            $yN22 = $sourceFaceData[0][22]['Y'];
            $xN22 = $sourceFaceData[0][22]['X'];
            $yN26 = $sourceFaceData[0][26]['Y'];
            $xN26 = $sourceFaceData[0][26]['X'];
            $yN19 = $sourceFaceData[0][19]['Y'];
            $xN19 = $sourceFaceData[0][19]['X'];
            $yN20 = $sourceFaceData[0][20]['Y'];
            $xN20 = $sourceFaceData[0][20]['X'];
            $yN23 = $sourceFaceData[0][23]['Y'];
            $xN23 = $sourceFaceData[0][23]['X'];
            $yN24 = $sourceFaceData[0][24]['Y'];
            $xN24 = $sourceFaceData[0][24]['X'];
            $yN38 = $sourceFaceData[0][38]['Y'];
            $yN43 = $sourceFaceData[0][43]['Y'];

            // интенсивность брови по вертикали – 100% - это длина отрезка от внешнего века глаза до середины брови.
            $maxLeftEyeBrow = ($sourceFaceData[0][38]['Y'] - $sourceFaceData[0][20]['Y']);
            $maxRightEyeBrow = ($sourceFaceData[0][43]['Y'] - $sourceFaceData[0][23]['Y']);
            // интенсивность брови по вертикали – 30% длины отрезка, соединяющего  внутренние точки бровей
            $maxXEyeBrow = round(0.3*($sourceFaceData[0][22]['X'] - $sourceFaceData[0][21]['X']));
//            $maxLeftEyeBrow = 2*($sourceFaceData[0][38]['Y'] - $sourceFaceData[0][20]['Y']);
//            $maxRightEyeBrow = 2*($sourceFaceData[0][43]['Y'] - $sourceFaceData[0][23]['Y']);
            //min - нормальное положение
            $scaleLeftEyeBrow = $maxLeftEyeBrow - ($yN20 - $yN38);
            $scaleRightEyeBrow = $maxRightEyeBrow - ($yN23 - $yN43);
            $scaleLeftXEyeBrow = $maxXEyeBrow - ($yN23 - $yN43);

            $maxY17 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 17, "Y");
            $minY17 = $this->getFaceDataMinForKeyV2($sourceFaceData, 17, "Y");
            $maxX17 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 17, "X");
            $minX17 = $this->getFaceDataMinForKeyV2($sourceFaceData, 17, "X");
            $scaleY17 = $maxY17 - $minY17;
            $scaleX17 = $maxX17 - $minX17;

            $maxY21 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 21, "Y");
            $minY21 = $this->getFaceDataMinForKeyV2($sourceFaceData, 21, "Y");
            $maxX21 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 21, "X");
            $minX21 = $this->getFaceDataMinForKeyV2($sourceFaceData, 21, "X");
            $scaleY21 = $maxY21 - $minY21;
            $scaleX21 = $maxX21 - $minX21;

            $maxY22 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 22, "Y");
            $minY22 = $this->getFaceDataMinForKeyV2($sourceFaceData, 22, "Y");
            $maxX22 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 22, "X");
            $minX22 = $this->getFaceDataMinForKeyV2($sourceFaceData, 22, "X");
            $scaleY22 = $maxY22 - $minY22;
            $scaleX22 = $maxX22 - $minX22;

            $maxY26 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 26, "Y");
            $minY26 = $this->getFaceDataMinForKeyV2($sourceFaceData, 26, "Y");
            $maxX26 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 26, "X");
            $minX26 = $this->getFaceDataMinForKeyV2($sourceFaceData, 26, "X");
            $scaleY26 = $maxY26 - $minY26;
            $scaleX26 = $maxX26 - $minX26;

            $maxY19 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 19, "Y");
            $minY19 = $this->getFaceDataMinForKeyV2($sourceFaceData, 19, "Y");
            $maxX19 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 19, "X");
            $minX19 = $this->getFaceDataMinForKeyV2($sourceFaceData, 19, "X");
            $scaleY19 = $maxY19 - $minY19;
            $scaleX19 = $maxX19 - $minX19;

            $maxY20 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 20, "Y");
            $minY20 = $this->getFaceDataMinForKeyV2($sourceFaceData, 20, "Y");
            $maxX20 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 20, "X");
            $minX20 = $this->getFaceDataMinForKeyV2($sourceFaceData, 20, "X");
            $scaleY20 = $maxY20 - $minY20;
            $scaleX20 = $maxX20 - $minX20;

            $maxY23 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 23, "Y");
            $minY23 = $this->getFaceDataMinForKeyV2($sourceFaceData, 23, "Y");
            $maxX23 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 23, "X");
            $minX23 = $this->getFaceDataMinForKeyV2($sourceFaceData, 23, "X");
            $scaleY23 = $maxY23 - $minY23;
            $scaleX23 = $maxX23 - $minX23;

            $maxY24 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 24, "Y");
            $minY24 = $this->getFaceDataMinForKeyV2($sourceFaceData, 24, "Y");
            $maxX24 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 24, "X");
            $minX24 = $this->getFaceDataMinForKeyV2($sourceFaceData, 24, "X");
            $scaleY24 = $maxY24 - $minY24;
            $scaleX24 = $maxX24 - $minX24;

            for ($i = 0; $i < count($sourceFaceData); $i++) {
                //eyebrow_line
                //Линия брови – отрезок [внешний уголок брови-XY, внутренний уголок брови-XY]
                //Движение брови-H-OUT = внешний уголок брови -Y – внешний уголок брови-YN
                //Движение брови-H-IN = внутренний уголок брови-Y – внутренний уголок брови-YN
                if (isset($sourceFaceData[$i][17]))
                    $leftEyebrowMovementHOut = $sourceFaceData[$i][17]['Y'] - $yN17;
                if (isset($sourceFaceData[$i][21])) {
                    $leftEyebrowMovementHIn = $sourceFaceData[$i][21]['Y'] - $yN21;
                    $leftEyebrowMovementXIn = $sourceFaceData[$i][21]['X'] - $xN21;
                }
                if (isset($sourceFaceData[$i][22])) {
                    $rightEyebrowMovementHIn = $sourceFaceData[$i][22]['Y'] - $yN22;
                    $rightEyebrowMovementXIn = $sourceFaceData[$i][22]['X'] - $xN22;
                }
                if (isset($sourceFaceData[$i][26]))
                    $rightEyebrowMovementHOut = $sourceFaceData[$i][26]['Y'] - $yN26;

                $leftEyebrowXMovForce = $this->getForce($maxXEyeBrow, abs($leftEyebrowMovementXIn));
                $leftEyebrowYMovForce = $this->getForce($maxLeftEyeBrow, abs($leftEyebrowMovementHIn));
//                $leftEyebrowXMovForce = $this->getForce($scaleX21, abs($leftEyebrowMovementXIn));
//                $leftEyebrowYMovForce = $this->getForce($scaleY21, abs($leftEyebrowMovementHIn));

                $targetFaceData[$facePart]['VALUES_REL']["left_eyebrow_inner_movement_x"]["max"] = $maxXEyeBrow;
                $targetFaceData[$facePart]['VALUES_REL']["left_eyebrow_inner_movement_x"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["left_eyebrow_inner_movement_x"][$i]["delta"] = $leftEyebrowMovementXIn;
                $targetFaceData[$facePart]['VALUES_REL']["left_eyebrow_inner_movement_y"]["max"] = $maxLeftEyeBrow;
                $targetFaceData[$facePart]['VALUES_REL']["left_eyebrow_inner_movement_y"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["left_eyebrow_inner_movement_y"][$i]["delta"] = $leftEyebrowMovementHIn;

                $targetFaceData[$facePart]["left_eyebrow_inner_movement_x"][$i]["force"] =
                    $leftEyebrowXMovForce;
                $targetFaceData[$facePart]["left_eyebrow_inner_movement_y"][$i]["force"] =
                    $leftEyebrowYMovForce;

                $targetFaceData[$facePart]["left_eyebrow_outer_movement"][$i]["force"] = $this->getForce(
                    $maxLeftEyeBrow, abs($leftEyebrowMovementHOut));
                //               $targetFaceData[$facePart]["left_eyebrow_outer_movement"][$i]["force"] = $this->getForce(
//                    $scaleY17, abs($leftEyebrowMovementHOut));

                $targetFaceData[$facePart]['VALUES_REL']["left_eyebrow_outer_movement"]["max"] = $maxLeftEyeBrow;
                $targetFaceData[$facePart]['VALUES_REL']["left_eyebrow_outer_movement"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["left_eyebrow_outer_movement"][$i]["delta"] = $leftEyebrowMovementHOut;

//                $rightEyebrowXMovForce = $this->getForce($scaleX22, abs($rightEyebrowMovementXIn));
//                $rightEyebrowYMovForce = $this->getForce($scaleY22, abs($rightEyebrowMovementHIn));
                $rightEyebrowXMovForce = $this->getForce($maxXEyeBrow, abs($rightEyebrowMovementXIn));
                $rightEyebrowYMovForce = $this->getForce($maxRightEyeBrow, abs($rightEyebrowMovementHIn));

                $targetFaceData[$facePart]['VALUES_REL']["right_eyebrow_inner_movement_x"]["max"] = $maxXEyeBrow;
                $targetFaceData[$facePart]['VALUES_REL']["right_eyebrow_inner_movement_x"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["right_eyebrow_inner_movement_x"][$i]["delta"] = $rightEyebrowMovementXIn;
                $targetFaceData[$facePart]['VALUES_REL']["right_eyebrow_inner_movement_y"]["max"] = $maxRightEyeBrow;
                $targetFaceData[$facePart]['VALUES_REL']["right_eyebrow_inner_movement_y"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["right_eyebrow_inner_movement_y"][$i]["delta"] = $rightEyebrowMovementHIn;

                $targetFaceData[$facePart]["right_eyebrow_inner_movement_x"][$i]["force"] =
                    $rightEyebrowXMovForce;
                $targetFaceData[$facePart]["right_eyebrow_inner_movement_y"][$i]["force"] =
                    $rightEyebrowYMovForce;

                //               $targetFaceData[$facePart]["right_eyebrow_outer_movement"][$i]["force"] = $this->getForce(
                //                   $scaleY26, abs($rightEyebrowMovementHOut));
                $targetFaceData[$facePart]["right_eyebrow_outer_movement"][$i]["force"] = $this->getForce(
                    $maxRightEyeBrow, abs($rightEyebrowMovementHOut));

                $targetFaceData[$facePart]['VALUES_REL']["right_eyebrow_outer_movement"]["max"] = $maxRightEyeBrow;
                $targetFaceData[$facePart]['VALUES_REL']["right_eyebrow_outer_movement"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["right_eyebrow_outer_movement"][$i]["delta"] = $rightEyebrowMovementHOut;

                $xMov = 'none';
                if ($leftEyebrowMovementHIn > 0) $yMov = 'down';
                if ($leftEyebrowMovementHIn < 0) $yMov = 'up';
                if ($leftEyebrowMovementHIn == 0) $yMov = 'none';
                if ($leftEyebrowMovementXIn > 0) $xMov = 'to center';
                if ($leftEyebrowMovementXIn < 0) $xMov = 'from center';

                $targetFaceData[$facePart]["left_eyebrow_inner_movement_x"][$i]["val"] = $xMov;
                $targetFaceData[$facePart]["left_eyebrow_inner_movement_y"][$i]["val"] = $yMov;

                if ($leftEyebrowMovementHOut > 0) $targetFaceData[$facePart]["left_eyebrow_outer_movement"][$i]["val"] = 'down';
                if ($leftEyebrowMovementHOut < 0) $targetFaceData[$facePart]["left_eyebrow_outer_movement"][$i]["val"] = 'up';
                if ($leftEyebrowMovementHOut == 0) {
                    $targetFaceData[$facePart]["left_eyebrow_outer_movement"][$i]["force"] = 0;
                    $targetFaceData[$facePart]["left_eyebrow_outer_movement"][$i]["val"] = 'none';
                }

                $xMov = 'none';
                if ($rightEyebrowMovementHIn > 0) $yMov = 'down';
                if ($rightEyebrowMovementHIn < 0) $yMov = 'up';
                if ($rightEyebrowMovementHIn == 0) $yMov = 'none';
                if ($rightEyebrowMovementXIn < 0) $xMov = 'to center';
                if ($rightEyebrowMovementXIn > 0) $xMov = 'from center';

                $targetFaceData[$facePart]["right_eyebrow_inner_movement_y"][$i]["val"] = $yMov;
                $targetFaceData[$facePart]["right_eyebrow_inner_movement_x"][$i]["val"] = $xMov;

                if ($rightEyebrowMovementHOut > 0) $targetFaceData[$facePart]["right_eyebrow_outer_movement"][$i]["val"] = 'down';
                if ($rightEyebrowMovementHOut < 0) $targetFaceData[$facePart]["right_eyebrow_outer_movement"][$i]["val"] = 'up';
                if ($rightEyebrowMovementHOut == 0) {
                    $targetFaceData[$facePart]["right_eyebrow_outer_movement"][$i]["force"] = 0;
                    $targetFaceData[$facePart]["right_eyebrow_outer_movement"][$i]["val"] = 'none';
                }

                //определяем движение брови по движению верхних точек бровей
                // 20 - левая бровь, 23 - правая бровь
                if (isset($sourceFaceData[$i])) {
                    $rightEyebrowMovementY = $sourceFaceData[$i][23]['Y'] - $yN23;
                    $rightEyebrowMovementX = $sourceFaceData[$i][23]['X'] - $xN23;
                    $leftEyebrowMovementY = $sourceFaceData[$i][20]['Y'] - $yN20;
                    $leftEyebrowMovementX = $sourceFaceData[$i][20]['X'] - $xN20;
                } else {
                    $rightEyebrowMovementY = 0;
                    $rightEyebrowMovementX = 0;
                    $leftEyebrowMovementY = 0;
                    $leftEyebrowMovementX = 0;
                }
//                $rightEyebrowXMovForce = $this->getForce($scaleX23, abs($rightEyebrowMovementX));
                $rightEyebrowXMovForce = $this->getForce($maxXEyeBrow, abs($rightEyebrowMovementX));
                $rightEyebrowYMovForce = $this->getForce($maxRightEyeBrow, abs($rightEyebrowMovementY));

                $targetFaceData[$facePart]['VALUES_REL']["right_eyebrow_movement_x"]["max"] = $maxXEyeBrow;
                $targetFaceData[$facePart]['VALUES_REL']["right_eyebrow_movement_x"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["right_eyebrow_movement_x"][$i]["delta"] = $rightEyebrowMovementX;
                $targetFaceData[$facePart]['VALUES_REL']["right_eyebrow_movement_y"]["max"] = $maxRightEyeBrow;
                $targetFaceData[$facePart]['VALUES_REL']["right_eyebrow_movement_y"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["right_eyebrow_movement_y"][$i]["delta"] = $rightEyebrowMovementY;

//                $targetFaceData["eyebrow"]["right_eyebrow_movement"][$i]["force"] =
//                    round(($rightEyebrowXMovForce + $rightEyebrowYMovForce) / 2);
                $targetFaceData[$facePart]["right_eyebrow_movement_x"][$i]["force"] =
                    $rightEyebrowXMovForce;
                $targetFaceData[$facePart]["right_eyebrow_movement_y"][$i]["force"] =
                    $rightEyebrowYMovForce;
//                echo $i.' $leftEyebrowMovementY:'.$leftEyebrowMovementY.'<br>';

//                $leftEyebrowXMovForce = $this->getForce($scaleX20, abs($leftEyebrowMovementX));
                $leftEyebrowXMovForce = $this->getForce($maxXEyeBrow, abs($leftEyebrowMovementX));
                $leftEyebrowYMovForce = $this->getForce($maxLeftEyeBrow, abs($leftEyebrowMovementY));

                $targetFaceData[$facePart]['VALUES_REL']["left_eyebrow_movement_x"]["max"] = $maxXEyeBrow;
                $targetFaceData[$facePart]['VALUES_REL']["left_eyebrow_movement_x"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["left_eyebrow_movement_x"][$i]["delta"] = $leftEyebrowMovementX;
                $targetFaceData[$facePart]['VALUES_REL']["left_eyebrow_movement_y"]["max"] = $maxLeftEyeBrow;
                $targetFaceData[$facePart]['VALUES_REL']["left_eyebrow_movement_y"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["left_eyebrow_movement_y"][$i]["delta"] = $leftEyebrowMovementY;

//                $targetFaceData["eyebrow"]["left_eyebrow_movement"][$i]["force"] =
//                    round(($leftEyebrowXMovForce + $leftEyebrowYMovForce) / 2);
                $targetFaceData[$facePart]["left_eyebrow_movement_x"][$i]["force"] =
                    $leftEyebrowXMovForce;
                $targetFaceData[$facePart]["left_eyebrow_movement_y"][$i]["force"] =
                    $leftEyebrowYMovForce;

                $xMov = 'none';
                if ($leftEyebrowMovementY > 0) $yMov = 'down';
                if ($leftEyebrowMovementY < 0) $yMov = 'up';
                if ($leftEyebrowMovementY == 0) $yMov = 'none';
                if ($leftEyebrowMovementX > 0) $xMov = 'to center';
                if ($leftEyebrowMovementX < 0) $xMov = 'from center';

                $targetFaceData[$facePart]["left_eyebrow_movement_x"][$i]["val"] = $xMov;
                $targetFaceData[$facePart]["left_eyebrow_movement_y"][$i]["val"] = $yMov;

                $xMov = 'none';
                if ($rightEyebrowMovementY > 0) $yMov = 'down';
                if ($rightEyebrowMovementY < 0) $yMov = 'up';
                if ($rightEyebrowMovementY == 0) $yMov = 'none';
                if ($rightEyebrowMovementX < 0) $xMov = 'to center';
                if ($rightEyebrowMovementX > 0) $xMov = 'from center';

                $targetFaceData[$facePart]["right_eyebrow_movement_x"][$i]["val"] = $xMov;
                $targetFaceData[$facePart]["right_eyebrow_movement_y"][$i]["val"] = $yMov;
            }
            return $targetFaceData[$facePart];
        }
        else return false;
    }

    /**
     * Обнаружение признаков бровей.
     *
     * @param $sourceFaceData - входной массив с лицевыми точками (landmarks)
     * @return array - выходной массив с обработанным массивом для лба
     */
    public function detectEyeBrowFeatures($sourceFaceData, $facePart, $point1,$point2){
        //-------------------------------------------------------------------------------------
        //Анализируемые точки бровей: левая – 17, 19, 21, правая – 22, 24, 26.
        //Брови, движение бровей (вверх, вниз, к центру, к центру и вверх)
        // относительно центральной точке между внутренними уголками глаз 39 и 42
        //--------------------------------------------------------------------------------------

        if (isset($sourceFaceData[0][17])
            && isset($sourceFaceData[0][19])
            && isset($sourceFaceData[0][21])
            && isset($sourceFaceData[0][22])
            && isset($sourceFaceData[0][24])
            && isset($sourceFaceData[0][20])
            && isset($sourceFaceData[0][23])
            && isset($sourceFaceData[0][26])
            && isset($sourceFaceData[0][38])
            && isset($sourceFaceData[0][43])
            && isset($sourceFaceData[0][$point1])
            && isset($sourceFaceData[0][$point2])
        ) {
            $midNY3942 = round(($sourceFaceData[0][$point2]['Y'] - $sourceFaceData[0][$point1]['Y'])/2) +
                $sourceFaceData[0][$point1]['Y'];
            $midNX3942 = round(($sourceFaceData[0][$point2]['X'] - $sourceFaceData[0][$point1]['X'])/2) +
                $sourceFaceData[0][$point1]['X'];

            $yN17 = $sourceFaceData[0][17]['Y'] - $midNY3942;
            $xN17 = $sourceFaceData[0][17]['X'] - $midNX3942;
            $yN21 = $sourceFaceData[0][21]['Y'] - $midNY3942;
            $xN21 = $sourceFaceData[0][21]['X'] - $midNX3942;
            $yN22 = $sourceFaceData[0][22]['Y'] - $midNY3942;
            $xN22 = $sourceFaceData[0][22]['X'] - $midNX3942;
            $yN26 = $sourceFaceData[0][26]['Y'] - $midNY3942;
            $xN26 = $sourceFaceData[0][26]['X'] - $midNX3942;
            $yN19 = $sourceFaceData[0][19]['Y'] - $midNY3942;
            $xN19 = $sourceFaceData[0][19]['X'] - $midNX3942;
            $yN20 = $sourceFaceData[0][20]['Y'] - $midNY3942;
            $xN20 = $sourceFaceData[0][20]['X'] - $midNX3942;
            $yN23 = $sourceFaceData[0][23]['Y'] - $midNY3942;
            $xN23 = $sourceFaceData[0][23]['X'] - $midNX3942;
            $yN24 = $sourceFaceData[0][24]['Y'] - $midNY3942;
            $xN24 = $sourceFaceData[0][24]['X'] - $midNX3942;
            $yN38 = $sourceFaceData[0][38]['Y'] - $midNY3942;
            $yN43 = $sourceFaceData[0][43]['Y'] - $midNY3942;

            // интенсивность брови по вертикали – 100% - это длина отрезка от внешнего века глаза до середины брови.
            $maxLeftEyeBrow = ($sourceFaceData[0][38]['Y'] - $sourceFaceData[0][20]['Y']);
            $maxRightEyeBrow = ($sourceFaceData[0][43]['Y'] - $sourceFaceData[0][23]['Y']);
            // интенсивность брови по горизонтали – 30% длины отрезка, соединяющего  внутренние точки бровей
            $maxXEyeBrow = round(0.3*($sourceFaceData[0][22]['X'] - $sourceFaceData[0][21]['X']));

            for ($i = 0; $i < count($sourceFaceData); $i++) {
                if (isset($sourceFaceData[$i][$point2]) && isset($sourceFaceData[$i][$point1])){
                    $midY3942 = round(($sourceFaceData[$i][$point2]['Y'] - $sourceFaceData[$i][$point1]['Y'])/2) +
                        $sourceFaceData[$i][$point1]['Y'];
                    $midX3942 = round(($sourceFaceData[$i][$point2]['X'] - $sourceFaceData[$i][$point1]['X'])/2) +
                        $sourceFaceData[$i][$point1]['X'];
                }
                //eyebrow_line
                //Линия брови – отрезок [внешний уголок брови-XY, внутренний уголок брови-XY]
                //Движение брови-H-OUT = внешний уголок брови -Y – внешний уголок брови-YN
                //Движение брови-H-IN = внутренний уголок брови-Y – внутренний уголок брови-YN
                if (isset($sourceFaceData[$i][17]))
                    $leftEyebrowMovementHOut = $sourceFaceData[$i][17]['Y'] - $yN17 - $midY3942;
                if (isset($sourceFaceData[$i][21])) {
                    $leftEyebrowMovementHIn = $sourceFaceData[$i][21]['Y'] - $yN21 - $midY3942;
                    $leftEyebrowMovementXIn = $sourceFaceData[$i][21]['X'] - $xN21 - $midX3942;
                }
                if (isset($sourceFaceData[$i][22])) {
                    $rightEyebrowMovementHIn = $sourceFaceData[$i][22]['Y'] - $yN22 - $midY3942;
                    $rightEyebrowMovementXIn = $sourceFaceData[$i][22]['X'] - $xN22 - $midX3942;
                }
                if (isset($sourceFaceData[$i][26]))
                    $rightEyebrowMovementHOut = $sourceFaceData[$i][26]['Y'] - $yN26 - $midY3942;

                $leftEyebrowXMovForce = $this->getForce($maxXEyeBrow, abs($leftEyebrowMovementXIn));
                $leftEyebrowYMovForce = $this->getForce($maxLeftEyeBrow, abs($leftEyebrowMovementHIn));

                $targetFaceData[$facePart]['VALUES_REL']["left_eyebrow_inner_movement_x"]["max"] = $maxXEyeBrow;
                $targetFaceData[$facePart]['VALUES_REL']["left_eyebrow_inner_movement_x"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["left_eyebrow_inner_movement_x"][$i]["delta"] = $leftEyebrowMovementXIn;
                $targetFaceData[$facePart]['VALUES_REL']["left_eyebrow_inner_movement_x"][$i]["val"] = $sourceFaceData[$i][21]['X'] - $midX3942;
                $targetFaceData[$facePart]['VALUES_REL']["left_eyebrow_inner_movement_y"]["max"] = $maxLeftEyeBrow;
                $targetFaceData[$facePart]['VALUES_REL']["left_eyebrow_inner_movement_y"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["left_eyebrow_inner_movement_y"][$i]["delta"] = $leftEyebrowMovementHIn;
                $targetFaceData[$facePart]['VALUES_REL']["left_eyebrow_inner_movement_y"][$i]["val"] = $sourceFaceData[$i][21]['Y'] - $midY3942;

                $targetFaceData[$facePart]["left_eyebrow_inner_movement_x"][$i]["force"] =
                   $leftEyebrowXMovForce;
                $targetFaceData[$facePart]["left_eyebrow_inner_movement_y"][$i]["force"] =
                    $leftEyebrowYMovForce;

                $targetFaceData[$facePart]["left_eyebrow_outer_movement"][$i]["force"] = $this->getForce(
                    $maxLeftEyeBrow, abs($leftEyebrowMovementHOut));

                $targetFaceData[$facePart]['VALUES_REL']["left_eyebrow_outer_movement"]["max"] = $maxLeftEyeBrow;
                $targetFaceData[$facePart]['VALUES_REL']["left_eyebrow_outer_movement"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["left_eyebrow_outer_movement"][$i]["delta"] = $leftEyebrowMovementHOut;
                $targetFaceData[$facePart]['VALUES_REL']["left_eyebrow_outer_movement"][$i]["val"] = $sourceFaceData[$i][17]['Y'] - $midY3942;

                $rightEyebrowXMovForce = $this->getForce($maxXEyeBrow, abs($rightEyebrowMovementXIn));
                $rightEyebrowYMovForce = $this->getForce($maxRightEyeBrow, abs($rightEyebrowMovementHIn));

                $targetFaceData[$facePart]['VALUES_REL']["right_eyebrow_inner_movement_x"]["max"] = $maxXEyeBrow;
                $targetFaceData[$facePart]['VALUES_REL']["right_eyebrow_inner_movement_x"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["right_eyebrow_inner_movement_x"][$i]["delta"] = $rightEyebrowMovementXIn;
                $targetFaceData[$facePart]['VALUES_REL']["right_eyebrow_inner_movement_x"][$i]["val"] = $sourceFaceData[$i][22]['X'] - $midX3942;
                $targetFaceData[$facePart]['VALUES_REL']["right_eyebrow_inner_movement_y"]["max"] = $maxRightEyeBrow;
                $targetFaceData[$facePart]['VALUES_REL']["right_eyebrow_inner_movement_y"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["right_eyebrow_inner_movement_y"][$i]["delta"] = $rightEyebrowMovementHIn;
                $targetFaceData[$facePart]['VALUES_REL']["right_eyebrow_inner_movement_y"][$i]["val"] = $sourceFaceData[$i][22]['Y'] - $midY3942;

                $targetFaceData[$facePart]["right_eyebrow_inner_movement_x"][$i]["force"] =
                    $rightEyebrowXMovForce;
                $targetFaceData[$facePart]["right_eyebrow_inner_movement_y"][$i]["force"] =
                    $rightEyebrowYMovForce;

                $targetFaceData[$facePart]["right_eyebrow_outer_movement"][$i]["force"] = $this->getForce(
                    $maxRightEyeBrow, abs($rightEyebrowMovementHOut));

                $targetFaceData[$facePart]['VALUES_REL']["right_eyebrow_outer_movement"]["max"] = $maxRightEyeBrow;
                $targetFaceData[$facePart]['VALUES_REL']["right_eyebrow_outer_movement"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["right_eyebrow_outer_movement"][$i]["delta"] = $rightEyebrowMovementHOut;
                $targetFaceData[$facePart]['VALUES_REL']["right_eyebrow_outer_movement"][$i]["val"] = $sourceFaceData[$i][26]['Y'] - $midY3942;

                $xMov = 'none';
                if ($leftEyebrowMovementHIn > 0) $yMov = 'down';
                if ($leftEyebrowMovementHIn < 0) $yMov = 'up';
                if ($leftEyebrowMovementHIn == 0) $yMov = 'none';
                if ($leftEyebrowMovementXIn > 0) $xMov = 'to center';
                if ($leftEyebrowMovementXIn < 0) $xMov = 'from center';

                $targetFaceData[$facePart]["left_eyebrow_inner_movement_x"][$i]["val"] = $xMov;
                $targetFaceData[$facePart]["left_eyebrow_inner_movement_y"][$i]["val"] = $yMov;

                if ($leftEyebrowMovementHOut > 0) $targetFaceData[$facePart]["left_eyebrow_outer_movement"][$i]["val"] = 'down';
                if ($leftEyebrowMovementHOut < 0) $targetFaceData[$facePart]["left_eyebrow_outer_movement"][$i]["val"] = 'up';
                if ($leftEyebrowMovementHOut == 0) {
                    $targetFaceData[$facePart]["left_eyebrow_outer_movement"][$i]["force"] = 0;
                    $targetFaceData[$facePart]["left_eyebrow_outer_movement"][$i]["val"] = 'none';
                }

                $xMov = 'none';
                if ($rightEyebrowMovementHIn > 0) $yMov = 'down';
                if ($rightEyebrowMovementHIn < 0) $yMov = 'up';
                if ($rightEyebrowMovementHIn == 0) $yMov = 'none';
                if ($rightEyebrowMovementXIn < 0) $xMov = 'to center';
                if ($rightEyebrowMovementXIn > 0) $xMov = 'from center';

                $targetFaceData[$facePart]["right_eyebrow_inner_movement_y"][$i]["val"] = $yMov;
                $targetFaceData[$facePart]["right_eyebrow_inner_movement_x"][$i]["val"] = $xMov;

                if ($rightEyebrowMovementHOut > 0) $targetFaceData[$facePart]["right_eyebrow_outer_movement"][$i]["val"] = 'down';
                if ($rightEyebrowMovementHOut < 0) $targetFaceData[$facePart]["right_eyebrow_outer_movement"][$i]["val"] = 'up';
                if ($rightEyebrowMovementHOut == 0) {
                    $targetFaceData[$facePart]["right_eyebrow_outer_movement"][$i]["force"] = 0;
                    $targetFaceData[$facePart]["right_eyebrow_outer_movement"][$i]["val"] = 'none';
                }

                //определяем движение брови по движению верхних точек бровей
                // 20 - левая бровь, 23 - правая бровь
                if (isset($sourceFaceData[$i])) {
                    $rightEyebrowMovementY = $sourceFaceData[$i][23]['Y'] - $yN23 - $midY3942;
                    $rightEyebrowMovementX = $sourceFaceData[$i][23]['X'] - $xN23 - $midX3942;
                    $leftEyebrowMovementY = $sourceFaceData[$i][20]['Y'] - $yN20 - $midY3942;
                    $leftEyebrowMovementX = $sourceFaceData[$i][20]['X'] - $xN20 - $midX3942;
                } else {
                    $rightEyebrowMovementY = 0;
                    $rightEyebrowMovementX = 0;
                    $leftEyebrowMovementY = 0;
                    $leftEyebrowMovementX = 0;
                }
//                $rightEyebrowXMovForce = $this->getForce($scaleX23, abs($rightEyebrowMovementX));
                $rightEyebrowXMovForce = $this->getForce($maxXEyeBrow, abs($rightEyebrowMovementX));
                $rightEyebrowYMovForce = $this->getForce($maxRightEyeBrow, abs($rightEyebrowMovementY));

                $targetFaceData[$facePart]['VALUES_REL']["right_eyebrow_movement_x"]["max"] = $maxXEyeBrow;
                $targetFaceData[$facePart]['VALUES_REL']["right_eyebrow_movement_x"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["right_eyebrow_movement_x"][$i]["delta"] = $rightEyebrowMovementX;
                $targetFaceData[$facePart]['VALUES_REL']["right_eyebrow_movement_x"][$i]["val"] = $sourceFaceData[$i][23]['X'] - $midX3942;
                $targetFaceData[$facePart]['VALUES_REL']["right_eyebrow_movement_y"]["max"] = $maxRightEyeBrow;
                $targetFaceData[$facePart]['VALUES_REL']["right_eyebrow_movement_y"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["right_eyebrow_movement_y"][$i]["delta"] = $rightEyebrowMovementY;
                $targetFaceData[$facePart]['VALUES_REL']["right_eyebrow_movement_y"][$i]["val"] = $sourceFaceData[$i][23]['Y'] - $midY3942;

                $targetFaceData[$facePart]["right_eyebrow_movement_x"][$i]["force"] =
                    $rightEyebrowXMovForce;
                $targetFaceData[$facePart]["right_eyebrow_movement_y"][$i]["force"] =
                    $rightEyebrowYMovForce;

                $leftEyebrowXMovForce = $this->getForce($maxXEyeBrow, abs($leftEyebrowMovementX));
                $leftEyebrowYMovForce = $this->getForce($maxLeftEyeBrow, abs($leftEyebrowMovementY));

                $targetFaceData[$facePart]['VALUES_REL']["left_eyebrow_movement_x"]["max"] = $maxXEyeBrow;
                $targetFaceData[$facePart]['VALUES_REL']["left_eyebrow_movement_x"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["left_eyebrow_movement_x"][$i]["delta"] = $leftEyebrowMovementX;
                $targetFaceData[$facePart]['VALUES_REL']["left_eyebrow_movement_x"][$i]["val"] = $sourceFaceData[$i][20]['X'] - $midX3942;
                $targetFaceData[$facePart]['VALUES_REL']["left_eyebrow_movement_y"]["max"] = $maxLeftEyeBrow;
                $targetFaceData[$facePart]['VALUES_REL']["left_eyebrow_movement_y"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["left_eyebrow_movement_y"][$i]["delta"] = $leftEyebrowMovementY;
                $targetFaceData[$facePart]['VALUES_REL']["left_eyebrow_movement_y"][$i]["val"] = $sourceFaceData[$i][20]['Y'] - $midY3942;

//                $targetFaceData["eyebrow"]["left_eyebrow_movement"][$i]["force"] =
//                    round(($leftEyebrowXMovForce + $leftEyebrowYMovForce) / 2);
                $targetFaceData[$facePart]["left_eyebrow_movement_x"][$i]["force"] =
                    $leftEyebrowXMovForce;
                $targetFaceData[$facePart]["left_eyebrow_movement_y"][$i]["force"] =
                    $leftEyebrowYMovForce;

                $xMov = 'none';
                if ($leftEyebrowMovementY > 0) $yMov = 'down';
                if ($leftEyebrowMovementY < 0) $yMov = 'up';
                if ($leftEyebrowMovementY == 0) $yMov = 'none';
                if ($leftEyebrowMovementX > 0) $xMov = 'to center';
                if ($leftEyebrowMovementX < 0) $xMov = 'from center';

                $targetFaceData[$facePart]["left_eyebrow_movement_x"][$i]["val"] = $xMov;
                $targetFaceData[$facePart]["left_eyebrow_movement_y"][$i]["val"] = $yMov;

                $xMov = 'none';
                if ($rightEyebrowMovementY > 0) $yMov = 'down';
                if ($rightEyebrowMovementY < 0) $yMov = 'up';
                if ($rightEyebrowMovementY == 0) $yMov = 'none';
                if ($rightEyebrowMovementX < 0) $xMov = 'to center';
                if ($rightEyebrowMovementX > 0) $xMov = 'from center';

                $targetFaceData[$facePart]["right_eyebrow_movement_x"][$i]["val"] = $xMov;
                $targetFaceData[$facePart]["right_eyebrow_movement_y"][$i]["val"] = $yMov;
            }
            return $targetFaceData[$facePart];
        }
        else return false;
    }

    /**
     * Обнаружение признаков рта.
     *
     * @param $sourceFaceData - входной массив с лицевыми точками (landmarks)
     * @return array - выходной массив с обработанным массивом для глаза
     */
    public function detectMouthFeaturesByAbsCoordinates($sourceFaceData, $facePart)
    {
        if (isset($sourceFaceData[0][48])) $normFrameIndex = 0;
        else $normFrameIndex = 1;

        if (isset($sourceFaceData[$normFrameIndex][48])
            && isset($sourceFaceData[$normFrameIndex][54])
            && isset($sourceFaceData[$normFrameIndex][51])
            && isset($sourceFaceData[$normFrameIndex][57])
            && isset($sourceFaceData[$normFrameIndex][67])
            && isset($sourceFaceData[$normFrameIndex][66])
            && isset($sourceFaceData[$normFrameIndex][65])
            && isset($sourceFaceData[$normFrameIndex][61])
            && isset($sourceFaceData[$normFrameIndex][62])
            && isset($sourceFaceData[$normFrameIndex][63])
            && isset($sourceFaceData[$normFrameIndex][67])
        ) {
            $facePart = $facePart.'_by_abs';

            $xN48 = $sourceFaceData[$normFrameIndex][48]['X'];
            $xN54 = $sourceFaceData[$normFrameIndex][54]['X'];
            $yN48 = $sourceFaceData[$normFrameIndex][48]['Y'];
            $yN54 = $sourceFaceData[$normFrameIndex][54]['Y'];
            $mouthLengthN = $xN54 - $xN48;
            //Рот – 100% - круг с диаметром длиной рта в нормальном состоянии + 25%
            $maxMouthLength = round($mouthLengthN*1.25);

            $yN51 = $sourceFaceData[$normFrameIndex][51]['Y'];
            $yN57 = $sourceFaceData[$normFrameIndex][57]['Y'];
            $mouthWidthN = $yN57 - $yN51;


            $maxX48 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 48, "X");
            $minX48 = $this->getFaceDataMinForKeyV2($sourceFaceData, 48, "X");
            $scaleX48 = $maxX48 - $minX48;
            $maxY48 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 48, "Y");
            $minY48 = $this->getFaceDataMinForKeyV2($sourceFaceData, 48, "Y");
            $scaleY48 = $maxY48 - $minY48;
            $maxX54 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 54, "X");
            $minX54 = $this->getFaceDataMinForKeyV2($sourceFaceData, 54, "X");
            $scaleX54 = $maxX54 - $minX54;
            $maxY54 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 54, "Y");
            $minY54 = $this->getFaceDataMinForKeyV2($sourceFaceData, 54, "Y");
            $scaleY54 = $maxY54 - $minY54;
//            $maxMouthLength = $maxX54 - $minX48;
//            $minMouthLength = $minX54 - $maxX48;
//            $scaleMouthLength = $maxMouthLength - $minMouthLength;
            $scaleMouthLength = $maxMouthLength - $mouthLengthN;

            $maxY51 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 51, "Y");
            $minY51 = $this->getFaceDataMinForKeyV2($sourceFaceData, 51, "Y");
            $scaleY51 = $maxY51 - $minY51;
            $maxY57 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 57, "Y");
            $minY57 = $this->getFaceDataMinForKeyV2($sourceFaceData, 57, "Y");
            $scaleY57 = $maxY57 - $minY57;
//            $maxMouthWidth = $maxY57 - $minY51;
//            $minMouthWidth = $minY57 - $maxY51;
//            $scaleMouthWidth = $maxMouthWidth - $minMouthWidth;
            $scaleMouthWidth = $maxMouthLength - $mouthWidthN;

            // изменение длины рта
            // NORM_POINTS 48 54
            // echo $FaceData_['normmask'][0][48][X];
            for ($i = 0; $i < count($sourceFaceData); $i++) {

                if ((isset($sourceFaceData[$i][48]))
                ) {
                    $leftMouthCornerXMov = $sourceFaceData[$i][48]['X'] - $xN48;
                    $leftMouthCornerYMov = $sourceFaceData[$i][48]['Y'] - $yN48;

                    $leftMouthCornerXMovForce = $this->getForce($scaleMouthLength, abs($leftMouthCornerXMov));
                    $leftMouthCornerYMovForce = $this->getForce($scaleMouthWidth, abs($leftMouthCornerYMov));

                    $targetFaceData[$facePart]['VALUES_REL']["left_corner_mouth_movement_x"]["max"] = $maxMouthLength;
                    $targetFaceData[$facePart]['VALUES_REL']["left_corner_mouth_movement_x"]["min"] = $mouthLengthN;
                    $targetFaceData[$facePart]['VALUES_REL']["left_corner_mouth_movement_x"][$i]["delta"] = $leftMouthCornerXMov;
                    $targetFaceData[$facePart]['VALUES_REL']["left_corner_mouth_movement_y"]["max"] = $maxMouthLength;
                    $targetFaceData[$facePart]['VALUES_REL']["left_corner_mouth_movement_y"]["min"] = $mouthWidthN;
                    $targetFaceData[$facePart]['VALUES_REL']["left_corner_mouth_movement_y"][$i]["delta"] = $leftMouthCornerYMov;

                    $targetFaceData[$facePart]["left_corner_mouth_movement_x"][$i]["force"] = $leftMouthCornerXMovForce;
                    $targetFaceData[$facePart]["left_corner_mouth_movement_y"][$i]["force"] = $leftMouthCornerYMovForce;

                    $yMov = '';
                    if ($leftMouthCornerYMov < 0) $yMov = 'up';
                    if ($leftMouthCornerYMov > 0) $yMov = 'down';
                    if ($leftMouthCornerXMov < 0) $xMov = 'from center';
                    else $xMov = 'to center';

                    $targetFaceData[$facePart]["left_corner_mouth_movement_x"][$i]["val"] = $xMov;
                    $targetFaceData[$facePart]["left_corner_mouth_movement_y"][$i]["val"] = $yMov;
                    if ($targetFaceData[$facePart]["left_corner_mouth_movement_x"][$i]["force"] == 0)
                        $targetFaceData[$facePart]["left_corner_mouth_movement_x"][$i]["val"] = 'none';
                    if ($targetFaceData[$facePart]["left_corner_mouth_movement_y"][$i]["force"] == 0)
                        $targetFaceData[$facePart]["left_corner_mouth_movement_y"][$i]["val"] = 'none';
                }

                if (isset($sourceFaceData[$i][54])) {
                    $rightMouthCornerXMov = $sourceFaceData[$i][54]['X'] - $xN54;
                    $rightMouthCornerYMov = $sourceFaceData[$i][54]['Y'] - $yN54;

                    $rightMouthCornerXMovForce = $this->getForce($scaleMouthLength, abs($rightMouthCornerXMov));
                    $rightMouthCornerYMovForce = $this->getForce($scaleMouthWidth, abs($rightMouthCornerYMov));

                    $targetFaceData[$facePart]['VALUES_REL']["right_corner_mouth_movement_x"]["max"] = $maxMouthLength;
                    $targetFaceData[$facePart]['VALUES_REL']["right_corner_mouth_movement_x"]["min"] = $mouthLengthN;
                    $targetFaceData[$facePart]['VALUES_REL']["right_corner_mouth_movement_x"][$i]["delta"] = $rightMouthCornerXMov;
                    $targetFaceData[$facePart]['VALUES_REL']["right_corner_mouth_movement_y"]["max"] = $maxMouthLength;
                    $targetFaceData[$facePart]['VALUES_REL']["right_corner_mouth_movement_y"]["min"] = $mouthWidthN;
                    $targetFaceData[$facePart]['VALUES_REL']["right_corner_mouth_movement_y"][$i]["delta"] = $rightMouthCornerYMov;

                    $targetFaceData[$facePart]["right_corner_mouth_movement_x"][$i]["force"] = $rightMouthCornerXMovForce;
                    $targetFaceData[$facePart]["right_corner_mouth_movement_y"][$i]["force"] = $rightMouthCornerYMovForce;

                    if (isset($sourceFaceData[$i][48])) {
                        $mouthLength = $sourceFaceData[$i][54]['X'] - $sourceFaceData[$i][48]['X'];
                    }
                    if ($rightMouthCornerYMov < 0) $yMov = 'up';
                    if ($rightMouthCornerYMov > 0) $yMov = 'down';
                    if ($rightMouthCornerXMov > 0) $xMov = 'from center';
                    else $xMov = 'to center';

                    $targetFaceData[$facePart]["right_corner_mouth_movement_x"][$i]["val"] = $xMov;
                    $targetFaceData[$facePart]["right_corner_mouth_movement_y"][$i]["val"] = $yMov;
                    if ($targetFaceData[$facePart]["right_corner_mouth_movement_x"][$i]["force"] == 0)
                        $targetFaceData[$facePart]["right_corner_mouth_movement_x"][$i]["val"] = 'none';
                    if ($targetFaceData[$facePart]["right_corner_mouth_movement_y"][$i]["force"] == 0)
                        $targetFaceData[$facePart]["right_corner_mouth_movement_y"][$i]["val"] = 'none';
                }

                //движение уголков рта
                $xMov = 'none';
                if (isset($targetFaceData[$facePart]["left_corner_mouth_movement_x"][$i]) &&
                    ($targetFaceData[$facePart]["right_corner_mouth_movement_x"][$i]["val"] ==
                        $targetFaceData[$facePart]["left_corner_mouth_movement_x"][$i]["val"])) {
                    $xMov = $targetFaceData[$facePart]["right_corner_mouth_movement_x"][$i]["val"];
                }
                $targetFaceData[$facePart]["mouth_corners_movement"][$i]["val"] = $xMov;
                $mouthLengthX = abs($mouthLength - $mouthLengthN);

                $targetFaceData[$facePart]['VALUES_REL']["mouth_length"]["max"] = $maxMouthLength;
                $targetFaceData[$facePart]['VALUES_REL']["mouth_length"]["min"] = $mouthLengthN;
                $targetFaceData[$facePart]['VALUES_REL']["mouth_length"][$i]["delta"] = $mouthLengthX;

                $targetFaceData[$facePart]["mouth_length"][$i]["force"] = $this->getForce(
                    $scaleMouthLength, $mouthLengthX);

                if ($mouthLength === $mouthLengthN) {
                    $targetFaceData[$facePart]["mouth_length"][$i]["force"] = 0;
                    $targetFaceData[$facePart]["mouth_length"][$i]["val"] = 'none';
                }
                if ($mouthLength > $mouthLengthN) $targetFaceData[$facePart]["mouth_length"][$i]["val"] = '+';
                if ($mouthLength < $mouthLengthN) $targetFaceData[$facePart]["mouth_length"][$i]["val"] = '-';

                // изменение ширины рта
                // NORM_POINTS 51 57
                if (isset($sourceFaceData[$i][51])) {
                    $upperLipYMov = $sourceFaceData[$i][51]['Y'] - $yN51;

                    $targetFaceData[$facePart]['VALUES_REL']["mouth_upper_lip_outer_center_movement"]["max"] = $maxMouthLength;
                    $targetFaceData[$facePart]['VALUES_REL']["mouth_upper_lip_outer_center_movement"]["min"] = 0;
                    $targetFaceData[$facePart]['VALUES_REL']["mouth_upper_lip_outer_center_movement"][$i]["delta"] = $upperLipYMov;

                    $targetFaceData[$facePart]["mouth_upper_lip_outer_center_movement"][$i]["force"] = $this->getForce(
                        $maxMouthLength, abs($upperLipYMov));
                }

                if (isset($targetFaceData[$facePart]["mouth_upper_lip_outer_center_movement"][$i]))
                    if (isset($targetFaceData[$facePart]["mouth_upper_lip_outer_center_movement"][$i]) &&
                        $targetFaceData[$facePart]["mouth_upper_lip_outer_center_movement"][$i]["force"] == 0)
                        $targetFaceData[$facePart]["mouth_upper_lip_outer_center_movement"][$i]["val"] = 'none';
                    else
                        if ($upperLipYMov < 0)
                            $targetFaceData[$facePart]["mouth_upper_lip_outer_center_movement"][$i]["val"] = 'up';
                        else
                            $targetFaceData[$facePart]["mouth_upper_lip_outer_center_movement"][$i]["val"] = 'down';

                if (isset($sourceFaceData[$i][57])) {
                    $lowerLipYMov = $sourceFaceData[$i][57]['Y'] - $yN57;
                    if (isset($sourceFaceData[$i][51])) {
                        $mouthWidth = $sourceFaceData[$i][57]['Y'] - $sourceFaceData[$i][51]['Y'];
                    }
                }
                $targetFaceData[$facePart]['VALUES_REL']["mouth_lower_lip_outer_center_movement"]["max"] = $maxMouthLength;
                $targetFaceData[$facePart]['VALUES_REL']["mouth_lower_lip_outer_center_movement"]["min"] = 0;
                $targetFaceData[$facePart]['VALUES_REL']["mouth_lower_lip_outer_center_movement"][$i]["delta"] = $lowerLipYMov;

                $targetFaceData[$facePart]["mouth_lower_lip_outer_center_movement"][$i]["force"] =
                    $this->getForce($maxMouthLength, abs($lowerLipYMov));

                if ($targetFaceData[$facePart]["mouth_lower_lip_outer_center_movement"][$i]["force"] == 0)
                    $targetFaceData[$facePart]["mouth_lower_lip_outer_center_movement"][$i]["val"] = 'none';
                else
                    if ($lowerLipYMov > 0)
                        $targetFaceData[$facePart]["mouth_lower_lip_outer_center_movement"][$i]["val"] = 'down';
                    else
                        $targetFaceData[$facePart]["mouth_lower_lip_outer_center_movement"][$i]["val"] = 'up';

                $targetFaceData[$facePart]['VALUES_REL']["mouth_width"]["max"] = $maxMouthLength;
                $targetFaceData[$facePart]['VALUES_REL']["mouth_width"]["min"] = $mouthWidthN;
                $targetFaceData[$facePart]['VALUES_REL']["mouth_width"][$i]["delta"] = $mouthWidth - $mouthWidthN;

                $targetFaceData[$facePart]["mouth_width"][$i]["force"] = $this->getForce(
                    $scaleMouthWidth, abs($mouthWidth - $mouthWidthN));

                if ($mouthWidth === $mouthWidthN) {
                    $targetFaceData[$facePart]["mouth_width"][$i]["force"] = 0;
                    $targetFaceData[$facePart]["mouth_width"][$i]["val"] = 'none';
                }
//            if() !!!! 'compressed'
                if ($mouthWidth > $mouthWidthN) $targetFaceData[$facePart]["mouth_width"][$i]["val"] = '+';
                if ($mouthWidth < $mouthWidthN) $targetFaceData[$facePart]["mouth_width"][$i]["val"] = '-';

                // определение формы рта
                // NORM_POINTS 61 62 63 65 66 67
                if (isset($sourceFaceData[$i][67])) {
                    $width1 = $sourceFaceData[$i][67]['Y'] - $sourceFaceData[$i][61]['Y'];
                    $width2 = $sourceFaceData[$i][66]['Y'] - $sourceFaceData[$i][62]['Y'];
                    $width3 = $sourceFaceData[$i][65]['Y'] - $sourceFaceData[$i][63]['Y'];
                    $lengthTest = $sourceFaceData[$i][65]['X'] - $sourceFaceData[$i][67]['X'];

                    //брать интенсивность изменения ширины рта
                    $targetFaceData[$facePart]["mouth_form"][$i]["force"] =
                        $targetFaceData[$facePart]["mouth_width"][$i]["force"];
                }
                // echo $width1.'/'.$width2.'/'.$width3.'<br>';
                if (($width1 != 0) and ($width2 != 0) and ($width3 != 0) and ($lengthTest / 4 < $width2))
                    if (($width1 < $width2) and ($width3 < $width2))
                        $targetFaceData[$facePart]["mouth_form"][$i]["val"] = 'ellipse';
                    else
                        $targetFaceData[$facePart]["mouth_form"][$i]["val"] = 'rectangle';
                else
                    $targetFaceData[$facePart]["mouth_form"][$i]["val"] = 'line';
            }

            return $targetFaceData[$facePart];
        } else return false;
    }

    /**
     * Обнаружение признаков рта.
     *
     * @param $sourceFaceData - входной массив с лицевыми точками (landmarks)
     * @return array - выходной массив с обработанным массивом для глаза
     */
    public function detectMouthFeatures($sourceFaceData, $facePart,$point1,$point2)
    {
        if (isset($sourceFaceData[0][48])) $normFrameIndex = 0;
        else $normFrameIndex = 1;

        if (isset($sourceFaceData[$normFrameIndex][48])
            && isset($sourceFaceData[$normFrameIndex][$point1])
            && isset($sourceFaceData[$normFrameIndex][$point2])
            && isset($sourceFaceData[$normFrameIndex][54])
            && isset($sourceFaceData[$normFrameIndex][51])
            && isset($sourceFaceData[$normFrameIndex][57])
            && isset($sourceFaceData[$normFrameIndex][67])
            && isset($sourceFaceData[$normFrameIndex][66])
            && isset($sourceFaceData[$normFrameIndex][65])
            && isset($sourceFaceData[$normFrameIndex][61])
            && isset($sourceFaceData[$normFrameIndex][62])
            && isset($sourceFaceData[$normFrameIndex][63])
            && isset($sourceFaceData[$normFrameIndex][67])
        ) {
            $log = '';
            $midNY3942 = round(($sourceFaceData[0][$point2]['Y'] - $sourceFaceData[0][$point1]['Y'])/2) +
                $sourceFaceData[0][$point1]['Y'];
            $midNX3942 = round(($sourceFaceData[0][$point2]['X'] - $sourceFaceData[0][$point1]['X'])/2) +
                $sourceFaceData[0][$point1]['X'];

            $xN48 = abs($sourceFaceData[$normFrameIndex][48]['X'] - $midNX3942);
            $xN54 = abs($sourceFaceData[$normFrameIndex][54]['X'] - $midNX3942);
            $yN48 = $sourceFaceData[$normFrameIndex][48]['Y'] - $midNY3942;
            $yN54 = $sourceFaceData[$normFrameIndex][54]['Y'] - $midNY3942;
//            $mouthLengthN = $xN54 - $xN48;
            $mouthLengthN = $sourceFaceData[$normFrameIndex][54]['X'] - $sourceFaceData[$normFrameIndex][48]['X'];
            //Рот – 100% - круг с диаметром длиной рта в нормальном состоянии + 25%
            $maxMouthLength = round($mouthLengthN*1.25);

            $yN51 = $sourceFaceData[$normFrameIndex][51]['Y'] - $midNY3942;
            $yN57 = $sourceFaceData[$normFrameIndex][57]['Y'] - $midNY3942;
            $mouthWidthN = abs($yN57 - $yN51);

            $minMouthLength = $mouthLengthN*0.70; //2020-05-19
            $scaleMouthLength = $maxMouthLength - $minMouthLength; //2020-05-19

            $scaleMouthWidth = $maxMouthLength - $mouthWidthN; //2020-05-19

            //2020-05-20
            //Максимум по оси Y (максимальное перемещение вверх) = отрезок тт.48-54* 20% (длина рта)
            $yN62 = $sourceFaceData[$normFrameIndex][62]['Y'] - $midNY3942;
            $upperLipMax = $mouthLengthN*0.2;

            //Минимальные значения Ось Y – длина отрезка (51-62)  близкая к 0
            $upperLipMin = 0; //2020-05-21
            $scaleUpperLip = $upperLipMax - $upperLipMin;

 //           echo $sourceFaceData[$normFrameIndex][62]['Y'].'-'.$midNY3942.'='.$yN62.'/'.$mouthLengthN.'/'.$upperLipMax.'/'.$upperLipMin.'/'.$scaleUpperLip.'<br>';
            //Максимум по оси Y (максимальное перемещение вниз) =
            //(отрезок тт.48-54* 20%) + отрезок тт.48-54 + 5% (125% от длины рта)
            $lowerLipMax = $upperLipMax + $mouthLengthN*1.05; //2020-05-20

            // Минимальные значения Ось Y – длина отрезка (57-66)  близкая к 0
            $lowerLipMin = $sourceFaceData[$normFrameIndex][57]['Y'] - $sourceFaceData[$normFrameIndex][66]['Y'];
            $scaleLowerLip = $lowerLipMax - $lowerLipMin;

            //Уголки рта, Ось Y, Максимальные значения
            //100% - (отрезок между т.0 и  уголком рта н.т. 48 (54)) - длина рта в нормальном состоянии (48-54) * 20%.
//            $leftCornerYMax = $yN48 - $mouthLengthN*0.2;
//            $rightCornerYMax = $yN54 - $mouthLengthN*0.2;
            //длина рта в нормальном состяонии по 20% при движении вверх и вниз
            $leftCornerYMax = $mouthLengthN*0.4; //2020-05-21
            $rightCornerYMax = $mouthLengthN*0.4; //2020-05-21

            //Минимальные значения
            //100% - (отрезок между т.0 и  уголком рта н.т. 48 (54)) + длина рта в нормальном состоянии (48-54) * 20%.
//            $leftCornerYMin = $yN48 + $mouthLengthN*0.2;
//            $rightCornerYMin = $yN54 + $mouthLengthN*0.2;
            $leftCornerYMin = 0; //2020-05-21
            $rightCornerYMin = 0; //2020-05-21
            $scaleLeftCornerY = abs($leftCornerYMax - $leftCornerYMin);
            $scaleRightCornerY = abs($rightCornerYMax - $rightCornerYMin);

            //Максимальные значения
            //100% - (отрезок между т.0 и  уголком рта н.т. 48 (54)) + (длина рта в нормальном состоянии (48-54) * 25%.
//            $leftCornerXMax = $xN48 + $mouthLengthN*0.25;
//            $rightCornerXMax = $xN54 + $mouthLengthN*0.25;
            //длина рта в нормальном состяонии*25% при движении в одну сторону и + 30% длины рта при движении в другую
            $leftCornerXMax =$mouthLengthN*0.55; //2020-05-21
            $rightCornerXMax =$mouthLengthN*0.55; //2020-05-21

            //Минимальные значения
            //Ось X – (отрезок между т.0 и  уголком рта н.т. 48 (54)) – (н. длина рта (48-54) * 30%)
//            $leftCornerXMin = $xN48 - $mouthLengthN*0.3;
//            $rightCornerXMin = $xN54 - $mouthLengthN*0.3;
            $leftCornerXMin = 0; //2020-05-21
            $rightCornerXMin = 0; //2020-05-21
            $scaleLeftCornerX = abs ($leftCornerXMax - $leftCornerXMin);
            $scaleRightCornerX = abs($rightCornerXMax - $rightCornerXMin);

            // изменение длины рта
            // NORM_POINTS 48 54
            // echo $FaceData_['normmask'][0][48][X];
            for ($i = 0; $i < count($sourceFaceData); $i++) {
                if (isset($sourceFaceData[$i][$point1]) && isset($sourceFaceData[$i][$point2])){
                    $midY3942 = round(($sourceFaceData[$i][$point2]['Y'] - $sourceFaceData[$i][$point1]['Y'])/2) +
                        $sourceFaceData[$i][$point1]['Y'];
                    $midX3942 = round(($sourceFaceData[$i][$point2]['X'] - $sourceFaceData[$i][$point1]['X'])/2) +
                        $sourceFaceData[$i][$point1]['X'];
                }

                if ((isset($sourceFaceData[$i][48]))
                   ) {
                    $leftMouthCornerXMov = abs($sourceFaceData[$i][48]['X'] - $midX3942) - $xN48;
                    $leftMouthCornerYMov = abs($sourceFaceData[$i][48]['Y'] - $midY3942) - $yN48;

//                    echo $leftMouthCornerXMov.' '.abs($sourceFaceData[$i][48]['X'] - $midX3942) .' '. $xN48.'<br>';

                    $leftMouthCornerXMovForce = $this->getForce($scaleLeftCornerX, abs($leftMouthCornerXMov));
                    $leftMouthCornerYMovForce = $this->getForce($scaleLeftCornerY, abs($leftMouthCornerYMov));

                    $targetFaceData[$facePart]['VALUES_REL']["left_corner_mouth_movement_x"]["max"] = $leftCornerXMax;
                    $targetFaceData[$facePart]['VALUES_REL']["left_corner_mouth_movement_x"]["min"] = $leftCornerXMin;
                    $targetFaceData[$facePart]['VALUES_REL']["left_corner_mouth_movement_x"][$i]["delta"] = $leftMouthCornerXMov;
                    $targetFaceData[$facePart]['VALUES_REL']["left_corner_mouth_movement_x"][$i]["val"] = abs($sourceFaceData[$i][48]['X'] - $midX3942);
                    $targetFaceData[$facePart]['VALUES_REL']["left_corner_mouth_movement_y"]["max"] = $leftCornerYMax;
                    $targetFaceData[$facePart]['VALUES_REL']["left_corner_mouth_movement_y"]["min"] = $leftCornerYMin;
                    $targetFaceData[$facePart]['VALUES_REL']["left_corner_mouth_movement_y"][$i]["delta"] = $leftMouthCornerYMov;
                    $targetFaceData[$facePart]['VALUES_REL']["left_corner_mouth_movement_y"][$i]["val"] = $sourceFaceData[$i][48]['Y'] - $midY3942;

                    $targetFaceData[$facePart]["left_corner_mouth_movement_x"][$i]["force"] = $leftMouthCornerXMovForce;
                    $targetFaceData[$facePart]["left_corner_mouth_movement_y"][$i]["force"] = $leftMouthCornerYMovForce;

                    $yMov = '';
                    if ($leftMouthCornerYMov < 0) $yMov = 'up';
                    if ($leftMouthCornerYMov > 0) $yMov = 'down';
                    if ($leftMouthCornerXMov > 0) $xMov = 'from center';
                    else $xMov = 'to center';

                    $targetFaceData[$facePart]["left_corner_mouth_movement_x"][$i]["val"] = $xMov;
                    $targetFaceData[$facePart]["left_corner_mouth_movement_y"][$i]["val"] = $yMov;
                    if ($targetFaceData[$facePart]["left_corner_mouth_movement_x"][$i]["force"] == 0)
                        $targetFaceData[$facePart]["left_corner_mouth_movement_x"][$i]["val"] = 'none';
                    if ($targetFaceData[$facePart]["left_corner_mouth_movement_y"][$i]["force"] == 0)
                        $targetFaceData[$facePart]["left_corner_mouth_movement_y"][$i]["val"] = 'none';
                }

                if (isset($sourceFaceData[$i][54])) {
                    $rightMouthCornerXMov = abs($sourceFaceData[$i][54]['X'] - $midX3942) - $xN54 ;
                    $rightMouthCornerYMov = abs($sourceFaceData[$i][54]['Y'] - $midY3942) - $yN54 ;

                    $rightMouthCornerXMovForce = $this->getForce($scaleRightCornerX, abs($rightMouthCornerXMov));
                    $rightMouthCornerYMovForce = $this->getForce($scaleRightCornerY, abs($rightMouthCornerYMov));

                    $targetFaceData[$facePart]['VALUES_REL']["right_corner_mouth_movement_x"]["max"] = $rightCornerXMax;
                    $targetFaceData[$facePart]['VALUES_REL']["right_corner_mouth_movement_x"]["min"] = $rightCornerXMin;
                    $targetFaceData[$facePart]['VALUES_REL']["right_corner_mouth_movement_x"][$i]["delta"] = $rightMouthCornerXMov;
                    $targetFaceData[$facePart]['VALUES_REL']["right_corner_mouth_movement_x"][$i]["val"] = $sourceFaceData[$i][54]['X'] - $midX3942;
                    $targetFaceData[$facePart]['VALUES_REL']["right_corner_mouth_movement_y"]["max"] = $rightCornerYMax;
                    $targetFaceData[$facePart]['VALUES_REL']["right_corner_mouth_movement_y"]["min"] = $rightCornerYMin;
                    $targetFaceData[$facePart]['VALUES_REL']["right_corner_mouth_movement_y"][$i]["delta"] = $rightMouthCornerYMov;
                    $targetFaceData[$facePart]['VALUES_REL']["right_corner_mouth_movement_y"][$i]["val"] = $sourceFaceData[$i][54]['Y'] - $midY3942;

                    $targetFaceData[$facePart]["right_corner_mouth_movement_x"][$i]["force"] = $rightMouthCornerXMovForce;
                    $targetFaceData[$facePart]["right_corner_mouth_movement_y"][$i]["force"] = $rightMouthCornerYMovForce;

                    if (isset($sourceFaceData[$i][48])) {
                        $mouthLength = $sourceFaceData[$i][54]['X'] - $sourceFaceData[$i][48]['X'];
                    }
                    if ($rightMouthCornerYMov < 0) $yMov = 'up';
                    if ($rightMouthCornerYMov > 0) $yMov = 'down';
                    if ($rightMouthCornerXMov > 0) $xMov = 'from center';
                    else $xMov = 'to center';

                    $targetFaceData[$facePart]["right_corner_mouth_movement_x"][$i]["val"] = $xMov;
                    $targetFaceData[$facePart]["right_corner_mouth_movement_y"][$i]["val"] = $yMov;
                    if ($targetFaceData[$facePart]["right_corner_mouth_movement_x"][$i]["force"] == 0)
                        $targetFaceData[$facePart]["right_corner_mouth_movement_x"][$i]["val"] = 'none';
                    if ($targetFaceData[$facePart]["right_corner_mouth_movement_y"][$i]["force"] == 0)
                        $targetFaceData[$facePart]["right_corner_mouth_movement_y"][$i]["val"] = 'none';
                }

                //движение уголков рта
                $xMov = 'none';
                if (isset($targetFaceData[$facePart]["left_corner_mouth_movement_x"][$i]) &&
                    ($targetFaceData[$facePart]["right_corner_mouth_movement_x"][$i]["val"] ==
                        $targetFaceData[$facePart]["left_corner_mouth_movement_x"][$i]["val"])) {
                    $xMov = $targetFaceData[$facePart]["right_corner_mouth_movement_x"][$i]["val"];
                }
                $targetFaceData[$facePart]["mouth_corners_movement"][$i]["val"] = $xMov;

                $mouthLengthX = abs($mouthLength - $mouthLengthN);

                $targetFaceData[$facePart]['VALUES_REL']["mouth_length"]["max"] = $maxMouthLength;
                $targetFaceData[$facePart]['VALUES_REL']["mouth_length"]["min"] = $minMouthLength;
                $targetFaceData[$facePart]['VALUES_REL']["mouth_length"][$i]["delta"] = $mouthLengthX;
                $targetFaceData[$facePart]['VALUES_REL']["mouth_length"][$i]["val"] = $mouthLength;

                $targetFaceData[$facePart]["mouth_length"][$i]["force"] = $this->getForce(
                    $scaleMouthLength, $mouthLengthX);

                if ($mouthLength === $mouthLengthN) {
                    $targetFaceData[$facePart]["mouth_length"][$i]["force"] = 0;
                    $targetFaceData[$facePart]["mouth_length"][$i]["val"] = 'none';
                }
                if ($mouthLength > $mouthLengthN) $targetFaceData[$facePart]["mouth_length"][$i]["val"] = '+';
                if ($mouthLength < $mouthLengthN) $targetFaceData[$facePart]["mouth_length"][$i]["val"] = '-';

                // изменение ширины рта
                // NORM_POINTS 51 57

                if (isset($sourceFaceData[$i][51])) {
                    $upperLipYMov = $sourceFaceData[$i][51]['Y'] - $yN51 - $midY3942;

                    $targetFaceData[$facePart]['VALUES_REL']["mouth_upper_lip_outer_center_movement"]["max"] = $upperLipMax;
                    $targetFaceData[$facePart]['VALUES_REL']["mouth_upper_lip_outer_center_movement"]["min"] = $upperLipMin;
                    $targetFaceData[$facePart]['VALUES_REL']["mouth_upper_lip_outer_center_movement"][$i]["delta"] = $upperLipYMov;
                    $targetFaceData[$facePart]['VALUES_REL']["mouth_upper_lip_outer_center_movement"][$i]["val"] = $sourceFaceData[$i][51]['Y'] - $midY3942;

                    $targetFaceData[$facePart]["mouth_upper_lip_outer_center_movement"][$i]["force"] = $this->getForce(
                        $scaleUpperLip, abs($upperLipYMov));
                }

                if (isset($targetFaceData[$facePart]["mouth_upper_lip_outer_center_movement"][$i]))
                    if (isset($targetFaceData[$facePart]["mouth_upper_lip_outer_center_movement"][$i]) &&
                        $targetFaceData[$facePart]["mouth_upper_lip_outer_center_movement"][$i]["force"] == 0)
                        $targetFaceData[$facePart]["mouth_upper_lip_outer_center_movement"][$i]["val"] = 'none';
                    else
                        if ($upperLipYMov < 0)
                            $targetFaceData[$facePart]["mouth_upper_lip_outer_center_movement"][$i]["val"] = 'up';
                        else
                            $targetFaceData[$facePart]["mouth_upper_lip_outer_center_movement"][$i]["val"] = 'down';

                if (isset($sourceFaceData[$i][57])) {
                    $lowerLipYMov = $sourceFaceData[$i][57]['Y'] - $yN57 - $midY3942;
                    if (isset($sourceFaceData[$i][51])) {
                        $mouthWidth = $sourceFaceData[$i][57]['Y'] - $sourceFaceData[$i][51]['Y'];
                    }
                }
                $targetFaceData[$facePart]['VALUES_REL']["mouth_lower_lip_outer_center_movement"]["max"] = $lowerLipMax;
                $targetFaceData[$facePart]['VALUES_REL']["mouth_lower_lip_outer_center_movement"]["min"] = $lowerLipMin;
                $targetFaceData[$facePart]['VALUES_REL']["mouth_lower_lip_outer_center_movement"][$i]["delta"] = $lowerLipYMov;
                $targetFaceData[$facePart]['VALUES_REL']["mouth_lower_lip_outer_center_movement"][$i]["val"] = $sourceFaceData[$i][57]['Y'] - $midY3942;

                $targetFaceData[$facePart]["mouth_lower_lip_outer_center_movement"][$i]["force"] =
                    $this->getForce($scaleLowerLip, abs($lowerLipYMov));

                if ($targetFaceData[$facePart]["mouth_lower_lip_outer_center_movement"][$i]["force"] == 0)
                    $targetFaceData[$facePart]["mouth_lower_lip_outer_center_movement"][$i]["val"] = 'none';
                else
                    if ($lowerLipYMov > 0)
                        $targetFaceData[$facePart]["mouth_lower_lip_outer_center_movement"][$i]["val"] = 'down';
                    else
                        $targetFaceData[$facePart]["mouth_lower_lip_outer_center_movement"][$i]["val"] = 'up';

                $targetFaceData[$facePart]['VALUES_REL']["mouth_width"]["max"] = $maxMouthLength;
                $targetFaceData[$facePart]['VALUES_REL']["mouth_width"]["min"] = $mouthWidthN;
                $targetFaceData[$facePart]['VALUES_REL']["mouth_width"][$i]["delta"] = $mouthWidth - $mouthWidthN;
                $targetFaceData[$facePart]['VALUES_REL']["mouth_width"][$i]["val"] = $mouthWidth;

                $targetFaceData[$facePart]["mouth_width"][$i]["force"] = $this->getForce(
                    $scaleMouthWidth, abs($mouthWidth - $mouthWidthN));

                if ($mouthWidth === $mouthWidthN) {
                    $targetFaceData[$facePart]["mouth_width"][$i]["force"] = 0;
                    $targetFaceData[$facePart]["mouth_width"][$i]["val"] = 'none';
                }
//            if() !!!! 'compressed'
                if ($mouthWidth > $mouthWidthN) $targetFaceData[$facePart]["mouth_width"][$i]["val"] = '+';
                if ($mouthWidth < $mouthWidthN) $targetFaceData[$facePart]["mouth_width"][$i]["val"] = '-';

                // определение формы рта
                // NORM_POINTS 61 62 63 65 66 67
                if (isset($sourceFaceData[$i][67])) {
                    $width1 = $sourceFaceData[$i][67]['Y'] - $sourceFaceData[$i][61]['Y'];
                    $width2 = $sourceFaceData[$i][66]['Y'] - $sourceFaceData[$i][62]['Y'];
                    $width3 = $sourceFaceData[$i][65]['Y'] - $sourceFaceData[$i][63]['Y'];
                    $lengthTest = $sourceFaceData[$i][65]['X'] - $sourceFaceData[$i][67]['X'];

                    //брать интенсивность изменения ширины рта
                    $targetFaceData[$facePart]["mouth_form"][$i]["force"] =
                        $targetFaceData[$facePart]["mouth_width"][$i]["force"];
                }
                // echo $width1.'/'.$width2.'/'.$width3.'<br>';
                if (($width1 != 0) and ($width2 != 0) and ($width3 != 0) and ($lengthTest / 4 < $width2))
                    if (($width1 < $width2) and ($width3 < $width2))
                        $targetFaceData[$facePart]["mouth_form"][$i]["val"] = 'ellipse';
                    else
                        $targetFaceData[$facePart]["mouth_form"][$i]["val"] = 'rectangle';
                else
                    $targetFaceData[$facePart]["mouth_form"][$i]["val"] = 'line';
            }

            return $targetFaceData[$facePart];
        } else return false;
    }

    /**
     * Обнаружение трендов (универсальная функция).
     *
     * @param $sourceFaceData1 - входной массив с лицевыми точками (landmarks)
     * @return array - выходной массив с обработанным массивом
     */
    public function detectTrends($sourceFaceData1, $trendLength)
    {
        if ($sourceFaceData1 != null)
            foreach ($sourceFaceData1 as $k => $v)
                if((strpos($k,'frame') === false) && (strpos($k,'MASK_NAMES') === false)){
                if ($v != null) {
                    foreach ($v as $k1 => $v1) {
                        if (isset($v1[0])) {
                            $v1[0]["trend"] = '1=';
                            $v1[0]["confidence"] = 1;
                        }
                        $currentTrendLength = 1;

                        for ($i = 1; $i < count($v1); $i++) {
                            //                    if(isset($v1[$i-1][$arrayKeys[1]]))
                            //                        $val0 = $v1[$i-1][$arrayKeys[1]];
                            //                    if(isset($v1[$i]) && isset($arrayKeys[1]))
                            //                        $val1 = $v1[$i][$arrayKeys[1]];

                            //echo $v1[$i]["force"].'/'.$v1[$i-1]["force"].'/'.$v1[$i]["val"].'/'.$v1[$i-1]["val"].'/'.$v1[$i]["trend"].'<br>';
                            if ((isset($v1[$i]["force"])) && (isset($v1[$i - 1]["force"])) &&
                                (isset($v1[$i]["val"])) && (isset($v1[$i - 1]["val"]))) {

                                if (!isset($v1[$i - 1]["trend"])) {
                                    $v1[$i - 1]["trend"] = '1=';
                                    $v1[$i - 1]["confidence"] = 1;
                                }
                                if (($v1[$i - 1]["force"] < $v1[$i]["force"]) &&    //если интенсивность увеличивается
//                                    ($v1[$i - 1]["val"] === $v1[$i]["val"]) &&    //и значение не меняет направление
                                    (isset($v1[$i - 1]["trend"]) && (strpos($v1[$i - 1]["trend"], '+') > 0))) { //и был тренд на увеличение, то продолжаем его
                                    ++$currentTrendLength;
                                    $v1[$i]["trend"] = $currentTrendLength . '+';
                                    $v1[$i]["confidence"] = 1;
                                }

                                if (($v1[$i - 1]["force"] > $v1[$i]["force"]) &&    //если интенсивность уменьшается
                                    ($v1[$i - 1]["val"] === $v1[$i]["val"]) &&    //и значение не меняет направление
                                    (isset($v1[$i - 1]["trend"]) && (strpos($v1[$i - 1]["trend"], '-') > 0))) { //и был тренд на уменьшение, то продолжаем его
                                    ++$currentTrendLength;
                                    $v1[$i]["trend"] = $currentTrendLength . '-';
                                    $v1[$i]["confidence"] = 1;
                                }

                                if (($v1[$i - 1]["force"] === $v1[$i]["force"]) &&    //если интенсивность не меняется
//                                    ($v1[$i - 1]["val"] === $v1[$i]["val"]) &&    //и значение не меняет направление
                                    (isset($v1[$i - 1]["trend"]) && (strpos($v1[$i - 1]["trend"], '=') > 0))) { //и был тренд на сохранение, то продолжаем его
                                    ++$currentTrendLength;
                                    $v1[$i]["trend"] = $currentTrendLength . '=';
                                    $v1[$i]["confidence"] = 1;
                                }

                                if (($v1[$i - 1]["force"] < $v1[$i]["force"]) &&    //если интенсивность увеличивается
//                                    ($v1[$i - 1]["val"] === $v1[$i]["val"]) &&    //и значение не меняет направление
                                    (isset($v1[$i - 1]["trend"]) && (strpos($v1[$i - 1]["trend"], '+') === false))) {
                                    //и был тренд на уменьшение или сохранение, то начинаем новый тренд на увеличение
                                    $currentTrendLength = 1;
                                    $v1[$i]["trend"] = $currentTrendLength . '+';
                                    $v1[$i]["confidence"] = 1;
                                }

                                if (($v1[$i - 1]["force"] > $v1[$i]["force"]) &&    //если интенсивность уменьшается
  //                                  ($v1[$i - 1]["val"] === $v1[$i]["val"]) &&    //и значение не меняет направление
                                    (isset($v1[$i - 1]["trend"]) && (strpos($v1[$i - 1]["trend"], '-') === false))) {
                                    //и был тренд на увеличение или сохранение, то начинаем новый тренд на уменьшение
                                    $currentTrendLength = 1;
                                    $v1[$i]["trend"] = $currentTrendLength . '-';
                                    $v1[$i]["confidence"] = 1;
                                }

                                if (($v1[$i - 1]["force"] === $v1[$i]["force"]) &&    //если интенсивность не маеняется
 //                                   ($v1[$i - 1]["val"] === $v1[$i]["val"]) &&    //и значение не меняет направление
                                    (isset($v1[$i - 1]["trend"]) && (strpos($v1[$i - 1]["trend"], '=') === false))) {
                                    //и был тренд на увеличение или уменьшение, то начинаем новый тренд на сохранение
                                    $currentTrendLength = 1;
                                    $v1[$i]["trend"] = $currentTrendLength . '=';
                                    $v1[$i]["confidence"] = 1;
                                }

                                if ($v1[$i - 1]["val"] !== $v1[$i]["val"]) { //если значения отличаются
                                    $trenfVal = 'none';
                                    //это либо числовое значение
                                    if (is_numeric($v1[$i]["val"])) {
                                        if ($v1[$i - 1]["val"] > $v1[$i]["val"]) $trenfVal = '-';
                                        if ($v1[$i - 1]["val"] < $v1[$i]["val"]) $trenfVal = '+';
                                        //значение тренда сохраняется
                                        if (isset($v1[$i - 1]["trend"]) && (strpos($v1[$i - 1]["trend"], $trenfVal) > 0)) {
                                            ++$currentTrendLength;
                                        } else {
                                            $currentTrendLength = 1;
                                        }
                                        $v1[$i]["trend"] = $currentTrendLength . $trenfVal;
                                        $v1[$i]["confidence"] = 1;
                                    } else {
                                        //либо смена направления для качественного значения
                                        $currentTrendLength = 1;
                                        if ($v1[$i - 1]["force"] > $v1[$i]["force"]) $trenfVal = '-';
                                        if ($v1[$i - 1]["force"] < $v1[$i]["force"]) $trenfVal = '+';
                                        if ($v1[$i - 1]["force"] === $v1[$i]["force"]) $trenfVal = '=';
                                        $v1[$i]["trend"] = $currentTrendLength . $trenfVal;
                                        $v1[$i]["confidence"] = 1;
                                    }
                                }
                            }
                        }
                        $sourceFaceData1[$k][$k1] = $v1;
                    }
                }
    }
        return $sourceFaceData1;
    }
    /**
     * Обновление определенных значений в заданном диапазоне
     * @param $sourceFaceData2 - входной массив с лицевыми точками (landmarks)
     * @param $keyForUpdate - наименование ключа
     * @param $newValue - новое значение
     * @param $starFrame и $endFrame - диапазон для обновления
     * @return array - выходной массив с обработанным массивом
     */
    public function updateValues($sourceFaceData2,$keyForUpdate,$newValue,$starFrame,$endFrame)
    { //$sourceFaceData1[$k][$prefix."eye_closed"]
//        echo '!!!'.$starFrame.':'.$endFrame.'<br>';
        foreach ($sourceFaceData2 as $k1 => $v1) {
          if(($k1 >= $starFrame)and($k1 <= $endFrame)){
//              echo $k1.' '.$v1[$keyForUpdate].' '.$newValue.' <br>';
              $sourceFaceData2[$k1][$keyForUpdate] = $newValue;
          }
        }
      return $sourceFaceData2;
    }

    //сохранение координат точек в csv файл
    //вход - массив с точками; имя файла
    public function saveXY($sourceFaceData2,$fileName)
    {
        // load data
        $FaceData_ = json_decode($sourceFaceData2, true);
        // check input format and convert the I format to AB
        if(strpos($sourceFaceData2,'NORM_POINTS') !== false)
            $sourceFaceData3 = $this->convertIJson($FaceData_);
        else
            $sourceFaceData3 =  $FaceData_; // use the AB format
        //
 //       $arr = array('61','62', '63', '65', '66', '67', 36,37,38,39, 40, 41, 42, 43, 44, 45, 46,47, 31, 35,
 //           19,24, 17, 21, 22, 26, 48, 54, 51, 57, 27, 28, 29);
        $arr = array('61');
        $res = array();
        for ($i = 0; $i < count($sourceFaceData3['normmask']); $i++) {
            foreach ($arr as $k1 => $v1) {
//              print_r($sourceFaceData3['normmask'][$i][$v1]);
//                echo  '<br>';
              if (isset($sourceFaceData3['normmask'][$i][$v1])){
                $res[$v1] =  $res[$v1].$i.';'.$sourceFaceData3['normmask'][$i][$v1]['X'].';'.
                    $sourceFaceData3['normmask'][$i][$v1]['Y']."\n";
              }
             }
        }
        for ($i = 0; $i < count($sourceFaceData3['left_nasolabial_fold']); $i++) {
            $res['31x48x74'] =  $res['31x48x74'].$i.';'.
                ($sourceFaceData3['left_nasolabial_fold'][$i][0]['X'] - $sourceFaceData3['left_nasolabial_fold'][$i][0]['X2']).';'.
                ($sourceFaceData3['left_nasolabial_fold'][$i][0]['Y'] - $sourceFaceData3['left_nasolabial_fold'][$i][0]['Y2']).';'.
                $sourceFaceData3['left_nasolabial_fold'][$i][0]['SUMX'].';'.
                $sourceFaceData3['left_nasolabial_fold'][$i][0]['SUMY'].';'.
                $sourceFaceData3['left_nasolabial_fold'][$i][0]['SUMX2'].';'.
                $sourceFaceData3['left_nasolabial_fold'][$i][0]['SUMY2'].';'.
                $sourceFaceData3['left_nasolabial_fold'][$i][0]['NNN']."\n";
        }
        for ($i = 0; $i < count($sourceFaceData3['right_nasolabial_fold']); $i++) {
            $res['35x54x75'] =  $res['35x54x75'].$i.';'.
                ($sourceFaceData3['right_nasolabial_fold'][$i][0]['X'] - $sourceFaceData3['right_nasolabial_fold'][$i][0]['X2']).';'.
                ($sourceFaceData3['right_nasolabial_fold'][$i][0]['Y'] - $sourceFaceData3['right_nasolabial_fold'][$i][0]['Y2']).';'.
                $sourceFaceData3['right_nasolabial_fold'][$i][0]['SUMX'].';'.
                $sourceFaceData3['right_nasolabial_fold'][$i][0]['SUMY'].';'.
                $sourceFaceData3['right_nasolabial_fold'][$i][0]['SUMX2'].';'.
                $sourceFaceData3['right_nasolabial_fold'][$i][0]['SUMY2'].';'.
                $sourceFaceData3['right_nasolabial_fold'][$i][0]['NNN']."\n";
        }
        for ($i = 0; $i < count($sourceFaceData3['left_nasolabial_fold_2']); $i++) {
            $res['31x40x74'] =  $res['31x40x74'].$i.';'.
                ($sourceFaceData3['left_nasolabial_fold_2'][$i][0]['X'] - $sourceFaceData3['left_nasolabial_fold_2'][$i][0]['X2']).';'.
                ($sourceFaceData3['left_nasolabial_fold_2'][$i][0]['Y'] - $sourceFaceData3['left_nasolabial_fold_2'][$i][0]['Y2']).';'.
                $sourceFaceData3['left_nasolabial_fold_2'][$i][0]['SUMX'].';'.
                $sourceFaceData3['left_nasolabial_fold_2'][$i][0]['SUMY'].';'.$sourceFaceData3['left_nasolabial_fold_2'][$i][0]['NNN']."\n";
        }
        for ($i = 0; $i < count($sourceFaceData3['right_nasolabial_fold_2']); $i++) {
            $res['35x47x75'] =  $res['35x47x75'].$i.';'.
                ($sourceFaceData3['right_nasolabial_fold_2'][$i][0]['X'] - $sourceFaceData3['right_nasolabial_fold_2'][$i][0]['X2']).';'.
                ($sourceFaceData3['right_nasolabial_fold_2'][$i][0]['Y'] - $sourceFaceData3['right_nasolabial_fold_2'][$i][0]['Y2']).';'.
                $sourceFaceData3['right_nasolabial_fold_2'][$i][0]['SUMX'].';'.
                $sourceFaceData3['right_nasolabial_fold_2'][$i][0]['SUMY'].';'.$sourceFaceData3['right_nasolabial_fold_2'][$i][0]['NNN']."\n";
        }
        //        print_r($res);
        foreach ($res as $k => $v) {
//            echo $v.'<br>';
            $fd = fopen($fileName.'_'.$k.'.csv', "w");
            fwrite($fd,$v);
            fclose($fd);
        }
 //       return $sourceFaceData2;
    }

    //сохранение координат точек в csv файл
    //вход - массив с точками; имя файла
    public function saveXY2($sourceFaceData3,$fileName)
    {
        //
//               $arr = array('61','62', '63', '65', '66', '67', 36,37,38,39, 40, 41, 42, 43, 44, 45, 46,47, 31, 35,
//                   19,24, 17, 21, 22, 26, 48, 54, 51, 57, 27, 28, 29);
        $arr = array('26');
        $res = array();
        for ($i = 0; $i < count($sourceFaceData3['normmask']); $i++) {
            foreach ($arr as $k1 => $v1) {
//              print_r($sourceFaceData3['normmask'][$i][$v1]);
//                echo  '<br>';
                if (isset($sourceFaceData3['normmask'][$i][$v1])){
                    $res[$v1] =  $res[$v1].$i.';'.$sourceFaceData3['normmask'][$i][$v1]['X'].';'.
                        $sourceFaceData3['normmask'][$i][$v1]['Y']."\n";
                }
            }
        }
        for ($i = 0; $i < count($sourceFaceData3['left_nasolabial_fold']); $i++) {
            $res['31x48x74'] =  $res['31x48x74'].$i.';'.
                ($sourceFaceData3['left_nasolabial_fold'][$i][0]['X'] - $sourceFaceData3['left_nasolabial_fold'][$i][0]['X2']).';'.
                ($sourceFaceData3['left_nasolabial_fold'][$i][0]['Y'] - $sourceFaceData3['left_nasolabial_fold'][$i][0]['Y2']).';'.
                $sourceFaceData3['left_nasolabial_fold'][$i][0]['SUMX'].';'.
                $sourceFaceData3['left_nasolabial_fold'][$i][0]['SUMY'].';'.
                $sourceFaceData3['left_nasolabial_fold'][$i][0]['SUMX2'].';'.
                $sourceFaceData3['left_nasolabial_fold'][$i][0]['SUMY2'].';'.
                $sourceFaceData3['left_nasolabial_fold'][$i][0]['NNN']."\n";
        }
        for ($i = 0; $i < count($sourceFaceData3['right_nasolabial_fold']); $i++) {
            $res['35x54x75'] =  $res['35x54x75'].$i.';'.
                ($sourceFaceData3['right_nasolabial_fold'][$i][0]['X'] - $sourceFaceData3['right_nasolabial_fold'][$i][0]['X2']).';'.
                ($sourceFaceData3['right_nasolabial_fold'][$i][0]['Y'] - $sourceFaceData3['right_nasolabial_fold'][$i][0]['Y2']).';'.
                $sourceFaceData3['right_nasolabial_fold'][$i][0]['SUMX'].';'.
                $sourceFaceData3['right_nasolabial_fold'][$i][0]['SUMY'].';'.
                $sourceFaceData3['right_nasolabial_fold'][$i][0]['SUMX2'].';'.
                $sourceFaceData3['right_nasolabial_fold'][$i][0]['SUMY2'].';'.
                $sourceFaceData3['right_nasolabial_fold'][$i][0]['NNN']."\n";
        }
        for ($i = 0; $i < count($sourceFaceData3['left_nasolabial_fold_2']); $i++) {
            $res['31x40x74'] =  $res['31x40x74'].$i.';'.
                ($sourceFaceData3['left_nasolabial_fold_2'][$i][0]['X'] - $sourceFaceData3['left_nasolabial_fold_2'][$i][0]['X2']).';'.
                ($sourceFaceData3['left_nasolabial_fold_2'][$i][0]['Y'] - $sourceFaceData3['left_nasolabial_fold_2'][$i][0]['Y2']).';'.
                $sourceFaceData3['left_nasolabial_fold_2'][$i][0]['SUMX'].';'.
                $sourceFaceData3['left_nasolabial_fold_2'][$i][0]['SUMY'].';'.$sourceFaceData3['left_nasolabial_fold_2'][$i][0]['NNN']."\n";
        }
        for ($i = 0; $i < count($sourceFaceData3['right_nasolabial_fold_2']); $i++) {
            $res['35x47x75'] =  $res['35x47x75'].$i.';'.
                ($sourceFaceData3['right_nasolabial_fold_2'][$i][0]['X'] - $sourceFaceData3['right_nasolabial_fold_2'][$i][0]['X2']).';'.
                ($sourceFaceData3['right_nasolabial_fold_2'][$i][0]['Y'] - $sourceFaceData3['right_nasolabial_fold_2'][$i][0]['Y2']).';'.
                $sourceFaceData3['right_nasolabial_fold_2'][$i][0]['SUMX'].';'.
                $sourceFaceData3['right_nasolabial_fold_2'][$i][0]['SUMY'].';'.$sourceFaceData3['right_nasolabial_fold_2'][$i][0]['NNN']."\n";
        }
        //        print_r($res);
        foreach ($res as $k => $v) {
//            echo $v.'<br>';
            $fd = fopen($fileName.'_'.$k.'.csv', "w");
            fwrite($fd,$v);
            fclose($fd);
        }
        //       return $sourceFaceData2;
    }

    /**
     * Определение дополнительных проявлений, в частности
     * моргание
     * закрытие глаза
     * @param $sourceFaceData1 - входной массив с лицевыми точками (landmarks)
     * @return array - выходной массив с обработанным массивом
     */
    public function detectAdditionalFeatures($sourceFaceData1)
    {
        if ($sourceFaceData1 != null)
            foreach ($sourceFaceData1 as $k=>$v) {
                // детекция носогубки (v.2): по уголкам рта
  /*              if (($k === 'mouth') && ($v != null)) {
                    foreach ($v as $k1 => $v1) {
                    if (($k1 === 'left_corner_mouth_movement_x') || ($k1 === 'right_corner_mouth_movement_x')){
                        if(strpos($k1,'right')>-1) $prefix = 'right_';
                        else $prefix = 'left_';
                        for ($i = 0; $i < count($v1); $i++) {
                            if (isset($v1[$i]["val"]) && isset($v1[$i]["force"])
                                //                                 &&  isset($v1[$i]["confidence"]) && isset($v1[$i]["trend"])
                            ) {
                                $sourceFaceData1['nose'][$prefix."nasolabial_fold_movement"][$i]["force"] =
                                    $v1[$i]["force"];
                                $sourceFaceData1['nose'][$prefix."nasolabial_fold_movement"][$i]["val"] =
                                    $v1[$i]["val"];
                                $sourceFaceData1['nose'][$prefix."nasolabial_fold_movement"][$i]["trend"] =
                                    $v1[$i]["trend"];
                                $sourceFaceData1['nose'][$prefix."nasolabial_fold_movement"][$i]["confidence"] =
                                    $v1[$i]["confidence"];
                            }
                        }
                    }
                }
                }*/
                if ($k === 'eye') {
                    $maxREW = $this->getFaceDataMaxForKeyV3($sourceFaceData1['eye'],'right_eye_width', "val");
                    $maxLEW = $this->getFaceDataMaxForKeyV3($sourceFaceData1['eye'],'left_eye_width', "val");
                    $maxREW2 = $this->getFaceDataMaxForKeyV3($sourceFaceData1['eye'],'right_eye_width2', "val");
                    $maxLEW2 = $this->getFaceDataMaxForKeyV3($sourceFaceData1['eye'],'left_eye_width2', "val");

                    if ($v != null)
                        foreach ($v as $k1 => $v1) {
                            //eye_width
                            if (($k1 === 'right_eye_width')||($k1 === 'left_eye_width')) {
                                if(strpos($k1,'right')>-1) $prefix = 'right_';
                                else $prefix = 'left_';
                                //---------------------------------------------------------------------------------------
                                for ($i = 1; $i < count($v1); $i++) {
                                    //определение закрытие глаза, когда ширина равна 50%
                                    if (//isset($v1[$i]["force"])&&
                                        isset($v1[$i]["val"])) {
                                       if($prefix === 'right_') {
                                           $val = round($maxREW*0.5);
                                           //width, расстояние между 37 и 41 для левого глаза, для правого - 43 и 47
                                           //альтернативно: width, расстояние между 38 и 40 для левого глаза, для правого - 44 и 46
        /*                                   echo $i.' : RE:43-47: '.round(($sourceFaceData1[$k][$prefix."eye_width"][$i]["val"]*100)/$maxREW).' % / '.
                                               $sourceFaceData1[$k][$prefix."eye_width"][$i]["val"].'<br>';
                                           echo $i.' : RE:44-46: '.round(($sourceFaceData1[$k][$prefix."eye_width2"][$i]["val"]*100)/$maxREW2).' % / '.
                                               $sourceFaceData1[$k][$prefix."eye_width2"][$i]["val"].'<br>';*/
                                       }
                                       else {
                                           $val = round($maxLEW*0.5); //50% - эвристическая оценка
         /*                                  echo $i.' : LE:37-41: '.round(($sourceFaceData1[$k][$prefix."eye_width"][$i]["val"]*100)/$maxLEW).' % / '.
                                               $sourceFaceData1[$k][$prefix."eye_width"][$i]["val"].'<br>';
                                           echo $i.' : LE:38-40: '.round(($sourceFaceData1[$k][$prefix."eye_width2"][$i]["val"]*100)/$maxLEW2).' % / '.
                                               $sourceFaceData1[$k][$prefix."eye_width2"][$i]["val"].'<br>';*/
                                       }
        //echo $i.':'.$sourceFaceData1[$k][$prefix."eye_width"][$i]["val"].'/'.$val.'<br>';
                                       if($sourceFaceData1[$k][$prefix."eye_width"][$i]["val"] <= $val)
                                            $sourceFaceData1[$k][$prefix."eye_closed"][$i]["val"] = 'yes';
                                        else
                                            $sourceFaceData1[$k][$prefix."eye_closed"][$i]["val"] = 'no';
                                    }
                                }
                                //---------------------------------------------------------------------------------------
                            }
                            //--------------------------------------------------------------------------------------------
                            //моргание
                            if (($k1 === 'right_eye_width_changing')||($k1 === 'left_eye_width_changing')) {
                                if(strpos($k1,'right')>-1) $prefix = 'right_';
                                else $prefix = 'left_';
                                //---------------------------------------------------------------------------------------
                                $eyeStartClosingFrame = '-1';
                                $eyeClosedFrame = '-1';
                                $eyeStartOpeningFrame = '-1';
                                $eyeEndOpeningFrame = '-1';
                                for ($i = 0; $i < count($v1); $i++) $sourceFaceData1[$k][$prefix."eye_blink"][$i]["val"] = 'no';

                                for ($i = 0; $i < count($v1); $i++) {
                                    //определение моргания: уменьшение, закрытие, предполагаем, что открытие длится столько же, сколько закрытие
                                    if (//isset($v1[$i]["trend"])&&
                                        isset($v1[$i]["val"])
                                        ) {
                                        //если глаз начинает закрываться, то фиксируем
                                        if (($v1[$i]["val"] === '-')&&($eyeStartClosingFrame === '-1')){
                                            $eyeStartClosingFrame = $i;
        //                                    $eyeStartOpeningFrame = '-1';
        //                                    $eyeClosedFrame = '-1';
                                        }
                                        //если глаз не закрывается, и не закрывался, то обнуляем
                                        if (($v1[$i]["val"] !== '-') && ($eyeClosedFrame === '-1')) {
                                            $eyeStartClosingFrame = '-1';
                                            $eyeStartOpeningFrame = '-1';
                                        }

                                        //если глаз закрыт и ранее это не фиксировалось, то фиксируем
                                        if (isset($sourceFaceData1[$k][$prefix."eye_closed"][$i]["val"]) &&
                                            ($sourceFaceData1[$k][$prefix."eye_closed"][$i]["val"] === 'yes') &&
                                            ($eyeClosedFrame === '-1')) $eyeClosedFrame = $i;

                                        //если глаз открыт и ранее фиксировалось его закрытие, то возможно моргание
                                        if (isset($sourceFaceData1[$k][$prefix."eye_closed"][$i]["val"]) &&
                                            ($sourceFaceData1[$k][$prefix."eye_closed"][$i]["val"] === 'no') &&
                                            ($eyeClosedFrame !== '-1')) {
                                            //processing
                                            //!!! эвристика - моргание - это закрытие глаза максиму на 14 кадров
                                           if(($eyeStartClosingFrame !== '-1') && (($i - $eyeClosedFrame) <= 14)) {
                                                //изменить значения свойств в диапазоне от $eyeStartClosingFrame до $eyeEndOpeningFrame
                                                $sourceFaceData1[$k][$prefix . "eye_blink"] =
                                                    $this->updateValues($sourceFaceData1[$k][$prefix . "eye_blink"], 'val',
                                                        //!!! эвристика - берем по 7 кадров на закрытие и открытие глаза
                                                        'yes', ($eyeClosedFrame - 7) , ($i + 7));
//                                                        'yes', $eyeStartClosingFrame, ($i + $eyeClosedFrame - $eyeStartClosingFrame));
        //                                       $eyeStartClosingFrame = $i + $eyeClosedFrame - $eyeStartClosingFrame;
                                            }
                                         $eyeClosedFrame = '-1';
                                         $eyeStartClosingFrame = -1;
                                        }
        //                                echo $i.' :: '.$eyeStartClosingFrame.'/'.$eyeClosedFrame.'/'.$v1[$i]["val"].'/'.
        //                                    $sourceFaceData1[$k][$prefix."eye_closed"][$i]["val"].'<br>';
                                    }
                                }
                                //---------------------------------------------------------------------------------------
                            }
                        }
                }
            }
        return $sourceFaceData1;
    }

    /**
     * Определение дополнительных проявлений, в частности
     * моргание
     * закрытие глаза на основе информации о движении зрачков по Ивану
     * @param $sourceFaceData1 - входной массив с лицевыми точками (landmarks)
     * @return array - выходной массив с обработанным массивом
     */
    public function detectAdditionalEyeFeatures($sourceFaceData1)
    {
        if ($sourceFaceData1 != null)
            foreach ($sourceFaceData1 as $k=>$v) {
                if ($k === 'eye') {
                    if ($v != null)
                        foreach ($v as $k1 => $v1) {
                            // анализируем движение зрачков по Ивану - left_eye_pupil_movement_x
                            //закрытие глаза
                            if (($k1 === 'left_eye_pupil_movement_x')||($k1 === 'right_eye_pupil_movement_x')) {
                                if(strpos($k1,'right')>-1) $prefix = 'right_';
                                 else $prefix = 'left_';
                                //---------------------------------------------------------------------------------------
                                for ($i = 1; $i < count($v1); $i++) {
                                    //определение закрытие глаза, когда интенсивность выше 100
                                    $val = 100; //!!!
                                    if (isset($v1[$i]["force"])) {
                                        if($sourceFaceData1[$k][$prefix."eye_pupil_movement_x"][$i]["force"] >= $val)
                                         $sourceFaceData1[$k][$prefix."eye_closed"][$i]["val"] = 'yes';
                                        else
                                            $sourceFaceData1[$k][$prefix."eye_closed"][$i]["val"] = 'no';
                                    }
                                }
                                //---------------------------------------------------------------------------------------
                            }
                        }
                }
            }
        if ($sourceFaceData1 != null)
            foreach ($sourceFaceData1 as $k=>$v) {
                if ($k === 'eye') {
                    if ($v != null)
                        foreach ($v as $k1 => $v1) {
                            //--------------------------------------------------------------------------------------------
                            //моргание
                            if (($k1 === 'right_eye_width_changing')||($k1 === 'left_eye_width_changing')) {
                                if(strpos($k1,'right')>-1) $prefix = 'right_';
                                else $prefix = 'left_';
                                //---------------------------------------------------------------------------------------
                                $eyeStartClosingFrame = '-1';
                                $eyeClosedFrame = '-1';
                                $eyeStartOpeningFrame = '-1';
                                $eyeEndOpeningFrame = '-1';
                                for ($i = 0; $i < count($v1); $i++) $sourceFaceData1[$k][$prefix."eye_blink"][$i]["val"] = 'no';

                                for ($i = 0; $i < count($v1); $i++) {
                                    //определение моргания: уменьшение, закрытие, предполагаем, что открытие длится столько же, сколько закрытие
                                    if (isset($v1[$i]["trend"])&&
                                     isset($v1[$i]["val"])
                                    ) {
                                        //если глаз начинает закрываться, то фиксируем
                                        if (($v1[$i]["val"] === '-')&&($eyeStartClosingFrame === '-1')
                                        && (strpos($v1[$i]["trend"],'+') == true)){
                                            $eyeStartClosingFrame = $i;
                                            $eyeStartOpeningFrame = '-1';
                                            //                                    $eyeClosedFrame = '-1';
                                        }
                                        //если глаз не закрывается, и не закрывался, то обнуляем
                                        if (($v1[$i]["val"] !== '-') && ($eyeClosedFrame === '-1')) {
                                            $eyeStartClosingFrame = '-1';
                                            $eyeStartOpeningFrame = '-1';
                                        }

                                        //если глаз закрыт и ранее это не фиксировалось, то фиксируем
                                        if (isset($sourceFaceData1[$k][$prefix."eye_closed"][$i]["val"]) &&
                                            ($sourceFaceData1[$k][$prefix."eye_closed"][$i]["val"] === 'yes') &&
                                            ($eyeClosedFrame === '-1')) {
                                            $eyeClosedFrame = $i;
//                                             echo $i.'<br>';
                                        }

                                        //если глаз открыт и ранее фиксировалось его закрытие, то фиксируем его окрывание
                                        if (isset($sourceFaceData1[$k][$prefix."eye_closed"][$i]["val"]) &&
                                            ($sourceFaceData1[$k][$prefix."eye_closed"][$i]["val"] === 'no') &&
                                            ($eyeClosedFrame != '-1')) {
                                            $eyeStartOpeningFrame = $i;
                                            $eyeEndOpeningFrame = -1;
                                        }

                                        //если глаз перестал открываться, то фиксируем
                                        if (($eyeStartOpeningFrame != '-1')
                                            && ((strpos($v1[$i]["trend"],'=') == true) || ($v1[$i]["val"] === '+'))){
                                            $eyeEndOpeningFrame = $i;
                                        }

                                        //если глаз открыт и ранее фиксировалось его закрытие, то возможно моргание
                                        //открытие глаза также зафиксировано
                                        if (isset($sourceFaceData1[$k][$prefix."eye_closed"][$i]["val"]) &&
                                            ($sourceFaceData1[$k][$prefix."eye_closed"][$i]["val"] === 'no') &&
                                            ($eyeClosedFrame != '-1') &&
                                            ($eyeStartOpeningFrame != '-1') && ($eyeEndOpeningFrame != '-1')) {
                                            //processing
                                            //открытие глаза считается по закрытию
                                            if(($eyeStartClosingFrame != '-1') ) {
//                                                echo '$eyeStartClosingFrame: '.$eyeStartClosingFrame.' $eyeClosedFrame: '.$eyeClosedFrame.
//                                                    ' $eyeStartOpeningFrame:'.$eyeStartOpeningFrame.' $eyeEndOpeningFrame: '. $eyeEndOpeningFrame.'<br>';
                                                //изменить значения свойств в диапазоне от $eyeStartClosingFrame до $eyeEndOpeningFrame
                                                $sourceFaceData1[$k][$prefix . "eye_blink"] =
                                                    $this->updateValues($sourceFaceData1[$k][$prefix . "eye_blink"], 'val',
 //                                                       'yes', $eyeStartClosingFrame, ($i + ($eyeClosedFrame - $eyeStartClosingFrame - 1)));
                                                'yes', $eyeStartClosingFrame, $eyeEndOpeningFrame);
                                            }

                                            /*
                                            //!!! эвристика - моргание - это закрытие глаза максиму на 14 кадров
                                            if(($eyeStartClosingFrame !== '-1') && (($i - $eyeClosedFrame) <= 14)) {
                                                //изменить значения свойств в диапазоне от $eyeStartClosingFrame до $eyeEndOpeningFrame
                                                $sourceFaceData1[$k][$prefix . "eye_blink"] =
                                                    $this->updateValues($sourceFaceData1[$k][$prefix . "eye_blink"], 'val',
                                                        //!!! эвристика - берем по 7 кадров на закрытие и открытие глаза
                                                        'yes', ($eyeClosedFrame - 7) , ($i + 7));
//                                                        'yes', $eyeStartClosingFrame, ($i + $eyeClosedFrame - $eyeStartClosingFrame));
                                                //                                       $eyeStartClosingFrame = $i + $eyeClosedFrame - $eyeStartClosingFrame;
                                            }
                                            */
                                            $eyeClosedFrame = '-1';
                                            $eyeStartClosingFrame = -1;
                                        }
                                        //                                echo $i.' :: '.$eyeStartClosingFrame.'/'.$eyeClosedFrame.'/'.$v1[$i]["val"].'/'.
                                        //                                    $sourceFaceData1[$k][$prefix."eye_closed"][$i]["val"].'<br>';
                                    }
                                }
                                //---------------------------------------------------------------------------------------
                            }
                        }
                }
            }
        return $sourceFaceData1;
    }

    //масштабирование точек маски
    //вход - массив с точками; точки, относительно которых происходит масштабирование
    //выход - отмасштабированные точки
    public function scaling($sourceFaceData1,$point1,$point2){
        // input data from the points levels
        if ($sourceFaceData1 != null) {
            $baseXVal = 0; $baseYVal = 0;
            for ($i = 0; $i < count($sourceFaceData1); $i++) {
                //--------------------------------------------------------------------------------------------------
                if (isset($sourceFaceData1[$i])) //frames
                    if (isset($sourceFaceData1[$i][$point1])
                        && isset($sourceFaceData1[$i][$point2])
                    ) {
                        if ($i == 0){
                         //it is a basic frame, then get the norm values
                            $baseXVal =  abs($sourceFaceData1[$i][$point1]['X'] - $sourceFaceData1[$i][$point2]['X']);
                            $baseYVal =  abs($sourceFaceData1[$i][$point1]['Y'] - $sourceFaceData1[$i][$point2]['Y']);
                        }else { //process frames? get the current frame values
                            $curXVal =  abs($sourceFaceData1[$i][$point1]['X'] - $sourceFaceData1[$i][$point2]['X']);
                            $curYVal =  abs($sourceFaceData1[$i][$point1]['Y'] - $sourceFaceData1[$i][$point2]['Y']);
 //                           $scKX = $baseXVal/$curXVal;
                            if ($curYVal != 0) $scKY = $baseYVal / $curYVal;
                             else $scKY = 1;
                            if ($curXVal != 0) $scKX = $baseXVal / $curXVal;
                             else $scKX = 1;
                            if ($scKY != 0)
                                foreach ($sourceFaceData1[$i] as $k1 => $v1) //points
                                    if (isset($sourceFaceData1[$i][$k1])) { //points $sourceFaceData3['normmask'][0][43]['X']
 //                                       $sourceFaceData1[$i][$k1]['X'] = round($scKX * $sourceFaceData1[$i][$k1]['X']);
                                        $sourceFaceData1[$i][$k1]['Y'] = round($scKY * $sourceFaceData1[$i][$k1]['Y']);
                                    }
                        }
//             echo $i.' : $baseYVal/$curYVal '.$baseYVal.'/'.$curYVal.' = '.$scKY.
//                 ' afterY: '.abs($sourceFaceData1[$i][$point1]['Y'] - $sourceFaceData1[$i][$point2]['Y']).' <br>';
                    }
                //---------------------------------------------------------------------------------------------------
            }
        }
        return $sourceFaceData1;
    }

    //поворот точек маски (горизонтирование)
    //вход - массив с точками; точки, относительно которых происходит поворот на горизонталь
    //выход - отнивилированные точки точки
    public function rotating($sourceFaceData1,$point1,$point2){
        // input data from the points levels
        if ($sourceFaceData1 != null) {
            for ($i = 0; $i < count($sourceFaceData1); $i++) {
                //--------------------------------------------------------------------------------------------------
                if (isset($sourceFaceData1[$i])) //frames
                    if (isset($sourceFaceData1[$i][$point1])
                        && isset($sourceFaceData1[$i][$point2])
                    ) {
                        //get  the equation of a linear function by 2 (39 and 42) points for each frame
                        //(y39-y42)x+(x42-x39)y+(x39*y42-x42*y39)=0
                        //when x=0 then y= - (x39*y42-x42*y39) / (x42-x39);
 /*                       $deltaY = abs(round(($sourceFaceData1[$i][$point1]['X'] * $sourceFaceData1[$i][$point2]['Y'] -
                                $sourceFaceData1[$i][$point2]['X'] * $sourceFaceData1[$i][$point1]['Y']) /
                            ($sourceFaceData1[$i][$point2]['X'] - $sourceFaceData1[$i][$point1]['X'])));

                        //get rotation angle, coordibates of 39 and 42 points are used
                        $rotationAngle = acos(abs($sourceFaceData1[$i][$point2]['X']) /
                            (sqrt(pow($sourceFaceData1[$i][$point2]['X'], 2) +
                                pow($sourceFaceData1[$i][$point2]['Y'] - $deltaY, 2))));*/
                        $distX = abs($sourceFaceData1[$i][$point2]['X'] - $sourceFaceData1[$i][$point1]['X']);
                        $distY = abs($sourceFaceData1[$i][$point2]['Y'] - $sourceFaceData1[$i][$point1]['Y']);

                        $divider = sqrt(pow($distX, 2) + pow($distY, 2));
                        if ($divider != 0)
                            $rotationAngle = acos($distX / $divider);
                        else
                            $rotationAngle = 0;

                        foreach ($sourceFaceData1[$i] as $k1 => $v1) { //points
                            if (isset($sourceFaceData1[$i][$k1])) { //points $sourceFaceData3['normmask'][0][43]['X']
                                $sourceFaceData1[$i][$k1]['X'] = round($sourceFaceData1[$i][$k1]['X'] * cos($rotationAngle) -
                                    $sourceFaceData1[$i][$k1]['Y'] * sin($rotationAngle));
                                $sourceFaceData1[$i][$k1]['Y'] = round($sourceFaceData1[$i][$k1]['X'] * sin($rotationAngle) +
                                    $sourceFaceData1[$i][$k1]['Y'] * cos($rotationAngle));
                            }
                        }
 //                       echo $i.': '.$sourceFaceData1[$i][$point1]['Y'].'/'.$sourceFaceData1[$i][$point2]['Y'].'<br>';
                    }
                //---------------------------------------------------------------------------------------------------
            }
        }
        return $sourceFaceData1;
    }

    //стабилизация точек маски относительно инварианта (неизменной точки)
    //вход - массив с точками; точки, относительно которых происходит определение инварианта
    //выход - стабилизированные точки
    public function stabilizating($sourceFaceData1,$point1,$point2){
        // input data from the points levels
        if ($sourceFaceData1 != null) {

//            $baseX = 0;
//            $baseY = 0;
            for ($i = 0; $i < count($sourceFaceData1); $i++) {
                //--------------------------------------------------------------------------------------------------
                if (isset($sourceFaceData1[$i])) //frames
                    if (isset($sourceFaceData1[$i][$point1])
                        && isset($sourceFaceData1[$i][$point2])
                    ) {
                        //precise positioning (stabilization) the 39 point is used
                        if(($i == 0) && (isset($sourceFaceData1[$i]))) {
                            $baseX = $sourceFaceData1[$i][$point1]['X'] +
                                round(($sourceFaceData1[$i][$point2]['X'] - $sourceFaceData1[$i][$point1]['X'])/2);
                            $baseY = $sourceFaceData1[$i][$point1]['Y'];
                        } elseif (($i == 1) && (isset($sourceFaceData1[$i]))) {
                            $baseX = $sourceFaceData1[$i][$point1]['X'] +
                                round(($sourceFaceData1[$i][$point2]['X'] - $sourceFaceData1[$i][$point1]['X'])/2);
                            $baseY = $sourceFaceData1[$i][$point1]['Y'];
                        }

                        $curX = $sourceFaceData1[$i][$point1]['X'] +
                            round(($sourceFaceData1[$i][$point2]['X'] - $sourceFaceData1[$i][$point1]['X'])/2);
                        $curY = $sourceFaceData1[$i][$point1]['Y'];
                        $deltaX = $curX - $baseX;
                        $deltaY = $curY - $baseY;

                        foreach ($sourceFaceData1[$i] as $k1 => $v1) { //points
                            if (isset($sourceFaceData1[$i][$k1])) { //points $sourceFaceData3['normmask'][0][43]['X']
                                $sourceFaceData1[$i][$k1]['X'] = round($sourceFaceData1[$i][$k1]['X'] - $deltaX);
                                $sourceFaceData1[$i][$k1]['Y'] = round($sourceFaceData1[$i][$k1]['Y'] - $deltaY);
                            }
                        }
/*                        $curX1 = $sourceFaceData1[$i][$point1]['X'] +
                            round(($sourceFaceData1[$i][$point2]['X'] - $sourceFaceData1[$i][$point1]['X'])/2);
                        $curY1 = $sourceFaceData1[$i][$point1]['Y'];
                        echo $i.' $baseX-Y: '.$baseX.'/'.$baseY.' $deltaX-Y: '.$deltaX.'/'.$deltaY.' Y-39-42: '.
                            $sourceFaceData1[$i][$point1]['Y'].'/'.$sourceFaceData1[$i][$point2]['Y'].' cur3942_X-Y: '.
                            $curX.'/'.$curY.'=>'.$curX1.'/'.$curY1.'<br>';*/

                    }
                //---------------------------------------------------------------------------------------------------
            }
        }
        return $sourceFaceData1;
    }

    public function processingOutliers($sourceFaceData1,$level,$neighborsCnt){
        //bt default $neighborsCnt = 1
        $resFaceData = array();
        $level = $level/100;
        if ($sourceFaceData1 != null)
            foreach ($sourceFaceData1 as $k => $v) //normpoints and triangles
                if ($v != null) {
                    for ($i = $neighborsCnt; $i < count($sourceFaceData1[$k]) - $neighborsCnt; $i++) {
                        if (isset($sourceFaceData1[$k][$i])) //frames
                            foreach ($sourceFaceData1[$k][$i] as $k1 => $v1) { //points
                                if (isset($sourceFaceData1[$k][$i-1][$k1]) && isset($sourceFaceData1[$k][$i+1][$k1]) &&
                                    isset($sourceFaceData1[$k][$i-1][$k1]['X'])) { //points $sourceFaceData3['normmask'][0][43]['X']
                                    $neighborLeftValueX = ($sourceFaceData1[$k][$i-1][$k1]['X']+
                                        $sourceFaceData1[$k][$i-1][$k1]['X']*$level);
                                    $neighborRightValueX = ($sourceFaceData1[$k][$i+1][$k1]['X']+
                                        $sourceFaceData1[$k][$i+1][$k1]['X']*$level);
 //                                   echo $neighborLeftValueX.'/'.$neighborRightValueX.'//'.$sourceFaceData1[$k][$i][$k1]['X'].'<br>';
                                    if (($neighborLeftValueX < $sourceFaceData1[$k][$i][$k1]['X'])&&
                                        ($neighborRightValueX < $sourceFaceData1[$k][$i][$k1]['X'])
                                    ) $sourceFaceData1[$k][$i][$k1]['X'] = (($sourceFaceData1[$k][$i-1][$k1]['X'] +
                                        $sourceFaceData1[$k][$i+1][$k1]['X'])/2);

                                    $neighborLeftValueY = ($sourceFaceData1[$k][$i-1][$k1]['Y']+
                                        $sourceFaceData1[$k][$i-1][$k1]['Y']*$level);
                                    $neighborRightValueY = ($sourceFaceData1[$k][$i+1][$k1]['Y']+
                                        $sourceFaceData1[$k][$i+1][$k1]['Y']*$level);
//                                    echo $neighborLeftValueY.'/'.$neighborRightValueY.'//'.$sourceFaceData1[$k][$i][$k1]['Y'].'<br>';
                                    if (($neighborLeftValueY < $sourceFaceData1[$k][$i][$k1]['Y'])&&
                                        ($neighborRightValueY < $sourceFaceData1[$k][$i][$k1]['Y'])
                                    ) $sourceFaceData1[$k][$i][$k1]['Y'] = ($sourceFaceData1[$k][$i-1][$k1]['Y'] +
                                            $sourceFaceData1[$k][$i+1][$k1]['Y'])/2;
                                }
                            }
                    }
                }
        return $sourceFaceData1;
    }
    /**
     * Сглаживание данных методом скользящего среднего.
     *
     * @param $sourceFaceData1 - входной массив с лицевыми точками (landmarks)
     * @return array - выходной массив с обработанным массивом
     */
    public function processingWithMovingAverage($sourceFaceData1, $cnt)
    {
     $resFaceData = array();
     if ($sourceFaceData1 != null)
         foreach ($sourceFaceData1 as $k => $v) //normpoints and triangles
             if (($v != null)and($k != 'gazeangle')) {
//        echo $k.' '.$v.'<br>';
                 for ($i = 0; $i < count($sourceFaceData1[$k]); $i++) {
                     if (isset($sourceFaceData1[$k][$i])) //frames
                         foreach ($sourceFaceData1[$k][$i] as $k1 => $v1) { //points
     //       if ($k!='normmask')              print_r($sourceFaceData1[$k][$i][$k1]);
            //                echo  '<br>';
                             if (isset($sourceFaceData1[$k][$i][$k1])) {
                                 //points $sourceFaceData3['normmask'][0][43]['X']
                                 ////print_r($sourceFaceData1[$k][$i][$k1]); echo  '<br>';
                                 $avSumX = 0;
                                 $avSumY = 0;
                                 $i2 = $i - $cnt + 1;
                                 if ($i2 < 0) $i2 = 0;
            //                   if($k1 == 61) $s = $i.'('.$i2.'/'.($i - $i2 + 1).')';
                                 if ($i > 0) {
                                     for ($i1 = $i; $i1 >= $i2; $i1--) {
                                         if (isset($sourceFaceData1[$k][$i1][$k1]) &&
                                             isset($sourceFaceData1[$k][$i1][$k1]['X']) &&
                                             isset($sourceFaceData1[$k][$i1][$k1]['Y'])) {
                                             $avSumX = $avSumX + $sourceFaceData1[$k][$i1][$k1]['X'];
                                             $avSumY = $avSumY + $sourceFaceData1[$k][$i1][$k1]['Y'];
            //                              if($k1 == 61) $s .= '['.$sourceFaceData1[$k][$i1][$k1]['X'].'/'.$sourceFaceData1[$k][$i1][$k1]['Y'].']';
                                         }
                                     }
                                     $avSumX = round($avSumX / ($i - $i2 + 1));
                                     $avSumY = round($avSumY / ($i - $i2 + 1));
                                 } else {
                                     if (isset($sourceFaceData1[$k][$i][$k1]['X']))
                                        $avSumX = $sourceFaceData1[$k][$i][$k1]['X'];
                                     if (isset($sourceFaceData1[$k][$i][$k1]['Y']))
                                        $avSumY = $sourceFaceData1[$k][$i][$k1]['Y'];
                                 }
            //                     if($k1 == 61) $s .= ' :'.$avSumX.'/'.$avSumY.'<br>';
            //                   if($k1 == 61) echo $s;
                                 $resFaceData[$k][$i][$k1]['X'] = $avSumX;
                                 $resFaceData[$k][$i][$k1]['Y'] = $avSumY;
                             }
                         }
                 }
                 //shift
                 $shiftCnt = round(($cnt-1)/2);
                 for ($i1 = 1; $i1 <= $shiftCnt; $i1++) {
                     if (is_array($resFaceData[$k])) array_shift($resFaceData[$k]);
//                     else echo $k.'<br>';
                 }

                 //add to the end of the array new values
                 for ($i1 = (count($sourceFaceData1[$k])  - $shiftCnt);
                       $i1 < (count($sourceFaceData1[$k])); $i1++) {
                     if (is_array($resFaceData[$k]))
                      array_push($resFaceData[$k], $sourceFaceData1[$k][$i1]);
                 }
             } else{ //for gazeangle
                 $resFaceData[$k] = $v;
             }
     return $resFaceData;
    }
    /**
     * Обнаружение признаков на основе анализа входных данных.
     *
     * @param $json - содержимое файла в формате json с лицевыми точками (landmarks)
     * @param $pointsFlag - тип точек (landmarks) для обработки: 0 - сырые, 1 - нормализованные
     * @return array - выходной массив с опредеделенными признаками
     */
    public function detectFeatures($json, $pointsFlag)
    {
        // load data
        if(strpos($json,'AUs') !== false) {
           $json = str_replace('{"AUs"',',{"AUs"',$json);
            $json =  trim($json, ',');
            $json = '['.$json.']';
        }

        $FaceData_ = json_decode($json, true);
        // check input format and convert the I and A formats to AB
        if(strpos($json,'NORM_POINTS') !== false) //I format
            $FaceData = $this->convertIJson($FaceData_);
        elseif(strpos($json,'AUs') !== false)   //A format
            $FaceData = $this->convertAJson($FaceData_);
        else
            $FaceData =  $FaceData_; // use the AB format

        //echo json_encode($FaceData['contours']).'<br>';

        $detectedFeatures = array();
        //----------------------------------------------------------------------------
        //----------------- norm points processing -----------------------------------
        if ((isset($FaceData['normmask'])) && ($pointsFlag == 1)) {
            $detectedFeatures = $this->addPointsToResults('normmask',
                'NORM_POINTS_ORIGIN', $FaceData, $detectedFeatures, '');

            $FaceData['normmask'] = $this->stabilizating($FaceData['normmask'], 39, 42);
            $detectedFeatures = $this->addPointsToResults('normmask',
                'NORM_POINTS_STABILIZED', $FaceData, $detectedFeatures, 'pp.3942');

            $FaceData['normmask'] = $this->rotating($FaceData['normmask'], 39, 42);
            $detectedFeatures = $this->addPointsToResults('normmask',
                'NORM_POINTS_ROTAITED', $FaceData, $detectedFeatures, 'pp.3942');

//        $FaceData['normmask'] = $this->scaling($FaceData['normmask'],27,28);
//        $detectedFeatures = $this->addPointsToResults('normmask',
//            'NORM_POINTS_SCALED',$FaceData,$detectedFeatures,'pp.2728');
        }

        if ((isset($FaceData['points'])) && ($pointsFlag == 0)) {
            //-------------------------- orig points processing ----------------------
             $detectedFeatures = $this->addPointsToResults('points',
                 'POINTS_ORIGIN',$FaceData,$detectedFeatures,'');

             $FaceData['points'] = $this->stabilizating($FaceData['points'],39,42);
             $detectedFeatures = $this->addPointsToResults('points',
                 'POINTS_STABILIZED',$FaceData,$detectedFeatures,'pp.3942');

             $FaceData['points'] = $this->rotating($FaceData['points'],39,42);
             $detectedFeatures = $this->addPointsToResults('points',
                 'POINTS_ROTAITED',$FaceData,$detectedFeatures,'pp.3942');
        }
        // ------------------------ зрачки ----------------------------------------
//        $FaceData['normirises'] = $this->stabilizating($FaceData['normirises'],0,1);
        if (isset($FaceData['normirises']))
         $FaceData['normirises'] = $this->rotating($FaceData['normirises'],0,1);
 //       $FaceData['normirises'] = $this->scaling($FaceData['normirises'],0,1);

//        $FaceData['origirises'] = $this->stabilizating($FaceData['origirises'],0,1);
        if (isset($FaceData['origirises']))
         $FaceData['origirises'] = $this->rotating($FaceData['origirises'],0,1);
//        $FaceData['origirises'] = $this->scaling($FaceData['origirises'],0,1);
        //---------------------------------------------------------------------------
        if ((isset($FaceData['normmask'])) && ($pointsFlag == 1)) {
            $FaceData = $this->processingOutliers($FaceData, 10, 1);
            $detectedFeatures = $this->addPointsToResults('normmask',
                'NORM_POINTS_OUTLIER', $FaceData, $detectedFeatures, 'outlier_level_percent(10)outlier_neighbors(1)');
            $FaceData = $this->processingWithMovingAverage($FaceData, 3);
            $detectedFeatures = $this->addPointsToResults('normmask',
                'NORM_POINTS_OUTLIER_MA', $FaceData, $detectedFeatures, 'smoth_order(3)');
            $FaceData = $this->processingWithMovingAverage($FaceData, 5);
            $detectedFeatures = $this->addPointsToResults('normmask',
                'NORM_POINTS_OUTLIER_MA', $FaceData, $detectedFeatures, 'smoth_order(3_5)');

            $detectedFeatures['eye'] = $this->detectEyeFeatures($FaceData['normmask'],'eye',39,42);
            $detectedFeatures['mouth'] = $this->detectMouthFeatures($FaceData['normmask'],'mouth',39,42);
            $detectedFeatures['brow'] = $this->detectBrowFeatures($FaceData['normmask'],'brow',39,42);
            $detectedFeatures['eyebrow'] = $this->detectEyeBrowFeatures($FaceData['normmask'],'eyebrow',39,42);
            $detectedFeatures['nose'] = $this->detectNoseFeatures($FaceData['normmask'],'nose', 39,42);
            $detectedFeatures['chin'] = $this->detectChinFeatures($FaceData['normmask'],'chin',39,42);
        }

        if ((isset($FaceData['points'])) && ($pointsFlag == 0)){
            //------------------- origin points processing ------------------------------
           $detectedFeatures = $this->addPointsToResults('points',
               'POINTS_OUTLIER',$FaceData,$detectedFeatures,'outlier_level_percent(10)outlier_neighbors(1)');
           $FaceData = $this->processingWithMovingAverage($FaceData,3);
           $detectedFeatures = $this->addPointsToResults('points',
               'POINTS_OUTLIER_MA',$FaceData,$detectedFeatures,'smoth_order(3)');
           $FaceData = $this->processingWithMovingAverage($FaceData,5);
           $detectedFeatures = $this->addPointsToResults('points',
               'POINTS_OUTLIER_MA',$FaceData,$detectedFeatures,'smoth_order(3_5)');

           $detectedFeatures['eye'] = $this->detectEyeFeatures($FaceData['points'],'eye',39,42);
           $detectedFeatures['mouth'] = $this->detectMouthFeatures($FaceData['points'],'mouth',39,42);
           $detectedFeatures['brow'] = $this->detectBrowFeatures($FaceData['points'],'brow',39,42);
           $detectedFeatures['eyebrow'] = $this->detectEyeBrowFeatures($FaceData['points'],'eyebrow',39,42);
           $detectedFeatures['nose'] = $this->detectNoseFeatures($FaceData['points'],'nose', 39,42);
           $detectedFeatures['chin'] = $this->detectChinFeatures($FaceData['points'],'chin',39,42);
        }
//                $this->saveXY2($FaceData,'m1.json');
        /*         $fd = fopen('_MA.json', "w");
                     fwrite($fd,json_encode($FaceData));
                     fclose($fd);*/
        if (isset($FaceData['normirises']))
            $detectedFeatures = $this->detectIrises($detectedFeatures,
                $FaceData['normirises'], 'eye','');
        if (isset($FaceData['origirises']))
            $detectedFeatures = $this->detectIrises($detectedFeatures,
                $FaceData['origirises'], 'eye','_orig');
        if (isset($FaceData['gazeangle']))
            $detectedFeatures = $this->detectIrisesA($detectedFeatures,
                $FaceData["gazeangle"], 'eye','');

//        if (isset($FaceData['contours']))
//            $detectedFeatures = $this->detectAdditionalNoseFeatures($detectedFeatures,
//                $FaceData["contours"], 'nose','');

        $detectedFeaturesWithTrends = $this->detectTrends($detectedFeatures,5);
        $detectedFeaturesWithTrends = $this->detectAdditionalEyeFeatures($detectedFeaturesWithTrends);

        return $detectedFeaturesWithTrends;
    }

    /**
     * Поиск соответствий между форматами МОП и МИП.
     *
     * @param $sourceFacePart - название части лица от МОП
     * @param $sourceFeatureName - название признака от МОП
     * @param $sourceValue - значение признака от МОП
     * @return array - массив значений для МИП
     */
    public static function findCorrespondences($sourceFacePart, $sourceFeatureName, $sourceValue)
    {
        // Формирование пустого целевого массива с лицевыми признаками для МИП
        $targetValues = array();
        $targetValues['targetFacePart'] = null;
        $targetValues['featureChangeType'] = null;
        $targetValues['changeDirection'] = null;
        /* Соответствия для лба */
        if ($sourceFacePart == 'brow')
            $targetValues['targetFacePart'] = 'Лоб';
        if ($sourceFeatureName == 'brow_width')
            $targetValues['featureChangeType'] = 'Изменение размера по вертикали';
        if ($sourceValue == '-')
            $targetValues['changeDirection'] = 'Уменьшение';
        if ($sourceValue == '+')
            $targetValues['changeDirection'] = 'Увеличение';
        /* Соответствия для брови */
        if ($sourceFacePart == 'eyebrow')
            $targetValues['targetFacePart'] = 'Бровь';
        if (($sourceFeatureName == 'left_eyebrow_movement_x') || ($sourceFeatureName == 'left_eyebrow_movement_y'))
            $targetValues['targetFacePart'] = 'Левая бровь';
        if (($sourceFeatureName == 'right_eyebrow_movement_x') || ($sourceFeatureName == 'right_eyebrow_movement_y') )
            $targetValues['targetFacePart'] = 'Правая бровь';
/*
        if ((($sourceFeatureName == 'left_eyebrow_movement_x') || ($sourceFeatureName == 'right_eyebrow_movement_x')
            || ($sourceFeatureName == 'left_eyebrow_movement_y') || ($sourceFeatureName == 'right_eyebrow_movement_y')) &&
            ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Отсутствие типа';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }*/
        if ((($sourceFeatureName == 'left_eyebrow_movement_x') || ($sourceFeatureName == 'right_eyebrow_movement_x')) &&
            ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Изменение положения по горизонтали';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if ((($sourceFeatureName == 'left_eyebrow_movement_y') || ($sourceFeatureName == 'right_eyebrow_movement_y')) &&
            ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if ((($sourceFeatureName == 'left_eyebrow_movement_x') || ($sourceFeatureName == 'right_eyebrow_movement_x')) &&
            ($sourceValue == 'to center')) {
            $targetValues['featureChangeType'] = 'Изменение положения по горизонтали';
            $targetValues['changeDirection'] = 'К центру';
        }
        if ((($sourceFeatureName == 'left_eyebrow_movement_x') || ($sourceFeatureName == 'right_eyebrow_movement_x')) &&
            ($sourceValue == 'from center')) {
            $targetValues['featureChangeType'] = 'Изменение положения по горизонтали';
            $targetValues['changeDirection'] = 'От центра';
        }
        if ((($sourceFeatureName == 'left_eyebrow_movement_y') || ($sourceFeatureName == 'right_eyebrow_movement_y')) &&
            ($sourceValue == 'up')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вверх';
        }
        if ((($sourceFeatureName == 'left_eyebrow_movement_y') || ($sourceFeatureName == 'right_eyebrow_movement_y')) &&
            ($sourceValue == 'down')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вниз';
        }
        if (($sourceFeatureName == 'left_eyebrow_inner_movement_x') || ($sourceFeatureName == 'left_eyebrow_inner_movement_y'))
            $targetValues['targetFacePart'] = 'Внутренний уголок левой брови';
        if (($sourceFeatureName == 'right_eyebrow_inner_movement_x') || ($sourceFeatureName == 'right_eyebrow_inner_movement_y'))
            $targetValues['targetFacePart'] = 'Внутренний уголок правой брови';

/*        if ((($sourceFeatureName == 'left_eyebrow_inner_movement_x') ||
                ($sourceFeatureName == 'right_eyebrow_inner_movement_x') ||
                ($sourceFeatureName == 'left_eyebrow_inner_movement_y') ||
                ($sourceFeatureName == 'right_eyebrow_inner_movement_y')) &&
            ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Отсутствие типа';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }*/
        if ((($sourceFeatureName == 'left_eyebrow_inner_movement_x') ||
                ($sourceFeatureName == 'right_eyebrow_inner_movement_x')) &&
            ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Изменение положения по горизонтали';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if ((($sourceFeatureName == 'left_eyebrow_inner_movement_y') ||
                ($sourceFeatureName == 'right_eyebrow_inner_movement_y')) &&
            ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if ((($sourceFeatureName == 'left_eyebrow_inner_movement_x') ||
                ($sourceFeatureName == 'right_eyebrow_inner_movement_x')) &&
            ($sourceValue == 'to center')) {
            $targetValues['featureChangeType'] = 'Изменение положения по горизонтали';
            $targetValues['changeDirection'] = 'К центру';
        }
        if ((($sourceFeatureName == 'left_eyebrow_inner_movement_x') ||
                ($sourceFeatureName == 'right_eyebrow_inner_movement_x')) &&
            ($sourceValue == 'from center')) {
            $targetValues['featureChangeType'] = 'Изменение положения по горизонтали';
            $targetValues['changeDirection'] = 'От центра';
        }
        if ((($sourceFeatureName == 'left_eyebrow_inner_movement_y') ||
            ($sourceFeatureName == 'right_eyebrow_inner_movement_y')) &&
        ($sourceValue == 'up')) {
        $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
        $targetValues['changeDirection'] = 'Вверх';
        }
        if ((($sourceFeatureName == 'left_eyebrow_inner_movement_y') ||
                ($sourceFeatureName == 'right_eyebrow_inner_movement_y')) &&
            ($sourceValue == 'down')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вниз';
        }
 /*       if ((($sourceFeatureName == 'left_eyebrow_inner_movement') ||
                ($sourceFeatureName == 'right_eyebrow_inner_movement')) &&
            ($sourceValue == 'to center and up')) {
            $targetValues['featureChangeType'] = 'Изменение положения по диагонали';
            $targetValues['changeDirection'] = 'К центру и вверх';
        }
        if (($sourceFeatureName == 'right_eyebrow_inner_movement') && ($sourceValue == 'to center and down')) {
            $targetValues['featureChangeType'] = 'Изменение положения по диагонали';
            $targetValues['changeDirection'] = 'К центру и вниз';
        }*/
        if ($sourceFeatureName == 'left_eyebrow_outer_movement')
            $targetValues['targetFacePart'] = 'Внешний уголок левой брови';
        if ($sourceFeatureName == 'right_eyebrow_outer_movement')
            $targetValues['targetFacePart'] = 'Внешний уголок правой брови';

        if ((($sourceFeatureName == 'left_eyebrow_outer_movement') ||
                ($sourceFeatureName == 'right_eyebrow_outer_movement')) &&
            ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Отсутствие типа';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }

        if ((($sourceFeatureName == 'left_eyebrow_outer_movement') ||
                ($sourceFeatureName == 'right_eyebrow_outer_movement')) &&
            ($sourceValue == 'up')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вверх';
        }
        if ((($sourceFeatureName == 'left_eyebrow_outer_movement') ||
                ($sourceFeatureName == 'right_eyebrow_outer_movement')) &&
            ($sourceValue == 'down')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вниз';
        }
        /* Соответствия для глаз */
        // Глаза
        if ($sourceFeatureName == 'left_eye_width_changing')
            $targetValues['targetFacePart'] = 'Левый глаз';
        if ($sourceFeatureName == 'right_eye_width_changing')
            $targetValues['targetFacePart'] = 'Правый глаз';
        if ((($sourceFeatureName == 'left_eye_width_changing') || ($sourceFeatureName == 'right_eye_width_changing')) &&
            ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Отсутствие типа';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if ((($sourceFeatureName == 'left_eye_width_changing') || ($sourceFeatureName == 'right_eye_width_changing')) &&
            ($sourceValue == '+')) {
            $targetValues['featureChangeType'] = 'Изменение размера по вертикали';
            $targetValues['changeDirection'] = 'Увеличение';
        }
        if ((($sourceFeatureName == 'left_eye_width_changing') || ($sourceFeatureName == 'right_eye_width_changing')) &&
            ($sourceValue == '-')) {
            $targetValues['featureChangeType'] = 'Изменение размера по вертикали';
            $targetValues['changeDirection'] = 'Уменьшение';
        }
        // Нижнии веки
        if (($sourceFeatureName == 'left_eye_lower_eyelid_movement_x') ||
            ($sourceFeatureName == 'left_eye_lower_eyelid_movement_y') ||
            ($sourceFeatureName == 'left_eye_lower_eyelid_movement_d'))
            $targetValues['targetFacePart'] = 'Нижнее веко левого глаза';
        if (($sourceFeatureName == 'right_eye_lower_eyelid_movement_x') ||
            ($sourceFeatureName == 'right_eye_lower_eyelid_movement_y') ||
            ($sourceFeatureName == 'right_eye_lower_eyelid_movement_d'))
            $targetValues['targetFacePart'] = 'Нижнее веко правого глаза';

 /*       if ((($sourceFeatureName == 'left_eye_lower_eyelid_movement_x') ||
                ($sourceFeatureName == 'right_eye_lower_eyelid_movement_x') ||
                ($sourceFeatureName == 'left_eye_lower_eyelid_movement_y') ||
                ($sourceFeatureName == 'right_eye_lower_eyelid_movement_y') ||
                ($sourceFeatureName == 'left_eye_lower_eyelid_movement_d') ||
                ($sourceFeatureName == 'right_eye_lower_eyelid_movement_d')) &&
            (($sourceValue == 'none') || ($sourceValue == 'none and none'))) {
            $targetValues['featureChangeType'] = 'Отсутствие типа';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }*/
        if ((($sourceFeatureName == 'left_eye_lower_eyelid_movement_x') ||
                ($sourceFeatureName == 'right_eye_lower_eyelid_movement_x')) &&
            ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Изменение положения по горизонтали';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if ((($sourceFeatureName == 'left_eye_lower_eyelid_movement_x') ||
                ($sourceFeatureName == 'right_eye_lower_eyelid_movement_x')) &&
            ($sourceValue == 'to center')) {
            $targetValues['featureChangeType'] = 'Изменение положения по горизонтали';
            $targetValues['changeDirection'] = 'К центру';
        }
        if ((($sourceFeatureName == 'left_eye_lower_eyelid_movement_x') ||
                ($sourceFeatureName == 'right_eye_lower_eyelid_movement_x')) &&
            ($sourceValue == 'from center')) {
            $targetValues['featureChangeType'] = 'Изменение положения по горизонтали';
            $targetValues['changeDirection'] = 'От центра';
        }
        if ((($sourceFeatureName == 'left_eye_lower_eyelid_movement_y') ||
                ($sourceFeatureName == 'right_eye_lower_eyelid_movement_y')) &&
            ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if ((($sourceFeatureName == 'left_eye_lower_eyelid_movement_y') ||
                ($sourceFeatureName == 'right_eye_lower_eyelid_movement_y')) &&
            ($sourceValue == 'up')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вверх';
        }
        if ((($sourceFeatureName == 'left_eye_lower_eyelid_movement_y') ||
                ($sourceFeatureName == 'right_eye_lower_eyelid_movement_y')) &&
            ($sourceValue == 'down')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вниз';
        }
  /*      if ((($sourceFeatureName == 'left_eye_lower_eyelid_movement_d') ||
                ($sourceFeatureName == 'right_eye_lower_eyelid_movement_d')) &&
            ($sourceValue != 'to center and up')) {
            $targetValues['featureChangeType'] = 'Изменение положения по диагонали';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if ((($sourceFeatureName == 'left_eye_lower_eyelid_movement_d') ||
                ($sourceFeatureName == 'right_eye_lower_eyelid_movement_d')) &&
            ($sourceValue == 'to center and up')) {
            $targetValues['featureChangeType'] = 'Изменение положения по диагонали';
            $targetValues['changeDirection'] = 'К центру и вверх';
        }*/

        // Верхнии веки
        if ($sourceFeatureName == 'left_eye_upper_eyelid_movement')
            $targetValues['targetFacePart'] = 'Верхнее веко левого глаза';
        if ($sourceFeatureName == 'right_eye_upper_eyelid_movement')
            $targetValues['targetFacePart'] = 'Верхнее веко правого глаза';
        if ((($sourceFeatureName == 'left_eye_upper_eyelid_movement') ||
                ($sourceFeatureName == 'right_eye_upper_eyelid_movement')) &&
            ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Отсутствие типа';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if ((($sourceFeatureName == 'left_eye_upper_eyelid_movement') ||
                ($sourceFeatureName == 'right_eye_upper_eyelid_movement')) &&
            ($sourceValue == 'up')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вверх';
        }
        if ((($sourceFeatureName == 'left_eye_upper_eyelid_movement') ||
                ($sourceFeatureName == 'right_eye_upper_eyelid_movement')) &&
            ($sourceValue == 'down')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вниз';
        }
        // Зрачки
        if (($sourceFeatureName == 'left_eye_pupil_movement_x') || ($sourceFeatureName == 'left_eye_pupil_movement_y')
            || ($sourceFeatureName == 'left_eye_pupil_movement_d'))
            $targetValues['targetFacePart'] = 'Левый зрачок';
        if (($sourceFeatureName == 'right_eye_pupil_movement_x') || ($sourceFeatureName == 'right_eye_pupil_movement_y')
            || ($sourceFeatureName == 'right_eye_pupil_movement_d'))
            $targetValues['targetFacePart'] = 'Правый зрачок';

        if ((($sourceFeatureName == 'left_eye_pupil_movement_d') || ($sourceFeatureName == 'right_eye_pupil_movement_d')) &&
            ($sourceValue == 'none and none')) {
            $targetValues['featureChangeType'] = 'Отсутствие типа';
            $targetValues['changeDirection'] = 'Прямо перед собой';
        }
        if ((($sourceFeatureName == 'left_eye_pupil_movement_x') || ($sourceFeatureName == 'right_eye_pupil_movement_x')) &&
            ($sourceValue == 'left')) {
            $targetValues['featureChangeType'] = 'Изменение положения по горизонтали';
            $targetValues['changeDirection'] = 'Влево';
        }
        if ((($sourceFeatureName == 'left_eye_pupil_movement_x') || ($sourceFeatureName == 'right_eye_pupil_movement_x')) &&
            ($sourceValue == 'right')) {
            $targetValues['featureChangeType'] = 'Изменение положения по горизонтали';
            $targetValues['changeDirection'] = 'Вправо';
        }
        if ((($sourceFeatureName == 'left_eye_pupil_movement_y') || ($sourceFeatureName == 'right_eye_pupil_movement_y')) &&
            ($sourceValue == 'up')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вверх';
        }
        if ((($sourceFeatureName == 'left_eye_pupil_movement_y') || ($sourceFeatureName == 'right_eye_pupil_movement_y')) &&
            ($sourceValue == 'down')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вниз';
        }
        if ((($sourceFeatureName == 'left_eye_pupil_movement_d') || ($sourceFeatureName == 'right_eye_pupil_movement_d')) &&
            ($sourceValue == 'up and right')) {
            $targetValues['featureChangeType'] = 'Изменение положения по диагонали';
            $targetValues['changeDirection'] = 'Вверх и вправо';
        }
        if ((($sourceFeatureName == 'left_eye_pupil_movement_d') || ($sourceFeatureName == 'right_eye_pupil_movement_d')) &&
            ($sourceValue == 'down and right')) {
            $targetValues['featureChangeType'] = 'Изменение положения по диагонали';
            $targetValues['changeDirection'] = 'Вниз и вправо';
        }
        if ((($sourceFeatureName == 'left_eye_pupil_movement_d') || ($sourceFeatureName == 'right_eye_pupil_movement_d')) &&
            ($sourceValue == 'up and left')) {
            $targetValues['featureChangeType'] = 'Изменение положения по диагонали';
            $targetValues['changeDirection'] = 'Вверх и влево';
        }
        if ((($sourceFeatureName == 'left_eye_pupil_movement_d') || ($sourceFeatureName == 'right_eye_pupil_movement_d')) &&
            ($sourceValue == 'down and left')) {
            $targetValues['featureChangeType'] = 'Изменение положения по диагонали';
            $targetValues['changeDirection'] = 'Вниз и влево';
        }

        // Уголки глаз
        if ($sourceFeatureName == 'left_eye_inner_movement')
            $targetValues['targetFacePart'] = 'Внутренний уголок левого глаза';
        if ($sourceFeatureName == 'right_eye_inner_movement')
            $targetValues['targetFacePart'] = 'Внутренний уголок правого глаза';
        if ((($sourceFeatureName == 'left_eye_inner_movement') || ($sourceFeatureName == 'right_eye_inner_movement')) &&
            ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Отсутствие типа';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if ((($sourceFeatureName == 'left_eye_inner_movement') || ($sourceFeatureName == 'right_eye_inner_movement')) &&
            ($sourceValue == 'up')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вверх';
        }
        if ((($sourceFeatureName == 'left_eye_inner_movement') || ($sourceFeatureName == 'right_eye_inner_movement')) &&
            ($sourceValue == 'down')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вниз';
        }
        if ($sourceFeatureName == 'left_eye_outer_movement')
            $targetValues['targetFacePart'] = 'Внешний уголок левого глаза';
        if ($sourceFeatureName == 'right_eye_outer_movement')
            $targetValues['targetFacePart'] = 'Внешний уголок правого глаза';
        if ((($sourceFeatureName == 'left_eye_outer_movement') || ($sourceFeatureName == 'right_eye_outer_movement')) &&
            ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Отсутствие типа';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if ((($sourceFeatureName == 'left_eye_outer_movement') || ($sourceFeatureName == 'right_eye_outer_movement')) &&
            ($sourceValue == 'up')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вверх';
        }
        if ((($sourceFeatureName == 'left_eye_outer_movement') || ($sourceFeatureName == 'right_eye_outer_movement')) &&
            ($sourceValue == 'down')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вниз';
        }
        // Закрытие глаз
        if ($sourceFeatureName == 'left_eye_closed')
            $targetValues['targetFacePart'] = 'Левый глаз';
        if ($sourceFeatureName == 'right_eye_closed')
            $targetValues['targetFacePart'] = 'Правый глаз';
        if ((($sourceFeatureName == 'left_eye_closed') || ($sourceFeatureName == 'right_eye_closed')) &&
            ($sourceValue == 'yes')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Глаз закрыт';
        }
        if ((($sourceFeatureName == 'left_eye_closed') || ($sourceFeatureName == 'right_eye_closed')) &&
            ($sourceValue == 'no')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Глаз открыт';
        }
        // Моргание
        if ($sourceFeatureName == 'left_eye_blink')
            $targetValues['targetFacePart'] = 'Левый глаз';
        if ($sourceFeatureName == 'right_eye_blink')
            $targetValues['targetFacePart'] = 'Правый глаз';
        if ((($sourceFeatureName == 'left_eye_blink') || ($sourceFeatureName == 'right_eye_blink')) &&
            ($sourceValue == 'yes')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Глаз моргает';
        }
        if ((($sourceFeatureName == 'left_eye_blink') || ($sourceFeatureName == 'right_eye_blink')) &&
            ($sourceValue == 'no')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Глаз не моргает';
        }
        /* Соответствия для рта */
        // Размер и форма рта
        if ($sourceFacePart == 'mouth')
            $targetValues['targetFacePart'] = 'Рот';
        if ($sourceFeatureName == 'mouth_form')
            $targetValues['targetFacePart'] = 'Рот';
        if (($sourceFeatureName == 'mouth_form') && ($sourceValue == 'ellipse')) {
            $targetValues['featureChangeType'] = 'Изменение формы';
            $targetValues['changeDirection'] = 'Овал';
        }
        if (($sourceFeatureName == 'mouth_form') && ($sourceValue == 'rectangle')) {
            $targetValues['featureChangeType'] = 'Изменение формы';
            $targetValues['changeDirection'] = 'Прямоугольник';
        }
        if ($sourceFeatureName == 'mouth_length')
            $targetValues['targetFacePart'] = 'Рот';
        if (($sourceFeatureName == 'mouth_length') && ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Изменение размера по горизонтали';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if (($sourceFeatureName == 'mouth_length') && ($sourceValue == '-')) {
            $targetValues['featureChangeType'] = 'Изменение размера по горизонтали';
            $targetValues['changeDirection'] = 'Уменьшение';
        }
        if (($sourceFeatureName == 'mouth_length') && ($sourceValue == '+')) {
            $targetValues['featureChangeType'] = 'Изменение размера по горизонтали';
            $targetValues['changeDirection'] = 'Увеличение';
        }
        if ($sourceFeatureName == 'mouth_width')
            $targetValues['targetFacePart'] = 'Рот';
        if (($sourceFeatureName == 'mouth_width') && ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Изменение размера по вертикали';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if (($sourceFeatureName == 'mouth_width') && ($sourceValue == '-')) {
            $targetValues['featureChangeType'] = 'Изменение размера по вертикали';
            $targetValues['changeDirection'] = 'Уменьшение';
        }
        if (($sourceFeatureName == 'mouth_width') && ($sourceValue == '+')) {
            $targetValues['featureChangeType'] = 'Изменение размера по вертикали';
            $targetValues['changeDirection'] = 'Увеличение';
        }
        if (($sourceFeatureName == 'mouth_width') && ($sourceValue == 'compressed')) {
            $targetValues['featureChangeType'] = 'Изменение размера по вертикали';
            $targetValues['changeDirection'] = 'Сжатие';
        }
        // Уголки рта
        if (($sourceFeatureName == 'left_corner_mouth_movement_x') || ($sourceFeatureName == 'left_corner_mouth_movement_y'))
            $targetValues['targetFacePart'] = 'Левый уголок рта';
        if (($sourceFeatureName == 'right_corner_mouth_movement_x') || ($sourceFeatureName == 'right_corner_mouth_movement_y'))
            $targetValues['targetFacePart'] = 'Правый уголок рта';

 /*       if ((($sourceFeatureName == 'left_corner_mouth_movement_x') ||
                ($sourceFeatureName == 'right_corner_mouth_movement_x') ||
                ($sourceFeatureName == 'left_corner_mouth_movement_y') ||
                ($sourceFeatureName == 'right_corner_mouth_movement_y')) &&
            ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Отсутствие типа';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }*/
        if ((($sourceFeatureName == 'left_corner_mouth_movement_x') ||
                ($sourceFeatureName == 'right_corner_mouth_movement_x')) &&
            ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Изменение положения по горизонтали';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if ((($sourceFeatureName == 'left_corner_mouth_movement_x') ||
                ($sourceFeatureName == 'right_corner_mouth_movement_x')) &&
            ($sourceValue == 'from center')) {
            $targetValues['featureChangeType'] = 'Изменение положения по горизонтали';
            $targetValues['changeDirection'] = 'От центра в стороны';
        }
        if ((($sourceFeatureName == 'left_corner_mouth_movement_x') ||
                ($sourceFeatureName == 'right_corner_mouth_movement_x')) &&
            ($sourceValue == 'to center')) {
            $targetValues['featureChangeType'] = 'Изменение положения по горизонтали';
            $targetValues['changeDirection'] = 'К центру';
        }
        if ((($sourceFeatureName == 'left_corner_mouth_movement_y') ||
                ($sourceFeatureName == 'right_corner_mouth_movement_y')) &&
            ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if ((($sourceFeatureName == 'left_corner_mouth_movement_y') ||
                ($sourceFeatureName == 'right_corner_mouth_movement_y')) &&
            ($sourceValue == 'up')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вверх';
        }
        if ((($sourceFeatureName == 'left_corner_mouth_movement_y') ||
                ($sourceFeatureName == 'right_corner_mouth_movement_y')) &&
            ($sourceValue == 'down')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вниз';
        }
        // Губы
        if ($sourceFeatureName == 'mouth_upper_lip_outer_center_movement')
            $targetValues['targetFacePart'] = 'Верхняя губа';
        if ($sourceFeatureName == 'mouth_lower_lip_outer_center_movement')
            $targetValues['targetFacePart'] = 'Нижняя губа';
        if ((($sourceFeatureName == 'mouth_upper_lip_outer_center_movement') ||
                ($sourceFeatureName == 'mouth_lower_lip_outer_center_movement')) &&
            ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if ((($sourceFeatureName == 'mouth_upper_lip_outer_center_movement') ||
                ($sourceFeatureName == 'mouth_lower_lip_outer_center_movement')) &&
            ($sourceValue == 'up')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вверх';
        }
        if ((($sourceFeatureName == 'mouth_upper_lip_outer_center_movement') ||
                ($sourceFeatureName == 'mouth_lower_lip_outer_center_movement')) &&
            ($sourceValue == 'down')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вниз';
        }
        /* Соответствия для подбородка */
        if ($sourceFacePart == 'chin')
            $targetValues['targetFacePart'] = 'Подбородок';
        if ($sourceFeatureName == 'chin_movement')
            $targetValues['targetFacePart'] = 'Подбородок';
        if (($sourceFeatureName == 'chin_movement') && ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Отсутствие типа';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if (($sourceFeatureName == 'chin_movement') && ($sourceValue == 'down')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вниз';
        }
        if (($sourceFeatureName == 'chin_movement') && ($sourceValue == 'up')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вверх';
        }
        /* Соответствия для носа */
        // Крылья носа
        if (($sourceFacePart == 'nose') || ($sourceFacePart == 'nose_movement') || ($sourceFacePart == 'nose_width_changing'))
            $targetValues['targetFacePart'] = 'Нос';
        if ($sourceFeatureName == 'nose_wing_movement')
            $targetValues['targetFacePart'] = 'Крылья носа';
        if (($sourceFeatureName == 'nose_wing_movement') && ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Отсутствие типа';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if (($sourceFeatureName == 'nose_movement') && ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if (($sourceFeatureName == 'nose_movement') && ($sourceValue == 'up')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вверх';
        }
        if (($sourceFeatureName == 'nose_movement') && ($sourceValue == 'down')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вниз';
        }
        if (($sourceFeatureName == 'nose_width_changing') && ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Изменение положения по горизонтали';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if (($sourceFeatureName == 'nose_width_changing') && ($sourceValue == '+')) {
            $targetValues['featureChangeType'] = 'Изменение положения по горизонтали';
            $targetValues['changeDirection'] = 'Увеличение';
        }
        if (($sourceFeatureName == 'nose_width_changing') && ($sourceValue == '-')) {
            $targetValues['featureChangeType'] = 'Изменение положения по горизонтали';
            $targetValues['changeDirection'] = 'Уменьшение';
        }
        if (($sourceFeatureName == 'nose_wing_movement') && ($sourceValue == 'up')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вверх';
        }
        if (($sourceFeatureName == 'nose_wing_movement') && ($sourceValue == 'down')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вниз';
        }
        /* Носогубная складка */
        if ($sourceFeatureName == 'left_nasolabial_fold_movement')
            $targetValues['targetFacePart'] = 'Левая носогубная складка';
        if ($sourceFeatureName == 'right_nasolabial_fold_movement')
            $targetValues['targetFacePart'] = 'Правая носогубная складка';
        if ((($sourceFeatureName == 'left_nasolabial_fold_movement') ||
                ($sourceFeatureName == 'right_nasolabial_fold_movement')) &&
            ($sourceValue != 'from center')) {
            $targetValues['featureChangeType'] = 'Отсутствие типа';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if ((($sourceFeatureName == 'left_nasolabial_fold_movement') ||
                ($sourceFeatureName == 'right_nasolabial_fold_movement')) &&
            ($sourceValue == 'from center')) {
            $targetValues['featureChangeType'] = 'Изменение положения по горизонтали';
            $targetValues['changeDirection'] = 'От центра в стороны';
        }

        return $targetValues;
    }

    /**
     * Преобразование массива с результатами определения признаков в массив фактов.
     *
     * @param $detectedFeatures - массив обнаруженных признаков
     * @return array - массив наборов фактов для кадого кадра видеоинтервью
     */
    public function convertFeaturesToFacts($detectedFeatures)
    {
        // Массив для наборов фактов, сформированных для каждого кадра
        $facts = array();
        // Кол-во кадров
        $numberFrames = 0;
        if (isset($detectedFeatures['eye']['left_eye_upper_eyelid_movement']) &&
            is_array($detectedFeatures['eye']['left_eye_upper_eyelid_movement']))
            $numberFrames = count($detectedFeatures['eye']['left_eye_upper_eyelid_movement']);
        // Цикл от 1 до общего-кол-ва кадров
        for ($i = 1; $i < $numberFrames; $i++) {
            // Массив фактов для текущего кадра
            $frameFacts = array();
            // Обход всех определенных лицевых признаков
            foreach ($detectedFeatures as $facePart => $features)
                if ($features != null)
                    foreach ($features as $featureName => $frames)
                        if (is_array($frames))
                            for ($j = 1; $j < count($frames); $j++)
                                if (isset($frames[$j]["val"]) && isset($frames[$j]["force"]))
                                    if ($i == $j) {
                                        // Поиск соответствий
                                        $targetValues = self::findCorrespondences($facePart, $featureName,
                                            $frames[$j]["val"]);
                                        // Если соответсвия найдены
                                        if ($targetValues['targetFacePart'] != null &&
                                            $targetValues['featureChangeType'] != null &&
                                            $targetValues['changeDirection'] != null) {
                                            // Формирование факта одного признака для текущего кадра
                                            $fact['NameOfTemplate'] = 'T1986';
                                            $fact['s861'] = $targetValues['targetFacePart'];
                                            $fact['s862'] = $targetValues['featureChangeType'];
                                            $fact['s863'] = $targetValues['changeDirection'];
                                            $fact['s864'] = $frames[$j]["force"];
                                            $fact['s869'] = $j;
                                            $fact['s870'] = $j;
                                            $fact['s871'] = count($frames);
                                            $fact['s874'] = $j;
                                            // Добавление факта одного признака для текущего кадра в набор фактов
                                            array_push($frameFacts, $fact);
                                        }
                                    }
            // Добавление набора фактов для текущего кадра в общий массив
            array_push($facts, $frameFacts);
        }

        return $facts;
    }

    /**
     * Преобразование массива с action units в массив фактов.
     *
     * @param stdClass $actionUnits - массив AUs (action units)
     * @param $frameIndex - номер кадра
     * @return array - массив факта
     */
    public function convertActionUnitsToFacts(stdClass $actionUnits, $frameIndex) {
        $replacementTable = array_combine(json_decode('["AU00","AU01","AU02","AU04","AU05","AU06","AU07","AU08","AU09","AU10","AU11","AU12","AU13","AU14","AU15","AU16","AU17","AU18","AU19","AU20","AU21","AU22","AU23","AU24","AU25","AU26","AU27","AU28","AU29","AU30","AU31","AU32","AU33","AU34","AU35","AU36","AU37","AU38","AU39","AU41","AU42","AU43","AU44","AU45","AU46","AU51","AU52","AU53","AU54","AU55","AU","AU56","AU","AU57","AU","AU58","AU","AU","AU","AU61","AU","AU62","AU","AU63","AU64","AU65","AU66","AU","AU69","AU","AU70","AU71","AU72","AU73","AU74","AU40","AU50","AU80","AU81","AU82","AU84","AU85"]'),
            json_decode('["AU0 - Нейтральное лицо","AU1 - Подниматель внутренней части брови","AU2 - Подниматель внешней части брови","AU4 - Опускатель брови","AU5 - Подниматель верхнего века","AU6 - Подниматель щеки","AU7 - Натягиватель века","AU8 - Губы навстречу друг другу","AU9 - Сморщиватель носа","AU10 - Подниматель верхней губы","AU11 - Углубитель носогубной складки","AU12 - Подниматель уголка губы","AU13 - Острый подниматель уголка губы","AU14 - Ямочка","AU15 - Опускатель уголка губы","AU16 - Опускатель нижней губы","AU17 - Подниматель подбородка","AU18 - Сморщиватель губ","AU19 - Показ языка","AU20 - Растягиватель губ","AU21 - Натягиватель шеи","AU22 - Губы воронкой","AU23 - Натягиватель губ","AU24 - Сжиматель губ","AU25 - Губы разведены","AU26 - Челюсть опущена","AU27 - Рот широко открыт","AU28 - Втягивание губ","AU29 - Нижняя челюсть вперёд","AU30 - Челюсть в бок","AU31 - Сжиматель челюстей","AU32 - Покусывание губы","AU33 - Выдувание","AU34 - Раздувание щёк","AU35 - Втягивание щёк","AU36 - Язык высунут","AU37 - Облизывание губ","AU38 - Расширитель ноздрей","AU39 - Суживатель ноздрей","AU41 - Опускатель надпереносья","AU42 - Опускатель внутренней части брови","AU43 - Глаза закрыты","AU44 - Сведение бровей","AU45 - Моргание","AU46 - Подмигивание","AU51 - Поворот головы влево","AU52 - Поворот головы вправо","AU53 - Голова вверх","AU54 - Голова вниз","AU55 - Наклон головы влево","AU M55 - Наклон головы влево","AU56 - Наклон головы вправо","AU M56 - Наклон головы вправо","AU57 - Голова вперёд","AU M57 - Толчок головы вперёд","AU58 - Голова назад","AU M59 - Кивок головой","AU M60 - Голова из стороны в сторону","AU M83 - Голова вверх и в сторону","AU61 - Отведение глаз влево","AU M61 - Глаза влево","AU62 - Отведение глаз вправо","AU M62 - Глаза вправо","AU63 - Глаза вверх","AU64 - Глаза вниз","AU65 - Расходящееся косоглазие","AU66 - Сходящееся косоглазие","AU M68 - Закатывание глаз","AU69 - Глаза на другом человеке","AU M69 - Голова и/или глаза на другом человеке","AU70 - Брови и лоб не видны","AU71 - Глаза не видны","AU72 - Нижняя часть лица не видна","AU73 - Всё лицо не видно","AU74 - Оценивание невозможно","AU40 - Втягивание носом","AU50 - Речь","AU80 - Глотание","AU81 - Жевание","AU82 - Пожатие плечом","AU84 - Движение головой назад и вперёд","AU85 - Кивок головой вверх и вниз"]'));
        $result = array();
        foreach ($actionUnits as $name => $actionUnit) {
            if ($actionUnit -> presence === 1) {
                $fact = new stdClass;
                // Имя шаблона: Признаки эмоций (Action units)
                $fact -> {'NameOfTemplate'} = 'T2045';
                // Имя слота: "Название" Описание слота: "Название action unit'а"
                $fact -> {'s900'} = $replacementTable[$name];
                // [Нет данных - пропускаем] Имя слота: "Проявление" Описание слота: "Описание проявления action unit'а:
                // левая часть лица, правая или обе стороны"
                // $fact -> {'s901'} = Null;
                // [+ Преобразование в %] Имя слота: "Интенсивность" Описание слота: ""
                $fact -> {'s902'} = $actionUnit -> intensity * 100;
                // Имя слота: "Номер кадра" Описание слота: ""
                $fact -> {'s903'} = $frameIndex;
                $result[] = $fact;
            };
        }

        return $result;
    }
}