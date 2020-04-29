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
                        (($facialCharacteristics[$i][$key] - $nat) / $deltaForMinus));

                } elseif ($facialCharacteristics[$i][$key] > $nat) {
                    // Увеличение ширины
                    $facialCharacteristics[$i]["widthChange"] = "+";
                    $targetFaceData[$i]["force"] = $this->getForce(
                        $deltaForPlus, abs($facialCharacteristics[$i][$key]- $nat));
                    $targetFaceData[$i]["widthChange"] = "+";


                    $facialCharacteristics[$i]["widthChangeForce"] = round(
                        (($facialCharacteristics[$i][$key] - $nat) / $deltaForPlus));
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

        return round($avr / $facialCharacteristicsNumber);
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
        $max = 0;
        for ($i = 0; $i < $facialCharacteristicsNumber; $i++)
            if (isset($facialCharacteristics[$i][$pointNum]) && isset($facialCharacteristics[$i][$pointNum][$key])
                && isset($facialCharacteristics[$i][$point1][$key])
                && isset($facialCharacteristics[$i][$point2][$key])) {
                $mid6167 = round(($facialCharacteristics[$i][$point2][$key] -
                            $facialCharacteristics[$i][$point1][$key])/2) +
                            $facialCharacteristics[$i][$point1][$key];
                $relPointValue = $facialCharacteristics[$i][$pointNum][$key] - $mid6167;
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
        $min = 0;
        for ($i = 0; $i < $facialCharacteristicsNumber; $i++)
            if (isset($facialCharacteristics[$i][$pointNum]) && isset($facialCharacteristics[$i][$pointNum][$key])
                && isset($facialCharacteristics[$i][$point1][$key])
                && isset($facialCharacteristics[$i][$point2][$key])) {
                $mid6167 = round(($facialCharacteristics[$i][$point2][$key] -
                            $facialCharacteristics[$i][$point1][$key])/2) +
                    $facialCharacteristics[$i][$point1][$key];
                $relPointValue = $facialCharacteristics[$i][$pointNum][$key] - $mid6167;
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
        $facialCharacteristicsNumber = 0;
        if (is_array($facialCharacteristics))
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
    public function detectEyeFeatures($sourceFaceData, $facePart)
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
            $midNY3942 = round(($sourceFaceData[0][42]['Y'] - $sourceFaceData[0][39]['Y'])/2) +
                $sourceFaceData[0][39]['Y'];
            $midNX3942 = round(($sourceFaceData[0][42]['X'] - $sourceFaceData[0][39]['X'])/2) +
                $sourceFaceData[0][39]['X'];

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

            $leftEyeWidthMaxByCircle = $xN39 - $xN36;
            $leftEyeWidthScaleByCircle = $leftEyeWidthMaxByCircle - $leftEyeWidthN;
            $rightEyeWidthMaxByCircle = $xN45 - $xN42;
            $rightEyeWidthScaleByCircle = $rightEyeWidthMaxByCircle - $rightEyeWidthN;

//            $maxY37 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 37, "Y");
//            $minY37 = $this->getFaceDataMinForKeyV2($sourceFaceData, 37, "Y");
            $maxY37 = $this->getFaceDataMaxOnPoints($sourceFaceData, 37, "Y",39,42);
            $minY37 = $this->getFaceDataMinOnPoints($sourceFaceData, 37, "Y",39,42);
            $scaleY37 = $maxY37 - $minY37;
//            $maxY43 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 43, "Y");
//            $minY43 = $this->getFaceDataMinForKeyV2($sourceFaceData, 43, "Y");
            $maxY43 = $this->getFaceDataMaxOnPoints($sourceFaceData, 43, "Y",39,42);
            $minY43 = $this->getFaceDataMinOnPoints($sourceFaceData, 43, "Y",39,42);
            $scaleY43 = $maxY43 - $minY43;
//            $maxY41 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 41, "Y");
//            $minY41 = $this->getFaceDataMinForKeyV2($sourceFaceData, 41, "Y");
            $maxY41 = $this->getFaceDataMaxOnPoints($sourceFaceData, 41, "Y",39,42);
            $minY41 = $this->getFaceDataMinOnPoints($sourceFaceData, 41, "Y",39,42);
            $scaleY41 = $maxY41 - $minY41;
            $maxLeftEyeWidth = $maxY41 - $minY37;
//            $maxY47 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 47, "Y");
//            $minY47 = $this->getFaceDataMinForKeyV2($sourceFaceData, 47, "Y");
            $maxY47 = $this->getFaceDataMaxOnPoints($sourceFaceData, 47, "Y",39,42);
            $minY47 = $this->getFaceDataMinOnPoints($sourceFaceData, 47, "Y",39,42);
            $scaleY47 = $maxY47 - $minY47;
            $maxRightEyeWidth = $maxY47 - $minY43;
            //38 и 40 для левого глаза, для правого - 44 и 46
//            $maxY38 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 38, "Y");
//            $minY38 = $this->getFaceDataMinForKeyV2($sourceFaceData, 38, "Y");
            $maxY38 = $this->getFaceDataMaxOnPoints($sourceFaceData, 38, "Y",39,42);
            $minY38 = $this->getFaceDataMinOnPoints($sourceFaceData, 38, "Y",39,42);

            $scaleY38 = $maxY38 - $minY38;
//            $maxY44 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 44, "Y");
//            $minY44 = $this->getFaceDataMinForKeyV2($sourceFaceData, 44, "Y");
            $maxY44 = $this->getFaceDataMaxOnPoints($sourceFaceData, 44, "Y",39,42);
            $minY44 = $this->getFaceDataMinOnPoints($sourceFaceData, 44, "Y",39,42);
            $scaleY44 = $maxY44 - $minY44;
//            $maxY40 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 40, "Y");
//            $minY40 = $this->getFaceDataMinForKeyV2($sourceFaceData, 40, "Y");
            $maxY40 = $this->getFaceDataMaxOnPoints($sourceFaceData, 40, "Y",39,42);
            $minY40 = $this->getFaceDataMinOnPoints($sourceFaceData, 40, "Y",39,42);
            $scaleY40 = $maxY40 - $minY40;
            $maxLeftEyeWidth2 = $maxY40 - $minY38;
//            $maxY46 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 46, "Y");
//            $minY46 = $this->getFaceDataMinForKeyV2($sourceFaceData, 46, "Y");
            $maxY46 = $this->getFaceDataMaxOnPoints($sourceFaceData, 46, "Y",39,42);
            $minY46 = $this->getFaceDataMinOnPoints($sourceFaceData, 46, "Y",39,42);
            $scaleY46 = $maxY46 - $minY46;
            $maxRightEyeWidth2 = $maxY46 - $minY44;

//            $maxX39 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 39, "X");
//            $minX39 = $this->getFaceDataMinForKeyV2($sourceFaceData, 39, "X");
            $maxX39 = $this->getFaceDataMaxOnPoints($sourceFaceData, 39, "X",39,42);
            $minX39 = $this->getFaceDataMinOnPoints($sourceFaceData, 39, "X",39,42);
            $scaleX39 = $maxX39 - $minX39;
//            $maxX42 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 42, "X");
//            $minX42 = $this->getFaceDataMinForKeyV2($sourceFaceData, 42, "X");
            $maxX42 = $this->getFaceDataMaxOnPoints($sourceFaceData, 42, "X",39,42);
            $minX42 = $this->getFaceDataMinOnPoints($sourceFaceData, 42, "X",39,42);
            $scaleX42 = $maxX42 - $minX42;
 //           $maxY39 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 39, "Y");
//            $minY39 = $this->getFaceDataMinForKeyV2($sourceFaceData, 39, "Y");
            $maxY39 = $this->getFaceDataMaxOnPoints($sourceFaceData, 39, "Y",39,42);
            $minY39 = $this->getFaceDataMinOnPoints($sourceFaceData, 39, "Y",39,42);
            $scaleY39 = $maxY39 - $minY39;
//            $maxY42 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 42, "Y");
//            $minY42 = $this->getFaceDataMinForKeyV2($sourceFaceData, 42, "Y");
            $maxY42 = $this->getFaceDataMaxOnPoints($sourceFaceData, 42, "Y",39,42);
            $minY42 = $this->getFaceDataMinOnPoints($sourceFaceData, 42, "Y",39,42);
            $scaleY42 = $maxY42 - $minY42;
//            $maxY36 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 36, "Y");
//            $minY36 = $this->getFaceDataMinForKeyV2($sourceFaceData, 36, "Y");
            $maxY36 = $this->getFaceDataMaxOnPoints($sourceFaceData, 36, "Y",39,42);
            $minY36 = $this->getFaceDataMinOnPoints($sourceFaceData, 36, "Y",39,42);
            $scaleY36 = $maxY36 - $minY36;
//            $maxY45 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 45, "Y");
//            $minY45 = $this->getFaceDataMinForKeyV2($sourceFaceData, 45, "Y");
            $maxY45 = $this->getFaceDataMaxOnPoints($sourceFaceData, 45, "Y",39,42);
            $minY45 = $this->getFaceDataMinOnPoints($sourceFaceData, 45, "Y",39,42);
            $scaleY45 = $maxY45 - $minY45;

            for ($i = 0; $i < count($sourceFaceData); $i++) {
                if (isset($sourceFaceData[$i][42]) && isset($sourceFaceData[$i][39])){
                $midY3942 = round(($sourceFaceData[$i][42]['Y'] - $sourceFaceData[$i][39]['Y'])/2) +
                    $sourceFaceData[$i][39]['Y'];
                $midX3942 = round(($sourceFaceData[$i][42]['X'] - $sourceFaceData[$i][39]['X'])/2) +
                    $sourceFaceData[$i][39]['X'];
                }
                //----------------------------------------------------------------------------------------
                //Верхнее веко, движение верхнего века (вверх, вниз)
                //left_eye_upper_eyelid_movement
                if (isset($sourceFaceData[$i][38]))
                    $leftEyeUpperEyelidH = $sourceFaceData[$i][38]['Y'] - $yN38 - $midY3942;
                if (isset($sourceFaceData[$i][43]))
                    $rightEyeUpperEyelidH = $sourceFaceData[$i][43]['Y'] - $yN43 - $midY3942;

                $targetFaceData[$facePart]["left_eye_upper_eyelid_movement"][$i]["force"] = $this->getForce(
//                    $scaleY38, abs($leftEyeUpperEyelidH)
                    round($leftEyeWidthMaxByCircle/2 - $yN39 - $yN38), abs($leftEyeUpperEyelidH)
                );
                $targetFaceData[$facePart]["right_eye_upper_eyelid_movement"][$i]["force"] = $this->getForce(
//                    $scaleY43, abs($rightEyeUpperEyelidH)
                    round($rightEyeWidthMaxByCircle/2 - $yN42 - $yN43), abs($rightEyeUpperEyelidH)
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
                $leftEyeInnerCornerForce = $this->getForce($scaleX39, abs($leftEyeInnerCorner));
                $rightEyeInnerCornerForce = $this->getForce($scaleX42, abs($rightEyeInnerCorner));

                $targetFaceData[$facePart]["left_eye_lower_eyelid_movement_y"][$i]["force"] = $this->getForce(
//                    $scaleY40, abs($leftEyeLowerEyelidH)
                    round($leftEyeWidthMaxByCircle/2 - $yN40 - $yN39), abs($leftEyeLowerEyelidH)
                );
                $targetFaceData[$facePart]["right_eye_lower_eyelid_movement_y"][$i]["force"] = $this->getForce(
//                    $scaleY47, abs($rightEyeLowerEyelidH)
                     round($rightEyeWidthMaxByCircle/2 - $yN47 - $yN42), abs($rightEyeLowerEyelidH)
                );

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

                $targetFaceData[$facePart]["right_eye_lower_eyelid_movement_d"][$i]["force"] =
                    round(($targetFaceData[$facePart]["right_eye_lower_eyelid_movement_x"][$i]["force"]+
                        $targetFaceData[$facePart]["right_eye_lower_eyelid_movement_y"][$i]["force"])/2);
                $targetFaceData[$facePart]["right_eye_lower_eyelid_movement_d"][$i]["val"] =
                    $targetFaceData[$facePart]["right_eye_lower_eyelid_movement_x"][$i]["val"].' and '.
                    $targetFaceData[$facePart]["right_eye_lower_eyelid_movement_y"][$i]["val"];

                $targetFaceData[$facePart]["left_eye_lower_eyelid_movement_d"][$i]["force"] =
                    round(($targetFaceData[$facePart]["left_eye_lower_eyelid_movement_x"][$i]["force"]+
                            $targetFaceData[$facePart]["left_eye_lower_eyelid_movement_y"][$i]["force"])/2);
                $targetFaceData[$facePart]["left_eye_lower_eyelid_movement_d"][$i]["val"] =
                    $targetFaceData[$facePart]["left_eye_lower_eyelid_movement_x"][$i]["val"].' and '.
                    $targetFaceData[$facePart]["left_eye_lower_eyelid_movement_y"][$i]["val"];
                //------------------------------------------------------------------------------------------------
                //width, расстояние между 37 и 41 для левого глаза, для правого - 43 и 47
                if (isset($sourceFaceData[$i][37]) &&
                    isset($sourceFaceData[$i][41]) &&
                    isset($sourceFaceData[$i][43]) &&
                    isset($sourceFaceData[$i][47])) {
                    $leftEyeWidth = $sourceFaceData[$i][41]['Y'] - $sourceFaceData[$i][37]['Y'];
                    $rightEyeWidth = $sourceFaceData[$i][47]['Y'] - $sourceFaceData[$i][43]['Y'];

//                    $targetFaceData["eye"]["left_eye_width"][$i]["force"] = $this->getForce(
//                        $maxLeftEyeWidth, abs($leftEyeWidth - $leftEyeWidthN));
                    $targetFaceData[$facePart]["left_eye_width"][$i]["force"] = $this->getForce(
                        $leftEyeWidthScaleByCircle, abs($leftEyeWidth - $leftEyeWidthN));
                    $targetFaceData[$facePart]["left_eye_width"][$i]["val"] = $leftEyeWidth;

//                    $targetFaceData["eye"]["right_eye_width"][$i]["force"] = $this->getForce(
//                        $maxRightEyeWidth, abs($rightEyeWidth - $rightEyeWidthN));
                    $targetFaceData[$facePart]["right_eye_width"][$i]["force"] = $this->getForce(
                        $rightEyeWidthScaleByCircle, abs($rightEyeWidth - $rightEyeWidthN));
                    $targetFaceData[$facePart]["right_eye_width"][$i]["val"] = $rightEyeWidth;

                    //альтернативно: width, расстояние между 38 и 40 для левого глаза, для правого - 44 и 46
                    $leftEyeWidth2 = $sourceFaceData[$i][40]['Y'] - $sourceFaceData[$i][38]['Y'];
                    $rightEyeWidth2 = $sourceFaceData[$i][46]['Y'] - $sourceFaceData[$i][44]['Y'];
 //                   $targetFaceData["eye"]["left_eye_width2"][$i]["force"] = $this->getForce(
//                        $maxLeftEyeWidth2, abs($leftEyeWidth2 - $leftEyeWidthN2));
                    $targetFaceData[$facePart]["left_eye_width2"][$i]["force"] = $this->getForce(
                        ($leftEyeWidthMaxByCircle - $leftEyeWidthN2), abs($leftEyeWidth2 - $leftEyeWidthN2));

                    $targetFaceData[$facePart]["left_eye_width2"][$i]["val"] = $leftEyeWidth2;
//                    $targetFaceData["eye"]["right_eye_width2"][$i]["force"] = $this->getForce(
//                        $maxRightEyeWidth2, abs($rightEyeWidth2 - $rightEyeWidthN2));
                    $targetFaceData[$facePart]["right_eye_width2"][$i]["force"] = $this->getForce(
                        ($rightEyeWidthMaxByCircle - $rightEyeWidthN2), abs($rightEyeWidth2 - $rightEyeWidthN2));
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
                    $scaleY36, abs($leftEyeOuterCornerH));
                $targetFaceData[$facePart]["right_eye_outer_movement"][$i]["force"] = $this->getForce(
                    $scaleY45, abs($rightEyeOuterCornerH));
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
                //------------------------------------------------------------------------------------------------
                //Внутренний уголок глаза, движение внутреннего уголка глаза (вверх, вниз)
                if (isset($sourceFaceData[$i][39]))
                    $leftEyeInnerCornerH = $sourceFaceData[$i][39]['Y'] - $yN39 - $midY3942;
                if (isset($sourceFaceData[$i][42]))
                    $rightEyeInnerCornerH = $sourceFaceData[$i][42]['Y'] - $yN42 - $midY3942;
                $targetFaceData[$facePart]["left_eye_inner_movement"][$i]["force"] = $this->getForce(
                    $scaleY39, abs($leftEyeInnerCornerH));
                $targetFaceData[$facePart]["right_eye_inner_movement"][$i]["force"] = $this->getForce(
                    $scaleY42, abs($rightEyeInnerCornerH));
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
                //------------------------------------------------------------------------------------------------
            }
            return $targetFaceData[$facePart];
        } else return false;
    }

    public function addPointsToResults($pointsName,$sourceFaceData,$resFaceData,$info)
    {
        if (isset($sourceFaceData['normmask'])) {
            $resFaceData['MASK_NAMES'][] = $pointsName . '(' . $info . ')';
            for ($i = 0; $i < count($sourceFaceData['normmask']); $i++) {
                if (isset($sourceFaceData['normmask'][$i]))
                    foreach ($sourceFaceData['normmask'][$i] as $k1 => $v1) { //points
                        $resFaceData['frame_#' . $i][$pointsName . '(' . $info . ')'][$k1][0] = $sourceFaceData['normmask'][$i][$k1]['X'];
                        $resFaceData['frame_#' . $i][$pointsName . '(' . $info . ')'][$k1][1] = $sourceFaceData['normmask'][$i][$k1]['Y'];
                    }
            }
        }
        return $resFaceData;
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
                            $FaceData_['normmask'][$i][$k1]['X'] = $v1[0];
                            $FaceData_['normmask'][$i][$k1]['Y'] = $v1[1];
                        }
                    //norm irises processing
                    if (isset($v['NORM_IRISES']))
                        foreach ($v['NORM_IRISES'] as $k1 => $v1) {
                            $FaceData_['normirises'][$i][$k1]['X'] = $v1[0];
                            $FaceData_['normirises'][$i][$k1]['Y'] = $v1[1];
                        }
                    //points processing
                    if (isset($v['POINTS']))
                        foreach ($v['POINTS'] as $k1 => $v1) {
                            $FaceData_['points'][$i][$k1]['X'] = $v1[0];
                            $FaceData_['points'][$i][$k1]['Y'] = $v1[1];
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
    public function detectNoseFeatures($sourceFaceData, $facePart)
    {
        //анализируемые точки низа носа
        // 31 (left_nose_wing),
        // 35 (right_nose_wing),
        // получение нормированного значения по кадру 0
        //относительно центра между внутренними уголками глаз, точки 39 и 42

        if (isset($sourceFaceData[0][31])
            && isset($sourceFaceData[0][35])
            && isset($sourceFaceData[0][39])
            && isset($sourceFaceData[0][42])
        ) {
            $midNY3942 = round(($sourceFaceData[0][42]['Y'] - $sourceFaceData[0][39]['Y'])/2) +
                $sourceFaceData[0][39]['Y'];
            $midNX3942 = round(($sourceFaceData[0][42]['X'] - $sourceFaceData[0][39]['X'])/2) +
                $sourceFaceData[0][39]['X'];

            $yN31 = $sourceFaceData[0][31]['Y'] - $midNY3942;
            $yN35 = $sourceFaceData[0][35]['Y'] - $midNY3942;
//            $maxY31 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 31, "Y");
//            $minY31 = $this->getFaceDataMinForKeyV2($sourceFaceData, 31, "Y");
            $maxY31 = $this->getFaceDataMaxOnPoints($sourceFaceData, 31, "Y",39,42);
            $minY31 = $this->getFaceDataMinOnPoints($sourceFaceData, 31, "Y",39,42);
            $scaleY31 = $maxY31 - $minY31;
 //           $maxY35 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 35, "Y");
//            $minY35 = $this->getFaceDataMinForKeyV2($sourceFaceData, 35, "Y");
            $maxY35 = $this->getFaceDataMaxOnPoints($sourceFaceData, 35, "Y",39,42);
            $minY35 = $this->getFaceDataMinOnPoints($sourceFaceData, 35, "Y",39,42);
            $scaleY35 = $maxY35 - $minY35;


            for ($i = 0; $i < count($sourceFaceData); $i++) {
                if (isset($sourceFaceData[$i][42]) && isset($sourceFaceData[$i][39])){
                    $midY3942 = round(($sourceFaceData[$i][42]['Y'] - $sourceFaceData[$i][39]['Y'])/2) +
                        $sourceFaceData[$i][39]['Y'];
                    $midX3942 = round(($sourceFaceData[$i][42]['X'] - $sourceFaceData[$i][39]['X'])/2) +
                        $sourceFaceData[$i][39]['X'];
                }
                if (isset($sourceFaceData[$i][31]) && $sourceFaceData[$i][35]) {
                    $leftNoseWingMovement = $sourceFaceData[$i][31]['Y'] - $yN31 - $midY3942;
                    $rightNoseWingMovement = $sourceFaceData[$i][35]['Y'] - $yN35 - $midY3942;

                    $leftNoseWingMovementForce = $this->getForce($scaleY31, abs($leftNoseWingMovement));
                    $rightNoseWingMovementForce = $this->getForce($scaleY35, abs($rightNoseWingMovement));
                    $noseWingsMovementForce = round(($leftNoseWingMovementForce + $rightNoseWingMovementForce) / 2); //среднее значение
                }
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
     * Обнаружение признаков подбородка.
     *
     * @param $sourceFaceData - входной массив с лицевыми точками (landmarks)
     * @return array - выходной массив с обработанным массивом для лба
     */
    public function detectChinFeatures($sourceFaceData, $facePart){
        //анализируемые точки:
        // 8 (нижняя центральная точка подбородка),
        //относительно центральной точки рта? определяемой по точкам 61 и 67

        if ((isset($sourceFaceData[0][8]))
            && (isset($sourceFaceData[0][61])) && (isset($sourceFaceData[0][67]))
        ) {
            $midNY6167 = round(($sourceFaceData[0][67]['Y'] - $sourceFaceData[0][61]['Y'])/2) +
                $sourceFaceData[0][61]['Y'];
            $yN8 = $sourceFaceData[0][8]['Y'] - $midNY6167;
            $maxY8 = $this->getFaceDataMaxOnPoints($sourceFaceData, 8,"Y",61,67);
            $minY8 = $this->getFaceDataMinOnPoints($sourceFaceData,8, "Y",61,67);
            $scaleY8 = $maxY8 - $minY8;

            for ($i = 0; $i < count($sourceFaceData); $i++) {
                if ((isset($sourceFaceData[$i][8]))
                    && (isset($sourceFaceData[$i][61])) && (isset($sourceFaceData[$i][67]))){
                    $midY6167 = round(($sourceFaceData[$i][67]['Y'] - $sourceFaceData[$i][61]['Y'])/2) +
                        $sourceFaceData[$i][61]['Y'];

                    $chinMovement = $sourceFaceData[$i][8]['Y'] - $yN8 - $midY6167;
                    $chinMovementForce = $this->getForce($scaleY8, abs($chinMovement));

                }
                $targetFaceData[$facePart]["chin_movement"][$i]["force"] = $chinMovementForce;
                if ($chinMovement < 0) $targetFaceData[$facePart]["chin_movement"][$i]["val"] = 'up';
                if ($chinMovement > 0) $targetFaceData[$facePart]["chin_movement"][$i]["val"] = 'down';
                if ($chinMovement == 0) $targetFaceData[$facePart]["chin_movement"][$i]["val"] = 'none';
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
    public function detectBrowFeatures($sourceFaceData, $facePart){
        //анализируемые точки:
        // 19 (left_eyebrow_center),
        // 24 (right_eyebrow_center),

        //относительно точки 39 (уголок глаза)
        //изменение ширины лба по движению бровей
        // получение нормированного значения по кадру 0

        if (isset($sourceFaceData[0][19])
            && isset($sourceFaceData[0][24])
            && isset($sourceFaceData[0][39])
        ) {
            $yN19 = $sourceFaceData[0][39]['Y'] - $sourceFaceData[0][19]['Y'];
            $yN24 = $sourceFaceData[0][39]['Y'] - $sourceFaceData[0][24]['Y'];
//            $maxY19 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 19,"Y");
//            $minY19 = $this->getFaceDataMinForKeyV2($sourceFaceData,19, "Y");
            $maxY19 = $this->getFaceDataMaxOnPoints($sourceFaceData, 19,"Y",39,42);
            $minY19 = $this->getFaceDataMinOnPoints($sourceFaceData,19, "Y",39,42);
            $scaleY19 = $maxY19 - $minY19;
//            $maxY24 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 24,"Y");
//            $minY24 = $this->getFaceDataMinForKeyV2($sourceFaceData,24, "Y");
            $maxY24 = $this->getFaceDataMaxOnPoints($sourceFaceData, 24,"Y",39,42);
            $minY24 = $this->getFaceDataMinOnPoints($sourceFaceData,24, "Y",39,42);
            $scaleY24 = $maxY24 - $minY24;


        for ($i = 0; $i < count($sourceFaceData); $i++) {
            if (isset($sourceFaceData[$i][19]) && $sourceFaceData[$i][24] && $sourceFaceData[$i][39]){
                $leftEyebrowMovement = $sourceFaceData[$i][39]['Y'] - $sourceFaceData[$i][19]['Y'] - $yN19;
                $rightEyebrowMovement = $sourceFaceData[$i][39]['Y'] - $sourceFaceData[$i][24]['Y'] - $yN24;

                $leftEyebrowMovementForce = $this->getForce($scaleY19, abs($leftEyebrowMovement));
                $rightEyebrowMovementForce = $this->getForce($scaleY24, abs($rightEyebrowMovement));
                $eyebrowMovementForce = round(($leftEyebrowMovementForce+$rightEyebrowMovementForce)/2); //среднее значение
            }
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
     * Обнаружение признаков бровей.
     *
     * @param $sourceFaceData - входной массив с лицевыми точками (landmarks)
     * @return array - выходной массив с обработанным массивом для лба
     */
    public function detectEyeBrowFeatures($sourceFaceData, $facePart){
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
            && isset($sourceFaceData[0][39])
            && isset($sourceFaceData[0][42])
        ) {
            $midNY3942 = round(($sourceFaceData[0][42]['Y'] - $sourceFaceData[0][39]['Y'])/2) +
                $sourceFaceData[0][39]['Y'];
            $midNX3942 = round(($sourceFaceData[0][42]['X'] - $sourceFaceData[0][39]['X'])/2) +
                $sourceFaceData[0][39]['X'];

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
            $yN24 = $sourceFaceData[0][24]['Y'] - $midNY3942;
            $xN24 = $sourceFaceData[0][24]['X'] - $midNX3942;

//            $maxY17 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 17, "Y");
//            $minY17 = $this->getFaceDataMinForKeyV2($sourceFaceData, 17, "Y");
            $maxY17 = $this->getFaceDataMaxOnPoints($sourceFaceData, 17, "Y",39,42);
            $minY17 = $this->getFaceDataMinOnPoints($sourceFaceData, 17, "Y",39,42);
//            $maxX17 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 17, "X");
//            $minX17 = $this->getFaceDataMinForKeyV2($sourceFaceData, 17, "X");
            $maxX17 = $this->getFaceDataMaxOnPoints($sourceFaceData, 17, "X",39,42);
            $minX17 = $this->getFaceDataMinOnPoints($sourceFaceData, 17, "X",39,42);
            $scaleY17 = $maxY17 - $minY17;
            $scaleX17 = $maxX17 - $minX17;

//            $maxY21 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 21, "Y");
//            $minY21 = $this->getFaceDataMinForKeyV2($sourceFaceData, 21, "Y");
            $maxY21 = $this->getFaceDataMaxOnPoints($sourceFaceData, 21, "Y",39,42);
            $minY21 = $this->getFaceDataMinOnPoints($sourceFaceData, 21, "Y",39,42);
//            $maxX21 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 21, "X");
//            $minX21 = $this->getFaceDataMinForKeyV2($sourceFaceData, 21, "X");
            $maxX21 = $this->getFaceDataMaxOnPoints($sourceFaceData, 21, "X",39,42);
            $minX21 = $this->getFaceDataMinOnPoints($sourceFaceData, 21, "X",39,42);
            $scaleY21 = $maxY21 - $minY21;
            $scaleX21 = $maxX21 - $minX21;

//            $maxY22 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 22, "Y");
//            $minY22 = $this->getFaceDataMinForKeyV2($sourceFaceData, 22, "Y");
            $maxY22 = $this->getFaceDataMaxOnPoints($sourceFaceData, 22, "Y",39,42);
            $minY22 = $this->getFaceDataMinOnPoints($sourceFaceData, 22, "Y",39,42);
//            $maxX22 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 22, "X");
//            $minX22 = $this->getFaceDataMinForKeyV2($sourceFaceData, 22, "X");
            $maxX22 = $this->getFaceDataMaxOnPoints($sourceFaceData, 22, "X",39,42);
            $minX22 = $this->getFaceDataMinOnPoints($sourceFaceData, 22, "X",39,42);
            $scaleY22 = $maxY22 - $minY22;
            $scaleX22 = $maxX22 - $minX22;

//            $maxY26 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 26, "Y");
//            $minY26 = $this->getFaceDataMinForKeyV2($sourceFaceData, 26, "Y");
            $maxY26 = $this->getFaceDataMaxOnPoints($sourceFaceData, 26, "Y",39,42);
            $minY26 = $this->getFaceDataMinOnPoints($sourceFaceData, 26, "Y",39,42);
//            $maxX26 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 26, "X");
//            $minX26 = $this->getFaceDataMinForKeyV2($sourceFaceData, 26, "X");
            $maxX26 = $this->getFaceDataMaxOnPoints($sourceFaceData, 26, "X",39,42);
            $minX26 = $this->getFaceDataMinOnPoints($sourceFaceData, 26, "X",39,42);
            $scaleY26 = $maxY26 - $minY26;
            $scaleX26 = $maxX26 - $minX26;

//            $maxY19 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 19, "Y");
//            $minY19 = $this->getFaceDataMinForKeyV2($sourceFaceData, 19, "Y");
            $maxY19 = $this->getFaceDataMaxOnPoints($sourceFaceData, 19, "Y",39,42);
            $minY19 = $this->getFaceDataMinOnPoints($sourceFaceData, 19, "Y",39,42);
//            $maxX19 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 19, "X");
//            $minX19 = $this->getFaceDataMinForKeyV2($sourceFaceData, 19, "X");
            $maxX19 = $this->getFaceDataMaxOnPoints($sourceFaceData, 19, "X",39,42);
            $minX19 = $this->getFaceDataMinOnPoints($sourceFaceData, 19, "X",39,42);
            $scaleY19 = $maxY19 - $minY19;
            $scaleX19 = $maxX19 - $minX19;

//            $maxY24 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 24, "Y");
//            $minY24 = $this->getFaceDataMinForKeyV2($sourceFaceData, 24, "Y");
            $maxY24 = $this->getFaceDataMaxOnPoints($sourceFaceData, 24, "Y",39,42);
            $minY24 = $this->getFaceDataMinOnPoints($sourceFaceData, 24, "Y",39,42);
//            $maxX24 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 24, "X");
//            $minX24 = $this->getFaceDataMinForKeyV2($sourceFaceData, 24, "X");
            $maxX24 = $this->getFaceDataMaxOnPoints($sourceFaceData, 24, "X",39,42);
            $minX24 = $this->getFaceDataMinOnPoints($sourceFaceData, 24, "X",39,42);
            $scaleY24 = $maxY24 - $minY24;
            $scaleX24 = $maxX24 - $minX24;

            for ($i = 0; $i < count($sourceFaceData); $i++) {
                if (isset($sourceFaceData[$i][42]) && isset($sourceFaceData[$i][39])){
                    $midY3942 = round(($sourceFaceData[$i][42]['Y'] - $sourceFaceData[$i][39]['Y'])/2) +
                        $sourceFaceData[$i][39]['Y'];
                    $midX3942 = round(($sourceFaceData[$i][42]['X'] - $sourceFaceData[$i][39]['X'])/2) +
                        $sourceFaceData[$i][39]['X'];
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

                $leftEyebrowXMovForce = $this->getForce($scaleX21, abs($leftEyebrowMovementXIn));
                $leftEyebrowYMovForce = $this->getForce($scaleY21, abs($leftEyebrowMovementHIn));
                $targetFaceData[$facePart]["left_eyebrow_inner_movement_x"][$i]["force"] =
                   $leftEyebrowXMovForce;
                $targetFaceData[$facePart]["left_eyebrow_inner_movement_y"][$i]["force"] =
                    $leftEyebrowYMovForce;

                $targetFaceData[$facePart]["left_eyebrow_outer_movement"][$i]["force"] = $this->getForce(
                    $scaleY17, abs($leftEyebrowMovementHOut));

                $rightEyebrowXMovForce = $this->getForce($scaleX22, abs($rightEyebrowMovementXIn));
                $rightEyebrowYMovForce = $this->getForce($scaleY22, abs($rightEyebrowMovementHIn));
                $targetFaceData[$facePart]["right_eyebrow_inner_movement_x"][$i]["force"] =
                    $rightEyebrowXMovForce;
                $targetFaceData[$facePart]["right_eyebrow_inner_movement_y"][$i]["force"] =
                    $rightEyebrowYMovForce;

                $targetFaceData[$facePart]["right_eyebrow_outer_movement"][$i]["force"] = $this->getForce(
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
 //               if ($yMov == 'none') $yMov = '';
//                if (($xMov != '') && ($yMov != '')) $yMov = ' and ' . $yMov;
//                if (($xMov == '') && ($yMov == '')) $yMov = 'none';
                $targetFaceData[$facePart]["right_eyebrow_inner_movement_y"][$i]["val"] = $yMov;
                $targetFaceData[$facePart]["right_eyebrow_inner_movement_x"][$i]["val"] = $xMov;

                if ($rightEyebrowMovementHOut > 0) $targetFaceData[$facePart]["right_eyebrow_outer_movement"][$i]["val"] = 'down';
                if ($rightEyebrowMovementHOut < 0) $targetFaceData[$facePart]["right_eyebrow_outer_movement"][$i]["val"] = 'up';
                if ($rightEyebrowMovementHOut == 0) {
                    $targetFaceData[$facePart]["right_eyebrow_outer_movement"][$i]["force"] = 0;
                    $targetFaceData[$facePart]["right_eyebrow_outer_movement"][$i]["val"] = 'none';
                }

                //определяем движение брови по движению верхних точек бровей
                // 19 - левая бровь, 24 - правая бровь
                if (isset($sourceFaceData[$i])) {
                    $rightEyebrowMovementY = $sourceFaceData[$i][24]['Y'] - $yN24 - $midY3942;
                    $rightEyebrowMovementX = $sourceFaceData[$i][24]['X'] - $xN24 - $midX3942;
                    $leftEyebrowMovementY = $sourceFaceData[$i][19]['Y'] - $yN19 - $midY3942;
                    $leftEyebrowMovementX = $sourceFaceData[$i][19]['X'] - $xN19 - $midX3942;
                } else {
                    $rightEyebrowMovementY = 0;
                    $rightEyebrowMovementX = 0;
                    $leftEyebrowMovementY = 0;
                    $leftEyebrowMovementX = 0;
                }
                $rightEyebrowXMovForce = $this->getForce($scaleX24, abs($rightEyebrowMovementX));
                $rightEyebrowYMovForce = $this->getForce($scaleY24, abs($rightEyebrowMovementY));
//                $targetFaceData["eyebrow"]["right_eyebrow_movement"][$i]["force"] =
//                    round(($rightEyebrowXMovForce + $rightEyebrowYMovForce) / 2);
                $targetFaceData[$facePart]["right_eyebrow_movement_x"][$i]["force"] =
                    $rightEyebrowXMovForce;
                $targetFaceData[$facePart]["right_eyebrow_movement_y"][$i]["force"] =
                    $rightEyebrowYMovForce;

                $leftEyebrowXMovForce = $this->getForce($scaleX19, abs($leftEyebrowMovementX));
                $leftEyebrowYMovForce = $this->getForce($scaleY19, abs($leftEyebrowMovementY));
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
//                if ($yMov == 'none') $yMov = '';
//                if (($xMov != '') && ($yMov != '')) $yMov = ' and ' . $yMov;
//                if (($xMov == '') && ($yMov == '')) $yMov = 'none';
//                $targetFaceData["eyebrow"]["left_eyebrow_movement"][$i]["val"] = $xMov.$yMov;
                $targetFaceData[$facePart]["left_eyebrow_movement_x"][$i]["val"] = $xMov;
                $targetFaceData[$facePart]["left_eyebrow_movement_y"][$i]["val"] = $yMov;

                $xMov = 'none';
                if ($rightEyebrowMovementY > 0) $yMov = 'down';
                if ($rightEyebrowMovementY < 0) $yMov = 'up';
                if ($rightEyebrowMovementY == 0) $yMov = 'none';
                if ($rightEyebrowMovementX < 0) $xMov = 'to center';
 //               if ($yMov == 'none') $yMov = '';
//                if (($xMov != '') && ($yMov != '')) $yMov = ' and ' . $yMov;
//                if (($xMov == '') && ($yMov == '')) $yMov = 'none';
//                $targetFaceData["eyebrow"]["right_eyebrow_movement"][$i]["val"] = $xMov.$yMov;
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
    public function detectMouthFeatures($sourceFaceData, $facePart)
    {
        //относительно центра между точками 62 и 66 (центр рта)
//        try {
        // first frame for standard (norm values)
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
            $midNY6266 = round(($sourceFaceData[0][66]['Y'] - $sourceFaceData[0][62]['Y'])/2) +
                $sourceFaceData[0][62]['Y'];
            $midNX6266 = round(($sourceFaceData[0][66]['X'] - $sourceFaceData[0][62]['X'])/2) +
                $sourceFaceData[0][62]['X'];

            $xN48 = $sourceFaceData[$normFrameIndex][48]['X'] - $midNX6266;
            $xN54 = $sourceFaceData[$normFrameIndex][54]['X'] - $midNX6266;
            $yN48 = $sourceFaceData[$normFrameIndex][48]['Y'] - $midNY6266;
            $yN54 = $sourceFaceData[$normFrameIndex][54]['Y'] - $midNY6266;
            $mouthLengthN = $xN54 - $xN48;

            $yN51 = $sourceFaceData[$normFrameIndex][51]['Y'] - $midNY6266;
            $yN57 = $sourceFaceData[$normFrameIndex][57]['Y'] - $midNY6266;
            $mouthWidthN = $yN57 - $yN51;


//            $maxX48 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 48, "X");
//            $minX48 = $this->getFaceDataMinForKeyV2($sourceFaceData, 48, "X");
            $maxX48 = $this->getFaceDataMaxOnPoints($sourceFaceData, 48, "X", 62,66);
            $minX48 = $this->getFaceDataMinOnPoints($sourceFaceData, 48, "X",62,66);
            $scaleX48 = $maxX48 - $minX48;
//            $maxY48 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 48, "Y");
//            $minY48 = $this->getFaceDataMinForKeyV2($sourceFaceData, 48, "Y");
            $maxY48 = $this->getFaceDataMaxOnPoints($sourceFaceData, 48, "Y",62,66);
            $minY48 = $this->getFaceDataMinOnPoints($sourceFaceData, 48, "Y",62,66);
            $scaleY48 = $maxY48 - $minY48;
//            $maxX54 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 54, "X");
//            $minX54 = $this->getFaceDataMinForKeyV2($sourceFaceData, 54, "X");
            $maxX54 = $this->getFaceDataMaxOnPoints($sourceFaceData, 54, "X",62,66);
            $minX54 = $this->getFaceDataMinOnPoints($sourceFaceData, 54, "X",62,66);
            $scaleX54 = $maxX54 - $minX54;
//            $maxY54 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 54, "Y");
//            $minY54 = $this->getFaceDataMinForKeyV2($sourceFaceData, 54, "Y");
            $maxY54 = $this->getFaceDataMaxOnPoints($sourceFaceData, 54, "Y",62,66);
            $minY54 = $this->getFaceDataMinOnPoints($sourceFaceData, 54, "Y",62,66);
            $scaleY54 = $maxY54 - $minY54;
            $maxMouthLength = $maxX54 - $minX48;
            $minMouthLength = $minX54 - $maxX48;
            $scaleMouthLength = $maxMouthLength - $minMouthLength;

//            $maxY51 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 51, "Y");
//            $minY51 = $this->getFaceDataMinForKeyV2($sourceFaceData, 51, "Y");
            $maxY51 = $this->getFaceDataMaxOnPoints($sourceFaceData, 51, "Y",62,66);
            $minY51 = $this->getFaceDataMinOnPoints($sourceFaceData, 51, "Y",62,66);
            $scaleY51 = $maxY51 - $minY51;
//            $maxY57 = $this->getFaceDataMaxForKeyV2($sourceFaceData, 57, "Y");
//            $minY57 = $this->getFaceDataMinForKeyV2($sourceFaceData, 57, "Y");
            $maxY57 = $this->getFaceDataMaxOnPoints($sourceFaceData, 57, "Y",62,66);
            $minY57 = $this->getFaceDataMinOnPoints($sourceFaceData, 57, "Y",62,66);
            $scaleY57 = $maxY57 - $minY57;
            $maxMouthWidth = $maxY57 - $minY51;
            $minMouthWidth = $minY57 - $maxY51;
            $scaleMouthWidth = $maxMouthWidth - $minMouthWidth;

            // изменение длины рта
            // NORM_POINTS 48 54
            // echo $FaceData_['normmask'][0][48][X];
            for ($i = 0; $i < count($sourceFaceData); $i++) {
                if (isset($sourceFaceData[$i][62]) && isset($sourceFaceData[$i][66])){
                    $midY6266 = round(($sourceFaceData[$i][66]['Y'] - $sourceFaceData[$i][62]['Y'])/2) +
                        $sourceFaceData[$i][62]['Y'];
                    $midX6266 = round(($sourceFaceData[$i][66]['X'] - $sourceFaceData[$i][62]['X'])/2) +
                        $sourceFaceData[$i][62]['X'];
                }

                if ((isset($sourceFaceData[$i][48]))
                   ) {
                    $leftMouthCornerXMov = $sourceFaceData[$i][48]['X'] - $xN48 - $midX6266;
                    $leftMouthCornerYMov = $sourceFaceData[$i][48]['Y'] - $yN48 - $midY6266;

                    $leftMouthCornerXMovForce = $this->getForce($scaleX48, abs($leftMouthCornerXMov));
                    $leftMouthCornerYMovForce = $this->getForce($scaleY48, abs($leftMouthCornerYMov));
//                    $leftMouthCornerYMovAvForce = round(($leftMouthCornerXMovForce + $leftMouthCornerYMovForce) / 2);
//                    $targetFaceData["mouth"]["left_corner_mouth_movement"][$i]["force"] = $leftMouthCornerYMovAvForce;
                    $targetFaceData[$facePart]["left_corner_mouth_movement_x"][$i]["force"] = $leftMouthCornerXMovForce;
                    $targetFaceData[$facePart]["left_corner_mouth_movement_y"][$i]["force"] = $leftMouthCornerYMovForce;

                    $yMov = '';
                    if ($leftMouthCornerYMov < 0) $yMov = 'up';
                    if ($leftMouthCornerYMov > 0) $yMov = 'down';
                    if ($leftMouthCornerXMov < 0) $xMov = 'from center';
                    else $xMov = 'to center';
                    //                       if ($yMov == 'none') $yMov = '';
//                        if (($xMov != '') && ($yMov != '')) $yMov = ' and ' . $yMov;
//                        if (($xMov == '') && ($yMov == '')) $yMov = 'none';
//                        $targetFaceData["mouth"]["left_corner_mouth_movement"][$i]["val"] = $xMov . $yMov;
                    $targetFaceData[$facePart]["left_corner_mouth_movement_x"][$i]["val"] = $xMov;
                    $targetFaceData[$facePart]["left_corner_mouth_movement_y"][$i]["val"] = $yMov;
                    if ($targetFaceData[$facePart]["left_corner_mouth_movement_x"][$i]["force"] == 0)
                        $targetFaceData[$facePart]["left_corner_mouth_movement_x"][$i]["val"] = 'none';
                    if ($targetFaceData[$facePart]["left_corner_mouth_movement_y"][$i]["force"] == 0)
                        $targetFaceData[$facePart]["left_corner_mouth_movement_y"][$i]["val"] = 'none';
                }

                if (isset($sourceFaceData[$i][54])) {
                    $rightMouthCornerXMov = $sourceFaceData[$i][54]['X'] - $xN54 - $midX6266;
                    $rightMouthCornerYMov = $sourceFaceData[$i][54]['Y'] - $yN54 - $midY6266;

                    $rightMouthCornerXMovForce = $this->getForce($scaleX54, abs($rightMouthCornerXMov));
                    $rightMouthCornerYMovForce = $this->getForce($scaleY54, abs($rightMouthCornerYMov));
//                    $rightMouthCornerYMovAvForce = round(($rightMouthCornerXMovForce + $rightMouthCornerYMovForce) / 2);
//                    $targetFaceData["mouth"]["right_corner_mouth_movement"][$i]["force"] = $rightMouthCornerYMovAvForce;
                    $targetFaceData[$facePart]["right_corner_mouth_movement_x"][$i]["force"] = $rightMouthCornerXMovForce;
                    $targetFaceData[$facePart]["right_corner_mouth_movement_y"][$i]["force"] = $rightMouthCornerYMovForce;

                    if (isset($sourceFaceData[$i][48])) {
                        $mouthLength = $sourceFaceData[$i][54]['X'] - $sourceFaceData[$i][48]['X'];
                    }
                    if ($rightMouthCornerYMov < 0) $yMov = 'up';
                    if ($rightMouthCornerYMov > 0) $yMov = 'down';
                    if ($rightMouthCornerXMov > 0) $xMov = 'from center';
                    else $xMov = 'to center';
//                    if ($yMov == 'none') $yMov = '';
//                    if (($xMov != '') && ($yMov != '')) $yMov = ' and ' . $yMov;
//                    if (($xMov == '') && ($yMov == '')) $yMov = 'none';
//                    $targetFaceData["mouth"]["right_corner_mouth_movement"][$i]["val"] = $xMov . $yMov;
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
                $targetFaceData[$facePart]["mouth_length"][$i]["force"] = $this->getForce(
                    $scaleMouthLength, $mouthLengthX);

                if ($mouthLength === $mouthLengthN) {
                    $targetFaceData[$facePart]["mouth_length"][$i]["force"] = 0;
                    $targetFaceData[$facePart]["mouth_length"][$i]["val"] = 'none';
                }
                if ($mouthLength > $mouthLengthN) $targetFaceData[$facePart]["mouth_length"][$i]["val"] = '+';
                if ($mouthLength < $mouthLengthN) $targetFaceData[$facePart]["mouth_length"][$i]["val"] = '-';

                /*            if (($targetFaceData["mouth"]["left_corner_mouth_movement"][$i]["val"] = 'left') and
                                ($targetFaceData["mouth"]["right_corner_mouth_movement"][$i]["val"] = 'right'))
                                $targetFaceData["mouth"]["mouth_length"][$i]["val"] = '+';
                            if (($targetFaceData["mouth"]["left_corner_mouth_movement"][$i]["val"] = 'right') and
                                ($targetFaceData["mouth"]["right_corner_mouth_movement"][$i]["val"] = 'left'))
                                $targetFaceData["mouth"]["mouth_length"][$i]["val"] = '-';*/

                // изменение ширины рта
                // NORM_POINTS 51 57

                if (isset($sourceFaceData[$i][51])) {
                    $upperLipYMov = $sourceFaceData[$i][51]['Y'] - $yN51 - $midY6266;
                    $targetFaceData[$facePart]["mouth_upper_lip_outer_center_movement"][$i]["force"] = $this->getForce(
                        $scaleY51, abs($upperLipYMov));
//                $force1 = $targetFaceData["mouth"]["mouth_upper_lip_outer_center_movement"][$i]["force"];
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
//            $deltaYUpperLip = $y;
//            if (isset($targetFaceData["mouth"]["mouth_upper_lip_outer_center_movement"][$i]["force"]))
//                $force1 = $targetFaceData["mouth"]["mouth_upper_lip_outer_center_movement"][$i]["force"];

                if (isset($sourceFaceData[$i][57])) {
                    $lowerLipYMov = $sourceFaceData[$i][57]['Y'] - $yN57 - $midY6266;
                    if (isset($sourceFaceData[$i][51])) {
                        $mouthWidth = $sourceFaceData[$i][57]['Y'] - $sourceFaceData[$i][51]['Y'];
                    }
                }

                $targetFaceData[$facePart]["mouth_lower_lip_outer_center_movement"][$i]["force"] =
                    $this->getForce($scaleY57, abs($lowerLipYMov));

                if ($targetFaceData[$facePart]["mouth_lower_lip_outer_center_movement"][$i]["force"] == 0)
                    $targetFaceData[$facePart]["mouth_lower_lip_outer_center_movement"][$i]["val"] = 'none';
                else
                    if ($lowerLipYMov > 0)
                        $targetFaceData[$facePart]["mouth_lower_lip_outer_center_movement"][$i]["val"] = 'down';
                    else
                        $targetFaceData[$facePart]["mouth_lower_lip_outer_center_movement"][$i]["val"] = 'up';
//            $deltaYLowerLip = $y;
//            $force2 = $targetFaceData["mouth"]["mouth_lower_lip_outer_center_movement"][$i]["force"];
//            $forceAv = round(($force1 + $force2)/2);

                $targetFaceData[$facePart]["mouth_width"][$i]["force"] = $this->getForce(
                    $scaleMouthWidth, abs(($mouthWidth - $mouthWidthN)));

                if ($mouthWidth === $mouthWidthN) {
                    $targetFaceData[$facePart]["mouth_width"][$i]["force"] = 0;
                    $targetFaceData[$facePart]["mouth_width"][$i]["val"] = 'none';
                }
//            if() !!!! 'compressed'
                if ($mouthWidth > $mouthWidthN) $targetFaceData[$facePart]["mouth_width"][$i]["val"] = '+';
                if ($mouthWidth < $mouthWidthN) $targetFaceData[$facePart]["mouth_width"][$i]["val"] = '-';

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
  /*  } catch (Exception $e) {
        echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
    }*/
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
                                    ($v1[$i - 1]["val"] === $v1[$i]["val"]) &&    //и значение не меняет направление
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
                                    ($v1[$i - 1]["val"] === $v1[$i]["val"]) &&    //и значение не меняет направление
                                    (isset($v1[$i - 1]["trend"]) && (strpos($v1[$i - 1]["trend"], '=') > 0))) { //и был тренд на сохранение, то продолжаем его
                                    ++$currentTrendLength;
                                    $v1[$i]["trend"] = $currentTrendLength . '=';
                                    $v1[$i]["confidence"] = 1;
                                }

                                if (($v1[$i - 1]["force"] < $v1[$i]["force"]) &&    //если интенсивность увеличивается
                                    ($v1[$i - 1]["val"] === $v1[$i]["val"]) &&    //и значение не меняет направление
                                    (isset($v1[$i - 1]["trend"]) && (strpos($v1[$i - 1]["trend"], '+') === false))) {
                                    //и был тренд на уменьшение или сохранение, то начинаем новый тренд на увеличение
                                    $currentTrendLength = 1;
                                    $v1[$i]["trend"] = $currentTrendLength . '+';
                                    $v1[$i]["confidence"] = 1;
                                }

                                if (($v1[$i - 1]["force"] > $v1[$i]["force"]) &&    //если интенсивность уменьшается
                                    ($v1[$i - 1]["val"] === $v1[$i]["val"]) &&    //и значение не меняет направление
                                    (isset($v1[$i - 1]["trend"]) && (strpos($v1[$i - 1]["trend"], '-') === false))) {
                                    //и был тренд на увеличение или сохранение, то начинаем новый тренд на уменьшение
                                    $currentTrendLength = 1;
                                    $v1[$i]["trend"] = $currentTrendLength . '-';
                                    $v1[$i]["confidence"] = 1;
                                }

                                if (($v1[$i - 1]["force"] === $v1[$i]["force"]) &&    //если интенсивность не маеняется
                                    ($v1[$i - 1]["val"] === $v1[$i]["val"]) &&    //и значение не меняет направление
                                    (isset($v1[$i - 1]["trend"]) && (strpos($v1[$i - 1]["trend"], '=') === false))) {
                                    //и был тренд на увеличение или уменьшение, то начинаем новый тренд на сохранение
                                    $currentTrendLength = 1;
                                    $v1[$i]["trend"] = $currentTrendLength . '=';
                                    $v1[$i]["confidence"] = 1;
                                }

                                if ($v1[$i - 1]["val"] !== $v1[$i]["val"]) { //если значения отличаются
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

    //input data is array
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
                if (($k === 'mouth') && ($v != null)) {
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
                }
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

    public function rotationAndStabilization($sourceFaceData1){
     // input data from the points levels
     if ($sourceFaceData1 != null) {
         $normX3942 = 0; $normY3942 = 0; $deltaX3942 = 0; $deltaY3942 = 0;
         for ($i = 0; $i < count($sourceFaceData1); $i++) {
             //--------------------------------------------------------------------------------------------------
             if (isset($sourceFaceData1[$i])) //frames
             if (isset($sourceFaceData1[$i][39])
                 && isset($sourceFaceData1[$i][42])
             ) {
                 //get  the equation of a linear function by 2 (39 and 42) points for each frame
                 //(y39-y42)x+(x42-x39)y+(x39*y42-x42*y39)=0
                 //when x=0 then y= - (x39*y42-x42*y39) / (x42-x39);
                 $deltaY = abs(round(($sourceFaceData1[$i][39]['X'] * $sourceFaceData1[$i][42]['Y'] -
                         $sourceFaceData1[$i][42]['X'] * $sourceFaceData1[$i][39]['Y']) /
                     ($sourceFaceData1[$i][42]['X'] - $sourceFaceData1[$i][39]['X'])));

                 //get rotation angle, coordibates of 39 and 42 points are used
                 $rotationAngle = acos(abs($sourceFaceData1[$i][42]['X']) /
                     (sqrt(pow($sourceFaceData1[$i][42]['X'], 2) +
                         pow($sourceFaceData1[$i][42]['Y'] - $deltaY, 2))));

                 foreach ($sourceFaceData1[$i] as $k1 => $v1) { //points
                     if (isset($sourceFaceData1[$i][$k1])) { //points $sourceFaceData3['normmask'][0][43]['X']
                         $sourceFaceData1[$i][$k1]['X'] = round($sourceFaceData1[$i][$k1]['X'] * cos($rotationAngle) -
                             $sourceFaceData1[$i][$k1]['Y'] * sin($rotationAngle));
                         $sourceFaceData1[$i][$k1]['Y'] = round($sourceFaceData1[$i][$k1]['X'] * sin($rotationAngle) +
                             $sourceFaceData1[$i][$k1]['Y'] * cos($rotationAngle));
                     }
                 }

                 //precise positioning (stabilization) the 39 point is used
                 if($normX3942 == 0) {
                 $normX3942 = $sourceFaceData1[$i][39]['X'] +
                     round(($sourceFaceData1[$i][42]['X'] - $sourceFaceData1[$i][39]['X'])/2);
                 $normY3942 = $sourceFaceData1[$i][39]['Y'];
                }
                $deltaX3942 = $sourceFaceData1[$i][39]['X'] +
                    round(($sourceFaceData1[$i][42]['X'] - $sourceFaceData1[$i][39]['X'])/2) - $normX3942;
                $deltaY3942 = $sourceFaceData1[$i][39]['Y'] - $normY3942;
//                echo $normX39.'/'.$normY39.' :: '.$deltaX39.'/'.$deltaY39.'<br>';
                if(($deltaX3942 != 0) || ($deltaY3942 != 0)){
                    foreach ($sourceFaceData1[$i] as $k1 => $v1) { //points
                        if (isset($sourceFaceData1[$i][$k1])) { //points $sourceFaceData3['normmask'][0][43]['X']
                            $sourceFaceData1[$i][$k1]['X'] = round($sourceFaceData1[$i][$k1]['X'] + $deltaX3942);
                            $sourceFaceData1[$i][$k1]['Y'] = round($sourceFaceData1[$i][$k1]['Y'] + $deltaY3942);
                        }
                    }
                }

//             $distRX3942 = $rX42 - $rX39;
//             $distRY3942 = $rY42 - $rY42;
//             echo $sourceFaceData1[0][39]['X'].'/'.$sourceFaceData1[0][39]['Y'].' '.$sourceFaceData1[0][42]['X'].'/'.
//                 $sourceFaceData1[0][42]['Y'].' <br>';
                 //                 $distX3942.' '.$distY3942.' -> '.
//                 rad2deg($rotationAngle).'='.rad2deg($rotationAngle).' '.$rX42.'/'.$rY42.' :: '.$distRX3942.' '.$distRY3942. '<br>';
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
                                if (isset($sourceFaceData1[$k][$i-1][$k1]) && isset($sourceFaceData1[$k][$i+1][$k1])) { //points $sourceFaceData3['normmask'][0][43]['X']
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
             if ($v != null) {
//        echo $k.' '.$v.'<br>';
                 for ($i = 0; $i < count($sourceFaceData1[$k]); $i++) {
                     if (isset($sourceFaceData1[$k][$i])) //frames
                         foreach ($sourceFaceData1[$k][$i] as $k1 => $v1) { //points
     //       if ($k!='normmask')              print_r($sourceFaceData1[$k][$i][$k1]);
            //                echo  '<br>';
                             if (isset($sourceFaceData1[$k][$i][$k1])) { //points $sourceFaceData3['normmask'][0][43]['X']
            //                  print_r($sourceFaceData1[$k][$i][$k1]); echo  '<br>';
                               $avSumX = 0;
                               $avSumY = 0;
                               $i2 = $i - $cnt + 1;
                               if ($i2 < 0) $i2 = 0;
            //                   if($k1 == 61) $s = $i.'('.$i2.'/'.($i - $i2 + 1).')';
                               if ($i > 0) {
                                   for ($i1 = $i; $i1 >= $i2; $i1--) {
                                       if (isset($sourceFaceData1[$k][$i1][$k1])) {
                                           $avSumX = $avSumX + $sourceFaceData1[$k][$i1][$k1]['X'];
                                           $avSumY = $avSumY + $sourceFaceData1[$k][$i1][$k1]['Y'];
            //                              if($k1 == 61) $s .= '['.$sourceFaceData1[$k][$i1][$k1]['X'].'/'.$sourceFaceData1[$k][$i1][$k1]['Y'].']';
                                       }
                                   }
                                   $avSumX = round($avSumX / ($i - $i2 + 1));
                                   $avSumY = round($avSumY / ($i - $i2 + 1));
                                } else {
                                    $avSumX = $sourceFaceData1[$k][$i][$k1]['X'];
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
             }
     return $resFaceData;
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

  /*      $fd = fopen('_AB.json', "w");
            fwrite($fd,json_encode($FaceData));
            fclose($fd);*/
        $detectedFeatures = array();
        $FaceData = $this->processingOutliers($FaceData,10,1);
        $detectedFeatures = $this->addPointsToResults('NORM_POINTS_OUTLIER',$FaceData,$detectedFeatures,
            'outlier_level_percent(10)outlier_neighbors(1)');
        $FaceData = $this->processingWithMovingAverage($FaceData,3);
        $detectedFeatures = $this->addPointsToResults('NORM_POINTS_OUTLIER_MA',$FaceData,$detectedFeatures,
            'smoth_order(3)');
        $FaceData = $this->processingWithMovingAverage($FaceData,5);
        $detectedFeatures = $this->addPointsToResults('NORM_POINTS_OUTLIER_MA',$FaceData,$detectedFeatures,
            'smoth_order(3_5)');
//                $this->saveXY2($FaceData,'m1.json');
 /*         $fd = fopen('_MA.json', "w");
              fwrite($fd,json_encode($FaceData));
              fclose($fd);*/
        $this->rotationAndStabilization($FaceData['normmask']);
/*                 $fd = fopen('_PP.json', "w");
                     fwrite($fd,json_encode($FaceData));
                     fclose($fd);*/
        $detectedFeatures['eye'] = $this->detectEyeFeatures($FaceData['normmask'],'eye');
        $detectedFeatures['mouth'] = $this->detectMouthFeatures($FaceData['normmask'],'mouth');
        $detectedFeatures['brow'] = $this->detectBrowFeatures($FaceData['normmask'],'brow');
        $detectedFeatures['eyebrow'] = $this->detectEyeBrowFeatures($FaceData['normmask'],'eyebrow');
        $detectedFeatures['nose'] = $this->detectNoseFeatures($FaceData['normmask'],'nose');
        $detectedFeatures['chin'] = $this->detectChinFeatures($FaceData['normmask'],'chin');
        $detectedFeaturesWithTrends = $this->detectTrends($detectedFeatures,5);
        $detectedFeaturesWithTrends = $this->detectAdditionalFeatures($detectedFeaturesWithTrends);
  //      $detectedFeaturesWithTrends = $this->addMAPoints($FaceData,$detectedFeaturesWithTrends,'3_5');

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
        if ((($sourceFeatureName == 'left_eye_lower_eyelid_movement_d') ||
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
        if (is_array($detectedFeatures['eye']['left_eye_upper_eyelid_movement']))
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