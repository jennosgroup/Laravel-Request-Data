<?php

namespace JennosGroup\LaravelRequestData;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

abstract class RequestData
{
    /**
     * The whitelist of attributes for all requests.
     */
    protected static array $attributes = [];

    /**
     * The whitelist of attributes for when a create request is being handled.
     * This is merged with the the $attributes property.
     */
    protected static array $createAttributes = [];

    /**
     * The whitelist of attributes for when an update request is being handled.
     * This is merged with the $attributes property.
     */
    protected static array $updateAttributes = [];

    /**
     * The default attributes for all requests.
     */
    protected static array $defaultAttributes = [];

    /**
     * The default attributes for when a create request is being handled.
     * This is merged with the $defaultAttributes property.
     */
    protected static array $createDefaultAttributes = [];

    /**
     * The default attributes for when an update request is being handled.
     * This is merged with the $defaultAttributes property.
     */
    protected static array $updateDefaultAttributes = [];

    /**
     * Flag to indicate if we are on a create request.
     */
    private static bool $isCreating = false;

    /**
     * Flag to indicate if we are on an update request.
     */
    private static bool $isUpdating = false;

    /**
     * The model to work with for the request.
     *
     * This will usually be for the update request as a model will not be
     * established as yet when creating.
     */
    private static ?Model $model = null;

    /**
     * Get the attributes when handling a create request.
     *
     * @param  array  $data
     * @param  Illuminate\Http\Request  $request
     *
     * @return array
     */
    public static function getForCreate(array $data, Request $request): array
    {
        static::$isCreating = true;

        // Get the data that is allowed
        $allowedData = array_intersect_key($data, array_flip(static::getCreateAttributes($data, $request)));

        // Merge any defaults with the allowed data
        $allowedData = array_merge(static::getCreateDefaultAttributes($data, $request), $allowedData);

        // Allows us to further manipulate the data.
        if (method_exists(static::class, 'compute')) {
            $allowedData = static::compute($data, $allowedData, $request);
        }

        // Allows us to further manipulate the data for create request. As you can see,
        // this takes precedence over the `compute` method.
        if (method_exists(static::class, 'computeForCreate')) {
            $allowedData = static::computeForCreate($data, $allowedData, $request);
        }

        return $allowedData;
    }

    /**
     * Get the attributes when handling an update request.
     *
     * @param  array  $data
     * @param  Illuminate\Database\Eloquent\Model  $model
     * @param  Illuminate\Http\Request  $request
     *
     * @return array
     */
    public static function getForUpdate(array $data, Model $model, Request $request): array
    {
        static::$isUpdating = true;
        static::$model = $model;

        // Get the data that is allowed
        $allowedData = array_intersect_key($data, array_flip(static::getUpdateAttributes($data, $model, $request)));

        // Merge any defaults with the allowed data
        $allowedData = array_merge(static::getUpdateDefaultAttributes($data, $model, $request), $allowedData);

        // Allows us to further manipulate the data.
        if (method_exists(static::class, 'compute')) {
            $allowedData = static::compute($data, $allowedData, $request);
        }

        // Allows us to further manipulate the data for update request. As you can see,
        // this takes precedence over the `compute` method.
        if (method_exists(static::class, 'computeForUpdate')) {
            $allowedData = static::computeForUpdate($data, $allowedData, $request);
        }

        return $allowedData;
    }

    /**
     * Get the whitelisted attributes for the create request.
     *
     * @param  array  $data
     * @param  Illuminate\Http\Request  $request
     *
     * @return array
     */
    public static function getCreateAttributes(array $data, Request $request): array
    {
        return array_merge(static::$attributes, static::$createAttributes);
    }

    /**
     * Get the whitelisted attributes for the update request.
     *
     * @param  array  $data
     * @param  Illuminate\Database\Eloquent\Model  $model
     * @param  Illuminate\Http\Request  $request
     *
     * @return array
     */
    public static function getUpdateAttributes(array $data, Model $model, Request $request): array
    {
        return array_merge(static::$attributes, static::$updateAttributes);
    }

    /**
     * Get the default attributes for the create request.
     *
     * @param  array  $data
     * @param  Illuminate\Http\Request  $request
     *
     * @return array
     */
    public static function getCreateDefaultAttributes(array $data, Request $request): array
    {
        return array_merge(static::$defaultAttributes, static::$createDefaultAttributes);
    }

    /**
     * Get the default attributes for the update request.
     *
     * @param  array  $data
     * @param  Illuminate\Database\Eloquent\Model  $model
     * @param  Illuminate\Http\Request  $request
     *
     * @return array
     */
    public static function getUpdateDefaultAttributes(array $data, Model $model, Request $request): array
    {
        return array_merge(static::$defaultAttributes, static::$updateDefaultAttributes);
    }

    /**
     * Check if we are on a create request.
     *
     * @return bool
     */
    public static function isCreating(): bool
    {
        return static::$isCreating;
    }

    /**
     * Check if we are on an update request.
     *
     * @return bool
     */
    public static function isUpdating(): bool
    {
        return static::$isUpdating;
    }

    /**
     * Get the model.
     *
     * @return Illuminate\Database\Eloquent\Model
     */
    public static function getModel(): ?Model
    {
        return static::$model;
    }
}