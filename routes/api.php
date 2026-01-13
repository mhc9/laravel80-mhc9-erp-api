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

/**
 * ===============================
 * Public routes
 * ===============================
 */
Route::post('/forgot-password', [App\Http\Controllers\ForgotPasswordController::class, 'forgotPassword']);
Route::post('/verify/pin', [App\Http\Controllers\ForgotPasswordController::class, 'verifyPin']);
Route::post( '/reset-password', [App\Http\Controllers\ResetPasswordController::class, 'resetPassword']);

Route::group(['middleware' => 'api', 'prefix' => 'auth'], function() {
    Route::post('/register', [App\Http\Controllers\AuthController::class, 'register']);
    Route::post('/login', [App\Http\Controllers\AuthController::class, 'login'])->name('login');
    Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout']);
    Route::post('/refresh', [App\Http\Controllers\AuthController::class, 'refresh']);
    Route::get('/me', [App\Http\Controllers\AuthController::class, 'me']);
});

/** Test db connection */
Route::get('/db-connection', function () {
    try {
        $dbconnect = \DB::connection()->getPDO();
        $dbname = \DB::connection()->getDatabaseName();

        echo "Connected successfully to the database. Database name is :".$dbname;
    } catch(Exception $e) {
        echo $e->getMessage();
    }
});

/**
 * ===============================
 * Authenticated routes
 * ===============================
 */
Route::middleware('auth:api')->group(function() {
    /** System */
    Route::get('system', [App\Http\Controllers\SystemController::class, 'getAll']);

    /** Password */
    Route::post( '/change-password', [App\Http\Controllers\ResetPasswordController::class, 'changePassword']);

    /** Users */
    Route::get( '/users/search', [App\Http\Controllers\UserController::class, 'search']);
    Route::get( '/users', [App\Http\Controllers\UserController::class, 'getAll']);
    Route::get( '/users/{id}', [App\Http\Controllers\UserController::class, 'getById']);
    Route::get('/users/init/form', [App\Http\Controllers\UserController::class, 'getInitialFormData']);
    Route::post( '/users/{id}', [App\Http\Controllers\UserController::class, 'store']);
    Route::post( '/users/{id}/update', [App\Http\Controllers\UserController::class, 'update']);
    Route::post( '/users/{id}/delete', [App\Http\Controllers\UserController::class, 'destroy']);
    Route::post( '/users/{id}/send-mail', [App\Http\Controllers\UserController::class, 'sendMail']);

    /** Tasks */
    Route::get('/tasks', 'App\Http\Controllers\TaskController@getAll');
    Route::get('/tasks/search', 'App\Http\Controllers\TaskController@search');
    Route::get('/tasks/{id}', 'App\Http\Controllers\TaskController@getById');
    Route::get('/tasks/count/status', 'App\Http\Controllers\TaskController@getCountByStatus');
    Route::get('/tasks/init/form', 'App\Http\Controllers\TaskController@getInitialFormData');
    Route::post('/tasks', 'App\Http\Controllers\TaskController@store');
    Route::post('/tasks/{id}/update', 'App\Http\Controllers\TaskController@update');
    Route::post('/tasks/{id}/delete', 'App\Http\Controllers\TaskController@destroy');
    Route::post('/tasks/{id}/handle', 'App\Http\Controllers\TaskController@handle');

    /** Repairations */
    Route::get('/repairations', 'App\Http\Controllers\RepairationController@getAll');
    Route::get('/repairations/search', 'App\Http\Controllers\RepairationController@search');
    Route::get('/repairations/{id}', 'App\Http\Controllers\RepairationController@getById');
    Route::get('/repairations/asset/{assetId}', 'App\Http\Controllers\RepairationController@getByAsset');
    Route::get('/repairations/init/form', 'App\Http\Controllers\RepairationController@getInitialFormData');
    Route::post('/repairations', 'App\Http\Controllers\RepairationController@store');
    Route::post('/repairations/{id}/update', 'App\Http\Controllers\RepairationController@update');
    Route::post('/repairations/{id}/repair', 'App\Http\Controllers\RepairationController@repair');
    Route::post('/repairations/{id}/delete', 'App\Http\Controllers\RepairationController@destroy');

    /** Computer Sets */
    Route::get('/comsets', 'App\Http\Controllers\ComsetController@getAll');
    Route::get('/comsets/search', 'App\Http\Controllers\ComsetController@search');
    Route::get('/comsets/{id}', 'App\Http\Controllers\ComsetController@getById');
    Route::get('/comsets/init/form', 'App\Http\Controllers\ComsetController@getInitialFormData');
    Route::post('/comsets', 'App\Http\Controllers\ComsetController@store');
    Route::post('/comsets/{id}/update', 'App\Http\Controllers\ComsetController@update');

    /** Assets */
    Route::get('/assets', 'App\Http\Controllers\AssetController@getAll');
    Route::get('/assets/search', 'App\Http\Controllers\AssetController@search');
    Route::get('/assets/{id}', 'App\Http\Controllers\AssetController@getById');
    Route::get('/assets/init/form', 'App\Http\Controllers\AssetController@getInitialFormData');
    Route::post('/assets', 'App\Http\Controllers\AssetController@store');
    Route::post('/assets/{id}/update', 'App\Http\Controllers\AssetController@update');
    Route::post('/assets/{id}/delete', 'App\Http\Controllers\AssetController@destroy');
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
    Route::get('/asset-categories/init/form', 'App\Http\Controllers\AssetCategoryController@getInitialFormData');

    /** Suppliers */
    Route::get('/suppliers', 'App\Http\Controllers\SupplierController@getAll');
    Route::get('/suppliers/search', 'App\Http\Controllers\SupplierController@search');
    Route::get('/suppliers/{id}', 'App\Http\Controllers\SupplierController@getById');
    Route::get('/suppliers/init/form', 'App\Http\Controllers\SupplierController@getInitialFormData');
    Route::post('/suppliers', 'App\Http\Controllers\SupplierController@store');

    /** Employees */
    Route::get('/employees', [App\Http\Controllers\EmployeeController::class, 'getAll']);
    Route::get('/employees/search', [App\Http\Controllers\EmployeeController::class, 'search']);
    Route::get('/employees/{id}', [App\Http\Controllers\EmployeeController::class, 'getById']);
    Route::get('/employees/init/form', [App\Http\Controllers\EmployeeController::class, 'getInitialFormData']);
    Route::post('/employees', [App\Http\Controllers\EmployeeController::class, 'store']);
    Route::post('/employees/{id}/update', [App\Http\Controllers\EmployeeController::class, 'update']);
    Route::post('/employees/{id}/delete', [App\Http\Controllers\EmployeeController::class, 'destroy']);
    Route::post('/employees/{id}/upload', [App\Http\Controllers\EmployeeController::class, 'uploadAvatar']);
    Route::post('/employees/{id}/update/descriptor', [App\Http\Controllers\EmployeeController::class, 'updateDescriptor']);

    /** Departments */
    Route::get('/departments', 'App\Http\Controllers\DepartmentController@getAll');
    Route::get('/departments/{id}', 'App\Http\Controllers\DepartmentController@getById');
    Route::post('/departments', 'App\Http\Controllers\DepartmentController@store');
    Route::post('/departments/{id}/update', 'App\Http\Controllers\DepartmentController@update');
    Route::post('/departments/{id}/delete', 'App\Http\Controllers\DepartmentController@destroy');

    /** Divisions */
    Route::get('/divisions', 'App\Http\Controllers\DivisionController@getAll');
    Route::get('/divisions/{id}', 'App\Http\Controllers\DivisionController@getById');
    Route::get('/divisions/init/form', 'App\Http\Controllers\DivisionController@getInitialFormData');
    Route::post('/divisions', 'App\Http\Controllers\DivisionController@store');
    Route::post('/divisions/{id}/update', 'App\Http\Controllers\DivisionController@update');
    Route::post('/divisions/{id}/delete', 'App\Http\Controllers\DivisionController@destroy');

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
    Route::post('/rooms/{id}/update', 'App\Http\Controllers\RoomController@update');
    Route::post('/rooms/{id}/delete', 'App\Http\Controllers\RoomController@destroy');

    /** Units */
    Route::get('/units', 'App\Http\Controllers\UnitController@getAll');
    Route::get('/units/{id}', 'App\Http\Controllers\UnitController@getById');
    Route::post('/units', 'App\Http\Controllers\UnitController@store');
    Route::post('/units/{id}/update', 'App\Http\Controllers\UnitController@update');
    Route::post('/units/{id}/delete', 'App\Http\Controllers\UnitController@destroy');

    /** budget-plans */
    Route::get('/budget-plans', [App\Http\Controllers\BudgetPlanController::class, 'getAll']);
    Route::get('/budget-plans/search', [App\Http\Controllers\BudgetPlanController::class, 'search']);
    Route::get('/budget-plans/{id}', [App\Http\Controllers\BudgetPlanController::class, 'getById']);
    Route::get('/budget-plans/init/form', [App\Http\Controllers\BudgetPlanController::class, 'getInitialFormData']);
    Route::post('/budget-plans', [App\Http\Controllers\BudgetPlanController::class, 'store']);
    Route::post('/budget-plans/{id}/update', [App\Http\Controllers\BudgetPlanController::class, 'update']);
    Route::post('/budget-plans/{id}/delete', [App\Http\Controllers\BudgetPlanController::class, 'destroy']);

    /** budget-projects */
    Route::get('/budget-projects', [App\Http\Controllers\BudgetProjectController::class, 'getAll']);
    Route::get('/budget-projects/search', [App\Http\Controllers\BudgetProjectController::class, 'search']);
    Route::get('/budget-projects/{id}', [App\Http\Controllers\BudgetProjectController::class, 'getById']);
    Route::get('/budget-projects/init/form', [App\Http\Controllers\BudgetProjectController::class, 'getInitialFormData']);
    Route::post('/budget-projects', [App\Http\Controllers\BudgetProjectController::class, 'store']);
    Route::post('/budget-projects/{id}/update', [App\Http\Controllers\BudgetProjectController::class, 'update']);
    Route::post('/budget-projects/{id}/delete', [App\Http\Controllers\BudgetProjectController::class, 'destroy']);

    /** budget-activities */
    Route::get('/budget-activities', [App\Http\Controllers\BudgetActivityController::class, 'getAll']);
    Route::get('/budget-activities/search', [App\Http\Controllers\BudgetActivityController::class, 'search']);
    Route::get('/budget-activities/{id}', [App\Http\Controllers\BudgetActivityController::class, 'getById']);
    Route::get('/budget-activities/init/form', [App\Http\Controllers\BudgetActivityController::class, 'getInitialFormData']);
    Route::post('/budget-activities', [App\Http\Controllers\BudgetActivityController::class, 'store']);
    Route::post('/budget-activities/{id}/update', [App\Http\Controllers\BudgetActivityController::class, 'update']);
    Route::post('/budget-activities/{id}/delete', [App\Http\Controllers\BudgetActivityController::class, 'destroy']);
    Route::post('/budget-activities/{id}/toggle', [App\Http\Controllers\BudgetActivityController::class, 'toggle']);

    /** budget-allocations */
    Route::get('/budget-allocations', [App\Http\Controllers\BudgetAllocationController::class, 'getAll']);
    Route::get('/budget-allocations/search', [App\Http\Controllers\BudgetAllocationController::class, 'search']);
    Route::get('/budget-allocations/{id}', [App\Http\Controllers\BudgetAllocationController::class, 'getById']);
    Route::get('/budget-allocations/budget/{id}', [App\Http\Controllers\BudgetAllocationController::class, 'getByBudget']);
    Route::get('/budget-allocations/init/form', [App\Http\Controllers\BudgetAllocationController::class, 'getInitialFormData']);
    Route::post('/budget-allocations', [App\Http\Controllers\BudgetAllocationController::class, 'store']);
    Route::post('/budget-allocations/{id}/update', [App\Http\Controllers\BudgetAllocationController::class, 'update']);
    Route::post('/budget-allocations/{id}/delete', [App\Http\Controllers\BudgetAllocationController::class, 'destroy']);

    /** budgets */
    Route::get('/budgets', [App\Http\Controllers\BudgetController::class, 'getAll']);
    Route::get('/budgets/search', [App\Http\Controllers\BudgetController::class, 'search']);
    Route::get('/budgets/{id}', [App\Http\Controllers\BudgetController::class, 'getById']);
    Route::get('/budgets/init/form', [App\Http\Controllers\BudgetController::class, 'getInitialFormData']);
    Route::post('/budgets', [App\Http\Controllers\BudgetController::class, 'store']);
    Route::post('/budgets/{id}/update', [App\Http\Controllers\BudgetController::class, 'update']);
    Route::post('/budgets/{id}/delete', [App\Http\Controllers\BudgetController::class, 'destroy']);
    Route::post('/budgets/{id}/toggle', [App\Http\Controllers\BudgetController::class, 'toggle']);

    /** projects */
    Route::get('/projects', [App\Http\Controllers\ProjectController::class, 'getAll']);
    Route::get('/projects/search', [App\Http\Controllers\ProjectController::class, 'search']);
    Route::get('/projects/{id}', [App\Http\Controllers\ProjectController::class, 'getById']);
    Route::get('/projects/init/form', [App\Http\Controllers\ProjectController::class, 'getInitialFormData']);
    Route::post('/projects', [App\Http\Controllers\ProjectController::class, 'store']);
    Route::post('/projects/{id}/update', [App\Http\Controllers\ProjectController::class, 'update']);
    Route::post('/projects/{id}/delete', [App\Http\Controllers\ProjectController::class, 'destroy']);

    /** Items */
    Route::get('/items', 'App\Http\Controllers\ItemController@getAll');
    Route::get('/items/search', 'App\Http\Controllers\ItemController@search');
    Route::get('/items/{id}', 'App\Http\Controllers\ItemController@getById');
    Route::get('/items/init/form', 'App\Http\Controllers\ItemController@getInitialFormData');
    Route::post('/items', 'App\Http\Controllers\ItemController@store');
    Route::post('/items/{id}/update', 'App\Http\Controllers\ItemController@update');
    Route::post('/items/{id}/delete', 'App\Http\Controllers\ItemController@destroy');
    Route::post('/items/{id}/upload', 'App\Http\Controllers\ItemController@uploadImage');

    /** Requisitions */
    Route::get('/requisitions', 'App\Http\Controllers\RequisitionController@getAll');
    Route::get('/requisitions/search', 'App\Http\Controllers\RequisitionController@search');
    Route::get('/requisitions/{id}', 'App\Http\Controllers\RequisitionController@getById');
    Route::get('/requisitions/{id}/with', 'App\Http\Controllers\RequisitionController@getByIdWithHeadOfDepart');
    Route::get('/requisitions/report/data', 'App\Http\Controllers\RequisitionController@getSummary');
    Route::get('/requisitions/init/form', 'App\Http\Controllers\RequisitionController@getInitialFormData');
    Route::post('/requisitions', 'App\Http\Controllers\RequisitionController@store');
    Route::post('/requisitions/{id}/update', 'App\Http\Controllers\RequisitionController@update');
    Route::post('/requisitions/{id}/delete', 'App\Http\Controllers\RequisitionController@destroy');

    /** Approval */
    Route::get('/approvals', 'App\Http\Controllers\ApprovalController@getAll');
    Route::get('/approvals/search', 'App\Http\Controllers\ApprovalController@search');
    Route::get('/approvals/{id}', 'App\Http\Controllers\ApprovalController@getById');
    Route::get('/approvals/init/form', 'App\Http\Controllers\ApprovalController@getInitialFormData');
    Route::post('/approvals', 'App\Http\Controllers\ApprovalController@store');
    Route::post('/approvals/{id}/update', 'App\Http\Controllers\ApprovalController@update');
    Route::post('/approvals/{id}/delete', 'App\Http\Controllers\ApprovalController@destroy');
    Route::post('/approvals/{id}/consider', 'App\Http\Controllers\ApprovalController@consider');

    /** Orders */
    Route::get('/orders', 'App\Http\Controllers\OrderController@getAll');
    Route::get('/orders/search', 'App\Http\Controllers\OrderController@search');
    Route::get('/orders/{id}', 'App\Http\Controllers\OrderController@getById');
    Route::get('/orders/init/form', 'App\Http\Controllers\OrderController@getInitialFormData');
    Route::post('/orders', 'App\Http\Controllers\OrderController@store');
    Route::post('/orders/{id}/update', 'App\Http\Controllers\OrderController@update');
    Route::post('/orders/{id}/delete', 'App\Http\Controllers\OrderController@destroy');

    /** Inspections */
    Route::get('/inspections', 'App\Http\Controllers\InspectionController@getAll');
    Route::get('/inspections/search', 'App\Http\Controllers\InspectionController@search');
    Route::get('/inspections/{id}', 'App\Http\Controllers\InspectionController@getById');
    Route::get('/inspections/init/form', 'App\Http\Controllers\InspectionController@getInitialFormData');
    Route::post('/inspections', 'App\Http\Controllers\InspectionController@store');
    Route::post('/inspections/{id}/update', 'App\Http\Controllers\InspectionController@update');
    Route::post('/inspections/{id}/delete', 'App\Http\Controllers\InspectionController@destroy');

    /** Loans */
    Route::get('/loans', [App\Http\Controllers\LoanController::class, 'getAll']);
    Route::get('/loans/search', [App\Http\Controllers\LoanController::class, 'search']);
    Route::get('/loans/{id}', [App\Http\Controllers\LoanController::class, 'getById']);
    Route::get('/loans/init/form', [App\Http\Controllers\LoanController::class, 'getInitialFormData']);
    Route::post('/loans', [App\Http\Controllers\LoanController::class, 'store']);
    Route::post('/loans/{id}/update', [App\Http\Controllers\LoanController::class, 'update']);
    Route::post('/loans/{id}/delete', [App\Http\Controllers\LoanController::class, 'destroy']);

    /** Loan Contracts */
    Route::get('/loan-contracts', [App\Http\Controllers\LoanContractController::class, 'getAll']);
    Route::get('/loan-contracts/search', [App\Http\Controllers\LoanContractController::class, 'search']);
    Route::get('/loan-contracts/{id}', [App\Http\Controllers\LoanContractController::class, 'getById']);
    Route::get('/loan-contracts/report/{year}', [App\Http\Controllers\LoanContractController::class, 'getReport']);
    Route::get('/loan-contracts/init/form', [App\Http\Controllers\LoanContractController::class, 'getInitialFormData']);
    Route::post('/loan-contracts', [App\Http\Controllers\LoanContractController::class, 'store']);
    Route::post('/loan-contracts/{id}/update', [App\Http\Controllers\LoanContractController::class, 'update']);
    Route::post('/loan-contracts/{id}/delete', [App\Http\Controllers\LoanContractController::class, 'destroy']);
    Route::post('/loan-contracts/{id}/approve', [App\Http\Controllers\LoanContractController::class, 'approve']);
    Route::post('/loan-contracts/{id}/deposit', [App\Http\Controllers\LoanContractController::class, 'deposit']);
    Route::post('/loan-contracts/{id}/cancel', [App\Http\Controllers\LoanContractController::class, 'cancel']);

    /** Loan Refunds */
    Route::get('/loan-refunds', [App\Http\Controllers\LoanRefundController::class, 'getAll']);
    Route::get('/loan-refunds/search', [App\Http\Controllers\LoanRefundController::class, 'search']);
    Route::get('/loan-refunds/{id}', [App\Http\Controllers\LoanRefundController::class, 'getById']);
    // Get latest bill no
    Route::get('/loan-refunds/bill/no', [App\Http\Controllers\LoanRefundController::class, 'getLatestBillNo']);
    Route::get('/loan-refunds/init/form', [App\Http\Controllers\LoanRefundController::class, 'getInitialFormData']);
    Route::post('/loan-refunds', [App\Http\Controllers\LoanRefundController::class, 'store']);
    Route::post('/loan-refunds/{id}/update', [App\Http\Controllers\LoanRefundController::class, 'update']);
    Route::post('/loan-refunds/{id}/delete', [App\Http\Controllers\LoanRefundController::class, 'destroy']);
    Route::post('/loan-refunds/{id}/approve', [App\Http\Controllers\LoanRefundController::class, 'approve']);
    Route::post('/loan-refunds/{id}/receipt', [App\Http\Controllers\LoanRefundController::class, 'receipt']);

    /** Places */
    Route::get('/places', [App\Http\Controllers\PlaceController::class, 'getAll']);
    Route::get('/places/search', [App\Http\Controllers\PlaceController::class, 'search']);
    Route::get('/places/{id}', [App\Http\Controllers\PlaceController::class, 'getById']);
    Route::get('/places/init/form', [App\Http\Controllers\PlaceController::class, 'getInitialFormData']);
    Route::post('/places', [App\Http\Controllers\PlaceController::class, 'store']);
    Route::post('/places/{id}/update', [App\Http\Controllers\PlaceController::class, 'update']);
    Route::post('/places/{id}/delete', [App\Http\Controllers\PlaceController::class, 'destroy']);

    /** Agencies */
    Route::get('/agencies', [App\Http\Controllers\AgencyController::class, 'getAll']);
    Route::get('/agencies/search', [App\Http\Controllers\AgencyController::class, 'search']);
    Route::get('/agencies/{id}', [App\Http\Controllers\AgencyController::class, 'getById']);
    Route::get('/agencies/init/form', [App\Http\Controllers\AgencyController::class, 'getInitialFormData']);
    Route::post('/agencies', [App\Http\Controllers\AgencyController::class, 'store']);
    Route::post('/agencies/{id}/update', [App\Http\Controllers\AgencyController::class, 'update']);
    Route::post('/agencies/{id}/delete', [App\Http\Controllers\AgencyController::class, 'destroy']);

    /** Vehicles */
    // Route::get( '/vehicles/search', [App\Http\Controllers\VehicleController::class, 'search']);
    // Route::get( '/vehicles', [App\Http\Controllers\VehicleController::class, 'getAll']);
    // Route::get( '/vehicles/{id}', [App\Http\Controllers\VehicleController::class, 'getById']);
    // Route::get('/vehicles/init/form', [App\Http\Controllers\VehicleController::class, 'getInitialFormData']);
    // Route::post( '/vehicles', [App\Http\Controllers\VehicleController::class, 'store']);
    // Route::post( '/vehicles/{id}/update', [App\Http\Controllers\VehicleController::class, 'update']);
    // Route::post( '/vehicles/{id}/delete', [App\Http\Controllers\VehicleController::class, 'destroy']);
    // Route::post( '/vehicles/{id}/send-mail', [App\Http\Controllers\VehicleController::class, 'sendMail']);

    /** Drivers */
    // Route::get( '/drivers/search', [App\Http\Controllers\DriverController::class, 'search']);
    // Route::get( '/drivers', [App\Http\Controllers\DriverController::class, 'getAll']);
    // Route::get( '/drivers/{id}', [App\Http\Controllers\DriverController::class, 'getById']);
    // Route::get('/drivers/init/form', [App\Http\Controllers\DriverController::class, 'getInitialFormData']);
    // Route::post( '/drivers', [App\Http\Controllers\DriverController::class, 'store']);
    // Route::post( '/drivers/{id}/update', [App\Http\Controllers\DriverController::class, 'update']);
    // Route::post( '/drivers/{id}/delete', [App\Http\Controllers\DriverController::class, 'destroy']);
    // Route::post( '/drivers/{id}/send-mail', [App\Http\Controllers\DriverController::class, 'sendMail']);

    /** Reservations */
    // Route::get( '/reservations/search', [App\Http\Controllers\ReservationController::class, 'search']);
    // Route::get( '/reservations', [App\Http\Controllers\ReservationController::class, 'getAll']);
    // Route::get( '/reservations/{id}', [App\Http\Controllers\ReservationController::class, 'getById']);
    // Route::get('/reservations/init/form', [App\Http\Controllers\ReservationController::class, 'getInitialFormData']);
    // Route::post( '/reservations', [App\Http\Controllers\ReservationController::class, 'store']);
    // Route::post( '/reservations/{id}/update', [App\Http\Controllers\ReservationController::class, 'update']);
    // Route::post( '/reservations/{id}/delete', [App\Http\Controllers\ReservationController::class, 'destroy']);

    /** Attendances */
    Route::get('/attendances', [App\Http\Controllers\EventController::class, 'getAll']);
    Route::get('/attendances/face/recognize', [App\Http\Controllers\AttendanceController::class, 'getFaceRecognize']);
    Route::post('/attendances/{date}/check-in', [App\Http\Controllers\AttendanceController::class, 'checkIn']);
});

/**
 * ===============================
 * API Key protected routes
 * ===============================
 */
Route::middleware('api.key')->group(function() {
    /** Vehicles */
    // Route::get( '/vehicles/search', [App\Http\Controllers\VehicleController::class, 'search']);
    // Route::get( '/vehicles/{id}', [App\Http\Controllers\VehicleController::class, 'getById']);

    /** Drivers */
    // Route::get( '/drivers/search', [App\Http\Controllers\DriverController::class, 'search']);
    // Route::get( '/drivers/{id}', [App\Http\Controllers\DriverController::class, 'getById']);
    // Route::get( '/drivers/{id}/assignments/{date}', [App\Http\Controllers\DriverController::class, 'getAssignments']);
    // Route::get('/drivers/init/form', [App\Http\Controllers\DriverController::class, 'getInitialFormData']);
    // Route::post( '/drivers', [App\Http\Controllers\DriverController::class, 'store']);
    // Route::post( '/drivers/{id}/update', [App\Http\Controllers\DriverController::class, 'update']);

    /** Reservations */
    // Route::get( '/reservations/search', [App\Http\Controllers\ReservationController::class, 'search']);
    // Route::get( '/reservations', [App\Http\Controllers\ReservationController::class, 'getAll']);
    // Route::get( '/reservations/{id}', [App\Http\Controllers\ReservationController::class, 'getById']);
    // Route::get('/reservations/init/form', [App\Http\Controllers\ReservationController::class, 'getInitialFormData']);
    // Route::post( '/reservations', [App\Http\Controllers\ReservationController::class, 'store']);
    // Route::post( '/reservations/{id}/update', [App\Http\Controllers\ReservationController::class, 'update']);
    // Route::post( '/reservations/{id}/assign', [App\Http\Controllers\ReservationController::class, 'assign']);
    // Route::post( '/reservations/{id}/status', [App\Http\Controllers\ReservationController::class, 'status']);

    /** Reservation Assignments */
    // Route::get( '/reservation-assignments/search', [App\Http\Controllers\ReservationAssignmentController::class, 'search']);
    // Route::get( '/reservation-assignments', [App\Http\Controllers\ReservationAssignmentController::class, 'getAll']);
    // Route::get( '/reservation-assignments/{id}', [App\Http\Controllers\ReservationAssignmentController::class, 'getById']);
    // Route::get('/reservation-assignments/init/form', [App\Http\Controllers\ReservationAssignmentController::class, 'getInitialFormData']);
    // Route::post( '/reservation-assignments', [App\Http\Controllers\ReservationAssignmentController::class, 'store']);
    // Route::post( '/reservation-assignments/{id}/update', [App\Http\Controllers\ReservationAssignmentController::class, 'update']);
    // Route::post( '/reservation-assignments/{id}/delete', [App\Http\Controllers\ReservationAssignmentController::class, 'destroy']);

    /** Calendar events */
    Route::get( '/events', [App\Http\Controllers\EventController::class, 'getAll']);

    /** Time Attendances */
    Route::prefix('time-attendance')->group(function() {
        Route::get( '/face/recognize', [App\Http\Controllers\AttendanceController::class, 'getFaceRecognize']);
        Route::post( '/check-in', [App\Http\Controllers\AttendanceController::class, 'checkIn']);
    });
});