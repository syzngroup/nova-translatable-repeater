<?php

namespace Syzn\Nova\TranslatableRepeater;

use Fourstacks\NovaRepeatableFields\Repeater;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;
use YesWeDev\Nova\Translatable\Translatable;

class TranslatableRepeater extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'nova-translatable-repeater';

    protected $locales = [];

    protected $repeater;

    protected $translatable;

    public function __construct($name, $attribute = null, $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback);

        $this->repeater = new Repeater($name, $attribute, $resolveCallback);

        $this->translatable = new Translatable($name, $attribute, $resolveCallback);

        $this->locales = array_map(function ($value) {
            return __($value);
        }, config('translatable.nova_locales'));

        $this->withMeta([
            'locales' => $this->locales,
            'indexLocale' => app()->getLocale()
        ]);
    }

    public function resolveAttribute($resource, $attribute = null)
    {
        $translations = $this->translatable->resolveAttribute($resource, $attribute);

        if (empty($translations)) {
            // Encode array with locales as keys and empty arrays as values.
            return json_encode(array_map(function ($value) {
                return [];
            }, $this->locales));
        } else {
            return json_encode($translations);
        }
    }

    /**
     * Hydrate the given attribute on the model based on the incoming request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $requestAttribute
     * @param  object  $model
     * @param  string  $attribute
     * @return void
     */
    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        if ($request->exists($requestAttribute)) {
            $requestValue = json_decode($request[$requestAttribute], true);
        }

        if (is_array($requestValue)) {
            foreach ($requestValue as $locale => $value) {
                // Filter empty entries.
                $filteredValue = array_filter(
                    array_map(function ($entry) {
                        return array_filter($entry);
                    }, $value)
                );

                $model->translateOrNew($locale)->{$attribute} = $filteredValue;
            }
        }
    }

    public function addField($fieldConfig)
    {
        $this->repeater->addField($fieldConfig);

        return $this->withMeta($this->repeater->meta());
    }

    public function addButtonText($text)
    {
        $this->repeater->addButtonText($text);

        return $this->withMeta($this->repeater->meta());
    }

    public function displayStackedForm()
    {
        $this->repeater->displayStackedForm();

        return $this->withMeta($this->repeater->meta());
    }

    public function summaryLabel($label)
    {
        $this->repeater->summaryLabel($label);

        return $this->withMeta($this->repeater->meta());
    }

    public function initialRows($count)
    {
        $this->repeater->initialRows($count);

        return $this->withMeta($this->repeater->meta());
    }

    public function maximumRows($count)
    {
        $this->repeater->maximumRows($count);

        return $this->withMeta($this->repeater->meta());
    }

    /**
     * Set the locales to display / edit.
     *
     * @param  array  $locales
     * @return $this
     */
    public function locales(array $locales)
    {
        $this->translatable->locales($locales);

        return $this->withMeta($this->translatable->meta());
    }

    /**
     * Set the locale to display on index.
     *
     * @param  string $locale
     * @return $this
     */
    public function indexLocale($locale)
    {
        $this->translatable->indexLocale($locale);

        return $this->withMeta($this->translatable->meta());
    }
}
