yii-json-dataprovider
=====================

A yii dataprovider that extends CActiveDataProvider in order to retrieve json. Attributes and relations that are fetched, are recursively configurable. 

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
