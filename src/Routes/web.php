<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/**
 * Preparation
 */
Route::get('install', 'InstallPreparationController@requirements')
	->name('install');
Route::get('install/env', 'InstallPreparationController@env')
	->name('install.env');
Route::get('install/modules', 'InstallPreparationController@modules')
	->name('install.modules');
Route::get('install/perform', 'InstallPreparationController@perform')
	->name('install.perform');

Route::post('install/env', 'InstallPreparationController@postEnv');
Route::post('install/modules', 'InstallPreparationController@postModules');

/**
 * Installation
 */
Route::get('install/step/env', 'InstallerController@stepEnv')
	->name('install.steps.env');
Route::get('install/step/modules', 'InstallerController@stepModules')
	->name('install.steps.modules');
Route::get('install/step/migrate', 'InstallerController@stepMigrate')
	->name('install.steps.migrate');
Route::get('install/step/migrateModules', 'InstallerController@stepMigrateModules')
	->name('install.steps.migrateModules');
Route::get('install/step/seed', 'InstallerController@stepSeed')
	->name('install.steps.seed');
Route::get('install/step/node', 'InstallerController@stepNode')
	->name('install.steps.node');
Route::get('install/step/assets', 'InstallerController@stepAssets')
	->name('install.steps.assets');
Route::get('install/step/symStorage', 'InstallerController@stepSymStorage')
	->name('install.steps.symStorage');
Route::get('install/step/symThemes', 'InstallerController@stepSymThemes')
	->name('install.steps.symThemes');
Route::get('install/step/cache', 'InstallerController@stepCache')
	->name('install.steps.cache');