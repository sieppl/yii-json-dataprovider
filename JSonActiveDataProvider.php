class JSonActiveDataProvider extends CActiveDataProvider
{
  public $attributes;
	public $relations;
	public $attributeAliases;
	public $includeDataProviderInformation = true;
	
	public function getArrayData($refresh=false)
	{
		$arrayData = array();
		$data = $this->getData($refresh);
		foreach ($data as $model)
		{
			$arrayData[] = $this->modelToArray($model, $this->attributes, $this->relations);
		}
		if ($this->includeDataProviderInformation)
		{
			return array(
					'itemCount' => $this->getItemCount(),
					'totalItemCount' => (int) $this->getTotalItemCount(),
					'currentPage' => $this->pagination->currentPage,
					'pageCount' => $this->pagination->pageCount,
					'pageSize' => $this->pagination->pageSize,
					'data' => $arrayData
			);
		}
		else
			return $arrayData;
	}
	
	public function getJsonData($refresh=false)
	{
		return json_encode($this->getArrayData($refresh));
	}
	
	
	/**
	 * 
	 * @param CActiveRecord $model
	 * @param mixed $attributes If is set to null all attributes of the model will be added. If a string is given, only this attribute will added. If an array of strings is given, all elements will be retrieved. 
	 * @param mixed $relations If is set to true, all relations will be retrieved. If a string is given, only one relation will be added. For array see the class definition of relations.
	 */
	protected function modelToArray($model, $attributes = null, $relations = null)
	{
		$relationJson = array();
		
		if ($relations === true || is_array($relations) || is_string($relations))
		{
			if ($relations === true)
			{
				//include all relations
				$relations = array();
				foreach ($model->getMetaData()->relations as $name => $relation)
				{
					$relations[] = $name;
				}
			}
			
			if (is_string($relations))
				$relations = array($relations);
			
			foreach ($relations as $key => $relation)
			{
				$relAttributes = null;
				$relRelations = null;
				
				$relationName = $relation;
				if (is_array($relation))
				{
					$relationName = $key;
					$relAttributes = isset($relation['attributes']) ? $relation['attributes'] : null;
					$relRelations = isset($relation['relations']) ? $relation['relations'] : null;
				}
				
				$relatedModels = $model->getRelated($relationName);
				if (is_array($relatedModels))
				{
					foreach ($relatedModels as $relatedModel)
					{
						$relationJson[$relationName][] = $this->modelToArray($relatedModel, $relAttributes, $relRelations);
					}
				}
				else {
					if ($relatedModels)
						$relationJson[$relationName] = $this->modelToArray($relatedModels, $relAttributes, $relRelations);
				}

			}
		}
		
		if (is_string($attributes))
			$attributes = array($attributes);
		
		if ($attributes === null)
			$attributes = true;
		
		$attributeArray = $model->getAttributes($attributes);
		if ($this->attributeAliases)
		{
			$tempArray = array();
			foreach ($attributeArray as $attributeName => $value)
			{
				if (isset($this->attributeAliases[$attributeName]))
				{
					$tempArray[$this->attributeAliases[$attributeName]] = $value;
				}
				else
					$tempArray[$attributeName] = $value;
			}
			
			$attributeArray = $tempArray;
		}
		return array_merge($attributeArray, $relationJson);
	}
}
