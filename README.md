# Laravel Request Data

## About 

A laravel package that fetches only the data you need for creating/updating your models.

## Installation

Install with composer, via command `composer require jennosgroup/laravel-request-data`.

## Getting Started

We recommend that you create a RequestData folder in your app directory. Then create an abstraction within that directory.

```php
<?php

namespace App\RequestData;

use JennosGroup\LaravelRequestData\RequestData as JennosGroupRequestData;

abstract class RequestData extends JennosGroupRequestData
{

}
```

Then you create the various classes, which extends your abstraction, to represent the domain you are working with.

```php
<?php

namespace App\RequestData;

class PostRequestData extends RequestData
{

}
```

## Usage

We have two implementation, which is used when creating or updating models.

When creating your model, in a typical store method of your controller, use the static `getForCreate` method, which accepts two arguments.

1) An array containing the data to work with i.e `$request->all()`.
2) The request instance.

When updating your model, in a typical update method of your controller, use the static `getForUpdate` method, which accepts three arguments.

1) An array containg the data to work with i.e `$request->all()`.
2) The model being updated
3) The request instance.


```php
<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\RequestData\PostRequestData;

class PostController extends Controller
{
	public function store(Request $request)
	{
		$data = PostRequestData::getForCreate($request->all(), $request);

		$post = Post::create($data);
	}

	public function update(Request $request, Post $post)
	{
		$data = PostRequestData::getForUpdate($request->all(), $post, $request);

		$post->fill($data)->save();
	}
}
```

## Defining Allowed Attributes

Declare a static array $attributes property on your class and list the attributes that are allowed. This will be the allowed attributes for the create and update request.

If you wish to define attributes only for create and update request, use the `$createAttributes` property or `$updateAttributes` property.

```php
<?php

namespace App\RequestData;

class PostRequestData extends RequestData
{
	/**
	 * The attributes allowed for create an update. These will always be present.
	 */
	protected static array $attributes = ['title', 'slug', 'content', 'author_id'];

	/**
	 * The attributes allowed when create. These will be merged with the $attributes
	 */
	protected static array $createAttributes - ['title', 'slug', 'content', 'author_id'];

	/**
	 * The attributes allowed when updating. These will be merged with the $attributes.
	 */
	protected static array $updateAttributes - ['title', 'slug', 'content'];
}
```

## Default Values

If you want default values to be added to attributes that are not present on request, you can add them to the `$defaultAttributes` property. As per convention, you can declare defaults only for create or update request by defining them on the `$createDefaultAttributes` and `$updateDefaultAttributes`.

```php
<?php

namespace App\RequestData;

class PostRequestData extends RequestData
{
	/**
	 * The default attributes for the create an update request, if no value was submitted for an attribute.
	 */
	protected static array $defaultAttributes = [
		'options' => [],
	];

	/**
	 * The default attributes for the create request, if no value was submitted for an attribute.
	 */
	protected static array $createDefaultAttributes - [
		// ...
	];

	/**
	 * The attributes allowed when updating. These will be merged with the $attributes.
	 */
	protected static array $updateDefaultAttributes - [
		// ...
	];
}
```

If your default attributes are complex to define in the properties, you can return an array from a static `getCreateDefaultAttributes` or static `getUpdateDefaultAttributes`.

`getCreateDefaultAttributes` method is passed 2 arguments.
1) $data - An array containing the data.
2) $request - The request instance.

`getUpdateDefaultAttributes` method is passed 3 arguments.
1) $data - An array containing the data.
2) $model - The model for the update
3) $request - The request instance.

## Computing Data

You may wish to manipulate the request data before output. You may do so by defining the following methods:

1) A static `compute` method. This will work for both creating/updating requests.
2) A static `computeForCreate` method. This only works for the create requests.
3) A static `computeForUpdate` method. This only works for the update requests.

3 arguments are passed which are:

1) $data - The initial unfiltered data that was passed through the `getForCreate` or `getForUpdate` method.
2) $allowedData - The data that is allowed. This is taken from the allowed attributes list.
3) $request - the Illuminate\Http\Request instance.

All the methods should return an array containing the data to return.

When updating, you can access the model by using the static `getModel` method.

If you choose to compute data by defining a static `compute` method, you can determine if we are on a create or update request by calling the static `isCreating` and static `isUpdating` methods.

