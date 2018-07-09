# Using Vtiger client

```php
try {


    /** @var ContactRepository $repo */
    $repo = $this->get('mautic.vtiger.repository_manager')->getRepository('Contacts');

    /** @var ModuleInfo $description */
    $description = $repo->describe();

    /** @var ModuleInfo $fields */
    $fields = $description->getFields();

    /** @var array $contacts */
    $contacts = $repo->findBy(['email' => 'galvani78@gmail.com']);

    $contact = (array)array_shift($contacts);
    $contact['email'] = date('mis') . "nonono@mautic.com";

    unset($contact['id']);

    $newContact = $repo->create($contact);
    $id = $newContact->getId();

    try {
        $retrieved = $repo->retrieve($id."AAA");
    } catch (DatabaseQueryException $e) {   // not found
        var_dump($e); die();
    }

    var_dump($retrieved);
}
catch (\Exception $e) {
    var_dump($e);
}
```