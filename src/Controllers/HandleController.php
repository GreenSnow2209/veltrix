<?php

namespace Veltrix\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Veltrix\Auth\Permission as Checker;
use Veltrix\Facades\Veltrix;

class HandleController extends Controller
{
    /**
     * Handle AJAX grid inline edits and switches.
     */
    public function handleAction(Request $request): JsonResponse
    {
        // Enforce strict authentication on all action executions
        if (!Veltrix::user()) {
            abort(403, 'Unauthorized action invocation: Authentication required');
        }

        $action = $request->input('_action');
        $modelClass = $request->input('_model');
        $id = $request->input('_id');

        // Inline switch toggle
        if ($action === 'switch') {
            $column = (string)$request->input('column');
            $val = $request->input('value');

            // Disallow toggling sensitive security columns
            $blockedColumns = ['password', 'remember_token', 'id', 'token'];
            if (in_array(strtolower($column), $blockedColumns, true)) {
                abort(403, 'Permission denied: mutating sensitive columns via inline switch is forbidden.');
            }

            if ($modelClass && class_exists($modelClass) && is_subclass_of($modelClass, \Illuminate\Database\Eloquent\Model::class)) {
                $record = $modelClass::findOrFail($id);
                $record->{$column} = $val;
                $record->save();

                return response()->json([
                    'status'  => true,
                    'message' => veltrix_trans('update_succeeded') ?: 'Updated successfully',
                ]);
            }

            return response()->json(['status' => false, 'message' => 'Invalid model'], 400);
        }

        // Custom action class handling
        if ($action && class_exists($action)) {
            // Prevent arbitrary code execution: action must be an Action subclass or explicitly permitted
            if (!is_subclass_of($action, \Veltrix\Actions\Action::class) && !is_subclass_of($action, 'Encore\Admin\Actions\Action')) {
                abort(403, 'Action invocation error: Action class must inherit from Veltrix\\Actions\\Action.');
            }

            $instance = new $action();

            if (method_exists($instance, 'authorize') && !$instance->authorize()) {
                abort(403, 'Permission denied to execute this action.');
            }

            if (method_exists($instance, 'handle')) {
                $result = app()->call([$instance, 'handle'], ['request' => $request]);

                if ($result instanceof \Veltrix\Actions\Response || (is_object($result) && method_exists($result, 'toArray'))) {
                    return response()->json($result->toArray());
                }

                return response()->json($result);
            }
        }

        return response()->json(['status' => false, 'message' => 'Action not handled'], 400);
    }

    public function handleForm(Request $request): JsonResponse
    {
        return response()->json(['status' => true]);
    }

    public function handleSelectable(Request $request): JsonResponse
    {
        return response()->json(['data' => []]);
    }

    public function handleRenderable(Request $request): JsonResponse
    {
        return response()->json(['html' => '']);
    }
}
