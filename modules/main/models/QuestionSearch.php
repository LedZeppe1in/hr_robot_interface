<?php

namespace app\modules\main\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * QuestionSearch represents the model behind the search form of `app\modules\main\models\Question`.
 */
class QuestionSearch extends Question
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'updated_at', 'test_question_id', 'video_interview_id'], 'integer'],
            [['video_file_name', 'description'], 'safe'],
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
        $query = Question::find();

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
            'test_question_id' => $this->test_question_id,
            'video_interview_id' => $this->video_interview_id,
        ]);

        $query->andFilterWhere(['ilike', 'video_file_name', $this->video_file_name])
            ->andFilterWhere(['ilike', 'description', $this->description]);

        return $dataProvider;
    }
}