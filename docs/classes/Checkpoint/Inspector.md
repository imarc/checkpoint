# Inspector
## The inspector is a rule/error message organizer that wraps around Respect/Validation

_Imarc LLC 2016_.

#### Namespace

`Checkpoint`

#### Imports

<table>

	<tr>
		<th>Alias</th>
		<th>Namespace / Target</th>
	</tr>
	
	<tr>
		<td>Exception</td>
		<td>Exception</td>
	</tr>
	
	<tr>
		<td>Validator</td>
		<td>Respect\Validation\Validator</td>
	</tr>
	
</table>

#### Authors

<table>
	<thead>
		<th>Name</th>
		<th>Handle</th>
		<th>Email</th>
	</thead>
	<tbody>
	
		<tr>
			<td>
				Matthew J. Sahagian
			</td>
			<td>
				mjs
			</td>
			<td>
				
			</td>
		</tr>
	
	</tbody>
</table>

## Properties
### Static Properties
#### <span style="color:#6a6e3d;">$defaultErrors</span>

Default errors corresponding to argumentless validation rules



### Instance Properties
#### <span style="color:#6a6e3d;">$rules</span>

Custom rules keyed by the rule name

#### <span style="color:#6a6e3d;">$errors</span>

List of error messages keyed by rule name

#### <span style="color:#6a6e3d;">$validator</span>

The internal validator

#### <span style="color:#6a6e3d;">$messages</span>

List of logged messages

#### <span style="color:#6a6e3d;">$children</span>

List of child inspectors




## Methods

### Instance Methods
<hr />

#### <span style="color:#3e6a6e;">add()</span>

Add a child inspector

###### Parameters

<table>
	<thead>
		<th>Name</th>
		<th>Type(s)</th>
		<th>Description</th>
	</thead>
	<tbody>
			
		<tr>
			<td>
				$reference
			</td>
			<td>
									<a href="http://php.net/language.types.string">string</a>
				
			</td>
			<td>
				The reference used to find or recall the child
			</td>
		</tr>
					
		<tr>
			<td>
				$child
			</td>
			<td>
									Inspector				
			</td>
			<td>
				The child instance
			</td>
		</tr>
			
	</tbody>
</table>

###### Returns

<dl>
	
		<dt>
			Inspector
		</dt>
		<dd>
			The object instance for method chaining
		</dd>
	
</dl>


<hr />

#### <span style="color:#3e6a6e;">check()</span>

Check data against a particular set of rules

###### Parameters

<table>
	<thead>
		<th>Name</th>
		<th>Type(s)</th>
		<th>Description</th>
	</thead>
	<tbody>
			
		<tr>
			<td>
				$key
			</td>
			<td>
									<a href="http://php.net/language.types.string">string</a>
				
			</td>
			<td>
				The key to use when logging error messages
			</td>
		</tr>
					
		<tr>
			<td>
				$data
			</td>
			<td>
									<a href="http://php.net/language.pseudo-types">mixed</a>
				
			</td>
			<td>
				The data to validate against the rules
			</td>
		</tr>
					
		<tr>
			<td>
				$rules
			</td>
			<td>
									<a href="http://php.net/language.types.array">array</a>
				
			</td>
			<td>
				The array of rules to check the data against
			</td>
		</tr>
			
	</tbody>
</table>

###### Returns

<dl>
	
		<dt>
			Inspector
		</dt>
		<dd>
			The object instance for method chaining
		</dd>
	
</dl>


<hr />

#### <span style="color:#3e6a6e;">countMessages()</span>

Count the number of validation messages (including registered children)

###### Returns

<dl>
	
		<dt>
			integer
		</dt>
		<dd>
			The number of error messages across this inspector and all its children
		</dd>
	
</dl>


<hr />

#### <span style="color:#3e6a6e;">define()</span>

Define a new rule and its related error messaging

###### Parameters

<table>
	<thead>
		<th>Name</th>
		<th>Type(s)</th>
		<th>Description</th>
	</thead>
	<tbody>
			
		<tr>
			<td>
				$rule
			</td>
			<td>
									<a href="http://php.net/language.types.string">string</a>
				
			</td>
			<td>
				The name of the rule to define
			</td>
		</tr>
					
		<tr>
			<td>
				$error
			</td>
			<td>
									<a href="http://php.net/language.types.string">string</a>
				
			</td>
			<td>
				The error message to log if the rule is violated
			</td>
		</tr>
			
	</tbody>
</table>

###### Returns

<dl>
	
		<dt>
			Validator
		</dt>
		<dd>
			A new respect validator instance for chaining rules
		</dd>
	
</dl>


<hr />

#### <span style="color:#3e6a6e;">getMessages()</span>

Get all the messages under a particular path.

###### Parameters

<table>
	<thead>
		<th>Name</th>
		<th>Type(s)</th>
		<th>Description</th>
	</thead>
	<tbody>
			
		<tr>
			<td>
				$path
			</td>
			<td>
									<a href="http://php.net/language.types.string">string</a>
				
			</td>
			<td>
				The path to the validation messages
			</td>
		</tr>
			
	</tbody>
</table>

###### Returns

<dl>
	
		<dt>
			array
		</dt>
		<dd>
			The list of validation messages based on violated rules
		</dd>
	
</dl>


<hr />

#### <span style="color:#3e6a6e;">run()</span>

The entry point for running validation

###### Parameters

<table>
	<thead>
		<th>Name</th>
		<th>Type(s)</th>
		<th>Description</th>
	</thead>
	<tbody>
			
		<tr>
			<td>
				$data
			</td>
			<td>
									<a href="http://php.net/language.pseudo-types">mixed</a>
				
			</td>
			<td>
				The data to validate
			</td>
		</tr>
			
	</tbody>
</table>

###### Returns

<dl>
	
		<dt>
			Inspector
		</dt>
		<dd>
			The object instance for method chaining
		</dd>
	
</dl>


<hr />

#### <span style="color:#3e6a6e;">setValidator()</span>

Set the internal validator (an instance of Respect\Validation)

###### Parameters

<table>
	<thead>
		<th>Name</th>
		<th>Type(s)</th>
		<th>Description</th>
	</thead>
	<tbody>
			
		<tr>
			<td>
				$validator
			</td>
			<td>
									Validator				
			</td>
			<td>
				The internal validator instance
			</td>
		</tr>
			
	</tbody>
</table>

###### Returns

<dl>
	
		<dt>
			Inspector
		</dt>
		<dd>
			The object instance for method chaining
		</dd>
	
</dl>


<hr />

#### <span style="color:#3e6a6e;">clear()</span>

Clear the messages, rules, and errors for this inspector (reset it back to defaults)

###### Returns

<dl>
	
		<dt>
			Inspector
		</dt>
		<dd>
			The object instance for method chaining
		</dd>
	
</dl>


<hr />

#### <span style="color:#3e6a6e;">fetch()</span>

Fetch a child inspector instance which was previously registered via `add()`

###### Parameters

<table>
	<thead>
		<th>Name</th>
		<th>Type(s)</th>
		<th>Description</th>
	</thead>
	<tbody>
			
		<tr>
			<td>
				$reference
			</td>
			<td>
									<a href="http://php.net/language.types.string">string</a>
				
			</td>
			<td>
				The reference under which the child inspector was added
			</td>
		</tr>
			
	</tbody>
</table>

###### Returns

<dl>
	
		<dt>
			Inspector
		</dt>
		<dd>
			The child inspector instance
		</dd>
	
</dl>


<hr />

#### <span style="color:#3e6a6e;">log()</span>

Log a message on this inspector

###### Parameters

<table>
	<thead>
		<th>Name</th>
		<th>Type(s)</th>
		<th>Description</th>
	</thead>
	<tbody>
			
		<tr>
			<td>
				$key
			</td>
			<td>
									<a href="http://php.net/language.types.string">string</a>
				
			</td>
			<td>
				The key under which to log the message.
			</td>
		</tr>
					
		<tr>
			<td>
				$message
			</td>
			<td>
									<a href="http://php.net/language.types.string">string</a>
				
			</td>
			<td>
				The message to log
			</td>
		</tr>
			
	</tbody>
</table>

###### Returns

<dl>
	
		<dt>
			Inspector
		</dt>
		<dd>
			The object instance for method chaining
		</dd>
	
</dl>


<hr />

#### <span style="color:#3e6a6e;">validate()</span>

Validate some data

###### Parameters

<table>
	<thead>
		<th>Name</th>
		<th>Type(s)</th>
		<th>Description</th>
	</thead>
	<tbody>
			
		<tr>
			<td>
				$data
			</td>
			<td>
									<a href="http://php.net/language.pseudo-types">mixed</a>
				
			</td>
			<td>
				The data to validate
			</td>
		</tr>
			
	</tbody>
</table>

###### Returns

<dl>
	
		<dt>
			void
		</dt>
		<dd>
			Provides no return value.
		</dd>
	
</dl>




