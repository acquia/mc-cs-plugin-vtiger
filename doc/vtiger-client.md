# Using Vtiger client

```php
/** @var ContactRepository $repo */
$repo = $this->get('mautic.vtiger.repository_manager')->getRepository('Contacts');

/** @var ModuleInfo $description */
$description = $repo->describe();

/** @var ModuleInfo $fields */
$fields = $description->getFields();

/** @var array $contacts */
$contacts = $repo->findBy(['email'=>'galvani78@gmail.com'],'firstname,lastname,email');

var_dump($contacts);
```