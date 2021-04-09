<?php

namespace app\modules\main\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * LandmarkSearch represents the model behind the search form of `app\modules\main\models\Landmark`.
 */
class LandmarkSearch extends Landmark
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'updated_at', 'video_interview_id', 'rotation', 'start_time', 'finish_time',
                'question_id', 'type'], 'integer'],
            [['landmark_file_name', 'description', 'processed_video_file_name'], 'safe'],
            [['mirroring'], 'boolean'],
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
        $query = Landmark::find();

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
            'video_interview_id' => $this->video_interview_id,
            'rotation' => $this->rotation,
            'mirroring' => $this->mirroring,
            'start_time' => $this->start_time,
            'finish_time' => $this->finish_time,
            'question_id' => $this->question_id,
            'type' => $this->type,
        ]);

        $query->andFilterWhere(['ilike', 'landmark_file_name', $this->landmark_file_name])
            ->andFilterWhere(['ilike', 'description', $this->description])
            ->andFilterWhere(['ilike', 'processed_video_file_name', $this->processed_video_file_name]);

        return $dataProvider;
    }
}