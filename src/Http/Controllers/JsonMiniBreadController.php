<?php

namespace JsonMiniBreadHook\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JsonMiniBreadHook\Events\JsonMiniBreadDataAdded;
use JsonMiniBreadHook\Events\JsonMiniBreadDataDeleted;
use JsonMiniBreadHook\Facades\JsonMiniBreadHookFacade;
use JsonMiniBreadHook\FormFields\JsonMiniBreadFormField;
use TCG\Voyager\Events\BreadDataAdded;
use TCG\Voyager\Events\BreadDataDeleted;
use TCG\Voyager\Events\BreadImagesDeleted;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\Controller;

class JsonMiniBreadController extends Controller
{
    public function index(Request $request, $id)
    {
        // GET THE SLUG, ex. 'posts', 'pages', etc.
        $slug = $this->getSlug($request);

        // GET THE DataType based on the slug
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('browse', app($dataType->model_name));

        $jsonMiniBreadFormField = new JsonMiniBreadFormField();

        $dataRow = $dataType->rows->where('type', $jsonMiniBreadFormField->getCodename())->first();

        $jsonMiniBreadHelper = JsonMiniBreadHookFacade::makeHelper($dataRow);

        $dataTypeContent = (strlen($dataType->model_name) != 0)
            ? app($dataType->model_name)->findOrFail($id)
            : DB::table($dataType->name)->where('id', $id)->first(); // If Model doest exist, get data from table name

        $jsonMiniBreadRows = json_decode($dataTypeContent->{$dataRow->field}) ?? [];

        $allFields = $jsonMiniBreadHelper->allFields();
        foreach ($jsonMiniBreadRows as $index => &$jsonMiniBreadRow) {
            foreach ($allFields as $field) {
                if (!property_exists($jsonMiniBreadRow, $field->field)) {
                    $jsonMiniBreadRow->{$field->field} = null;
                }
            }
            $jsonMiniBreadRow->id = $index;
            unset($jsonMiniBreadRow);
        }

        return view('json-mini-bread::mini-bread.browse', compact(
            'dataType',
            'dataRow',
            'dataTypeContent',
            'jsonMiniBreadRows',
            'jsonMiniBreadHelper'
        ));
    }

    public function store(Request $request, $id)
    {
        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('add', app($dataType->model_name));

        $jsonMiniBreadFormField = new JsonMiniBreadFormField();

        $dataRow = $dataType->rows->where('type', $jsonMiniBreadFormField->getCodename())->first();

        $jsonMiniBreadHelper = JsonMiniBreadHookFacade::makeHelper($dataRow);

        // Validate fields with ajax
        $val = $this->validateBread($request->all(), collect($jsonMiniBreadHelper->addFields()));

        if ($val->fails()) {
            return response()->json(['errors' => $val->messages()]);
        }

        if (!$request->has('_validate')) {
            $dataTypeContent = (strlen($dataType->model_name) != 0)
                ? app($dataType->model_name)->findOrFail($id)
                : DB::table($dataType->name)->where('id', $id)->first(); // If Model doest exist, get data from table name

            $data = $this->insertUpdateData($request, $slug, $jsonMiniBreadHelper->addFields(), (object)[]);

            $allData = json_decode($dataTypeContent->{$dataRow->field}, true) ?? [];
            do {
                $randomKey = str_random(3);
            } while (array_key_exists($randomKey, $allData));
            $allData[$randomKey] = $data;
            $dataTypeContent->{$dataRow->field} = json_encode($allData);

            $dataTypeContent->save();

            event(new JsonMiniBreadDataAdded($dataType, $dataTypeContent));
            event(new BreadDataAdded($dataType, $data));

            if ($request->ajax()) {
                return response()->json(['success' => true, 'data' => $data]);
            }

            $slugSingular = JsonMiniBreadHookFacade::getSlugSingular($slug);

            return redirect()
                ->route("voyager.{$dataType->slug}.mini.index", [$slugSingular => $id])
                ->with([
                    'message' => __('voyager::generic.successfully_added_new') . " {$dataType->display_name_singular}",
                    'alert-type' => 'success',
                ]);
        }
    }

//    TODO: Por alguna razón no se actualizan las checkbox (la de correcta)
    public function update(Request $request, $parentId, $childId)
    {
        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('edit', app($dataType->model_name));

        $jsonMiniBreadFormField = new JsonMiniBreadFormField();

        $dataRow = $dataType->rows->where('type', $jsonMiniBreadFormField->getCodename())->first();

        $jsonMiniBreadHelper = JsonMiniBreadHookFacade::makeHelper($dataRow);

        // Validate fields with ajax
        $val = $this->validateBread($request->all(), $jsonMiniBreadHelper->editFields());

        if ($val->fails()) {
            return response()->json(['errors' => $val->messages()]);
        }

        if (!$request->has('_validate')) {
            $dataTypeContent = (strlen($dataType->model_name) != 0)
                ? app($dataType->model_name)->findOrFail($parentId)
                : DB::table($dataType->name)->where('id', $parentId)->first(); // If Model doest exist, get data from table name

            $allData = json_decode($dataTypeContent->{$dataRow->field}) ?? (object)[];
            $oldData = $allData->{$childId};
            $data = $this->insertUpdateData($request, $slug, $jsonMiniBreadHelper->editFields(), (object)$oldData);

            $dataTypeContent->{$dataRow->field} = json_encode($allData);
            $dataTypeContent->save();

            if ($request->ajax()) {
                return response()->json(['success' => true, 'data' => $data]);
            }

            $slugSingular = JsonMiniBreadHookFacade::getSlugSingular($slug);

            return redirect()
                ->route("voyager.{$dataType->slug}.mini.index", [$slugSingular => $parentId])
                ->with([
                    'message' => __('voyager::generic.successfully_updated') . " {$dataType->display_name_singular}",
                    'alert-type' => 'success',
                ]);
        }
    }

    public function destroy(Request $request, $parentId, $childId)
    {
        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('delete', app($dataType->model_name));

        $jsonMiniBreadFormField = new JsonMiniBreadFormField();

        $dataRow = $dataType->rows->where('type', $jsonMiniBreadFormField->getCodename())->first();

        $jsonMiniBreadHelper = JsonMiniBreadHookFacade::makeHelper($dataRow);

        $dataTypeContent = (strlen($dataType->model_name) != 0)
            ? app($dataType->model_name)->findOrFail($parentId)
            : DB::table($dataType->name)->where('id', $parentId)->first(); // If Model doest exist, get data from table name

        // Init array of IDs
        $ids = [];
        if (empty($childId)) {
            // Bulk delete, get IDs from POST
            $ids = explode(',', $request->ids);
        } else {
            // Single item delete, get ID from URL
            $ids[] = $childId;
        }

        $allData = json_decode($dataTypeContent->{$dataRow->field}, true) ?? [];
        $allData = collect($allData);
        $allData = $allData->filter(function ($data, $index) use ($jsonMiniBreadHelper, $ids) {
            if (in_array($index, $ids)) {
                $this->cleanup($jsonMiniBreadHelper, $data);
                return false;
            }
            return true;
        });

        $dataTypeContent->{$dataRow->field} = json_encode($allData);

        $dataTypeContent->save();

        event(new JsonMiniBreadDataDeleted($dataType, $dataTypeContent));
        event(new BreadDataDeleted($dataType, $dataTypeContent));

        $slugSingular = JsonMiniBreadHookFacade::getSlugSingular($slug);

        return redirect()
            ->route("voyager.{$dataType->slug}.mini.index", [$slugSingular => $parentId])
            ->with([
                'message' => __('voyager::generic.successfully_deleted') . " {$dataType->display_name_singular}",
                'alert-type' => 'success',
            ]);
    }

    public function insertUpdateData($request, $slug, $rows, $data)
    {
        foreach ($rows as $row) {
            // if the field for this row is absent from the request, continue
            // checkboxes will be absent when unchecked, thus they are the exception
            if (!$request->hasFile($row->field) && !$request->has($row->field) && $row->type !== 'checkbox') {
                // if the field is a belongsToMany relationship, don't remove it
                // if no content is provided, that means the relationships need to be removed
                if ((isset($row->details->type) && $row->details->type !== 'belongsToMany') || $row->field !== 'user_belongsto_role_relationship') {
                    continue;
                }
            }

            $content = $this->getContentBasedOnType($request, $slug, $row, $row->details);

            if ($row->type == 'relationship' && $row->details->type != 'belongsToMany') {
                $row->field = @$row->details->column;
            }

            /*
             * merge ex_images and upload images
             */
            if ($row->type == 'multiple_images' && !is_null($content)) {
                if (isset($data->{$row->field})) {
                    $ex_files = json_decode($data->{$row->field}, true);
                    if (!is_null($ex_files)) {
                        $content = json_encode(array_merge($ex_files, json_decode($content)));
                    }
                }
            }

            if (is_null($content)) {

                // If the image upload is null and it has a current image keep the current image
                if ($row->type == 'image' && is_null($request->input($row->field)) && isset($data->{$row->field})) {
                    $content = $data->{$row->field};
                }

                // If the multiple_images upload is null and it has a current image keep the current image
                if ($row->type == 'multiple_images' && is_null($request->input($row->field)) && isset($data->{$row->field})) {
                    $content = $data->{$row->field};
                }

                // If the file upload is null and it has a current file keep the current file
                if ($row->type == 'file') {
                    $content = $data->{$row->field};
                }

                if ($row->type == 'password') {
                    $content = $data->{$row->field};
                }
            }

            $data->{$row->field} = $content;
        }

        return $data;
    }

    /**
     * Remove translations, images and files related to a BREAD item.
     *
     * @param \JsonMiniBreadHook\JsonMiniBreadHelper $dataType
     * @param array $data
     *
     * @return void
     */
    protected function cleanup($jsonMiniBreadHelper, $data)
    {
        // Delete Images
        $this->deleteBreadImages($data, $jsonMiniBreadHelper->deleteFields()->where('type', 'image'));

        // Delete Files
        foreach ($jsonMiniBreadHelper->deleteFields()->where('type', 'file') as $row) {
            if (isset($data[$row->field])) {
                foreach (json_decode($data[$row->field]) as $file) {
                    $this->deleteFileIfExists($file->download_link);
                }
            }
        }
    }

    /**
     * Delete all images related to a BREAD item.
     *
     * @param array $data
     * @param \Illuminate\Database\Eloquent\Model $fields
     *
     * @return void
     */
    public function deleteBreadImages($data, $fields)
    {
        $deletedImages = false;
        foreach ($fields as $row) {
            if (isset($data[$row->field])) {
                if ($data[$row->field] != config('voyager.user.default_avatar')) {
                    $this->deleteFileIfExists($data[$row->field]);
                }

                if (isset($row->details->thumbnails)) {
                    foreach ($row->details->thumbnails as $thumbnail) {
                        $ext = explode('.', $data[$row->field]);
                        $extension = '.' . $ext[count($ext) - 1];

                        $path = str_replace($extension, '', $data[$row->field]);

                        $thumb_name = $thumbnail->name;

                        $this->deleteFileIfExists($path . '-' . $thumb_name . $extension);
                    }
                }
                $deletedImages = true;
            }
        }

        if ($deletedImages) {
            event(new BreadImagesDeleted(collect($data), $fields));
        }
    }
}
