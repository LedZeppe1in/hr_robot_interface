<?php

namespace app\modules\main\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * VideoInterviewProcessingStatusSearch represents the model behind the search form of `app\modules\main\models\VideoInterviewProcessingStatus`.
 */
class VideoInterviewProcessingStatusSearch extends VideoInterviewProcessingStatus
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'updated_at', 'status', 'all_runtime', 'emotion_interpretation_runtime',
                'video_interview_id'], 'integer'],
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
        $query = VideoInterviewProcessingStatus::find();

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
            'all_runtime' => $this->all_runtime,
            'emotion_interpretation_runtime' => $this->emotion_interpretation_runtime,
            'video_interview_id' => $this->video_interview_id,
        ]);

        return $dataProvider;
    }
}