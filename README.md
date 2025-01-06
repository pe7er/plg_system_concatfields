# plg_system_concatfields
System plugin to combine two Custom Fields into a third Custom Field.

Change in ``plg_system_concatfields/concatfields.php`` line 72 the names of the Custom Fields:
```php
        // Change these Custom Field names
        $customField1 = 'name-of-custom-field1';
        $customField2 = 'name-of-custom-field2';
        $customField3 = 'name-of-custom-field3'; // Concatenate Field1 + Field2 into this Field3
```

Be sure to run this Plugin AFTER the ``System - Fields`` Plugin!
