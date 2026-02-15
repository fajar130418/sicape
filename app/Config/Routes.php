<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Auth::index');
$routes->get('/login', 'Auth::index');
$routes->post('/auth/login', 'Auth::login');
$routes->get('/logout', 'Auth::logout');
$routes->get('/dashboard', 'Dashboard::index', ['filter' => 'auth']);
$routes->get('/leave/create', 'Leave::create', ['filter' => 'auth']);
$routes->post('/leave/store', 'Leave::store', ['filter' => 'auth']);
$routes->get('/leave/history', 'Leave::history', ['filter' => 'auth']);
$routes->match(['get', 'post'], '/leave/print/(:num)', 'Leave::print/$1', ['filter' => 'auth']);
$routes->get('/admin', 'Admin::index', ['filter' => 'auth']);
$routes->get('/admin/process/(:num)/(:segment)', 'Admin::process/$1/$2', ['filter' => 'auth']);
$routes->post('/admin/process/(:num)/(:segment)', 'Admin::process/$1/$2', ['filter' => 'auth']);
$routes->get('/admin/recalculate-durations', 'Admin::recalculateDurations', ['filter' => 'auth']);

// Employee Management Routes
$routes->get('/employee', 'Employee::index', ['filter' => 'auth']);
$routes->get('/employee/supervisors', 'Employee::supervisors', ['filter' => 'auth']);
$routes->get('/employee/admins', 'Employee::admins', ['filter' => 'auth']);
$routes->get('/employee/create', 'Employee::create', ['filter' => 'auth']);
$routes->post('/employee/store', 'Employee::store', ['filter' => 'auth']);
$routes->get('/employee/edit/(:num)', 'Employee::edit/$1', ['filter' => 'auth']);
$routes->post('/employee/update/(:num)', 'Employee::update/$1', ['filter' => 'auth']);
$routes->get('/employee/delete/(:num)', 'Employee::delete/$1', ['filter' => 'auth']);
$routes->get('/employee/import', 'Import::index', ['filter' => 'auth']);
$routes->get('/employee/import/template', 'Import::downloadTemplate', ['filter' => 'auth']);
$routes->post('/employee/import/process', 'Import::process', ['filter' => 'auth']);
$routes->get('/employee/export', 'Import::export', ['filter' => 'auth']);
$routes->get('/employee/contracts', 'Employee::contracts', ['filter' => 'auth']);
$routes->post('/employee/mass-renew', 'Employee::massRenew', ['filter' => 'auth']);

// Holiday Management Routes
$routes->get('/admin/holidays', 'Holiday::index', ['filter' => 'auth']);
$routes->post('/admin/holidays/store', 'Holiday::store', ['filter' => 'auth']);
$routes->get('/admin/holidays/delete/(:num)', 'Holiday::delete/$1', ['filter' => 'auth']);

// Supervisor Approval Routes
$routes->get('/approval', 'Approval::index', ['filter' => 'auth']);
$routes->post('/approval/process/(:num)/(:segment)', 'Approval::process/$1/$2', ['filter' => 'auth']);

// Report Routes
$routes->get('/report', 'Report::index', ['filter' => 'auth']);
$routes->get('/report/recap', 'Report::recap', ['filter' => 'auth']);
$routes->get('/report/details', 'Report::details', ['filter' => 'auth']);
$routes->get('/report/quota', 'Report::quota', ['filter' => 'auth']);
