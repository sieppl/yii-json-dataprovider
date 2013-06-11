<?php
Yii::import('ext.yii-json-dataprovider.ModelToArrayConverter');

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
	
	/**
	 * 
	 * @var callable Callback to be called after an model was processed for json, the callback will receive the model itself, the attribute array and the relation array
	 */
	public $onAfterModelToArray = null;
	
	public function getArrayCountData()
	{
		return array(
				'itemCount' => $this->getItemCount(),
				'totalItemCount' => (int) $this->getTotalItemCount(),
				'currentPage' => $this->pagination ? $this->pagination->currentPage : 1,
				'pageCount' => $this->pagination ? $this->pagination->pageCount : 1,
				'pageSize' => $this->pagination ? $this->pagination->pageSize : $this->getItemCount(),
		);
	}
	
	public function getArrayData($refresh=false)
	{
		$arrayData = array();		
		
		if ($data = $this->getData($refresh))
		{
			$converter = new ModelToArrayConverter($data, $this->attributes, $this->relations);
			$converter->attributeAliases = $this->attributeAliases;
			$converter->onAfterModelToArray = $this->onAfterModelToArray;
			$arrayData = $converter->convert();	
		}
		
		if ($this->includeDataProviderInformation)
		{
			return array_merge($this->getArrayCountData(), array(
					'data' => $arrayData
			));
		}
		else
			return $arrayData;
	}
	
	public function getJsonData($refresh=false)
	{
		return json_encode($this->getArrayData($refresh));
	}	
}
