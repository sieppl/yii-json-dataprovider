<?php
class JSonActiveDataProvider extends CActiveDataProvider
{
	/**
	 * 
	 * @var mixed If is set to null (default) all attributes of the model will be added. 
	 *            If a string is given, only this attribute will added. 
	 *            If an array of strings is given, all elements will be retrieved.
	 */
	public $attributes;
	
	/**
	 * 
	 * @var mixed If is set to null (default) no relations of the model will be added. 
	 *            If a string is given, only the mentioned relation will be added.
	 *            If an array is given, there a two valid array formats:
	 *            
	 *            1. array('relation1', 'relation2') will return the mentioned relations with all attributes, but no sub relations
	 *            2. array('relation1', 'relation2' => array('attributes' => array('foo', 'bar'), 'relations' => array('subRelation'))) will return configured attributes and relations for relation2
	 *            
	 *            Sub configurations of relations follow the same rules like the global configuration for attributes and relations
	 */
	public $relations;
	
	/**
	 * 
	 * @var array An array where the key is the original attribute name and the value the alias to be used instead when retrieving it. This will affect all retrieved models recursively.
	 */
	public $attributeAliases;
	
	/**
	 * 
	 * @var boolean When set to true the root of the json will have meta informations from the data provider for counts and pagination.
	 */
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
					'currentPage' => $this->pagination ? $this->pagination->currentPage : 1,
					'pageCount' => $this->pagination ? $this->pagination->pageCount : 1,
					'pageSize' => $this->pagination ? $this->pagination->pageSize : $this->getItemCount(),
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
