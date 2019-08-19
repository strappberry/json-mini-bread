<?php

namespace JsonMiniBreadHook;

use Illuminate\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use JsonMiniBreadHook\Actions\ShowJsonMiniBreadAction;
use JsonMiniBreadHook\Facades\JsonMiniBreadHookFacade;
use JsonMiniBreadHook\FormFields\JsonMiniBreadFormField;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Models\DataType;

class JsonMiniBreadHookServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(realpath(__DIR__ . '/../resources/views'), 'json-mini-bread');

        $this->loadTranslationsFrom(realpath(__DIR__ . '/../resources/lang'), 'json-mini-bread');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Get the from field, this contains the Codename which is saved in the DataRows
        $jsonMiniBreadModel = new JsonMiniBreadFormField();

        // Listen to when de Voyager is setting the administrator routes
        app(Dispatcher::class)->listen('voyager.admin.routing', function ($router) use ($jsonMiniBreadModel) {
            try {
                // Get all data rows which type is equal to the JsonMiniBread codename
                $dataRowsWithTypeJsonMiniBread = Voyager::model('Data_Row')->where('type', $jsonMiniBreadModel->getCodename());
                if (!$dataRowsWithTypeJsonMiniBread->exists()) {
                    return;
                }

                // Get all Data Rows
                $dataRows = $dataRowsWithTypeJsonMiniBread->get();
                foreach ($dataRows as $dataRow) {
                    // For each data row, get the related data type
                    $dataType = Voyager::model('Data_Type')->where('id', $dataRow->data_type_id)->first();
                    if (!$dataType) {
                        return;
                    }
                    $dataTypeSlug = $dataType->slug;
                    $dataTypeSlugSingular = JsonMiniBreadHookFacade::getSlugSingular($dataType->slug);
                    $miniBreadSlug = kebab_case($dataRow->display_name);
                    $router->get("{$dataTypeSlug}/{{$dataTypeSlugSingular}}/{$miniBreadSlug}/", '\\JsonMiniBreadHook\\Http\\Controllers\\JsonMiniBreadController@index')->name("{$dataTypeSlug}.mini.index");
                    $router->post("{$dataTypeSlug}/{{$dataTypeSlugSingular}}/{$miniBreadSlug}/", '\\JsonMiniBreadHook\\Http\\Controllers\\JsonMiniBreadController@store')->name("{$dataTypeSlug}.mini.store");
                    $router->put("{$dataTypeSlug}/{{$dataTypeSlugSingular}}/{$miniBreadSlug}/{id}", '\\JsonMiniBreadHook\\Http\\Controllers\\JsonMiniBreadController@update')->name("{$dataTypeSlug}.mini.update");
                    $router->delete("{$dataTypeSlug}/{{$dataTypeSlugSingular}}/{$miniBreadSlug}/{id}", '\\JsonMiniBreadHook\\Http\\Controllers\\JsonMiniBreadController@destroy')->name("{$dataTypeSlug}.mini.destroy");
                }
            } catch (\Exception $ex) {
            }
        });

        // Add the JsonMiniBreadFromField to Voyager
        Voyager::addFormField(JsonMiniBreadFormField::class);

        // When the browse bread is loading (only applies to the generic one, no custom ones)
        // we see if the DataType's DataRows contain the JsonMiniBread in their 'data' column, if so, we add the
        // "ShowJsonMiniBread" Action button
        Voyager::onLoadingView('voyager::bread.browse', function ($name, $parameters) use ($jsonMiniBreadModel) {
            if (isset($parameters['dataType'])) {
                /** @var DataType $dataType */
                $dataType = $parameters['dataType'];
                $jsonMiniBreadRowsQuery = $dataType->rows()->where('type', $jsonMiniBreadModel->getCodename());
                if (!$jsonMiniBreadRowsQuery->exists()) {
                    return;
                }
                Voyager::addAction(ShowJsonMiniBreadAction::class);
            }
        });

        // Listen to when the BREAD edit-add is loading and set the view listener to inject a script to handle our json mini bread
        Voyager::onLoadingView('voyager::tools.bread.edit-add', function () {
            app(Dispatcher::class)->listen('composing: voyager::master', function () {
                view('json-mini-bread::tools.bread.json-mini-bread-scripts')->render();
            });
        });
    }
}
