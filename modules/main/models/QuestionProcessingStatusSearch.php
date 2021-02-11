<?php

namespace app\modules\main\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * QuestionProcessingStatusSearch represents the model behind the search form of `app\modules\main\models\QuestionProcessingStatus`.
 */
class QuestionProcessingStatusSearch extends QuestionProcessingStatus
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'updated_at', 'status', 'ivan_video_analysis_runtime',
                'andrey_video_analysis_runtime', 'feature_detection_runtime', 'feature_interpretation_runtime',
                'question_id', 'video_interview_processing_status_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = QuestionProcessingStatus::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'status' => $this->status,
            'ivan_video_analysis_runtime' => $this->ivan_video_analysis_runtime,
            'andrey_video_analysis_runtime' => $this->andrey_video_analysis_runtime,
            'feature_detection_runtime' => $this->feature_detection_runtime,
            'feature_interpretation_runtime' => $this->feature_interpretation_runtime,
            'question_id' => $this->question_id,
            'video_interview_processing_status_id' => $this->video_interview_processing_status_id,
        ]);

        return $dataProvider;
    }
}