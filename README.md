yii-json-dataprovider
=====================

A yii dataprovider that extends CActiveDataProvider in order to retrieve json. Attributes and relations that are fetched, are recursively configurable. 

Examples
--------

```php
  header('Content-Type: application/json');
  $dataProvider = new JSonActiveDataProvider('Person');
  echo $dataProvider->getJsonData();
```

```php
  $dataProvider = new JSonActiveDataProvider('Person', array('attributes' => array('id', 'name')));
```

```php
  $dataProvider = new JSonActiveDataProvider('Person', array('attributes' => 'name'));
```

```php
  $dataProvider = new JSonActiveDataProvider('Person', array(
    'attributes' => array('id', 'name'), 
    'relations' => array('bankAccounts', 'address')
  ));
```

```php
  $dataProvider = new JSonActiveDataProvider('Person', array('attributes' => array('id', 'name')));
```

```php
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
