<?php
class ModelToArrayConverter extends CComponent
{
	
	protected $_models;
	/**
	 *
	 * @var mixed If is set to null (default) all attributes of the model will be added.
	 *            If a string is given, only this attribute will added.
	 *            If an array of strings is given, all elements will be retrieved.
	 */
	protected $_attributes;
	
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
	protected $_relations;	
	
	/**
	 *
	 * @var array An array where the key is the original attribute name and the value the alias to be used instead when retrieving it. This will affect all retrieved models recursively.
	 */
	public $attributeAliases;	
	
	/**
	 *
	 * @var callable Callback to be called after an model was processed for json, the callback will receive the model itself, the attribute array and the relation array
	 */
	public $onAfterModelToArray = null;
	
	/**
	 * 
	 * @param mixed single $model or model array
	 * @param unknown_type $attributes
	 * @param unknown_type $relations
	 */
	public function __construct($models, $attributes = null, $relations = null)
	{
		$this->_models = $models;
		$this->_attributes = $attributes;
		$this->_relations = $relations;
	}
	
	/**
	 *
	 * @param CActiveRecord $model
	 * @param mixed $attributes If is set to null all attributes of the model will be added. If a string is given, only this attribute will added. If an array of strings is given, all elements will be retrieved.
	 * @param mixed $relations If is set to true, all relations will be retrieved. If a string is given, only one relation will be added. For array see the class definition of relations.
	 */
	protected function convertModel($model, $attributes = null, $relations = null)
	{
		$relationArray = array();
		
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
					
					if (!isset($relation['attributes']) && !isset($relation['relations']))
					{
						// for convenient configuration
						$relAttributes = $relation;
					}
					else
					{
						$relAttributes = isset($relation['attributes']) ? $relation['attributes'] : null;
						$relRelations = isset($relation['relations']) ? $relation['relations'] : null;
					}
				}
		
				$relatedModels = $model->getRelated($relationName);
				if (is_array($relatedModels))
				{
					foreach ($relatedModels as $relatedModel)
					{
						$relationArray[$relationName][] = $this->convertModel($relatedModel, $relAttributes, $relRelations);
					}
				}
				else {
					if ($relatedModels)
						$relationArray[$relationName] = $this->convertModel($relatedModels, $relAttributes, $relRelations);
				}
			}
		}
		
		if (is_string($attributes))
			$attributes = array($attributes);
		
		if ($attributes === null)
			$attributes = true;
		
		foreach ($attributes as $attribute)
		{
			$attributeArray[$attribute] = $model->$attribute;
		}
		
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
		
		if ($this->onAfterModelToArray)
		{
			call_user_func_array($this->onAfterModelToArray, array($model, &$attributeArray, &$relationArray));
		}
		
		return array_merge($attributeArray, $relationArray);
	}
	
	public function convert()
	{
		if (is_array($this->_models))
		{
			$result = array();
			foreach ($this->_models as $model)
			{
				$result[] = $this->convertModel($model, $this->_attributes, $this->_relations);
			}
			return $result;
		}
		else
			return $this->convertModel($this->_models, $this->_attributes, $this->_relations);
	}

	public static function instance($models, $attributes = null, $relations = null)
	{
		return new ModelToArrayConverter($models, $attributes, $relations);
	}
}