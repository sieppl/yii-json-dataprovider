yii-json-dataprovider
=====================

A yii dataprovider that extends CActiveDataProvider in order to retrieve json. Attributes and relations that are fetched, are recursively configurable. 
The dataprovier uses ModelToArrayConverter, a class that does the actual converting from models to an array representation.
ModelToArrayConverter can be used stand-alone.

Update on 06/11/2013
--------------------

1. Extracting the work horse that does the converting to new class
ModelToArrayConverter. This class is usable stand-alone, too.
2. new function getArrayCountData on JSonActiveDataProvider to retrieve
just the count information, but not data.
3. New callback onAfterModelToArray on JSonActiveDataProvider for
optional custom extracting of data to the final json per model.
4. For convenience relations can be defined now directly with an
attributes description (instead of wrapping them into another array with
a key "attributes"

All changes do not break old behavior or usage!


Examples
--------

```php
  header('Content-Type: application/json');
  //same syntax like CActiveDataProvider
  $dataProvider = new JSonActiveDataProvider('Person'); 
  echo $dataProvider->getJsonData(); 
```

```php
  //in order to get the raw array (before encoding) you can call
  $dataProvider->getArrayData(); 
```

```php
  //limit the attributes to retrieve from Person
  $dataProvider = new JSonActiveDataProvider('Person', array('attributes' => array('id', 'name')));
```

```php
  //only retrieve the "name" attribute of Person, same result like array('name')
  $dataProvider = new JSonActiveDataProvider('Person', array('attributes' => 'name'));
```

```php
  //retrieve relations with all attribute, but no sub relations of "bankAccounts" or "address"
  $dataProvider = new JSonActiveDataProvider('Person', array(
    'attributes' => array('id', 'name'), 
    'relations' => array('bankAccounts', 'address')
  ));
```

```php
  //same like above, but limit the attribite of the relations and retrieve relation "bank" of each "bankAccounts"
  $dataProvider = new JSonActiveDataProvider('Person', array(
    'attributes' => array('id', 'name'), 
    'relations' => array(
      'bankAccounts' => array(
        'attributes' => array('id', 'account_number'), 
        'relations' => array(
          'bank' => array(
            'attributes' => array('id', 'name')
          )
        )
      )
    )
  ));
```

```php
  //same like above, but with convenient attribute definition of relations
  $dataProvider = new JSonActiveDataProvider('Person', array(
    'attributes' => array('id', 'name'), 
    'relations' => array(
      'bankAccounts' => array(
        'attributes' => array('id', 'account_number'), 
        'relations' => array(
          'bank' => array('id', 'name')
        )
      )
    )
  ));
```

```php
  //replacing "id" by "i" and "name" by "n" in all results
  $dataProvider = new JSonActiveDataProvider('Person', array(
    'attributeAliases' => array('id' => 'i', 'name' => 'n'),
    'attributes' => array('id', 'name'), 
    'relations' => array(
      'bankAccounts' => array(
        'attributes' => array('id', 'account_number'), 
        'relations' => array(
          'bank' => array(
            'attributes' => array('id', 'name')
          )
        )
      )
    )
  ));
```

```php
  //using a callback to manipulate the json outcome manually
  //in this example we us a closure, but any callback as defined here is fine: 
  //http://php.net/manual/en/language.types.callable.php
  
  $callback = function ($model, &$attributeArray, &$relationArray)
	{
		if ($model instanceof Person)
		{
			$attributeArray['bar'] = 'foo';
		}
	};
    
  $dataProvider = new JSonActiveDataProvider('Person', array(
    'onAfterModelToArray' => $callback,
  ));
```

```php
  //converting a custom model array without JSonActiveDataProvider
  $array = ModelToArrayConverter::instance($myModels)->convert();
  
  //with optional configuration whhich attributes to be converted (third parameter accepts relation config)
  $array = ModelToArrayConverter::instance($myModels, array('id', 'name'))->convert();
```
