# Checkpoint

Checkpoint is a validation wrapper around Respect/Validation which is designed to allow custom rule generation and validation error message logging.  Some of its primary goals are:

- Do not abstract the validation logic
- Enable validation of multiple types of data
- Isolate validation to explicit encapsulated objects

## Creating an Inspector

```php
class CustomInspector extends Checkpoint\Inspector
{
	protected function validate($data)
	{
		//
		// Your validation logic here
		//
	}
}
```

### Setting the Validator

```php
$custom_inspector = new CustomInspector();

$custom_inspector->setValidator(new Respect\Validation\Validator());
```

Normally the instantiation and setting of the validator will be set up in your dependency injector.  Using a dependency injector like [Auryn]() allows you to define a preparer for the `Checkpoint\Validation` interface so that any instantiated inspector will automatically have the validator injected through its setter method.

```php
$auryn->prepare('Checkpoint\Validation', function($inspector) {
	$inspector->setValidator(new Respect\Validation\Validator());
});
```

### Additional Validation Dependencies

Because validators are encapsulated in their own classes, it's easy to inject other dependencies you may need to validate your data.  If your dependency injector does recursive construction you can set these on your custom inspector.  For example, a common requirement is to determine if an e-mail address is unique in a database table or repository, so on the inspector class you might do:

```php
public function __construct(PeopleRepository $people)
{
	$this->people = $people;
}
```

Then during validation:

```php
public function validate($data)
{
	if ($this->people->findOneByEmail($data['email'])) {
		$this->log('email', 'The e-mail address must be unique in our system.');
	}
}
```

## Performing Validation

All validation logic should be encapsulated in the `validate()` method.  The data you receive to validate can be any type of data and it's up to you to pass a valid data format.  Whether you use arrays or objects, you write your validation how you want.

```php
public function validate($data)
{
	$this->check('firstName', $data['firstName'], ['notBlank']);
}
```

Or perhaps a model/entity from your ORM:

```php
public function validate($data)
{
	$this->check('firstName', $data->getFirstName(), ['notBlank']);
}
```

### Custom Rules

The `check()` method suppports a handful of default rules provided by Respect, including:

- alpha
- email
- phone
- lowercase
- notBlank

Default rules will only ever include those that do not require additional arguments.  To define custom rules you can use the define method which takes the rule name, the error message to log, and returns a `Respect\Validation\Validator` to chain rules on:

```php
public function validate($data)
{
	$this->define('descLength', 'Please enter a description of at least 100 characters.')
		 -> length(100);

	$this->check('description', $data['description'], ['descLength']);
}
```

### Running Validation

Once your `validate()` method is setup you can run validation simply by passing in the requisite data:

```
$custom_inspector->run($data);
```

Since the data to be inspected is whatever you want it to be this can take multiple formats:

```php
$person = new Person();
$person->setFirstName('Matthew');
$person->setLastName('Sahagian');
...
$person_inspector->run($person)
```

Or perhaps a more explicit array:

```php
$person_inspector->run([
	'firstName' => 'Matthew',
	'lastName'  => 'Sahagian'
]);
```

This flexibility allows you to validate anything from form input, models, all the way to a single value:

```php
$email_inspector->run('user@example.com');
```

Write your validators for whatever suites you, including direct request input:

```php
$registration_inspector->run($this->request->getParsedBody());
```

### Checking Messages

The first argument of the `check()` method defines under what name messages will be logged.  Messages are always added to an array keyed under the name provided so that multiple validation messages can be added if multiple checks are added.  You can get the total number of messages logged with the `countMessages()` method:

```php
if ($custom_inspector->countMessages()) {
	throw new Checkpoint\ValidationException('Please correct the errors below.');
}
```

To get a specific message you can use the `getMessages()` method and provide it a path to the messages.  The path for a top-level inspector is simply the key name which was passed when calling check.

```php
if ($messages = $custom_inspector->getMessages('description')) {
	foreach ($messages as $message) {
		echo '<li>' . $message . '</li>';
	}
}
```

In most cases you will want to define a template partial for this.  Here's an example in twig:

```html
{% if errors %}
	{% if errors|length == 1 %}
		<div class="messaging error">
			<p>{{ errors[0]|raw }}</p>
		</div>
	{% else %}
		<ul class="messaging error">
			{% for error in errors %}
				<li>{{ error|raw }}</li>
			{% endfor %}
		</ul>
	{% endif %}
{% endif %}
```

You can then see how this partial can be included inline to produce per-field messaging:

```html
<label>First Name</label>
{% include '@messaging/errors.html' with {'errors': inspector.messages('firstName')} %}
<input type="text" name="firstName" value="{{ params.firstName }}" />
```

## Child Inspectors

Sometimes you want to validate really complex data structures or related objects and the such.  For this reason, Checkpoint supports adding child inspectors to pass additional validation on to.  To add a child inspector you must take the following steps:

### Register the Child

Since the child is a dependency of the parent, we can dependency inject it into the constructor and register it there.

```php
public function __construct(PersonInspector $person_inspector)
{
	$this->add('person', $person_inspector);
}
```

### Fetch and Run the Child

Now that you have a child inspector added, you can fetch and run it during valdiation with a subset of your data:

```php
public function validate($data)
{
	$this->fetch('person')->run($data->getPerson());
}
```

### Check Child Messages

Child messages will be accessible from the top-level validator using an object notation to recursively reference child inspectors and eventually get the messages for their particular checks:

```php
$messages = $registration_inspector->getMessages('person.firstName');
```

## A Complex Example

Here is a moderately complex example which includes a child inspector based on some of the concepts we've already covered:

```php
class ProfileInspector extends Checkpoint\Inspector
{
	public function __construct(PeopleRepository $people, PersonInspector $pinspector, CompanyInspector $cinspector)
	{
		$this->people = $people;
		$this->add('person', $pinspector);
		$this->add('company', $cinspector);
	}


	protected function validate($data)
	{
		if ($this->people->findOneByEmail($data['person']['email'])) {
			$this->log('duplicate', TRUE);
			return;
		}

		$this->fetch('person')->run($data['person']);
		$this->fetch('company')->run($data['company']);
	}
}
```

In this example the `ProfileInspector` is a top level inspector which checks for a duplicate and returns immediately if one is found.  Otherwise, it goes on to validate the additional details.  It's dependencies are a `PersonInspector` and a `CompanyInspector` which will check subsets of its data, for example:

```php
class CompanyInspector extends Checkpoint\Inspector
{
	protected function validate($data)
	{
		$this->define('zipCode', 'Please enter a valid zipcode for the US')
			 ->postalCode('US');

		$this
			->check('name', $data['name'], ['notBlank'])
			->check('address', $data['address'], ['notBlank'])
			->check('city', $data['city'], ['notBlank'])
			->check('state', $data['state'], ['notBlank'])
			->check('zipCode', $data['zipCode'], ['zipCode'])
		;
	}
}
```

Similarly, the `PersonInspector` would only concern itself with checking/logging errors on the person related data.  Once you have all three objects, the following is possible:

```php
$profile_inspector = new ProfileInspector(new PeopleRepository, new PersonInspector, new CompanyInspector);
$profile_inspector->run([
	'person' => [
		'firstName' => 'Matthew',
		'lastName'  => 'Sahagian'
	],
	'company' => [
		'name'    => 'Imarc LLC',
		'address' => '111 1/2 Cooper Street',
		'city'    => 'Santa Cruz',
		'state'   => 'CA',
		'zipCode' => '95060'
	]
]);

if ($profile_inspector->countMessages()) {
	throw new Checkpoint\ValidationException('Please correct the errors below.');
}
```

Remember that the data you submit is probably going to come in the form of `POST` data submitted directly by a form or an object, so actual usage will not be this verbose.  Additionally, a recursive dependency injector will go a long way towards filling your dependencies right down the line.  Checking messages on the Company's zip code would then follow as something like this:

```twig
<label>Company Name</label>
{% include '@messaging/errors.html' with {'errors': inspector.messages('company.name')} %}
<input type="text" name="company[name]" value="{{ params.company.name }}" />
```

You could also check for a duplicate at the very top to provide a much more obvious warning:

```php
{% if inspector.messages('duplicate') %}
	<div class="error">
		<p>
			The e-mail you are trying to use is already taken by another person in our system.  If you are the
			owner of that account, you may want to try <a href="/forgot-password">recovering your account</a>.
		</p>
	</div>
{% endif %}
```

## Conclusion

Checkpoint was written to solve much of the inflexibility found in other validation systems.  The ability to add child inspectors means that you can aggregate a number of inspectors for simpler objects and the composite them together for more complex validation like with a registration form that might actually represent more than one domain modeled data structure.  Inspectors can be written an composited from the bottom (a single field) to the top (a complex form).

By encapsulating validation logic and using actual logic with a few clean helper methods, it's possible to do much more complex validation while still keeping all the rules for a particular domain model in one place as opposed to having simpler checks written in a config, and then more complex checks written in separately registered helpers or as separate rule objects.

Repsect/Validation provides a lot of possible rules out of the box, so simple validation remains easy.  To learn more about Respect/Validation, see [their github repository](https://github.com/respect/validation).
