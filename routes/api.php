<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => 'api', 'prefix' => 'auth'], function() {
    Route::post('register', 'App\Http\Controllers\AuthController@register');
    Route::post('login', 'App\Http\Controllers\AuthController@login')->name('login');
    Route::post('logout', 'App\Http\Controllers\AuthController@logout');
    Route::post('refresh', 'App\Http\Controllers\AuthController@refresh');
    Route::get('me', 'App\Http\Controllers\AuthController@me');
});

Route::middleware('auth:api')->group(function() {
    /** Tasks */
    Route::get('/tasks', 'App\Http\Controllers\TaskController@getAll');
    Route::get('/tasks/search', 'App\Http\Controllers\TaskController@search');
    Route::get('/tasks/{id}', 'App\Http\Controllers\TaskController@getById');
    Route::get('/tasks/count/status', 'App\Http\Controllers\TaskController@getCountByStatus');
    Route::get('/tasks/init/form', 'App\Http\Controllers\TaskController@getInitialFormData');
    Route::post('/tasks', 'App\Http\Controllers\TaskController@store');
    Route::put('/tasks/{id}', 'App\Http\Controllers\TaskController@update');
    Route::delete('/tasks/{id}', 'App\Http\Controllers\TaskController@destroy');
    Route::put('/tasks/{id}/handle', 'App\Http\Controllers\TaskController@handle');

    /** Repairations */
    Route::get('/repairations', 'App\Http\Controllers\RepairationController@getAll');
    Route::get('/repairations/search', 'App\Http\Controllers\RepairationController@search');
    Route::get('/repairations/{id}', 'App\Http\Controllers\RepairationController@getById');
    Route::get('/repairations/asset/{assetId}', 'App\Http\Controllers\RepairationController@getByAsset');
    Route::get('/repairations/init/form', 'App\Http\Controllers\RepairationController@getInitialFormData');
    Route::post('/repairations', 'App\Http\Controllers\RepairationController@store');
    Route::put('/repairations/{id}', 'App\Http\Controllers\RepairationController@update');
    Route::put('/repairations/{id}/repair', 'App\Http\Controllers\RepairationController@repair');
    Route::delete('/repairations/{id}', 'App\Http\Controllers\RepairationController@destroy');

    /** Equipments */
    Route::get('/equipments', 'App\Http\Controllers\EquipmentController@getAll');
    Route::get('/equipments/{id}', 'App\Http\Controllers\EquipmentController@getById');
    Route::get('/equipments/init/form', 'App\Http\Controllers\EquipmentController@getInitialFormData');
    Route::post('/equipments', 'App\Http\Controllers\EquipmentController@store');

    Route::get('/equipment-types', 'App\Http\Controllers\EquipmentTypeController@getAll');

    Route::get('/equipment-groups', 'App\Http\Controllers\EquipmentGroupController@getAll');

    /** Assets */
    Route::get('/assets', 'App\Http\Controllers\AssetController@getAll');
    Route::get('/assets/search', 'App\Http\Controllers\AssetController@search');
    Route::get('/assets/{id}', 'App\Http\Controllers\AssetController@getById');
    Route::get('/assets/init/form', 'App\Http\Controllers\AssetController@getInitialFormData');
    Route::post('/assets', 'App\Http\Controllers\AssetController@store');
    Route::put('/assets/{id}', 'App\Http\Controllers\AssetController@update');
    Route::delete('/assets/{id}', 'App\Http\Controllers\AssetController@destroy');
    Route::post('/assets/{id}/upload', 'App\Http\Controllers\AssetController@uploadImage');

    /** Asset Ownerships */
    Route::get('/asset-ownerships', 'App\Http\Controllers\AssetOwnershipController@getAll');
    Route::get('/asset-ownerships/{id}', 'App\Http\Controllers\AssetOwnershipController@getById');
    Route::get('/asset-ownerships/asset/{id}', 'App\Http\Controllers\AssetOwnershipController@getByAsset');
    Route::get('/asset-ownerships/owner/{id}', 'App\Http\Controllers\AssetOwnershipController@getByOwner');
    Route::get('/asset-ownerships/init/form', 'App\Http\Controllers\AssetOwnershipController@getInitialFormData');
    Route::post('/asset-ownerships', 'App\Http\Controllers\AssetOwnershipController@store');

    Route::get('/asset-types', 'App\Http\Controllers\AssetTypeController@getAll');

    Route::get('/asset-categories', 'App\Http\Controllers\AssetCategoryController@getAll');

    /** Suppliers */
    Route::get('/suppliers', 'App\Http\Controllers\SupplierController@getAll');
    Route::get('/suppliers/search', 'App\Http\Controllers\SupplierController@search');
    Route::get('/suppliers/{id}', 'App\Http\Controllers\SupplierController@getById');
    Route::get('/suppliers/init/form', 'App\Http\Controllers\SupplierController@getInitialFormData');
    Route::post('/suppliers', 'App\Http\Controllers\SupplierController@store');

    /** Employees */
    Route::get('/employees', 'App\Http\Controllers\EmployeeController@getAll');
    Route::get('/employees/search', 'App\Http\Controllers\EmployeeController@search');
    Route::get('/employees/{id}', 'App\Http\Controllers\EmployeeController@getById');
    Route::get('/employees/init/form', 'App\Http\Controllers\EmployeeController@getInitialFormData');
    Route::post('/employees', 'App\Http\Controllers\EmployeeController@store');
    Route::post('/employees/{id}', 'App\Http\Controllers\EmployeeController@update');
    Route::delete('/employees/{id}', 'App\Http\Controllers\EmployeeController@destroy');
    Route::post('/employees/{id}/upload', 'App\Http\Controllers\EmployeeController@uploadAvatar');

    /** Departments */
    Route::get('/departments', 'App\Http\Controllers\DepartmentController@getAll');
    Route::get('/departments/{id}', 'App\Http\Controllers\DepartmentController@getById');
    Route::post('/departments', 'App\Http\Controllers\DepartmentController@store');
    Route::put('/departments/{id}', 'App\Http\Controllers\DepartmentController@update');
    Route::delete('/departments/{id}', 'App\Http\Controllers\DepartmentController@destroy');

    /** Divisions */
    Route::get('/divisions', 'App\Http\Controllers\DivisionController@getAll');
    Route::get('/divisions/{id}', 'App\Http\Controllers\DivisionController@getById');
    Route::get('/divisions/init/form', 'App\Http\Controllers\DivisionController@getInitialFormData');
    Route::post('/divisions', 'App\Http\Controllers\DivisionController@store');
    Route::put('/divisions/{id}', 'App\Http\Controllers\DivisionController@update');
    Route::delete('/divisions/{id}', 'App\Http\Controllers\DivisionController@destroy');

    /** Members */
    Route::get('/members', 'App\Http\Controllers\MemberController@getAll');
    // Route::get('/members/search', 'App\Http\Controllers\MemberController@search');
    Route::get('/members/{id}', 'App\Http\Controllers\MemberController@getById');
    Route::get('/members/employee/{employeeId}', 'App\Http\Controllers\MemberController@getByEmployee');
    // Route::get('/members/init/form', 'App\Http\Controllers\MemberController@getInitialFormData');
    Route::post('/members', 'App\Http\Controllers\MemberController@store');

    /** Rooms */
    Route::get('/rooms', 'App\Http\Controllers\RoomController@getAll');
    Route::get('/rooms/{id}', 'App\Http\Controllers\RoomController@getById');
    Route::post('/rooms', 'App\Http\Controllers\RoomController@store');
    Route::put('/rooms/{id}', 'App\Http\Controllers\RoomController@update');
    Route::delete('/rooms/{id}', 'App\Http\Controllers\RoomController@destroy');

    /** Units */
    Route::get('/units', 'App\Http\Controllers\UnitController@getAll');
    Route::get('/units/{id}', 'App\Http\Controllers\UnitController@getById');
    Route::post('/units', 'App\Http\Controllers\UnitController@store');
    Route::put('/units/{id}', 'App\Http\Controllers\UnitController@update');
    Route::delete('/units/{id}', 'App\Http\Controllers\UnitController@destroy');

    /** budgets */
    Route::get('/budgets', 'App\Http\Controllers\BudgetController@getAll');
    Route::get('/budgets/search', 'App\Http\Controllers\BudgetController@search');
    Route::get('/budgets/{id}', 'App\Http\Controllers\BudgetController@getById');
    Route::post('/budgets', 'App\Http\Controllers\BudgetController@store');
    Route::put('/budgets/{id}', 'App\Http\Controllers\BudgetController@update');
    Route::delete('/budgets/{id}', 'App\Http\Controllers\BudgetController@destroy');

    /** Items */
    Route::get('/items', 'App\Http\Controllers\ItemController@getAll');
    Route::get('/items/search', 'App\Http\Controllers\ItemController@search');
    Route::get('/items/{id}', 'App\Http\Controllers\ItemController@getById');
    Route::get('/items/init/form', 'App\Http\Controllers\ItemController@getInitialFormData');
    Route::post('/items', 'App\Http\Controllers\ItemController@store');
    Route::post('/items/{id}', 'App\Http\Controllers\ItemController@update');
    Route::delete('/items/{id}', 'App\Http\Controllers\ItemController@destroy');

    /** Requisitions */
    Route::get('/requisitions', 'App\Http\Controllers\RequisitionController@getAll');
    Route::get('/requisitions/search', 'App\Http\Controllers\RequisitionController@search');
    Route::get('/requisitions/{id}', 'App\Http\Controllers\RequisitionController@getById');
    Route::get('/requisitions/init/form', 'App\Http\Controllers\RequisitionController@getInitialFormData');
    Route::post('/requisitions', 'App\Http\Controllers\RequisitionController@store');
    Route::put('/requisitions/{id}', 'App\Http\Controllers\RequisitionController@update');
    Route::delete('/requisitions/{id}', 'App\Http\Controllers\RequisitionController@destroy');

    /** Orders */
    Route::get('/orders', 'App\Http\Controllers\OrderController@getAll');
    Route::get('/orders/search', 'App\Http\Controllers\OrderController@search');
    Route::get('/orders/{id}', 'App\Http\Controllers\OrderController@getById');
    Route::get('/orders/init/form', 'App\Http\Controllers\OrderController@getInitialFormData');
    Route::post('/orders', 'App\Http\Controllers\OrderController@store');
    Route::put('/orders/{id}', 'App\Http\Controllers\OrderController@update');
    Route::delete('/orders/{id}', 'App\Http\Controllers\OrderController@destroy');

    /** Inspections */
    Route::get('/inspections', 'App\Http\Controllers\InspectionController@getAll');
    Route::get('/inspections/search', 'App\Http\Controllers\InspectionController@search');
    Route::get('/inspections/{id}', 'App\Http\Controllers\InspectionController@getById');
    Route::get('/inspections/init/form', 'App\Http\Controllers\InspectionController@getInitialFormData');
    Route::post('/inspections', 'App\Http\Controllers\InspectionController@store');
    Route::put('/inspections/{id}', 'App\Http\Controllers\InspectionController@update');
    Route::delete('/inspections/{id}', 'App\Http\Controllers\InspectionController@destroy');
});

Route::get('/db-connection', function () {
    try {
        $dbconnect = \DB::connection()->getPDO();
        $dbname = \DB::connection()->getDatabaseName();

        echo "Connected successfully to the database. Database name is :".$dbname;
    } catch(Exception $e) {
        echo $e->getMessage();
    }
});
