<?php

namespace app\components;

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
//        $af = $val1 / (10);
        if($val1 != 0) $res = abs(round((100*$val2 / $val1)));
         else $res = 0;

        return $res;
    }

    /**
     * Вычисляет новую характеристику с именем $newFacialCharacteristicsName в массиве $facialCharacteristics
     * с учетом разбиений на фреймы.
     *
     * @param $facialCharacteristics - массив харрактеристик лица
     * @param $newFacialCharacteristicsName - название новой харрактеристики лица
     * @param $facialCharacteristics1 - первый массив с характеристиками лицевой точки
     * @param $key1 - координата точки из первого массива
     * @param $facialCharacteristics2 - второй массив с характеристиками лицевой точки
     * @param $key2 - координата точки из второго массива
     * @return bool - возвращаемое значение
     */
    public function addOneDimDistance($facialCharacteristics, $newFacialCharacteristicsName,
                                      $facialCharacteristics1, $key1, $facialCharacteristics2, $key2)
    {
        $facialCharacteristics1Number = count($facialCharacteristics1);
        if ($facialCharacteristics1Number <= 0)
            return false;

        $facialCharacteristics2Number = count($facialCharacteristics2);
        if ($facialCharacteristics2Number <= 0)
            return false;

        if ($facialCharacteristics1Number != $facialCharacteristics2Number)
            return false;

        for ($i = 0; $i < $facialCharacteristics1Number; $i++) {
 //           $facialCharacteristics[] = array();
            if ($facialCharacteristics1[$i] && $facialCharacteristics1[$i][$key1] &&
                $facialCharacteristics2[$i] && $facialCharacteristics2[$i][$key2])
                $facialCharacteristics[$i][$newFacialCharacteristicsName] = $facialCharacteristics1[$i][$key1] -
                    $facialCharacteristics2[$i][$key2];
        }

        return $facialCharacteristics;
    }

    /**
     * Обработка изменений значений характеристики лица. Увеличение: +, уменьшение: -.
     * После выполнения добавляет в массив значения с ключом WidthChange и WidthChangeForce для второй размерности
     * для каждого элемента $facialCharacteristics.
     *
     * @param $facialCharacteristics - массив с характеристикой лица
     * @param $key - название характеристики
     * @param $max - максимальное значение характеристики
     * @param $min - минимальное значение характеристики
     * @param $nat - нормальное значение характеристики
     * @return bool - возвращаемое значение
     */
    public function moveD($facialCharacteristics, $key, $max, $min, $nat)
    {
//        $targetFaceData = $facialCharacteristics;
        $facialCharacteristicsNumber = count($facialCharacteristics);

        if ($facialCharacteristicsNumber <= 0)
            return false;

        $deltaForMinus = $min - $nat;
        $deltaForPlus = $max - $nat;
//        $scale = $max - $min;

        for ($i = 0; $i < $facialCharacteristicsNumber; $i++) {
            if ($facialCharacteristics[$i] && $facialCharacteristics[$i][$key]) {
                if ($facialCharacteristics[$i][$key] < $nat) {
                    // Уменьшение ширины
                    $targetFaceData[$i]["force"] = $this->getForce(
                        $deltaForMinus, abs($facialCharacteristics[$i][$key]- $nat) );
                    $targetFaceData[$i]["widthChange"] = "-";

                    $facialCharacteristics[$i]["widthChange"] = "-";
                    $facialCharacteristics[$i]["widthChangeForce"] = round(
                        (($facialCharacteristics[$i][$key] - $nat) / $deltaForMinus),
                        2
                    );

                } elseif ($facialCharacteristics[$i][$key] > $nat) {
                    // Увеличение ширины
                    $facialCharacteristics[$i]["widthChange"] = "+";
                    $targetFaceData[$i]["force"] = $this->getForce(
                        $deltaForPlus, abs($facialCharacteristics[$i][$key]- $nat));
                    $targetFaceData[$i]["widthChange"] = "+";


                    $facialCharacteristics[$i]["widthChangeForce"] = round(
                        (($facialCharacteristics[$i][$key] - $nat) / $deltaForPlus),
                        2
                    );
                } else {
                    // Ввести погрешность для определения отсутсвтия движения
                    $facialCharacteristics[$i]["widthChange"] = "X";
                    $facialCharacteristics[$i]["widthChangeForce"] = 0;
                    $targetFaceData[$i]["force"] = 0;
                    $targetFaceData[$i]["widthChange"] = "none";
                }
            }
        }
  //print_r($targetFaceData);
        return $targetFaceData;
    }

    /**
     * Обработка вертикальных движений для указанной точки. Движение: N - вверх, S - вниз.
     * После выполнения добавляет в массив значения с ключом MovementDirection и MovementForce для
     * второй размерности в каждой точки.
     *
     * @param $facialLandmarkCharacteristics - массив с характеристиками лицевой точки
     * @param $characteristics - максимальное, минимальное и нормальное положение по Y
     * @return bool - возвращаемое значение
     */
    /*
    public function moveY($facialLandmarkCharacteristics, $characteristics)
    {
        $facialLandmarkCharacteristicsNumber = count($facialLandmarkCharacteristics);
        if ($facialLandmarkCharacteristicsNumber <= 0)
            return false;

        $deltaForN = $characteristics["MinY"] - $characteristics["NatY"];
        $deltaForS = $characteristics["MaxY"] - $characteristics["NatY"];
 //       $scale = $characteristics["MaxY"] - $characteristics["MinY"];

        for ($i = 0; $i < $facialLandmarkCharacteristicsNumber; $i++) {
            if ($facialLandmarkCharacteristics[$i] && $facialLandmarkCharacteristics[$i]["Y"]) {
                if ($facialLandmarkCharacteristics[$i]["Y"] < $characteristics["NatY"]) {
                    $targetFaceData[$i]["force"] = $this->getForce(
                        $deltaForN, abs($facialLandmarkCharacteristics[$i]["Y"]
                        - $characteristics["NatY"]) );
                    $targetFaceData[$i]["movementDirection"] = "N";

                    $facialLandmarkCharacteristics[$i]["MovementDirection"] = "N";
                    $facialLandmarkCharacteristics[$i]["MovementForce"] = round(
                        (($facialLandmarkCharacteristics[$i]["Y"] - $characteristics["NatY"]) / $deltaForN),
                        2
                    );
                } elseif ($facialLandmarkCharacteristics[$i]["Y"] > $characteristics["NatY"]) {
                    $targetFaceData[$i]["force"] = $this->getForce(
                        $deltaForS, abs($facialLandmarkCharacteristics[$i]["Y"]
                        - $characteristics["NatY"]) );
                    $targetFaceData[$i]["movementDirection"] = "S";


                    $facialLandmarkCharacteristics[$i]["MovementDirection"] = "S";
                    $facialLandmarkCharacteristics[$i]["MovementForce"] = round(
                        (($facialLandmarkCharacteristics[$i]["Y"] - $characteristics["NatY"]) / $deltaForS),
                        2
                    );
                } else {
                    // Ввести погрешность для определения отсутсвтия движения
                    $facialLandmarkCharacteristics[$i]["MovementDirection"] = "X";
                    $facialLandmarkCharacteristics[$i]["MovementForce"] = 0;
                    $targetFaceData[$i]["force"] = 0;
                    $targetFaceData[$i]["movementDirection"] = "none";

                }
            }
        }

        return $targetFaceData;
    }*/

    /**
     * Вычисление среднего значения характеристики лица за время наблюдений.
     *
     * @param $facialCharacteristics - массив с характеристикой лица
     * @param $key - название характеристики
     * @return float|bool - возвращаемое значение
     */
    public function getFaceDataAvrForKey($facialCharacteristics, $key)
    {
        $facialCharacteristicsNumber = count($facialCharacteristics);
        if ($facialCharacteristicsNumber <= 0)
            return false;

        $avr = 0;
        for ($i = 0; $i < $facialCharacteristicsNumber; $i++)
            if ($facialCharacteristics[$i] && $facialCharacteristics[$i][$key])
                $avr += $facialCharacteristics[$i][$key];

        return round($avr / $facialCharacteristicsNumber, 0);
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
        $max = 0;
        if (isset($facialCharacteristics[0][$pointNum][$key]))
            $max = $facialCharacteristics[0][$pointNum][$key];
        $maxFrame = 0;
        for ($i = 0; $i < $facialCharacteristicsNumber; $i++)
            if (isset($facialCharacteristics[$i][$pointNum]) && isset($facialCharacteristics[$i][$pointNum][$key]))
                if ($facialCharacteristics[$i][$pointNum][$key] > $max) {
                    $max = $facialCharacteristics[$i][$pointNum][$key];
                    $maxFrame = $i;
                }

//        return array(0 => $max, 1 => $maxFrame);
        return $max;
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
        $min = 0;
        if (isset($facialCharacteristics[0][$pointNum][$key]))
            $min = $facialCharacteristics[0][$pointNum][$key];
        $minFrame = 0;

        for ($i = 0; $i < $facialCharacteristicsNumber; $i++)
            if (isset($facialCharacteristics[$i][$pointNum]) && isset($facialCharacteristics[$i][$pointNum][$key]))
                if ($facialCharacteristics[$i][$pointNum][$key] < $min) {
                    $min = $facialCharacteristics[$i][$pointNum][$key];
                    $minFrame = $i;
                }

//        return array(0 => $min, 1 => $minFrame);
        return $min;
    }

    public function getFaceDataMaxForKeyV3($facialCharacteristics, $pointNum, $key)
    {
        $facialCharacteristicsNumber = count($facialCharacteristics);
        if ($facialCharacteristicsNumber <= 0)
            return false;

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
     * Обнаружение признаков глаза.
     *
     * @param $faceData - входной массив с лицевыми точками (landmarks)
     * @return mixed - выходной массив с обработанным массивом для глаза
     */
    public function detectEyeFeatures($sourceFaceData)
    {
       //Анализируемые точки: левый глаз – 36-41
       // (верхнее веко – 36-37-38-39, нижнее веко – 39-40-41-36, левый зрачок - ???),
       // правый глаз – 42-47 (верхнее веко – 42-43-44-45, нижнее веко – 45-46-47-42, правый зрачок - ???).
        //ширина глаза. для левого расстояние между точками 37 и 41, для правого- 43 и 47

        //Верхнее веко, движение верхнего века (вверх, вниз)
        //для левого глаза 37 38
        //для правого глаза 43 44

        if (isset($sourceFaceData['normmask'][0][37])
            && isset($sourceFaceData['normmask'][0][41])
            && isset($sourceFaceData['normmask'][0][43])
            && isset($sourceFaceData['normmask'][0][47])
            && isset($sourceFaceData['normmask'][0][39])
            && isset($sourceFaceData['normmask'][0][42])
            && isset($sourceFaceData['normmask'][0][36])
            && isset($sourceFaceData['normmask'][0][45])
        ) {
            $yN37 = $sourceFaceData['normmask'][0][37]['Y'];
            $yN41 = $sourceFaceData['normmask'][0][41]['Y'];
            $leftEyeWidthN = $yN41 - $yN37;
            $yN43 = $sourceFaceData['normmask'][0][43]['Y'];
            $yN47 = $sourceFaceData['normmask'][0][47]['Y'];
            $rightEyeWidthN = $yN47 - $yN43;
            //38 и 40 для левого глаза, для правого - 44 и 46
            $yN38 = $sourceFaceData['normmask'][0][38]['Y'];
            $yN40 = $sourceFaceData['normmask'][0][40]['Y'];
            $leftEyeWidthN2 = $yN40 - $yN38;
            $yN44 = $sourceFaceData['normmask'][0][44]['Y'];
            $yN46 = $sourceFaceData['normmask'][0][46]['Y'];
            $rightEyeWidthN2 = $yN46 - $yN44;

            $xN39 = $sourceFaceData['normmask'][0][39]['X'];
            $xN42 = $sourceFaceData['normmask'][0][42]['X'];
            $xN36 = $sourceFaceData['normmask'][0][36]['X'];
            $yN39 = $sourceFaceData['normmask'][0][39]['Y'];
            $yN42 = $sourceFaceData['normmask'][0][42]['Y'];
            $yN36 = $sourceFaceData['normmask'][0][36]['Y'];
            $yN45 = $sourceFaceData['normmask'][0][45]['Y'];
            $xN45 = $sourceFaceData['normmask'][0][45]['X'];

            $leftEyeWidthMaxByCircle = $xN39 - $xN36;
            $leftEyeWidthScaleByCircle = $leftEyeWidthMaxByCircle - $leftEyeWidthN;
            $rightEyeWidthMaxByCircle = $xN45 - $xN42;
            $rightEyeWidthScaleByCircle = $rightEyeWidthMaxByCircle - $rightEyeWidthN;

            $maxY37 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 37, "Y");
            $minY37 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'], 37, "Y");
            $scaleY37 = $maxY37 - $minY37;
            $maxY43 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 43, "Y");
            $minY43 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'], 43, "Y");
            $scaleY43 = $maxY43 - $minY43;
            $maxY41 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 41, "Y");
            $minY41 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'], 41, "Y");
            $scaleY41 = $maxY41 - $minY41;
            $maxLeftEyeWidth = $maxY41 - $minY37;
            $maxY47 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 47, "Y");
            $minY47 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'], 47, "Y");
            $scaleY47 = $maxY47 - $minY47;
            $maxRightEyeWidth = $maxY47 - $minY43;
            //38 и 40 для левого глаза, для правого - 44 и 46
            $maxY38 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 38, "Y");
            $minY38 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'], 38, "Y");
            $scaleY38 = $maxY38 - $minY38;
            $maxY44 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 44, "Y");
            $minY44 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'], 44, "Y");
            $scaleY44 = $maxY44 - $minY44;
            $maxY40 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 40, "Y");
            $minY40 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'], 40, "Y");
            $scaleY40 = $maxY40 - $minY40;
            $maxLeftEyeWidth2 = $maxY40 - $minY38;
            $maxY46 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 46, "Y");
            $minY46 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'], 46, "Y");
            $scaleY46 = $maxY46 - $minY46;
            $maxRightEyeWidth2 = $maxY46 - $minY44;

            $maxX39 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 39, "X");
            $minX39 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'], 39, "X");
            $scaleX39 = $maxX39 - $minX39;
            $maxX42 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 42, "X");
            $minX42 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'], 42, "X");
            $scaleX42 = $maxX42 - $minX42;
            $maxY39 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 39, "Y");
            $minY39 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'], 39, "Y");
            $scaleY39 = $maxY39 - $minY39;
            $maxY42 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 42, "Y");
            $minY42 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'], 42, "Y");
            $scaleY42 = $maxY42 - $minY42;
            $maxY36 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 36, "Y");
            $minY36 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'], 36, "Y");
            $scaleY36 = $maxY36 - $minY36;
            $maxY45 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 45, "Y");
            $minY45 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'], 45, "Y");
            $scaleY45 = $maxY45 - $minY45;

            for ($i = 0; $i < count($sourceFaceData['normmask']); $i++) {
                //----------------------------------------------------------------------------------------
                //Верхнее веко, движение верхнего века (вверх, вниз)
                //left_eye_upper_eyelid_movement
                if (isset($sourceFaceData['normmask'][$i][37]))
                    $leftEyeUpperEyelidH = $sourceFaceData['normmask'][$i][37]['Y'] - $yN37;
                if (isset($sourceFaceData['normmask'][$i][43]))
                    $rightEyeUpperEyelidH = $sourceFaceData['normmask'][$i][43]['Y'] - $yN43;

                $targetFaceData["eye"]["left_eye_upper_eyelid_movement"][$i]["force"] = $this->getForce(
                    $scaleY37, abs($leftEyeUpperEyelidH));
                $targetFaceData["eye"]["right_eye_upper_eyelid_movement"][$i]["force"] = $this->getForce(
                    $scaleY43, abs($rightEyeUpperEyelidH));

                if ($leftEyeUpperEyelidH < 0) $targetFaceData["eye"]["left_eye_upper_eyelid_movement"][$i]["val"] = 'up';
                if ($leftEyeUpperEyelidH > 0) $targetFaceData["eye"]["left_eye_upper_eyelid_movement"][$i]["val"] = 'down';
                if ($leftEyeUpperEyelidH == 0) {
                    $targetFaceData["eye"]["left_eye_upper_eyelid_movement"][$i]["force"] = 0;
                    $targetFaceData["eye"]["left_eye_upper_eyelid_movement"][$i]["val"] = 'none';
                }
                if ($rightEyeUpperEyelidH < 0) $targetFaceData["eye"]["right_eye_upper_eyelid_movement"][$i]["val"] = 'up';
                if ($rightEyeUpperEyelidH > 0) $targetFaceData["eye"]["right_eye_upper_eyelid_movement"][$i]["val"] = 'down';
                if ($rightEyeUpperEyelidH == 0) {
                    $targetFaceData["eye"]["right_eye_upper_eyelid_movement"][$i]["force"] = 0;
                    $targetFaceData["eye"]["right_eye_upper_eyelid_movement"][$i]["val"] = 'none';
                }
                //------------------------------------------------------------------------------------------------
                //Нижнее веко, движение нижнего века (без движения, вверх, вниз, к центру и вверх)
                //left_eye_lower_eyelid_movement
                if (isset($sourceFaceData['normmask'][$i][41]))
                    $leftEyeLowerEyelidH = $sourceFaceData['normmask'][$i][41]['Y'] - $yN41;
                if (isset($sourceFaceData['normmask'][$i][47]))
                    $rightEyeLowerEyelidH = $sourceFaceData['normmask'][$i][47]['Y'] - $yN47;
                if (isset($sourceFaceData['normmask'][$i][39]))
                    $leftEyeInnerCorner = $sourceFaceData['normmask'][$i][39]['X'] - $xN39;
                if (isset($sourceFaceData['normmask'][$i][42]))
                    $rightEyeInnerCorner = $sourceFaceData['normmask'][$i][42]['X'] - $xN42;
                $leftEyeInnerCornerForce = $this->getForce($scaleX39, abs($leftEyeInnerCorner));
                $rightEyeInnerCornerForce = $this->getForce($scaleX42, abs($rightEyeInnerCorner));

                $targetFaceData["eye"]["left_eye_lower_eyelid_movement_y"][$i]["force"] = $this->getForce(
                    $scaleY41, abs($leftEyeLowerEyelidH));
                $targetFaceData["eye"]["right_eye_lower_eyelid_movement_y"][$i]["force"] = $this->getForce(
                    $scaleY47, abs($rightEyeLowerEyelidH));

                if ($leftEyeLowerEyelidH < 0) $targetFaceData["eye"]["left_eye_lower_eyelid_movement_y"][$i]["val"] = 'up';
                if ($leftEyeLowerEyelidH > 0) $targetFaceData["eye"]["left_eye_lower_eyelid_movement_y"][$i]["val"] = 'down';
                if ($leftEyeLowerEyelidH == 0) {
                    $targetFaceData["eye"]["left_eye_lower_eyelid_movement_y"][$i]["force"] = 0;
                    $targetFaceData["eye"]["left_eye_lower_eyelid_movement_y"][$i]["val"] = 'none';
                }
                $targetFaceData["eye"]["left_eye_lower_eyelid_movement_x"][$i]["force"] = $leftEyeInnerCornerForce;
                if ($leftEyeInnerCorner > 0) $targetFaceData["eye"]["left_eye_lower_eyelid_movement_x"][$i]["val"] = 'to center';
                if ($leftEyeInnerCorner < 0) $targetFaceData["eye"]["left_eye_lower_eyelid_movement_x"][$i]["val"] = 'from center';
                if ($leftEyeInnerCorner == 0) $targetFaceData["eye"]["left_eye_lower_eyelid_movement_x"][$i]["val"] = 'none';

                if ($rightEyeLowerEyelidH < 0) $targetFaceData["eye"]["right_eye_lower_eyelid_movement_y"][$i]["val"] = 'up';
                if ($rightEyeLowerEyelidH > 0) $targetFaceData["eye"]["right_eye_lower_eyelid_movement_y"][$i]["val"] = 'down';
                if ($rightEyeLowerEyelidH == 0) {
                    $targetFaceData["eye"]["right_eye_lower_eyelid_movement_y"][$i]["force"] = 0;
                    $targetFaceData["eye"]["right_eye_lower_eyelid_movement_y"][$i]["val"] = 'none';
                }
                $targetFaceData["eye"]["right_eye_lower_eyelid_movement_x"][$i]["force"] = $rightEyeInnerCornerForce;
                if ($rightEyeInnerCorner < 0) $targetFaceData["eye"]["right_eye_lower_eyelid_movement_x"][$i]["val"] = 'to center';
                if ($rightEyeInnerCorner > 0) $targetFaceData["eye"]["right_eye_lower_eyelid_movement_x"][$i]["val"] = 'from center';
                if ($rightEyeInnerCorner == 0) $targetFaceData["eye"]["right_eye_lower_eyelid_movement_x"][$i]["val"] = 'none';

                $targetFaceData["eye"]["right_eye_lower_eyelid_movement_d"][$i]["force"] =
                    round(($targetFaceData["eye"]["right_eye_lower_eyelid_movement_x"][$i]["force"]+
                        $targetFaceData["eye"]["right_eye_lower_eyelid_movement_y"][$i]["force"])/2,2);
                $targetFaceData["eye"]["right_eye_lower_eyelid_movement_d"][$i]["val"] =
                    $targetFaceData["eye"]["right_eye_lower_eyelid_movement_x"][$i]["val"].' and '.
                    $targetFaceData["eye"]["right_eye_lower_eyelid_movement_y"][$i]["val"];

                $targetFaceData["eye"]["left_eye_lower_eyelid_movement_d"][$i]["force"] =
                    round(($targetFaceData["eye"]["left_eye_lower_eyelid_movement_x"][$i]["force"]+
                            $targetFaceData["eye"]["left_eye_lower_eyelid_movement_y"][$i]["force"])/2,2);
                $targetFaceData["eye"]["left_eye_lower_eyelid_movement_d"][$i]["val"] =
                    $targetFaceData["eye"]["left_eye_lower_eyelid_movement_x"][$i]["val"].' and '.
                    $targetFaceData["eye"]["left_eye_lower_eyelid_movement_y"][$i]["val"];
                //------------------------------------------------------------------------------------------------
                //width, расстояние между 37 и 41 для левого глаза, для правого - 43 и 47
                if (isset($sourceFaceData['normmask'][$i][37]) &&
                    isset($sourceFaceData['normmask'][$i][41]) &&
                    isset($sourceFaceData['normmask'][$i][43]) &&
                    isset($sourceFaceData['normmask'][$i][47])) {
                    $leftEyeWidth = $sourceFaceData['normmask'][$i][41]['Y'] - $sourceFaceData['normmask'][$i][37]['Y'];
                    $rightEyeWidth = $sourceFaceData['normmask'][$i][47]['Y'] - $sourceFaceData['normmask'][$i][43]['Y'];

//                    $targetFaceData["eye"]["left_eye_width"][$i]["force"] = $this->getForce(
//                        $maxLeftEyeWidth, abs($leftEyeWidth - $leftEyeWidthN));
                    $targetFaceData["eye"]["left_eye_width"][$i]["force"] = $this->getForce(
                        $leftEyeWidthScaleByCircle, abs($leftEyeWidth - $leftEyeWidthN));
                    $targetFaceData["eye"]["left_eye_width"][$i]["val"] = $leftEyeWidth;

//                    $targetFaceData["eye"]["right_eye_width"][$i]["force"] = $this->getForce(
//                        $maxRightEyeWidth, abs($rightEyeWidth - $rightEyeWidthN));
                    $targetFaceData["eye"]["right_eye_width"][$i]["force"] = $this->getForce(
                        $rightEyeWidthScaleByCircle, abs($rightEyeWidth - $rightEyeWidthN));
                    $targetFaceData["eye"]["right_eye_width"][$i]["val"] = $rightEyeWidth;

                    //альтернативно: width, расстояние между 38 и 40 для левого глаза, для правого - 44 и 46
                    $leftEyeWidth2 = $sourceFaceData['normmask'][$i][40]['Y'] - $sourceFaceData['normmask'][$i][38]['Y'];
                    $rightEyeWidth2 = $sourceFaceData['normmask'][$i][46]['Y'] - $sourceFaceData['normmask'][$i][44]['Y'];
 //                   $targetFaceData["eye"]["left_eye_width2"][$i]["force"] = $this->getForce(
//                        $maxLeftEyeWidth2, abs($leftEyeWidth2 - $leftEyeWidthN2));
                    $targetFaceData["eye"]["left_eye_width2"][$i]["force"] = $this->getForce(
                        ($leftEyeWidthMaxByCircle - $leftEyeWidthN2), abs($leftEyeWidth2 - $leftEyeWidthN2));

                    $targetFaceData["eye"]["left_eye_width2"][$i]["val"] = $leftEyeWidth2;
//                    $targetFaceData["eye"]["right_eye_width2"][$i]["force"] = $this->getForce(
//                        $maxRightEyeWidth2, abs($rightEyeWidth2 - $rightEyeWidthN2));
                    $targetFaceData["eye"]["right_eye_width2"][$i]["force"] = $this->getForce(
                        ($rightEyeWidthMaxByCircle - $rightEyeWidthN2), abs($rightEyeWidth2 - $rightEyeWidthN2));
                    $targetFaceData["eye"]["right_eye_width2"][$i]["val"] = $rightEyeWidth2;

                    //Глаза, ширина глаз (увеличение, уменьшение) через изменение ширины
                    $targetFaceData["eye"]["left_eye_width_changing"][$i]["force"] =
                        $targetFaceData["eye"]["left_eye_width"][$i]["force"];
                    $targetFaceData["eye"]["left_eye_width_changing"][$i]["val"] = 'none';
                    if ($targetFaceData["eye"]["left_eye_width"][$i]["val"] > $leftEyeWidthN)
                        $targetFaceData["eye"]["left_eye_width_changing"][$i]["val"] = '+';
                    if ($targetFaceData["eye"]["left_eye_width"][$i]["val"] < $leftEyeWidthN)
                        $targetFaceData["eye"]["left_eye_width_changing"][$i]["val"] = '-';

                    $targetFaceData["eye"]["right_eye_width_changing"][$i]["force"] =
                        $targetFaceData["eye"]["right_eye_width"][$i]["force"];
                    $targetFaceData["eye"]["right_eye_width_changing"][$i]["val"] = 'none';
                    if ($targetFaceData["eye"]["right_eye_width"][$i]["val"] > $rightEyeWidthN)
                        $targetFaceData["eye"]["right_eye_width_changing"][$i]["val"] = '+';
                    if ($targetFaceData["eye"]["right_eye_width"][$i]["val"] < $rightEyeWidthN)
                        $targetFaceData["eye"]["right_eye_width_changing"][$i]["val"] = '-';
                }
                //------------------------------------------------------------------------------------------------
                //Внешний уголок глаза, движение внешнего уголка глаза (вверх, вниз)
                if (isset($sourceFaceData['normmask'][$i][36]))
                    $leftEyeOuterCornerH = $sourceFaceData['normmask'][$i][36]['Y'] - $yN36;
                if (isset($sourceFaceData['normmask'][$i][45]))
                    $rightEyeOuterCornerH = $sourceFaceData['normmask'][$i][45]['Y'] - $yN45;
                $targetFaceData["eye"]["left_eye_outer_movement"][$i]["force"] = $this->getForce(
                    $scaleY36, abs($leftEyeOuterCornerH));
                $targetFaceData["eye"]["right_eye_outer_movement"][$i]["force"] = $this->getForce(
                    $scaleY45, abs($rightEyeOuterCornerH));
                if ($leftEyeOuterCornerH < 0) $targetFaceData["eye"]["left_eye_outer_movement"][$i]["val"] = 'up';
                if ($leftEyeOuterCornerH > 0) $targetFaceData["eye"]["left_eye_outer_movement"][$i]["val"] = 'down';
                if ($leftEyeOuterCornerH == 0) {
                    $targetFaceData["eye"]["left_eye_outer_movement"][$i]["force"] = 0;
                    $targetFaceData["eye"]["left_eye_outer_movement"][$i]["val"] = 'none';
                }
                if ($rightEyeOuterCornerH < 0) $targetFaceData["eye"]["right_eye_outer_movement"][$i]["val"] = 'up';
                if ($rightEyeOuterCornerH > 0) $targetFaceData["eye"]["right_eye_outer_movement"][$i]["val"] = 'down';
                if ($rightEyeOuterCornerH == 0) {
                    $targetFaceData["eye"]["right_eye_outer_movement"][$i]["force"] = 0;
                    $targetFaceData["eye"]["right_eye_outer_movement"][$i]["val"] = 'none';
                }
                //------------------------------------------------------------------------------------------------
                //Внутренний уголок глаза, движение внутреннего уголка глаза (вверх, вниз)
                if (isset($sourceFaceData['normmask'][$i][39]))
                    $leftEyeInnerCornerH = $sourceFaceData['normmask'][$i][39]['Y'] - $yN39;
                if (isset($sourceFaceData['normmask'][$i][42]))
                    $rightEyeInnerCornerH = $sourceFaceData['normmask'][$i][42]['Y'] - $yN42;
                $targetFaceData["eye"]["left_eye_inner_movement"][$i]["force"] = $this->getForce(
                    $scaleY39, abs($leftEyeInnerCornerH));
                $targetFaceData["eye"]["right_eye_inner_movement"][$i]["force"] = $this->getForce(
                    $scaleY42, abs($rightEyeInnerCornerH));
                if ($leftEyeInnerCornerH < 0) $targetFaceData["eye"]["left_eye_inner_movement"][$i]["val"] = 'up';
                if ($leftEyeInnerCornerH > 0) $targetFaceData["eye"]["left_eye_inner_movement"][$i]["val"] = 'down';
                if ($leftEyeInnerCornerH == 0) {
                    $targetFaceData["eye"]["left_eye_inner_movement"][$i]["force"] = 0;
                    $targetFaceData["eye"]["left_eye_inner_movement"][$i]["val"] = 'none';
                }
                if ($rightEyeInnerCornerH < 0) $targetFaceData["eye"]["right_eye_inner_movement"][$i]["val"] = 'up';
                if ($rightEyeInnerCornerH > 0) $targetFaceData["eye"]["right_eye_inner_movement"][$i]["val"] = 'down';
                if ($rightEyeInnerCornerH == 0) {
                    $targetFaceData["eye"]["right_eye_inner_movement"][$i]["force"] = 0;
                    $targetFaceData["eye"]["right_eye_inner_movement"][$i]["val"] = 'none';
                }
                //------------------------------------------------------------------------------------------------
            }
            return $targetFaceData["eye"];
        } else return false;
    }

    /**
     * конвертация входного файла И в массив АБ
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
//                  for ($i1 = 0; $i1 < count($v['NORM_POINTS']); $i1++) {
//                      $pointName = $k1;
//                      echo $pointName.'<br>';
                            $FaceData_['normmask'][$i][$k1]['X'] = $v1[0];
                            $FaceData_['normmask'][$i][$k1]['Y'] = $v1[1];
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
     * Обнаружение признаков носа
     *
     * @param $sourceFaceData - входной массив с лицевыми точками (landmarks)
     * @return array - выходной массив с обработанным массивом для лба
     */
    public function detectNoseFeatures($sourceFaceData)
    {
        //анализируемые точки низа носа
        // 31 (left_nose_wing),
        // 35 (right_nose_wing),
        // получение нормированного значения по кадру 0

        if (isset($sourceFaceData['normmask'][0][31])
            && isset($sourceFaceData['normmask'][0][35])
        ) {
            $yN31 = $sourceFaceData['normmask'][0][31]['Y'];
            $yN35 = $sourceFaceData['normmask'][0][35]['Y'];
            $maxY31 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 31, "Y");
            $minY31 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'], 31, "Y");
            $scaleY31 = $maxY31 - $minY31;
            $maxY35 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 35, "Y");
            $minY35 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'], 35, "Y");
            $scaleY35 = $maxY35 - $minY35;


            for ($i = 0; $i < count($sourceFaceData['normmask']); $i++) {
                if (isset($sourceFaceData['normmask'][$i][31]) && $sourceFaceData['normmask'][$i][35]) {
                    $leftNoseWingMovement = $sourceFaceData['normmask'][$i][31]['Y'] - $yN31;
                    $rightNoseWingMovement = $sourceFaceData['normmask'][$i][35]['Y'] - $yN35;

                    $leftNoseWingMovementForce = $this->getForce($scaleY31, abs($leftNoseWingMovement));
                    $rightNoseWingMovementForce = $this->getForce($scaleY35, abs($rightNoseWingMovement));
                    $noseWingsMovementForce = round(($leftNoseWingMovementForce + $rightNoseWingMovementForce) / 2); //среднее значение
                }
                $targetFaceData["nose"]["nose_wing_movement"][$i]["force"] = $noseWingsMovementForce;
                if (($leftNoseWingMovement < 0) || ($rightNoseWingMovement < 0)) $targetFaceData["nose"]["nose_wing_movement"][$i]["val"] = 'up';
                if (($leftNoseWingMovement > 0) || ($rightNoseWingMovement > 0)) $targetFaceData["nose"]["nose_wing_movement"][$i]["val"] = 'down';
                if (($leftNoseWingMovement == 0) && ($rightNoseWingMovement == 0)) {
                    $targetFaceData["nose"]["nose_wing_movement"][$i]["force"] = 0;
                    $targetFaceData["nose"]["nose_wing_movement"][$i]["val"] = 'none';
                }
            }

//        echo json_encode($sourceFaceData['letf_nasolabial_fold'][0][0]);
//        echo '<br><br>';
//        json_encode($sourceFaceData['letf_nasolabial_fold'][0][0]);
//        json_encode($sourceFaceData['right_nasolabial_fold'][0][0]);
            //анализ носогубных складок на основе треугольников
 /*           $normFrameIndex = -1;
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
            return $targetFaceData["nose"];
        }else return false;
    }

    /**
     * Обнаружение признаков подбородка.
     *
     * @param $sourceFaceData - входной массив с лицевыми точками (landmarks)
     * @return array - выходной массив с обработанным массивом для лба
     */
    public function detectChinFeatures($sourceFaceData){
        //анализируемые точки:
        // 8 (нижняя центральная точка подбородка),

        if (isset($sourceFaceData['normmask'][0][8])
        ) {
            $yN8 = $sourceFaceData['normmask'][0][8]['Y'];
            $maxY8 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 8,"Y");
            $minY8 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'],8, "Y");
            $scaleY8 = $maxY8 - $minY8;

            for ($i = 0; $i < count($sourceFaceData['normmask']); $i++) {
                if (isset($sourceFaceData['normmask'][$i][8])){
                    $chinMovement = $sourceFaceData['normmask'][$i][8]['Y'] - $yN8;
                    $chinMovementForce = $this->getForce($scaleY8, abs($chinMovement));
                }
                $targetFaceData["chin"]["chin_movement"][$i]["force"] = $chinMovementForce;
                if ($chinMovement < 0) $targetFaceData["chin"]["chin_movement"][$i]["val"] = 'up';
                if ($chinMovement > 0) $targetFaceData["chin"]["chin_movement"][$i]["val"] = 'down';
                if ($chinMovement == 0) $targetFaceData["chin"]["chin_movement"][$i]["val"] = 'none';
            }
            return $targetFaceData["chin"];
        } else return false;

    }
    /**
     * Обнаружение признаков лба.
     *
     * @param $sourceFaceData - входной массив с лицевыми точками (landmarks)
     * @return array - выходной массив с обработанным массивом для лба
     */
    public function detectBrowFeatures($sourceFaceData){
        //анализируемые точки:
        // 19 (left_eyebrow_center),
        // 24 (right_eyebrow_center),
        //изменение ширины лба по движению бровей
        // получение нормированного значения по кадру 0

        if (isset($sourceFaceData['normmask'][0][19])
            && isset($sourceFaceData['normmask'][0][24])
        ) {
            $yN19 = $sourceFaceData['normmask'][0][19]['Y'];
            $yN24 = $sourceFaceData['normmask'][0][24]['Y'];
            $maxY19 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 19,"Y");
            $minY19 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'],19, "Y");
            $scaleY19 = $maxY19 - $minY19;
            $maxY24 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 24,"Y");
            $minY24 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'],24, "Y");
            $scaleY24 = $maxY24 - $minY24;


        for ($i = 0; $i < count($sourceFaceData['normmask']); $i++) {
            if (isset($sourceFaceData['normmask'][$i][19]) && $sourceFaceData['normmask'][$i][24]){
                $leftEyebrowMovement = $sourceFaceData['normmask'][$i][19]['Y'] - $yN19;
                $rightEyebrowMovement = $sourceFaceData['normmask'][$i][24]['Y'] - $yN24;

                $leftEyebrowMovementForce = $this->getForce($scaleY19, abs($leftEyebrowMovement));
                $rightEyebrowMovementForce = $this->getForce($scaleY24, abs($rightEyebrowMovement));
                $eyebrowMovementForce = round(($leftEyebrowMovementForce+$rightEyebrowMovementForce)/2); //среднее значение
            }
            $targetFaceData["brow"]["brow_width"][$i]["force"] = $eyebrowMovementForce;
            if (($leftEyebrowMovement < 0)||($rightEyebrowMovement < 0)) $targetFaceData["brow"]["brow_width"][$i]["val"] = '-';
            if (($leftEyebrowMovement > 0)||($rightEyebrowMovement > 0)) $targetFaceData["brow"]["brow_width"][$i]["val"] = '+';
            if (($leftEyebrowMovement == 0)&&($rightEyebrowMovement == 0)) {
                $targetFaceData["brow"]["brow_width"][$i]["force"] = 0;
                $targetFaceData["brow"]["brow_width"][$i]["val"] = 'none';
            }
        }
        return $targetFaceData["brow"];
        } else return false;

    }

    /**
     * Обнаружение признаков бровей.
     *
     * @param $sourceFaceData - входной массив с лицевыми точками (landmarks)
     * @return array - выходной массив с обработанным массивом для лба
     */
    public function detectEyeBrowFeatures($sourceFaceData){
        //-------------------------------------------------------------------------------------
        //Анализируемые точки бровей: левая – 17, 19, 21, правая – 22, 24, 26.
        //Брови, движение бровей (вверх, вниз, к центру, к центру и вверх)
        //--------------------------------------------------------------------------------------

        if (isset($sourceFaceData['normmask'][0][17])
            && isset($sourceFaceData['normmask'][0][19])
            && isset($sourceFaceData['normmask'][0][21])
            && isset($sourceFaceData['normmask'][0][22])
            && isset($sourceFaceData['normmask'][0][24])
            && isset($sourceFaceData['normmask'][0][20])
            && isset($sourceFaceData['normmask'][0][23])
            && isset($sourceFaceData['normmask'][0][26])
        ) {
            $yN17 = $sourceFaceData['normmask'][0][17]['Y'];
            $xN17 = $sourceFaceData['normmask'][0][17]['X'];
            $yN21 = $sourceFaceData['normmask'][0][21]['Y'];
            $xN21 = $sourceFaceData['normmask'][0][21]['X'];
            $yN22 = $sourceFaceData['normmask'][0][22]['Y'];
            $xN22 = $sourceFaceData['normmask'][0][22]['X'];
            $yN26 = $sourceFaceData['normmask'][0][26]['Y'];
            $xN26 = $sourceFaceData['normmask'][0][26]['X'];
            $yN19 = $sourceFaceData['normmask'][0][19]['Y'];
            $xN19 = $sourceFaceData['normmask'][0][19]['X'];
            $yN24 = $sourceFaceData['normmask'][0][24]['Y'];
            $xN24 = $sourceFaceData['normmask'][0][24]['X'];

            $maxY17 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 17, "Y");
            $minY17 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'], 17, "Y");
            $maxX17 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 17, "X");
            $minX17 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'], 17, "X");
            $scaleY17 = $maxY17 - $minY17;
            $scaleX17 = $maxX17 - $minX17;

            $maxY21 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 21, "Y");
            $minY21 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'], 21, "Y");
            $maxX21 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 21, "X");
            $minX21 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'], 21, "X");
            $scaleY21 = $maxY21 - $minY21;
            $scaleX21 = $maxX21 - $minX21;

            $maxY22 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 22, "Y");
            $minY22 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'], 22, "Y");
            $maxX22 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 22, "X");
            $minX22 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'], 22, "X");
            $scaleY22 = $maxY22 - $minY22;
            $scaleX22 = $maxX22 - $minX22;

            $maxY26 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 26, "Y");
            $minY26 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'], 26, "Y");
            $maxX26 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 26, "X");
            $minX26 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'], 26, "X");
            $scaleY26 = $maxY26 - $minY26;
            $scaleX26 = $maxX26 - $minX26;

            $maxY19 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 19, "Y");
            $minY19 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'], 19, "Y");
            $maxX19 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 19, "X");
            $minX19 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'], 19, "X");
            $scaleY19 = $maxY19 - $minY19;
            $scaleX19 = $maxX19 - $minX19;

            $maxY24 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 24, "Y");
            $minY24 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'], 24, "Y");
            $maxX24 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 24, "X");
            $minX24 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'], 24, "X");
            $scaleY24 = $maxY24 - $minY24;
            $scaleX24 = $maxX24 - $minX24;

            for ($i = 0; $i < count($sourceFaceData['normmask']); $i++) {
                //eyebrow_line
                //Линия брови – отрезок [внешний уголок брови-XY, внутренний уголок брови-XY]
                //Движение брови-H-OUT = внешний уголок брови -Y – внешний уголок брови-YN
                //Движение брови-H-IN = внутренний уголок брови-Y – внутренний уголок брови-YN
                if (isset($sourceFaceData['normmask'][$i][17]))
                    $leftEyebrowMovementHOut = $sourceFaceData['normmask'][$i][17]['Y'] - $yN17;
                if (isset($sourceFaceData['normmask'][$i][21])) {
                    $leftEyebrowMovementHIn = $sourceFaceData['normmask'][$i][21]['Y'] - $yN21;
                    $leftEyebrowMovementXIn = $sourceFaceData['normmask'][$i][21]['X'] - $xN21;
                }
                if (isset($sourceFaceData['normmask'][$i][22])) {
                    $rightEyebrowMovementHIn = $sourceFaceData['normmask'][$i][22]['Y'] - $yN22;
                    $rightEyebrowMovementXIn = $sourceFaceData['normmask'][$i][22]['X'] - $xN22;
                }
                if (isset($sourceFaceData['normmask'][$i][26]))
                    $rightEyebrowMovementHOut = $sourceFaceData['normmask'][$i][26]['Y'] - $yN26;

                $leftEyebrowXMovForce = $this->getForce($scaleX21, abs($leftEyebrowMovementXIn));
                $leftEyebrowYMovForce = $this->getForce($scaleY21, abs($leftEyebrowMovementHIn));
                $targetFaceData["eyebrow"]["left_eyebrow_inner_movement_x"][$i]["force"] =
                   $leftEyebrowXMovForce;
                $targetFaceData["eyebrow"]["left_eyebrow_inner_movement_y"][$i]["force"] =
                    $leftEyebrowYMovForce;

                $targetFaceData["eyebrow"]["left_eyebrow_outer_movement"][$i]["force"] = $this->getForce(
                    $scaleY17, abs($leftEyebrowMovementHOut));

                $rightEyebrowXMovForce = $this->getForce($scaleX22, abs($rightEyebrowMovementXIn));
                $rightEyebrowYMovForce = $this->getForce($scaleY22, abs($rightEyebrowMovementHIn));
                $targetFaceData["eyebrow"]["right_eyebrow_inner_movement_x"][$i]["force"] =
                    $rightEyebrowXMovForce;
                $targetFaceData["eyebrow"]["right_eyebrow_inner_movement_y"][$i]["force"] =
                    $rightEyebrowYMovForce;

                $targetFaceData["eyebrow"]["right_eyebrow_outer_movement"][$i]["force"] = $this->getForce(
                    $scaleY26, abs($rightEyebrowMovementHOut));

                $xMov = 'none';
                if ($leftEyebrowMovementHIn > 0) $yMov = 'down';
                if ($leftEyebrowMovementHIn < 0) $yMov = 'up';
                if ($leftEyebrowMovementHIn == 0) $yMov = 'none';
                if ($leftEyebrowMovementXIn > 0) $xMov = 'to center';
                if ($leftEyebrowMovementXIn < 0) $xMov = 'from center';
//                if ($yMov == 'none') $yMov = '';
//                if (($xMov != '') && ($yMov != '')) $yMov = ' and ' . $yMov;
//                if (($xMov == '') && ($yMov == '')) $yMov = 'none';

                $targetFaceData["eyebrow"]["left_eyebrow_inner_movement_x"][$i]["val"] = $xMov;
                $targetFaceData["eyebrow"]["left_eyebrow_inner_movement_y"][$i]["val"] = $yMov;

                if ($leftEyebrowMovementHOut > 0) $targetFaceData["eyebrow"]["left_eyebrow_outer_movement"][$i]["val"] = 'down';
                if ($leftEyebrowMovementHOut < 0) $targetFaceData["eyebrow"]["left_eyebrow_outer_movement"][$i]["val"] = 'up';
                if ($leftEyebrowMovementHOut == 0) {
                    $targetFaceData["eyebrow"]["left_eyebrow_outer_movement"][$i]["force"] = 0;
                    $targetFaceData["eyebrow"]["left_eyebrow_outer_movement"][$i]["val"] = 'none';
                }

                $xMov = 'none';
                if ($rightEyebrowMovementHIn > 0) $yMov = 'down';
                if ($rightEyebrowMovementHIn < 0) $yMov = 'up';
                if ($rightEyebrowMovementHIn == 0) $yMov = 'none';
                if ($rightEyebrowMovementXIn < 0) $xMov = 'to center';
                if ($rightEyebrowMovementXIn > 0) $xMov = 'from center';
 //               if ($yMov == 'none') $yMov = '';
//                if (($xMov != '') && ($yMov != '')) $yMov = ' and ' . $yMov;
//                if (($xMov == '') && ($yMov == '')) $yMov = 'none';
                $targetFaceData["eyebrow"]["right_eyebrow_inner_movement_y"][$i]["val"] = $yMov;
                $targetFaceData["eyebrow"]["right_eyebrow_inner_movement_x"][$i]["val"] = $xMov;

                if ($rightEyebrowMovementHOut > 0) $targetFaceData["eyebrow"]["right_eyebrow_outer_movement"][$i]["val"] = 'down';
                if ($rightEyebrowMovementHOut < 0) $targetFaceData["eyebrow"]["right_eyebrow_outer_movement"][$i]["val"] = 'up';
                if ($rightEyebrowMovementHOut == 0) {
                    $targetFaceData["eyebrow"]["right_eyebrow_outer_movement"][$i]["force"] = 0;
                    $targetFaceData["eyebrow"]["right_eyebrow_outer_movement"][$i]["val"] = 'none';
                }

                //определяем движение брови по движению верхних точек бровей
                // 19 - левая бровь, 24 - правая бровь
                $rightEyebrowMovementY = $sourceFaceData['normmask'][$i][24]['Y'] - $yN24;
                $rightEyebrowMovementX = $sourceFaceData['normmask'][$i][24]['X'] - $xN24;
                $leftEyebrowMovementY = $sourceFaceData['normmask'][$i][19]['Y'] - $yN19;
                $leftEyebrowMovementX = $sourceFaceData['normmask'][$i][19]['X'] - $xN19;
                $rightEyebrowXMovForce = $this->getForce($scaleX24, abs($rightEyebrowMovementX));
                $rightEyebrowYMovForce = $this->getForce($scaleY24, abs($rightEyebrowMovementY));
//                $targetFaceData["eyebrow"]["right_eyebrow_movement"][$i]["force"] =
//                    round(($rightEyebrowXMovForce + $rightEyebrowYMovForce) / 2);
                $targetFaceData["eyebrow"]["right_eyebrow_movement_x"][$i]["force"] =
                    $rightEyebrowXMovForce;
                $targetFaceData["eyebrow"]["right_eyebrow_movement_y"][$i]["force"] =
                    $rightEyebrowYMovForce;

                $leftEyebrowXMovForce = $this->getForce($scaleX19, abs($leftEyebrowMovementX));
                $leftEyebrowYMovForce = $this->getForce($scaleY19, abs($leftEyebrowMovementY));
//                $targetFaceData["eyebrow"]["left_eyebrow_movement"][$i]["force"] =
//                    round(($leftEyebrowXMovForce + $leftEyebrowYMovForce) / 2);
                $targetFaceData["eyebrow"]["left_eyebrow_movement_x"][$i]["force"] =
                    $leftEyebrowXMovForce;
                $targetFaceData["eyebrow"]["left_eyebrow_movement_y"][$i]["force"] =
                    $leftEyebrowYMovForce;

                $xMov = 'none';
                if ($leftEyebrowMovementY > 0) $yMov = 'down';
                if ($leftEyebrowMovementY < 0) $yMov = 'up';
                if ($leftEyebrowMovementY == 0) $yMov = 'none';
                if ($leftEyebrowMovementX > 0) $xMov = 'to center';
                if ($leftEyebrowMovementX < 0) $xMov = 'from center';
//                if ($yMov == 'none') $yMov = '';
//                if (($xMov != '') && ($yMov != '')) $yMov = ' and ' . $yMov;
//                if (($xMov == '') && ($yMov == '')) $yMov = 'none';
//                $targetFaceData["eyebrow"]["left_eyebrow_movement"][$i]["val"] = $xMov.$yMov;
                $targetFaceData["eyebrow"]["left_eyebrow_movement_x"][$i]["val"] = $xMov;
                $targetFaceData["eyebrow"]["left_eyebrow_movement_y"][$i]["val"] = $yMov;

                $xMov = 'none';
                if ($rightEyebrowMovementY > 0) $yMov = 'down';
                if ($rightEyebrowMovementY < 0) $yMov = 'up';
                if ($rightEyebrowMovementY == 0) $yMov = 'none';
                if ($rightEyebrowMovementX < 0) $xMov = 'to center';
 //               if ($yMov == 'none') $yMov = '';
//                if (($xMov != '') && ($yMov != '')) $yMov = ' and ' . $yMov;
//                if (($xMov == '') && ($yMov == '')) $yMov = 'none';
//                $targetFaceData["eyebrow"]["right_eyebrow_movement"][$i]["val"] = $xMov.$yMov;
                $targetFaceData["eyebrow"]["right_eyebrow_movement_x"][$i]["val"] = $xMov;
                $targetFaceData["eyebrow"]["right_eyebrow_movement_y"][$i]["val"] = $yMov;

                //If Движение брови-H-OUT > 0 AND Движение брови-H-IN > 0 → Направление движения брови (Линии брови) = Вверх
                //If Движение брови-H-OUT < 0 AND Движение брови-H-IN < 0 → Направление движения брови (Линии брови)  = Вниз
                //!!! только по внутреннему уголку
/*                $targetFaceData["eyebrow"]["left_eyebrow_movement"][$i]["force"] =
                    $targetFaceData["eyebrow"]["left_eyebrow_inner_movement"][$i]["force"];
//                    round((($targetFaceData["eyebrow"]["left_eyebrow_inner_movement"][$i]["force"] +
//                            $targetFaceData["eyebrow"]["left_eyebrow_outer_movement"][$i]["force"]) / 2), 2);
                $targetFaceData["eyebrow"]["right_eyebrow_movement"][$i]["force"] =
                    $targetFaceData["eyebrow"]["right_eyebrow_inner_movement"][$i]["force"];
//                    round((($targetFaceData["eyebrow"]["right_eyebrow_inner_movement"][$i]["force"] +
//                            $targetFaceData["eyebrow"]["right_eyebrow_outer_movement"][$i]["force"]) / 2), 2);

                $targetFaceData["eyebrow"]["left_eyebrow_movement"][$i]["val"] = 'none';
                if (//($leftEyebrowMovementHOut > 0) and
                    ($leftEyebrowMovementHIn > 0))
                    $targetFaceData["eyebrow"]["left_eyebrow_movement"][$i]["val"] = 'down';
                if (//($leftEyebrowMovementHOut < 0) and
                    ($leftEyebrowMovementHIn < 0))
                    $targetFaceData["eyebrow"]["left_eyebrow_movement"][$i]["val"] = 'up';
                $targetFaceData["eyebrow"]["right_eyebrow_movement"][$i]["val"] = 'none';
                if (//($rightEyebrowMovementHOut > 0) and
                    ($rightEyebrowMovementHIn > 0))
                    $targetFaceData["eyebrow"]["right_eyebrow_movement"][$i]["val"] = 'down';
                if (//($rightEyebrowMovementHOut < 0) and
                    ($rightEyebrowMovementHIn < 0))
                    $targetFaceData["eyebrow"]["right_eyebrow_movement"][$i]["val"] = 'up';
*/
                //If Направление движения брови (Линии брови) = Вверх AND Движение брови-H-OUT < Движение брови-H-IN
                //  → Направление движения внутреннего уголка брови = Вверх AND Направление движения внешнего уголка брови = Вниз
/*                if (($targetFaceData["eyebrow"]["left_eyebrow_movement"][$i]["val"] == 'up') and
                    ($leftEyebrowMovementHOut < $leftEyebrowMovementHIn)) {
                    //               $targetFaceData["eyebrow"]["left_eyebrow_inner_movement"][$i]["val"] = 'up';
                    $targetFaceData["eyebrow"]["left_eyebrow_outer_movement"][$i]["val"] = 'down';
                }
                if (($targetFaceData["eyebrow"]["right_eyebrow_movement"][$i]["val"] == 'up') and
                    ($rightEyebrowMovementHOut < $rightEyebrowMovementHIn)) {
//                $targetFaceData["eyebrow"]["right_eyebrow_inner_movement"][$i]["val"] = 'up';
                    $targetFaceData["eyebrow"]["right_eyebrow_outer_movement"][$i]["val"] = 'down';
                }*/

                //If |Движение брови-H-OUT| > |Движение брови-H-IN|  → Направление движения внешнего уголка брови = Вниз
            /*    if (abs($leftEyebrowMovementHOut) > abs($leftEyebrowMovementHIn)) {
                    $targetFaceData["eyebrow"]["left_eyebrow_outer_movement"][$i]["val"] = 'down';
                }
                if (abs($rightEyebrowMovementHOut) > abs($rightEyebrowMovementHIn)) {
                    $targetFaceData["eyebrow"]["right_eyebrow_outer_movement"][$i]["val"] = 'down';
                }*/

                //If Движение брови-L-IN > 0 → Направление движения брови (Линии брови) = К центру
                //Движение брови-L-IN = внутренний уголок брови-X – внутренний уголок брови-XN
/*                if (isset($sourceFaceData['normmask'][$i][21]))
                    $leftEyebrowMovementLIn = $sourceFaceData['normmask'][$i][21]['X'] - $xN21;
                if (isset($sourceFaceData['normmask'][$i][22]))
                    $rightEyebrowMovementLIn = $sourceFaceData['normmask'][$i][22]['X'] - $xN22;

                $xMov = '';
                $yMov = $targetFaceData["eyebrow"]["left_eyebrow_movement"][$i]["val"];
                if ($leftEyebrowMovementLIn > 0) $xMov = 'to center';
                if ($yMov == 'none') $yMov = '';
                if (($xMov != '') && ($yMov != '')) $yMov = ' and ' . $yMov;
                if (($xMov == '') && ($yMov == '')) $yMov = 'none';
                $targetFaceData["eyebrow"]["left_eyebrow_movement"][$i]["val"] = $xMov.$yMov;

                $xMov = '';
                $yMov = $targetFaceData["eyebrow"]["right_eyebrow_movement"][$i]["val"];
                if ($rightEyebrowMovementLIn < 0) $xMov = 'to center';
                if ($yMov == 'none') $yMov = '';
                if (($xMov != '') && ($yMov != '')) $yMov = ' and ' . $yMov;
                if (($xMov == '') && ($yMov == '')) $yMov = 'none';
                $targetFaceData["eyebrow"]["right_eyebrow_movement"][$i]["val"] = $xMov.$yMov;*/

            }

            return $targetFaceData["eyebrow"];
        }
        else return false;
    }

    /**
     * Обнаружение признаков рта.
     *
     * @param $sourceFaceData - входной массив с лицевыми точками (landmarks)
     * @return array - выходной массив с обработанным массивом для глаза
     */
    public function detectMouthFeatures($sourceFaceData)
    {
        // first frame for standard (norm values)

        if (isset($sourceFaceData['normmask'][0][48])
            && isset($sourceFaceData['normmask'][0][54])

            && isset($sourceFaceData['normmask'][0][43])
            && isset($sourceFaceData['normmask'][0][47])
            && isset($sourceFaceData['normmask'][0][39])
            && isset($sourceFaceData['normmask'][0][42])
            && isset($sourceFaceData['normmask'][0][36])
            && isset($sourceFaceData['normmask'][0][45])
        ) {
            $xN48 = $sourceFaceData['normmask'][0][48]['X'];
            $xN54 = $sourceFaceData['normmask'][0][54]['X'];
            $yN48 = $sourceFaceData['normmask'][0][48]['Y'];
            $yN54 = $sourceFaceData['normmask'][0][54]['Y'];
            $mouthLengthN = $xN54 - $xN48;

            $yN51 = $sourceFaceData['normmask'][0][51]['Y'];
            $yN57 = $sourceFaceData['normmask'][0][57]['Y'];
            $mouthWidthN = $yN57 - $yN51;


            $maxX48 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 48, "X");
            $minX48 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'], 48, "X");
            $scaleX48 = $maxX48 - $minX48;
            $maxY48 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 48, "Y");
            $minY48 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'], 48, "Y");
            $scaleY48 = $maxY48 - $minY48;
            $maxX54 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 54, "X");
            $minX54 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'], 54, "X");
            $scaleX54 = $maxX54 - $minX54;
            $maxY54 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 54, "Y");
            $minY54 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'], 54, "Y");
            $scaleY54 = $maxY54 - $minY54;
            $maxMouthLength = $maxX54 - $minX48;
            $minMouthLength = $minX54 - $maxX48;
            $scaleMouthLength = $maxMouthLength - $minMouthLength;

            $maxY51 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 51, "Y");
            $minY51 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'], 51, "Y");
            $scaleY51 = $maxY51 - $minY51;
            $maxY57 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 57, "Y");
            $minY57 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'], 57, "Y");
            $scaleY57 = $maxY57 - $minY57;
            $maxMouthWidth = $maxY57 - $minY51;
            $minMouthWidth = $minY57 - $maxY51;
            $scaleMouthWidth = $maxMouthWidth - $minMouthWidth;

            // изменение длины рта
            // NORM_POINTS 48 54
            // echo $FaceData_['normmask'][0][48][X];
            for ($i = 0; $i < count($sourceFaceData['normmask']); $i++) {
                if (isset($sourceFaceData['normmask'][$i][48])) {
                    $leftMouthCornerXMov = $sourceFaceData['normmask'][$i][48]['X'] - $xN48;
                    $leftMouthCornerYMov = $sourceFaceData['normmask'][$i][48]['Y'] - $yN48;

                    $leftMouthCornerXMovForce = $this->getForce($scaleX48, abs($leftMouthCornerXMov));
                    $leftMouthCornerYMovForce = $this->getForce($scaleY48, abs($leftMouthCornerYMov));
//                    $leftMouthCornerYMovAvForce = round(($leftMouthCornerXMovForce + $leftMouthCornerYMovForce) / 2);
//                    $targetFaceData["mouth"]["left_corner_mouth_movement"][$i]["force"] = $leftMouthCornerYMovAvForce;
                    $targetFaceData["mouth"]["left_corner_mouth_movement_x"][$i]["force"] = $leftMouthCornerXMovForce;
                    $targetFaceData["mouth"]["left_corner_mouth_movement_y"][$i]["force"] = $leftMouthCornerYMovForce;

                    $yMov = '';
                    if ($leftMouthCornerYMov < 0) $yMov = 'up';
                    if ($leftMouthCornerYMov > 0) $yMov = 'down';
                    if ($leftMouthCornerXMov < 0) $xMov = 'from center';
                    else $xMov = 'to center';
                    //                       if ($yMov == 'none') $yMov = '';
//                        if (($xMov != '') && ($yMov != '')) $yMov = ' and ' . $yMov;
//                        if (($xMov == '') && ($yMov == '')) $yMov = 'none';
//                        $targetFaceData["mouth"]["left_corner_mouth_movement"][$i]["val"] = $xMov . $yMov;
                    $targetFaceData["mouth"]["left_corner_mouth_movement_x"][$i]["val"] = $xMov;
                    $targetFaceData["mouth"]["left_corner_mouth_movement_y"][$i]["val"] = $yMov;
                    if ($targetFaceData["mouth"]["left_corner_mouth_movement_x"][$i]["force"] == 0)
                        $targetFaceData["mouth"]["left_corner_mouth_movement_x"][$i]["val"] = 'none';
                    if ($targetFaceData["mouth"]["left_corner_mouth_movement_y"][$i]["force"] == 0)
                        $targetFaceData["mouth"]["left_corner_mouth_movement_y"][$i]["val"] = 'none';
                }

                if (isset($sourceFaceData['normmask'][$i][54])) {
                    $rightMouthCornerXMov = $sourceFaceData['normmask'][$i][54]['X'] - $xN54;
                    $rightMouthCornerYMov = $sourceFaceData['normmask'][$i][54]['Y'] - $yN54;

                    $rightMouthCornerXMovForce = $this->getForce($scaleX54, abs($rightMouthCornerXMov));
                    $rightMouthCornerYMovForce = $this->getForce($scaleY54, abs($rightMouthCornerYMov));
//                    $rightMouthCornerYMovAvForce = round(($rightMouthCornerXMovForce + $rightMouthCornerYMovForce) / 2);
//                    $targetFaceData["mouth"]["right_corner_mouth_movement"][$i]["force"] = $rightMouthCornerYMovAvForce;
                    $targetFaceData["mouth"]["right_corner_mouth_movement_x"][$i]["force"] = $rightMouthCornerXMovForce;
                    $targetFaceData["mouth"]["right_corner_mouth_movement_y"][$i]["force"] = $rightMouthCornerYMovForce;

                    if (isset($sourceFaceData['normmask'][$i][48])) {
                        $mouthLength = $sourceFaceData['normmask'][$i][54]['X'] - $sourceFaceData['normmask'][$i][48]['X'];
                    }
                    if ($rightMouthCornerYMov < 0) $yMov = 'up';
                    if ($rightMouthCornerYMov > 0) $yMov = 'down';
                    if ($rightMouthCornerXMov > 0) $xMov = 'from center';
                    else $xMov = 'to center';
//                    if ($yMov == 'none') $yMov = '';
//                    if (($xMov != '') && ($yMov != '')) $yMov = ' and ' . $yMov;
//                    if (($xMov == '') && ($yMov == '')) $yMov = 'none';
//                    $targetFaceData["mouth"]["right_corner_mouth_movement"][$i]["val"] = $xMov . $yMov;
                    $targetFaceData["mouth"]["right_corner_mouth_movement_x"][$i]["val"] = $xMov;
                    $targetFaceData["mouth"]["right_corner_mouth_movement_y"][$i]["val"] = $yMov;
                    if ($targetFaceData["mouth"]["right_corner_mouth_movement_x"][$i]["force"] == 0)
                        $targetFaceData["mouth"]["right_corner_mouth_movement_x"][$i]["val"] = 'none';
                    if ($targetFaceData["mouth"]["right_corner_mouth_movement_y"][$i]["force"] == 0)
                        $targetFaceData["mouth"]["right_corner_mouth_movement_y"][$i]["val"] = 'none';

                }

                //движение уголков рта
                $xMov = 'none';
                if (($targetFaceData["mouth"]["right_corner_mouth_movement_x"][$i]["val"] ==
                        $targetFaceData["mouth"]["left_corner_mouth_movement_x"][$i]["val"] ) and
                    (isset($targetFaceData["mouth"]["left_corner_mouth_movement_x"][$i]))) {
                    $xMov = $targetFaceData["mouth"]["right_corner_mouth_movement_x"][$i]["val"];
                }
                $targetFaceData["mouth"]["mouth_corners_movement"][$i]["val"] = $xMov;
/*                $yMov = '';
                if (($leftMouthCornerXMov > 0) && ($rightMouthCornerXMov > 0))
                    $yMov = 'down';
                else
                    if (($leftMouthCornerXMov < 0) && ($rightMouthCornerXMov < 0))
                        $yMov = 'up';

                $force1 = 0;
                if (isset($targetFaceData["mouth"]["right_corner_mouth_movement"][$i]) &&
                    isset($targetFaceData["mouth"]["right_corner_mouth_movement"][$i]["force"]))
                    $force1 = $targetFaceData["mouth"]["right_corner_mouth_movement"][$i]["force"];
                if (isset($targetFaceData["mouth"]["left_corner_mouth_movement"][$i])) {
                    $force2 = $targetFaceData["mouth"]["left_corner_mouth_movement"][$i]["force"];
                    $forceAv = round(($force1 + $force2) / 2);
                }*/
                /*        $targetFaceData["mouth"]["mouth_corners_movement"][$i]["force"] = $forceAv;

                        if($xMov == 'none') $xMov = '';
                        if($yMov == 'none') $yMov = '';
                        if (($xMov != '')&&($yMov != ''))  $yMov = ' and '.$yMov;

                        $targetFaceData["mouth"]["mouth_corners_movement"][$i]["val"] = trim($xMov.$yMov);
                        if ($targetFaceData["mouth"]["mouth_corners_movement"][$i]["val"] == '')
                           $targetFaceData["mouth"]["mouth_corners_movement"][$i]["val"] = 'none';*/
                $mouthLengthX = abs($mouthLength - $mouthLengthN);
                $targetFaceData["mouth"]["mouth_length"][$i]["force"] = $this->getForce(
                    $scaleMouthLength, $mouthLengthX);

                if ($mouthLength === $mouthLengthN) {
                    $targetFaceData["mouth"]["mouth_length"][$i]["force"] = 0;
                    $targetFaceData["mouth"]["mouth_length"][$i]["val"] = 'none';
                }
                if ($mouthLength > $mouthLengthN) $targetFaceData["mouth"]["mouth_length"][$i]["val"] = '+';
                if ($mouthLength < $mouthLengthN) $targetFaceData["mouth"]["mouth_length"][$i]["val"] = '-';

                /*            if (($targetFaceData["mouth"]["left_corner_mouth_movement"][$i]["val"] = 'left') and
                                ($targetFaceData["mouth"]["right_corner_mouth_movement"][$i]["val"] = 'right'))
                                $targetFaceData["mouth"]["mouth_length"][$i]["val"] = '+';
                            if (($targetFaceData["mouth"]["left_corner_mouth_movement"][$i]["val"] = 'right') and
                                ($targetFaceData["mouth"]["right_corner_mouth_movement"][$i]["val"] = 'left'))
                                $targetFaceData["mouth"]["mouth_length"][$i]["val"] = '-';*/

                // изменение ширины рта
                // NORM_POINTS 51 57

                if (isset($sourceFaceData['normmask'][$i][51])) {
                    $upperLipYMov = $sourceFaceData['normmask'][$i][51]['Y'] - $yN51;
                    $targetFaceData["mouth"]["mouth_upper_lip_outer_center_movement"][$i]["force"] = $this->getForce(
                        $scaleY51, abs($upperLipYMov));
//                $force1 = $targetFaceData["mouth"]["mouth_upper_lip_outer_center_movement"][$i]["force"];
                }

                if (isset($targetFaceData["mouth"]["left_corner_mouth_movement"][$i]))
                    if (isset($targetFaceData["mouth"]["mouth_upper_lip_outer_center_movement"][$i]) &&
                        $targetFaceData["mouth"]["mouth_upper_lip_outer_center_movement"][$i]["force"] == 0)
                        $targetFaceData["mouth"]["mouth_upper_lip_outer_center_movement"][$i]["val"] = 'none';
                    else
                        if ($upperLipYMov < 0)
                            $targetFaceData["mouth"]["mouth_upper_lip_outer_center_movement"][$i]["val"] = 'up';
                        else
                            $targetFaceData["mouth"]["mouth_upper_lip_outer_center_movement"][$i]["val"] = 'down';
//            $deltaYUpperLip = $y;
//            if (isset($targetFaceData["mouth"]["mouth_upper_lip_outer_center_movement"][$i]["force"]))
//                $force1 = $targetFaceData["mouth"]["mouth_upper_lip_outer_center_movement"][$i]["force"];

                if (isset($sourceFaceData['normmask'][$i][57])) {
                    $lowerLipYMov = $sourceFaceData['normmask'][$i][57]['Y'] - $yN57;
                    if (isset($sourceFaceData['normmask'][$i][51])) {
                        $mouthWidth = $sourceFaceData['normmask'][$i][57]['Y'] - $sourceFaceData['normmask'][$i][51]['Y'];
                    }
                }

                $targetFaceData["mouth"]["mouth_lower_lip_outer_center_movement"][$i]["force"] =
                    $this->getForce($scaleY57, abs($lowerLipYMov));

                if ($targetFaceData["mouth"]["mouth_lower_lip_outer_center_movement"][$i]["force"] == 0)
                    $targetFaceData["mouth"]["mouth_lower_lip_outer_center_movement"][$i]["val"] = 'none';
                else
                    if ($lowerLipYMov > 0)
                        $targetFaceData["mouth"]["mouth_lower_lip_outer_center_movement"][$i]["val"] = 'down';
                    else
                        $targetFaceData["mouth"]["mouth_lower_lip_outer_center_movement"][$i]["val"] = 'up';
//            $deltaYLowerLip = $y;
//            $force2 = $targetFaceData["mouth"]["mouth_lower_lip_outer_center_movement"][$i]["force"];
//            $forceAv = round(($force1 + $force2)/2);

                $targetFaceData["mouth"]["mouth_width"][$i]["force"] = $this->getForce(
                    $scaleMouthWidth, abs(($mouthWidth - $mouthWidthN)));

                if ($mouthWidth === $mouthWidthN) {
                    $targetFaceData["mouth"]["mouth_width"][$i]["force"] = 0;
                    $targetFaceData["mouth"]["mouth_width"][$i]["val"] = 'none';
                }
//            if() !!!! 'compressed'
                if ($mouthWidth > $mouthWidthN) $targetFaceData["mouth"]["mouth_width"][$i]["val"] = '+';
                if ($mouthWidth < $mouthWidthN) $targetFaceData["mouth"]["mouth_width"][$i]["val"] = '-';

                /*           $targetFaceData["mouth"]["mouth_width"][$i]["force"] = $forceAv;
                           $targetFaceData["mouth"]["mouth_width"][$i]["val"] = 'none';
                           if (($deltaYLowerLip == 0)and($deltaYLowerLip == 0)){
                               $targetFaceData["mouth"]["mouth_width"][$i]["val"] = 'compressed';
                           }
                           if(($targetFaceData["mouth"]["mouth_lower_lip_outer_center_movement"][$i]["val"] = 'down')and
                               ($targetFaceData["mouth"]["mouth_upper_lip_outer_center_movement"][$i]["val"] = 'up')){
                               $targetFaceData["mouth"]["mouth_width"][$i]["val"] = '+';
                           }
                           if(($targetFaceData["mouth"]["mouth_lower_lip_outer_center_movement"][$i]["val"] = 'up')and
                               ($targetFaceData["mouth"]["mouth_upper_lip_outer_center_movement"][$i]["val"] = 'down')){
                               $targetFaceData["mouth"]["mouth_width"][$i]["val"] = '-';
                           }*/
                // определение формы рта
                // NORM_POINTS 61 62 63 65 66 67
                if (isset($sourceFaceData['normmask'][$i][67])) {
                    $width1 = $sourceFaceData['normmask'][$i][67]['Y'] - $sourceFaceData['normmask'][$i][61]['Y'];
                    $width2 = $sourceFaceData['normmask'][$i][66]['Y'] - $sourceFaceData['normmask'][$i][62]['Y'];
                    $width3 = $sourceFaceData['normmask'][$i][65]['Y'] - $sourceFaceData['normmask'][$i][63]['Y'];
                    $lengthTest = $sourceFaceData['normmask'][$i][65]['X'] - $sourceFaceData['normmask'][$i][67]['X'];

                    //брать интенсивность изменения ширины рта
                    $targetFaceData["mouth"]["mouth_form"][$i]["force"] =
                        $targetFaceData["mouth"]["mouth_width"][$i]["force"];
                }
                // echo $width1.'/'.$width2.'/'.$width3.'<br>';
                if (($width1 != 0) and ($width2 != 0) and ($width3 != 0) and ($lengthTest / 4 < $width2))
                    if (($width1 < $width2) and ($width3 < $width2))
                        $targetFaceData["mouth"]["mouth_form"][$i]["val"] = 'ellipse';
                    else
                        $targetFaceData["mouth"]["mouth_form"][$i]["val"] = 'rectangle';
                else
                    $targetFaceData["mouth"]["mouth_form"][$i]["val"] = 'line';
            }

            return $targetFaceData["mouth"];
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
        foreach ($sourceFaceData1 as $k=>$v) {
            foreach ($v as $k1=>$v1) {
                if(isset($v1[0])) {
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
                    if ((isset($v1[$i]["force"])) && (isset($v1[$i-1]["force"])) &&
                        (isset($v1[$i]["val"])) && (isset($v1[$i-1]["val"]))) {

                        if (!isset($v1[$i-1]["trend"])){
                            $v1[$i-1]["trend"] = '1=';
                            $v1[$i-1]["confidence"] = 1;
                        }
                        if (($v1[$i-1]["force"]<$v1[$i]["force"]) &&    //если интенсивность увеличивается
                            ($v1[$i-1]["val"] === $v1[$i]["val"]) &&    //и значение не меняет направление
                            (isset($v1[$i-1]["trend"]) && (strpos($v1[$i-1]["trend"],'+') > 0))) { //и был тренд на увеличение, то продолжаем его
                            ++$currentTrendLength;
                            $v1[$i]["trend"] = $currentTrendLength.'+';
                            $v1[$i]["confidence"] = 1;
                        }

                        if (($v1[$i-1]["force"]>$v1[$i]["force"]) &&    //если интенсивность уменьшается
                            ($v1[$i-1]["val"] === $v1[$i]["val"]) &&    //и значение не меняет направление
                            (isset($v1[$i-1]["trend"]) && (strpos($v1[$i-1]["trend"],'-') > 0))) { //и был тренд на уменьшение, то продолжаем его
                            ++$currentTrendLength;
                            $v1[$i]["trend"] = $currentTrendLength.'-';
                            $v1[$i]["confidence"] = 1;
                        }

                        if (($v1[$i-1]["force"] === $v1[$i]["force"]) &&    //если интенсивность не меняется
                            ($v1[$i-1]["val"] === $v1[$i]["val"]) &&    //и значение не меняет направление
                            (isset($v1[$i-1]["trend"]) && (strpos($v1[$i-1]["trend"],'=') > 0))) { //и был тренд на сохранение, то продолжаем его
                            ++$currentTrendLength;
                            $v1[$i]["trend"] = $currentTrendLength.'=';
                            $v1[$i]["confidence"] = 1;
                        }

                        if (($v1[$i-1]["force"]<$v1[$i]["force"]) &&    //если интенсивность увеличивается
                            ($v1[$i-1]["val"] === $v1[$i]["val"]) &&    //и значение не меняет направление
                            (isset($v1[$i-1]["trend"]) && (strpos($v1[$i-1]["trend"],'+') === false))) {
                            //и был тренд на уменьшение или сохранение, то начинаем новый тренд на увеличение
                            $currentTrendLength = 1;
                            $v1[$i]["trend"] = $currentTrendLength.'+';
                            $v1[$i]["confidence"] = 1;
                        }

                        if (($v1[$i-1]["force"]>$v1[$i]["force"]) &&    //если интенсивность уменьшается
                            ($v1[$i-1]["val"] === $v1[$i]["val"]) &&    //и значение не меняет направление
                            (isset($v1[$i-1]["trend"]) && (strpos($v1[$i-1]["trend"],'-') === false))) {
                            //и был тренд на увеличение или сохранение, то начинаем новый тренд на уменьшение
                            $currentTrendLength = 1;
                            $v1[$i]["trend"] = $currentTrendLength.'-';
                            $v1[$i]["confidence"] = 1;
                        }

                        if (($v1[$i-1]["force"] === $v1[$i]["force"]) &&    //если интенсивность не маеняется
                            ($v1[$i-1]["val"] === $v1[$i]["val"]) &&    //и значение не меняет направление
                            (isset($v1[$i-1]["trend"]) && (strpos($v1[$i-1]["trend"],'=') === false))) {
                            //и был тренд на увеличение или уменьшение, то начинаем новый тренд на сохранение
                            $currentTrendLength = 1;
                            $v1[$i]["trend"] = $currentTrendLength.'=';
                            $v1[$i]["confidence"] = 1;
                        }

                        if ($v1[$i-1]["val"] !== $v1[$i]["val"]){ //если значения отличаются
                            //это либо числовое значение
                            if (is_numeric($v1[$i]["val"])){
                                if ($v1[$i-1]["val"]>$v1[$i]["val"]) $trenfVal = '-';
                                if ($v1[$i-1]["val"]<$v1[$i]["val"]) $trenfVal = '+';
                                //значение тренда сохраняется
                                if (isset($v1[$i-1]["trend"]) && (strpos($v1[$i-1]["trend"], $trenfVal) > 0)) {
                                    ++$currentTrendLength;
                                } else {$currentTrendLength = 1;}
                                $v1[$i]["trend"] = $currentTrendLength.$trenfVal;
                                $v1[$i]["confidence"] = 1;
                            } else {
                                //либо смена направления для качественного значения
                                $currentTrendLength = 1;
                                if ($v1[$i-1]["force"]>$v1[$i]["force"]) $trenfVal = '-';
                                if ($v1[$i-1]["force"]<$v1[$i]["force"]) $trenfVal = '+';
                                if ($v1[$i-1]["force"] === $v1[$i]["force"]) $trenfVal = '=';
                                $v1[$i]["trend"] = $currentTrendLength.$trenfVal;
                                $v1[$i]["confidence"] = 1;
                            }
                        }
                    }
                }
             $sourceFaceData1[$k][$k1] = $v1;
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
        $arr = array('61','62', '63', '65', '66', '67', 36,37,38,39, 40, 41, 42, 43, 44, 45, 46,47, 31, 35,
            19,24, 17, 21, 22, 26, 48,54, 51, 57, );
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
        foreach ($sourceFaceData1 as $k=>$v) {

            // детекция носогубки (v.2): по уголкам рта
            if ($k === 'mouth'){
                foreach ($v as $k1 => $v1) {
                if (($k1 === 'left_corner_mouth_movement_x') || ($k1 === 'right_corner_mouth_movement_x')){
                    if(strpos($k1,'right')>-1) $prefix = 'right_';
                    elseif ($prefix = 'left_');
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
            }
            if ($k === 'eye') {
                $maxREW = $this->getFaceDataMaxForKeyV3($sourceFaceData1['eye'],'right_eye_width', "val");
                $maxLEW = $this->getFaceDataMaxForKeyV3($sourceFaceData1['eye'],'left_eye_width', "val");
                $maxREW2 = $this->getFaceDataMaxForKeyV3($sourceFaceData1['eye'],'right_eye_width2', "val");
                $maxLEW2 = $this->getFaceDataMaxForKeyV3($sourceFaceData1['eye'],'left_eye_width2', "val");

                foreach ($v as $k1 => $v1) {
                    //eye_width
                    if (($k1 === 'right_eye_width')||($k1 === 'left_eye_width')) {
                        if(strpos($k1,'right')>-1) $prefix = 'right_';
                        elseif ($prefix = 'left_');
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
                        elseif ($prefix = 'left_');
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
                                if (($v1[$i]["val"] !== '-')&&($eyeClosedFrame === '-1')) {
                                    $eyeStartClosingFrame = '-1';
                                    $eyeStartOpeningFrame = '-1';
                                }

                                //если глаз закрыт и ранее это не фиксировалось, то фиксируем
                                if(($sourceFaceData1[$k][$prefix."eye_closed"][$i]["val"] === 'yes')
                                    &&($eyeClosedFrame === '-1')) $eyeClosedFrame = $i;

                                //если глаз открыт и ранее фиксировалось его закрытие, то возможно моргание
                                if(($sourceFaceData1[$k][$prefix."eye_closed"][$i]["val"] === 'no')
                                    &&($eyeClosedFrame !== '-1')){
                                    //processing
                                   if($eyeStartClosingFrame !== '-1') {
                                        //изменить значения свойств в диапазоне от $eyeStartClosingFrame до $eyeEndOpeningFrame
                                        $sourceFaceData1[$k][$prefix . "eye_blink"] =
                                            $this->updateValues($sourceFaceData1[$k][$prefix . "eye_blink"], 'val',
                                                'yes', $eyeStartClosingFrame, ($i + $eyeClosedFrame - $eyeStartClosingFrame));
//                                       $eyeStartClosingFrame = $i + $eyeClosedFrame - $eyeStartClosingFrame;
                                    }
                                 $eyeClosedFrame = '-1';
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
     * Обнаружение признаков на основе анализа входных данных.
     *
     * @param $json - содержимое файла в формате json с лицевыми точками (landmarks)
     * @return array - выходной массив с опредеделенными признаками
     */
    public function detectFeatures($json)
    {
        // load data
        $FaceData_ = json_decode($json, true);
        // check input format and convert the I format to AB
        if(strpos($json,'NORM_POINTS') !== false)
            $FaceData = $this->convertIJson($FaceData_);
        else
            $FaceData =  $FaceData_; // use the AB format
        $detectedFeatures['eye'] = $this->detectEyeFeatures($FaceData);
        $detectedFeatures['mouth'] = $this->detectMouthFeatures($FaceData);
        $detectedFeatures['brow'] = $this->detectBrowFeatures($FaceData);
        $detectedFeatures['eyebrow'] = $this->detectEyeBrowFeatures($FaceData);
        $detectedFeatures['nose'] = $this->detectNoseFeatures($FaceData);
        $detectedFeatures['chin'] = $this->detectChinFeatures($FaceData);
        $detectedFeaturesWithTrends = $this->detectTrends($detectedFeatures,5);
        $detectedFeaturesWithTrends = $this->detectAdditionalFeatures($detectedFeaturesWithTrends);

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
        if (($sourceFeatureName == 'left_eyebrow_movement_x') || ($sourceFeatureName == 'left_eyebrow_movement_Y'))
            $targetValues['targetFacePart'] = 'Левая бровь';
        if (($sourceFeatureName == 'right_eyebrow_movement_x') || ($sourceFeatureName == 'right_eyebrow_movement_y') )
            $targetValues['targetFacePart'] = 'Правая бровь';
        if ((($sourceFeatureName == 'left_eyebrow_movement_x') || ($sourceFeatureName == 'right_eyebrow_movement_x')
            || ($sourceFeatureName == 'left_eyebrow_movement_y') || ($sourceFeatureName == 'right_eyebrow_movement_y')) &&
            ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Отсутствие типа';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if ((($sourceFeatureName == 'left_eyebrow_movement_x') || ($sourceFeatureName == 'right_eyebrow_movement_x')) &&
            ($sourceValue == 'to center')) {
            $targetValues['featureChangeType'] = 'Изменение положения по горизонтали';
            $targetValues['changeDirection'] = 'К центру';
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
        if ((($sourceFeatureName == 'left_eyebrow_inner_movement_x') ||
                ($sourceFeatureName == 'right_eyebrow_inner_movement_x') ||
                ($sourceFeatureName == 'left_eyebrow_inner_movement_y') ||
                ($sourceFeatureName == 'right_eyebrow_inner_movement_y')) &&
            ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Отсутствие типа';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if ((($sourceFeatureName == 'left_eyebrow_inner_movement_x') ||
                ($sourceFeatureName == 'right_eyebrow_inner_movement_x')) &&
            ($sourceValue == 'to center')) {
            $targetValues['featureChangeType'] = 'Изменение положения по горизонтали';
            $targetValues['changeDirection'] = 'К центру';
        }
        if ((($sourceFeatureName == 'left_eyebrow_inner_movement_y') ||
                ($sourceFeatureName == 'right_eyebrow_inner_movement_y')) &&
            ($sourceValue == 'up')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вверх';
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
        if ((($sourceFeatureName == 'left_eye_lower_eyelid_movement_x') ||
                ($sourceFeatureName == 'right_eye_lower_eyelid_movement_x') ||
                ($sourceFeatureName == 'left_eye_lower_eyelid_movement_y') ||
                ($sourceFeatureName == 'right_eye_lower_eyelid_movement_y') ||
                ($sourceFeatureName == 'left_eye_lower_eyelid_movement_d') ||
                ($sourceFeatureName == 'right_eye_lower_eyelid_movement_d')) &&
            (($sourceValue == 'none') || ($sourceValue == 'none and none'))) {
            $targetValues['featureChangeType'] = 'Отсутствие типа';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if ((($sourceFeatureName == 'left_eye_lower_eyelid_movement') ||
                ($sourceFeatureName == 'right_eye_lower_eyelid_movement')) &&
            ($sourceValue == 'up')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вверх';
        }
        if ((($sourceFeatureName == 'left_eye_lower_eyelid_movement') ||
                ($sourceFeatureName == 'right_eye_lower_eyelid_movement')) &&
            ($sourceValue == 'down')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вниз';
        }
        if ((($sourceFeatureName == 'left_eye_lower_eyelid_movement_d') ||
                ($sourceFeatureName == 'right_eye_lower_eyelid_movement_d')) &&
            ($sourceValue == 'to center and up')) {
            $targetValues['featureChangeType'] = 'Изменение положения по диагонали';
            $targetValues['changeDirection'] = 'К центру и вверх';
        }

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
        if ($sourceFeatureName == 'left_eye_pupil_movement')
            $targetValues['targetFacePart'] = 'Левый зрачок';
        if ($sourceFeatureName == 'right_eye_pupil_movement')
            $targetValues['targetFacePart'] = 'Правый зрачок';
        if ((($sourceFeatureName == 'left_eye_pupil_movement') || ($sourceFeatureName == 'right_eye_pupil_movement')) &&
            ($sourceValue == 'straight ahead')) {
            $targetValues['featureChangeType'] = 'Отсутствие типа';
            $targetValues['changeDirection'] = 'Прямо перед собой';
        }
        if ((($sourceFeatureName == 'left_eye_pupil_movement') || ($sourceFeatureName == 'right_eye_pupil_movement')) &&
            ($sourceValue == 'straight left')) {
            $targetValues['featureChangeType'] = 'Изменение положения по горизонтали';
            $targetValues['changeDirection'] = 'Влево';
        }
        if ((($sourceFeatureName == 'left_eye_pupil_movement') || ($sourceFeatureName == 'right_eye_pupil_movement')) &&
            ($sourceValue == 'down right')) {
            $targetValues['featureChangeType'] = 'Изменение положения по диагонали';
            $targetValues['changeDirection'] = 'Вниз и вправо';
        }
        if ((($sourceFeatureName == 'left_eye_pupil_movement') || ($sourceFeatureName == 'right_eye_pupil_movement')) &&
            ($sourceValue == 'up left')) {
            $targetValues['featureChangeType'] = 'Изменение положения по диагонали';
            $targetValues['changeDirection'] = 'Вверх и влево';
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
            $targetValues['featureChangeType'] = 'Отсутствие типа';
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
            $targetValues['featureChangeType'] = 'Отсутствие типа';
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
        if ((($sourceFeatureName == 'left_corner_mouth_movement_x') ||
                ($sourceFeatureName == 'right_corner_mouth_movement_x') ||
                ($sourceFeatureName == 'left_corner_mouth_movement_y') ||
                ($sourceFeatureName == 'right_corner_mouth_movement_y')) &&
            ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Отсутствие типа';
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
            $targetValues['featureChangeType'] = 'Отсутствие типа';
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
        /* Соответствия для носа */
        // Крылья носа
        if ($sourceFacePart == 'nose')
            $targetValues['targetFacePart'] = 'Нос';
        if ($sourceFeatureName == 'nose_wing_movement')
            $targetValues['targetFacePart'] = 'Крылья носа';
        if (($sourceFeatureName == 'nose_wing_movement') && ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Отсутствие типа';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if (($sourceFeatureName == 'nose_wing_movement') && ($sourceValue == 'up')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вверх';
        }
        /* Носогубная складка */
        if ($sourceFacePart == 'left_nasolabial_fold_movement')
            $targetValues['targetFacePart'] = 'Левая носогубная складка';
        if ($sourceFeatureName == 'right_nasolabial_fold_movement')
            $targetValues['targetFacePart'] = 'Правая носогубная складка';
        if ((($sourceFeatureName == 'left_nasolabial_fold_movement') ||
                ($sourceFeatureName == 'right_nasolabial_fold_movement')) &&
            ($sourceValue == 'none')) {
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
        $numberFrames = count($detectedFeatures['eye']['left_eye_upper_eyelid_movement']);
        // Цикл от 1 до общего-кол-ва кадров
        for ($i = 1; $i < $numberFrames; $i++) {
            // Массив фактов для текущего кадра
            $frameFacts = array();
            // Обход всех определенных лицевых признаков
            foreach ($detectedFeatures as $facePart => $features)
                foreach ($features as $featureName => $frames)
                    for ($j = 1; $j < count($frames); $j++)
                        if (isset($frames[$j]["val"]) && isset($frames[$j]["force"]))
                            if ($i == $j) {
                                // Поиск соответствий
                                $targetValues = self::findCorrespondences($facePart, $featureName, $frames[$j]["val"]);
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
                                    $fact['s869'] = count($frames);
                                    $fact['s870'] = 1;
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
}