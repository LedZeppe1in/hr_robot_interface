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
        $res = abs(round((100*$val2 / $val1)));
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
            $xN39 = $sourceFaceData['normmask'][0][39]['X'];
            $xN42 = $sourceFaceData['normmask'][0][42]['X'];
            $yN39 = $sourceFaceData['normmask'][0][39]['Y'];
            $yN42 = $sourceFaceData['normmask'][0][42]['Y'];
            $yN36 = $sourceFaceData['normmask'][0][36]['Y'];
            $yN45 = $sourceFaceData['normmask'][0][45]['Y'];
        }

        $maxY37 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 37,"Y");
        $minY37 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'],37, "Y");
        $scaleY37 = $maxY37 - $minY37;
        $maxY43 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 43,"Y");
        $minY43 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'],43, "Y");
        $scaleY43 = $maxY43 - $minY43;
        $maxY41 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 41,"Y");
        $minY41 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'],41, "Y");
        $scaleY41 = $maxY41 - $minY41;
        $maxLeftEyeWidth = $maxY41 - $minY37;
        $maxY47 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 47,"Y");
        $minY47 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'],47, "Y");
        $scaleY47 = $maxY47 - $minY47;
        $maxRightEyeWidth = $maxY47 - $minY43;
        $maxX39 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 39,"X");
        $minX39 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'],39, "X");
        $scaleX39 = $maxX39 - $minX39;
        $maxX42 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 42,"X");
        $minX42 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'],42, "X");
        $scaleX42 = $maxX42 - $minX42;
        $maxY39 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 39,"Y");
        $minY39 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'],39, "Y");
        $scaleY39 = $maxY39 - $minY39;
        $maxY42 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 42,"Y");
        $minY42 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'],42, "Y");
        $scaleY42 = $maxY42 - $minY42;
        $maxY36 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 36,"Y");
        $minY36 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'],36, "Y");
        $scaleY36 = $maxY36 - $minY36;
        $maxY45 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 45,"Y");
        $minY45 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'],45, "Y");
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
            if ($leftEyeUpperEyelidH == 0) $targetFaceData["eye"]["left_eye_upper_eyelid_movement"][$i]["val"] = 'none';
            if ($rightEyeUpperEyelidH < 0) $targetFaceData["eye"]["right_eye_upper_eyelid_movement"][$i]["val"] = 'up';
            if ($rightEyeUpperEyelidH > 0) $targetFaceData["eye"]["right_eye_upper_eyelid_movement"][$i]["val"] = 'down';
            if ($rightEyeUpperEyelidH == 0) $targetFaceData["eye"]["right_eye_upper_eyelid_movement"][$i]["val"] = 'none';
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

            $targetFaceData["eye"]["left_eye_lower_eyelid_movement"][$i]["force"] = $this->getForce(
                $scaleY41, abs($leftEyeLowerEyelidH));
            $targetFaceData["eye"]["right_eye_lower_eyelid_movement"][$i]["force"] = $this->getForce(
                $scaleY47, abs($rightEyeLowerEyelidH));

            if ($leftEyeLowerEyelidH < 0) $targetFaceData["eye"]["left_eye_lower_eyelid_movement"][$i]["val"] = 'up';
            if ($leftEyeLowerEyelidH > 0) $targetFaceData["eye"]["left_eye_lower_eyelid_movement"][$i]["val"] = 'down';
            if ($leftEyeLowerEyelidH == 0) $targetFaceData["eye"]["left_eye_lower_eyelid_movement"][$i]["val"] = 'none';
            if ($leftEyeInnerCorner > 0) {
                $targetFaceData["eye"]["left_eye_lower_eyelid_movement"][$i]["val"] =
                    trim($targetFaceData["eye"]["left_eye_lower_eyelid_movement"][$i]["val"] . ' to center');
                $targetFaceData["eye"]["left_eye_lower_eyelid_movement"][$i]["force"] =
                    round((($targetFaceData["eye"]["left_eye_lower_eyelid_movement"][$i]["force"] +
                            $leftEyeInnerCornerForce) / 2), 2);
                }

            if ($rightEyeLowerEyelidH < 0) $targetFaceData["eye"]["right_eye_lower_eyelid_movement"][$i]["val"] = 'up';
            if ($rightEyeLowerEyelidH > 0) $targetFaceData["eye"]["right_eye_lower_eyelid_movement"][$i]["val"] = 'down';
            if ($rightEyeLowerEyelidH == 0) $targetFaceData["eye"]["right_eye_lower_eyelid_movement"][$i]["val"] = 'none';
            if ($rightEyeInnerCorner < 0) {
                $targetFaceData["eye"]["right_eye_lower_eyelid_movement"][$i]["val"] =
                    trim($targetFaceData["eye"]["right_eye_lower_eyelid_movement"][$i]["val"] . ' to center');
                $targetFaceData["eye"]["right_eye_lower_eyelid_movement"][$i]["force"] =
                    round((($targetFaceData["eye"]["right_eye_lower_eyelid_movement"][$i]["force"] +
                            $rightEyeInnerCornerForce) / 2), 2);
            }
            //------------------------------------------------------------------------------------------------
            //Нижнее веко, движение нижнего века (без движения, вверх, вниз, к центру и вверх)
            //Глаза, ширина глаз (увеличение, уменьшение) через движение век
           /* if (($targetFaceData["eye"]["left_eye_lower_eyelid_movement"][$i]["val"] == 'down')and
                ($targetFaceData["eye"]["left_eye_upper_eyelid_movement"][$i]["val"] == 'up')){
                $targetFaceData["eye"]["left_eye_width_changing"][$i]["force"] =
                    round((($targetFaceData["eye"]["left_eye_lower_eyelid_movement"][$i]["force"] +
                            $targetFaceData["eye"]["left_eye_upper_eyelid_movement"][$i]["force"]) / 2), 2);
                $targetFaceData["eye"]["left_eye_width_changing"][$i]["val"] = '+';
            } else{
                $targetFaceData["eye"]["left_eye_width_changing"][$i]["force"] = 0;
                $targetFaceData["eye"]["left_eye_width_changing"][$i]["val"] = 'none';
            }

            if (($targetFaceData["eye"]["right_eye_lower_eyelid_movement"][$i]["val"] == 'down')and
                ($targetFaceData["eye"]["right_eye_upper_eyelid_movement"][$i]["val"] == 'up')){
                $targetFaceData["eye"]["right_eye_width_changing"][$i]["force"] =
                    round((($targetFaceData["eye"]["right_eye_lower_eyelid_movement"][$i]["force"] +
                            $targetFaceData["eye"]["right_eye_upper_eyelid_movement"][$i]["force"]) / 2), 2);
                $targetFaceData["eye"]["right_eye_width_changing"][$i]["val"] = '+';
            } else{
                $targetFaceData["eye"]["right_eye_width_changing"][$i]["force"] = 0;
                $targetFaceData["eye"]["right_eye_width_changing"][$i]["val"] = 'none';
            }*/
            //width, расстояние между 37 и 41 для левого глаза, для правого - 43 и 47
            if (isset($sourceFaceData['normmask'][$i][37])&&
                isset($sourceFaceData['normmask'][$i][41])&&
                isset($sourceFaceData['normmask'][$i][43])&&
                isset($sourceFaceData['normmask'][$i][47])){
                $leftEyeWidth = $sourceFaceData['normmask'][$i][41]['Y'] - $sourceFaceData['normmask'][$i][37]['Y'];
                $rightEyeWidth = $sourceFaceData['normmask'][$i][47]['Y'] - $sourceFaceData['normmask'][$i][43]['Y'];
                $targetFaceData["eye"]["left_eye_width"][$i]["force"] = $this->getForce(
                    $maxLeftEyeWidth, abs($leftEyeWidth - $leftEyeWidthN));
                $targetFaceData["eye"]["left_eye_width"][$i]["val"] = $leftEyeWidth;
//                $targetFaceData["eye"]["left_eye_width"][$i]["valNorm"] = $leftEyeWidthN;
                $targetFaceData["eye"]["right_eye_width"][$i]["force"] = $this->getForce(
                    $maxRightEyeWidth, abs($rightEyeWidth - $rightEyeWidthN));
//                echo $rightEyeWidthN.'/'.$maxRightEyeWidth.'/'.$rightEyeWidth.'<br>';
                $targetFaceData["eye"]["right_eye_width"][$i]["val"] = $rightEyeWidth;
//                $targetFaceData["eye"]["right_eye_width"][$i]["valNorm"] = $rightEyeWidthN;
                //Глаза, ширина глаз (увеличение, уменьшение) через изменение ширины
                $targetFaceData["eye"]["left_eye_width_changing"][$i]["force"] =
                    $targetFaceData["eye"]["left_eye_width"][$i]["force"];
                $targetFaceData["eye"]["left_eye_width_changing"][$i]["val"] = 'none';
              if($targetFaceData["eye"]["left_eye_width"][$i]["val"]>$leftEyeWidthN)
                  $targetFaceData["eye"]["left_eye_width_changing"][$i]["val"] = '+';
              if($targetFaceData["eye"]["left_eye_width"][$i]["val"]<$leftEyeWidthN)
                  $targetFaceData["eye"]["left_eye_width_changing"][$i]["val"] = '-';

                $targetFaceData["eye"]["right_eye_width_changing"][$i]["force"] =
                    $targetFaceData["eye"]["right_eye_width"][$i]["force"];
                $targetFaceData["eye"]["right_eye_width_changing"][$i]["val"] = 'none';
                if($targetFaceData["eye"]["right_eye_width"][$i]["val"]>$rightEyeWidthN)
                    $targetFaceData["eye"]["right_eye_width_changing"][$i]["val"] = '+';
                if($targetFaceData["eye"]["right_eye_width"][$i]["val"]<$rightEyeWidthN)
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
            if ($leftEyeOuterCornerH == 0) $targetFaceData["eye"]["left_eye_outer_movement"][$i]["val"] = 'none';
            if ($rightEyeOuterCornerH < 0) $targetFaceData["eye"]["right_eye_outer_movement"][$i]["val"] = 'up';
            if ($rightEyeOuterCornerH > 0) $targetFaceData["eye"]["right_eye_outer_movement"][$i]["val"] = 'down';
            if ($rightEyeOuterCornerH == 0) $targetFaceData["eye"]["right_eye_outer_movement"][$i]["val"] = 'none';
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
            if ($leftEyeInnerCornerH == 0) $targetFaceData["eye"]["left_eye_inner_movement"][$i]["val"] = 'none';
            if ($rightEyeInnerCornerH < 0) $targetFaceData["eye"]["right_eye_inner_movement"][$i]["val"] = 'up';
            if ($rightEyeInnerCornerH > 0) $targetFaceData["eye"]["right_eye_inner_movement"][$i]["val"] = 'down';
            if ($rightEyeInnerCornerH == 0) $targetFaceData["eye"]["right_eye_inner_movement"][$i]["val"] = 'none';
            //------------------------------------------------------------------------------------------------
        }
       return $targetFaceData["eye"];
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
                }
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
    public function detectNoseFeatures($sourceFaceData){
        //анализируемые точки ерза носа
        // 31 (left_nose_wing),
        // 35 (right_nose_wing),
        // получение нормированного значения по кадру 0

        if (isset($sourceFaceData['normmask'][0][31])
            && isset($sourceFaceData['normmask'][0][35])
        ) {
            $yN31 = $sourceFaceData['normmask'][0][31]['Y'];
            $yN35 = $sourceFaceData['normmask'][0][35]['Y'];
            $maxY31 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 31,"Y");
            $minY31 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'],31, "Y");
            $scaleY31 = $maxY31 - $minY31;
            $maxY35 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 35,"Y");
            $minY35 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'],35, "Y");
            $scaleY35 = $maxY35 - $minY35;
        }

        for ($i = 0; $i < count($sourceFaceData['normmask']); $i++) {
            if (isset($sourceFaceData['normmask'][$i][31]) && $sourceFaceData['normmask'][$i][35]){
                $leftNoseWingMovement = $sourceFaceData['normmask'][$i][31]['Y'] - $yN31;
                $rightNoseWingMovement = $sourceFaceData['normmask'][$i][35]['Y'] - $yN35;

                $leftNoseWingMovementForce = $this->getForce($scaleY31, abs($leftNoseWingMovement));
                $rightNoseWingMovementForce = $this->getForce($scaleY35, abs($rightNoseWingMovement));
                $noseWingsMovementForce = round(($leftNoseWingMovementForce+$rightNoseWingMovementForce)/2); //среднее значение
            }
            $targetFaceData["nose"]["nose_wing_movement"][$i]["force"] = $noseWingsMovementForce;
            if (($leftNoseWingMovement < 0)||($rightNoseWingMovement < 0)) $targetFaceData["nose"]["nose_wing_movement"][$i]["val"] = 'up';
            if (($leftNoseWingMovement > 0)||($rightNoseWingMovement > 0)) $targetFaceData["nose"]["nose_wing_movement"][$i]["val"] = 'down';
            if (($leftNoseWingMovement == 0)&&($rightNoseWingMovement == 0)) $targetFaceData["nose"]["nose_wing_movement"][$i]["val"] = 'none';
        }
        return $targetFaceData["nose"];
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
        }

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
            if (($leftEyebrowMovement == 0)&&($rightEyebrowMovement == 0)) $targetFaceData["brow"]["brow_width"][$i]["val"] = 'none';
        }
        return $targetFaceData["brow"];
    }

    /**
     * Обнаружение признаков лба.
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
        }

        $maxY17 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 17,"Y");
        $minY17 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'],17, "Y");
        $maxX17 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 17,"X");
        $minX17 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'],17, "X");
        $scaleY17 = $maxY17 - $minY17;
        $scaleX17 = $maxX17 - $minX17;

        $maxY21 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 21,"Y");
        $minY21 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'],21, "Y");
        $maxX21 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 21,"X");
        $minX21 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'],21, "X");
        $scaleY21 = $maxY21 - $minY21;
        $scaleX21 = $maxX21 - $minX21;

        $maxY22 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 22,"Y");
        $minY22 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'],22, "Y");
        $maxX22 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 22,"X");
        $minX22 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'],22, "X");
        $scaleY22 = $maxY22 - $minY22;
        $scaleX22 = $maxX22 - $minX22;

        $maxY26 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 26,"Y");
        $minY26 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'],26, "Y");
        $maxX26 = $this->getFaceDataMaxForKeyV2($sourceFaceData['normmask'], 26,"X");
        $minX26 = $this->getFaceDataMinForKeyV2($sourceFaceData['normmask'],26, "X");
        $scaleY26 = $maxY26 - $minY26;
        $scaleX26 = $maxX26 - $minX26;

        for ($i = 0; $i < count($sourceFaceData['normmask']); $i++) {
            //eyebrow_line
            //Линия брови – отрезок [внешний уголок брови-XY, внутренний уголок брови-XY]
            //Движение брови-H-OUT = внешний уголок брови -Y – внешний уголок брови-YN
            //Движение брови-H-IN = внутренний уголок брови-Y – внутренний уголок брови-YN
            if (isset($sourceFaceData['normmask'][$i][17]))
                $leftEyebrowMovementHOut =  $sourceFaceData['normmask'][$i][17]['Y'] - $yN17;
            if (isset($sourceFaceData['normmask'][$i][21]))
                $leftEyebrowMovementHIn =  $sourceFaceData['normmask'][$i][21]['Y'] - $yN21;
            if (isset($sourceFaceData['normmask'][$i][22]))
                $rightEyebrowMovementHIn =  $sourceFaceData['normmask'][$i][22]['Y'] - $yN22;
            if (isset($sourceFaceData['normmask'][$i][26]))
                $rightEyebrowMovementHOut =  $sourceFaceData['normmask'][$i][26]['Y'] - $yN26;

            $targetFaceData["eyebrow"]["left_eyebrow_inner_movement"][$i]["force"] = $this->getForce(
                $scaleY21, abs($leftEyebrowMovementHIn));
            $targetFaceData["eyebrow"]["left_eyebrow_outer_movement"][$i]["force"] = $this->getForce(
                $scaleY17, abs($leftEyebrowMovementHOut));
            $targetFaceData["eyebrow"]["right_eyebrow_inner_movement"][$i]["force"] = $this->getForce(
                $scaleY22, abs($rightEyebrowMovementHIn));
            $targetFaceData["eyebrow"]["right_eyebrow_outer_movement"][$i]["force"] = $this->getForce(
                $scaleY26, abs($rightEyebrowMovementHOut));

            if ($leftEyebrowMovementHIn > 0) $targetFaceData["eyebrow"]["left_eyebrow_inner_movement"][$i]["val"] = 'up';
            if ($leftEyebrowMovementHIn < 0) $targetFaceData["eyebrow"]["left_eyebrow_inner_movement"][$i]["val"] = 'down';
            if ($leftEyebrowMovementHIn == 0) $targetFaceData["eyebrow"]["left_eyebrow_inner_movement"][$i]["val"] = 'none';
            if ($leftEyebrowMovementHOut > 0) $targetFaceData["eyebrow"]["left_eyebrow_outer_movement"][$i]["val"] = 'up';
            if ($leftEyebrowMovementHOut < 0) $targetFaceData["eyebrow"]["left_eyebrow_outer_movement"][$i]["val"] = 'down';
            if ($leftEyebrowMovementHOut == 0) $targetFaceData["eyebrow"]["left_eyebrow_outer_movement"][$i]["val"] = 'none';
            if ($rightEyebrowMovementHIn > 0) $targetFaceData["eyebrow"]["right_eyebrow_inner_movement"][$i]["val"] = 'up';
            if ($rightEyebrowMovementHIn < 0) $targetFaceData["eyebrow"]["right_eyebrow_inner_movement"][$i]["val"] = 'down';
            if ($rightEyebrowMovementHIn == 0) $targetFaceData["eyebrow"]["right_eyebrow_inner_movement"][$i]["val"] = 'none';
            if ($rightEyebrowMovementHOut > 0) $targetFaceData["eyebrow"]["right_eyebrow_outer_movement"][$i]["val"] = 'up';
            if ($rightEyebrowMovementHOut < 0) $targetFaceData["eyebrow"]["right_eyebrow_outer_movement"][$i]["val"] = 'down';
            if ($rightEyebrowMovementHOut == 0) $targetFaceData["eyebrow"]["right_eyebrow_outer_movement"][$i]["val"] = 'none';

            //If Движение брови-H-OUT > 0 AND Движение брови-H-IN > 0 → Направление движения брови (Линии брови) = Вверх
            //If Движение брови-H-OUT < 0 AND Движение брови-H-IN < 0 → Направление движения брови (Линии брови)  = Вниз
            $targetFaceData["eyebrow"]["left_eyebrow_movement"][$i]["force"] =
             round((($targetFaceData["eyebrow"]["left_eyebrow_inner_movement"][$i]["force"] +
                 $targetFaceData["eyebrow"]["left_eyebrow_outer_movement"][$i]["force"])/2),2);
            $targetFaceData["eyebrow"]["right_eyebrow_movement"][$i]["force"] =
                round((($targetFaceData["eyebrow"]["right_eyebrow_inner_movement"][$i]["force"] +
                        $targetFaceData["eyebrow"]["right_eyebrow_outer_movement"][$i]["force"])/2),2);

            $targetFaceData["eyebrow"]["left_eyebrow_movement"][$i]["val"] = 'none';
            if (($leftEyebrowMovementHOut > 0)and($leftEyebrowMovementHIn > 0))
                $targetFaceData["eyebrow"]["left_eyebrow_movement"][$i]["val"] = 'up';
            if (($leftEyebrowMovementHOut < 0)and($leftEyebrowMovementHIn < 0))
                $targetFaceData["eyebrow"]["left_eyebrow_movement"][$i]["val"] = 'down';
            $targetFaceData["eyebrow"]["right_eyebrow_movement"][$i]["val"] = 'none';
            if (($rightEyebrowMovementHOut > 0)and($rightEyebrowMovementHIn > 0))
                $targetFaceData["eyebrow"]["right_eyebrow_movement"][$i]["val"] = 'up';
            if (($rightEyebrowMovementHOut < 0)and($rightEyebrowMovementHIn < 0))
                $targetFaceData["eyebrow"]["right_eyebrow_movement"][$i]["val"] = 'down';

            //If Направление движения брови (Линии брови) = Вверх AND Движение брови-H-OUT < Движение брови-H-IN
            //  → Направление движения внутреннего уголка брови = Вверх AND Направление движения внешнего уголка брови = Вниз
            if(($targetFaceData["eyebrow"]["left_eyebrow_movement"][$i]["val"] == 'up')and
                ($leftEyebrowMovementHOut < $leftEyebrowMovementHIn)){
                $targetFaceData["eyebrow"]["left_eyebrow_inner_movement"][$i]["val"] = 'up';
                $targetFaceData["eyebrow"]["left_eyebrow_outer_movement"][$i]["val"] = 'down';
            }
            if(($targetFaceData["eyebrow"]["right_eyebrow_movement"][$i]["val"] == 'up')and
                ($rightEyebrowMovementHOut < $rightEyebrowMovementHIn)){
                $targetFaceData["eyebrow"]["right_eyebrow_inner_movement"][$i]["val"] = 'up';
                $targetFaceData["eyebrow"]["right_eyebrow_outer_movement"][$i]["val"] = 'down';
            }

            //If |Движение брови-H-OUT| > |Движение брови-H-IN|  → Направление движения внешнего уголка брови = Вниз
            if(abs($leftEyebrowMovementHOut) > abs($leftEyebrowMovementHIn)){
                $targetFaceData["eyebrow"]["left_eyebrow_outer_movement"][$i]["val"] = 'down';
            }
            if(abs($rightEyebrowMovementHOut) > abs($rightEyebrowMovementHIn)){
                $targetFaceData["eyebrow"]["right_eyebrow_outer_movement"][$i]["val"] = 'down';
            }

            //If Движение брови-L-IN > 0 → Направление движения брови (Линии брови) = К центру
            //Движение брови-L-IN = внутренний уголок брови-X – внутренний уголок брови-XN
            if (isset($sourceFaceData['normmask'][$i][21]))
                $leftEyebrowMovementLIn =  $sourceFaceData['normmask'][$i][21]['X'] - $xN21;
            if (isset($sourceFaceData['normmask'][$i][22]))
                $rightEyebrowMovementLIn =  $sourceFaceData['normmask'][$i][22]['X'] - $xN22;
            if($leftEyebrowMovementLIn > 0) $targetFaceData["eyebrow"]["left_eyebrow_movement"][$i]["val"] =
                trim($targetFaceData["eyebrow"]["left_eyebrow_movement"][$i]["val"].' to center');
            if($rightEyebrowMovementLIn < 0) $targetFaceData["eyebrow"]["right_eyebrow_movement"][$i]["val"] =
                trim($targetFaceData["eyebrow"]["right_eyebrow_movement"][$i]["val"].' to center');
        }

        return $targetFaceData["eyebrow"];
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
        // get initial values
        // echo $FaceData_['normmask'][0][48][X];

        $facePoints = array(array(), array());
        for ($i = 0; $i < count($sourceFaceData['normmask'][0]); $i++)
            $facePoints[$i] = array(
                array($sourceFaceData['normmask'][0][$i]['X'], 500, 0, 0),
                array($sourceFaceData['normmask'][0][$i]['Y'], 500, 0, 0)
            );

        // get min and max values
        for ($i = 1; $i < count($sourceFaceData['normmask']); $i++)
            if (isset($sourceFaceData['normmask'][$i]))
                for ($j = 0; $j < count($sourceFaceData['normmask'][$i]); $j++) {
                    // min
                    // x
                    // echo $facePoints[$j][0][1].' :: '.$FaceData_['frame_#'.$i]['NORM_POINTS'][$j][0].'<br>';
                    if (($sourceFaceData['normmask'][$i][$j]['X'] < $facePoints[$j][0][1]) and
                        ($sourceFaceData['normmask'][$i][$j]['X'] != 0))
                        $facePoints[$j][0][1] = $sourceFaceData['normmask'][$i][$j]['X'];
                    // y
                    if (($sourceFaceData['normmask'][$i][$j]['Y'] < $facePoints[$j][1][1]) and
                        ($sourceFaceData['normmask'][$i][$j]['Y'] != 0))
                        $facePoints[$j][1][1] = $sourceFaceData['normmask'][$i][$j]['Y'];

                    // max
                    // x
                    if ($sourceFaceData['normmask'][$i][$j]['X'] > $facePoints[$j][0][2])
                        $facePoints[$j][0][2] = $sourceFaceData['normmask'][$i][$j]['X'];
                    // y
                    if ($sourceFaceData['normmask'][$i][$j]['Y'] > $facePoints[$j][1][2])
                        $facePoints[$j][1][2] = $sourceFaceData['normmask'][$i][$j]['Y'];
                }

        // get scale for x and y
        // length of the scale for power detection
        for ($i = 0; $i < count($facePoints); $i++) {
            $facePoints[$i][0][3] = $facePoints[$i][0][2] - $facePoints[$i][0][1];
            $facePoints[$i][1][3] = $facePoints[$i][1][2] - $facePoints[$i][1][1];
        }

        $targetFaceData = array();

        // print_r($facePoints);
        // изменнеие длины рта
        // NORM_POINTS 48 54
        // echo $FaceData_['normmask'][0][48][X];
        for ($i = 0; $i < count($sourceFaceData['normmask']); $i++) {
            if (isset($facePoints[48]) && isset($sourceFaceData['normmask'][$i][48])) {
                $x = $facePoints[48][0][0] - $sourceFaceData['normmask'][$i][48]['X'];
                $deltaYLeftCorner = $facePoints[48][0][1] - $sourceFaceData['normmask'][$i][48]['Y'];
                $targetFaceData["mouth"]["left_corner_mouth_movement"][$i]["force"] = $this->getForce(
                    $facePoints[48][0][3], $x
                );
            }
            // echo $facePoints[48][0][0].'-'.$FaceData_['normmask'][$i][48]['X'].'='.
            // $x.' scale='.$facePoints[48][0][3].' force='.
            // $targetFaceData["mouth"]["left_corner_mouth"][$i]["MovmentForce"].'<br>';
            if (isset($targetFaceData["mouth"]["left_corner_mouth_movement"][$i]))
                if ($targetFaceData["mouth"]["left_corner_mouth_movement"][$i]["force"] == 0)
                    $targetFaceData["mouth"]["left_corner_mouth_movement"][$i]["val"] = 'none';
                else
                    if ($x > 0)
                        $targetFaceData["mouth"]["left_corner_mouth_movement"][$i]["val"] = 'left';
                    else
                        $targetFaceData["mouth"]["left_corner_mouth_movement"][$i]["val"] = 'right';

            if (isset($facePoints[54]) && isset($sourceFaceData['normmask'][$i][54])) {
                $x = $facePoints[54][0][0] - $sourceFaceData['normmask'][$i][54]['X'];
                $deltaYRightCorner = $facePoints[54][0][1] - $sourceFaceData['normmask'][$i][54]['Y'];
            }
            $targetFaceData["mouth"]["right_corner_mouth_movement"][$i]["force"] = $this->getForce(
                $facePoints[54][0][3], $x
            );

            if ($targetFaceData["mouth"]["right_corner_mouth_movement"][$i]["force"] == 0)
                $targetFaceData["mouth"]["right_corner_mouth_movement"][$i]["val"] = 'none';
            else
                if ($x < 0)
                    $targetFaceData["mouth"]["right_corner_mouth_movement"][$i]["val"] = 'left';
                else
                    $targetFaceData["mouth"]["right_corner_mouth_movement"][$i]["val"] = 'right';
            //движение рта
            $xMov = '';
            if (($targetFaceData["mouth"]["right_corner_mouth_movement"][$i]["val"] == 'right') and
                (isset($targetFaceData["mouth"]["left_corner_mouth_movement"][$i]) &&
                    $targetFaceData["mouth"]["left_corner_mouth_movement"][$i]["val"] == 'left')) {
                $xMov = 'aside';
                // $targetFaceData["mouth"]["mouth_corners_movement"][$i]["val"] = 'aside';
            }
            $yMov = '';
            if (($deltaYLeftCorner > 0) && ($deltaYLeftCorner > 0))
                $yMov = 'up';
            else
               if (($deltaYLeftCorner < 0) && ($deltaYLeftCorner < 0))
                   $yMov = 'down';
            if (isset($targetFaceData["mouth"]["right_corner_mouth_movement"][$i]))
                $force1 = $targetFaceData["mouth"]["right_corner_mouth_movement"][$i]["force"];
            if (isset($targetFaceData["mouth"]["left_corner_mouth_movement"][$i]))
                $force2 = $targetFaceData["mouth"]["left_corner_mouth_movement"][$i]["force"];
            $forceAv = round(($force1 + $force2) / 2);
            $targetFaceData["mouth"]["mouth_corners_movement"][$i]["force"] = $forceAv;

            $targetFaceData["mouth"]["mouth_corners_movement"][$i]["val"] = trim($xMov.' '.$yMov);
            if ($targetFaceData["mouth"]["mouth_corners_movement"][$i]["val"] == '')
               $targetFaceData["mouth"]["mouth_corners_movement"][$i]["val"] = 'none';

            $targetFaceData["mouth"]["mouth_length"][$i]["force"] = $forceAv;
            $targetFaceData["mouth"]["mouth_length"][$i]["val"] = 'none';
            if (($targetFaceData["mouth"]["left_corner_mouth_movement"][$i]["val"] = 'left') and
                ($targetFaceData["mouth"]["right_corner_mouth_movement"][$i]["val"] = 'right'))
                $targetFaceData["mouth"]["mouth_length"][$i]["val"] = '+';
            if (($targetFaceData["mouth"]["left_corner_mouth_movement"][$i]["val"] = 'right') and
                ($targetFaceData["mouth"]["right_corner_mouth_movement"][$i]["val"] = 'left'))
                $targetFaceData["mouth"]["mouth_length"][$i]["val"] = '-';
        }

        // изменение ширины рта
        // NORM_POINTS 51 57
        for ($i = 0; $i < count($sourceFaceData['normmask']); $i++) {
            if (isset($facePoints[51]) && isset($sourceFaceData['normmask'][$i][51])) {
                $y = $facePoints[51][1][0] - $sourceFaceData['normmask'][$i][51]['Y'];
                $targetFaceData["mouth"]["mouth_upper_lip_outer_center_movement"][$i]["force"] = $this->getForce(
                    $facePoints[51][1][3], $y
                );
            }

            if (isset($targetFaceData["mouth"]["left_corner_mouth_movement"][$i]))
                if (isset($targetFaceData["mouth"]["mouth_upper_lip_outer_center_movement"][$i]) &&
                    $targetFaceData["mouth"]["mouth_upper_lip_outer_center_movement"][$i]["force"] == 0)
                    $targetFaceData["mouth"]["mouth_upper_lip_outer_center_movement"][$i]["val"] = 'none';
                else
                    if ($y > 0)
                        $targetFaceData["mouth"]["mouth_upper_lip_outer_center_movement"][$i]["val"] = 'up';
                    else
                        $targetFaceData["mouth"]["mouth_upper_lip_outer_center_movement"][$i]["val"] = 'down';
            $deltaYUpperLip = $y;
            if (isset($targetFaceData["mouth"]["mouth_upper_lip_outer_center_movement"][$i]["force"]))
                $force1 = $targetFaceData["mouth"]["mouth_upper_lip_outer_center_movement"][$i]["force"];

            if (isset($facePoints[57]) && isset($sourceFaceData['normmask'][$i][57]))
                $y = $facePoints[57][1][0] - $sourceFaceData['normmask'][$i][57]['Y'];

            $targetFaceData["mouth"]["mouth_lower_lip_outer_center_movement"][$i]["force"] =
                $this->getForce($facePoints[57][1][3], $y);

            if ($targetFaceData["mouth"]["mouth_lower_lip_outer_center_movement"][$i]["force"] == 0)
                $targetFaceData["mouth"]["mouth_lower_lip_outer_center_movement"][$i]["val"] = 'none';
            else
                if ($y < 0)
                    $targetFaceData["mouth"]["mouth_lower_lip_outer_center_movement"][$i]["val"] = 'down';
                else
                    $targetFaceData["mouth"]["mouth_lower_lip_outer_center_movement"][$i]["val"] = 'up';
            $deltaYLowerLip = $y;
            $force2 = $targetFaceData["mouth"]["mouth_lower_lip_outer_center_movement"][$i]["force"];
            $forceAv = round(($force1 + $force2)/2);
            $targetFaceData["mouth"]["mouth_width"][$i]["force"] = $forceAv;
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
            }
        }

        // определение формы рта
        // NORM_POINTS 61 62 63 65 66 67
        for ($i = 0; $i < count($sourceFaceData['normmask']); $i++) {
            if (isset($sourceFaceData['normmask'][$i][67])) {
                $width1 = $sourceFaceData['normmask'][$i][67]['Y'] - $sourceFaceData['normmask'][$i][61]['Y'];
                $width2 = $sourceFaceData['normmask'][$i][66]['Y'] - $sourceFaceData['normmask'][$i][62]['Y'];
                $width3 = $sourceFaceData['normmask'][$i][65]['Y'] - $sourceFaceData['normmask'][$i][63]['Y'];
                $lengthTest = $sourceFaceData['normmask'][$i][65]['X'] - $sourceFaceData['normmask'][$i][67]['X'];

                $force1 = $this->getForce($facePoints[66][1][3], $y);
                $force2 = $this->getForce($facePoints[62][1][3], $y);
                $forceAv = round(($force1 + $force2)/2);
                $targetFaceData["mouth"]["mouth_form"][$i]["force"] = $forceAv;
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
                if(isset($v1[0])) $arrayKeys = array_keys($v1[0]);
                $currentTrendLength = 0;
                for ($i = 1; $i < count($v1); $i++) {
                    if(isset($v1[$i-1][$arrayKeys[1]]))
                        $val0 = $v1[$i-1][$arrayKeys[1]];
                    if(isset($v1[$i]) && isset($arrayKeys[1]))
                        $val1 = $v1[$i][$arrayKeys[1]];
                    if ((isset($v1[$i]["force"]) && $v1[$i]["force"] != 0)//force не рабно нулю
                        and ($val0 == $val1)) { //значение не меняет направление
                        $currentTrendLength++;
                        $v1[$i]["trend"] = $currentTrendLength;
                        $v1[$i]["confidence"] = 1;
                    } else { //the trend is change direction or force = 0
//                        if ($currentTrendLength < $trendLength) {
//                            echo $currentTrendLength . ' ' . $i . '<br>';
                            //clear features of previouse frames
                        $v1[$i]["trend"] = 0;
                        $v1[$i]["confidence"] = 0;
                        for ($i1 = $i; $i1 < ($i - $currentTrendLength); $i1--) {
                                $v1[$i1]["confidence"] = 0;
//                            if (isset($v[$i1][1])) $v[$i1][1] = 'none';
                            }
                            $currentTrendLength = 0;
 //                       }
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
        foreach ($sourceFaceData2 as $k1 => $v1) {
          if(($k1 >= $starFrame)and($k1 <= $endFrame)){
//              echo $k1.' '.$v1[$keyForUpdate].' '.$newValue.' <br>';
              $sourceFaceData2[$k1][$keyForUpdate] = $newValue;
          }
        }
      return $sourceFaceData2;
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
            if ($k === 'eye') {
                foreach ($v as $k1 => $v1) {
                    //eye_width
                    if (($k1 === 'right_eye_width')||($k1 === 'left_eye_width')) {
                        if(strpos($k1,'right')>-1) $prefix = 'right_';
                        elseif ($prefix = 'left_');
                        //---------------------------------------------------------------------------------------
                        for ($i = 1; $i < count($v1); $i++) {
                            //определение закрытие глаза, когда ширина равна 0
                            if (isset($v1[$i]["force"])&&
                                isset($v1[$i]["val"])) {
                               if(($sourceFaceData1[$k][$prefix."eye_width"][$i]["force"] <= 10)&&
                                ($sourceFaceData1[$k][$prefix."eye_width_changing"][$i]["val"] === '-'))
//                                if ($v1[$i]["val"] == 0)
                                    $sourceFaceData1[$k][$prefix."eye_closed"][$i]["val"] = 'yes';
                                else
                                    $sourceFaceData1[$k][$prefix."eye_closed"][$i]["val"] = 'no';
                            }
                        }
                        //---------------------------------------------------------------------------------------
                    }
                    //eye_width_changing
                    if (($k1 === 'right_eye_width_changing')||($k1 === 'left_eye_width_changing')) {
                        if(strpos($k1,'right')>-1) $prefix = 'right_';
                        elseif ($prefix = 'left_');
                        //---------------------------------------------------------------------------------------
                        $eyeStartClosingFrame = '-1';
                        $eyeClosedFrame = '-1';
                        $eyeStartOpeningFrame = '-1';
                        $eyeEndOpeningFrame = '-1';

                        for ($i = 1; $i < count($v1); $i++) {
                            //определение моргания: тренд на уменьшение, закрытие, тренд на увеличение
                            if (isset($v1[$i]["trend"])&&
                                isset($v1[$i]["val"])
                                ) {
                                //если глаз закрыт и было его закрытие, то фиксируем
                                if(($sourceFaceData1[$k][$prefix."eye_closed"][$i]["val"] === 'yes')&&
                                    ($eyeStartClosingFrame !== '-1')&&
                                    ($eyeStartClosingFrame === '-1')){
                                    $eyeClosedFrame = $i;
                                }
                                //если глаз начинает закрываться, то фиксируем
                                if (($v1[$i]["val"] === '-')&&($eyeStartClosingFrame === '-1')){
                                    $eyeStartClosingFrame = $i;
                                    $eyeStartOpeningFrame = '-1';
                                    $eyeClosedFrame = '-1';
                                }
                                //если глаз открывается, но не закрывался, то обнуляем
                                if (($v1[$i]["val"] === '+')&&($eyeClosedFrame === '-1')) {
                                    $eyeStartClosingFrame = '-1';
                                    $eyeStartOpeningFrame = '-1';
                                }
                                //если глаз открывается и закрывался, то фиксируем
                                if (($v1[$i]["val"] === '+')&&($eyeClosedFrame !== '-1')&&
                                    ($eyeStartClosingFrame !== '-1')) {
                                    $eyeStartOpeningFrame = $i;
                                }
                                //если глаз открывается и закрывался, то ожидаем момента, когда он закончит открываться
                                if (($v1[$i]["val"] !== '+')&&($eyeClosedFrame !== '-1')&&
                                    ($eyeStartClosingFrame !== '-1')&&
                                    ($eyeStartOpeningFrame !== '-1')) {
                                    $eyeEndOpeningFrame = $i-1;
                                }
                                //произошло моргание, то фиксируем его
                                if (($eyeEndOpeningFrame !== '-1')&&($eyeClosedFrame !== '-1')&&
                                    ($eyeStartClosingFrame !== '-1')&&
                                    ($eyeStartOpeningFrame !== '-1')) {
                                  //изменить значения свойств в диапазоне от $eyeStartClosingFrame до $eyeEndOpeningFrame
                                    $sourceFaceData1[$k][$prefix."eye_blink"] =
                                     $this->updateValues($sourceFaceData1[$k][$prefix."eye_blink"],'val',
                                       'yes',$eyeStartClosingFrame,$eyeEndOpeningFrame);
                                  //обнулить счетчики
                                    $eyeClosedFrame = '-1';
                                    $eyeStartClosingFrame = '-1';
                                    $eyeStartOpeningFrame = '-1';
                                    $eyeEndOpeningFrame = '-1';
                                }else{
                                    $sourceFaceData1[$k][$prefix."eye_blink"][$i]["val"] = 'no';
                                }

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
        $detectedFeaturesWithTrends = $this->detectTrends($detectedFeatures,5);
        $detectedFeaturesWithTrends = $this->detectAdditionalFeatures($detectedFeaturesWithTrends);

//        $detectedFeaturesWithTrends['eye']["right_eye_blink"] = $this->updateValues(
//            $detectedFeaturesWithTrends['eye']["right_eye_blink"],'val',
//            'yes','5','10');
        return $detectedFeaturesWithTrends;
    }
}