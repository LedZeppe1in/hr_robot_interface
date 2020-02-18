<?php

namespace app\components;

class Brow1
{
    public $browWidth = '';
}

class EyeBrow1
{
    public $eyeBrowMovement = '';
}

class Eye1
{
    public $leftEyeWidth = '';
    public $rightEyeWidth = '';
    public $leftEyeUpperEyelidMovement = '';
    public $rightEyeUpperEyelidMovement = '';
}

class Mouth1
{
    public $mouthLength = '';
    public $mouthForm = '';
    public $mouthCornersMovement = '';
}

class EmotionDetector
{
    public $name = 'none';
    public $firedRule = 'none';

    public function RunKB($FaceData)
    {
        $brow1 = new Brow1();
        $eyebrow1 = new EyeBrow1();
        $eye1 = new Eye1();
        $mouth1 = new Mouth1();

        if (isset($FaceData['frames']['0']["brow"]["brow_width"]["val"]))
            $brow1->browWidth = $FaceData['frames']['0']["brow"]["brow_width"]["val"];
        if (isset($FaceData['frames']['0']["eyebrow"]["eyebrow_movement"]["val"]))
            $eyebrow1->eyeBrowMovement = $FaceData['frames']['0']["eyebrow"]["eyebrow_movement"]["val"];
        if (isset($FaceData['frames']['0']["eye"]["left_eye_width"]["val"]))
            $eye1->leftEyeWidth = $FaceData['frames']['0']["eye"]["left_eye_width"]["val"];
        if (isset($FaceData['frames']['0']["eye"]["right_eye_width"]["val"]))
            $eye1->rightEyeWidth = $FaceData['frames']['0']["eye"]["right_eye_width"]["val"];
        if (isset($FaceData['frames']['0']["eye"]["left_eye_upper_eyelid_movement"]["val"]))
            $eye1->leftEyeUpperEyelidMovement = $FaceData['frames']['0']["eye"]["left_eye_upper_eyelid_movement"]["val"];
        if (isset($FaceData['frames']['0']["eye"]["right_eye_upper_eyelid_movement"]["val"]))
            $eye1->rightEyeUpperEyelidMovement = $FaceData['frames']['0']["eye"]["right_eye_upper_eyelid_movement"]["val"];
        if (isset($FaceData['frames']['0']["mouth"]["mouth_length"]["val"]))
            $mouth1->mouthLength = $FaceData['frames']['0']["mouth"]["mouth_length"]["val"];
        if (isset($FaceData['frames']['0']["mouth"]["mouth_form"]["val"]))
            $mouth1->mouthForm = $FaceData['frames']['0']["mouth"]["mouth_form"]["val"];
        if (isset($FaceData['frames']['0']["mouth"]["mouth_corners_movement"]["val"]))
            $mouth1->mouthCornersMovement = $FaceData['frames']['0']["mouth"]["mouth_corners_movement"]["val"];

        //fear-p-01
        if (
            (($brow1->browWidth == "decrease"))
            and
            (($eyebrow1->eyeBrowMovement == "up"))
            and
            (($eye1->leftEyeWidth == "increase") and ($eye1->leftEyeUpperEyelidMovement == "up"))
            and
            (($mouth1->mouthLength == "increase") and ($mouth1->mouthForm == "ellipse"))
        ){
            $this->name = "fear";
            $this->firedRule = "fear-p-01";
        }
        //fear-p-02
        if (
            (($brow1->browWidth == "decrease"))
            and
            (($eyebrow1->eyeBrowMovement == "to_center"))
            and
            (($eye1->rightEyeWidth == "increase") and ($eye1->rightEyeUpperEyelidMovement == "up"))
            and
            (($mouth1->mouthLength == "increase") and ($mouth1->mouthForm == "ellipse"))
        ){
            $this->name = "fear";
            $this->firedRule = "fear-p-02";
        }
        //fear-p-03
        if (
            (($brow1->browWidth == "decrease"))
            and
            (($eyebrow1->eyeBrowMovement == "up"))
            and
            (($eye1->leftEyeWidth == "increase") and ($eye1->rightEyeWidth == "increase"))
            and
            (($mouth1->mouthLength == "increase") and ($mouth1->mouthForm == "ellipse") and
                ($mouth1->mouthCornersMovement == "aside"))
        ){
            $this->name = "fear";
            $this->firedRule = "fear-p-03";
        }
        //fear-p-04
        if (
            (($brow1->browWidth == "decrease"))
            and
            (($eyebrow1->eyeBrowMovement == "up"))
            and
            (($eye1->leftEyeWidth == "increase") and ($eye1->leftEyeUpperEyelidMovement == "up"))
            and
            (($mouth1->mouthLength == "increase") and ($mouth1->mouthForm == "ellipse") and
                ($mouth1->mouthCornersMovement == "aside"))
        ){
            $this->name = "fear";
            $this->firedRule = "fear-p-04";
        }
        //fear-p-05
        if (
            (($brow1->browWidth == "decrease"))
            and
            (($eyebrow1->eyeBrowMovement == "to_center"))
            and
            (($eye1->rightEyeWidth == "increase") and ($eye1->rightEyeUpperEyelidMovement == "up"))
            and
            (($mouth1->mouthForm == "ellipse") and ($mouth1->mouthCornersMovement == "aside"))
        ){
            $this->name = "fear";
            $this->firedRule = "fear-p-05";
        }
        //fear-p-06
        if (
            (($brow1->browWidth == "decrease"))
            and
            (($eyebrow1->eyeBrowMovement == "up"))
            and
            (($eye1->leftEyeWidth == "increase") and ($eye1->rightEyeWidth == "increase") and
                ($eye1->leftEyeUpperEyelidMovement == "up") and ($eye1->rightEyeUpperEyelidMovement == "up"))
            and
            (($mouth1->mouthForm == "ellipse") and ($mouth1->mouthCornersMovement == "aside"))
        ){
            $this->name = "fear";
            $this->firedRule = "fear-p-06";
        }
        //fear-p-07
        if (
            (($brow1->browWidth == "decrease"))
            and
            (($eyebrow1->eyeBrowMovement == "up"))
            and
            (($eye1->leftEyeWidth == "increase") and ($eye1->rightEyeWidth == "increase"))
            and
            (($mouth1->mouthLength == "decrease") and ($mouth1->mouthForm == "ellipse") and
                ($mouth1->mouthCornersMovement == "aside"))
        ){
            $this->name = "fear";
            $this->firedRule = "fear-p-07";
        }
        //fear-p-08
        if (
            (($brow1->browWidth == "decrease"))
            and
            (($eyebrow1->eyeBrowMovement == "up"))
            and
            (($eye1->leftEyeWidth == "increase"))
            and
            (($mouth1->mouthLength == "none") and ($mouth1->mouthForm == "ellipse") and
                ($mouth1->mouthCornersMovement == "aside"))
        ){
            $this->name = "fear";
            $this->firedRule = "fear-p-08";
        }
        //fear-p-09
        if (
            (($brow1->browWidth == "decrease"))
            and
            (($eyebrow1->eyeBrowMovement == "to_center"))
            and
            (($eye1->leftEyeWidth == "increase") and ($eye1->rightEyeWidth == "increase"))
            and
            (($mouth1->mouthLength == "increase") and ($mouth1->mouthForm == "ellipse") and
                ($mouth1->mouthCornersMovement == "aside"))
        ){
            $this->name = "fear";
            $this->firedRule = "fear-p-09";
        }
        //fear-p-10
        if (
            (($brow1->browWidth == "decrease"))
            and
            (($eyebrow1->eyeBrowMovement == "to_center"))
            and
            (($eye1->leftEyeWidth == "increase") and ($eye1->rightEyeWidth == "increase") and
                ($eye1->leftEyeUpperEyelidMovement == "up") and ($eye1->rightEyeUpperEyelidMovement == "up"))
            and
            (($mouth1->mouthForm == "ellipse") and ($mouth1->mouthCornersMovement == "aside"))
        ){
            $this->name = "fear";
            $this->firedRule = "fear-p-10";
        }
        //fear-p-11
        if (
            (($brow1->browWidth == "decrease"))
            and
            (($eyebrow1->eyeBrowMovement == "to_center"))
            and
            (($eye1->leftEyeWidth == "increase") and ($eye1->rightEyeWidth == "increase"))
            and
            (($mouth1->mouthLength == "decrease") and ($mouth1->mouthForm == "ellipse") and
                ($mouth1->mouthCornersMovement == "aside"))
        ){
            $this->name = "fear";
            $this->firedRule = "fear-p-11";
        }
        //
        if ($this->name == "") {
            $this->name = "none";
            $this->firedRule = "none";
        }
    }
}