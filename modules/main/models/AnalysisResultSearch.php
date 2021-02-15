<?php

namespace app\modules\main\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * AnalysisResultSearch represents the model behind the search form of `app\modules\main\models\AnalysisResult`.
 */
class AnalysisResultSearch extends AnalysisResult
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'updated_at', 'landmark_id'], 'integer'],
            [['detection_result_file_name', 'facts_file_name', 'interpretation_result_file_name', 'description',
                'landmarkName'], 'safe'],
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
        $query = AnalysisResult::find();

        $query->joinWith('landmark'); // Объединение с таблицей "hrrobot_landmark" в БД

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        // Реализация сортировки по добавленному полю "landmarkName" в модели Landmark
        $dataProvider->setSort([
            'attributes' => [
                'id',
                'landmark_id',
                'landmarkName' => [
                    'asc' => ['hrrobot_landmark.landmark_file_name' => SORT_ASC],
                    'desc' => ['hrrobot_landmark.landmark_file_name' => SORT_DESC],
                ],
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'hrrobot_analysis_result.id' => $this->id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'landmark_id' => $this->landmark_id,
        ]);

        $query->andFilterWhere(['ilike', 'detection_result_file_name', $this->detection_result_file_name])
            ->andFilterWhere(['ilike', 'facts_file_name', $this->facts_file_name])
            ->andFilterWhere(['ilike', 'interpretation_result_file_name', $this->interpretation_result_file_name])
            ->andFilterWhere(['ilike', 'description', $this->description])
            ->andFilterWhere(['like', 'hrrobot_landmark.landmark_file_name', $this->landmarkName]); // Поиск по названию файла цифровой маски

        return $dataProvider;
    }
}